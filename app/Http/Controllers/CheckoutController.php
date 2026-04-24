<?php

namespace App\Http\Controllers;

use App\Support\HotelBranding;
use Carbon\Carbon;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;
        $sortBy = $this->resolveCheckoutSortBy((string) $request->query('sort_by', 'check_in'));
        $sortDir = $this->resolveCheckoutSortDirection((string) $request->query('sort_dir', 'desc'));
        $selectedRegNo = strtoupper(trim((string) $request->query('reg_no', '')));
        $selectedRegNo2 = strtoupper(trim((string) $request->query('reg_no2', '')));

        $directory = $this->loadActiveCheckoutDirectory($search, $perPage, $request, $sortBy, $sortDir);

        if ($this->isCheckoutDirectoryPartialRequest($request)) {
            return view('checkout.partials.directory-section', [
                'directory' => $directory,
                'search' => $search,
                'perPage' => $perPage,
                'sortBy' => $sortBy,
                'sortDir' => $sortDir,
                'selectedRegNo' => $selectedRegNo,
                'selectedRegNo2' => $selectedRegNo2,
            ])->render();
        }

        $directoryItems = collect($directory->items());
        if ($selectedRegNo === '' && $directoryItems->isNotEmpty()) {
            $selectedRegNo = trim((string) ($directoryItems->first()->RegNo ?? ''));
            $selectedRegNo2 = trim((string) ($directoryItems->first()->RegNo2 ?? ''));
        }

        $selectedRegistration = $selectedRegNo !== '' ? $this->loadCheckoutRegistration($selectedRegNo) : null;
        $checkoutDate = $request->query('checkout_date', now()->format('Y-m-d'));
        $checkoutTime = $request->query('checkout_time', now()->format('H:i:s'));
        $checkoutAt = $this->combineDateTime($checkoutDate, $checkoutTime);
        $folio = $selectedRegistration ? $this->buildFolioPayload($selectedRegistration, $checkoutAt, 'PREVIEW') : null;

        $viewData = [
            'profile' => $this->loadHotelProfile(),
            'directory' => $directory,
            'search' => $search,
            'perPage' => $perPage,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'selectedRegNo' => $selectedRegNo,
            'selectedRegNo2' => $selectedRegNo2,
            'selectedRegistration' => $selectedRegistration,
            'checkoutDate' => $checkoutAt->format('Y-m-d'),
            'checkoutTime' => $checkoutAt->format('H:i:s'),
            'folio' => $folio,
        ];

        return $this->respond($request, 'checkout.index', $viewData, [
            'directory' => $this->paginatorPayload($directory),
            'search' => $search,
            'per_page' => $perPage,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
            'selected_reg_no' => $selectedRegNo,
            'selected_reg_no2' => $selectedRegNo2,
            'selected_registration' => $selectedRegistration,
            'checkout_at' => $checkoutAt->format('Y-m-d H:i:s'),
            'folio' => $folio,
        ]);
    }

    private function isCheckoutDirectoryPartialRequest(Request $request): bool
    {
        return $request->header('X-Partial-Component') === 'checkout-directory';
    }

    private function resolveCheckoutSortBy(string $sortBy): string
    {
        $allowedSorts = ['reg_no', 'guest', 'room', 'check_in', 'nominal'];

        return in_array($sortBy, $allowedSorts, true) ? $sortBy : 'check_in';
    }

    private function resolveCheckoutSortDirection(string $sortDir): string
    {
        return strtolower(trim($sortDir)) === 'asc' ? 'asc' : 'desc';
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reg_no' => ['required', 'string', 'max:30'],
            'checkout_date' => ['required', 'date_format:Y-m-d'],
            'checkout_time' => ['required', 'string', 'max:20'],
        ]);

        $regNo = strtoupper(trim((string) $validated['reg_no']));
        $registration = $this->loadCheckoutRegistration($regNo);

        if (!$registration || $registration['active_details']->isEmpty()) {
            return $this->respondError($request, 'Data checkout aktif tidak ditemukan.', 404, [], '/checkout', false);
        }

        $checkoutAt = $this->combineDateTime($validated['checkout_date'], $validated['checkout_time']);
        $checkoutDate = $checkoutAt->format('Y-m-d');
        $checkoutTime = $checkoutAt->format('H:i:s');
        $checkoutStamp = $this->buildStrTgl($checkoutDate, $checkoutTime);
        $invoiceNo = $this->ensureCheckoutInvoiceNo($regNo);
        $primaryDetail = $registration['active_details']->first();
        $statusLabel = 'LEAVED';
        $username = strtoupper(trim((string) session('user', 'SYSTEM')));

        DB::transaction(function () use (
            $regNo,
            $registration,
            $checkoutDate,
            $checkoutTime,
            $checkoutStamp,
            $invoiceNo,
            $primaryDetail,
            $statusLabel,
            $username,
            $checkoutAt
        ) {
            foreach ($registration['active_details'] as $detail) {
                $hari = $this->calculateHariForDetail($detail, $checkoutAt);
                $payload = [
                    'TglOut' => $checkoutDate,
                    'JamOut' => $checkoutTime,
                    'Pst' => '*',
                    'Hari' => $hari,
                    'strTglOut' => $checkoutStamp,
                    'Status' => $statusLabel,
                ];

                if (Schema::hasColumn('DATA2', 'Dispensasi')) {
                    $payload['Dispensasi'] = 0;
                }

                DB::table('DATA2')
                    ->whereRaw('RTRIM(RegNo) = ?', [$regNo])
                    ->whereRaw('RTRIM(Kode) = ?', [trim((string) ($detail->Kode ?? ''))])
                    ->where('Pst', '=', ' ')
                    ->update($payload);

                DB::table('ROOM')
                    ->whereRaw('RTRIM(Kode) = ?', [trim((string) ($detail->Kode ?? ''))])
                    ->update([
                        'Status' => 'Vacant Dirty',
                        'Status2' => null,
                        'StatusC' => $statusLabel,
                    ]);

                if (Schema::hasTable('LogRoom')) {
                    $logPayload = [
                        'Status' => 'Check Out',
                        'Kode' => trim((string) ($detail->Kode ?? '')),
                        'Tgl' => $checkoutDate,
                        'Jam' => $checkoutTime,
                    ];

                    if (Schema::hasColumn('LogRoom', 'UserId')) {
                        $logPayload['UserId'] = $username;
                    }

                    DB::table('LogRoom')->insert($logPayload);
                }
            }

            $dataPayload = [
                'strTglOut' => $checkoutStamp,
                'TglOut' => $checkoutDate,
                'UserOut' => $username,
                'CONo' => $invoiceNo,
                'Kode2' => trim((string) ($primaryDetail->Kode ?? '')),
            ];

            DB::table('DATA')
                ->whereRaw('RTRIM(RegNo) = ?', [$regNo])
                ->update($dataPayload);
        });

        $payload = [
            'reg_no' => $regNo,
            'invoice_no' => $invoiceNo,
            'checkout_at' => $checkoutAt->format('Y-m-d H:i:s'),
            'print_url' => url('/checkout/' . rawurlencode($regNo) . '/print-folio?mode=checkout&checkout_date=' . rawurlencode($checkoutDate) . '&checkout_time=' . rawurlencode($checkoutTime)),
        ];

        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'message' => 'Checkout berhasil disimpan.',
                'data' => $payload,
            ]);
        }

        return redirect('/checkout/' . rawurlencode($regNo) . '/print-folio?mode=checkout&checkout_date=' . rawurlencode($checkoutDate) . '&checkout_time=' . rawurlencode($checkoutTime))
            ->with('success', 'Checkout berhasil disimpan.');
    }

    public function printFolio(Request $request, string $regNo)
    {
        $normalizedRegNo = strtoupper(trim((string) $regNo));
        $registration = $this->loadCheckoutRegistration($normalizedRegNo, false);

        if (!$registration) {
            return $this->respondError($request, 'Guest folio tidak ditemukan.', 404, [], '/checkout', false);
        }

        $checkoutDate = (string) $request->query('checkout_date', now()->format('Y-m-d'));
        $checkoutTime = (string) $request->query('checkout_time', now()->format('H:i:s'));
        $mode = strtolower(trim((string) $request->query('mode', 'preview'))) === 'checkout' ? 'CHECK OUT' : 'PREVIEW';
        $folio = $this->buildFolioPayload($registration, $this->combineDateTime($checkoutDate, $checkoutTime), $mode);

        $viewData = [
            'profile' => $this->loadHotelProfile(),
            'registration' => $registration,
            'folio' => $folio,
            'mode' => $mode,
            'cashierName' => strtoupper(trim((string) session('user', 'SYSTEM'))),
            'qrCodeDataUri' => $this->buildCheckoutQrCodeDataUri($registration, $folio, $checkoutDate, $checkoutTime),
        ];

        return $this->respond($request, 'checkout.print-folio', $viewData, [
            'registration' => $registration,
            'folio' => $folio,
            'mode' => $mode,
        ]);
    }

    private function loadHotelProfile(): array
    {
        $profile = HotelBranding::profile();
        $profile['logo_url'] = !empty($profile['logo_path'])
            ? url('/settings/hotel-branding/logo?ts=' . rawurlencode((string) ($profile['updated_at'] ?? now()->timestamp)))
            : null;

        return $profile;
    }

    private function loadActiveCheckoutDirectory(string $search, int $perPage, Request $request, string $sortBy, string $sortDir): LengthAwarePaginator
    {
        $rows = DB::table('DATA2')
            ->leftJoin('DATA', 'DATA.RegNo', '=', 'DATA2.RegNo')
            ->selectRaw("
                RTRIM(DATA2.RegNo) as RegNo,
                RTRIM(DATA2.RegNo2) as RegNo2,
                RTRIM(DATA2.Kode) as Kode,
                RTRIM(DATA2.Guest) as Guest,
                RTRIM(DATA2.Tipe) as Tipe,
                RTRIM(DATA2.Package) as Package,
                RTRIM(DATA2.Payment) as Payment,
                RTRIM(DATA2.Usaha) as Usaha,
                RTRIM(DATA2.Remark) as Remark,
                DATA2.TglIn as TglIn,
                DATA2.TglKeluar as TglKeluar,
                DATA2.JamIn as JamIn,
                DATA2.Nominal as Nominal,
                RTRIM(DATA.CONo) as CONo
            ")
            ->where('DATA2.Pst', '=', ' ')
            ->whereRaw("RTRIM(DATA2.Kode) <> '999'")
            ->orderByDesc('DATA2.TglIn')
            ->orderByDesc('DATA2.RegNo2')
            ->get();

        $directoryRows = $rows
            ->map(function ($row) {
                $row->room_count = 1;
                $row->check_in_date = !empty($row->TglIn) ? Carbon::parse($row->TglIn)->format('d-m-Y') : '';
                $row->check_out_date = !empty($row->TglKeluar) ? Carbon::parse($row->TglKeluar)->format('d-m-Y') : '';
                $row->nominal_display = number_format((float) ($row->Nominal ?? 0), 0, ',', '.');
                $row->room_list = trim((string) ($row->Kode ?? ''));

                return $row;
            })
            ->values();

        if ($search !== '') {
            $keyword = strtoupper($search);
            $directoryRows = $directoryRows->filter(function ($row) use ($keyword) {
                $haystacks = [
                    trim((string) ($row->RegNo ?? '')),
                    trim((string) ($row->RegNo2 ?? '')),
                    trim((string) ($row->Kode ?? '')),
                    trim((string) ($row->Guest ?? '')),
                    trim((string) ($row->Usaha ?? '')),
                    trim((string) ($row->Package ?? '')),
                ];

                foreach ($haystacks as $value) {
                    if (str_contains(strtoupper($value), $keyword)) {
                        return true;
                    }
                }

                return false;
            })->values();
        }

        $sortCallback = match ($sortBy) {
            'reg_no' => fn ($row) => trim((string) ($row->RegNo ?? '')),
            'guest' => fn ($row) => trim((string) ($row->Guest ?? '')),
            'room' => fn ($row) => trim((string) ($row->Kode ?? '')),
            'nominal' => fn ($row) => (float) ($row->Nominal ?? 0),
            default => fn ($row) => (
                (!empty($row->TglIn) ? Carbon::parse($row->TglIn)->format('Y-m-d') : '0000-00-00')
                . ' '
                . (!empty($row->JamIn) ? (string) $row->JamIn : '00:00:00')
            ),
        };

        $directoryRows = $sortDir === 'asc'
            ? $directoryRows->sortBy($sortCallback, SORT_NATURAL)->values()
            : $directoryRows->sortByDesc($sortCallback, SORT_NATURAL)->values();

        return $this->paginateCollection($directoryRows, $perPage, $request);
    }

    private function loadCheckoutRegistration(string $regNo, bool $activeOnly = true): ?array
    {
        $header = DB::table('DATA')
            ->selectRaw("
                RTRIM(RegNo) as RegNo,
                RTRIM(CONo) as CONo,
                RTRIM(KodeCust) as KodeCust,
                RTRIM(UserIn) as UserIn,
                RTRIM(UserOut) as UserOut,
                RTRIM(Kode2) as Kode2,
                Deposit,
                TglIn,
                TglOut,
                strTgl,
                strTglOut
            ")
            ->whereRaw('RTRIM(RegNo) = ?', [$regNo])
            ->first();

        if (!$header) {
            return null;
        }

        $detailQuery = DB::table('DATA2')
            ->selectRaw("
                RTRIM(RegNo) as RegNo,
                RTRIM(RegNo2) as RegNo2,
                RTRIM(Kode) as Kode,
                RTRIM(Guest) as Guest,
                RTRIM(KTP) as KTP,
                RTRIM(Kota) as Kota,
                RTRIM(Alamat) as Alamat,
                RTRIM(Kelurahan) as Kelurahan,
                RTRIM(Kecamatan) as Kecamatan,
                RTRIM(Propinsi) as Propinsi,
                RTRIM(KodeNegara) as KodeNegara,
                RTRIM(Usaha) as Usaha,
                RTRIM(Profesi) as Profesi,
                RTRIM(Remark) as Remark,
                RTRIM(Package) as Package,
                RTRIM(Payment) as Payment,
                RTRIM(Tipe) as Tipe,
                RTRIM(Posisi) as Posisi,
                RTRIM(Receipt) as Receipt,
                RTRIM(Closing) as Closing,
                RTRIM(Status) as Status,
                TglIn,
                TglOut,
                JamIn,
                JamOut,
                TglKeluar,
                TglClosing,
                JamClosing,
                Short,
                Rate1,
                Rate2,
                Rate3,
                Rate4,
                Disc,
                Nominal,
                Plot,
                Dispensasi
            ")
            ->whereRaw('RTRIM(RegNo) = ?', [$regNo]);

        if ($activeOnly) {
            $detailQuery->where('Pst', '=', ' ');
        }

        $details = $detailQuery
            ->orderBy('TglIn')
            ->orderBy('RegNo2')
            ->get();

        if ($details->isEmpty()) {
            return null;
        }

        $allDetails = DB::table('DATA2')
            ->selectRaw("RTRIM(RegNo) as RegNo, RTRIM(RegNo2) as RegNo2, RTRIM(Kode) as Kode, RTRIM(Guest) as Guest, RTRIM(Usaha) as Usaha, RTRIM(Remark) as Remark, RTRIM(Payment) as Payment, RTRIM(Tipe) as Tipe, RTRIM(Receipt) as Receipt, TglIn, TglOut, JamIn, JamOut, TglKeluar, Nominal")
            ->whereRaw('RTRIM(RegNo) = ?', [$regNo])
            ->orderBy('TglIn')
            ->orderBy('RegNo2')
            ->get();

        $primary = $details->first();
        $roomCodes = $details->pluck('Kode')->map(fn ($value) => trim((string) $value))->filter()->values()->all();

        return [
            'header' => $header,
            'details' => $allDetails,
            'active_details' => $details,
            'primary' => $primary,
            'reg_no' => $regNo,
            'room_codes' => $roomCodes,
            'room_count' => count($roomCodes),
            'invoice_no' => $this->existingCheckoutInvoiceNo($regNo),
            'invoice_display' => $this->formatInvoiceDisplay($this->existingCheckoutInvoiceNo($regNo), $primary->TglIn ?? null),
            'guest' => trim((string) ($primary->Guest ?? '')),
            'company' => trim((string) ($primary->Usaha ?? '')),
            'address' => trim((string) ($primary->Alamat ?? $primary->Kota ?? '')),
            'remark' => trim((string) ($primary->Remark ?? '')),
            'payment' => trim((string) ($primary->Payment ?? '')),
            'type' => trim((string) ($primary->Tipe ?? '')),
            'room_label' => implode(', ', $roomCodes),
            'check_in_date' => !empty($primary->TglIn) ? Carbon::parse($primary->TglIn)->format('d-m-Y') : '',
            'check_out_date' => !empty($primary->TglKeluar) ? Carbon::parse($primary->TglKeluar)->format('d-m-Y') : '',
            'check_in_time' => $this->displayTime($primary->JamIn ?? null),
            'check_out_time' => $this->displayTime($primary->JamOut ?? null),
            'nominal_total' => (float) $details->sum(fn ($row) => (float) ($row->Nominal ?? 0)),
        ];
    }

    private function buildFolioPayload(array $registration, Carbon $checkoutAt, string $mode): array
    {
        $invoiceNo = $registration['invoice_no'] ?: $this->previewCheckoutInvoiceNo($registration['reg_no']);
        $invoiceDisplay = $this->formatInvoiceDisplay($invoiceNo, $registration['primary']->TglIn ?? null) . ' ' . $mode;
        $lines = [];
        $balance = 0.0;
        $debitTotal = 0.0;
        $creditTotal = 0.0;
        $transactionTotal = 0.0;
        $roomCount = (int) ($registration['room_count'] ?? 0);

        $pushLine = function (
            $date,
            string $invoice,
            string $description,
            float $debit,
            float $credit,
            string $code = ''
        ) use (&$lines, &$balance, &$debitTotal, &$creditTotal, &$transactionTotal) {
            $debit = round($debit, 2);
            $credit = round($credit, 2);
            $balance = round($balance + $credit - $debit, 2);
            $debitTotal += $debit;
            $creditTotal += $credit;
            $transactionTotal += $debit;
            $lineDate = $date instanceof Carbon
                ? $date->format('d-m-Y')
                : ($date ? Carbon::parse($date)->format('d-m-Y') : '');

            $lines[] = [
                'date' => $lineDate,
                'invoice' => $invoice,
                'description' => trim($description),
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $balance,
                'code' => $code,
            ];
        };

        $activeDetails = $registration['active_details'];
        $primary = $registration['primary'];

        $headerDeposit = (float) ($registration['header']->Deposit ?? 0);
        if ($headerDeposit !== 0.0) {
            $pushLine(
                $primary->TglIn ?? $checkoutAt,
                $this->crNofak($registration['reg_no']),
                $this->crJudul('Room Deposit', $primary->Posisi ?? ''),
                0,
                $headerDeposit,
                'A'
            );
        }

        $roomDiscountTotal = 0.0;
        foreach ($activeDetails as $detail) {
            $roomComputation = $this->buildRoomPackageChargeLines($registration['reg_no'], $detail, $checkoutAt);
            foreach ($roomComputation['lines'] as $line) {
                $pushLine(
                    $line['date'],
                    $line['invoice'],
                    $line['description'],
                    $line['debit'],
                    $line['credit'],
                    $line['code']
                );
            }
            $roomDiscountTotal += $roomComputation['discount_total'];
        }

        $extraBedRows = DB::table('EXTRABED')
            ->join('EXTRABEDD', 'EXTRABED.Nofak', '=', 'EXTRABEDD.Nofak')
            ->selectRaw("EXTRABED.Tgl as Tgl, RTRIM(EXTRABEDD.Nofak) as Nofak, RTRIM(EXTRABED.Kode) as Kode, RTRIM(EXTRABEDD.Ket) as Ket, EXTRABEDD.Nominal as Nominal")
            ->whereRaw('RTRIM(EXTRABED.RegNo) = ?', [$registration['reg_no']])
            ->orderBy('EXTRABED.Tgl')
            ->get();
        foreach ($extraBedRows as $row) {
            $pushLine($row->Tgl, $this->crNofak((string) $row->Nofak), $this->crJudul((string) $row->Ket, (string) $row->Kode), (float) ($row->Nominal ?? 0), 0, 'I2');
        }

        foreach ($this->loadCategoryCharges('MINI', 'MINID', 'MINI', 'MINID', 'J', 'Mini Bar', $registration['reg_no']) as $row) {
            $pushLine($row['date'], $row['invoice'], $row['description'], $row['debit'], 0, 'J');
        }

        foreach ($this->loadCategoryCharges('RES', 'RESD', 'RES', 'RESD', 'K', 'Coffe Lounge', $registration['reg_no']) as $row) {
            $pushLine($row['date'], $row['invoice'], $row['description'], $row['debit'], 0, 'K');
        }

        foreach ($this->loadCategoryCharges('RES2', 'RESD2', 'RES2', 'RESD2', 'K01', 'Restaurant', $registration['reg_no']) as $row) {
            $pushLine($row['date'], $row['invoice'], $row['description'], $row['debit'], 0, 'K01');
        }

        foreach ($this->loadCategoryCharges('BANQUET', 'BANQUETD', 'BANQUET', 'BANQUETD', 'J1', 'Banquet', $registration['reg_no']) as $row) {
            $pushLine($row['date'], $row['invoice'], $row['description'], $row['debit'], 0, 'J1');
        }

        foreach ($this->loadCategoryCharges('CUCI', 'CUCID', 'CUCI', 'CUCID', 'L', 'Laundry', $registration['reg_no']) as $row) {
            $pushLine($row['date'], $row['invoice'], $row['description'], $row['debit'], 0, 'L');
        }

        $otherRows = DB::table('TAMBAH')
            ->join('TAMBAHD', 'TAMBAH.Nofak', '=', 'TAMBAHD.Nofak')
            ->selectRaw("TAMBAH.Tgl as Tgl, RTRIM(TAMBAHD.Nofak) as Nofak, RTRIM(TAMBAH.Kode) as Kode, RTRIM(TAMBAHD.Ket) as Ket, TAMBAHD.Nominal as Nominal")
            ->whereRaw('RTRIM(TAMBAH.RegNo) = ?', [$registration['reg_no']])
            ->orderBy('TAMBAH.Tgl')
            ->get();
        foreach ($otherRows as $row) {
            $pushLine($row->Tgl, $this->crNofak((string) $row->Nofak), $this->crJudul((string) $row->Ket, (string) $row->Kode), (float) ($row->Nominal ?? 0), 0, 'M');
        }

        foreach ($this->loadTelephoneChargeRows($registration['reg_no'], $checkoutAt) as $row) {
            $pushLine($row['date'], $row['invoice'], $row['description'], $row['debit'], 0, $row['code']);
        }

        foreach ($this->loadCorrectionRows($registration['reg_no']) as $row) {
            $pushLine($row['date'], $row['invoice'], $row['description'], $row['debit'], $row['credit'], $row['code']);
        }

        if ($roomDiscountTotal > 0) {
            $pushLine(
                $checkoutAt,
                $this->crNofak($registration['reg_no']),
                'Room Disc= ' . $this->buildDiscountCaption($activeDetails) . ' %',
                0,
                $roomDiscountTotal,
                'P'
            );
        }

        foreach ($this->loadPaymentRows($registration['reg_no']) as $row) {
            $pushLine($row['date'], $row['invoice'], $row['description'], 0, $row['credit'], $row['code']);
        }

        foreach ($this->loadRefundRows($registration['reg_no']) as $row) {
            $pushLine($row['date'], $row['invoice'], $row['description'], 0, $row['credit'], $row['code']);
        }

        if (round($balance, 2) < 0) {
            $pushLine($checkoutAt, $this->crNofak($registration['reg_no']), 'Balanced To Payment', 0, abs($balance), 'ZZZZZ');
        } elseif (round($balance, 2) > 0) {
            $pushLine($checkoutAt, $this->crNofak($registration['reg_no']), 'Balanced To Refund', abs($balance), 0, 'ZZZZZ');
        }

        return [
            'invoice_no' => $invoiceNo,
            'invoice_display' => trim($invoiceDisplay),
            'lines' => $lines,
            'totals' => [
                'debit' => round($debitTotal, 2),
                'credit' => round($creditTotal, 2),
                'balance' => round($debitTotal - $creditTotal, 2),
                'transaction' => round($transactionTotal, 2),
            ],
            'note' => '[' . number_format($roomCount, 0, ',', '.') . ' R#]',
            'checkout_at' => $checkoutAt->format('Y-m-d H:i:s'),
            'guest_name' => trim((string) ($registration['guest'] ?? '')),
        ];
    }

    private function buildCheckoutQrCodeDataUri(array $registration, array $folio, string $checkoutDate, string $checkoutTime): string
    {
        $payload = implode("\n", [
            'RegNo: ' . trim((string) ($registration['reg_no'] ?? '')),
            'Room: ' . trim((string) ($registration['room_label'] ?? '')),
            'TglIn: ' . trim((string) ($registration['check_in_date'] ?? '')) . ' ' . trim((string) ($registration['check_in_time'] ?? '')),
            'TglOut: ' . trim($checkoutDate) . ' ' . trim($checkoutTime),
            'Nominal: ' . number_format((float) ($folio['totals']['transaction'] ?? 0), 0, '.', ','),
        ]);

        $writer = new PngWriter();
        $qrCode = new QrCode(
            data: $payload,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 170,
            margin: 4
        );

        return $writer->write($qrCode)->getDataUri();
    }

    private function buildRoomPackageChargeLines(string $regNo, object $detail, Carbon $checkoutAt): array
    {
        $effectiveOutDate = $this->resolveEffectiveOutDate($detail, $checkoutAt);
        $effectiveOutTime = $this->resolveEffectiveOutTime($detail, $checkoutAt);
        $days = $this->cariHari($detail->TglIn, $effectiveOutDate, $detail->JamIn, $effectiveOutTime);
        $days = max($days, 1);
        $runningDate = Carbon::parse($detail->TglIn);
        $checkInDate = Carbon::parse($detail->TglIn);
        $checkInTime = $this->parseTime($detail->JamIn ?? '00:00:00');
        $plot = (int) ($detail->Plot ?? 0);
        $nominal = (float) ($detail->Nominal ?? 0);
        $discount = 0.0;
        $lines = [];

        for ($sequence = 1; $sequence <= $days; $sequence++) {
            $sameDayOutTime = $sequence === $days ? $effectiveOutTime : $checkInTime;
            $kodeKamar = $this->crKodeKamar(
                $runningDate,
                $checkInDate,
                $checkInTime,
                trim((string) ($detail->Kode ?? '')),
                $regNo,
                trim((string) ($detail->RegNo2 ?? ''))
            );

            $roomAmount = $this->hrgPerKamar2012(
                $runningDate,
                $checkInDate,
                $checkInTime,
                trim((string) ($detail->RegNo2 ?? '')),
                $kodeKamar,
                $regNo,
                $nominal,
                $plot,
                trim((string) ($detail->Package ?? ''))
            );

            $discount += $this->crPotongan(
                $runningDate,
                (float) ($detail->Rate2 ?? 0),
                (float) ($detail->Disc ?? 0),
                $checkInDate,
                $checkInTime,
                $kodeKamar,
                $regNo
            );

            $shouldPrint = true;
            if ($plot === 1 && $days > 1) {
                $shouldPrint = $sequence % 2 === 0;
            }

            if ($shouldPrint) {
                $lines[] = [
                    'date' => $runningDate->copy(),
                    'invoice' => $this->crNofak($regNo),
                    'description' => $plot === 1 ? 'Room Package 2 Days ' . trim($kodeKamar) : 'Room Package ' . trim($kodeKamar),
                    'debit' => $roomAmount,
                    'credit' => 0.0,
                    'code' => 'B',
                ];
            }

            if ($sequence < $days) {
                $runningDate->addDay();
            }
        }

        $lateCharge = $this->roomLebih2013(
            $checkInDate,
            $effectiveOutDate,
            $checkInTime,
            $effectiveOutTime,
            $nominal,
            $plot
        );

        if ($lateCharge !== 0.0) {
            $lateLabel = 'Late ' . trim((string) ($detail->Kode ?? '')) . ' [' . $effectiveOutTime->format('H:i') . ']';
            $lines[] = [
                'date' => $effectiveOutDate->copy(),
                'invoice' => $this->crNofak($regNo),
                'description' => $lateLabel,
                'debit' => $lateCharge,
                'credit' => 0.0,
                'code' => 'E',
            ];
        }

        return [
            'lines' => $lines,
            'discount_total' => $discount,
        ];
    }

    private function loadCategoryCharges(
        string $headerTable,
        string $detailTable,
        string $headerAlias,
        string $detailAlias,
        string $code,
        string $defaultLabel,
        string $regNo
    ): array {
        $rows = DB::table($headerTable)
            ->join($detailTable, $headerTable . '.Nofak', '=', $detailTable . '.Nofak')
            ->selectRaw("
                {$headerTable}.Tgl as Tgl,
                RTRIM({$headerTable}.Nofak) as Nofak,
                RTRIM({$headerTable}.Kode) as Kode,
                SUM(({$detailTable}.Qty * {$detailTable}.Harga) - (({$detailTable}.Disc / 100.0) * ({$detailTable}.Qty * {$detailTable}.Harga))) as Nominal
            ")
            ->whereRaw("RTRIM({$headerTable}.RegNo) = ?", [$regNo])
            ->groupBy($headerTable . '.Tgl', $headerTable . '.Nofak', $headerTable . '.Kode')
            ->orderBy($headerTable . '.Tgl')
            ->get();

        return $rows->map(function ($row) use ($defaultLabel) {
            return [
                'date' => $row->Tgl,
                'invoice' => $this->crNofak((string) $row->Nofak),
                'description' => $this->crJudul($defaultLabel, (string) ($row->Kode ?? '')),
                'debit' => (float) ($row->Nominal ?? 0),
            ];
        })->all();
    }

    private function loadTelephoneChargeRows(string $regNo, Carbon $checkoutAt): array
    {
        $rows = [];
        $callRows = DB::table('CALL')
            ->selectRaw('RTRIM(CallRegNo2) as RegNo2, SUM(CallCost) as Cost')
            ->whereRaw('RTRIM(CallRegno) = ?', [$regNo])
            ->groupBy('CallRegNo2')
            ->get();

        foreach ($callRows as $row) {
            $roomCode = DB::table('DATA2')
                ->whereRaw('RTRIM(RegNo2) = ?', [trim((string) ($row->RegNo2 ?? ''))])
                ->value('Kode');

            $rows[] = [
                'date' => $checkoutAt->copy(),
                'invoice' => $this->crNofak($regNo),
                'description' => $this->crJudul('Local Call Room ', trim((string) ($roomCode ?? ''))),
                'debit' => (float) ($row->Cost ?? 0),
                'code' => 'N',
            ];
        }

        $manualRows = DB::table('TELP')
            ->join('TELPD', 'TELP.Nofak', '=', 'TELPD.Nofak')
            ->selectRaw("TELP.Tgl as Tgl, RTRIM(TELPD.Nofak) as Nofak, RTRIM(TELP.Kode) as Kode, RTRIM(TELPD.Ket) as Ket, TELPD.Nominal as Nominal")
            ->whereRaw('RTRIM(TELP.RegNo) = ?', [$regNo])
            ->orderBy('TELP.Tgl')
            ->get();

        foreach ($manualRows as $row) {
            $rows[] = [
                'date' => $row->Tgl,
                'invoice' => $this->crNofak((string) $row->Nofak),
                'description' => $this->crJudul((string) $row->Ket, (string) $row->Kode),
                'debit' => (float) ($row->Nominal ?? 0),
                'code' => 'M0',
            ];
        }

        return $rows;
    }

    private function loadCorrectionRows(string $regNo): array
    {
        if (!Schema::hasTable('Koreksi')) {
            return [];
        }

        $details = DB::table('DATA2')
            ->selectRaw('RTRIM(RegNo2) as RegNo2')
            ->whereRaw('RTRIM(RegNo) = ?', [$regNo])
            ->get();

        if ($details->isEmpty()) {
            return [];
        }

        $corrections = DB::table('Koreksi')
            ->whereIn(DB::raw('RTRIM(RegNo2)'), $details->pluck('RegNo2')->all())
            ->orderBy('Tgl')
            ->get();

        $mapping = [
            'BRoom2' => ['COR. ROOM', 'X01'],
            'BDiscount2' => ['COR. DISCOUNT', 'X02'],
            'BExtraBed2' => ['COR. EXTRA BED', 'X03'],
            'BMiniBar2' => ['COR. MINI BAR', 'X04'],
            'BCoffe2' => ['COR. COFFE LOUNGE', 'X05'],
            'BRestaurant2' => ['COR. Restaurant', 'X06'],
            'BLaundry2' => ['COR. LAUNDRY', 'X07'],
            'BOthers2' => ['COR. OTHERS', 'X08'],
            'BTelphone2' => ['COR. TELEPHONE', 'X09'],
            'BBarBerShop2' => ['COR. BARBER SHOP', 'X10'],
        ];

        $rows = [];
        foreach ($corrections as $correction) {
            foreach ($mapping as $column => [$label, $code]) {
                $amount = (float) ($correction->{$column} ?? 0);
                if ($amount === 0.0) {
                    continue;
                }

                $rows[] = [
                    'date' => $correction->Tgl,
                    'invoice' => $this->crNofak((string) ($correction->Nofak ?? $regNo)),
                    'description' => $this->crJudul($label, (string) ($correction->Kode ?? '')),
                    'debit' => $amount > 0 ? abs($amount) : 0.0,
                    'credit' => $amount < 0 ? abs($amount) : 0.0,
                    'code' => $code,
                ];
            }
        }

        return $rows;
    }

    private function loadPaymentRows(string $regNo): array
    {
        $rows = [];

        $payments = DB::table('KAS')
            ->selectRaw("Tgl, RTRIM(Kode) as Kode, RTRIM(Ket1) as Ket1, RTRIM(TipeBayar) as TipeBayar, Nominal")
            ->whereRaw('RTRIM(RegNo) = ?', [$regNo])
            ->where('Nominal', '<>', 0)
            ->orderBy('Tgl')
            ->get();

        foreach ($payments as $row) {
            $label = trim((string) ($row->Ket1 ?? ''));
            if ($label === '') {
                $label = 'Room Deposit';
            }

            $rows[] = [
                'date' => $row->Tgl,
                'invoice' => $this->crNofak($regNo),
                'description' => $this->crJudul($label, (string) ($row->Kode ?? '')) . '  By : ' . trim((string) ($row->TipeBayar ?? '')),
                'credit' => (float) ($row->Nominal ?? 0),
                'code' => 'Z',
            ];
        }

        if (Schema::hasTable('KASMUKA')) {
            $advances = DB::table('KASMUKA')
                ->selectRaw("TglC as Tgl, RTRIM(KodeC) as Kode, RTRIM(Ket1) as Ket1, RTRIM(TipeBayar) as TipeBayar, Nominal")
                ->whereRaw('RTRIM(RegNoC) = ?', [$regNo])
                ->where('Nominal', '<>', 0)
                ->orderBy('TglC')
                ->get();

            foreach ($advances as $row) {
                $label = trim((string) ($row->Ket1 ?? ''));
                if ($label === '') {
                    $label = 'Room Deposit [*]';
                }

                $rows[] = [
                    'date' => $row->Tgl,
                    'invoice' => $this->crNofak($regNo),
                    'description' => $this->crJudul($label, (string) ($row->Kode ?? '')) . '  By : ' . trim((string) ($row->TipeBayar ?? '')),
                    'credit' => (float) ($row->Nominal ?? 0),
                    'code' => 'Z1',
                ];
            }
        }

        return $rows;
    }

    private function loadRefundRows(string $regNo): array
    {
        if (!Schema::hasTable('KELUARH')) {
            return [];
        }

        $refunds = DB::table('KELUARH')
            ->selectRaw("Tgl, RTRIM(Nofak) as Nofak, RTRIM(Kode) as Kode, RTRIM(Ket1) as Ket1, Nominal")
            ->whereRaw('RTRIM(RegNo) = ?', [$regNo])
            ->where('Nominal', '<>', 0)
            ->orderBy('Tgl')
            ->get();

        return $refunds->map(function ($row) {
            $label = trim((string) ($row->Ket1 ?? ''));
            if ($label === '') {
                $label = 'Refund';
            }

            return [
                'date' => $row->Tgl,
                'invoice' => $this->crNofak((string) ($row->Nofak ?? '')),
                'description' => $this->crJudul($label, (string) ($row->Kode ?? '')),
                'credit' => -1 * (float) ($row->Nominal ?? 0),
                'code' => 'Z',
            ];
        })->all();
    }

    private function buildDiscountCaption(Collection $details): string
    {
        $captions = $details
            ->pluck('Disc')
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '' && $value !== '0')
            ->unique()
            ->values();

        if ($captions->isEmpty()) {
            return '0';
        }

        return $captions->implode(' % & ');
    }

    private function existingCheckoutInvoiceNo(string $regNo): string
    {
        $fromData = trim((string) (DB::table('DATA')->whereRaw('RTRIM(RegNo) = ?', [$regNo])->value('CONo') ?? ''));
        if ($fromData !== '') {
            return $fromData;
        }

        if (Schema::hasTable('DATACO')) {
            return trim((string) (DB::table('DATACO')->whereRaw('RTRIM(RegNo) = ?', [$regNo])->value('CONo') ?? ''));
        }

        return '';
    }

    private function ensureCheckoutInvoiceNo(string $regNo): string
    {
        $existing = $this->existingCheckoutInvoiceNo($regNo);
        if ($existing !== '') {
            return $existing;
        }

        $nextInvoice = $this->nextCheckoutInvoiceNo();

        if (Schema::hasTable('DATACO')) {
            DB::table('DATACO')->insert([
                'CONo' => $nextInvoice,
                'RegNo' => $regNo,
            ]);
        }

        return $nextInvoice;
    }

    private function previewCheckoutInvoiceNo(string $regNo): string
    {
        $existing = $this->existingCheckoutInvoiceNo($regNo);
        return $existing !== '' ? $existing : $this->nextCheckoutInvoiceNo();
    }

    private function nextCheckoutInvoiceNo(): string
    {
        $source = Schema::hasTable('DATACO') ? 'DATACO' : 'DATA';
        $column = 'CONo';
        $rows = DB::table($source)
            ->selectRaw("RTRIM($column) as CONo")
            ->whereRaw("LTRIM(RTRIM(ISNULL($column, ''))) <> ''")
            ->get();

        $max = $rows->map(function ($row) {
            $digits = preg_replace('/\D+/', '', trim((string) ($row->CONo ?? ''))) ?? '';
            return $digits !== '' ? (int) $digits : 0;
        })->max() ?? 0;

        return str_pad((string) ($max + 1), 10, '0', STR_PAD_LEFT);
    }

    private function formatInvoiceDisplay(string $invoiceNo, $checkInDate): string
    {
        if ($invoiceNo === '') {
            return '';
        }

        $checkIn = $checkInDate ? Carbon::parse($checkInDate) : now();
        $suffix = substr($invoiceNo, -4);

        return $suffix . '/' . $this->cariRomawi((int) $checkIn->format('n')) . '/' . $checkIn->format('Y');
    }

    private function calculateHariForDetail(object $detail, Carbon $checkoutAt): int
    {
        $effectiveOutDate = $this->resolveEffectiveOutDate($detail, $checkoutAt);
        $effectiveOutTime = $this->resolveEffectiveOutTime($detail, $checkoutAt);

        return max($this->cariHari($detail->TglIn, $effectiveOutDate, $detail->JamIn, $effectiveOutTime), 1);
    }

    private function resolveEffectiveOutDate(object $detail, Carbon $checkoutAt): Carbon
    {
        if (trim((string) ($detail->Pst ?? '')) === '*' && !empty($detail->TglOut)) {
            return Carbon::parse($detail->TglOut);
        }

        if (trim((string) ($detail->Closing ?? '')) === '*' && !empty($detail->TglClosing)) {
            return Carbon::parse($detail->TglClosing);
        }

        return $checkoutAt->copy();
    }

    private function resolveEffectiveOutTime(object $detail, Carbon $checkoutAt): Carbon
    {
        if (trim((string) ($detail->Pst ?? '')) === '*' && !empty($detail->JamOut)) {
            return $this->parseTime($detail->JamOut);
        }

        if (trim((string) ($detail->Closing ?? '')) === '*' && !empty($detail->JamClosing)) {
            return $this->parseTime($detail->JamClosing);
        }

        return $this->parseTime($checkoutAt->format('H:i:s'));
    }

    private function accountDayConfig(): array
    {
        $row = DB::table('SETUP')
            ->selectRaw('JamMasuk, JamBatas, JamBatas2, JamBatas3, KaliBatas, KaliBatas2, KaliBatas3')
            ->whereRaw("RTRIM(Kode) = '01'")
            ->first();

        return [
            'jam_masuk' => $this->timeString($row->JamMasuk ?? '00:00:00'),
            'jam_batas' => $this->timeString($row->JamBatas ?? '12:00:00'),
            'jam_batas2' => $this->timeString($row->JamBatas2 ?? '18:00:00'),
            'jam_batas3' => $this->timeString($row->JamBatas3 ?? '23:59:59'),
            'kali_batas' => (float) ($row->KaliBatas ?? 0),
            'kali_batas2' => (float) ($row->KaliBatas2 ?? 0),
            'kali_batas3' => (float) ($row->KaliBatas3 ?? 0),
        ];
    }

    private function cariHari($tglIn, $tglOut, $jamIn, $jamOut): int
    {
        $config = $this->accountDayConfig();
        $inDate = Carbon::parse($tglIn);
        $outDate = Carbon::parse($tglOut);
        $inTime = $this->timeString($jamIn);

        if ($inDate->isSameDay($outDate)) {
            return 1;
        }

        $days = $inDate->diffInDays($outDate);
        if ($inTime < $config['jam_masuk']) {
            $days++;
        }

        return max($days, 1);
    }

    private function lateChargePercent($tglIn, $tglOut, $jamIn, $jamOut): float
    {
        $config = $this->accountDayConfig();
        $inDate = Carbon::parse($tglIn);
        $outDate = Carbon::parse($tglOut);
        $inTime = $this->timeString($jamIn);
        $outTime = $this->timeString($jamOut);

        if ($inDate->isSameDay($outDate)) {
            if ($inTime < $config['jam_masuk'] && $outTime > $config['jam_batas']) {
                if ($outTime <= $config['jam_batas2']) {
                    return $config['kali_batas'];
                }

                if ($outTime <= $config['jam_batas3']) {
                    return $config['kali_batas2'];
                }

                return $config['kali_batas3'];
            }

            return 0;
        }

        if ($outTime > $config['jam_batas']) {
            if ($outTime <= $config['jam_batas2']) {
                return $config['kali_batas'];
            }

            if ($outTime <= $config['jam_batas3']) {
                return $config['kali_batas2'];
            }

            return $config['kali_batas3'];
        }

        return 0;
    }

    private function roomLebih2013(Carbon $tglIn, Carbon $tglOut, Carbon $jamIn, Carbon $jamOut, float $nominal, int $plot): float
    {
        $percent = $this->lateChargePercent($tglIn, $tglOut, $jamIn, $jamOut);
        if ($percent === 0.0) {
            return 0.0;
        }

        $base = $plot === 1 ? ($nominal / 2) : $nominal;

        return round(($percent / 100) * $base, 2);
    }

    private function hrgPerKamar2012(
        Carbon $dayDate,
        Carbon $checkInDate,
        Carbon $checkInTime,
        string $regNo2,
        string $kodeKamar,
        string $regNo,
        float $nominal,
        int $plot,
        string $packageCode
    ): float {
        $hargaKamar = $nominal;

        if (Schema::hasTable('PERIODEHARGA')) {
            $period = DB::table('PERIODEHARGA')
                ->selectRaw('RTRIM(PACKAGE) as Package, Nominal')
                ->whereRaw('RTRIM(Regno) = ?', [$regNo])
                ->whereRaw('RTRIM(Kode) = ?', [$kodeKamar])
                ->whereDate('TglIn', $checkInDate->format('Y-m-d'))
                ->whereRaw('RTRIM(Regno2) = ?', [$regNo2])
                ->whereDate('TglIn', '<=', $dayDate->format('Y-m-d'))
                ->whereDate('TglOut', '>=', $dayDate->format('Y-m-d'))
                ->first();

            if ($period) {
                return (float) ($period->Nominal ?? $hargaKamar);
            }
        }

        $move = DB::table('DataMove')
            ->selectRaw('RTRIM(PACKAGE) as Package, Nominal')
            ->whereRaw('RTRIM(Regno) = ?', [$regNo])
            ->whereRaw('RTRIM(Kode) = ?', [$kodeKamar])
            ->whereDate('TglIn', $checkInDate->format('Y-m-d'))
            ->whereDate('TglMove', $checkInDate->format('Y-m-d'))
            ->orderBy('TglMove')
            ->orderBy('JamMove')
            ->get();

        if ($move->isNotEmpty()) {
            $selectedMove = $move->count() > 1 && $checkInTime->format('H:i') >= '05:00' ? $move->last() : $move->first();
            $movePackage = trim((string) ($selectedMove->Package ?? '*'));
            $moveNominal = (float) ($selectedMove->Nominal ?? 0);

            if ($movePackage !== '*' && $movePackage !== '') {
                if ($moveNominal > 0) {
                    return $moveNominal;
                }

                $packageNominal = DB::table('Package')
                    ->whereRaw('RTRIM(Nofak) = ?', [$movePackage])
                    ->value('JumlahRes');

                if ($packageNominal !== null) {
                    return (float) $packageNominal;
                }
            }
        }

        return $hargaKamar;
    }

    private function crPotongan(
        Carbon $dayDate,
        float $rate2,
        float $disc,
        Carbon $checkInDate,
        Carbon $checkInTime,
        string $kode,
        string $regNo
    ): float {
        $move = DB::table('DataMove')
            ->selectRaw('Rate2, TglMove')
            ->whereRaw('RTRIM(Regno) = ?', [$regNo])
            ->whereDate('TglIn', $checkInDate->format('Y-m-d'))
            ->whereRaw('RTRIM(JamIn) = ?', [$checkInTime->format('H:i:s')])
            ->whereDate('TglMove', '<=', $dayDate->format('Y-m-d'))
            ->whereRaw('RTRIM(Kode) = ?', [$kode])
            ->orderBy('TglMove')
            ->get();

        $baseRate = $rate2;
        if ($move->isNotEmpty()) {
            $baseRate = (float) ($move->last()->Rate2 ?? $rate2);
        }

        return round($baseRate * ($disc / 100), 2);
    }

    private function crKodeKamar(
        Carbon $dayDate,
        Carbon $checkInDate,
        Carbon $checkInTime,
        string $defaultKode,
        string $regNo,
        string $regNo2
    ): string {
        $sameDateMoveCount = (int) (DB::table('DataMove')
            ->whereRaw('RTRIM(Regno2) = ?', [$regNo2])
            ->whereRaw('RTRIM(Regno) = ?', [$regNo])
            ->whereDate('TglMove', $dayDate->format('Y-m-d'))
            ->count() ?? 0);

        $moves = DB::table('DataMove')
            ->selectRaw('RTRIM(Kode) as Kode, TglMove, JamMove, JamIn')
            ->whereRaw('RTRIM(Regno) = ?', [$regNo])
            ->whereDate('TglIn', $checkInDate->format('Y-m-d'))
            ->whereRaw('RTRIM(JamIn) = ?', [$checkInTime->format('H:i:s')])
            ->whereDate('TglMove', '<=', $dayDate->format('Y-m-d'))
            ->whereRaw('RTRIM(Regno2) = ?', [$regNo2])
            ->orderBy('TglMove')
            ->orderBy('JamMove')
            ->get();

        if ($moves->isEmpty()) {
            return $defaultKode;
        }

        if ($sameDateMoveCount > 1) {
            $selected = $moves->last();
            $sameDayMoves = DB::table('DataMove')
                ->selectRaw('RTRIM(Kode) as Kode, JamIn, TglMove, JamMove')
                ->whereRaw('RTRIM(Regno) = ?', [$regNo])
                ->whereRaw('RTRIM(Regno2) = ?', [$regNo2])
                ->whereDate('TglMove', Carbon::parse($selected->TglMove)->format('Y-m-d'))
                ->orderBy('TglMove')
                ->orderBy('JamMove')
                ->get();

            if ($sameDayMoves->count() > 1) {
                $firstJamIn = $this->timeString($sameDayMoves->first()->JamIn ?? '00:00:00');
                if ($firstJamIn < $this->accountDayConfig()['jam_masuk']) {
                    return trim((string) ($sameDayMoves->last()->Kode ?? $defaultKode));
                }

                $currentRoom = DB::table('DATA2')
                    ->whereRaw('RTRIM(Regno) = ?', [$regNo])
                    ->whereRaw('RTRIM(Regno2) = ?', [$regNo2])
                    ->value('Kode');

                return trim((string) ($currentRoom ?? $sameDayMoves->last()->Kode ?? $defaultKode));
            }

            return trim((string) ($selected->Kode ?? $defaultKode));
        }

        foreach ($moves->reverse() as $move) {
            if ($dayDate->greaterThanOrEqualTo(Carbon::parse($move->TglMove))) {
                return trim((string) ($move->Kode ?? $defaultKode));
            }
        }

        return $defaultKode;
    }

    private function crNofak(string $nofak): string
    {
        return substr($nofak, 6, 4) . '-' . substr($nofak, 10, 4);
    }

    private function crJudul(string $judul, string $kode): string
    {
        $normalizedKode = trim($kode);

        return $normalizedKode === ''
            ? trim($judul)
            : trim($judul) . '-[' . $normalizedKode . ']';
    }

    private function cariRomawi(int $month): string
    {
        return match ($month) {
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
            default => 'I',
        };
    }

    private function combineDateTime(string $date, string $time): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $this->normalizeTime($time));
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

        return Carbon::parse($raw)->format('H:i:s');
    }

    private function displayTime($value): string
    {
        if (empty($value)) {
            return '';
        }

        return $this->parseTime($value)->format('H:i:s');
    }

    private function parseTime($value): Carbon
    {
        $time = $this->normalizeTime((string) $value);

        return Carbon::createFromFormat('H:i:s', $time);
    }

    private function timeString($value): string
    {
        return $this->parseTime($value)->format('H:i:s');
    }

    private function buildStrTgl(string $date, string $time): string
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time)->format('YmdHis');
    }
}
