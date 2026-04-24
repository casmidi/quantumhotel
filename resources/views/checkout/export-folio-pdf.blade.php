<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 12px 20px 14px;
        }

        body {
            margin: 0;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td,
        th {
            vertical-align: top;
        }

        .page {
            position: relative;
            width: 100%;
        }

        .qr {
            position: absolute;
            top: 4px;
            left: 0;
            width: 96px;
            border: 1px solid #cfd7e4;
            padding: 5px;
            text-align: center;
        }

        .qr img {
            width: 84px;
            height: 84px;
        }

        .qr-code {
            margin-top: 4px;
            font-size: 8px;
            color: #334155;
        }

        .hotel {
            text-align: center;
            border-bottom: 1px solid #000;
            padding: 5px 0 8px;
        }

        .hotel-logo {
            height: 54px;
            margin-bottom: 4px;
        }

        .hotel-logo img {
            max-width: 54px;
            max-height: 54px;
        }

        .hotel-brand {
            font-size: 16px;
            line-height: 17px;
        }

        .hotel-business {
            font-size: 15px;
            line-height: 16px;
        }

        .hotel-line {
            font-size: 11px;
            line-height: 13px;
        }

        .top {
            margin-top: 8px;
        }

        .meta td {
            padding: 1px 0;
            font-size: 12px;
            line-height: 14px;
        }

        .label {
            width: 84px;
            white-space: nowrap;
        }

        .colon {
            width: 12px;
            text-align: center;
        }

        .folio-title {
            color: #0019a8;
            font-size: 14px;
            text-align: right;
        }

        .right-meta {
            margin-top: 8px;
            font-size: 12px;
            text-align: right;
            line-height: 16px;
        }

        .lines {
            margin-top: 9px;
            table-layout: fixed;
        }

        .lines th {
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
            color: #0019a8;
            font-size: 12px;
            padding: 4px 6px;
            text-align: left;
        }

        .lines td {
            font-size: 12px;
            padding: 3px 6px;
            line-height: 14px;
        }

        .text-right {
            text-align: right;
            white-space: nowrap;
        }

        .summary {
            margin-top: 8px;
            table-layout: fixed;
        }

        .summary td {
            font-size: 12px;
            line-height: 14px;
        }

        .total-box td {
            font-size: 12px;
            font-weight: 700;
            padding-top: 4px;
        }

        .balance-box {
            width: 240px;
            margin: 0 auto;
        }

        .balance-box td {
            padding: 1px 0;
        }

        .balance-box .balance-line td {
            border-top: 1px solid #000;
            height: 0;
            line-height: 0;
            padding: 0;
        }

        .signatures {
            margin-top: 24px;
        }

        .signatures td {
            font-size: 12px;
            vertical-align: bottom;
        }

        .thanks {
            margin-top: 14px;
            text-align: center;
            font-size: 11px;
        }
    </style>
</head>
<body>
    @php
        $checkOutAt = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $folio['checkout_at']);
        $fmt = fn ($value) => number_format((float) $value, 0, '.', ',');
        $balanceFmt = function ($value) use ($fmt) {
            $value = (float) $value;

            return $value < 0 ? '(' . $fmt(abs($value)) . ')' : $fmt($value);
        };
    @endphp

    <div class="page">
        <div class="qr">
            <img src="{{ $qrCodeDataUri }}" alt="Checkout QR code">
            <div class="qr-code">{{ $registration['reg_no'] }}</div>
        </div>

        <div class="hotel">
            <div class="hotel-logo">
                @if (!empty($logoDataUri))
                    <img src="{{ $logoDataUri }}" alt="Hotel logo">
                @endif
            </div>
            <div class="hotel-brand">{{ $profile['name'] }}</div>
            <div class="hotel-business">{{ $profile['business'] }}</div>
            <div class="hotel-line">{{ $profile['address'] }}, {{ $profile['phone'] }}</div>
            <div class="hotel-line">Email: {{ $profile['email'] }} / Website: {{ $profile['website'] }}</div>
        </div>

        <table class="top">
            <tr>
                <td style="width: 72%;">
                    <table class="meta">
                        <tr>
                            <td class="label">Invoice#</td>
                            <td class="colon">:</td>
                            <td>{{ $folio['invoice_display'] }}</td>
                        </tr>
                        <tr>
                            <td class="label">Registration</td>
                            <td class="colon">:</td>
                            <td>{{ $registration['reg_no'] }}@if($registration['company'] !== '') Company : {{ $registration['company'] }}@endif</td>
                        </tr>
                        <tr>
                            <td class="label">Guest Name</td>
                            <td class="colon">:</td>
                            <td>{{ $registration['guest'] }}</td>
                        </tr>
                        <tr>
                            <td class="label">Address</td>
                            <td class="colon">:</td>
                            <td>{{ $registration['address'] }}</td>
                        </tr>
                    </table>

                    <table class="meta" style="margin-top: 4px;">
                        <tr>
                            <td class="label">Room</td>
                            <td class="colon">:</td>
                            <td style="width: 150px;">{{ $registration['room_label'] }}</td>
                            <td style="width: 70px;">Remark</td>
                            <td class="colon">:</td>
                            <td>{{ preg_replace('/\s+/', ' ', trim((string) ($registration['remark'] ?: '-'))) }}</td>
                        </tr>
                        <tr>
                            <td class="label">C/I Date</td>
                            <td class="colon">:</td>
                            <td>{{ $registration['check_in_date'] }}</td>
                            <td>C/I Time</td>
                            <td class="colon">:</td>
                            <td>{{ $registration['check_in_time'] }}</td>
                        </tr>
                        <tr>
                            <td class="label">C/O Date</td>
                            <td class="colon">:</td>
                            <td>{{ $checkOutAt->format('d-m-Y') }}</td>
                            <td>C/O Time</td>
                            <td class="colon">:</td>
                            <td>{{ $checkOutAt->format('H:i:s') }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 28%;">
                    <div class="folio-title">GUEST FOLIO</div>
                    <div class="right-meta">
                        {{ number_format((int) ($registration['room_count'] ?? 1), 0, '.', ',') }}<br>
                        {{ $checkOutAt->format('n/j/Y') }}<br>
                        {{ $checkOutAt->format('H:i') }}
                    </div>
                </td>
            </tr>
        </table>

        <table class="lines">
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
                        <td class="text-right">{{ $balanceFmt($line['balance']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="summary">
            <tr>
                <td style="width: 34%;">Note : {{ $folio['note'] }}</td>
                <td style="width: 32%;">
                    <table class="balance-box">
                        <tr>
                            <td class="text-right" style="width: 90px; padding-right: 10px;">Debit</td>
                            <td class="text-right">{{ $fmt($folio['totals']['debit']) }}</td>
                        </tr>
                        <tr>
                            <td class="text-right" style="padding-right: 10px;">Credit</td>
                            <td class="text-right">{{ $fmt($folio['totals']['credit']) }}</td>
                        </tr>
                        <tr class="balance-line">
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td class="text-right" style="padding-right: 10px;">Balanced</td>
                            <td class="text-right">{{ $fmt($folio['totals']['balance']) }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 34%;">
                    <table class="total-box">
                        <tr>
                            <td class="text-right">Total Transaction :</td>
                            <td class="text-right" style="width: 115px;">{{ $fmt($folio['totals']['transaction']) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="signatures">
            <tr>
                <td style="width: 50%;">({{ $cashierName }})</td>
                <td class="text-right" style="width: 50%;">({{ $registration['guest'] }})</td>
            </tr>
        </table>

        <div class="thanks">Thank you for staying with us, We look forward to welcoming you again</div>
    </div>
</body>
</html>
