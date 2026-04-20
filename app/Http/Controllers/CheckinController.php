<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckinController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $records = $this->loadActiveCheckins($search, 80);
        $checkins = $this->paginateCollection($records, 10, $request);
        $rooms = $this->loadRoomOptions();
        $packages = $this->loadPackageOptions();

        $viewData = [
            'checkins' => $checkins,
            'search' => $search,
            'nextRegNo' => $this->generateNextRegNo(),
            'rooms' => $rooms,
            'packages' => $packages,
            'summary' => [
                'active' => $this->countActiveCheckins($search),
                'rooms_ready' => collect($rooms)->where('available', true)->count(),
                'packages' => count($packages),
            ],
            'typeOptions' => ['INDIVIDUAL', 'GROUP RESERVATION', 'GROUP COMPANY', 'TRAVEL', 'OTA'],
            'paymentOptions' => ['CASH', 'CARD', 'OTA', 'COMPANY', 'TRAVEL', 'COMPLIMENT'],
            'segmentOptions' => ['DIRECT', 'TRAVEL', 'OTA', 'CORPORATE', 'GROUP'],
            'religionOptions' => ['ISLAM', 'KRISTEN', 'KATOLIK', 'HINDU', 'BUDDHA', 'KONGHUCU'],
            'nationalityOptions' => ['INA', 'MAL', 'SGP', 'AUS', 'JPN', 'KOR', 'USA'],
            'idTypeOptions' => ['KTP', 'SIM', 'PASSPORT', 'KITAS'],
        ];

        return $this->respond($request, 'checkin.index', $viewData, [
            'checkins' => $this->paginatorPayload($checkins),
            'search' => $search,
            'next_reg_no' => $viewData['nextRegNo'],
            'rooms' => $rooms,
            'packages' => $packages,
            'summary' => $viewData['summary'],
            'options' => [
                'type' => $viewData['typeOptions'],
                'payment' => $viewData['paymentOptions'],
                'segment' => $viewData['segmentOptions'],
                'religion' => $viewData['religionOptions'],
                'nationality' => $viewData['nationalityOptions'],
                'id_type' => $viewData['idTypeOptions'],
            ],
        ]);
    }

    public function store(Request $request)
    {
        return $this->saveCheckin($request);
    }

    public function update(Request $request, string $regNo2)
    {
        return $this->saveCheckin($request, trim((string) $regNo2));
    }

    public function destroy(Request $request, string $regNo2)
    {
        $detail = $this->findDetailByKey(trim((string) $regNo2));

        if (!$detail) {
            return $this->respondError($request, 'Detail check in tidak ditemukan.', 404, [], '/checkin', false);
        }

        DB::transaction(function () use ($detail) {
            DB::table('DATA2')->whereRaw('RTRIM(RegNo2) = ?', [$detail->RegNo2])->delete();
            DB::table('DataMove')->whereRaw('RTRIM(RegNo2) = ?', [$detail->RegNo2])->delete();
            DB::table('Deposit')
                ->whereRaw('RTRIM(RegNo) = ?', [$detail->RegNo])
                ->whereRaw('RTRIM(Kode) = ?', [$detail->Kode])
                ->delete();

            $this->clearRoomOccupiedMarker($detail->Kode, $detail->RegNo2);
            $this->syncHeaderFromRemainingDetail($detail->RegNo);
        });

        return $this->respondAfterMutation($request, '/checkin', 'Detail check in berhasil dihapus.', [
            'id' => $detail->id ?? null,
            'reg_no' => $detail->RegNo,
            'reg_no2' => $detail->RegNo2,
            'room_code' => $detail->Kode,
        ]);
    }

    private function saveCheckin(Request $request, ?string $currentRegNo2 = null)
    {
        $validated = $this->validateRequest($request);
        $regNo = strtoupper(trim((string) ($validated['GeneratedRegNo'] ?? $this->generateNextRegNo())));
        $currentDetail = $currentRegNo2 ? $this->findDetailByKey($currentRegNo2) : null;
        $detailRows = $this->collectRoomDetails($validated);

        if ($regNo === '') {
            return $this->respondError($request, 'Reg number wajib diisi.');
        }

        if (empty($detailRows)) {
            return $this->respondError($request, 'Minimal satu room detail harus diisi.');
        }

        if ($currentRegNo2 && !$currentDetail) {
            return $this->respondError($request, 'Detail check in yang akan diubah tidak ditemukan.', 404, [], '/checkin', false);
        }

        if ($currentDetail && count($detailRows) !== 1) {
            return $this->respondError($request, 'Mode update hanya boleh memuat satu baris detail room.');
        }

        $roomTracker = [];
        foreach ($detailRows as $index => &$detailRow) {
            $roomCode = $detailRow['room_code'];
            $detailKey = $detailRow['detail_key'];

            if (!$this->roomExists($roomCode)) {
                return $this->respondError($request, 'Kode room ' . $roomCode . ' tidak ditemukan.');
            }

            if (isset($roomTracker[$roomCode])) {
                return $this->respondError($request, 'Room ' . $roomCode . ' muncul lebih dari satu kali di form yang sama.');
            }

            $roomTracker[$roomCode] = true;
            $ignoreKey = $currentDetail && $detailKey !== '' ? $currentDetail->RegNo2 : null;

            if ($this->roomHasActiveCheckin($roomCode, $ignoreKey)) {
                return $this->respondError($request, 'Room ' . $roomCode . ' masih dipakai guest aktif.');
            }

            $detailRow['regno2'] = $currentDetail
                && $detailKey !== ''
                && $currentDetail->RegNo === $regNo
                && $currentDetail->Kode === $roomCode
                    ? $currentDetail->RegNo2
                    : $this->generateRegNo2($regNo, $roomCode);
        }
        unset($detailRow);

        $primaryRoomCode = $detailRows[0]['room_code'];
        $dataPayload = $this->buildDataPayload($validated, $regNo, $primaryRoomCode);
        $detailPayloads = collect($detailRows)->map(function ($detailRow) use ($validated, $regNo) {
            return $this->buildData2Payload($validated, $regNo, $detailRow);
        })->values();
        $movePayloads = $detailPayloads->map(fn ($detailPayload) => $this->buildDataMovePayload($detailPayload))->values();
        $depositPayloads = $detailPayloads->map(fn ($detailPayload) => $this->buildDepositPayload($regNo, $detailPayload['Kode'], $detailPayload['TglIn']))->values();

        DB::transaction(function () use ($currentDetail, $currentRegNo2, $regNo, $dataPayload, $detailPayloads, $movePayloads, $depositPayloads) {
            $this->upsertHeader($regNo, $dataPayload);

            if ($currentDetail) {
                DB::table('DATA2')->whereRaw('RTRIM(RegNo2) = ?', [$currentRegNo2])->delete();
                DB::table('DataMove')->whereRaw('RTRIM(RegNo2) = ?', [$currentRegNo2])->delete();
                DB::table('Deposit')
                    ->whereRaw('RTRIM(RegNo) = ?', [$currentDetail->RegNo])
                    ->whereRaw('RTRIM(Kode) = ?', [$currentDetail->Kode])
                    ->delete();
            }

            foreach ($detailPayloads as $detailPayload) {
                DB::table('DATA2')->insert($detailPayload);
            }

            foreach ($movePayloads as $movePayload) {
                DB::table('DataMove')->insert($movePayload);
            }

            foreach ($depositPayloads as $depositPayload) {
                DB::table('Deposit')->insert($depositPayload);
            }

            if ($currentDetail && $currentDetail->Kode !== $detailPayloads[0]['Kode']) {
                $this->clearRoomOccupiedMarker($currentDetail->Kode, $currentDetail->RegNo2);
            }

            if ($currentDetail && $currentDetail->RegNo !== $regNo) {
                $this->syncHeaderFromRemainingDetail($currentDetail->RegNo);
            }

            foreach ($detailPayloads as $detailPayload) {
                $this->markRoomOccupiedClean($detailPayload['Kode']);
            }
        });

        return $this->respondAfterMutation($request, '/checkin', $currentDetail ? 'Detail check in berhasil diperbarui.' : 'Check in multi-room berhasil disimpan.', [
            'reg_no' => $regNo,
            'detail_keys' => $detailPayloads->pluck('RegNo2')->values(),
            'rooms' => $detailPayloads->pluck('Kode')->values(),
        ], $currentDetail ? 200 : 201);
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'GeneratedRegNo' => 'required|string|max:30',
            'CheckInDate' => 'required|date_format:Y-m-d',
            'CheckInTime' => 'required|string|max:20',
            'ReservationNumber' => 'nullable|string|max:30',
            'GuestName' => 'required|string|max:120',
            'GuestName2' => 'nullable|string|max:120',
            'Address' => 'nullable|string|max:150',
            'Kelurahan' => 'nullable|string|max:60',
            'Kecamatan' => 'nullable|string|max:60',
            'KabCity' => 'nullable|string|max:60',
            'ProvinceCountry' => 'nullable|string|max:60',
            'TypeOfId' => 'nullable|string|max:30',
            'IdNumber' => 'nullable|string|max:60',
            'ExpiredDate' => 'nullable|date_format:Y-m-d',
            'GroupPosition' => 'nullable|string|max:60',
            'TypeOfCheckIn' => 'required|string|max:40',
            'PlaceOfBirth' => 'nullable|string|max:60',
            'BirthDate' => 'nullable|date_format:Y-m-d',
            'Religion' => 'nullable|string|max:30',
            'Nationality' => 'nullable|string|max:10',
            'NumberOfPerson' => 'required|integer|min:1|max:20',
            'EstimationOut' => 'required|date_format:Y-m-d|after_or_equal:CheckInDate',
            'PaymentMethod' => 'required|string|max:30',
            'Company' => 'nullable|string|max:80',
            'CreditCardNumber' => 'nullable|string|max:60',
            'CheckDeposit' => 'nullable',
            'Segment' => 'nullable|string|max:30',
            'Phone' => 'nullable|string|max:40',
            'Email' => 'nullable|string|max:100',
            'Remarks' => 'nullable|string|max:120',
            'Member' => 'nullable|string|max:80',
            'Sales' => 'nullable|string|max:80',
            'RoomCodeList' => 'required|array|min:1',
            'RoomCodeList.*' => 'nullable|string|max:10',
            'PackageCodeList' => 'nullable|array',
            'PackageCodeList.*' => 'nullable|string|max:30',
            'NominalList' => 'nullable|array',
            'NominalList.*' => 'nullable|string|max:30',
            'BreakfastList' => 'nullable|array',
            'BreakfastList.*' => 'nullable|integer|min:0|max:20',
            'DetailKeyList' => 'nullable|array',
            'DetailKeyList.*' => 'nullable|string|max:60',
        ]);
    }

    private function collectRoomDetails(array $validated): array
    {
        $roomCodes = $validated['RoomCodeList'] ?? [];
        $packageCodes = $validated['PackageCodeList'] ?? [];
        $nominals = $validated['NominalList'] ?? [];
        $breakfasts = $validated['BreakfastList'] ?? [];
        $detailKeys = $validated['DetailKeyList'] ?? [];
        $totalRows = max(count($roomCodes), count($packageCodes), count($nominals), count($breakfasts), count($detailKeys));
        $details = [];

        for ($index = 0; $index < $totalRows; $index++) {
            $roomCode = strtoupper(trim((string) ($roomCodes[$index] ?? '')));
            if ($roomCode === '') {
                continue;
            }

            $details[] = [
                'detail_key' => trim((string) ($detailKeys[$index] ?? '')),
                'room_code' => $roomCode,
                'package_code' => strtoupper(trim((string) ($packageCodes[$index] ?? ''))),
                'nominal' => trim((string) ($nominals[$index] ?? '')),
                'breakfast' => (int) ($breakfasts[$index] ?? 0),
            ];
        }

        return $details;
    }

    private function buildDataPayload(array $validated, string $regNo, string $roomCode): array
    {
        return [
            'RegNo' => $regNo,
            'ResNo' => trim((string) ($validated['ReservationNumber'] ?? '')),
            'Tipe' => strtoupper(trim((string) $validated['TypeOfCheckIn'])),
            'KodeCust' => strtoupper(trim((string) ($validated['Company'] ?? ''))),
            'Service' => 0,
            'Deposit' => 0,
            'Tax' => 0,
            'Disc' => 0,
            'Discount' => 0,
            'DiscWeekDay' => 0,
            'Kode' => $roomCode,
            'Periksa' => !empty($validated['CheckDeposit']) ? 1 : 0,
            'Sales' => strtoupper(trim((string) ($validated['Sales'] ?? ''))),
        ];
    }

    private function buildData2Payload(array $validated, string $regNo, array $detailRow): array
    {
        $roomCode = $detailRow['room_code'];
        $regNo2 = $detailRow['regno2'];
        $room = DB::table('ROOM')
            ->selectRaw('Rate1, Rate2, Rate3, Rate4')
            ->whereRaw('RTRIM(Kode) = ?', [$roomCode])
            ->first();

        $checkInDate = Carbon::createFromFormat('Y-m-d', $validated['CheckInDate'])->format('Y-m-d');
        $checkOutDate = Carbon::createFromFormat('Y-m-d', $validated['EstimationOut'])->format('Y-m-d');
        $jamIn = $this->normalizeTime($validated['CheckInTime']);
        $birthDate = !empty($validated['BirthDate']) ? Carbon::createFromFormat('Y-m-d', $validated['BirthDate'])->format('Y-m-d') : null;
        $expiredDate = !empty($validated['ExpiredDate']) ? Carbon::createFromFormat('Y-m-d', $validated['ExpiredDate'])->format('Y-m-d') : $checkInDate;
        $hari = max(Carbon::parse($checkInDate)->diffInDays(Carbon::parse($checkOutDate)), 1);
        $packageCode = $detailRow['package_code'];
        $packageNominal = $packageCode !== ''
            ? (float) (DB::table('Package')->whereRaw('RTRIM(Nofak) = ?', [$packageCode])->value('JumlahRes') ?? 0)
            : 0;
        $nominal = $this->normalizeMoney($detailRow['nominal'] ?? null);
        $nominal = $nominal > 0 ? $nominal : $packageNominal;
        $checkDeposit = !empty($validated['CheckDeposit']) ? 1 : 0;
        $segment = strtoupper(trim((string) ($validated['Segment'] ?? '')));
        $company = strtoupper(trim((string) ($validated['Company'] ?? '')));
        $creditCard = strtoupper(trim((string) ($validated['CreditCardNumber'] ?? '')));
        $reservationNumber = trim((string) ($validated['ReservationNumber'] ?? ''));

        return [
            'RegNo' => $regNo,
            'TglIn' => $checkInDate,
            'JamIn' => $jamIn,
            'TglKeluar' => $checkOutDate,
            'TglOut' => $checkOutDate,
            'TglLahir' => $birthDate,
            'Guest' => strtoupper(trim((string) $validated['GuestName'])),
            'KTP' => strtoupper(trim((string) ($validated['IdNumber'] ?? ''))),
            'Alamat' => strtoupper(trim((string) ($validated['Address'] ?? ''))),
            'Agama' => strtoupper(trim((string) ($validated['Religion'] ?? ''))),
            'KodeNegara' => strtoupper(trim((string) ($validated['Nationality'] ?? 'INA'))),
            'Person' => (int) $validated['NumberOfPerson'],
            'Usaha' => $company,
            'Profesi' => '',
            'Kode' => $roomCode,
            'Pst' => ' ',
            'Rate1' => (float) ($room->Rate1 ?? 0),
            'Rate2' => (float) ($room->Rate2 ?? 0),
            'Rate3' => (float) ($room->Rate3 ?? 0),
            'Rate4' => (float) ($room->Rate4 ?? 0),
            'Short' => 0,
            'Hari' => $hari,
            'SpecialRate' => 0,
            'Guest2' => strtoupper(trim((string) ($validated['GuestName2'] ?? ''))),
            'Guest3' => strtoupper(trim((string) ($validated['Member'] ?? ''))),
            'Payment' => strtoupper(trim((string) $validated['PaymentMethod'])),
            'CardNumber' => $creditCard,
            'Remark' => strtoupper(trim((string) ($validated['Remarks'] ?? ''))),
            'Expired' => $expiredDate,
            'SafeDeposit' => (string) $checkDeposit,
            'Visa' => '',
            'Purpose' => $segment,
            'TypeId' => strtoupper(trim((string) ($validated['TypeOfId'] ?? ''))),
            'Information' => '',
            'Kelurahan' => strtoupper(trim((string) ($validated['Kelurahan'] ?? ''))),
            'Kecamatan' => strtoupper(trim((string) ($validated['Kecamatan'] ?? ''))),
            'Kota' => strtoupper(trim((string) ($validated['KabCity'] ?? ''))),
            'Propinsi' => strtoupper(trim((string) ($validated['ProvinceCountry'] ?? ''))),
            'Nominal' => $nominal,
            'Tipe' => strtoupper(trim((string) $validated['TypeOfCheckIn'])),
            'PlaceBirth' => strtoupper(trim((string) ($validated['PlaceOfBirth'] ?? ''))),
            'PlaceIssued' => strtoupper(trim((string) ($validated['ProvinceCountry'] ?? ''))),
            'DateIssued' => $expiredDate,
            'ExtraBed' => 0,
            'strTglIn' => $this->buildStrTgl($checkInDate, $jamIn),
            'Receipt' => $reservationNumber,
            'CreditCard' => $creditCard,
            'Remark2' => '',
            'Disc' => 0,
            'RegNo2' => $regNo2,
            'Posisi' => strtoupper(trim((string) ($validated['GroupPosition'] ?? ''))),
            'Phone' => trim((string) ($validated['Phone'] ?? '')),
            'Periksa' => $checkDeposit,
            'Package' => $packageCode,
            'Segment' => $segment,
            'Plot' => 0,
            'Email' => trim((string) ($validated['Email'] ?? '')),
            'Member' => strtoupper(trim((string) ($validated['Member'] ?? ''))),
            'Sales' => strtoupper(trim((string) ($validated['Sales'] ?? ''))),
            'Member2' => '',
            'Member3' => '',
            'BF' => (int) ($detailRow['breakfast'] ?? 0),
            'Status' => 'IN HOUSE',
        ];
    }

    private function buildDataMovePayload(array $detailPayload): array
    {
        return [
            'RegNo' => $detailPayload['RegNo'],
            'TglIn' => $detailPayload['TglIn'],
            'JamIn' => $detailPayload['JamIn'],
            'Kode' => $detailPayload['Kode'],
            'Rate1' => $detailPayload['Rate1'],
            'Rate2' => $detailPayload['Rate2'],
            'TglMove' => $detailPayload['TglIn'],
            'JamMove' => $detailPayload['JamIn'],
            'RegNo2' => $detailPayload['RegNo2'],
            'Package' => $detailPayload['Package'],
            'Nominal' => $detailPayload['Nominal'],
        ];
    }

    private function buildDepositPayload(string $regNo, string $roomCode, string $checkInDate): array
    {
        return [
            'RegNo' => $regNo,
            'Deposit' => 0,
            'TglIn' => $checkInDate,
            'Kode' => $roomCode,
        ];
    }
    private function upsertHeader(string $regNo, array $payload): void
    {
        if (DB::table('DATA')->whereRaw('RTRIM(RegNo) = ?', [$regNo])->exists()) {
            DB::table('DATA')->whereRaw('RTRIM(RegNo) = ?', [$regNo])->update($payload);
            return;
        }

        DB::table('DATA')->insert($payload);
    }

    private function syncHeaderFromRemainingDetail(string $regNo): void
    {
        $remaining = DB::table('DATA2')
            ->selectRaw("RTRIM(RegNo) as RegNo, RTRIM(Kode) as Kode, RTRIM(Tipe) as Tipe, RTRIM(Receipt) as Receipt, RTRIM(Sales) as Sales, Periksa")
            ->whereRaw('RTRIM(RegNo) = ?', [$regNo])
            ->orderBy('TglIn')
            ->first();

        if (!$remaining) {
            DB::table('DATA')->whereRaw('RTRIM(RegNo) = ?', [$regNo])->delete();
            return;
        }

        DB::table('DATA')
            ->whereRaw('RTRIM(RegNo) = ?', [$regNo])
            ->update([
                'ResNo' => trim((string) ($remaining->Receipt ?? '')),
                'Tipe' => trim((string) ($remaining->Tipe ?? '')),
                'Kode' => trim((string) ($remaining->Kode ?? '')),
                'Periksa' => (int) ($remaining->Periksa ?? 0),
                'Sales' => trim((string) ($remaining->Sales ?? '')),
            ]);
    }

    private function loadActiveCheckins(string $search, int $limit = 80)
    {
        $idSelect = $this->legacyIdSelect('DATA2');
        $query = DB::table('DATA2')
            ->selectRaw("$idSelect, RTRIM(RegNo) as RegNo, RTRIM(RegNo2) as RegNo2, RTRIM(Kode) as Kode, RTRIM(Guest) as Guest, RTRIM(Guest2) as Guest2, RTRIM(Tipe) as Tipe, RTRIM(Payment) as Payment, RTRIM(Segment) as Segment, RTRIM(Package) as Package, RTRIM(Receipt) as Receipt, RTRIM(TypeId) as TypeOfId, RTRIM(KTP) as KTP, RTRIM(Alamat) as Alamat, RTRIM(Kelurahan) as Kelurahan, RTRIM(Kecamatan) as Kecamatan, RTRIM(Kota) as Kota, RTRIM(Propinsi) as Propinsi, RTRIM(PlaceBirth) as PlaceBirth, RTRIM(Agama) as Agama, RTRIM(KodeNegara) as KodeNegara, RTRIM(Usaha) as Usaha, RTRIM(CardNumber) as CardNumber, RTRIM(Remark) as Remark, RTRIM(Posisi) as Posisi, RTRIM(Phone) as Phone, RTRIM(Email) as Email, RTRIM(Member) as Member, RTRIM(Sales) as Sales, TglIn, JamIn, JamOut, TglKeluar, TglLahir, Expired, Person, BF, Nominal, SafeDeposit")
            ->where('Pst', '=', ' ')
            ->whereRaw("RTRIM(Kode) <> '999'");

        if ($search !== '') {
            $keyword = '%' . strtoupper($search) . '%';
            $query->where(function ($builder) use ($keyword) {
                $builder->whereRaw('UPPER(RTRIM(RegNo)) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(RegNo2)) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(Kode)) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(Guest)) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(Package)) LIKE ?', [$keyword]);
            });
        }

        return $query
            ->orderByDesc('TglIn')
            ->orderByDesc('RegNo2')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $row->check_in_date = !empty($row->TglIn) ? Carbon::parse($row->TglIn)->format('d-m-Y') : '';
                $row->check_out_date = !empty($row->TglKeluar) ? Carbon::parse($row->TglKeluar)->format('d-m-Y') : '';
                $row->birth_date_iso = !empty($row->TglLahir) ? Carbon::parse($row->TglLahir)->format('Y-m-d') : '';
                $row->expired_date_iso = !empty($row->Expired) ? Carbon::parse($row->Expired)->format('Y-m-d') : '';
                $row->check_in_date_iso = !empty($row->TglIn) ? Carbon::parse($row->TglIn)->format('Y-m-d') : '';
                $row->check_out_date_iso = !empty($row->TglKeluar) ? Carbon::parse($row->TglKeluar)->format('Y-m-d') : '';
                $row->check_in_time = $this->displayTime($row->JamIn ?? null);
                $row->nominal_display = number_format((float) ($row->Nominal ?? 0), 0, ',', '.');
                $row->record_json = json_encode([
                    'Id' => $row->id,
                    'DetailKey' => $row->RegNo2,
                    'RegNo' => $row->RegNo,
                    'RoomCode' => $row->Kode,
                    'GuestName' => $row->Guest,
                    'GuestName2' => $row->Guest2,
                    'ReservationNumber' => $row->Receipt,
                    'TypeOfCheckIn' => $row->Tipe,
                    'CheckInDate' => $row->check_in_date_iso,
                    'CheckInTime' => $row->check_in_time,
                    'EstimationOut' => $row->check_out_date_iso,
                    'PlaceOfBirth' => $row->PlaceBirth,
                    'BirthDate' => $row->birth_date_iso,
                    'Religion' => $row->Agama,
                    'Nationality' => $row->KodeNegara,
                    'NumberOfPerson' => (int) ($row->Person ?? 1),
                    'PaymentMethod' => $row->Payment,
                    'Company' => $row->Usaha,
                    'CreditCardNumber' => $row->CardNumber,
                    'Segment' => $row->Segment,
                    'Phone' => $row->Phone,
                    'Email' => $row->Email,
                    'Breakfast' => (int) ($row->BF ?? 0),
                    'Remarks' => $row->Remark,
                    'Member' => $row->Member,
                    'Sales' => $row->Sales,
                    'Address' => $row->Alamat,
                    'Kelurahan' => $row->Kelurahan,
                    'Kecamatan' => $row->Kecamatan,
                    'KabCity' => $row->Kota,
                    'ProvinceCountry' => $row->Propinsi,
                    'TypeOfId' => $row->TypeOfId,
                    'IdNumber' => $row->KTP,
                    'ExpiredDate' => $row->expired_date_iso,
                    'GroupPosition' => $row->Posisi,
                    'PackageCode' => $row->Package,
                    'Nominal' => (float) ($row->Nominal ?? 0),
                    'CheckDeposit' => (int) ($row->SafeDeposit ?? 0),
                ], JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_TAG | JSON_HEX_QUOT);

                return $row;
            });
    }

    private function countActiveCheckins(string $search): int
    {
        $query = DB::table('DATA2')
            ->where('Pst', '=', ' ')
            ->whereRaw("RTRIM(Kode) <> '999'");

        if ($search !== '') {
            $keyword = '%' . strtoupper($search) . '%';
            $query->where(function ($builder) use ($keyword) {
                $builder->whereRaw('UPPER(RTRIM(RegNo)) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(RegNo2)) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(Kode)) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(Guest)) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(Package)) LIKE ?', [$keyword]);
            });
        }

        return (int) $query->count();
    }

    private function loadRoomOptions(): array
    {
        $activeRooms = DB::table('DATA2')
            ->selectRaw('RTRIM(Kode) as Kode')
            ->where('Pst', '=', ' ')
            ->whereRaw("RTRIM(Kode) <> '999'")
            ->get()
            ->pluck('Kode')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->flip();

        $blockedStatus = ['OUT OF ORDER', 'RENOVATED', 'OWNER UNIT', 'COMPLIMENTARY'];

        return DB::table('ROOM')
            ->selectRaw("RTRIM(Kode) as Kode, RTRIM(Nama) as Nama, RTRIM(Status) as Status, RTRIM(Status2) as Status2")
            ->whereRaw("RTRIM(Kode) <> '999'")
            ->orderBy('Kode')
            ->get()
            ->map(function ($room) use ($activeRooms, $blockedStatus) {
                $status = strtoupper(trim((string) ($room->Status ?? '')));
                $kode = trim((string) $room->Kode);

                return [
                    'kode' => $kode,
                    'kelas' => trim((string) ($room->Nama ?? '')),
                    'status' => trim((string) ($room->Status ?? '')),
                    'status2' => trim((string) ($room->Status2 ?? '')),
                    'available' => !$activeRooms->has($kode) && !in_array($status, $blockedStatus, true),
                ];
            })
            ->values()
            ->all();
    }

    private function loadPackageOptions(): array
    {
        return DB::table('Package')
            ->selectRaw("RTRIM(Nofak) as Nofak, RTRIM(Meja) as Meja, JumlahRes, Expired")
            ->where('Expired', '>=', Carbon::today()->format('Y-m-d'))
            ->orderByDesc('Expired')
            ->orderByDesc('Nofak')
            ->get()
            ->map(function ($package) {
                return [
                    'kode' => trim((string) $package->Nofak),
                    'nama' => trim((string) $package->Meja),
                    'nominal' => (float) ($package->JumlahRes ?? 0),
                    'expired' => !empty($package->Expired) ? Carbon::parse($package->Expired)->format('d-m-Y') : '',
                ];
            })
            ->values()
            ->all();
    }

    private function findDetailByKey(string $regNo2)
    {
        $idSelect = $this->legacyIdSelect('DATA2');
        return DB::table('DATA2')
            ->selectRaw("$idSelect, RTRIM(RegNo) as RegNo, RTRIM(RegNo2) as RegNo2, RTRIM(Kode) as Kode")
            ->whereRaw('RTRIM(RegNo2) = ?', [$regNo2])
            ->first();
    }


    private function roomExists(string $roomCode): bool
    {
        return DB::table('ROOM')->whereRaw('RTRIM(Kode) = ?', [$roomCode])->exists();
    }

    private function roomHasActiveCheckin(string $roomCode, ?string $ignoreRegNo2 = null): bool
    {
        $query = DB::table('DATA2')
            ->where('Pst', '=', ' ')
            ->whereRaw('RTRIM(Kode) = ?', [$roomCode]);

        if ($ignoreRegNo2) {
            $query->whereRaw('RTRIM(RegNo2) <> ?', [$ignoreRegNo2]);
        }

        return $query->exists();
    }

    private function markRoomOccupiedClean(string $roomCode): void
    {
        DB::table('ROOM')
            ->whereRaw('RTRIM(Kode) = ?', [$roomCode])
            ->update(['Status2' => 'Occupied Clean']);
    }

    private function clearRoomOccupiedMarker(string $roomCode, ?string $ignoreRegNo2 = null): void
    {
        if ($roomCode === '' || $this->roomHasActiveCheckin($roomCode, $ignoreRegNo2)) {
            return;
        }

        DB::table('ROOM')
            ->whereRaw('RTRIM(Kode) = ?', [$roomCode])
            ->update(['Status2' => null]);
    }

    private function generateNextRegNo(): string
    {
        $prefix = Carbon::now()->format('Ym') . '0088';
        $rows = DB::table('DATA2')
            ->selectRaw('RTRIM(RegNo) as RegNo')
            ->whereRaw('RTRIM(RegNo) LIKE ?', [$prefix . '%'])
            ->get();

        $maxSequence = $rows->map(function ($row) {
            $suffix = substr(trim((string) ($row->RegNo ?? '')), -4);
            return ctype_digit($suffix) ? (int) $suffix : 0;
        })->max() ?? 0;

        return $prefix . str_pad((string) ($maxSequence + 1), 4, '0', STR_PAD_LEFT);
    }

    private function generateRegNo2(string $regNo, string $roomCode): string
    {
        $last = DB::table('DATA2')
            ->selectRaw('RTRIM(RegNo2) as RegNo2')
            ->whereRaw('RTRIM(RegNo) = ?', [$regNo])
            ->whereRaw('RTRIM(Kode) = ?', [$roomCode])
            ->orderByRaw('RTRIM(RegNo2) DESC')
            ->first();

        $sequence = 1;

        if ($last && !empty($last->RegNo2)) {
            $lastSequence = (int) substr(trim((string) $last->RegNo2), -2);
            $sequence = $lastSequence + 1;
        }

        return $regNo . $roomCode . str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
    }

    private function normalizeMoney($value): float
    {
        $normalized = preg_replace('/[^\d]/', '', (string) $value);
        return is_numeric($normalized) ? (float) $normalized : 0;
    }

    private function normalizeTime(?string $value): string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '00:00:00';
        }

        foreach (['H:i:s', 'H:i', 'g:i A', 'g:i:s A'] as $format) {
            try {
                return Carbon::createFromFormat($format, strtoupper($raw))->format('H:i:s');
            } catch (\Throwable $exception) {
            }
        }

        try {
            return Carbon::parse($raw)->format('H:i:s');
        } catch (\Throwable $exception) {
            return '00:00:00';
        }
    }

    private function displayTime($value): string
    {
        if (empty($value)) {
            return '';
        }

        try {
            return Carbon::parse((string) $value)->format('H:i');
        } catch (\Throwable $exception) {
            return substr(trim((string) $value), 0, 5);
        }
    }

    private function buildStrTgl(string $date, string $time): string
    {
        try {
            return Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time)->format('d-m-Y H:i');
        } catch (\Throwable $exception) {
            return Carbon::parse($date)->format('d-m-Y');
        }
    }
}
