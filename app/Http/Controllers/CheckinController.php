<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Symfony\Component\Process\Process;

class CheckinController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $records = $this->loadActiveCheckins($search, 80);
        $checkins = $this->paginateCollection($records, 10, $request);
        $rooms = $this->loadRoomOptions();
        $packages = $this->loadPackageOptions();
        $typeOptions = $this->loadTypeOptions();
        $idTypeOptions = $this->loadIdTypeOptions();
        $defaultIdType = in_array('KTP', $idTypeOptions, true) ? 'KTP' : ($idTypeOptions[0] ?? '');
        $reservationNumberOptions = $this->loadReservationNumberOptions();
        $companyOptions = $this->loadCompanyOptions();
        $provinceOptions = $this->loadProvinceOptions();
        $groupPositionOptions = $this->loadGroupPositionOptions();
        $salesOptions = $this->loadSalesOptions();
        $checkInIso = old('CheckInDate', now()->format('Y-m-d'));
        $checkOutIso = old('EstimationOut', now()->addDay()->format('Y-m-d'));
        $birthIso = old('BirthDate', '');
        $expiredIso = old('ExpiredDate', '');
        $roomCodeList = old('RoomCodeList', []);
        $packageCodeList = old('PackageCodeList', []);
        $nominalList = old('NominalList', []);
        $breakfastList = old('BreakfastList', []);
        $detailKeyList = old('DetailKeyList', []);
        $sameAsLeaderList = old('SameAsLeaderList', []);
        $roomGroupPositionList = old('RoomGroupPositionList', []);
        $roomGuestNameList = old('RoomGuestNameList', []);
        $roomGuestIdTypeList = old('RoomGuestIdTypeList', []);
        $roomGuestIdNumberList = old('RoomGuestIdNumberList', []);
        $roomGuestBirthDateList = old('RoomGuestBirthDateList', []);
        $roomGuestPhoneList = old('RoomGuestPhoneList', []);
        $roomGuestEmailList = old('RoomGuestEmailList', []);
        $roomGuestAddressList = old('RoomGuestAddressList', []);
        $roomGuestNationalityList = old('RoomGuestNationalityList', []);
        $oldAdditionalRoomRows = collect($roomCodeList)
            ->values()
            ->slice(1)
            ->map(function ($roomCode, $index) use (
                $packageCodeList,
                $nominalList,
                $breakfastList,
                $detailKeyList,
                $sameAsLeaderList,
                $roomGroupPositionList,
                $roomGuestNameList,
                $roomGuestIdTypeList,
                $roomGuestIdNumberList,
                $roomGuestBirthDateList,
                $roomGuestPhoneList,
                $roomGuestEmailList,
                $roomGuestAddressList,
                $roomGuestNationalityList
            ) {
                $rowIndex = $index + 1;

                return [
                    'detailKey' => (string) ($detailKeyList[$rowIndex] ?? ''),
                    'roomCode' => (string) ($roomCode ?? ''),
                    'packageCode' => (string) ($packageCodeList[$rowIndex] ?? ''),
                    'nominal' => (string) ($nominalList[$rowIndex] ?? ''),
                    'breakfast' => (int) ($breakfastList[$rowIndex] ?? 0),
                    'groupPosition' => (string) ($roomGroupPositionList[$rowIndex] ?? ''),
                    'sameAsLeader' => (string) ($sameAsLeaderList[$rowIndex] ?? '1') !== '0',
                    'guestName' => (string) ($roomGuestNameList[$rowIndex] ?? ''),
                    'guestIdType' => (string) ($roomGuestIdTypeList[$rowIndex] ?? 'KTP'),
                    'guestIdNumber' => (string) ($roomGuestIdNumberList[$rowIndex] ?? ''),
                    'guestBirthDate' => (string) ($roomGuestBirthDateList[$rowIndex] ?? ''),
                    'guestPhone' => (string) ($roomGuestPhoneList[$rowIndex] ?? ''),
                    'guestEmail' => (string) ($roomGuestEmailList[$rowIndex] ?? ''),
                    'guestAddress' => (string) ($roomGuestAddressList[$rowIndex] ?? ''),
                    'guestNationality' => (string) ($roomGuestNationalityList[$rowIndex] ?? 'INA'),
                ];
            })
            ->filter(fn (array $detail) => collect($detail)->contains(fn ($value) => $value !== '' && $value !== 0))
            ->values()
            ->all();

        $viewData = [
            'checkins' => $checkins,
            'search' => $search,
            'nextRegNo' => $this->generateNextRegNo(),
            'rooms' => $rooms,
            'packages' => $packages,
            'reservationNumberOptions' => $reservationNumberOptions,
            'companyOptions' => $companyOptions,
            'provinceOptions' => $provinceOptions,
            'groupPositionOptions' => $groupPositionOptions,
            'salesOptions' => $salesOptions,
            'summary' => [
                'active' => $this->countActiveCheckins($search),
                'rooms_ready' => collect($rooms)->where('available', true)->count(),
                'packages' => count($packages),
            ],
            'typeOptions' => $typeOptions,
            'defaultTypeOfCheckIn' => $typeOptions[0] ?? '',
            'paymentOptions' => ['CASH', 'CARD', 'OTA', 'COMPANY', 'TRAVEL', 'COMPLIMENT'],
            'segmentOptions' => ['DIRECT', 'TRAVEL', 'OTA', 'CORPORATE', 'GROUP'],
            'religionOptions' => ['ISLAM', 'KRISTEN', 'KATOLIK', 'HINDU', 'BUDDHA', 'KONGHUCU'],
            'nationalityOptions' => ['INA', 'MAL', 'SGP', 'AUS', 'JPN', 'KOR', 'USA'],
            'idTypeOptions' => $idTypeOptions,
            'defaultIdType' => $defaultIdType,
            'checkInIso' => $checkInIso,
            'checkOutIso' => $checkOutIso,
            'birthIso' => $birthIso,
            'expiredIso' => $expiredIso,
            'primaryGroupPosition' => (string) ($roomGroupPositionList[0] ?? old('GroupPosition', '')),
            'firstDetailKey' => (string) ($detailKeyList[0] ?? ''),
            'firstRoomCode' => (string) ($roomCodeList[0] ?? ''),
            'firstPackageCode' => (string) ($packageCodeList[0] ?? ''),
            'firstNominal' => (string) ($nominalList[0] ?? ''),
            'firstBreakfast' => (int) ($breakfastList[0] ?? 0),
            'oldAdditionalRoomRows' => $oldAdditionalRoomRows,
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
                'company' => $viewData['companyOptions'],
                'province' => $viewData['provinceOptions'],
                'group_position' => $viewData['groupPositionOptions'],
                'sales' => $viewData['salesOptions'],
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

    public function scanKtp(Request $request)
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'max:10240'],
        ]);

        if (!$this->paddleOcrConfigured() && !$this->googleDocumentAiConfigured() && !$this->openAiKtpCorrectionConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'PaddleOCR lokal, Google Document AI, dan AI OCR belum dikonfigurasi.',
            ], 503);
        }

        try {
            if ($this->paddleOcrConfigured()) {
                $rawText = $this->scanKtpRawWithLocalPaddleOcr($validated['image']);
                $draft = $this->buildKtpRegexDraft($rawText);
                $parsed = $draft;
                $provider = 'paddleocr_local';
                $correctionProvider = null;

                if ($this->openAiKtpCorrectionConfigured()) {
                    try {
                        $parsed = $this->correctKtpDraftWithOpenAi($validated['image'], $rawText, $draft);
                        $correctionProvider = 'openai';
                    } catch (\Throwable $exception) {
                        report($exception);
                    }
                }
            } elseif ($this->googleDocumentAiConfigured()) {
                $rawText = $this->scanKtpRawWithDocumentAi($validated['image']);
                $draft = $this->buildKtpRegexDraft($rawText);
                $parsed = $draft;
                $provider = 'google_document_ai';
                $correctionProvider = null;

                if ($this->openAiKtpCorrectionConfigured()) {
                    try {
                        $parsed = $this->correctKtpDraftWithOpenAi($validated['image'], $rawText, $draft);
                        $correctionProvider = 'openai';
                    } catch (\Throwable $exception) {
                        report($exception);
                    }
                }
            } else {
                $rawText = '';
                $draft = null;
                $parsed = $this->extractKtpWithOpenAiDirect($validated['image']);
                $provider = 'openai_vision';
                $correctionProvider = null;
            }

            return response()->json([
                'success' => true,
                'message' => 'KTP scanned successfully.',
                'data' => [
                    'provider' => $provider,
                    'correction_provider' => $correctionProvider,
                    'draft' => $draft,
                    'parsed' => $parsed,
                ],
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            [$message, $status] = $this->resolveKtpScanException($exception);

            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }
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

            $this->clearRoomOccupiedMarker($detail->Kode, [$detail->RegNo2]);
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
        $checkInType = strtoupper(trim((string) ($validated['TypeOfCheckIn'] ?? '')));
        $isGroupCheckIn = str_contains($checkInType, 'GROUP');
        $currentDetail = $currentRegNo2 ? $this->findDetailByKey($currentRegNo2) : null;
        $existingDetails = $currentDetail ? $this->findDetailsByRegNo($currentDetail->RegNo) : collect();
        $detailRows = $this->collectRoomDetails($validated);
        $paymentMethod = strtoupper(trim((string) ($validated['PaymentMethod'] ?? '')));

        if ($regNo === '') {
            return $this->respondError($request, 'Reg number wajib diisi.');
        }

        if (empty($detailRows)) {
            return $this->respondError($request, 'Minimal satu room detail harus diisi.');
        }

        if ($currentRegNo2 && !$currentDetail) {
            return $this->respondError($request, 'Detail check in yang akan diubah tidak ditemukan.', 404, [], '/checkin', false);
        }

        $provinceCountry = trim((string) ($validated['ProvinceCountry'] ?? ''));
        $provinceCountrySource = strtolower(trim((string) ($validated['ProvinceCountrySource'] ?? 'manual')));

        if ($provinceCountry === '') {
            return $this->respondError($request, 'Province / Country tidak boleh kosong.');
        }

        if ($provinceCountrySource !== 'scan' && !$this->isAllowedProvince($provinceCountry)) {
            return $this->respondError($request, 'Province / Country manual harus dipilih dari daftar.');
        }

        if (trim((string) ($validated['Phone'] ?? '')) === '') {
            return $this->respondError($request, 'Telephone number tidak boleh kosong.');
        }

        if ($this->paymentRequiresCompany($paymentMethod) && trim((string) ($validated['Company'] ?? '')) === '') {
            return $this->respondError($request, 'Company wajib diisi karena payment method menggunakan OTA / Company / Travel.');
        }

        if ($this->paymentRequiresCreditCard($paymentMethod) && trim((string) ($validated['CreditCardNumber'] ?? '')) === '') {
            return $this->respondError($request, 'Credit Card Number tidak boleh kosong untuk payment kartu.');
        }

        $existingDifferentType = $existingDetails->first(function ($detail) use ($isGroupCheckIn) {
            $existingType = strtoupper(trim((string) ($detail->Tipe ?? '')));
            return str_contains($existingType, 'GROUP') !== $isGroupCheckIn;
        });

        if ($existingDifferentType) {
            return $this->respondError(
                $request,
                'Register ' . $regNo . ' room ' . trim((string) ($existingDifferentType->Kode ?? '')) .
                ' sudah check-in dengan type ' . trim((string) ($existingDifferentType->Tipe ?? '')) . '.'
            );
        }

        if ($isGroupCheckIn) {
            $groupPositionOptions = collect($this->loadGroupPositionOptions())
                ->map(fn ($value) => strtoupper(trim((string) $value)))
                ->filter()
                ->values();
            $leaderIndex = collect($detailRows)->search(function (array $detailRow) {
                return strtoupper(trim((string) ($detailRow['room_position'] ?? ''))) === 'LEADER';
            });

            if ($leaderIndex === false) {
                $leaderIndex = 0;
                $detailRows[$leaderIndex]['room_position'] = 'LEADER';
            }

            $leaderCount = collect($detailRows)->filter(function (array $detailRow) {
                return strtoupper(trim((string) ($detailRow['room_position'] ?? ''))) === 'LEADER';
            })->count();

            if ($leaderCount > 1) {
                return $this->respondError($request, 'Only one LEADER is allowed in one group registration.');
            }

            foreach ($detailRows as $index => $detailRow) {
                $roomPosition = strtoupper(trim((string) ($detailRow['room_position'] ?? '')));

                if ($roomPosition === '') {
                    if ($index === $leaderIndex) {
                        $detailRows[$index]['room_position'] = 'LEADER';
                        continue;
                    }

                    if ($groupPositionOptions->contains('SUB')) {
                        $detailRows[$index]['room_position'] = 'SUB';
                        continue;
                    }

                    return $this->respondError($request, 'Group position wajib dipilih untuk setiap room pada check-in group.');
                }
            }
        }

        $roomTracker = [];
        $existingDetailMap = $existingDetails->keyBy(fn ($detail) => trim((string) ($detail->Kode ?? '')));
        $existingRegNo2s = $existingDetails->pluck('RegNo2')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values()
            ->all();

        foreach ($detailRows as $index => &$detailRow) {
            $roomCode = $detailRow['room_code'];

            if (!$this->roomExists($roomCode)) {
                return $this->respondError($request, 'Kode room ' . $roomCode . ' tidak ditemukan.');
            }

            if (isset($roomTracker[$roomCode])) {
                return $this->respondError($request, 'Room ' . $roomCode . ' muncul lebih dari satu kali di form yang sama.');
            }

            if (!$detailRow['same_as_leader'] && $detailRow['guest_name'] === '') {
                return $this->respondError($request, 'Guest name untuk room ' . $roomCode . ' wajib diisi jika tidak mengikuti leader.');
            }

            if ($detailRow['package_code'] === '') {
                return $this->respondError($request, 'Package wajib diisi untuk room ' . $roomCode . '.');
            }

            $roomTracker[$roomCode] = true;
            if ($this->roomHasActiveCheckin($roomCode, $existingRegNo2s)) {
                return $this->respondError($request, 'Room ' . $roomCode . ' masih dipakai guest aktif.');
            }

            $matchedExistingDetail = $existingDetailMap->get($roomCode);
            $detailRow['regno2'] = $matchedExistingDetail
                ? trim((string) ($matchedExistingDetail->RegNo2 ?? ''))
                : $this->generateRegNo2($regNo, $roomCode);
        }
        unset($detailRow);

        $primaryRoomCode = $detailRows[0]['room_code'];
        $dataPayload = $this->buildDataPayload($validated, $regNo, $primaryRoomCode);
        $nextData2Id = $this->nextLegacyId('DATA2');
        $detailIdColumn = $this->legacyIdColumn('DATA2');
        $detailPayloads = collect($detailRows)->values()->map(function ($detailRow, $index) use ($validated, $regNo, $nextData2Id, $detailIdColumn) {
            $payload = $this->buildData2Payload($validated, $regNo, $detailRow);

            if ($detailIdColumn && $nextData2Id !== null) {
                $payload[$detailIdColumn] = $nextData2Id + $index;
            }

            return $payload;
        });
        $nextDataMoveId = $this->nextLegacyId('DataMove');
        $dataMoveIdColumn = $this->legacyIdColumn('DataMove');
        $movePayloads = $detailPayloads->values()->map(function ($detailPayload, $index) use ($nextDataMoveId, $dataMoveIdColumn) {
            $payload = $this->buildDataMovePayload($detailPayload);

            if ($dataMoveIdColumn && $nextDataMoveId !== null) {
                $payload[$dataMoveIdColumn] = $nextDataMoveId + $index;
            }

            return $payload;
        })->values();
        $depositPayloads = $detailPayloads->map(fn($detailPayload) => $this->buildDepositPayload($regNo, $detailPayload['Kode'], $detailPayload['TglIn']))->values();

        DB::transaction(function () use ($currentDetail, $existingDetails, $regNo, $dataPayload, $detailPayloads, $movePayloads, $depositPayloads) {
            $this->upsertHeader($regNo, $dataPayload);

            if ($currentDetail) {
                $existingRegNo2s = $existingDetails->pluck('RegNo2')
                    ->map(fn ($value) => trim((string) $value))
                    ->filter()
                    ->values()
                    ->all();
                $existingRooms = $existingDetails->pluck('Kode')
                    ->map(fn ($value) => trim((string) $value))
                    ->filter()
                    ->values()
                    ->all();

                if (!empty($existingRegNo2s)) {
                    DB::table('DATA2')->whereIn(DB::raw('RTRIM(RegNo2)'), $existingRegNo2s)->delete();
                    DB::table('DataMove')->whereIn(DB::raw('RTRIM(RegNo2)'), $existingRegNo2s)->delete();
                }

                if (!empty($existingRooms)) {
                    DB::table('Deposit')
                        ->whereRaw('RTRIM(RegNo) = ?', [$currentDetail->RegNo])
                        ->whereIn(DB::raw('RTRIM(Kode)'), $existingRooms)
                        ->delete();
                }

                foreach ($existingRooms as $existingRoomCode) {
                    $this->clearRoomOccupiedMarker($existingRoomCode);
                }
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
        $groupPositionOptions = $this->loadGroupPositionOptions();
        $isGroupCheckIn = str_contains(
            strtoupper(trim((string) $request->input('TypeOfCheckIn', ''))),
            'GROUP'
        );

        $validated = $request->validate([
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
            'ProvinceCountrySource' => ['nullable', 'string', Rule::in(['manual', 'scan'])],
            'TypeOfId' => ['required', 'string', 'max:30', Rule::in($this->loadIdTypeOptions())],
            'IdNumber' => 'nullable|string|max:60',
            'ExpiredDate' => 'nullable|date_format:Y-m-d',
            'TypeOfCheckIn' => 'required|string|max:40',
            'PlaceOfBirth' => 'nullable|string|max:60',
            'BirthDate' => 'nullable|date_format:Y-m-d',
            'Religion' => 'nullable|string|max:30',
            'Nationality' => 'nullable|string|max:10',
            'NumberOfPerson' => 'required|integer|min:1|max:20',
            'EstimationOut' => 'required|date_format:Y-m-d|after_or_equal:CheckInDate',
            'PaymentMethod' => 'required|string|max:30',
            'Company' => 'nullable|string|max:80',
            'Occupation' => 'nullable|string|max:80',
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
            'SameAsLeaderList' => 'nullable|array',
            'SameAsLeaderList.*' => 'nullable|string|max:5',
            'PackageCodeList' => 'nullable|array',
            'PackageCodeList.*' => 'nullable|string|max:30',
            'NominalList' => 'nullable|array',
            'NominalList.*' => 'nullable|string|max:30',
            'BreakfastList' => 'nullable|array',
            'BreakfastList.*' => 'nullable|integer|min:0|max:20',
            'DetailKeyList' => 'nullable|array',
            'DetailKeyList.*' => 'nullable|string|max:60',
            'RoomGroupPositionList' => 'nullable|array',
            'RoomGroupPositionList.*' => ['nullable', 'string', 'max:60', Rule::in($groupPositionOptions)],
            'RoomGuestNameList' => 'nullable|array',
            'RoomGuestNameList.*' => 'nullable|string|max:120',
            'RoomGuestIdTypeList' => 'nullable|array',
            'RoomGuestIdTypeList.*' => ['nullable', 'string', 'max:30', Rule::in($this->loadIdTypeOptions())],
            'RoomGuestIdNumberList' => 'nullable|array',
            'RoomGuestIdNumberList.*' => 'nullable|string|max:60',
            'RoomGuestBirthDateList' => 'nullable|array',
            'RoomGuestBirthDateList.*' => 'nullable|date_format:Y-m-d',
            'RoomGuestPhoneList' => 'nullable|array',
            'RoomGuestPhoneList.*' => 'nullable|string|max:40',
            'RoomGuestEmailList' => 'nullable|array',
            'RoomGuestEmailList.*' => 'nullable|string|max:100',
            'RoomGuestAddressList' => 'nullable|array',
            'RoomGuestAddressList.*' => 'nullable|string|max:150',
            'RoomGuestNationalityList' => 'nullable|array',
            'RoomGuestNationalityList.*' => 'nullable|string|max:10',
        ]);

        $validated['RoomGroupPositionList'] = collect($validated['RoomGroupPositionList'] ?? [])
            ->map(function ($value) use ($isGroupCheckIn) {
                if (!$isGroupCheckIn) {
                    return '';
                }

                return strtoupper(trim((string) $value));
            })
            ->sortKeys()
            ->all();
        $validated['Religion'] = trim((string) ($validated['Religion'] ?? '')) !== ''
            ? trim((string) $validated['Religion'])
            : 'ISLAM';
        $validated['Nationality'] = trim((string) ($validated['Nationality'] ?? '')) !== ''
            ? trim((string) $validated['Nationality'])
            : 'INA';

        return $validated;
    }

    private function collectRoomDetails(array $validated): array
    {
        $roomCodes = $validated['RoomCodeList'] ?? [];
        $packageCodes = $validated['PackageCodeList'] ?? [];
        $nominals = $validated['NominalList'] ?? [];
        $breakfasts = $validated['BreakfastList'] ?? [];
        $detailKeys = $validated['DetailKeyList'] ?? [];
        $sameAsLeaderList = $validated['SameAsLeaderList'] ?? [];
        $roomGroupPositionList = $validated['RoomGroupPositionList'] ?? [];
        $roomGuestNameList = $validated['RoomGuestNameList'] ?? [];
        $roomGuestIdTypeList = $validated['RoomGuestIdTypeList'] ?? [];
        $roomGuestIdNumberList = $validated['RoomGuestIdNumberList'] ?? [];
        $roomGuestBirthDateList = $validated['RoomGuestBirthDateList'] ?? [];
        $roomGuestPhoneList = $validated['RoomGuestPhoneList'] ?? [];
        $roomGuestEmailList = $validated['RoomGuestEmailList'] ?? [];
        $roomGuestAddressList = $validated['RoomGuestAddressList'] ?? [];
        $roomGuestNationalityList = $validated['RoomGuestNationalityList'] ?? [];
        $totalRows = max(
            count($roomCodes),
            count($packageCodes),
            count($nominals),
            count($breakfasts),
            count($detailKeys),
            count($sameAsLeaderList),
            count($roomGroupPositionList),
            count($roomGuestNameList),
            count($roomGuestIdTypeList),
            count($roomGuestIdNumberList),
            count($roomGuestBirthDateList),
            count($roomGuestPhoneList),
            count($roomGuestEmailList),
            count($roomGuestAddressList),
            count($roomGuestNationalityList)
        );
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
                'room_position' => strtoupper(trim((string) ($roomGroupPositionList[$index] ?? ''))),
                'same_as_leader' => $index === 0 ? true : (string) ($sameAsLeaderList[$index] ?? '1') !== '0',
                'guest_name' => trim((string) ($roomGuestNameList[$index] ?? '')),
                'guest_id_type' => strtoupper(trim((string) ($roomGuestIdTypeList[$index] ?? 'KTP'))),
                'guest_id_number' => trim((string) ($roomGuestIdNumberList[$index] ?? '')),
                'guest_birth_date' => trim((string) ($roomGuestBirthDateList[$index] ?? '')),
                'guest_phone' => trim((string) ($roomGuestPhoneList[$index] ?? '')),
                'guest_email' => trim((string) ($roomGuestEmailList[$index] ?? '')),
                'guest_address' => trim((string) ($roomGuestAddressList[$index] ?? '')),
                'guest_nationality' => strtoupper(trim((string) ($roomGuestNationalityList[$index] ?? 'INA'))),
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
        $occupation = strtoupper(trim((string) ($validated['Occupation'] ?? '')));
        $creditCard = strtoupper(trim((string) ($validated['CreditCardNumber'] ?? '')));
        $reservationNumber = trim((string) ($validated['ReservationNumber'] ?? ''));
        $sameAsLeader = (bool) ($detailRow['same_as_leader'] ?? true);
        $guestBirthDate = $sameAsLeader
            ? $birthDate
            : (!empty($detailRow['guest_birth_date'])
                ? Carbon::createFromFormat('Y-m-d', $detailRow['guest_birth_date'])->format('Y-m-d')
                : null);
        $guestName = $sameAsLeader
            ? strtoupper(trim((string) $validated['GuestName']))
            : strtoupper(trim((string) ($detailRow['guest_name'] ?? '')));
        $guestIdType = $sameAsLeader
            ? strtoupper(trim((string) ($validated['TypeOfId'] ?? 'KTP')))
            : strtoupper(trim((string) ($detailRow['guest_id_type'] ?? 'KTP')));
        $guestIdNumber = $sameAsLeader
            ? strtoupper(trim((string) ($validated['IdNumber'] ?? '')))
            : strtoupper(trim((string) ($detailRow['guest_id_number'] ?? '')));
        $guestAddress = $sameAsLeader
            ? strtoupper(trim((string) ($validated['Address'] ?? '')))
            : strtoupper(trim((string) ($detailRow['guest_address'] ?? '')));
        $guestPhone = $sameAsLeader
            ? trim((string) ($validated['Phone'] ?? ''))
            : trim((string) ($detailRow['guest_phone'] ?? ''));
        $guestEmail = $sameAsLeader
            ? trim((string) ($validated['Email'] ?? ''))
            : trim((string) ($detailRow['guest_email'] ?? ''));
        $guestNationality = $sameAsLeader
            ? strtoupper(trim((string) ($validated['Nationality'] ?? 'INA')))
            : strtoupper(trim((string) ($detailRow['guest_nationality'] ?? 'INA')));
        $guestReligion = $sameAsLeader ? strtoupper(trim((string) ($validated['Religion'] ?? ''))) : '';
        $guestPlaceBirth = $sameAsLeader ? strtoupper(trim((string) ($validated['PlaceOfBirth'] ?? ''))) : '';
        $guestKelurahan = $sameAsLeader ? strtoupper(trim((string) ($validated['Kelurahan'] ?? ''))) : '';
        $guestKecamatan = $sameAsLeader ? strtoupper(trim((string) ($validated['Kecamatan'] ?? ''))) : '';
        $guestKota = $sameAsLeader ? strtoupper(trim((string) ($validated['KabCity'] ?? ''))) : '';
        $guestPropinsi = $sameAsLeader ? strtoupper(trim((string) ($validated['ProvinceCountry'] ?? ''))) : '';

        return [
            'RegNo' => $regNo,
            'TglIn' => $checkInDate,
            'JamIn' => $jamIn,
            'TglKeluar' => $checkOutDate,
            'TglOut' => $checkOutDate,
            'TglLahir' => $guestBirthDate,
            'Guest' => $guestName,
            'KTP' => $guestIdNumber,
            'Alamat' => $guestAddress,
            'Agama' => $guestReligion,
            'KodeNegara' => $guestNationality,
            'Person' => (int) $validated['NumberOfPerson'],
            'Usaha' => $company,
            'Profesi' => $occupation,
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
            'TypeId' => $guestIdType,
            'Information' => '',
            'Kelurahan' => $guestKelurahan,
            'Kecamatan' => $guestKecamatan,
            'Kota' => $guestKota,
            'Propinsi' => $guestPropinsi,
            'Nominal' => $nominal,
            'Tipe' => strtoupper(trim((string) $validated['TypeOfCheckIn'])),
            'PlaceBirth' => $guestPlaceBirth,
            'PlaceIssued' => $guestPropinsi,
            'DateIssued' => $expiredDate,
            'ExtraBed' => 0,
            'strTglIn' => $this->buildStrTgl($checkInDate, $jamIn),
            'Receipt' => $reservationNumber,
            'CreditCard' => $creditCard,
            'Remark2' => '',
            'Disc' => 0,
            'RegNo2' => $regNo2,
            'Posisi' => strtoupper(trim((string) ($detailRow['room_position'] ?? ''))),
            'Phone' => $guestPhone,
            'Periksa' => $checkDeposit,
            'Package' => $packageCode,
            'Segment' => $segment,
            'Plot' => 0,
            'Email' => $guestEmail,
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
            'Rate1' => $detailPayload['Rate1'],
            'Rate2' => $detailPayload['Rate2'],
            'Kode' => $detailPayload['Kode'],
            'TglMove' => $detailPayload['TglIn'],
            'JamMove' => $detailPayload['JamIn'],
            'NoUrut' => null,
            'RegNo2' => $detailPayload['RegNo2'],
            'Bendera' => null,
            'Rate3' => null,
            'Rate4' => null,
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

    private function paymentRequiresCompany(string $paymentMethod): bool
    {
        return in_array($paymentMethod, ['OTA', 'COMPANY', 'TRAVEL'], true);
    }

    private function paymentRequiresCreditCard(string $paymentMethod): bool
    {
        return $paymentMethod === 'CARD';
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
        $todayIso = Carbon::today()->format('Y-m-d');
        $query = DB::table('DATA2')
            ->selectRaw("$idSelect, Pst, RTRIM(RegNo) as RegNo, RTRIM(RegNo2) as RegNo2, RTRIM(Kode) as Kode, RTRIM(Guest) as Guest, RTRIM(Guest2) as Guest2, RTRIM(Tipe) as Tipe, RTRIM(Payment) as Payment, RTRIM(Segment) as Segment, RTRIM(Package) as Package, RTRIM(Receipt) as Receipt, RTRIM(TypeId) as TypeOfId, RTRIM(KTP) as KTP, RTRIM(Alamat) as Alamat, RTRIM(Kelurahan) as Kelurahan, RTRIM(Kecamatan) as Kecamatan, RTRIM(Kota) as Kota, RTRIM(Propinsi) as Propinsi, RTRIM(PlaceBirth) as PlaceBirth, RTRIM(Agama) as Agama, RTRIM(KodeNegara) as KodeNegara, RTRIM(Usaha) as Usaha, RTRIM(Profesi) as Profesi, RTRIM(CardNumber) as CardNumber, RTRIM(Remark) as Remark, RTRIM(Posisi) as Posisi, RTRIM(Phone) as Phone, RTRIM(Email) as Email, RTRIM(Member) as Member, RTRIM(Sales) as Sales, TglIn, JamIn, JamOut, TglKeluar, TglLahir, Expired, Person, BF, Nominal, SafeDeposit")
            ->where(function ($q) use ($todayIso) {
                $q->where('Pst', '=', ' ')
                    ->orWhere(function ($q2) use ($todayIso) {
                        $q2->where('Pst', '=', '*')
                            ->whereDate('TglKeluar', '=', $todayIso);
                    });
            })
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

        $rows = $query
            ->orderByDesc('TglIn')
            ->orderByDesc('RegNo2')
            ->limit($limit)
            ->get();

        $groupedRows = $rows->groupBy(fn ($row) => trim((string) ($row->RegNo ?? '')));

        return $rows->map(function ($row) use ($groupedRows) {
                $registrationRows = $groupedRows->get(trim((string) ($row->RegNo ?? '')), collect())->values();
                $primaryRow = $registrationRows->first() ?: $row;
                $row->check_in_date = !empty($row->TglIn) ? Carbon::parse($row->TglIn)->format('d-m-Y') : '';
                $row->check_out_date = !empty($row->TglKeluar) ? Carbon::parse($row->TglKeluar)->format('d-m-Y') : '';
                $row->birth_date_iso = !empty($row->TglLahir) ? Carbon::parse($row->TglLahir)->format('Y-m-d') : '';
                $row->expired_date_iso = !empty($row->Expired) ? Carbon::parse($row->Expired)->format('Y-m-d') : '';
                $row->check_in_date_iso = !empty($row->TglIn) ? Carbon::parse($row->TglIn)->format('Y-m-d') : '';
                $row->check_out_date_iso = !empty($row->TglKeluar) ? Carbon::parse($row->TglKeluar)->format('Y-m-d') : '';
                $row->check_in_time = $this->displayTime($row->JamIn ?? null);
                $row->nominal_display = number_format((float) ($row->Nominal ?? 0), 0, ',', '.');
                $row->guest_status = trim((string) ($row->Pst ?? '')) === '*' ? 'CHECKOUT' : 'STAY';
                $additionalRooms = $registrationRows->slice(1)->map(function ($detail) use ($primaryRow) {
                    $detailBirthDate = !empty($detail->TglLahir) ? Carbon::parse($detail->TglLahir)->format('Y-m-d') : '';
                    $sameAsLeader = strtoupper(trim((string) ($detail->Guest ?? ''))) === strtoupper(trim((string) ($primaryRow->Guest ?? '')))
                        && strtoupper(trim((string) ($detail->KTP ?? ''))) === strtoupper(trim((string) ($primaryRow->KTP ?? '')))
                        && strtoupper(trim((string) ($detail->Alamat ?? ''))) === strtoupper(trim((string) ($primaryRow->Alamat ?? '')))
                        && trim((string) ($detail->Phone ?? '')) === trim((string) ($primaryRow->Phone ?? ''))
                        && trim((string) ($detail->Email ?? '')) === trim((string) ($primaryRow->Email ?? ''))
                        && strtoupper(trim((string) ($detail->KodeNegara ?? ''))) === strtoupper(trim((string) ($primaryRow->KodeNegara ?? '')))
                        && strtoupper(trim((string) ($detail->TypeOfId ?? ''))) === strtoupper(trim((string) ($primaryRow->TypeOfId ?? '')))
                        && $detailBirthDate === (!empty($primaryRow->TglLahir) ? Carbon::parse($primaryRow->TglLahir)->format('Y-m-d') : '');

                    return [
                        'detailKey' => trim((string) ($detail->RegNo2 ?? '')),
                        'roomCode' => trim((string) ($detail->Kode ?? '')),
                        'packageCode' => trim((string) ($detail->Package ?? '')),
                        'nominal' => (string) ((float) ($detail->Nominal ?? 0)),
                        'breakfast' => (int) ($detail->BF ?? 0),
                        'groupPosition' => trim((string) ($detail->Posisi ?? '')),
                        'guestName' => trim((string) ($detail->Guest ?? '')),
                        'guestIdType' => trim((string) ($detail->TypeOfId ?? 'KTP')),
                        'guestIdNumber' => trim((string) ($detail->KTP ?? '')),
                        'guestBirthDate' => $detailBirthDate,
                        'guestPhone' => trim((string) ($detail->Phone ?? '')),
                        'guestEmail' => trim((string) ($detail->Email ?? '')),
                        'guestAddress' => trim((string) ($detail->Alamat ?? '')),
                        'guestNationality' => trim((string) ($detail->KodeNegara ?? 'INA')),
                        'sameAsLeader' => $sameAsLeader,
                    ];
                })->values()->all();
                $row->record_json = json_encode([
                    'Id' => $primaryRow->id,
                    'DetailKey' => $primaryRow->RegNo2,
                    'RegNo' => $primaryRow->RegNo,
                    'RoomCode' => $primaryRow->Kode,
                    'GuestName' => $primaryRow->Guest,
                    'GuestName2' => $primaryRow->Guest2,
                    'ReservationNumber' => $primaryRow->Receipt,
                    'TypeOfCheckIn' => $primaryRow->Tipe,
                    'CheckInDate' => !empty($primaryRow->TglIn) ? Carbon::parse($primaryRow->TglIn)->format('Y-m-d') : '',
                    'CheckInTime' => $this->displayTime($primaryRow->JamIn ?? null),
                    'EstimationOut' => !empty($primaryRow->TglKeluar) ? Carbon::parse($primaryRow->TglKeluar)->format('Y-m-d') : '',
                    'PlaceOfBirth' => $primaryRow->PlaceBirth,
                    'BirthDate' => !empty($primaryRow->TglLahir) ? Carbon::parse($primaryRow->TglLahir)->format('Y-m-d') : '',
                    'Religion' => $primaryRow->Agama,
                    'Nationality' => $primaryRow->KodeNegara,
                    'NumberOfPerson' => (int) ($primaryRow->Person ?? 1),
                    'PaymentMethod' => $primaryRow->Payment,
                    'Company' => $primaryRow->Usaha,
                    'CreditCardNumber' => $primaryRow->CardNumber,
                    'Occupation' => $primaryRow->Profesi,
                    'Segment' => $primaryRow->Segment,
                    'Phone' => $primaryRow->Phone,
                    'Email' => $primaryRow->Email,
                    'Breakfast' => (int) ($primaryRow->BF ?? 0),
                    'Remarks' => $primaryRow->Remark,
                    'Member' => $primaryRow->Member,
                    'Sales' => $primaryRow->Sales,
                    'Address' => $primaryRow->Alamat,
                    'Kelurahan' => $primaryRow->Kelurahan,
                    'Kecamatan' => $primaryRow->Kecamatan,
                    'KabCity' => $primaryRow->Kota,
                    'ProvinceCountry' => $primaryRow->Propinsi,
                    'TypeOfId' => $primaryRow->TypeOfId,
                    'IdNumber' => $primaryRow->KTP,
                    'ExpiredDate' => !empty($primaryRow->Expired) ? Carbon::parse($primaryRow->Expired)->format('Y-m-d') : '',
                    'GroupPosition' => $primaryRow->Posisi,
                    'PackageCode' => $primaryRow->Package,
                    'Nominal' => (float) ($primaryRow->Nominal ?? 0),
                    'CheckDeposit' => (int) ($primaryRow->SafeDeposit ?? 0),
                    'AdditionalRooms' => $additionalRooms,
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
            ->map(fn($value) => trim((string) $value))
            ->filter()
            ->flip();

        $blockedStatus = ['CHECK OUT', 'OUT OF ORDER', 'VACANT DIRTY', 'RENOVATED', 'VACANT CLEAN'];

        return DB::table('ROOM')
            ->selectRaw("RTRIM(Kode) as Kode, RTRIM(Nama) as Nama, RTRIM(Status) as Status, RTRIM(Status2) as Status2, RTRIM(Fasilitas) as Fasilitas, Rate1, Meeting, Urut")
            ->whereRaw("RTRIM(Kode) <> '999'")
            ->where('Meeting', 0)
            ->orderBy('Urut')
            ->orderBy('Kode')
            ->get()
            ->map(function ($room) use ($activeRooms, $blockedStatus) {
                $status = strtoupper(trim((string) ($room->Status ?? '')));
                $kode = trim((string) $room->Kode);
                $available = !$activeRooms->has($kode) && !in_array($status, $blockedStatus, true);

                return [
                    'kode' => $kode,
                    'kelas' => trim((string) ($room->Nama ?? '')),
                    'status' => trim((string) ($room->Status ?? '')),
                    'status2' => trim((string) ($room->Status2 ?? '')),
                    'fasilitas' => trim((string) ($room->Fasilitas ?? '')),
                    'rate1' => (float) ($room->Rate1 ?? 0),
                    'available' => $available,
                ];
            })
            ->filter(fn (array $room) => $room['available'])
            ->values()
            ->all();
    }

    private function loadCompanyOptions(): array
    {
        return DB::table('DATA2')
            ->selectRaw('RTRIM(Usaha) as Company')
            ->whereRaw("LTRIM(RTRIM(ISNULL(Usaha, ''))) <> ''")
            ->distinct()
            ->orderBy('Company')
            ->get()
            ->pluck('Company')
            ->map(fn($value) => strtoupper(trim((string) $value)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function loadProvinceOptions(): array
    {
        return DB::table('province')
            ->selectRaw('RTRIM(Nama) as Nama')
            ->orderBy('Nama')
            ->get()
            ->pluck('Nama')
            ->map(fn($value) => trim((string) $value))
            ->filter()
            ->values()
            ->all();
    }

    private function isAllowedProvince(string $value): bool
    {
        $normalized = trim($value);
        if ($normalized === '') {
            return false;
        }

        foreach ($this->loadProvinceOptions() as $option) {
            if (strcasecmp(trim((string) $option), $normalized) === 0) {
                return true;
            }
        }

        return false;
    }

    private function paddleOcrConfigured(): bool
    {
        return trim((string) config('services.paddle_ocr.python_path', '')) !== '' &&
            trim((string) config('services.paddle_ocr.script_path', '')) !== '';
    }

    private function googleDocumentAiConfigured(): bool
    {
        return trim((string) config('services.google_document_ai.project_id', '')) !== '' &&
            trim((string) config('services.google_document_ai.processor_id', '')) !== '' &&
            $this->loadGoogleDocumentAiCredentials() !== null;
    }

    private function openAiKtpCorrectionConfigured(): bool
    {
        return trim((string) config('services.openai_ocr.api_key', '')) !== '';
    }

    private function scanKtpRawWithDocumentAi($image): string
    {
        $binary = $this->readUploadedImageBinary($image);
        $projectId = trim((string) config('services.google_document_ai.project_id', ''));
        $location = trim((string) config('services.google_document_ai.location', 'us')) ?: 'us';
        $processorId = trim((string) config('services.google_document_ai.processor_id', ''));
        $processorVersion = trim((string) config('services.google_document_ai.processor_version', ''));
        $endpoint = rtrim(trim((string) config('services.google_document_ai.endpoint', 'https://documentai.googleapis.com')) ?: 'https://documentai.googleapis.com', '/');
        $languageHints = array_values(array_filter((array) config('services.google_document_ai.language_hints', ['id', 'en'])));
        $mimeType = trim((string) ($image->getMimeType() ?: 'image/jpeg'));
        $credentials = $this->loadGoogleDocumentAiCredentials();

        if (!$credentials) {
            throw new \RuntimeException('Google Document AI credentials are missing.');
        }

        $processorName = sprintf(
            'projects/%s/locations/%s/processors/%s',
            $projectId,
            $location,
            $processorId
        );

        if ($processorVersion !== '') {
            $processorName .= '/processorVersions/' . $processorVersion;
        }

        $response = Http::timeout(90)
            ->acceptJson()
            ->withToken($this->fetchGoogleServiceAccountAccessToken($credentials))
            ->post($endpoint . '/v1/' . $processorName . ':process', [
                'rawDocument' => [
                    'content' => base64_encode($binary),
                    'mimeType' => $mimeType,
                ],
                'processOptions' => [
                    'ocrConfig' => [
                        'enableNativePdfParsing' => true,
                        'enableImageQualityScores' => true,
                        'enableSymbol' => true,
                        'hints' => [
                            'languageHints' => $languageHints,
                        ],
                    ],
                ],
            ])
            ->throw()
            ->json();

        $rawText = trim((string) ($response['document']['text'] ?? ''));

        if ($rawText === '') {
            throw new \RuntimeException('Google Document AI returned empty text.');
        }

        return $rawText;
    }

    private function scanKtpRawWithLocalPaddleOcr($image): string
    {
        $pythonPath = trim((string) config('services.paddle_ocr.python_path', ''));
        $scriptPath = trim((string) config('services.paddle_ocr.script_path', ''));
        $lang = trim((string) config('services.paddle_ocr.lang', 'en')) ?: 'en';

        if ($pythonPath === '' || $scriptPath === '') {
            throw new \RuntimeException('PaddleOCR local runtime is not configured.');
        }

        $process = new Process([
            $pythonPath,
            $scriptPath,
            $image->getRealPath(),
            $lang,
        ], base_path(), $this->buildPaddleOcrProcessEnvironment($pythonPath), null, 180);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(trim($process->getErrorOutput()) ?: trim($process->getOutput()) ?: 'PaddleOCR local process failed.');
        }

        $payload = json_decode(trim($process->getOutput()), true);
        if (!is_array($payload)) {
            throw new \RuntimeException('PaddleOCR local output is not valid JSON.');
        }

        $error = trim((string) ($payload['error'] ?? ''));
        if ($error !== '') {
            throw new \RuntimeException($error);
        }

        $rawText = trim((string) ($payload['rawText'] ?? ''));
        if ($rawText === '') {
            throw new \RuntimeException('PaddleOCR local returned empty text.');
        }

        return $rawText;
    }

    private function buildPaddleOcrProcessEnvironment(string $pythonPath): array
    {
        $runtimeRoot = storage_path('app/paddleocr');
        $homeDir = $runtimeRoot . DIRECTORY_SEPARATOR . 'home';
        $tempDir = $runtimeRoot . DIRECTORY_SEPARATOR . 'temp';
        $cacheRoot = $runtimeRoot . DIRECTORY_SEPARATOR . 'cache';
        $appDataDir = $cacheRoot . DIRECTORY_SEPARATOR . 'roaming';
        $localAppDataDir = $cacheRoot . DIRECTORY_SEPARATOR . 'local';
        $modelDir = $runtimeRoot . DIRECTORY_SEPARATOR . 'models';
        $huggingFaceDir = $cacheRoot . DIRECTORY_SEPARATOR . 'huggingface';
        $huggingFaceHubDir = $huggingFaceDir . DIRECTORY_SEPARATOR . 'hub';

        foreach ([
            $runtimeRoot,
            $homeDir,
            $tempDir,
            $cacheRoot,
            $appDataDir,
            $localAppDataDir,
            $modelDir,
            $huggingFaceDir,
            $huggingFaceHubDir,
        ] as $directory) {
            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }
        }

        $pythonDir = dirname($pythonPath);
        $pythonRoot = dirname($pythonDir);
        $systemRoot = getenv('SystemRoot') ?: getenv('WINDIR') ?: 'C:\\Windows';
        $existingPath = getenv('PATH') ?: ($_SERVER['PATH'] ?? '');

        $pathParts = [];
        foreach ([
            $pythonDir,
            $pythonRoot,
            $existingPath,
            $systemRoot . '\\System32',
            $systemRoot,
            $systemRoot . '\\System32\\Wbem',
            $systemRoot . '\\System32\\WindowsPowerShell\\v1.0',
        ] as $part) {
            foreach (explode(';', (string) $part) as $segment) {
                $segment = trim($segment);
                if ($segment !== '' && !in_array(strtolower($segment), array_map('strtolower', $pathParts), true)) {
                    $pathParts[] = $segment;
                }
            }
        }

        return [
            'PATH' => implode(';', $pathParts),
            'SystemRoot' => $systemRoot,
            'SYSTEMROOT' => $systemRoot,
            'WINDIR' => getenv('WINDIR') ?: $systemRoot,
            'TEMP' => $tempDir,
            'TMP' => $tempDir,
            'TMPDIR' => $tempDir,
            'HOME' => $homeDir,
            'USERPROFILE' => $homeDir,
            'APPDATA' => $appDataDir,
            'LOCALAPPDATA' => $localAppDataDir,
            'PYTHONUTF8' => '1',
            'PYTHONIOENCODING' => 'utf-8',
            'PYTHONHOME' => false,
            'PYTHONPATH' => false,
            'PADDLE_PDX_DISABLE_MODEL_SOURCE_CHECK' => 'True',
            'PADDLE_PDX_MODEL_HOME' => $modelDir,
            'HF_HOME' => $huggingFaceDir,
            'HUGGINGFACE_HUB_CACHE' => $huggingFaceHubDir,
        ];
    }

    private function loadGoogleDocumentAiCredentials(): ?array
    {
        $rawJson = trim((string) config('services.google_document_ai.credentials_json', ''));
        if ($rawJson !== '') {
            $decoded = json_decode($rawJson, true);
            return is_array($decoded) ? $decoded : null;
        }

        $path = trim((string) config('services.google_document_ai.credentials_path', ''));
        if ($path === '' || !is_file($path)) {
            return null;
        }

        $contents = @file_get_contents($path);
        if ($contents === false || trim($contents) === '') {
            return null;
        }

        $decoded = json_decode($contents, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function fetchGoogleServiceAccountAccessToken(array $credentials): string
    {
        $clientEmail = trim((string) ($credentials['client_email'] ?? ''));
        $privateKey = (string) ($credentials['private_key'] ?? '');
        $tokenUri = trim((string) ($credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token'));

        if ($clientEmail === '' || $privateKey === '') {
            throw new \RuntimeException('Google Document AI service account credentials are incomplete.');
        }

        $issuedAt = time();
        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ], JSON_UNESCAPED_SLASHES));
        $payload = $this->base64UrlEncode(json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/cloud-platform',
            'aud' => $tokenUri,
            'iat' => $issuedAt,
            'exp' => $issuedAt + 3600,
        ], JSON_UNESCAPED_SLASHES));

        $unsignedToken = $header . '.' . $payload;
        $signature = '';
        $privateKeyResource = openssl_pkey_get_private($privateKey);
        if ($privateKeyResource === false || !openssl_sign($unsignedToken, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('Unable to sign Google service account JWT.');
        }

        $jwt = $unsignedToken . '.' . $this->base64UrlEncode($signature);

        $response = Http::asForm()
            ->acceptJson()
            ->timeout(60)
            ->post($tokenUri, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ])
            ->throw()
            ->json();

        $accessToken = trim((string) ($response['access_token'] ?? ''));
        if ($accessToken === '') {
            throw new \RuntimeException('Google OAuth access token response was empty.');
        }

        return $accessToken;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function correctKtpDraftWithOpenAi($image, string $rawText, array $draft): array
    {
        $binary = $this->readUploadedImageBinary($image);

        $mimeType = trim((string) ($image->getMimeType() ?: 'image/jpeg'));
        $detail = trim((string) config('services.openai_ocr.detail', 'high')) ?: 'high';
        $model = trim((string) config('services.openai_ocr.model', 'gpt-5.4')) ?: 'gpt-5.4';
        $dataUrl = 'data:' . $mimeType . ';base64,' . base64_encode($binary);

        $response = Http::timeout(90)
            ->acceptJson()
            ->withToken((string) config('services.openai_ocr.api_key'))
            ->post('https://api.openai.com/v1/responses', [
                'model' => $model,
                'input' => [[
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => implode("\n", [
                                'You are correcting OCR extraction for an Indonesian KTP.',
                                'Primary OCR text came from Google Document AI.',
                                'You also receive a regex-based draft extracted by Laravel.',
                                'Use the image, raw OCR text, and draft together.',
                                'Prefer the most plausible value that is strongly supported by the image context.',
                                'Keep string fields uppercase except rawText.',
                                'Use YYYY-MM-DD for birthDate, or an empty string if unreadable.',
                                'If a value is uncertain, return an empty string.',
                                'Do not invent data.',
                                'Raw OCR text:',
                                $rawText,
                                'Regex draft JSON:',
                                json_encode($draft, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                            ]),
                        ],
                        [
                            'type' => 'input_image',
                            'image_url' => $dataUrl,
                            'detail' => $detail,
                        ],
                    ],
                ]],
                'text' => [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => 'ktp_extraction',
                        'strict' => true,
                        'schema' => [
                            'type' => 'object',
                            'additionalProperties' => false,
                            'properties' => [
                                'name' => ['type' => 'string'],
                                'idNumber' => ['type' => 'string'],
                                'placeOfBirth' => ['type' => 'string'],
                                'birthDate' => ['type' => 'string'],
                                'address' => ['type' => 'string'],
                                'kelurahan' => ['type' => 'string'],
                                'kecamatan' => ['type' => 'string'],
                                'city' => ['type' => 'string'],
                                'province' => ['type' => 'string'],
                                'religion' => ['type' => 'string'],
                                'occupation' => ['type' => 'string'],
                                'postalCode' => ['type' => 'string'],
                                'rawText' => ['type' => 'string'],
                            ],
                            'required' => [
                                'name',
                                'idNumber',
                                'placeOfBirth',
                                'birthDate',
                                'address',
                                'kelurahan',
                                'kecamatan',
                                'city',
                                'province',
                                'religion',
                                'occupation',
                                'postalCode',
                                'rawText',
                            ],
                        ],
                    ],
                ],
            ])
            ->throw()
            ->json();

        $content = $this->extractOpenAiResponseText($response);
        $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $decoded['rawText'] = trim((string) ($decoded['rawText'] ?? $rawText));

        return $this->normalizeOpenAiKtpPayload($decoded);
    }

    private function extractKtpWithOpenAiDirect($image): array
    {
        $binary = $this->readUploadedImageBinary($image);
        $mimeType = trim((string) ($image->getMimeType() ?: 'image/jpeg'));
        $detail = trim((string) config('services.openai_ocr.detail', 'high')) ?: 'high';
        $model = trim((string) config('services.openai_ocr.model', 'gpt-5.4')) ?: 'gpt-5.4';
        $dataUrl = 'data:' . $mimeType . ';base64,' . base64_encode($binary);

        $response = Http::timeout(90)
            ->acceptJson()
            ->withToken((string) config('services.openai_ocr.api_key'))
            ->post('https://api.openai.com/v1/responses', [
                'model' => $model,
                'input' => [[
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => implode("\n", [
                                'Extract fields from this Indonesian KTP image.',
                                'Return only the requested JSON schema.',
                                'Pay close attention to Indonesian person names and correct obvious OCR splits when strongly supported by the image.',
                                'For names, prefer the most plausible full reading visible on the KTP rather than the raw OCR fragments.',
                                'Use uppercase for all string fields except rawText.',
                                'Use YYYY-MM-DD for birthDate, or an empty string if unreadable.',
                                'If a value is uncertain, return an empty string.',
                                'Do not invent data beyond what is visible or strongly inferable from the image.',
                            ]),
                        ],
                        [
                            'type' => 'input_image',
                            'image_url' => $dataUrl,
                            'detail' => $detail,
                        ],
                    ],
                ]],
                'text' => [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => 'ktp_extraction',
                        'strict' => true,
                        'schema' => [
                            'type' => 'object',
                            'additionalProperties' => false,
                            'properties' => [
                                'name' => ['type' => 'string'],
                                'idNumber' => ['type' => 'string'],
                                'placeOfBirth' => ['type' => 'string'],
                                'birthDate' => ['type' => 'string'],
                                'address' => ['type' => 'string'],
                                'kelurahan' => ['type' => 'string'],
                                'kecamatan' => ['type' => 'string'],
                                'city' => ['type' => 'string'],
                                'province' => ['type' => 'string'],
                                'religion' => ['type' => 'string'],
                                'occupation' => ['type' => 'string'],
                                'postalCode' => ['type' => 'string'],
                                'rawText' => ['type' => 'string'],
                            ],
                            'required' => [
                                'name',
                                'idNumber',
                                'placeOfBirth',
                                'birthDate',
                                'address',
                                'kelurahan',
                                'kecamatan',
                                'city',
                                'province',
                                'religion',
                                'occupation',
                                'postalCode',
                                'rawText',
                            ],
                        ],
                    ],
                ],
            ])
            ->throw()
            ->json();

        $content = $this->extractOpenAiResponseText($response);
        $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return $this->normalizeOpenAiKtpPayload($decoded);
    }

    private function buildKtpRegexDraft(string $rawText): array
    {
        $normalizedText = $this->normalizeRawKtpText($rawText);
        $lines = $this->splitKtpLines($normalizedText);
        [$province, $city] = $this->extractProvinceAndCity($lines);
        $birthData = $this->extractBirthDataFromLines($lines);
        $address = $this->extractLabeledValue($lines, [
            '/^ALAMAT\b[:\-\s]*/i',
        ], 'address');
        $kelurahan = $this->extractLabeledValue($lines, [
            '/^KEL(?:\/|\s*)DESA\b[:\-\s]*/i',
            '/^KELDESA\b[:\-\s]*/i',
            '/^KELURAHAN\b[:\-\s]*/i',
            '/^DESA\b[:\-\s]*/i',
        ], 'kelurahan');
        $kecamatan = $this->extractLabeledValue($lines, [
            '/^KECAMATAN\b[:\-\s]*/i',
            '/^KEC\b[:\-\s]*/i',
        ], 'kecamatan');
        if ($kecamatan === '') {
            $kecamatan = $this->inferKecamatanFromKelurahan($kelurahan, $city);
        }

        $draft = [
            'name' => $this->extractLabeledValue($lines, [
                '/^NAMA\b[:\-\s]*/i',
            ], 'name'),
            'idNumber' => $this->extractNikValueFromText($normalizedText),
            'placeOfBirth' => $birthData['placeOfBirth'],
            'birthDate' => $birthData['birthDate'],
            'address' => $address,
            'kelurahan' => $kelurahan,
            'kecamatan' => $kecamatan,
            'city' => $city,
            'province' => $province !== '' ? $province : $this->inferProvinceFromCity($city),
            'religion' => $this->extractReligionValue($lines),
            'occupation' => $this->extractLabeledValue($lines, [
                '/^PEKERJAAN\b[:\-\s]*/i',
                '/^PEKERJA\b[:\-\s]*/i',
                '/^PEK\b[:\-\s]*/i',
            ], 'occupation'),
            'postalCode' => $this->extractPostalCodeValue($normalizedText),
            'nationality' => 'INA',
            'rawText' => trim($rawText),
        ];

        if ($draft['occupation'] === '' && preg_match('/MENGURUS\s+RUMAH\s+TANGGA/i', $normalizedText)) {
            $draft['occupation'] = 'MENGURUS RUMAH TANGGA';
        }

        return $draft;
    }

    private function readUploadedImageBinary($image): string
    {
        $binary = @file_get_contents($image->getRealPath());
        if ($binary === false) {
            throw new \RuntimeException('Unable to read uploaded KTP image.');
        }

        return $binary;
    }

    private function normalizeRawKtpText(string $text): string
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $text);
        $replacements = [
            '/\bNAME\b/i' => 'NAMA',
            '/\bNAMU\b/i' => 'NAMA',
            '/\bTEMPEL\b/i' => 'TEMPAT',
            '/\bTEMPAU?TG\b/i' => 'TEMPAT TGL',
            '/\bTAL\s+ATER\b/i' => 'JAKARTA',
            '/\bLANG\b/i' => 'LAHIR',
            '/\bALARA\b/i' => 'ALAMAT',
            '/\bKEOWN\b/i' => 'KELDESA',
            '/\bKOOANAT[MN]\b/i' => 'KECAMATAN',
            '/\bAGANA\b/i' => 'AGAMA',
            '/\bAGA\s*MA\b/i' => 'AGAMA',
            '/\bPEKARJAAN\b/i' => 'PEKERJAAN',
            '/\bPEKERJA\b/i' => 'PEKERJAAN',
            '/%OVDESA/i' => 'KELDESA',
            '/\bOUR[SI]\b/i' => 'DURI',
            '/\bPALO\b/i' => 'PULO',
            '/\bGAMAIR\b/i' => 'GAMBIR',
            '/\bGAMER\b/i' => 'GAMBIR',
            '/\bOKI\s+JAKARTA\b/i' => 'DKI JAKARTA',
            '/\bWHI\b/i' => 'WNI',
            '/\bKAMN\b/i' => 'KAWIN',
            '/\bMENGURAS\b/i' => 'MENGURUS',
        ];

        foreach ($replacements as $pattern => $replacement) {
            $normalized = preg_replace($pattern, $replacement, $normalized) ?? $normalized;
        }

        return $normalized;
    }

    private function splitKtpLines(string $text): array
    {
        return array_values(array_filter(array_map(
            static fn ($line) => trim((string) $line),
            preg_split('/\n+/', $text) ?: []
        ), static fn ($line) => $line !== ''));
    }

    private function cleanupKtpValue(string $value, string $field = 'generic'): string
    {
        $cleaned = strtoupper(trim($value));
        $cleaned = preg_replace('/^[^A-Z0-9]+/', '', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/[^A-Z0-9.\/,\-\s]+$/', '', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/\s+([:.,\/-])/', '$1', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/([:.,\/-])\s+/', '$1 ', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/\s{2,}/', ' ', $cleaned) ?? $cleaned;
        $cleaned = trim($cleaned);

        if (in_array($field, ['address', 'kelurahan', 'kecamatan', 'city', 'province'], true)) {
            $cleaned = preg_replace('/^(ALAMAT|ADDRESS|KEL\/DESA|KELDESA|KELURAHAN|DESA|KECAMATAN|KAB\/KOTA|KABUPATEN|KOTA|PROVINSI|PROVINCE)[:\-\s]*/i', '', $cleaned) ?? $cleaned;
            $cleaned = preg_replace('/\bK[PF](?=[A-Z])/', 'KP ', $cleaned) ?? $cleaned;
            $cleaned = preg_replace('/\bPALO\b/', 'PULO', $cleaned) ?? $cleaned;
            $cleaned = preg_replace('/\bGAMER\b|\bGAMAIR\b/', 'GAMBIR', $cleaned) ?? $cleaned;
            $cleaned = preg_replace('/([A-Z]{3,})(BARAT|TIMUR|UTARA|SELATAN|TENGAH|PUSAT)\b/', '$1 $2', $cleaned) ?? $cleaned;
            $cleaned = preg_replace('/([A-Z]{3,})(PULO|PULAU|HILIR|HULU|LOR|KIDUL)\b/', '$1 $2', $cleaned) ?? $cleaned;
            $cleaned = preg_replace('/([A-Z]{2,})NO\.?\s*(\d+)/', '$1 NO $2', $cleaned) ?? $cleaned;
            $cleaned = preg_replace('/(?:\s+[:.,\/-])+$/', '', $cleaned) ?? $cleaned;
        }

        if ($field === 'name') {
            $cleaned = preg_replace('/^NAMA[:\-\s]*/i', '', $cleaned) ?? $cleaned;
            $cleaned = preg_replace('/\b5\s*KOM\b/', 'S KOM', $cleaned) ?? $cleaned;
            $cleaned = preg_replace('/(?:\s+[:.,\/-])+$/', '', $cleaned) ?? $cleaned;
        }

        if ($field === 'religion') {
            $cleaned = preg_replace('/^AGAMA[:\-\s]*/i', '', $cleaned) ?? $cleaned;
            $cleaned = preg_replace('/\bLAM\b/', 'ISLAM', $cleaned) ?? $cleaned;
        }

        if ($field === 'occupation') {
            $cleaned = preg_replace('/^(PEKERJAAN|PEKERJA|PEK)[:\-\s]*/i', '', $cleaned) ?? $cleaned;
            $cleaned = preg_replace('/\bJAKARTA\s+PUSAT\b$/', '', $cleaned) ?? $cleaned;
            if (preg_match('/RUMAH\s+TANGGA/', $cleaned)) {
                $cleaned = 'MENGURUS RUMAH TANGGA';
            }
        }

        if ($field === 'placeOfBirth') {
            $cleaned = preg_replace('/\b(TEMPAT|TGL|LAHIR)\b/', '', $cleaned) ?? $cleaned;
            $cleaned = preg_replace('/\.$/', '', $cleaned) ?? $cleaned;
            $cleaned = trim(preg_replace('/\s{2,}/', ' ', $cleaned) ?? $cleaned);
        }

        return trim($cleaned);
    }

    private function extractProvinceAndCity(array $lines): array
    {
        $province = '';
        $city = '';

        foreach ($lines as $index => $line) {
            if (preg_match('/PROVINSI/i', $line)) {
                $province = $this->cleanupKtpValue((string) preg_replace('/^.*PROVINSI[:\-\s]*/i', '', $line), 'province');
                $nextLine = $lines[$index + 1] ?? '';
                if ($nextLine !== '' && !preg_match('/NIK/i', $nextLine)) {
                    $city = $this->cleanupKtpValue($nextLine, 'city');
                }
                break;
            }
        }

        if ($city === '') {
            foreach ($lines as $line) {
                if (preg_match('/JAKARTA\s+(PUSAT|BARAT|TIMUR|UTARA|SELATAN)|\bKAB(?:UPATEN)?\b|\bKOTA\b/i', $line) && !preg_match('/PROVINSI|NIK/i', $line)) {
                    $city = $this->cleanupKtpValue($line, 'city');
                    break;
                }
            }
        }

        if ($province === '' && $city !== '') {
            $province = $this->inferProvinceFromCity($city);
        }

        return [$province, $city];
    }

    private function extractBirthDataFromLines(array $lines): array
    {
        foreach ($lines as $index => $line) {
            if (!preg_match('/LAHIR|TEMP|TGL|LANG/i', $line)) {
                continue;
            }

            if (preg_match('/(\d{2}[.\-\/\s]\d{2}[.\-\/\s]\d{4})/', $line, $matches, PREG_OFFSET_CAPTURE)) {
                $dateText = $matches[1][0] ?? '';
                $position = $matches[1][1] ?? 0;
                $placeRaw = trim(substr($line, 0, (int) $position));

                return [
                    'placeOfBirth' => $this->cleanupKtpValue($placeRaw, 'placeOfBirth'),
                    'birthDate' => $this->normalizeLooseDate($dateText),
                ];
            }

            $nextLine = $lines[$index + 1] ?? '';
            if (preg_match('/(\d{2}[.\-\/\s]\d{2}[.\-\/\s]\d{4})/', $nextLine, $matches)) {
                return [
                    'placeOfBirth' => $this->cleanupKtpValue($line, 'placeOfBirth'),
                    'birthDate' => $this->normalizeLooseDate($matches[1]),
                ];
            }
        }

        return [
            'placeOfBirth' => '',
            'birthDate' => '',
        ];
    }

    private function normalizeLooseDate(string $value): string
    {
        $normalized = trim($value);
        $normalized = preg_replace('/[.\/\s]+/', '-', $normalized) ?? $normalized;
        $normalized = preg_replace('/-+/', '-', $normalized) ?? $normalized;
        $normalized = trim($normalized, '-');

        if (!preg_match('/(\d{2})-(\d{2})-(\d{4})/', $normalized, $matches)) {
            return '';
        }

        $day = $matches[1];
        $month = $matches[2];
        $year = $matches[3];

        if ((int) $day > 31) {
            $candidateDay = preg_replace('/^[79]/', '2', $day) ?? $day;
            if ((int) $candidateDay <= 31) {
                $day = $candidateDay;
            }
        }

        if ((int) $year < 1900) {
            $shortYear = ((int) $year) % 100;
            $currentYear = (int) date('Y');
            $candidates = [1900 + $shortYear, 2000 + $shortYear];
            foreach ($candidates as $candidate) {
                $age = $currentYear - $candidate;
                if ($candidate <= $currentYear && $age >= 17 && $age <= 100) {
                    $year = (string) $candidate;
                    break;
                }
            }
        }

        return sprintf('%04d-%02d-%02d', (int) $year, (int) $month, (int) $day);
    }

    private function extractLabeledValue(array $lines, array $patterns, string $field): string
    {
        foreach ($lines as $index => $line) {
            foreach ($patterns as $pattern) {
                if (!preg_match($pattern, $line)) {
                    continue;
                }

                $cleaned = $this->cleanupKtpValue((string) preg_replace($pattern, '', $line, 1), $field);
                if ($cleaned !== '') {
                    return $cleaned;
                }

                $nextLine = $lines[$index + 1] ?? '';
                $nextCleaned = $this->cleanupKtpValue($nextLine, $field);
                if ($nextCleaned !== '' && !preg_match('/^[A-Z\s\/.]+:?\s*$/', $nextLine)) {
                    return $nextCleaned;
                }
            }
        }

        return '';
    }

    private function extractNikValueFromText(string $text): string
    {
        if (preg_match('/\b\d{16}\b/', preg_replace('/[^\d]/', ' ', $text) ?? '', $matches)) {
            return $matches[0];
        }

        foreach ($this->splitKtpLines($text) as $line) {
            if (!preg_match('/NIK/i', $line)) {
                continue;
            }

            $candidate = preg_replace('/^.*NIK[:\-\s]*/i', '', $line) ?? '';
            $candidate = strtoupper(preg_replace('/\s+/', '', $candidate) ?? '');
            $candidate = strtr($candidate, [
                'O' => '0',
                'Q' => '0',
                'D' => '0',
                'I' => '1',
                'L' => '1',
                'Z' => '2',
                'A' => '4',
                'S' => '5',
                'E' => '6',
                'G' => '6',
                'B' => '8',
                '?' => '7',
                'N' => '0',
            ]);
            $digits = preg_replace('/[^\d]/', '', $candidate) ?? '';
            if (strlen($digits) >= 16) {
                return substr($digits, 0, 16);
            }
        }

        return '';
    }

    private function extractReligionValue(array $lines): string
    {
        $value = $this->extractLabeledValue($lines, [
            '/^AGAMA\b[:\-\s]*/i',
        ], 'religion');

        if ($value !== '') {
            return $value;
        }

        $joined = implode(' ', $lines);
        if (preg_match('/\b(LAM|ISLAM|I5LAM)\b/i', $joined)) {
            return 'ISLAM';
        }

        return '';
    }

    private function extractPostalCodeValue(string $text): string
    {
        if (preg_match('/(?:KODE\s*POS|POSTAL\s*CODE|POS)[:\-\s]*(\d{5})/i', $text, $matches)) {
            return $matches[1];
        }

        return '';
    }

    private function inferProvinceFromCity(string $city): string
    {
        return preg_match('/JAKARTA/i', $city) ? 'DKI JAKARTA' : '';
    }

    private function inferKecamatanFromKelurahan(string $kelurahan, string $city = ''): string
    {
        $kelurahanKey = preg_replace('/[^A-Z]/', '', strtoupper($kelurahan)) ?? '';
        $cityKey = preg_replace('/[^A-Z]/', '', strtoupper($city)) ?? '';
        $aliasMap = [
            'DURIPULO' => 'GAMBIR',
            'DURIPALO' => 'GAMBIR',
            'DUREPULO' => 'GAMBIR',
            'DUREPULD' => 'GAMBIR',
            'DURIPULD' => 'GAMBIR',
        ];

        if ($cityKey === 'JAKARTAPUSAT' && isset($aliasMap[$kelurahanKey])) {
            return $aliasMap[$kelurahanKey];
        }

        return $aliasMap[$kelurahanKey] ?? '';
    }

    private function resolveKtpScanException(\Throwable $exception): array
    {
        if ($exception instanceof RequestException) {
            $status = $exception->response?->status() ?? 502;
            if ($status === 429) {
                return ['Quota OCR AI habis. OCR modern tidak bisa dipakai sekarang sampai billing/quota provider tersedia lagi.', 429];
            }

            if ($status === 401) {
                return ['API key OCR tidak valid atau tidak punya akses.', 401];
            }

            if ($status === 403) {
                return ['Akses ke provider OCR ditolak. Periksa project, billing, atau permission API.', 403];
            }
        }

        $message = trim($exception->getMessage());
        if (stripos($message, 'PaddleOCR') !== false) {
            return [$message, 502];
        }

        return ['OCR modern backend belum berhasil membaca KTP.', 502];
    }

    private function extractOpenAiResponseText(array $payload): string
    {
        foreach ((array) ($payload['output'] ?? []) as $outputItem) {
            foreach ((array) ($outputItem['content'] ?? []) as $contentItem) {
                $text = trim((string) ($contentItem['text'] ?? ''));
                if ($text !== '') {
                    return $this->stripJsonCodeFence($text);
                }
            }
        }

        $fallback = trim((string) ($payload['output_text'] ?? ''));
        if ($fallback !== '') {
            return $this->stripJsonCodeFence($fallback);
        }

        throw new \RuntimeException('OpenAI OCR returned an empty response.');
    }

    private function stripJsonCodeFence(string $value): string
    {
        $trimmed = trim($value);
        if (str_starts_with($trimmed, '```')) {
            $trimmed = preg_replace('/^```[a-zA-Z0-9_-]*\s*/', '', $trimmed) ?? $trimmed;
            $trimmed = preg_replace('/\s*```$/', '', $trimmed) ?? $trimmed;
        }

        return trim($trimmed);
    }

    private function normalizeOpenAiKtpPayload(array $payload): array
    {
        $textFields = [
            'name',
            'idNumber',
            'placeOfBirth',
            'address',
            'kelurahan',
            'kecamatan',
            'city',
            'province',
            'religion',
            'occupation',
            'postalCode',
        ];

        $normalized = [
            'name' => '',
            'idNumber' => '',
            'placeOfBirth' => '',
            'birthDate' => trim((string) ($payload['birthDate'] ?? '')),
            'address' => '',
            'kelurahan' => '',
            'kecamatan' => '',
            'city' => '',
            'province' => '',
            'religion' => '',
            'occupation' => '',
            'postalCode' => '',
            'nationality' => 'INA',
            'rawText' => trim((string) ($payload['rawText'] ?? '')),
        ];

        foreach ($textFields as $field) {
            $normalized[$field] = strtoupper(trim((string) ($payload[$field] ?? '')));
        }

        return $normalized;
    }

    private function loadGroupPositionOptions(): array
    {
        return DB::table('PENENG')
            ->selectRaw('RTRIM(KET) as KET')
            ->whereRaw("RTRIM(Flag) = 'POSISI'")
            ->orderBy('Urut')
            ->get()
            ->pluck('KET')
            ->map(fn($value) => trim((string) $value))
            ->filter()
            ->values()
            ->all();
    }

    private function loadTypeOptions(): array
    {
        return DB::table('PENENG')
            ->selectRaw('RTRIM(KET) as KET, RTRIM(Flag) as Flag')
            ->whereRaw("RTRIM(Flag) = 'Tipe'")
            ->orderBy('Urut')
            ->get()
            ->pluck('KET')
            ->map(fn($value) => trim((string) $value))
            ->filter()
            ->values()
            ->all();
    }

    private function loadIdTypeOptions(): array
    {
        return DB::table('PENENG')
            ->selectRaw('RTRIM(KET) as KET')
            ->whereRaw("RTRIM(Flag) = 'KTP'")
            ->orderBy('Urut')
            ->get()
            ->pluck('KET')
            ->map(fn($value) => trim((string) $value))
            ->filter()
            ->values()
            ->all();
    }

    private function loadReservationNumberOptions(): array
    {
        return DB::table('Book')
            ->join('Book2', 'Book.ResNo', '=', 'Book2.ResNo')
            ->selectRaw("
                RTRIM(Book2.ResNo) as ResNo,
                RTRIM(Book2.TelPhone) as TelPhone,
                RTRIM(Book2.Remark) as Remark,
                RTRIM(Book2.OriginalGuest) as OriginalGuest,
                Book2.TglIn as TglIn,
                RTRIM(Book2.Alamat) as Alamat,
                CASE WHEN ISNULL(Book2.Batal, 0) = 0 THEN '' ELSE 'Cancel' END as Status
            ")
            ->whereRaw("LTRIM(RTRIM(ISNULL(Book2.ResNo, ''))) <> ''")
            ->orderByDesc('Book2.TglIn')
            ->orderByDesc('Book2.ResNo')
            ->get()
            ->map(function ($row) {
                return [
                    'resno' => trim((string) ($row->ResNo ?? '')),
                    'phone' => trim((string) ($row->TelPhone ?? '')),
                    'remarks' => trim((string) ($row->Remark ?? '')),
                    'original_guest' => trim((string) ($row->OriginalGuest ?? '')),
                    'address' => trim((string) ($row->Alamat ?? '')),
                    'room_code' => '',
                    'room_class' => '',
                    'accept_by' => '',
                    'status' => trim((string) ($row->Status ?? '')),
                    'check_in_date' => !empty($row->TglIn) ? Carbon::parse($row->TglIn)->format('Y-m-d') : '',
                    'nationality' => 'INA',
                ];
            })
            ->filter(fn (array $row) => $row['resno'] !== '')
            ->unique('resno')
            ->values()
            ->all();
    }

    private function loadSalesOptions(): array
    {
        return DB::table('DATA2')
            ->selectRaw('RTRIM(Sales) as Sales')
            ->whereRaw("LTRIM(RTRIM(ISNULL(Sales, ''))) <> ''")
            ->distinct()
            ->orderBy('Sales')
            ->get()
            ->pluck('Sales')
            ->map(fn($value) => strtoupper(trim((string) $value)))
            ->filter()
            ->unique()
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

    private function findDetailsByRegNo(string $regNo)
    {
        return DB::table('DATA2')
            ->selectRaw('RTRIM(RegNo) as RegNo, RTRIM(RegNo2) as RegNo2, RTRIM(Kode) as Kode, RTRIM(Tipe) as Tipe, RTRIM(Posisi) as Posisi, RTRIM(Guest) as Guest')
            ->whereRaw('RTRIM(RegNo) = ?', [$regNo])
            ->orderBy('TglIn')
            ->orderBy('RegNo2')
            ->get();
    }


    private function roomExists(string $roomCode): bool
    {
        return DB::table('ROOM')->whereRaw('RTRIM(Kode) = ?', [$roomCode])->exists();
    }

    private function roomHasActiveCheckin(string $roomCode, array $ignoreRegNo2s = []): bool
    {
        $query = DB::table('DATA2')
            ->where('Pst', '=', ' ')
            ->whereRaw('RTRIM(Kode) = ?', [$roomCode]);

        $normalizedIgnoreKeys = collect($ignoreRegNo2s)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values()
            ->all();

        if (!empty($normalizedIgnoreKeys)) {
            $query->whereNotIn(DB::raw('RTRIM(RegNo2)'), $normalizedIgnoreKeys);
        }

        return $query->exists();
    }

    private function markRoomOccupiedClean(string $roomCode): void
    {
        DB::table('ROOM')
            ->whereRaw('RTRIM(Kode) = ?', [$roomCode])
            ->update(['Status2' => 'Occupied Clean']);
    }

    private function clearRoomOccupiedMarker(string $roomCode, array $ignoreRegNo2s = []): void
    {
        if ($roomCode === '' || $this->roomHasActiveCheckin($roomCode, $ignoreRegNo2s)) {
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

    private function nextLegacyId(string $table): ?int
    {
        $idColumn = $this->legacyIdColumn($table);

        if (!$idColumn) {
            return null;
        }

        return (int) (DB::table($table)->max($idColumn) ?? 0) + 1;
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
            return Carbon::parse((string) $value)->format('H:i:s');
        } catch (\Throwable $exception) {
            return substr(trim((string) $value), 0, 8);
        }
    }

    private function buildStrTgl(string $date, string $time): string
    {
        try {
            return Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time)->format('YmdHis');
        } catch (\Throwable $exception) {
            try {
                return Carbon::parse($date)->format('Ymd') . '000000';
            } catch (\Throwable $nestedException) {
                return '';
            }
        }
    }
}
