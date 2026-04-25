<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Expected Departure - {{ \Carbon\Carbon::parse($businessDate)->format('Ymd') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            color: #14243a;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
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
            width: min(1180px, 100%);
            margin: 0 auto;
            padding: 20px;
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
            grid-template-columns: 110px minmax(0, 1fr) 240px;
            gap: 18px;
            align-items: center;
            padding: 18px 22px 14px;
            border-bottom: 3px solid #193f6d;
        }

        .hotel-logo-box {
            width: 92px;
            height: 72px;
            border: 1px solid #d6e2ef;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .hotel-logo-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .hotel-logo-fallback {
            color: #64748b;
            font-size: 10px;
            font-weight: 800;
            line-height: 1.25;
            text-align: center;
        }

        .hotel-name {
            margin: 0;
            color: #173761;
            font-size: 22px;
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
            padding: 14px 22px;
            border-bottom: 1px solid #dce7f2;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 16px;
            align-items: end;
        }

        .report-title h1 {
            margin: 0;
            color: #173761;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .report-title .muted {
            color: #64748b;
            margin-top: 4px;
            font-weight: 700;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 8px;
            padding: 14px 22px;
            border-bottom: 1px solid #dce7f2;
        }

        .summary-item {
            border: 1px solid #dce7f2;
            background: #f9fbfe;
            padding: 8px;
            border-radius: 6px;
        }

        .summary-item small {
            display: block;
            color: #64748b;
            font-size: 9px;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .summary-item strong {
            display: block;
            margin-top: 3px;
            color: #173761;
            font-size: 13px;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #dce7f2;
            padding: 6px;
            vertical-align: top;
        }

        th {
            background: #edf5ff;
            color: #173761;
            font-size: 9px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }

        td {
            font-size: 10px;
        }

        .text-right {
            text-align: right;
        }

        .nowrap {
            white-space: nowrap;
        }

        .guest-cell strong {
            display: block;
        }

        .guest-cell small {
            color: #64748b;
            font-weight: 700;
        }

        .badge {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 999px;
            background: #e8f7f1;
            color: #17624a;
            font-size: 9px;
            font-weight: 900;
            white-space: nowrap;
        }

        .badge.warning {
            background: #fde8e8;
            color: #9f1f1f;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            padding: 22px;
            break-inside: avoid;
        }

        .signature-box {
            min-height: 84px;
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
    $money = fn ($value) => number_format((float) $value, 0, ',', '.');
    $number = fn ($value) => number_format((float) $value, 0, ',', '.');
@endphp

<div class="page">
    <div class="toolbar">
        <a href="/expected-departure?business_date={{ $businessDate }}@if($search !== '')&search={{ urlencode($search) }}@endif" class="btn">Back</a>
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
                <div>Period: {{ \Carbon\Carbon::parse($businessDate)->format('d-m-Y') }}</div>
                <div>Source: {{ $summary['source'] }}</div>
                <div>{{ $summary['audit_no'] ? 'Audit No: ' . $summary['audit_no'] : 'Audit No: -' }}</div>
                <div>Generated: {{ \Carbon\Carbon::parse($summary['generated_at'])->format('d M Y H:i:s') }}</div>
            </div>
        </header>

        <section class="report-title">
            <div>
                <h1>Expected Departure</h1>
                <div class="muted">Period : {{ \Carbon\Carbon::parse($businessDate)->format('d-m-Y') }}</div>
            </div>
            <div class="muted">Format: DATA2</div>
        </section>

        <section class="summary-grid">
            <div class="summary-item"><small>Rooms</small><strong>{{ $number($summary['rooms']) }}</strong></div>
            <div class="summary-item"><small>Departure Rows</small><strong>{{ $number($summary['total_rows']) }}</strong></div>
            <div class="summary-item"><small>Due Today</small><strong>{{ $number($summary['due_today']) }}</strong></div>
            <div class="summary-item"><small>Overdue</small><strong>{{ $number($summary['overdue']) }}</strong></div>
            <div class="summary-item"><small>Pax</small><strong>{{ $number($summary['pax']) }}</strong></div>
            <div class="summary-item"><small>Rate Total</small><strong>Rp {{ $money($summary['rate_total']) }}</strong></div>
        </section>

        @if(!$schemaReady)
            <section style="padding: 18px 22px; color: #9f1f1f; font-weight: 800;">
                Tabel DATA2 atau ROOM belum tersedia.
            </section>
        @endif

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Kota</th>
                        <th>Guest</th>
                        <th>Payment</th>
                        <th>Remark</th>
                        <th>TglIn</th>
                        <th>JamIn</th>
                        <th>TglKeluar</th>
                        <th class="text-right">Pax</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="nowrap">{{ $row->Kode ?: '-' }}</td>
                            <td>{{ $row->Kota ?: '-' }}</td>
                            <td class="guest-cell">
                                <strong>{{ $row->GuestDisplay ?: '-' }}</strong>
                                <small>{{ $row->RegNo ?: '-' }} / {{ $row->RegNo2 ?: '-' }}</small>
                            </td>
                            <td>{{ $row->Payment ?: '-' }}</td>
                            <td>{{ $row->Remark ?: '-' }}</td>
                            <td class="nowrap">{{ $row->TglInDisplay ?: '-' }}</td>
                            <td class="nowrap">{{ $row->JamInDisplay ?: '-' }}</td>
                            <td class="nowrap">{{ $row->TglKeluarDisplay ?: '-' }}</td>
                            <td class="text-right">{{ $number($row->Pax) }}</td>
                            <td>
                                <span class="badge {{ $row->departure_status === 'Overdue' ? 'warning' : '' }}">{{ $row->departure_status }}</span>
                                {{ $row->control_flag ? ' / ' . $row->control_flag : '' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">Tidak ada Expected Departure untuk period ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <section class="signature-grid">
            <div class="signature-box">
                <div>Prepared by</div>
                <div class="signature-line">Night Auditor</div>
            </div>
            <div class="signature-box">
                <div>Verified by</div>
                <div class="signature-line">Front Office Supervisor</div>
            </div>
            <div class="signature-box">
                <div>Approved by</div>
                <div class="signature-line">Duty Manager</div>
            </div>
        </section>
    </article>
</div>
</body>
</html>
