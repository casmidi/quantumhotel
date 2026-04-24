<?php

namespace App\Http\Controllers;

use App\Support\HotelBranding;
use Barryvdh\DomPDF\Facade\Pdf;
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
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;
        $sortBy = $this->resolveCheckoutSortBy((string) $request->query('sort_by', 'check_in'));
        $sortDir = $this->resolveCheckoutSortDirection((string) $request->query('sort_dir', 'desc'));
        $checkoutScope = $this->resolveCheckoutScope((string) $request->query('checkout_scope', 'all'));
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
                'checkoutScope' => $checkoutScope,
                'selectedRegNo' => $selectedRegNo,
                'selectedRegNo2' => $selectedRegNo2,
            ])->render();
        }

        $directoryItems = collect($directory->items());
        if ($selectedRegNo === '' && $directoryItems->isNotEmpty()) {
            $selectedRegNo = trim((string) ($directoryItems->first()->RegNo ?? ''));
            $selectedRegNo2 = trim((string) ($directoryItems->first()->RegNo2 ?? ''));
        }

        if ($checkoutScope === 'room' && $selectedRegNo !== '' && $selectedRegNo2 === '') {
            $selectedRoom = $directoryItems->first(fn ($row) => trim((string) ($row->RegNo ?? '')) === $selectedRegNo);
            $selectedRegNo2 = trim((string) ($selectedRoom->RegNo2 ?? ''));
        }

        $selectedRegistration = $selectedRegNo !== '' ? $this->loadCheckoutRegistration(
            $selectedRegNo,
            true,
            $checkoutScope === 'room' ? $selectedRegNo2 : null
        ) : null;
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
            'checkoutScope' => $checkoutScope,
            'checkoutScopeLabel' => $checkoutScope === 'room' ? 'Checkout 1 Kamar' : 'Checkout Semua Kamar',
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
            'checkout_scope' => $checkoutScope,
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

    private function resolveCheckoutScope(string $scope): string
    {
        return strtolower(trim($scope)) === 'room' ? 'room' : 'all';
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reg_no' => ['required', 'string', 'max:30'],
            'reg_no2' => ['nullable', 'string', 'max:30'],
            'checkout_scope' => ['nullable', 'in:all,room'],
            'checkout_date' => ['required', 'date_format:Y-m-d'],
            'checkout_time' => ['required', 'string', 'max:20'],
        ]);

        $regNo = strtoupper(trim((string) $validated['reg_no']));
        $selectedRegNo2 = strtoupper(trim((string) ($validated['reg_no2'] ?? '')));
        $checkoutScope = $this->resolveCheckoutScope((string) ($validated['checkout_scope'] ?? 'all'));

        if ($checkoutScope === 'room' && $selectedRegNo2 === '') {
            return $this->respondError($request, 'Pilih kamar group yang akan di-checkout.', 422, [], '/checkout?checkout_scope=room&reg_no=' . rawurlencode($regNo), false);
        }

        $registration = $this->loadCheckoutRegistration(
            $regNo,
            true,
            $checkoutScope === 'room' ? $selectedRegNo2 : null
        );

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
            $checkoutAt,
            $checkoutScope
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
                    ->whereRaw('RTRIM(RegNo2) = ?', [trim((string) ($detail->RegNo2 ?? ''))])
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

            $hasRemainingActiveRooms = DB::table('DATA2')
                ->whereRaw('RTRIM(RegNo) = ?', [$regNo])
                ->where('Pst', '=', ' ')
                ->exists();

            if ($checkoutScope === 'all' || !$hasRemainingActiveRooms) {
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
            }
        });

        $printQuery = [
            'mode' => 'checkout',
            'checkout_date' => $checkoutDate,
            'checkout_time' => $checkoutTime,
            'checkout_scope' => $checkoutScope,
        ];

        if ($checkoutScope === 'room') {
            $printQuery['reg_no2'] = $selectedRegNo2;
        }

        $payload = [
            'reg_no' => $regNo,
            'reg_no2' => $checkoutScope === 'room' ? $selectedRegNo2 : null,
            'checkout_scope' => $checkoutScope,
            'invoice_no' => $invoiceNo,
            'checkout_at' => $checkoutAt->format('Y-m-d H:i:s'),
            'print_url' => url('/checkout/' . rawurlencode($regNo) . '/print-folio?' . http_build_query($printQuery)),
        ];

        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'message' => $checkoutScope === 'room' ? 'Checkout kamar berhasil disimpan.' : 'Checkout berhasil disimpan.',
                'data' => $payload,
            ]);
        }

        return redirect('/checkout/' . rawurlencode($regNo) . '/print-folio?' . http_build_query($printQuery))
            ->with('success', $checkoutScope === 'room' ? 'Checkout kamar berhasil disimpan.' : 'Checkout berhasil disimpan.');
    }

    public function printFolio(Request $request, string $regNo)
    {
        $normalizedRegNo = strtoupper(trim((string) $regNo));
        $checkoutDate = (string) $request->query('checkout_date', now()->format('Y-m-d'));
        $checkoutTime = (string) $request->query('checkout_time', now()->format('H:i:s'));
        $checkoutScope = $this->resolveCheckoutScope((string) $request->query('checkout_scope', 'all'));
        $selectedRegNo2 = strtoupper(trim((string) $request->query('reg_no2', '')));

        if ($checkoutScope === 'room' && $selectedRegNo2 === '') {
            return $this->respondError($request, 'Pilih kamar group yang akan ditampilkan.', 422, [], '/checkout?checkout_scope=room&reg_no=' . rawurlencode($normalizedRegNo), false);
        }

        $registration = $this->loadCheckoutRegistration(
            $normalizedRegNo,
            false,
            $checkoutScope === 'room' ? $selectedRegNo2 : null
        );

        if (!$registration) {
            return $this->respondError($request, 'Guest folio tidak ditemukan.', 404, [], '/checkout', false);
        }

        $mode = strtolower(trim((string) $request->query('mode', 'preview'))) === 'checkout' ? 'CHECK OUT' : 'PREVIEW';
        $folio = $this->buildFolioPayload($registration, $this->combineDateTime($checkoutDate, $checkoutTime), $mode);

        $viewData = [
            'profile' => $this->loadHotelProfile(),
            'registration' => $registration,
            'folio' => $folio,
            'mode' => $mode,
            'cashierName' => strtoupper(trim((string) session('user', 'SYSTEM'))),
            'qrCodeDataUri' => $this->buildCheckoutQrCodeDataUri($registration, $folio, $checkoutDate, $checkoutTime),
            'excelUrl' => $this->folioExportUrl($registration['reg_no'], $checkoutDate, $checkoutTime, $mode, 'excel', $checkoutScope, $selectedRegNo2),
            'pdfUrl' => $this->folioExportUrl($registration['reg_no'], $checkoutDate, $checkoutTime, $mode, 'pdf', $checkoutScope, $selectedRegNo2),
        ];

        return $this->respond($request, 'checkout.print-folio', $viewData, [
            'registration' => $registration,
            'folio' => $folio,
            'mode' => $mode,
        ]);
    }

    public function exportFolio(Request $request, string $regNo, string $format)
    {
        $normalizedRegNo = strtoupper(trim((string) $regNo));
        $checkoutDate = (string) $request->query('checkout_date', now()->format('Y-m-d'));
        $checkoutTime = (string) $request->query('checkout_time', now()->format('H:i:s'));
        $checkoutScope = $this->resolveCheckoutScope((string) $request->query('checkout_scope', 'all'));
        $selectedRegNo2 = strtoupper(trim((string) $request->query('reg_no2', '')));

        if ($checkoutScope === 'room' && $selectedRegNo2 === '') {
            return $this->respondError($request, 'Pilih kamar group yang akan diexport.', 422, [], '/checkout?checkout_scope=room&reg_no=' . rawurlencode($normalizedRegNo), false);
        }

        $registration = $this->loadCheckoutRegistration(
            $normalizedRegNo,
            false,
            $checkoutScope === 'room' ? $selectedRegNo2 : null
        );

        if (!$registration) {
            return $this->respondError($request, 'Guest folio tidak ditemukan.', 404, [], '/checkout', false);
        }

        $mode = strtolower(trim((string) $request->query('mode', 'preview'))) === 'checkout' ? 'CHECK OUT' : 'PREVIEW';
        $folio = $this->buildFolioPayload($registration, $this->combineDateTime($checkoutDate, $checkoutTime), $mode);
        $format = strtolower(trim($format));
        $profile = $this->loadHotelProfile();
        $viewData = [
            'profile' => $profile,
            'registration' => $registration,
            'folio' => $folio,
            'mode' => $mode,
            'cashierName' => strtoupper(trim((string) session('user', 'SYSTEM'))),
            'qrCodeDataUri' => $this->buildCheckoutQrCodeDataUri($registration, $folio, $checkoutDate, $checkoutTime),
            'logoDataUri' => $this->localImageDataUri($profile['logo_absolute_path'] ?? null),
        ];

        if ($format === 'pdf') {
            $fileName = $this->folioExportFileName($registration['reg_no'], $folio, 'pdf');
            $pdf = Pdf::loadView('checkout.export-folio-pdf', $viewData)
                ->setPaper('a4', 'portrait')
                ->setOption([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => false,
                    'defaultFont' => 'Arial',
                ]);

            return $pdf->stream($fileName);
        }

        if ($format !== 'excel') {
            return $this->respondError($request, 'Format export folio tidak dikenal.', 404, [], '/checkout', false);
        }

        return $this->downloadFolioSpreadsheet(
            $profile,
            $registration,
            $folio,
            $viewData['cashierName'],
            $viewData['qrCodeDataUri'],
            $profile['logo_absolute_path'] ?? null
        );
    }

    private function folioExportUrl(
        string $regNo,
        string $checkoutDate,
        string $checkoutTime,
        string $mode,
        string $format,
        string $checkoutScope = 'all',
        ?string $regNo2 = null
    ): string
    {
        $query = [
            'mode' => $mode === 'CHECK OUT' ? 'checkout' : 'preview',
            'checkout_date' => $checkoutDate,
            'checkout_time' => $checkoutTime,
            'checkout_scope' => $checkoutScope,
        ];

        if ($checkoutScope === 'room' && trim((string) $regNo2) !== '') {
            $query['reg_no2'] = trim((string) $regNo2);
        }

        return url('/checkout/' . rawurlencode($regNo) . '/export-folio/' . $format) . '?' . http_build_query($query);
    }

    private function folioExportFileName(string $regNo, array $folio, string $extension): string
    {
        $safeRegNo = preg_replace('/[^A-Za-z0-9_-]+/', '-', trim($regNo)) ?: 'folio';
        $checkoutAt = Carbon::createFromFormat('Y-m-d H:i:s', $folio['checkout_at']);

        return 'guest-folio-' . $safeRegNo . '-' . $checkoutAt->format('Ymd-His') . '.' . $extension;
    }

    private function downloadFolioSpreadsheet(
        array $profile,
        array $registration,
        array $folio,
        string $cashierName,
        string $qrCodeDataUri,
        ?string $logoPath
    ) {
        $checkOutAt = Carbon::createFromFormat('Y-m-d H:i:s', $folio['checkout_at']);
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Quantum Hotel')
            ->setTitle('Guest Folio ' . $registration['reg_no'])
            ->setSubject($folio['invoice_display']);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Guest Folio');
        $sheet->setShowGridlines(false);
        $sheet->getPageSetup()
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setOrientation(PageSetup::ORIENTATION_PORTRAIT)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
        $sheet->getPageMargins()
            ->setTop(0.35)
            ->setRight(0.25)
            ->setBottom(0.35)
            ->setLeft(0.25);

        foreach ([
            'A' => 13,
            'B' => 15,
            'C' => 31,
            'D' => 14,
            'E' => 14,
            'F' => 16,
        ] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        foreach ([1 => 18, 2 => 18, 3 => 15, 4 => 15, 5 => 7] as $row => $height) {
            $sheet->getRowDimension($row)->setRowHeight($height);
        }

        $this->addSpreadsheetImage($sheet, $qrCodeDataUri, 'A1', 76);
        $this->addSpreadsheetImage($sheet, $logoPath, 'D1', 42, 52);

        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');
        $sheet->mergeCells('A3:F3');
        $sheet->mergeCells('A4:F4');
        $sheet->setCellValue('A1', $profile['name']);
        $sheet->setCellValue('A2', $profile['business']);
        $sheet->setCellValue('A3', trim((string) $profile['address']) . ', ' . trim((string) $profile['phone']));
        $sheet->setCellValue('A4', 'Email: ' . $profile['email'] . ' / Website: ' . $profile['website']);
        $sheet->getStyle('A1:F4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setSize(14);
        $sheet->getStyle('A2')->getFont()->setSize(13);
        $sheet->getStyle('A3:A4')->getFont()->setSize(10);
        $sheet->getStyle('A4:F4')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

        $sheet->setCellValueExplicit('A5', $registration['reg_no'], DataType::TYPE_STRING);
        $sheet->getStyle('A5')->getFont()->setSize(8);

        $sheet->setCellValue('A7', 'Invoice#');
        $sheet->setCellValue('B7', ':');
        $sheet->setCellValue('C7', $folio['invoice_display']);
        $sheet->setCellValue('A8', 'Registration');
        $sheet->setCellValue('B8', ':');
        $sheet->setCellValue('C8', $registration['reg_no'] . ($registration['company'] !== '' ? ' Company : ' . $registration['company'] : ''));
        $sheet->setCellValue('A9', 'Guest Name');
        $sheet->setCellValue('B9', ':');
        $sheet->setCellValue('C9', $registration['guest']);
        $sheet->setCellValue('A10', 'Address');
        $sheet->setCellValue('B10', ':');
        $sheet->setCellValue('C10', $registration['address']);

        $sheet->setCellValue('A12', 'Room');
        $sheet->setCellValue('B12', ':');
        $sheet->setCellValue('C12', $registration['room_label']);
        $sheet->setCellValue('D12', 'Remark');
        $sheet->setCellValue('E12', ':');
        $sheet->setCellValue('F12', preg_replace('/\s+/', ' ', trim((string) ($registration['remark'] ?: '-'))));
        $sheet->setCellValue('A13', 'C/I Date');
        $sheet->setCellValue('B13', ':');
        $sheet->setCellValue('C13', $registration['check_in_date']);
        $sheet->setCellValue('D13', 'C/I Time');
        $sheet->setCellValue('E13', ':');
        $sheet->setCellValue('F13', $registration['check_in_time']);
        $sheet->setCellValue('A14', 'C/O Date');
        $sheet->setCellValue('B14', ':');
        $sheet->setCellValue('C14', $checkOutAt->format('d-m-Y'));
        $sheet->setCellValue('D14', 'C/O Time');
        $sheet->setCellValue('E14', ':');
        $sheet->setCellValue('F14', $checkOutAt->format('H:i:s'));

        $sheet->mergeCells('E7:F7');
        $sheet->setCellValue('E7', 'GUEST FOLIO');
        $sheet->setCellValue('F8', number_format((int) ($registration['room_count'] ?? 1), 0, '.', ','));
        $sheet->setCellValue('F9', $checkOutAt->format('n/j/Y'));
        $sheet->setCellValue('F10', $checkOutAt->format('H:i'));
        $sheet->getStyle('E7:F10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('E7')->getFont()->getColor()->setARGB('FF0019A8');

        $sheet->getStyle('A7:F14')->getFont()->setSize(10);
        $sheet->getStyle('B7:B14')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A7:A14')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('F12:F14')->getAlignment()->setWrapText(true);

        $headerRow = 16;
        $sheet->fromArray(['Date', 'Invoice#', 'Description', 'Debit', 'Credit', 'Balance'], null, 'A' . $headerRow);
        $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FF0019A8'],
            ],
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_MEDIUM],
                'bottom' => ['borderStyle' => Border::BORDER_THIN],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF8FAFC'],
            ],
        ]);
        $sheet->getStyle('D' . $headerRow . ':F' . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $row = $headerRow + 1;
        $numberFormat = '#,##0;(#,##0);0';
        foreach ($folio['lines'] as $line) {
            $sheet->setCellValue('A' . $row, $line['date']);
            $sheet->setCellValueExplicit('B' . $row, $line['invoice'], DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $row, $line['description']);

            if ((float) $line['debit'] === 0.0) {
                $sheet->setCellValue('D' . $row, '-');
            } else {
                $sheet->setCellValue('D' . $row, (float) $line['debit']);
            }

            if ((float) $line['credit'] === 0.0) {
                $sheet->setCellValue('E' . $row, '-');
            } else {
                $sheet->setCellValue('E' . $row, (float) $line['credit']);
            }

            $sheet->setCellValue('F' . $row, (float) $line['balance']);
            $row++;
        }

        $lastLineRow = max($headerRow, $row - 1);
        $sheet->getStyle('A' . ($headerRow + 1) . ':F' . $lastLineRow)->getFont()->setSize(10);
        $sheet->getStyle('D' . ($headerRow + 1) . ':F' . $lastLineRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('D' . ($headerRow + 1) . ':F' . $lastLineRow)->getNumberFormat()->setFormatCode($numberFormat);
        $sheet->getStyle('A' . $headerRow . ':F' . $lastLineRow)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

        $summaryRow = $row + 1;
        $sheet->setCellValue('A' . $summaryRow, 'Note : ' . $folio['note']);
        $sheet->setCellValue('D' . $summaryRow, 'Total Transaction :');
        $sheet->setCellValue('F' . $summaryRow, (float) $folio['totals']['transaction']);
        $sheet->getStyle('D' . $summaryRow . ':F' . $summaryRow)->getFont()->setBold(true);

        $balanceStart = $summaryRow + 2;
        $sheet->setCellValue('B' . $balanceStart, 'Debit');
        $sheet->setCellValue('C' . $balanceStart, (float) $folio['totals']['debit']);
        $sheet->setCellValue('B' . ($balanceStart + 1), 'Credit');
        $sheet->setCellValue('C' . ($balanceStart + 1), (float) $folio['totals']['credit']);
        $sheet->setCellValue('B' . ($balanceStart + 2), 'Balanced');
        $sheet->setCellValue('C' . ($balanceStart + 2), (float) $folio['totals']['balance']);
        $sheet->getStyle('B' . ($balanceStart + 2) . ':C' . ($balanceStart + 2))->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle('B' . $balanceStart . ':C' . ($balanceStart + 2))->getFont()->setSize(10);
        $sheet->getStyle('B' . $balanceStart . ':B' . ($balanceStart + 2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('C' . $balanceStart . ':C' . ($balanceStart + 2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('C' . $balanceStart . ':C' . ($balanceStart + 2))->getNumberFormat()->setFormatCode($numberFormat);
        $sheet->getStyle('F' . $summaryRow)->getNumberFormat()->setFormatCode($numberFormat);

        $signatureRow = $balanceStart + 5;
        $sheet->setCellValue('A' . $signatureRow, '(' . $cashierName . ')');
        $sheet->mergeCells('D' . $signatureRow . ':F' . $signatureRow);
        $sheet->setCellValue('D' . $signatureRow, '(' . $registration['guest'] . ')');
        $sheet->getStyle('D' . $signatureRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $thanksRow = $signatureRow + 2;
        $sheet->mergeCells('A' . $thanksRow . ':F' . $thanksRow);
        $sheet->setCellValue('A' . $thanksRow, 'Thank you for staying with us, We look forward to welcoming you again');
        $sheet->getStyle('A' . $thanksRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $thanksRow)->getFont()->setSize(10);

        $sheet->freezePane('A' . ($headerRow + 1));
        $sheet->getStyle('A1:F' . $thanksRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle('A1:F' . $thanksRow)->getFont()->setName('Arial');

        $fileName = $this->folioExportFileName($registration['reg_no'], $folio, 'xlsx');

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'public',
        ]);
    }

    private function addSpreadsheetImage($sheet, ?string $path, string $coordinates, int $height, int $offsetX = 0): void
    {
        if (!$path) {
            return;
        }

        try {
            $drawing = new Drawing();
            $drawing->setPath($path);
            $drawing->setCoordinates($coordinates);
            $drawing->setHeight($height);
            $drawing->setOffsetX($offsetX);
            $drawing->setWorksheet($sheet);
        } catch (\Throwable $exception) {
        }
    }

    private function localImageDataUri(?string $path): ?string
    {
        if (!$path || !is_file($path) || !is_readable($path)) {
            return null;
        }

        $mimeType = mime_content_type($path) ?: 'image/png';
        $contents = file_get_contents($path);

        if ($contents === false) {
            return null;
        }

        return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
    }

    private function loadHotelProfile(): array
    {
        $profile = HotelBranding::profile();
        $profile['logo_absolute_path'] = HotelBranding::logoAbsolutePath($profile);
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

    private function loadCheckoutRegistration(string $regNo, bool $activeOnly = true, ?string $regNo2 = null): ?array
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

        $registration = [
            'header' => $header,
            'details' => $allDetails,
            'active_details' => $details,
            'primary' => $primary,
            'reg_no' => $regNo,
            'selected_reg_no2' => '',
            'checkout_scope' => 'all',
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

        $regNo2 = strtoupper(trim((string) $regNo2));

        return $regNo2 !== '' ? $this->scopeCheckoutRegistrationToRoom($registration, $regNo2) : $registration;
    }

    private function scopeCheckoutRegistrationToRoom(array $registration, string $regNo2): ?array
    {
        $regNo2 = strtoupper(trim($regNo2));
        $details = collect($registration['active_details'] ?? [])
            ->filter(fn ($row) => strtoupper(trim((string) ($row->RegNo2 ?? ''))) === $regNo2)
            ->values();

        if ($details->isEmpty()) {
            return null;
        }

        $allDetails = collect($registration['details'] ?? [])
            ->filter(fn ($row) => strtoupper(trim((string) ($row->RegNo2 ?? ''))) === $regNo2)
            ->values();
        $primary = $details->first();
        $roomCodes = $details->pluck('Kode')->map(fn ($value) => trim((string) $value))->filter()->values()->all();

        return array_merge($registration, [
            'details' => $allDetails,
            'active_details' => $details,
            'primary' => $primary,
            'selected_reg_no2' => $regNo2,
            'checkout_scope' => 'room',
            'room_codes' => $roomCodes,
            'room_count' => count($roomCodes),
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
        ]);
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
        $isRoomOnly = ($registration['checkout_scope'] ?? 'all') === 'room';
        $roomCodes = collect($registration['room_codes'] ?? [])->map(fn ($value) => trim((string) $value))->filter()->values()->all();
        $regNo2s = collect($activeDetails)->pluck('RegNo2')->map(fn ($value) => trim((string) $value))->filter()->values()->all();

        $headerDeposit = (float) ($registration['header']->Deposit ?? 0);
        if (!$isRoomOnly && $headerDeposit !== 0.0) {
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

        $extraBedQuery = DB::table('EXTRABED')
            ->join('EXTRABEDD', 'EXTRABED.Nofak', '=', 'EXTRABEDD.Nofak')
            ->selectRaw("EXTRABED.Tgl as Tgl, RTRIM(EXTRABEDD.Nofak) as Nofak, RTRIM(EXTRABED.Kode) as Kode, RTRIM(EXTRABEDD.Ket) as Ket, EXTRABEDD.Nominal as Nominal")
            ->whereRaw('RTRIM(EXTRABED.RegNo) = ?', [$registration['reg_no']]);

        if ($isRoomOnly) {
            $extraBedQuery->whereIn(DB::raw('RTRIM(EXTRABED.Kode)'), $roomCodes);
        }

        $extraBedRows = $extraBedQuery->orderBy('EXTRABED.Tgl')->get();
        foreach ($extraBedRows as $row) {
            $pushLine($row->Tgl, $this->crNofak((string) $row->Nofak), $this->crJudul((string) $row->Ket, (string) $row->Kode), (float) ($row->Nominal ?? 0), 0, 'I2');
        }

        foreach ($this->loadCategoryCharges('MINI', 'MINID', 'MINI', 'MINID', 'J', 'Mini Bar', $registration['reg_no'], $isRoomOnly ? $roomCodes : []) as $row) {
            $pushLine($row['date'], $row['invoice'], $row['description'], $row['debit'], 0, 'J');
        }

        foreach ($this->loadCategoryCharges('RES', 'RESD', 'RES', 'RESD', 'K', 'Coffe Lounge', $registration['reg_no'], $isRoomOnly ? $roomCodes : []) as $row) {
            $pushLine($row['date'], $row['invoice'], $row['description'], $row['debit'], 0, 'K');
        }

        foreach ($this->loadCategoryCharges('RES2', 'RESD2', 'RES2', 'RESD2', 'K01', 'Restaurant', $registration['reg_no'], $isRoomOnly ? $roomCodes : []) as $row) {
            $pushLine($row['date'], $row['invoice'], $row['description'], $row['debit'], 0, 'K01');
        }

        foreach ($this->loadCategoryCharges('BANQUET', 'BANQUETD', 'BANQUET', 'BANQUETD', 'J1', 'Banquet', $registration['reg_no'], $isRoomOnly ? $roomCodes : []) as $row) {
            $pushLine($row['date'], $row['invoice'], $row['description'], $row['debit'], 0, 'J1');
        }

        foreach ($this->loadCategoryCharges('CUCI', 'CUCID', 'CUCI', 'CUCID', 'L', 'Laundry', $registration['reg_no'], $isRoomOnly ? $roomCodes : []) as $row) {
            $pushLine($row['date'], $row['invoice'], $row['description'], $row['debit'], 0, 'L');
        }

        $otherQuery = DB::table('TAMBAH')
            ->join('TAMBAHD', 'TAMBAH.Nofak', '=', 'TAMBAHD.Nofak')
            ->selectRaw("TAMBAH.Tgl as Tgl, RTRIM(TAMBAHD.Nofak) as Nofak, RTRIM(TAMBAH.Kode) as Kode, RTRIM(TAMBAHD.Ket) as Ket, TAMBAHD.Nominal as Nominal")
            ->whereRaw('RTRIM(TAMBAH.RegNo) = ?', [$registration['reg_no']]);

        if ($isRoomOnly) {
            $otherQuery->whereIn(DB::raw('RTRIM(TAMBAH.Kode)'), $roomCodes);
        }

        $otherRows = $otherQuery->orderBy('TAMBAH.Tgl')->get();
        foreach ($otherRows as $row) {
            $pushLine($row->Tgl, $this->crNofak((string) $row->Nofak), $this->crJudul((string) $row->Ket, (string) $row->Kode), (float) ($row->Nominal ?? 0), 0, 'M');
        }

        foreach ($this->loadTelephoneChargeRows($registration['reg_no'], $checkoutAt, $isRoomOnly ? $regNo2s : [], $isRoomOnly ? $roomCodes : []) as $row) {
            $pushLine($row['date'], $row['invoice'], $row['description'], $row['debit'], 0, $row['code']);
        }

        foreach ($this->loadCorrectionRows($registration['reg_no'], $isRoomOnly ? $regNo2s : []) as $row) {
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

        if (!$isRoomOnly) {
            foreach ($this->loadPaymentRows($registration['reg_no']) as $row) {
                $pushLine($row['date'], $row['invoice'], $row['description'], 0, $row['credit'], $row['code']);
            }

            foreach ($this->loadRefundRows($registration['reg_no']) as $row) {
                $pushLine($row['date'], $row['invoice'], $row['description'], 0, $row['credit'], $row['code']);
            }
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
        string $regNo,
        array $roomCodes = []
    ): array {
        $query = DB::table($headerTable)
            ->join($detailTable, $headerTable . '.Nofak', '=', $detailTable . '.Nofak')
            ->selectRaw("
                {$headerTable}.Tgl as Tgl,
                RTRIM({$headerTable}.Nofak) as Nofak,
                RTRIM({$headerTable}.Kode) as Kode,
                SUM(({$detailTable}.Qty * {$detailTable}.Harga) - (({$detailTable}.Disc / 100.0) * ({$detailTable}.Qty * {$detailTable}.Harga))) as Nominal
            ")
            ->whereRaw("RTRIM({$headerTable}.RegNo) = ?", [$regNo])
            ->groupBy($headerTable . '.Tgl', $headerTable . '.Nofak', $headerTable . '.Kode')
            ->orderBy($headerTable . '.Tgl');

        if ($roomCodes !== []) {
            $query->whereIn(DB::raw("RTRIM({$headerTable}.Kode)"), $roomCodes);
        }

        $rows = $query->get();

        return $rows->map(function ($row) use ($defaultLabel) {
            return [
                'date' => $row->Tgl,
                'invoice' => $this->crNofak((string) $row->Nofak),
                'description' => $this->crJudul($defaultLabel, (string) ($row->Kode ?? '')),
                'debit' => (float) ($row->Nominal ?? 0),
            ];
        })->all();
    }

    private function loadTelephoneChargeRows(string $regNo, Carbon $checkoutAt, array $regNo2s = [], array $roomCodes = []): array
    {
        $rows = [];
        $callQuery = DB::table('CALL')
            ->selectRaw('RTRIM(CallRegNo2) as RegNo2, SUM(CallCost) as Cost')
            ->whereRaw('RTRIM(CallRegno) = ?', [$regNo])
            ->groupBy('CallRegNo2');

        if ($regNo2s !== []) {
            $callQuery->whereIn(DB::raw('RTRIM(CallRegNo2)'), $regNo2s);
        }

        $callRows = $callQuery->get();

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

        $manualQuery = DB::table('TELP')
            ->join('TELPD', 'TELP.Nofak', '=', 'TELPD.Nofak')
            ->selectRaw("TELP.Tgl as Tgl, RTRIM(TELPD.Nofak) as Nofak, RTRIM(TELP.Kode) as Kode, RTRIM(TELPD.Ket) as Ket, TELPD.Nominal as Nominal")
            ->whereRaw('RTRIM(TELP.RegNo) = ?', [$regNo])
            ->orderBy('TELP.Tgl');

        if ($roomCodes !== []) {
            $manualQuery->whereIn(DB::raw('RTRIM(TELP.Kode)'), $roomCodes);
        }

        $manualRows = $manualQuery->get();

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

    private function loadCorrectionRows(string $regNo, array $regNo2s = []): array
    {
        if (!Schema::hasTable('Koreksi')) {
            return [];
        }

        $detailsQuery = DB::table('DATA2')
            ->selectRaw('RTRIM(RegNo2) as RegNo2')
            ->whereRaw('RTRIM(RegNo) = ?', [$regNo]);

        if ($regNo2s !== []) {
            $detailsQuery->whereIn(DB::raw('RTRIM(RegNo2)'), $regNo2s);
        }

        $details = $detailsQuery->get();

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
