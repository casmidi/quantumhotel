<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Guest Folio - {{ $registration['reg_no'] }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            color-scheme: light;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #edf2f9;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
        }

        .folio-shell {
            position: relative;
            width: 820px;
            margin: 18px auto 24px;
            padding: 20px 26px 22px;
            background: #fff;
            border: 1px solid #d7deea;
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.09);
        }

        .folio-qr-corner {
            position: absolute;
            top: 18px;
            left: 24px;
            width: 98px;
            border: 1px solid #cfd7e4;
            background: #fff;
            padding: 5px;
            text-align: center;
        }

        .folio-qr-corner img {
            display: block;
            width: 100%;
            height: auto;
        }

        .folio-qr-corner .qr-caption {
            margin-top: 4px;
            font-size: 0.55rem;
            line-height: 1.15;
            color: #334155;
            word-break: break-word;
        }

        .hotel-head {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
        }

        .hotel-logo {
            width: 58px;
            height: 58px;
            margin: 0 auto 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .hotel-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .hotel-name {
            margin: 0;
            font-size: 0.98rem;
            font-weight: 400;
            color: #000;
        }

        .hotel-brand {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 400;
            color: #000;
        }

        .hotel-line {
            font-size: 0.78rem;
            line-height: 1.2;
            color: #000;
        }

        .folio-top {
            display: grid;
            grid-template-columns: 1.85fr 0.75fr;
            gap: 14px;
            padding-top: 8px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 2px 0;
            font-size: 0.86rem;
            vertical-align: top;
        }

        .meta-label {
            width: 82px;
            white-space: nowrap;
        }

        .meta-colon {
            width: 12px;
            text-align: center;
        }

        .meta-double {
            display: grid;
            gap: 2px;
            margin-top: 3px;
        }

        .meta-pair-row {
            display: grid;
            grid-template-columns: 68px 12px 124px 74px 12px minmax(0, 1fr);
            gap: 0 6px;
            align-items: start;
            font-size: 0.86rem;
            line-height: 1.15;
        }

        .meta-pair-row + .meta-pair-row {
            margin-top: 2px;
        }

        .meta-pair-label {
            white-space: nowrap;
        }

        .meta-pair-colon {
            text-align: center;
        }

        .meta-pair-value {
            min-width: 0;
        }

        .meta-pair-value.is-remark {
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .folio-side {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
        }

        .folio-title {
            margin: 0;
            color: #0019a8;
            font-size: 0.98rem;
            font-weight: 400;
            letter-spacing: 0;
            text-align: right;
        }

        .folio-side-meta {
            width: 100%;
            max-width: 190px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 2px 12px;
            font-size: 0.8rem;
        }

        .folio-side-meta .right {
            text-align: right;
        }

        .folio-table {
            width: 100%;
            margin-top: 8px;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .folio-table thead th {
            padding: 4px 6px;
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
            color: #0019a8;
            text-align: left;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .folio-table thead th.text-right {
            text-align: right;
            padding-right: 6px;
        }

        .folio-table td {
            padding: 3px 6px;
            font-size: 0.82rem;
            vertical-align: top;
        }

        .folio-table td.text-right {
            padding-right: 6px;
        }

        .text-right {
            text-align: right;
            white-space: nowrap;
        }

        .balance-negative {
            white-space: nowrap;
        }

        .folio-summary {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 270px 280px;
            gap: 8px 10px;
            align-items: start;
            margin-top: 8px;
        }

        .folio-note {
            font-size: 0.82rem;
            align-self: center;
            grid-column: 1;
        }

        .folio-totals {
            width: 280px;
            font-size: 0.84rem;
            display: flex;
            justify-content: flex-end;
            align-self: center;
            grid-column: 3;
        }

        .folio-totals table {
            width: 280px;
            border-collapse: collapse;
        }

        .folio-totals td {
            padding: 2px 0;
            vertical-align: bottom;
        }

        .folio-totals .label {
            width: 165px;
            text-align: right;
            padding-right: 14px;
            white-space: nowrap;
        }

        .folio-totals .amount {
            width: 115px;
            text-align: right;
            white-space: nowrap;
        }

        .folio-totals .strong-row td {
            padding-top: 4px;
            font-weight: 700;
        }

        .folio-balances {
            width: 270px;
            display: flex;
            justify-content: center;
            margin-top: 2px;
            grid-column: 2;
        }

        .folio-balances table {
            width: 270px;
            border-collapse: collapse;
            font-size: 0.84rem;
        }

        .folio-balances td {
            padding: 2px 0;
            vertical-align: bottom;
        }

        .folio-balances .label {
            width: 105px;
            text-align: right;
            padding-right: 10px;
            white-space: nowrap;
        }

        .folio-balances .amount {
            width: 135px;
            text-align: right;
            white-space: nowrap;
        }

        .folio-balances .balance-row .amount,
        .folio-balances .balance-row .label {
            position: relative;
            padding-top: 3px;
        }

        .folio-balances .balance-row .label::before {
            content: "";
            position: absolute;
            top: 0;
            right: -150px;
            width: 222px;
            border-top: 1px solid #000;
        }

        .folio-signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            align-items: end;
            margin-top: 14px;
        }

        .signature-line {
            min-height: 28px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding-bottom: 3px;
            font-size: 0.86rem;
        }

        .signature-left {
            justify-content: flex-start;
        }

        .signature-right {
            justify-content: flex-end;
        }

        .thanks-line {
            margin-top: 8px;
            padding-top: 5px;
            text-align: center;
            font-size: 0.8rem;
        }

        .print-actions {
            width: 820px;
            margin: 0 auto 24px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .print-actions a,
        .print-actions button {
            border: none;
            border-radius: 999px;
            padding: 12px 20px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            text-decoration: none;
        }

        .close-btn {
            background: #dbe7fb;
            color: #1336a3;
        }

        .excel-btn {
            background: #dcefe4;
            color: #166534;
        }

        .pdf-btn {
            background: #fee2e2;
            color: #991b1b;
        }

        .print-btn {
            background: #173761;
            color: #fff;
        }

        @media print {
            body {
                background: #fff;
            }

            .folio-shell {
                width: 100%;
                margin: 0;
                padding: 0;
                border: 0;
                box-shadow: none;
            }

            .print-actions {
                display: none;
            }
        }

        @media screen and (max-width: 860px) {
            .folio-summary {
                grid-template-columns: minmax(0, 1fr) 270px;
            }

            .folio-totals {
                grid-column: 2;
                justify-content: center;
            }

            .folio-balances {
                grid-column: 2;
            }
        }
    </style>
</head>
<body>
    @php
        $primary = $registration['primary'];
        $checkOutAt = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $folio['checkout_at']);
        $fmt = fn ($value) => number_format((float) $value, 0, '.', ',');
        $balanceFmt = function ($value) use ($fmt) {
            $value = (float) $value;

            return $value < 0 ? '(' . $fmt(abs($value)) . ')' : $fmt($value);
        };
    @endphp

    <div class="folio-shell">
        <div class="folio-qr-corner">
            <img src="{{ $qrCodeDataUri }}" alt="Checkout QR code">
            <div class="qr-caption">
                {{ $registration['reg_no'] }}
            </div>
        </div>

        <div class="hotel-head">
            <div class="hotel-logo">
                @if (!empty($profile['logo_url']))
                    <img src="{{ $profile['logo_url'] }}" alt="Hotel logo">
                @endif
            </div>
            <h1 class="hotel-brand">{{ $profile['name'] }}</h1>
            <p class="hotel-name">{{ $profile['business'] }}</p>
            <div class="hotel-line">{{ $profile['address'] }}, {{ $profile['phone'] }}</div>
            <div class="hotel-line">Email: {{ $profile['email'] }} / Website: {{ $profile['website'] }}</div>
        </div>

        <div class="folio-top">
            <div>
                <table class="meta-table">
                    <tr>
                        <td class="meta-label">Invoice#</td>
                        <td class="meta-colon">:</td>
                        <td>{{ $folio['invoice_display'] }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Registration</td>
                        <td class="meta-colon">:</td>
                        <td>{{ $registration['reg_no'] }}@if($registration['company'] !== '') Company : {{ $registration['company'] }}@endif</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Guest Name</td>
                        <td class="meta-colon">:</td>
                        <td>{{ $registration['guest'] }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Address</td>
                        <td class="meta-colon">:</td>
                        <td>{{ $registration['address'] }}</td>
                    </tr>
                </table>

                <div class="meta-double">
                    <div class="meta-pair-row">
                        <div class="meta-pair-label">Room</div>
                        <div class="meta-pair-colon">:</div>
                        <div class="meta-pair-value">{{ $registration['room_label'] }}</div>
                        <div class="meta-pair-label">Remark</div>
                        <div class="meta-pair-colon">:</div>
                        <div class="meta-pair-value is-remark">{{ preg_replace('/\s+/', ' ', trim((string) ($registration['remark'] ?: '-'))) }}</div>
                    </div>
                    <div class="meta-pair-row">
                        <div class="meta-pair-label">C/I Date</div>
                        <div class="meta-pair-colon">:</div>
                        <div class="meta-pair-value">{{ $registration['check_in_date'] }}</div>
                        <div class="meta-pair-label">C/I Time</div>
                        <div class="meta-pair-colon">:</div>
                        <div class="meta-pair-value">{{ $registration['check_in_time'] }}</div>
                    </div>
                    <div class="meta-pair-row">
                        <div class="meta-pair-label">C/O Date</div>
                        <div class="meta-pair-colon">:</div>
                        <div class="meta-pair-value">{{ $checkOutAt->format('d-m-Y') }}</div>
                        <div class="meta-pair-label">C/O Time</div>
                        <div class="meta-pair-colon">:</div>
                        <div class="meta-pair-value">{{ $checkOutAt->format('H:i:s') }}</div>
                    </div>
                </div>
            </div>

            <div class="folio-side">
                <h2 class="folio-title">GUEST FOLIO</h2>
                <div class="folio-side-meta">
                    <div></div>
                    <div class="right">{{ number_format((int) ($registration['room_count'] ?? 1), 0, '.', ',') }}</div>
                    <div></div>
                    <div class="right">{{ $checkOutAt->format('n/j/Y') }}</div>
                    <div></div>
                    <div class="right">{{ $checkOutAt->format('H:i') }}</div>
                </div>
            </div>
        </div>

        <table class="folio-table">
            <colgroup>
                <col style="width: 12%;">
                <col style="width: 16%;">
                <col style="width: 28%;">
                <col style="width: 14%;">
                <col style="width: 14%;">
                <col style="width: 16%;">
            </colgroup>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Invoice#</th>
                    <th>Description</th>
                    <th class="text-right">Debit</th>
                    <th class="text-right">Credit</th>
                    <th class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($folio['lines'] as $line)
                    <tr>
                        <td>{{ $line['date'] }}</td>
                        <td>{{ $line['invoice'] }}</td>
                        <td>{{ $line['description'] }}</td>
                        <td class="text-right">{{ $line['debit'] == 0 ? '-' : $fmt($line['debit']) }}</td>
                        <td class="text-right">{{ $line['credit'] == 0 ? '-' : $fmt($line['credit']) }}</td>
                        <td class="text-right">
                            <span class="balance-negative">{{ $balanceFmt($line['balance']) }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="folio-summary">
            <div class="folio-note">Note : {{ $folio['note'] }}</div>
            <div class="folio-totals">
                <table>
                    <tr class="strong-row">
                        <td class="label">Total Transaction :</td>
                        <td class="amount">{{ $fmt($folio['totals']['transaction']) }}</td>
                    </tr>
                </table>
            </div>
            <div class="folio-balances">
                <table>
                    <tr>
                        <td class="label">Debit</td>
                        <td class="amount">{{ $fmt($folio['totals']['debit']) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Credit</td>
                        <td class="amount">{{ $fmt($folio['totals']['credit']) }}</td>
                    </tr>
                    <tr class="balance-row">
                        <td class="label">Balanced</td>
                        <td class="amount">{{ $fmt($folio['totals']['balance']) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="folio-signatures">
            <div class="signature-line signature-left">({{ $cashierName }})</div>
            <div class="signature-line signature-right">({{ $registration['guest'] }})</div>
        </div>

        <div class="thanks-line">Thank you for staying with us, We look forward to welcoming you again</div>
    </div>

    <div class="print-actions">
        <a href="{{ $excelUrl }}" class="excel-btn">Excel</a>
        <a href="{{ $pdfUrl }}" class="pdf-btn" target="_blank" rel="noopener">PDF</a>
        <button type="button" class="close-btn" onclick="window.close()">Close</button>
        <button type="button" class="print-btn" onclick="window.print()">Print</button>
    </div>
</body>
</html>
