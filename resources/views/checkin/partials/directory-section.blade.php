@php
    $sortBy = $sortBy ?? 'check_in';
    $sortDir = $sortDir ?? 'desc';
    $sortUrl = function (string $column) use ($sortBy, $sortDir) {
        $query = array_merge(request()->except('page'), [
            'sort_by' => $column,
            'sort_dir' => $sortBy === $column && $sortDir === 'asc' ? 'desc' : 'asc',
        ]);

        $queryString = http_build_query($query);

        return url()->current() . ($queryString !== '' ? '?' . $queryString : '');
    };
    $sortIcon = function (string $column) use ($sortBy, $sortDir) {
        if ($sortBy !== $column) {
            return 'fa-solid fa-sort';
        }

        return $sortDir === 'asc' ? 'fa-solid fa-sort-up' : 'fa-solid fa-sort-down';
    };
@endphp
<section class="package-shell checkin-shell" id="checkinDirectoryShell">
    <div class="checkin-directory-loading" aria-hidden="true">
        <div class="checkin-directory-loading-card" role="status" aria-live="polite">
            <span class="checkin-directory-loading-spinner"></span>
            <span>Loading check-in grid...</span>
        </div>
    </div>
    <div class="package-shell-body">
        <div class="checkin-directory-head">
            <div>
                <h3 class="package-grid-title mb-1">Check In Directory</h3>
                <p class="package-grid-note mb-0">Click any row to load its data into the form above.</p>
            </div>
            <div class="checkin-table-meta">
                Active: <strong>{{ number_format($summary['active'], 0, ',', '.') }}</strong> &nbsp;|&nbsp;
                Rooms Ready: <strong>{{ number_format($summary['rooms_ready'], 0, ',', '.') }}</strong>
                &nbsp;|&nbsp;
                Active Packages: <strong>{{ number_format($summary['packages'], 0, ',', '.') }}</strong>
            </div>
        </div>

        <form method="GET" action="/checkin" class="checkin-search-form" id="checkinSearchForm">
            <input type="hidden" name="sort_by" value="{{ $sortBy }}">
            <input type="hidden" name="sort_dir" value="{{ $sortDir }}">
            <div class="checkin-search-group">
                <label class="package-label" for="searchKeyword">Search Keyword</label>
                <input type="text" name="search" id="searchKeyword" class="form-control package-input"
                    value="{{ $search }}" placeholder="Search reg number, room, guest, or package">
            </div>
            <div class="checkin-search-actions">
                <button type="submit" class="btn package-btn-primary"><i
                        class="fa-solid fa-magnifying-glass mr-2"></i>Search</button>
                <a href="/checkin" class="btn package-btn-secondary">Clear</a>
            </div>
        </form>
        <div class="checkin-directory-tools">
            <div class="checkin-directory-result">
                Showing
                <strong>{{ number_format($checkins->firstItem() ?? 0, 0, ',', '.') }}</strong>
                -
                <strong>{{ number_format($checkins->lastItem() ?? 0, 0, ',', '.') }}</strong>
                of
                <strong>{{ number_format($checkins->total(), 0, ',', '.') }}</strong>
                active stay record(s)
            </div>
            <form method="GET" action="/checkin" class="checkin-per-page">
                <input type="hidden" name="search" value="{{ $search }}">
                <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                <input type="hidden" name="sort_dir" value="{{ $sortDir }}">
                <label class="package-label" for="perPage">Rows Per Page</label>
                <select name="per_page" id="perPage" class="form-control package-select"
                    onchange="this.form.submit()">
                    @foreach ([10, 25, 50] as $size)
                        <option value="{{ $size }}" {{ (int) $perPage === $size ? 'selected' : '' }}>
                            {{ $size }} rows
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="package-table-wrap mt-4">
            <table class="table package-table checkin-table mb-0">
                <colgroup>
                    <col class="checkin-col-reg">
                    <col class="checkin-col-room">
                    <col class="checkin-col-guest">
                    <col class="checkin-col-date">
                    <col class="checkin-col-date">
                    <col class="checkin-col-package">
                    <col class="checkin-col-nominal">
                    <col class="checkin-col-action">
                </colgroup>
                <thead>
                    <tr>
                        <th><a href="{{ $sortUrl('reg_no') }}" data-sort-column="reg_no"
                                class="checkin-sort-link {{ $sortBy === 'reg_no' ? 'is-active' : '' }}">Reg Number <i
                                    class="{{ $sortIcon('reg_no') }}"></i></a></th>
                        <th><a href="{{ $sortUrl('room') }}" data-sort-column="room"
                                class="checkin-sort-link {{ $sortBy === 'room' ? 'is-active' : '' }}">Room <i
                                    class="{{ $sortIcon('room') }}"></i></a></th>
                        <th><a href="{{ $sortUrl('guest') }}" data-sort-column="guest"
                                class="checkin-sort-link {{ $sortBy === 'guest' ? 'is-active' : '' }}">Guest <i
                                    class="{{ $sortIcon('guest') }}"></i></a></th>
                        <th><a href="{{ $sortUrl('check_in') }}" data-sort-column="check_in"
                                class="checkin-sort-link {{ $sortBy === 'check_in' ? 'is-active' : '' }}">Check In <i
                                    class="{{ $sortIcon('check_in') }}"></i></a></th>
                        <th><a href="{{ $sortUrl('check_out') }}" data-sort-column="check_out"
                                class="checkin-sort-link {{ $sortBy === 'check_out' ? 'is-active' : '' }}">Est. Out <i
                                    class="{{ $sortIcon('check_out') }}"></i></a></th>
                        <th><a href="{{ $sortUrl('package') }}" data-sort-column="package"
                                class="checkin-sort-link {{ $sortBy === 'package' ? 'is-active' : '' }}">Package <i
                                    class="{{ $sortIcon('package') }}"></i></a></th>
                        <th class="text-right"><a href="{{ $sortUrl('nominal') }}" data-sort-column="nominal"
                                class="checkin-sort-link {{ $sortBy === 'nominal' ? 'is-active' : '' }}">Nominal <i
                                    class="{{ $sortIcon('nominal') }}"></i></a></th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($checkins as $record)
                        <tr class="checkin-record-row" data-record="{{ $record->record_json }}"
                            data-detail-key="{{ $record->RegNo2 }}">
                            <td class="checkin-cell-reg"><span class="package-code">{{ $record->RegNo }}</span></td>
                            <td class="checkin-cell-room"><span class="room-pill">{{ $record->Kode }}</span></td>
                            <td>
                                <div class="guest-block">
                                    <strong>{{ $record->Guest }}</strong>
                                    <span>{{ $record->Tipe ?: 'CHECK IN' }}</span>
                                </div>
                            </td>
                            <td class="checkin-cell-date">{{ $record->check_in_date }}</td>
                            <td class="checkin-cell-date">{{ $record->check_out_date }}</td>
                            <td class="checkin-cell-package">{{ $record->Package ?: '-' }}</td>
                            <td class="text-right nominal-cell checkin-cell-nominal">Rp {{ $record->nominal_display }}</td>
                            <td class="text-center">
                                <a href="/checkin/{{ urlencode($record->RegNo2) }}/delete" class="checkin-delete-link"
                                    onclick="event.stopPropagation(); return confirm('Hapus data check in ini?');"
                                    title="Delete record"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="package-empty">Belum ada data check in aktif untuk
                                ditampilkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($checkins->hasPages())
            <div class="package-pagination-wrap mt-4">
                {{ $checkins->onEachSide(1)->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</section>
