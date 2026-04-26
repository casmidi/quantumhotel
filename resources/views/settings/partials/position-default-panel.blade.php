@php
    $existingDefaultMenus = $position['menus']->pluck('ket')->flip();
    $missingDefaultMenus = $allMenus->reject(fn ($menu) => $existingDefaultMenus->has($menu['ket']))->values();
@endphp

<div data-position-content>
    <div class="authorization-position-feedback" data-position-feedback hidden></div>
    <form method="POST" action="/settings/user-authorization/positions/menus" class="authorization-position-toolbar" data-position-ajax-form>
        @csrf
        <input type="hidden" name="position" value="{{ $position['position'] }}">
        <input type="hidden" name="mode" value="selected" data-position-menu-mode>
        <div class="authorization-field">
            <label for="position_menu_{{ $positionIndex }}">Add Default Menu</label>
            <select name="menu_ket" id="position_menu_{{ $positionIndex }}" class="form-control package-input" {{ $missingDefaultMenus->isEmpty() ? 'disabled' : '' }}>
                @if ($missingDefaultMenus->isEmpty())
                    <option value="">All menus already added</option>
                @else
                    @foreach ($missingDefaultMenus as $menu)
                        <option value="{{ $menu['ket'] }}">{{ $menu['ket'] }} - {{ $menu['kunci'] }}</option>
                    @endforeach
                @endif
            </select>
        </div>
        <button type="submit" class="btn package-btn-primary" {{ $missingDefaultMenus->isEmpty() ? 'disabled' : '' }}>Save Selected</button>
        <button type="submit" class="btn btn-sm authorization-secondary-btn" data-add-all-position-menus {{ $missingDefaultMenus->isEmpty() ? 'disabled' : '' }}>Save All</button>
    </form>
    <div class="authorization-position-summary">
        <div class="authorization-position-summary-title">
            <strong>Default Accessible Menus - {{ $position['position'] }}</strong>
            <span>{{ $position['menus']->count() }} menus recorded</span>
        </div>
        <div class="authorization-sync-action">
            <div class="authorization-sync-copy">Update all {{ $position['position'] }} users with these defaults.</div>
            <form method="POST" action="/settings/user-authorization/positions/apply" data-position-ajax-form data-confirm-sync="Apply these default menus to all users in this position?">
                @csrf
                <input type="hidden" name="position" value="{{ $position['position'] }}">
                <button type="submit" class="btn authorization-sync-btn" {{ $position['menus']->isEmpty() ? 'disabled' : '' }}>
                    <i class="fas fa-sync-alt" aria-hidden="true"></i>
                    <span>Sync to Users</span>
                </button>
            </form>
        </div>
    </div>
    <div class="authorization-table-wrap">
        @if ($position['menus']->isEmpty())
            <div class="authorization-empty">No default accessible menus are recorded for this position.</div>
        @else
            <table class="authorization-table">
                <thead>
                    <tr>
                        <th style="width: 90px;">Code</th>
                        <th>Menu</th>
                        <th>Menu Key</th>
                        <th style="width: 130px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($position['menus'] as $menu)
                        <tr>
                            <td><span class="authorization-menu-code">{{ $menu['code'] }}</span></td>
                            <td>{{ $menu['ket'] }}</td>
                            <td class="authorization-master-key">{{ $menu['kunci'] }}</td>
                            <td>
                                <form method="POST" action="/settings/user-authorization/positions/menus/delete" data-position-ajax-form data-confirm-delete="Are you sure you want to delete this default menu?">
                                    @csrf
                                    <input type="hidden" name="position" value="{{ $position['position'] }}">
                                    <input type="hidden" name="ket" value="{{ $menu['ket'] }}">
                                    <button type="submit" class="btn btn-sm authorization-danger-btn authorization-icon-btn" title="Delete" aria-label="Delete {{ $menu['ket'] }}">
                                        <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                        <span class="authorization-visually-hidden">Delete</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
