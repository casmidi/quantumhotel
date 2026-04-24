@php
    $sortBy = $sortBy ?? 'check_in';
    $sortDir = $sortDir ?? 'desc';
    $checkoutScope = $checkoutScope ?? 'all';
    $selectedRegNo = $selectedRegNo ?? '';
    $selectedRegNo2 = $selectedRegNo2 ?? '';
    $sortUrl = function (string $column) use ($sortBy, $sortDir, $checkoutScope, $selectedRegNo, $selectedRegNo2) {
        $query = array_merge(request()->except('page'), [
            'sort_by' => $column,
            'sort_dir' => $sortBy === $column && $sortDir === 'asc' ? 'desc' : 'asc',
            'checkout_scope' => $checkoutScope,
        ]);

        if ($selectedRegNo !== '') {
            $query['reg_no'] = $selectedRegNo;
        }

        if ($selectedRegNo2 !== '') {
            $query['reg_no2'] = $selectedRegNo2;
        }

        $queryString = http_build_query($query);

        return url('/checkout') . ($queryString !== '' ? '?' . $queryString : '');
    };
    $sortIcon = function (string $column) use ($sortBy, $sortDir) {
        if ($sortBy !== $column) {
            return 'fa-solid fa-sort';
        }

        return $sortDir === 'asc' ? 'fa-solid fa-sort-up' : 'fa-solid fa-sort-down';
    };
    $rowUrl = function ($row) use ($search, $perPage, $sortBy, $sortDir, $checkoutScope) {
        $query = [
            'checkout_scope' => $checkoutScope,
            'search' => $search,
            'per_page' => $perPage,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
            'reg_no' => trim((string) $row->RegNo),
            'reg_no2' => trim((string) $row->RegNo2),
        ];

        return '/checkout?' . http_build_query(array_filter($query, fn ($value) => $value !== '' && $value !== null));
    };
