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
        $records = $this->loadActiveCheckins($search);
        $checkins = $this->paginateCollection($records, 10, $request);
        $rooms = $this->loadRoomOptions();
        $packages = $this->loadPackageOptions();
        $summary = [
            'active' => $records->count(),
            'rooms_ready' => collect($rooms)->where('available', true)->count(),
            'packages' => count($packages),
        ];

        return view('checkin.index', [
            'checkins' => $checkins,
            'search' => $search,
            'nextRegNo' => $this->generateNextRegNo(),
            'rooms' => $rooms,
            'packages' => $packages,
            'summary' => $summary,
            'typeOptions' => ['INDIVIDUAL', 'GROUP RESERVATION', 'GROUP COMPANY', 'TRAVEL', 'OTA'],
            'paymentOptions' => ['CASH', 'CARD', 'OTA', 'COMPANY', 'TRAVEL', 'COMPLIMENT'],
            'segmentOptions' => ['DIRECT', 'TRAVEL', 'OTA', 'CORPORATE', 'GROUP'],
            'religionOptions' => ['ISLAM', 'KRISTEN', 'KATOLIK', 'HINDU', 'BUDDHA', 'KONGHUCU'],
            'nationalityOptions' => ['INA', 'MAL', 'SGP', 'AUS', 'JPN', 'KOR', 'USA'],
            'idTypeOptions' => ['KTP', 'SIM', 'PASSPORT', 'KITAS'],
        ]);
    }

    public function store(Request $request)
    {
        return $this->saveCheckin($request);
    }

    public function update(Request $request, string $regNo)
    {
        return $this->saveCheckin($request, trim((string) $regNo));
    }

    public function destroy(string $regNo)
    {
        $normalizedRegNo = trim((string) $regNo);
        $existing = DB::table('DATA2')
            ->selectRaw("RTRIM(RegNo) as RegNo, RTRIM(Kode) as Kode")
            ->whereRaw('RTRIM(RegNo) = ?', [$normalizedRegNo])
            ->first();

        if (!$existing) {
            return redirect('/checkin')->with('error', 'Data check in tidak ditemukan.');
        }

        DB::transaction(function () use ($normalizedRegNo, $existing) {
            DB::table('DATA2')->whereRaw('RTRIM(RegNo) = ?', [$normalizedRegNo])->delete();
            $this->clearRoomOccupiedMarker(trim((string) $existing->Kode), $normalizedRegNo);
        });

        return redirect('/checkin')->with('success', 'Data check in berhasil dihapus.');
    }

    private function saveCheckin(Request $request, ?string $currentRegNo = null)
    {
        $validated = $this->validateRequest($request, $currentRegNo);
        $regNo = $currentRegNo ?: trim((string) ($validated['GeneratedRegNo'] ?? $this->generateNextRegNo()));
        $roomCode = strtoupper(trim((string) $validated['RoomCode']));

        if (!$this->roomExists($roomCode)) {
            return back()->withInput()->with('error', 'Kode room tidak ditemukan.');
        }

        if ($this->roomHasActiveCheckin($roomCode, $currentRegNo)) {
            return back()->withInput()->with('error', 'Room masih dipakai guest aktif. Pilih room lain terlebih dulu.');
        }

        $oldRoomCode = null;
        if ($currentRegNo) {
            $oldRoomCode = DB::table('DATA2')
                ->whereRaw('RTRIM(RegNo) = ?', [$currentRegNo])
                ->value(DB::raw('RTRIM(Kode)'));

            if (!$oldRoomCode) {
                return redirect('/checkin')->with('error', 'Data check in yang akan diubah tidak ditemukan.');
            }
        }

        $payload = $this->buildPayload($validated, $regNo);

        DB::transaction(function () use ($currentRegNo, $regNo, $payload, $roomCode, $oldRoomCode) {
            if ($currentRegNo) {
                DB::table('DATA2')->whereRaw('RTRIM(RegNo) = ?', [$currentRegNo])->update($payload);
            } else {
                DB::table('DATA2')->insert($payload);
            }

            if ($oldRoomCode && $oldRoomCode !== $roomCode) {
                $this->clearRoomOccupiedMarker($oldRoomCode, $regNo);
            }

            $this->markRoomOccupiedClean($roomCode);
        });

        return redirect('/checkin')->with('success', $currentRegNo ? 'Data check in berhasil diperbarui.' : 'Data check in berhasil disimpan.');
    }

    private function validateRequest(Request $request, ?string $currentRegNo = null): array
    {
        $rules = [
            'GeneratedRegNo' => 'nullable|string|max:30',
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
            'PackageCode' => 'nullable|string|max:30',
            'Nominal' => 'nullable|string|max:30',
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
            'Breakfast' => 'nullable|integer|min:0|max:20',
            'Remarks' => 'nullable|string|max:120',
            'Member' => 'nullable|string|max:80',
            'Sales' => 'nullable|string|max:80',
            'RoomCode' => 'required|string|max:10',
        ];

        return $request->validate($rules);
    }

    private function buildPayload(array $validated, string $regNo): array
    {
        $roomCode = strtoupper(trim((string) $validated['RoomCode']));
        $room = DB::table('ROOM')
            ->selectRaw('Rate1, Rate2, Rate3, Rate4')
            ->whereRaw('RTRIM(Kode) = ?', [$roomCode])
            ->first();

        $packageCode = strtoupper(trim((string) ($validated['PackageCode'] ?? '')));
        $packageNominal = $packageCode !== ''
            ? (float) (DB::table('Package')->whereRaw('RTRIM(Nofak) = ?', [$packageCode])->value('JumlahRes') ?? 0)
            : 0;

        $nominal = $this->normalizeMoney($validated['Nominal'] ?? null);
        $nominal = $nominal > 0 ? $nominal : $packageNominal;
        $tglIn = Carbon::createFromFormat('Y-m-d', $validated['CheckInDate'])->startOfDay();
        $tglOut = Carbon::createFromFormat('Y-m-d', $validated['EstimationOut'])->startOfDay();
        $hari = max($tglIn->diffInDays($tglOut), 1);
        $expiredDate = !empty($validated['ExpiredDate']) ? Carbon::createFromFormat('Y-m-d', $validated['ExpiredDate'])->format('Y-m-d') : null;
        $birthDate = !empty($validated['BirthDate']) ? Carbon::createFromFormat('Y-m-d', $validated['BirthDate'])->format('Y-m-d') : null;
        $breakfast = (int) ($validated['Breakfast'] ?? 0);
        $reservationNumber = trim((string) ($validated['ReservationNumber'] ?? ''));

        return [
            'RegNo' => $regNo,
            'RegNo2' => $regNo . $roomCode,
            'Kode' => $roomCode,
            'Rate1' => (float) ($room->Rate1 ?? 0),
            'Rate2' => (float) ($room->Rate2 ?? 0),
            'Rate3' => (float) ($room->Rate3 ?? 0),
            'Rate4' => (float) ($room->Rate4 ?? 0),
            'Pst' => ' ',
            'Short' => 0,
            'TglIn' => $tglIn->format('Y-m-d'),
            'JamIn' => $this->normalizeTime($validated['CheckInTime']),
            'TglOut' => $tglOut->format('Y-m-d'),
            'JamOut' => '1900-01-01 12:00:00',
            'TglKeluar' => $tglOut->format('Y-m-d'),
            'Guest' => strtoupper(trim((string) $validated['GuestName'])),
            'Guest2' => strtoupper(trim((string) ($validated['GuestName2'] ?? ''))),
            'Guest3' => strtoupper(trim((string) ($validated['Member'] ?? ''))),
            'KTP' => strtoupper(trim((string) ($validated['IdNumber'] ?? ''))),
            'Expired' => $expiredDate,
            'Alamat' => strtoupper(trim((string) ($validated['Address'] ?? ''))),
            'Kelurahan' => strtoupper(trim((string) ($validated['Kelurahan'] ?? ''))),
            'Kecamatan' => strtoupper(trim((string) ($validated['Kecamatan'] ?? ''))),
            'Kota' => strtoupper(trim((string) ($validated['KabCity'] ?? ''))),
            'Propinsi' => strtoupper(trim((string) ($validated['ProvinceCountry'] ?? ''))),
            'Agama' => strtoupper(trim((string) ($validated['Religion'] ?? ''))),
            'TglLahir' => $birthDate,
            'KodeNegara' => strtoupper(trim((string) ($validated['Nationality'] ?? 'INA'))),
            'Person' => (int) $validated['NumberOfPerson'],
            'Usaha' => strtoupper(trim((string) ($validated['Company'] ?? ''))),
            'Hari' => $hari,
            'SpecialRate' => 0,
            'Payment' => strtoupper(trim((string) $validated['PaymentMethod'])),
            'CardNumber' => strtoupper(trim((string) ($validated['CreditCardNumber'] ?? ''))),
            'Remark' => strtoupper(trim((string) ($validated['Remarks'] ?? ''))),
            'Posisi' => strtoupper(trim((string) ($validated['GroupPosition'] ?? ''))),
            'Visa' => null,
            'Purpose' => strtoupper(trim((string) ($validated['Segment'] ?? ''))),
            'SafeDeposit' => !empty($validated['CheckDeposit']) ? 1 : 0,
            'TYPEID' => strtoupper(trim((string) ($validated['TypeOfId'] ?? ''))),
            'INFORMATION' => null,
            'Nominal' => $nominal,
            'PlaceBirth' => strtoupper(trim((string) ($validated['PlaceOfBirth'] ?? ''))),
            'Tipe' => strtoupper(trim((string) $validated['TypeOfCheckIn'])),
            'PlaceIssued' => strtoupper(trim((string) ($validated['ProvinceCountry'] ?? ''))),
            'DateIssued' => $expiredDate,
            'ExtraBed' => 0,
            'StrTglIn' => $tglIn->format('d-m-Y'),
            'StrTglOut' => $tglOut->format('d-m-Y'),
            'Receipt' => $reservationNumber,
            'CreditCard' => strtoupper(trim((string) ($validated['CreditCardNumber'] ?? ''))),
            'Remark2' => null,
            'Disc' => 0,
            'Dispensasi' => 0,
            'Status' => 'IN HOUSE',
            'Phone' => trim((string) ($validated['Phone'] ?? '')),
            'Periksa' => !empty($validated['CheckDeposit']) ? '1' : '0',
            'Package' => $packageCode,
            'Segment' => strtoupper(trim((string) ($validated['Segment'] ?? ''))),
            'Email' => trim((string) ($validated['Email'] ?? '')),
            'Member' => strtoupper(trim((string) ($validated['Member'] ?? ''))),
            'Sales' => strtoupper(trim((string) ($validated['Sales'] ?? ''))),
            'BF' => $breakfast,
            'travel' => strtoupper(trim((string) ($validated['Company'] ?? ''))),
            'ID' => $reservationNumber,
        ];
    }

    private function loadActiveCheckins(string $search)
    {
        $query = DB::table('DATA2')
            ->selectRaw("RTRIM(RegNo) as RegNo, RTRIM(Kode) as Kode, RTRIM(Guest) as Guest, RTRIM(Guest2) as Guest2, RTRIM(Tipe) as Tipe, RTRIM(Payment) as Payment, RTRIM(Segment) as Segment, RTRIM(Package) as Package, RTRIM(Receipt) as Receipt, RTRIM(TYPEID) as TypeOfId, RTRIM(KTP) as KTP, RTRIM(Alamat) as Alamat, RTRIM(Kelurahan) as Kelurahan, RTRIM(Kecamatan) as Kecamatan, RTRIM(Kota) as Kota, RTRIM(Propinsi) as Propinsi, RTRIM(PlaceBirth) as PlaceBirth, RTRIM(Agama) as Agama, RTRIM(KodeNegara) as KodeNegara, RTRIM(Usaha) as Usaha, RTRIM(CardNumber) as CardNumber, RTRIM(Remark) as Remark, RTRIM(Posisi) as Posisi, RTRIM(Phone) as Phone, RTRIM(Email) as Email, RTRIM(Member) as Member, RTRIM(Sales) as Sales, TglIn, JamIn, TglOut, TglLahir, Expired, Person, BF, Nominal, SafeDeposit")
            ->where('Pst', '=', ' ')
            ->whereRaw("RTRIM(Kode) <> '999'");

        if ($search !== '') {
            $keyword = '%' . strtoupper($search) . '%';
            $query->where(function ($builder) use ($keyword) {
                $builder->whereRaw('UPPER(RTRIM(RegNo)) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(Kode)) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(Guest)) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(RTRIM(Package)) LIKE ?', [$keyword]);
            });
        }

        return $query
            ->orderByDesc('TglIn')
            ->orderByDesc('RegNo')
            ->get()
            ->map(function ($row) {
                $row->check_in_date = !empty($row->TglIn) ? Carbon::parse($row->TglIn)->format('d-m-Y') : '';
                $row->check_out_date = !empty($row->TglOut) ? Carbon::parse($row->TglOut)->format('d-m-Y') : '';
                $row->birth_date = !empty($row->TglLahir) ? Carbon::parse($row->TglLahir)->format('d-m-Y') : '';
                $row->expired_date = !empty($row->Expired) ? Carbon::parse($row->Expired)->format('d-m-Y') : '';
                $row->check_in_date_iso = !empty($row->TglIn) ? Carbon::parse($row->TglIn)->format('Y-m-d') : '';
                $row->check_out_date_iso = !empty($row->TglOut) ? Carbon::parse($row->TglOut)->format('Y-m-d') : '';
                $row->birth_date_iso = !empty($row->TglLahir) ? Carbon::parse($row->TglLahir)->format('Y-m-d') : '';
                $row->expired_date_iso = !empty($row->Expired) ? Carbon::parse($row->Expired)->format('Y-m-d') : '';
                $row->check_in_time = $this->displayTime($row->JamIn ?? null);
                $row->nominal_display = number_format((float) ($row->Nominal ?? 0), 0, ',', '.');
                $row->record_json = json_encode([
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

    private function loadRoomOptions(): array
    {
        $activeRooms = DB::table('DATA2')
            ->where('Pst', '=', ' ')
            ->whereRaw("RTRIM(Kode) <> '999'")
            ->pluck(DB::raw('RTRIM(Kode)'))
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
                $occupied = $activeRooms->has($kode);

                return [
                    'kode' => $kode,
                    'kelas' => trim((string) ($room->Nama ?? '')),
                    'status' => trim((string) ($room->Status ?? '')),
                    'status2' => trim((string) ($room->Status2 ?? '')),
                    'available' => !$occupied && !in_array($status, $blockedStatus, true),
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

    private function roomExists(string $roomCode): bool
    {
        return DB::table('ROOM')->whereRaw('RTRIM(Kode) = ?', [$roomCode])->exists();
    }

    private function roomHasActiveCheckin(string $roomCode, ?string $ignoreRegNo = null): bool
    {
        $query = DB::table('DATA2')
            ->where('Pst', '=', ' ')
            ->whereRaw('RTRIM(Kode) = ?', [$roomCode]);

        if ($ignoreRegNo) {
            $query->whereRaw('RTRIM(RegNo) <> ?', [$ignoreRegNo]);
        }

        return $query->exists();
    }

    private function markRoomOccupiedClean(string $roomCode): void
    {
        DB::table('ROOM')
            ->whereRaw('RTRIM(Kode) = ?', [$roomCode])
            ->update([
                'Status2' => 'Occupied Clean',
            ]);
    }

    private function clearRoomOccupiedMarker(string $roomCode, ?string $ignoreRegNo = null): void
    {
        if ($roomCode === '' || $this->roomHasActiveCheckin($roomCode, $ignoreRegNo)) {
            return;
        }

        DB::table('ROOM')
            ->whereRaw('RTRIM(Kode) = ?', [$roomCode])
            ->update([
                'Status2' => null,
            ]);
    }

    private function generateNextRegNo(): string
    {
        $prefix = Carbon::now()->format('Ym') . '0088';
        $rows = DB::table('DATA2')
            ->selectRaw('RTRIM(RegNo) as RegNo')
            ->whereRaw('RTRIM(RegNo) LIKE ?', [$prefix . '%'])
            ->get();

        $maxSequence = $rows->map(function ($row) {
            $regNo = trim((string) ($row->RegNo ?? ''));
            $suffix = substr($regNo, -4);

            return ctype_digit($suffix) ? (int) $suffix : 0;
        })->max() ?? 0;

        return $prefix . str_pad((string) ($maxSequence + 1), 4, '0', STR_PAD_LEFT);
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
            return '1900-01-01 00:00:00';
        }

        foreach (['H:i:s', 'H:i', 'g:i A', 'g:i:s A'] as $format) {
            try {
                return Carbon::createFromFormat($format, strtoupper($raw))->format('1900-01-01 H:i:s');
            } catch (\Throwable $exception) {
            }
        }

        try {
            return Carbon::parse($raw)->format('1900-01-01 H:i:s');
        } catch (\Throwable $exception) {
            return '1900-01-01 00:00:00';
        }
    }

    private function displayTime($value): string
    {
        if (empty($value)) {
            return '';
        }

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Throwable $exception) {
            return trim((string) $value);
        }
    }
}



