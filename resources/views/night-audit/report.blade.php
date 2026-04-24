<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Night Audit Standard Report - {{ $batch->audit_no }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            color: #14243a;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
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
            padding: 22px;
        }

        .report-shell {
            background: #fff;
            border: 1px solid #d6e2ef;
            box-shadow: 0 16px 40px rgba(21, 42, 70, 0.08);
        }

        .report-header {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 20px;
            padding: 24px;
            border-bottom: 3px solid #193f6d;
        }

        h1,
        h2,
        h3 {
            margin: 0;
            color: #173761;
        }

        h1 {
            font-size: 24px;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        h2 {
            margin-bottom: 10px;
            font-size: 17px;
            border-bottom: 1px solid #dce7f2;
            padding-bottom: 7px;
        }

        h3 {
            font-size: 14px;
            margin-bottom: 8px;
        }

        .muted {
            color: #64748b;
        }

        .header-meta {
            display: grid;
            gap: 5px;
            text-align: right;
            font-weight: 700;
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

        .section {
            padding: 18px 24px;
            border-bottom: 1px solid #e1e9f2;
            break-inside: avoid;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 8px;
        }

        .stat {
            border: 1px solid #dce7f2;
            background: #f9fbfe;
            padding: 10px;
            border-radius: 6px;
        }

        .stat small {
            display: block;
            margin-bottom: 4px;
            color: #64748b;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .stat strong {
            display: block;
            color: #173761;
            font-size: 15px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th,
        td {
            border: 1px solid #dce7f2;
            padding: 7px;
            vertical-align: top;
        }

        th {
            background: #edf5ff;
            color: #173761;
            font-size: 10px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        td.text-right,
        th.text-right {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 999px;
            background: #edf1f7;
            color: #40536b;
            font-size: 10px;
            font-weight: 800;
            white-space: nowrap;
        }

        .badge.ready {
            background: #e8f7f1;
            color: #17624a;
        }

        .badge.warning {
            background: #fff3d8;
            color: #75530d;
        }

        .badge.danger {
            background: #fde8e8;
            color: #9f1f1f;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-top: 24px;
        }

        .signature-box {
            min-height: 92px;
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
            .report-header,
            .grid-2,
            .signature-grid {
                grid-template-columns: 1fr;
            }

            .header-meta {
                text-align: left;
            }

            .stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
</head>
<body>
@php
    $money = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $number = fn ($value) => number_format((float) $value, 0, ',', '.');
    $reportBadge = function (?string $status) {
        return match ($status) {
            'Ready', 'No Adjustment' => 'ready',
            'Need Action', 'Manual Review' => 'warning',
            'Setup Required' => 'danger',
            default => '',
        };
    };
@endphp

<div class="page">
    <div class="toolbar">
        <a href="/night-audit?batch_id={{ $batch->id }}" class="btn">Back</a>
        <button type="button" class="btn" onclick="window.print()">Print</button>
    </div>

    <article class="report-shell">
        <header class="report-header">
            <div>
                <h1>Night Audit Standard Report Pack</h1>
                <div class="muted">International hotel daily closing control report</div>
            </div>
            <div class="header-meta">
                <div>Audit No: {{ $batch->audit_no }}</div>
                <div>Business Date: {{ \Carbon\Carbon::parse($batch->business_date)->format('d M Y') }}</div>
                <div>Status: {{ $batch->status }}</div>
                <div>Generated: {{ now()->format('d M Y H:i:s') }}</div>
            </div>
        </header>

        <section class="section">
            <h2>NA-01 Executive Night Audit Summary</h2>
            <div class="stat-grid">
                <div class="stat"><small>Occupancy</small><strong>{{ number_format((float) $summary['occupancy_percent'], 2, ',', '.') }}%</strong></div>
                <div class="stat"><small>Occupied</small><strong>{{ $number($summary['occupied_rooms']) }} / {{ $number($summary['total_rooms']) }}</strong></div>
                <div class="stat"><small>Vacant</small><strong>{{ $number($summary['vacant_rooms']) }}</strong></div>
                <div class="stat"><small>OOO / Reno</small><strong>{{ $number($summary['out_of_order_rooms']) }}</strong></div>
                <div class="stat"><small>Arrival / Departure</small><strong>{{ $number($summary['arrival_count']) }} / {{ $number($summary['departure_count']) }}</strong></div>
                <div class="stat"><small>In House</small><strong>{{ $number($summary['in_house_count']) }}</strong></div>
                <div class="stat"><small>Room Revenue</small><strong>{{ $money($summary['room_revenue']) }}</strong></div>
                <div class="stat"><small>Gross Revenue</small><strong>{{ $money($summary['gross_revenue']) }}</strong></div>
                <div class="stat"><small>Cash Receipt</small><strong>{{ $money($summary['cash_receipt_total']) }}</strong></div>
                <div class="stat"><small>Non Cash</small><strong>{{ $money($summary['non_cash_receipt_total']) }}</strong></div>
                <div class="stat"><small>City Ledger</small><strong>{{ $money($summary['city_ledger_total']) }}</strong></div>
                <div class="stat"><small>Exceptions</small><strong>{{ $number($summary['critical_exception_count']) }} / {{ $number($summary['exception_count']) }}</strong></div>
            </div>
        </section>

        <section class="section">
            <h2>International Report Index</h2>
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Report</th>
                        <th>Owner</th>
                        <th>Status</th>
                        <th>Purpose</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($standardReports as $report)
                        <tr>
                            <td>{{ $report->code }}</td>
                            <td><strong>{{ $report->title }}</strong></td>
                            <td>{{ $report->owner }}</td>
                            <td><span class="badge {{ $reportBadge($report->status ?? null) }}">{{ $report->status }}</span></td>
                            <td>{{ $report->purpose }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section class="section">
            <h2>NA-02 Occupancy & Room Statistics</h2>
            <div class="grid-2">
                <div>
                    <h3>Rooms by Segment</h3>
                    <table>
                        <thead><tr><th>Segment</th><th class="text-right">Rooms</th></tr></thead>
                        <tbody>
                            @forelse($reportPack['rooms_by_segment'] as $row)
                                <tr><td>{{ $row->label }}</td><td class="text-right">{{ $number($row->count) }}</td></tr>
                            @empty
                                <tr><td colspan="2">No room segment data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div>
                    <h3>Rooms by Payment</h3>
                    <table>
                        <thead><tr><th>Payment</th><th class="text-right">Rooms</th></tr></thead>
                        <tbody>
                            @forelse($reportPack['rooms_by_payment'] as $row)
                                <tr><td>{{ $row->label }}</td><td class="text-right">{{ $number($row->count) }}</td></tr>
                            @empty
                                <tr><td colspan="2">No payment data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="section">
            <h2>NA-03 Guest In-House / House Count</h2>
            <table>
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Guest</th>
                        <th>RegNo</th>
                        <th>Payment</th>
                        <th>Segment</th>
                        <th>Package</th>
                        <th class="text-right">Nights</th>
                        <th class="text-right">Net Rate</th>
                        <th>Flag</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roomSnapshots as $room)
                        <tr>
                            <td>{{ $room->room_code }}</td>
                            <td>{{ $room->guest_name ?: '-' }}</td>
                            <td>{{ $room->regno ?: '-' }}</td>
                            <td>{{ $room->payment_method ?: '-' }}</td>
                            <td>{{ $room->market_segment ?: '-' }}</td>
                            <td>{{ $room->package_code ?: '-' }}</td>
                            <td class="text-right">{{ $number($room->stay_nights ?? 0) }}</td>
                            <td class="text-right">{{ $money($room->net_room_rate ?? 0) }}</td>
                            <td>{{ $room->risk_flag ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9">No guest in-house data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="section">
            <h2>NA-04 Arrival, Departure, Stay-Over & No-Show Control</h2>
            <div class="stat-grid">
                <div class="stat"><small>Arrival</small><strong>{{ $number($summary['arrival_count']) }}</strong></div>
                <div class="stat"><small>Departure</small><strong>{{ $number($summary['departure_count']) }}</strong></div>
                <div class="stat"><small>Stay-Over</small><strong>{{ $number(max(($summary['in_house_count'] ?? 0) - ($summary['arrival_count'] ?? 0), 0)) }}</strong></div>
                <div class="stat"><small>Walk-In</small><strong>{{ $number($summary['walk_in_count']) }}</strong></div>
                <div class="stat"><small>No-Show</small><strong>Manual</strong></div>
                <div class="stat"><small>Cancellation</small><strong>Manual</strong></div>
            </div>
        </section>

        <section class="section">
            <h2>NA-05 Room Revenue & Rate Variance</h2>
            <table>
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Guest</th>
                        <th>Payment</th>
                        <th>Package</th>
                        <th class="text-right">Room Rate</th>
                        <th class="text-right">Discount %</th>
                        <th class="text-right">Net Rate</th>
                        <th>Flag</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportPack['rate_exception_rows'] as $room)
                        <tr>
                            <td>{{ $room->room_code }}</td>
                            <td>{{ $room->guest_name ?: '-' }}</td>
                            <td>{{ $room->payment_method ?: '-' }}</td>
                            <td>{{ $room->package_code ?: '-' }}</td>
                            <td class="text-right">{{ $money($room->room_rate ?? 0) }}</td>
                            <td class="text-right">{{ number_format((float) ($room->discount_percent ?? 0), 2, ',', '.') }}</td>
                            <td class="text-right">{{ $money($room->net_room_rate ?? 0) }}</td>
                            <td>{{ $room->risk_flag ?: 'ZERO_OR_REVIEW' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No room rate exception.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="section">
            <h2>NA-06 Department Revenue Summary</h2>
            <table>
                <thead>
                    <tr>
                        <th>Department</th>
                        <th class="text-right">Count</th>
                        <th class="text-right">Debit</th>
                        <th class="text-right">Credit</th>
                        <th class="text-right">Net Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportPack['revenue_by_department'] as $row)
                        <tr>
                            <td>{{ $row->label }}</td>
                            <td class="text-right">{{ $number($row->count) }}</td>
                            <td class="text-right">{{ $money($row->debit ?? 0) }}</td>
                            <td class="text-right">{{ $money($row->credit ?? 0) }}</td>
                            <td class="text-right"><strong>{{ $money($row->net_amount ?? 0) }}</strong></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No revenue data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="section">
            <h2>NA-07 Cashier Audit & Payment Reconciliation</h2>
            <table>
                <thead>
                    <tr>
                        <th>Payment Type</th>
                        <th class="text-right">Transactions</th>
                        <th class="text-right">Receipt</th>
                        <th class="text-right">Refund</th>
                        <th class="text-right">Cash Drop</th>
                        <th class="text-right">Variance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportPack['payment_by_type'] as $row)
                        <tr>
                            <td>{{ $row->label }}</td>
                            <td class="text-right">{{ $number($row->count) }}</td>
                            <td class="text-right">{{ $money($row->gross_receipt ?? 0) }}</td>
                            <td class="text-right">{{ $money($row->refund_amount ?? 0) }}</td>
                            <td class="text-right">{{ $money($row->cash_drop ?? 0) }}</td>
                            <td class="text-right">{{ $money($row->variance_amount ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No cashier transaction data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="section">
            <h2>NA-08 Guest Ledger, Deposit & City Ledger</h2>
            <div class="stat-grid">
                <div class="stat"><small>Deposit Total</small><strong>{{ $money($summary['deposit_total']) }}</strong></div>
                <div class="stat"><small>City Ledger</small><strong>{{ $money($summary['city_ledger_total']) }}</strong></div>
                <div class="stat"><small>Guest Ledger Exposure</small><strong>{{ $money(max(($summary['gross_revenue'] ?? 0) - ($summary['deposit_total'] ?? 0), 0)) }}</strong></div>
                <div class="stat"><small>Cash Receipt</small><strong>{{ $money($summary['cash_receipt_total']) }}</strong></div>
                <div class="stat"><small>Non Cash Receipt</small><strong>{{ $money($summary['non_cash_receipt_total']) }}</strong></div>
                <div class="stat"><small>AR Review</small><strong>Required</strong></div>
            </div>
        </section>

        <section class="section">
            <h2>NA-09 Housekeeping Discrepancy</h2>
            <table>
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>PMS</th>
                        <th>HK</th>
                        <th>Type</th>
                        <th>Severity</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($housekeepingExceptions as $exception)
                        <tr>
                            <td>{{ $exception->room_code }}</td>
                            <td>{{ $exception->pms_status ?: '-' }}</td>
                            <td>{{ $exception->housekeeping_status ?: '-' }}</td>
                            <td>{{ $exception->exception_type }}</td>
                            <td>{{ $exception->severity }}</td>
                            <td>{{ $exception->action_status }}</td>
                            <td>{{ $exception->notes ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No housekeeping discrepancy.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="section">
            <h2>NA-10 Adjustment, Allowance, Void & Refund</h2>
            <div class="stat-grid">
                <div class="stat"><small>Adjustment Count</small><strong>{{ $number($reportPack['adjustment_count']) }}</strong></div>
                <div class="stat"><small>Adjustment Total</small><strong>{{ $money($reportPack['adjustment_total']) }}</strong></div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Dept</th>
                        <th>Room</th>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                        <th>Status</th>
                        <th>Requested By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $adjustment)
                        <tr>
                            <td>{{ $adjustment->adjustment_no }}</td>
                            <td>{{ $adjustment->department }}</td>
                            <td>{{ $adjustment->room_code ?: '-' }}</td>
                            <td>{{ $adjustment->description }}</td>
                            <td class="text-right">{{ $money($adjustment->amount ?? 0) }}</td>
                            <td>{{ $adjustment->approval_status }}</td>
                            <td>{{ $adjustment->requested_by ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No adjustment recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="section">
            <h2>NA-11 Tax & Service Charge Summary</h2>
            <div class="muted">Tax and service charge setup has not been automated in this report pack. Finance must reconcile tax/service amount from official outlet and folio posting until tax rules are configured.</div>
        </section>

        <section class="section">
            <h2>NA-12 Exception, Checklist & Approval Sign-Off</h2>
            <div class="grid-2">
                <div>
                    <h3>Checklist by Status</h3>
                    <table>
                        <thead><tr><th>Status</th><th class="text-right">Count</th></tr></thead>
                        <tbody>
                            @foreach($reportPack['checklist_by_status'] as $row)
                                <tr><td>{{ $row->label }}</td><td class="text-right">{{ $number($row->count) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div>
                    <h3>Exception by Severity</h3>
                    <table>
                        <thead><tr><th>Severity</th><th class="text-right">Count</th></tr></thead>
                        <tbody>
                            @forelse($reportPack['exceptions_by_severity'] as $row)
                                <tr><td>{{ $row->label }}</td><td class="text-right">{{ $number($row->count) }}</td></tr>
                            @empty
                                <tr><td colspan="2">No exception.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Level</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Approver</th>
                        <th>Approved At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($approvals as $approval)
                        <tr>
                            <td>{{ $approval->approval_level }}</td>
                            <td>{{ $approval->role_name }}</td>
                            <td>{{ $approval->status }}</td>
                            <td>{{ $approval->approver_name ?: '-' }}</td>
                            <td>{{ $approval->approved_at ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="signature-grid">
                <div class="signature-box">
                    <div>Prepared by</div>
                    <div class="signature-line">Night Auditor</div>
                </div>
                <div class="signature-box">
                    <div>Reviewed by</div>
                    <div class="signature-line">Duty Manager</div>
                </div>
                <div class="signature-box">
                    <div>Approved by</div>
                    <div class="signature-line">Financial Controller</div>
                </div>
            </div>
        </section>
    </article>
</div>
</body>
</html>