@endphp
<div class="checkout-section">
    <div class="checkout-section-header">
        <h3>Checkout Directory</h3>
        <span class="checkout-folio-meta">{{ $checkoutScope === 'room' ? 'Choose one room from a group to checkout.' : 'Choose a live registration to checkout all active rooms.' }}</span>
    </div>
    <div class="checkout-section-body checkout-section-main-body" id="checkoutDirectoryShell">
        <div class="checkout-directory-loading" aria-hidden="true">
            <div class="checkout-directory-loading-card" role="status" aria-live="polite">
                <span class="checkout-directory-loading-spinner"></span>
                <span>Loading checkout grid...</span>
            </div>
        </div>

        <form method="GET" action="/checkout" id="checkoutSearchForm" class="mb-4">
            <input type="hidden" name="sort_by" value="{{ $sortBy }}">
            <input type="hidden" name="sort_dir" value="{{ $sortDir }}">
            <input type="hidden" name="checkout_scope" value="{{ $checkoutScope }}">
            @if($selectedRegNo !== '')
                <input type="hidden" name="reg_no" value="{{ $selectedRegNo }}">
            @endif
            @if($selectedRegNo2 !== '')
                <input type="hidden" name="reg_no2" value="{{ $selectedRegNo2 }}">
            @endif

            <div class="checkout-search-grid">
                <div class="checkout-field">
                    <label for="search">Search Keyword</label>
                    <input type="text" name="search" id="search" class="form-control package-input" value="{{ $search }}" placeholder="Search reg number, guest, room, company, or package">
                </div>
                <div class="checkout-field">
                    <label for="per_page">Rows Per Page</label>
                    <select name="per_page" id="per_page" class="form-control package-select">
                        @foreach ([10, 25, 50] as $size)
                            <option value="{{ $size }}" {{ (int) $perPage === $size ? 'selected' : '' }}>{{ $size }} rows</option>
                        @endforeach
                    </select>
                </div>
                <div class="checkout-actions" style="justify-content: flex-start; align-items: end;">
                    <button type="submit" class="btn package-btn-primary">Search</button>
                    <a href="/checkout?checkout_scope={{ urlencode($checkoutScope) }}" class="btn package-btn-secondary">Clear</a>
                </div>
            </div>
        </form>

        <div class="package-table-wrap checkout-directory-table-wrap">
            <table class="table package-table checkout-directory-table mb-0">
                <thead>
                    <tr>
                        <th class="{{ $sortBy === 'reg_no' ? 'is-sorted' : '' }}">
                            <a href="{{ $sortUrl('reg_no') }}" data-sort-column="reg_no" class="checkout-sort-link {{ $sortBy === 'reg_no' ? 'is-active' : '' }}">
                                Reg Number <i class="{{ $sortIcon('reg_no') }}"></i>
                            </a>
                        </th>
                        <th class="{{ $sortBy === 'guest' ? 'is-sorted' : '' }}">
                            <a href="{{ $sortUrl('guest') }}" data-sort-column="guest" class="checkout-sort-link {{ $sortBy === 'guest' ? 'is-active' : '' }}">
                                Guest <i class="{{ $sortIcon('guest') }}"></i>
                            </a>
                        </th>
                        <th class="{{ $sortBy === 'room' ? 'is-sorted' : '' }}">
                            <a href="{{ $sortUrl('room') }}" data-sort-column="room" class="checkout-sort-link {{ $sortBy === 'room' ? 'is-active' : '' }}">
                                Room <i class="{{ $sortIcon('room') }}"></i>
                            </a>
                        </th>
                        <th class="{{ $sortBy === 'check_in' ? 'is-sorted' : '' }}">
                            <a href="{{ $sortUrl('check_in') }}" data-sort-column="check_in" class="checkout-sort-link {{ $sortBy === 'check_in' ? 'is-active' : '' }}">
                                Check In <i class="{{ $sortIcon('check_in') }}"></i>
                            </a>
                        </th>
                        <th class="text-right {{ $sortBy === 'nominal' ? 'is-sorted' : '' }}">
                            <a href="{{ $sortUrl('nominal') }}" data-sort-column="nominal" class="checkout-sort-link {{ $sortBy === 'nominal' ? 'is-active' : '' }}">
                                Nominal <i class="{{ $sortIcon('nominal') }}"></i>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($directory as $row)
                        @php
                            $isActive = $selectedRegNo === trim((string) $row->RegNo)
                                && ($checkoutScope === 'all' || $selectedRegNo2 === trim((string) $row->RegNo2));
                            $href = $rowUrl($row);
                        @endphp
                        <tr class="checkout-directory-row {{ $isActive ? 'is-active' : '' }}">
                            <td><a href="{{ $href }}"><span class="package-code">{{ trim((string) $row->RegNo) }}</span></a></td>
                            <td><a href="{{ $href }}"><strong>{{ trim((string) $row->Guest) }}</strong><br><span class="folio-muted">{{ trim((string) $row->Usaha) !== '' ? trim((string) $row->Usaha) : trim((string) $row->Tipe) }}</span></a></td>
                            <td><a href="{{ $href }}">{{ trim((string) $row->Kode) }}</a></td>
                            <td><a href="{{ $href }}">{{ $row->check_in_date }}</a></td>
                            <td class="text-right"><a href="{{ $href }}">Rp {{ $row->nominal_display }}</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="package-empty">Belum ada data guest aktif yang siap checkout.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="checkout-directory-footer">
            <div class="checkout-footer-card">
                <div class="checkout-footer-copy">
                    <strong>Directory Navigation</strong>
                    Gunakan pagination untuk berpindah antar daftar guest aktif tanpa kehilangan fokus kerja di halaman checkout.
                </div>
                <div class="checkout-footer-pagination">
                    @if($directory->hasPages())
                        <div class="package-pagination-wrap">
                            {{ $directory->onEachSide(1)->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
