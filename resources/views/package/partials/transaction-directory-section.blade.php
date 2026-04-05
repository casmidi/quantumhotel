@php
    $sortBy = $sortBy ?? 'invoice';
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
<section class="package-shell" id="packageDirectoryShell">
        <div class="package-shell-header">
            <div>
                <h2 class="package-shell-title">Transaction Directory</h2>
                <p class="package-shell-subtitle">Click a row to load the transaction into the form and continue in update mode.</p>
            </div>
            <span class="package-shell-badge">{{ number_format($summary['total'], 0, ',', '.') }} Records</span>
        </div>
        <div class="package-shell-body pb-0">
            <form method="GET" action="/menu-package-transaction" class="package-search-form">
                <div class="package-search-group">
                    <label class="package-label" for="search_type">Search Option</label>
                    <select name="search_type" id="search_type" class="form-control package-select">
                        <option value="all" {{ ($searchType ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                        <option value="invoice" {{ ($searchType ?? '') === 'invoice' ? 'selected' : '' }}>Invoice</option>
                        <option value="package" {{ ($searchType ?? '') === 'package' ? 'selected' : '' }}>Package Code / Name</option>
                        <option value="nominal" {{ ($searchType ?? '') === 'nominal' ? 'selected' : '' }}>Package Nominal</option>
                    </select>
                </div>
                <div class="package-search-group">
                    <label class="package-label" for="search">Search Keyword</label>
                    <input type="text" name="search" id="search" class="form-control package-input" value="{{ $searchValue ?? '' }}" placeholder="Search invoice, package code, or nominal">
                </div>
                <div class="package-search-actions">
                    <button type="submit" class="btn package-btn-primary"><i class="fa-solid fa-magnifying-glass mr-2"></i>Search</button>
                    <a href="/menu-package-transaction" class="btn package-btn-secondary">Clear</a>
                </div>
            </form>
        </div>
        <div class="package-table-wrap"><div class="table-responsive"><table class="table package-table" id="tablePackageTransaction"><thead><tr><th><a href="{{ $sortUrl('invoice') }}" class="package-sort-link {{ $sortBy === 'invoice' ? 'is-active' : '' }}">Invoice <i class="{{ $sortIcon('invoice') }}"></i></a></th><th><a href="{{ $sortUrl('package') }}" class="package-sort-link {{ $sortBy === 'package' ? 'is-active' : '' }}">Package Code <i class="{{ $sortIcon('package') }}"></i></a></th><th><a href="{{ $sortUrl('items') }}" class="package-sort-link {{ $sortBy === 'items' ? 'is-active' : '' }}">Items <i class="{{ $sortIcon('items') }}"></i></a></th><th><a href="{{ $sortUrl('expired') }}" class="package-sort-link {{ $sortBy === 'expired' ? 'is-active' : '' }}">Expired <i class="{{ $sortIcon('expired') }}"></i></a></th><th class="text-right"><a href="{{ $sortUrl('nominal') }}" class="package-sort-link {{ $sortBy === 'nominal' ? 'is-active' : '' }}">Nominal <i class="{{ $sortIcon('nominal') }}"></i></a></th><th class="text-center" width="90">Action</th></tr></thead><tbody>
            @forelse($packages as $package)
            <tr class="{{ $package->is_used ? 'package-row-locked' : '' }}" data-nofak="{{ $package->Nofak }}" data-meja="{{ $package->Meja }}" data-expired="{{ \Carbon\Carbon::parse($package->Expired)->format('Y-m-d') }}" data-details='@json(json_decode($package->detail_json, true))' data-used="{{ $package->is_used ? '1' : '0' }}">
                <td>
                    <span class="package-code">{{ $package->Nofak }}</span>

                </td>
                <td>{{ $package->Meja }}</td>
                <td>{{ $package->detail_summary }}</td>
                <td>{{ \Carbon\Carbon::parse($package->Expired)->format('d-m-Y') }}</td>
                <td class="text-right">Rp {{ number_format($package->JumlahRes ?? 0, 0, ',', '.') }}</td>
                <td class="text-center">
                    @if($package->is_used)
                    <span class="package-disabled-action" title="This package transaction is already used." aria-label="Locked"><i class="fa-solid fa-lock"></i></span>
                    @else
                    <a href="/menu-package-transaction/{{ $package->Nofak }}/delete" class="package-delete" title="Delete" aria-label="Delete" data-confirm-delete="Do you want to delete this package transaction?">&#128465;</a>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="package-empty">No package transactions found for the current filter.</td></tr>
            @endforelse
        </tbody></table></div></div>
        @if($packages->hasPages())
        @php
            $currentPage = $packages->currentPage();
            $lastPage = $packages->lastPage();
            $startPage = max(1, $currentPage - 2);
            $endPage = min($lastPage, $currentPage + 2);
        @endphp
        <div class="package-pagination-wrap">
            <ul class="package-pagination">
                <li class="package-page-item {{ $packages->onFirstPage() ? 'disabled' : '' }}"><a class="package-page-link" href="{{ $packages->previousPageUrl() ?: '#' }}">&lt;</a></li>
                @if($startPage > 1)
                <li class="package-page-item"><a class="package-page-link" href="{{ $packages->url(1) }}">1</a></li>
                @if($startPage > 2)
                <li class="package-page-item disabled"><span class="package-page-link">...</span></li>
                @endif
                @endif
                @for($page = $startPage; $page <= $endPage; $page++)
                <li class="package-page-item {{ $page === $currentPage ? 'active' : '' }}"><a class="package-page-link" href="{{ $packages->url($page) }}">{{ $page }}</a></li>
                @endfor
                @if($endPage < $lastPage)
                @if($endPage < $lastPage - 1)
                <li class="package-page-item disabled"><span class="package-page-link">...</span></li>
                @endif
                <li class="package-page-item"><a class="package-page-link" href="{{ $packages->url($lastPage) }}">{{ $lastPage }}</a></li>
                @endif
                <li class="package-page-item {{ $packages->hasMorePages() ? '' : 'disabled' }}"><a class="package-page-link" href="{{ $packages->nextPageUrl() ?: '#' }}">&gt;</a></li>
            </ul>
        </div>
        @endif
    </section>