<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Reception Customer Recaptulation - {{ \Carbon\Carbon::parse($startDate)->format('Ymd') }}-{{ \Carbon\Carbon::parse($endDate)->format('Ymd') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @page {
            size: legal landscape;
            margin: 10mm;
        }

        :root {
            color: #14243a;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef3f8;
            color: #14243a;
        }

        .page {
            width: min(1320px, 100%);
            margin: 0 auto;
            padding: 18px;
        }

        .toolbar {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-bottom: 14px;
        }

        .btn {
            border: 1px solid #b8c9dc;
            background: #fff;
            color: #173761;
            padding: 9px 13px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 800;
            cursor: pointer;
        }

        .report-shell {
            background: #fff;
            border: 1px solid #d6e2ef;
            box-shadow: 0 16px 40px rgba(21, 42, 70, 0.08);
        }

        .hotel-header {
            display: grid;
            grid-template-columns: 100px minmax(0, 1fr) 300px;
            gap: 16px;
            align-items: center;
            padding: 16px 18px 12px;
            border-bottom: 3px solid #193f6d;
        }

        .hotel-logo-box {
            width: 86px;
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: transparent;
        }

        .hotel-logo-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .hotel-logo-fallback {
            color: #64748b;
            font-size: 9px;
            font-weight: 800;
            line-height: 1.25;
            text-align: center;
        }

        .hotel-name {
            margin: 0;
            color: #173761;
            font-size: 20px;
            line-height: 1.05;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .hotel-line {
            margin-top: 3px;
            color: #465a73;
            font-weight: 700;
        }

        .report-meta {
            display: grid;
            gap: 5px;
            text-align: right;
            font-weight: 800;
        }

        .report-title {
            padding: 12px 18px;
            border-bottom: 1px solid #dce7f2;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 16px;
            align-items: end;
        }

        .report-title h1 {
            margin: 0;
            color: #173761;
            font-size: 17px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .muted {
            color: #64748b;
            font-weight: 700;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(8, minmax(0, 1fr));
            gap: 6px;
            padding: 12px 18px;
            border-bottom: 1px solid #dce7f2;
        }

        .summary-item {
            border: 1px solid #dce7f2;
            background: #f9fbfe;
            padding: 7px;
            border-radius: 6px;
        }

        .summary-item small {
            display: block;
            color: #64748b;
            font-size: 8px;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .summary-item strong {
            display: block;
            margin-top: 3px;
            color: #173761;
            font-size: 11px;
        }

        .warning {
            padding: 10px 18px;
            border-bottom: 1px solid #fecdd3;
            background: #fff1f2;
            color: #8a1f1f;
            font-weight: 800;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-family: "Times New Roman", Times, serif;
        }

        th,
        td {
            border: 1px solid #d9d9d9;
            padding: 3px 4px;
            vertical-align: middle;
        }

        th {
            background: #fff;
            color: #000;
            font-size: 8.5px;
            text-align: left;
            text-transform: none;
            letter-spacing: 0;
            white-space: nowrap;
        }

        td {
            font-size: 8.5px;
            color: #000;
        }

        tfoot td {
            background: #fff;
            color: #000080;
            font-weight: 900;
        }

        .subtotal-row td {
            background: #f8fafc;
            color: #000080;
            font-weight: 900;
            border-top: 2px solid #b8c9dc;
        }

        .text-right {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .nowrap {
            white-space: nowrap;
        }

        .guest-cell strong {
            display: block;
            max-width: 180px;
            white-space: nowrap;
            overflow: hidden;
            color: #000;
            font-weight: 400;
        }

        .badge.warning-badge {
            background: #fde8e8;
            color: #9f1f1f;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            padding: 18px;
            break-inside: avoid;
        }

        .signature-box {
            min-height: 76px;
            border: 1px solid #dce7f2;
            padding: 10px;
            display: grid;
            align-content: space-between;
        }

        .signature-line {
            border-top: 1px solid #14243a;
            padding-top: 6px;
            font-weight: 800;
            text-align: center;
        }

        @media print {
            body {
                background: #fff;
            }

            .page {
                width: 100%;
                padding: 0;
            }

            .toolbar {
                display: none;
            }

            .report-shell {
                border: 0;
                box-shadow: none;
            }
        }

        @media (max-width: 900px) {
            .hotel-header,
            .report-title,
            .signature-grid {
                grid-template-columns: 1fr;
            }

            .report-meta {
                text-align: left;
            }

            .summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
</head>
<body>
@php
    $money = fn ($value) => number_format((float) $value, 2, '.', ',');
    $number = fn ($value) => number_format((float) $value, 0, ',', '.');
    $backUrl = '/reception-customer-recaptulation?' . http_build_query(array_filter([
        'start_date' => $startDate,
        'end_date' => $endDate,
        'regno' => $regno !== '' ? $regno : null,
        'search' => $search !== '' ? $search : null,
    ]));
@endphp

<div class="page">
    <div class="toolbar">
        <a href="{{ $backUrl }}" class="btn">Back</a>
        <button type="button" class="btn" onclick="window.print()">Print</button>
    </div>

    <article class="report-shell">
        <header class="hotel-header">
            <div class="hotel-logo-box">
                @if(!empty($profile['logo_url']))
                    <img src="{{ $profile['logo_url'] }}" alt="Hotel logo">
                @else
                    <div class="hotel-logo-fallback">HOTEL<br>LOGO</div>
                @endif
            </div>
            <div>
                <h1 class="hotel-name">{{ $profile['name'] }}</h1>
                <div class="hotel-line">{{ $profile['business'] }}</div>
                <div class="hotel-line">{{ $profile['address'] }}{{ !empty($profile['address_secondary']) ? ', ' . $profile['address_secondary'] : '' }}</div>
                <div class="hotel-line">Telp: {{ $profile['phone'] }} / Email: {{ $profile['email'] }} / Website: {{ $profile['website'] }}</div>
            </div>
            <div class="report-meta">
                <div>Period: {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} Until : {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}</div>
                <div>Source: {{ $summary['source'] }} / {{ $summary['mode'] }}</div>
                <div>Posting: {{ $postingStatus ? $postingStatus->period_code . ' - ' . ($postingStatus->is_posted ? 'Posted' : 'Review') : '-' }}</div>
                <div>{{ $auditBatch ? 'Audit No: ' . ($auditBatch->audit_no ?? '-') : 'Audit No: -' }}</div>
                <div>Generated: {{ \Carbon\Carbon::parse($summary['generated_at'])->format('d-m-Y H:i:s') }}</div>
            </div>
        </header>

        <section class="report-title">
            <div>
                <h1>RECEPTIONIST CUSTOMER RECAPTULATION</h1>
                <div class="muted">Period : {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} Until : {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}</div>
            </div>
                <div class="muted">Format: RCR</div>
        </section>

        <section class="summary-grid">
            <div class="summary-item"><small>Rows</small><strong>{{ $number($summary['total_rows']) }}</strong></div>
            <div class="summary-item"><small>Invoices</small><strong>{{ $number($summary['invoices']) }}</strong></div>
            <div class="summary-item"><small>RegNo</small><strong>{{ $number($summary['registrations']) }}</strong></div>
            <div class="summary-item"><small>Pax</small><strong>{{ $number($summary['pax']) }}</strong></div>
            <div class="summary-item"><small>DPP</small><strong>Rp {{ $money($summary['totals']['dpp']) }}</strong></div>
            <div class="summary-item"><small>Tax</small><strong>Rp {{ $money($summary['totals']['tax']) }}</strong></div>
            <div class="summary-item"><small>Service</small><strong>Rp {{ $money($summary['totals']['service']) }}</strong></div>
            <div class="summary-item"><small>Total</small><strong>Rp {{ $money($summary['totals']['total']) }}</strong></div>
        </section>

        @if(!$schemaReady)
            <section class="warning">
                PB1 table is not ready. Missing columns: {{ implode(', ', $missingColumns) }}.
            </section>
        @endif

        @if($periodWarning)
            <section class="warning">{{ $periodWarning }}</section>
        @endif

        @if($summary['variance_count'] > 0 || abs((float) $summary['variance_total']) > 0.05)
            <section class="warning">
                Fiscal variance: {{ $number($summary['variance_count']) }} rows / Rp {{ $money($summary['variance_total']) }}.
            </section>
        @endif

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>INVOICE</th>
                        <th>Room #</th>
                        <th>Guest</th>
                        <th class="text-right">Pax</th>
                        <th>C/I Date</th>
                        <th>C/O Date</th>
                        <th>Date #</th>
                        <th class="text-right">ROOM</th>
                        <th class="text-right">REST</th>
                        <th class="text-right">Other</th>
                        <th class="text-right">VILA</th>
                        <th class="text-right">DPP</th>
                        <th class="text-right">Service</th>
                        <th class="text-right">Tax</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $regnoGroups = collect($rows)->groupBy(fn ($row) => trim((string) ($row->Regno ?? '')) ?: '-');
                    @endphp
                    @if($regnoGroups->isEmpty())
                        <tr>
                            <td colspan="15">No RCR data is available for this period.</td>
                        </tr>
                    @else
                        @foreach($regnoGroups as $groupRegno => $groupRows)
                            @foreach($groupRows as $row)
                                <tr>
                                    <td class="nowrap">{{ $row->Invoice2 ?: '-' }}</td>
                                    <td class="nowrap">{{ $row->Kode ?: '-' }}</td>
                                    <td class="guest-cell">
                                        <strong>{{ $row->Guest ?: '-' }}</strong>
                                    </td>
                                    <td class="text-right">{{ $number($row->Person) }}</td>
                                    <td class="nowrap">{{ $row->TglInDisplay ?: '-' }}</td>
                                    <td class="nowrap">{{ $row->TglOutDisplay ?: '-' }}</td>
                                    <td class="nowrap">{{ $row->TanggalDisplay ?: '-' }}</td>
                                    <td class="text-right">{{ $money($row->Room1) }}</td>
                                    <td class="text-right">{{ $money($row->Cafe1) }}</td>
                                    <td class="text-right">{{ $money($row->Other1) }}</td>
                                    <td class="text-right">{{ $money($row->Vila) }}</td>
                                    <td class="text-right">{{ $money($row->Dpp) }}</td>
                                    <td class="text-right">{{ $money($row->Service) }}</td>
                                    <td class="text-right">{{ $money($row->Tax) }}</td>
                                    <td class="text-right">{{ $money($row->Total) }}</td>
                                </tr>
                            @endforeach
                            <tr class="subtotal-row">
                                <td colspan="7">SUBTOTAL REGNO {{ $groupRegno }}</td>
                                <td class="text-right">{{ $money($groupRows->sum('Room1')) }}</td>
                                <td class="text-right">{{ $money($groupRows->sum('Cafe1')) }}</td>
                                <td class="text-right">{{ $money($groupRows->sum('Other1')) }}</td>
                                <td class="text-right">{{ $money($groupRows->sum('Vila')) }}</td>
                                <td class="text-right">{{ $money($groupRows->sum('Dpp')) }}</td>
                                <td class="text-right">{{ $money($groupRows->sum('Service')) }}</td>
                                <td class="text-right">{{ $money($groupRows->sum('Tax')) }}</td>
                                <td class="text-right">{{ $money($groupRows->sum('Total')) }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7">GRAND TOTAL</td>
                        <td class="text-right">{{ $money($summary['totals']['room']) }}</td>
                        <td class="text-right">{{ $money($summary['totals']['cafe']) }}</td>
                        <td class="text-right">{{ $money($summary['totals']['other']) }}</td>
                        <td class="text-right">{{ $money($summary['totals']['vila']) }}</td>
                        <td class="text-right">{{ $money($summary['totals']['dpp']) }}</td>
                        <td class="text-right">{{ $money($summary['totals']['service']) }}</td>
                        <td class="text-right">{{ $money($summary['totals']['tax']) }}</td>
                        <td class="text-right">{{ $money($summary['totals']['total']) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <section class="signature-grid">
            <div class="signature-box">
                <div>Prepared by</div>
                <div class="signature-line">Reception / Night Auditor</div>
            </div>
            <div class="signature-box">
                <div>Verified by</div>
                <div class="signature-line">Front Office Manager</div>
            </div>
            <div class="signature-box">
                <div>Approved by</div>
                <div class="signature-line">Accounting / Tax</div>
            </div>
        </section>
    </article>
</div>
</body>
</html>
