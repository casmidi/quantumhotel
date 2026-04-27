<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Booking Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #111;
            margin: 24px;
            font-size: 12px;
        }

        .report-head {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            border-bottom: 2px solid #111;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        h1 {
            margin: 0 0 4px;
            font-size: 20px;
        }

        .muted {
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 5px 6px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background: #e9edf3;
            text-transform: uppercase;
            font-size: 10px;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .room-list {
            min-width: 180px;
            line-height: 1.35;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 6px;
        }

        .calendar-day {
            min-height: 92px;
            border: 1px solid #333;
            padding: 6px;
            page-break-inside: avoid;
        }

        .calendar-day.has-booking {
            background: #f8dddd;
        }

        .calendar-date {
            display: flex;
            justify-content: space-between;
            gap: 6px;
            font-weight: 700;
        }

        .calendar-count {
            margin-top: 5px;
            color: #9a2531;
            font-size: 10px;
            font-weight: 700;
        }

        .calendar-detail {
            margin: 5px 0 0;
            padding-left: 14px;
            font-size: 10px;
        }

        .calendar-detail li + li {
            margin-top: 4px;
        }

        .calendar-group-count {
            color: #9a2531;
            font-weight: 700;
            white-space: nowrap;
        }

        @media print {
            body {
                margin: 10mm;
            }
        }
    </style>
</head>
<body>
    <div class="report-head">
        <div>
            <h1>
                @if ($viewMode === 'detail')
                    Breakdown / Details Booking Report
                @elseif ($viewMode === 'calendar')
                    Calendar Booking Report
                @else
                    Summary Booking Report
                @endif
            </h1>
            <div class="muted">{{ $profile['address'] ?? '' }}</div>
            <div class="muted">{{ $profile['phone'] ?? '' }}</div>
        </div>
        <div>
            <strong>{{ \Carbon\Carbon::parse($dateFrom)->format('d-m-Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d-m-Y') }}</strong><br>
            @if ($viewMode === 'calendar')
                <span class="muted">Calendar: {{ $calendar['month_label'] }}</span><br>
            @endif
            <span class="muted">Generated: {{ $summary['generated_at'] }}</span>
        </div>
    </div>

    @if ($viewMode === 'summary')
        <table>
            <thead>
                <tr>
                    <th>ResNo</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Guest</th>
                    <th>Rooms</th>
                    <th>Room List</th>
                    <th>Class</th>
                    <th>Remark</th>
                    <th class="text-right">Rate</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($summaryRows as $row)
                    <tr>
                        <td>{{ $row->ResNo }}</td>
                        <td>{{ $row->TglInDisplay }}</td>
                        <td>{{ $row->TglKeluarDisplay }}</td>
                        <td>{{ $row->Guest }}</td>
                        <td>{{ $row->RoomCount }} {{ $row->RoomCount === 1 ? 'room' : 'rooms' }}</td>
                        <td class="text-left room-list">{{ $row->RoomList }}</td>
                        <td>{{ $row->ClassList }}</td>
                        <td>{{ $row->Remark }}</td>
                        <td class="text-right">{{ number_format($row->Rate, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" style="text-align:center;">No booking data.</td></tr>
                @endforelse
            </tbody>
        </table>
    @elseif ($viewMode === 'detail')
        <table>
            <thead>
                <tr>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Room</th>
                    <th>Class</th>
                    <th>Guest</th>
                    <th>Phone</th>
                    <th>Remark</th>
                    <th>User FO</th>
                    <th>User In</th>
                    <th class="text-right">Rate</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $row->TglInDisplay }}</td>
                        <td>{{ $row->TglKeluarDisplay }}</td>
                        <td>{{ $row->Kode }}</td>
                        <td>{{ $row->Kelas }}</td>
                        <td>{{ $row->Guest }}</td>
                        <td>{{ $row->Telphone }}</td>
                        <td>{{ $row->Remark }}</td>
                        <td>{{ $row->UserFO }}</td>
                        <td>{{ $row->UserIn }}</td>
                        <td class="text-right">{{ number_format($row->Rate, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="10" style="text-align:center;">No booking data.</td></tr>
                @endforelse
            </tbody>
        </table>
    @else
        <div class="calendar-grid">
            @foreach ($calendar['days'] as $day)
                <div class="calendar-day {{ $day['has_booking'] ? 'has-booking' : '' }}">
                    <div class="calendar-date">
                        <span>{{ $day['day'] }}</span>
                        <span>{{ $day['weekday'] }}</span>
                    </div>
                    @if ($day['has_booking'])
                        <div class="calendar-count">{{ $day['room_count'] }} rooms / {{ $day['booking_count'] }} bookings</div>
                        <ul class="calendar-detail">
                            @foreach ($day['groups'] as $group)
                                @php($groupRoomCount = count($group['rooms']))
                                <li>
                                    <strong>{{ $group['guest'] ?: $group['res_no'] }}</strong>
                                    <span class="calendar-group-count">- {{ $groupRoomCount }} {{ $groupRoomCount === 1 ? 'room' : 'rooms' }}</span><br>
                                    {{ implode(', ', $group['rooms']) }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <script>
        window.addEventListener('load', () => window.print());
    </script>
</body>
</html>
