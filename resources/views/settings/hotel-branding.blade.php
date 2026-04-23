@extends('layouts.app')

@section('title', '')

@section('topbar_brand')
    <div class="hotel-branding-topbar-title">Hotel Branding</div>
@endsection

@section('content')
@include('partials.crud-package-theme')

<style>
    .hotel-branding-topbar-title {
        color: #173761;
        font-size: 2rem;
        line-height: 1;
        font-weight: 900;
        letter-spacing: -0.04em;
        font-family: "Segoe UI", "Trebuchet MS", "Helvetica Neue", Arial, sans-serif;
    }

    .branding-shell {
        display: grid;
        grid-template-columns: minmax(0, 1.12fr) minmax(280px, 0.7fr);
        gap: 1.25rem;
    }

    .branding-card {
        background: #fff;
        border: 1px solid #dbe8ff;
        border-radius: 22px;
        box-shadow: 0 12px 30px rgba(16, 35, 59, 0.06);
        overflow: hidden;
    }

    .branding-head {
        padding: 1rem 1.2rem;
        border-bottom: 1px solid rgba(137, 167, 214, 0.22);
        background: linear-gradient(180deg, #f2f7ff 0%, #e8f1ff 100%);
    }

    .branding-head h3 {
        margin: 0;
        color: #173761;
        font-size: 1.15rem;
        font-weight: 800;
    }

    .branding-head p {
        margin: 0.28rem 0 0;
        color: #60748f;
        font-size: 0.9rem;
    }

    .branding-body {
        padding: 1.2rem;
    }

    .branding-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .branding-field {
        display: grid;
        gap: 0.45rem;
    }

    .branding-field-wide {
        grid-column: span 2;
    }

    .branding-field label {
        margin: 0;
        color: #173761;
        font-size: 0.92rem;
        font-weight: 700;
    }

    .branding-logo-panel {
        display: grid;
        gap: 1rem;
    }

    .branding-logo-preview {
        min-height: 240px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        border: 1px dashed rgba(84, 110, 180, 0.35);
        border-radius: 20px;
        background: linear-gradient(180deg, #fbfdff 0%, #f2f7ff 100%);
    }

    .branding-logo-preview img {
        max-width: 100%;
        max-height: 180px;
        object-fit: contain;
    }

    .branding-logo-empty {
        color: #77879c;
        text-align: center;
        font-size: 0.92rem;
        line-height: 1.55;
    }

    .branding-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        margin-top: 1.25rem;
        flex-wrap: wrap;
    }

    .branding-theme-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 0.9rem;
    }

    .branding-theme-card {
        position: relative;
        display: grid;
        gap: 0.7rem;
        padding: 0.95rem 1rem;
        border: 1px solid rgba(137, 167, 214, 0.24);
        border-radius: 20px;
        background: linear-gradient(180deg, #fbfdff 0%, #f5f8ff 100%);
        cursor: pointer;
    }

    .branding-theme-card input {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
    }

    .branding-theme-card.is-active {
        border-color: rgba(23, 55, 97, 0.5);
        box-shadow: 0 14px 28px rgba(16, 35, 59, 0.08);
    }

    .branding-theme-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        color: #173761;
        font-size: 0.96rem;
        font-weight: 800;
    }

    .branding-theme-title small {
        color: #6f7f95;
        font-size: 0.76rem;
        font-weight: 700;
    }

    .branding-theme-swatches {
        display: flex;
        gap: 0.45rem;
    }

    .branding-theme-swatch {
        flex: 1 1 0;
        height: 34px;
        border-radius: 12px;
        border: 1px solid rgba(16, 35, 59, 0.08);
    }

    .branding-theme-desc {
        color: #5d718b;
        font-size: 0.86rem;
        line-height: 1.55;
    }

    .branding-checkbox {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        color: #173761;
        font-size: 0.92rem;
        font-weight: 700;
    }

    .branding-note {
        padding: 0.95rem 1rem;
        border-radius: 18px;
        background: #f8fbff;
        border: 1px solid rgba(137, 167, 214, 0.24);
        color: #566b88;
        font-size: 0.88rem;
        line-height: 1.6;
    }

    .branding-mini-table {
        width: 100%;
        border-collapse: collapse;
    }

    .branding-mini-table td {
        padding: 0.38rem 0;
        vertical-align: top;
        color: #41546f;
        font-size: 0.9rem;
    }

    .branding-mini-table td:first-child {
        width: 92px;
        color: #173761;
        font-weight: 700;
        white-space: nowrap;
    }

    @media (max-width: 991.98px) {
        .branding-shell,
        .branding-grid {
            grid-template-columns: 1fr;
        }

        .branding-field-wide {
            grid-column: span 1;
        }
    }
</style>

@php
    $themePreviewMap = [];
    foreach (array_keys($themeOptions) as $themeKey) {
        $themePreviewMap[$themeKey] = \App\Support\HotelBranding::themeVariables(['FormTheme' => $themeKey]);
    }
    $activeThemeKey = old('FormTheme', $profile['FormTheme'] ?? 'ocean-blue');
@endphp

<section class="package-shell">
    <div class="branding-shell">
        <form method="POST" action="/settings/hotel-branding" enctype="multipart/form-data" class="branding-card">
            @csrf
            <div class="branding-head">
                <h3>Hotel Profile Setup</h3>
                <p>These fields stay connected to the legacy setup table, while the form labels are simplified for easier input.</p>
            </div>
            <div class="branding-body">
                <div class="branding-grid">
                    <div class="branding-field">
                        <label for="NamaPT">Hotel Name</label>
                        <input type="text" name="NamaPT" id="NamaPT" class="form-control package-input" value="{{ old('NamaPT', $profile['NamaPT'] ?? '') }}">
                    </div>
                    <div class="branding-field">
                        <label for="UsahaPT">Business Type</label>
                        <input type="text" name="UsahaPT" id="UsahaPT" class="form-control package-input" value="{{ old('UsahaPT', $profile['UsahaPT'] ?? '') }}">
                    </div>
                    <div class="branding-field">
                        <label for="FaxPT">Fax</label>
                        <input type="text" name="FaxPT" id="FaxPT" class="form-control package-input" value="{{ old('FaxPT', $profile['FaxPT'] ?? '') }}">
                    </div>
                    <div class="branding-field">
                        <label for="TelponPT">Phone</label>
                        <input type="text" name="TelponPT" id="TelponPT" class="form-control package-input" value="{{ old('TelponPT', $profile['TelponPT'] ?? '') }}">
                    </div>
                    <div class="branding-field branding-field-wide">
                        <label for="AlamatPT">Address</label>
                        <input type="text" name="AlamatPT" id="AlamatPT" class="form-control package-input" value="{{ old('AlamatPT', $profile['AlamatPT'] ?? '') }}">
                    </div>
                    <div class="branding-field">
                        <label for="AlamatPT2">Region / State</label>
                        <input type="text" name="AlamatPT2" id="AlamatPT2" class="form-control package-input" value="{{ old('AlamatPT2', $profile['AlamatPT2'] ?? '') }}">
                    </div>
                    <div class="branding-field">
                        <label for="WebsitePT">Website</label>
                        <input type="text" name="WebsitePT" id="WebsitePT" class="form-control package-input" value="{{ old('WebsitePT', $profile['WebsitePT'] ?? '') }}">
                    </div>
                    <div class="branding-field branding-field-wide">
                        <label for="EmailPT">Email</label>
                        <input type="text" name="EmailPT" id="EmailPT" class="form-control package-input" value="{{ old('EmailPT', $profile['EmailPT'] ?? '') }}">
                    </div>
                    <div class="branding-field branding-field-wide">
                        <label for="logo">Hotel Logo</label>
                        <input type="file" name="logo" id="logo" class="form-control package-input" accept="image/*">
                    </div>
                    <div class="branding-field branding-field-wide">
                        <label>Form Theme</label>
                        <div class="branding-theme-grid">
                            @foreach ($themeOptions as $themeKey => $theme)
                                @php
                                    $isActiveTheme = $activeThemeKey === $themeKey;
                                @endphp
                                <label class="branding-theme-card {{ $isActiveTheme ? 'is-active' : '' }}">
                                    <input type="radio" name="FormTheme" value="{{ $themeKey }}" {{ $isActiveTheme ? 'checked' : '' }}>
                                    <div class="branding-theme-title">
                                        <span>{{ $theme['label'] }}</span>
                                        <small>{{ $themeKey }}</small>
                                    </div>
                                    <div class="branding-theme-swatches">
                                        @foreach ($theme['swatches'] as $swatch)
                                            <span class="branding-theme-swatch" style="background: {{ $swatch }};"></span>
                                        @endforeach
                                    </div>
                                    <div class="branding-theme-desc">{{ $theme['description'] }}</div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="branding-actions">
                    <label class="branding-checkbox" for="remove_logo">
                        <input type="checkbox" name="remove_logo" id="remove_logo" value="1">
                        Remove current logo
                    </label>
                    <button type="submit" class="btn package-btn-primary">Save Branding</button>
                </div>
            </div>
        </form>

        <div class="branding-card branding-logo-panel">
            <div class="branding-head">
                <h3>Branding Preview</h3>
                <p>The sample below changes instantly when you choose a theme. All real forms update only after you save.</p>
            </div>
            <div class="branding-body">
                <div class="branding-logo-preview">
                    @if (!empty($profile['logo_url']))
                        <img src="{{ $profile['logo_url'] }}" alt="Hotel logo preview">
                    @else
                        <div class="branding-logo-empty">
                            No logo uploaded yet.
                            <br>Upload an image to show the logo in the top-left corner.
                        </div>
                    @endif
                </div>

                <div class="branding-note">
                    <table class="branding-mini-table">
                        <tr>
                            <td>Hotel Name</td>
                            <td>{{ $profile['NamaPT'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Business</td>
                            <td>{{ $profile['UsahaPT'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Address</td>
                            <td>{{ $profile['AlamatPT'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Region</td>
                            <td>{{ $profile['AlamatPT2'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Phone</td>
                            <td>{{ $profile['TelponPT'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Website</td>
                            <td>{{ $profile['WebsitePT'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>{{ $profile['EmailPT'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Form Theme</td>
                            <td>{{ $themeOptions[$profile['FormTheme'] ?? 'ocean-blue']['label'] ?? ($profile['FormTheme'] ?? '-') }}</td>
                        </tr>
                    </table>
                </div>

                <div id="themeLivePreview" style="margin-top: 1rem; border-radius: 22px; padding: 1rem; background: var(--preview-page-bg);">
                    <div style="background: var(--preview-shell-bg); border: 1px solid var(--preview-shell-border); box-shadow: var(--preview-shell-shadow), inset 0 1px 0 rgba(255, 255, 255, 0.7); border-radius: 24px; overflow: hidden;">
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: 1rem 1.2rem 0.85rem; background: var(--preview-header-bg); border-bottom: 1px solid var(--preview-shell-border);">
                            <div style="display: inline-block; padding: 0.9rem 1rem; border-radius: 20px; background: var(--preview-heading-bg); border: 1px solid var(--preview-heading-border);">
                                <div style="margin: 0; font-family: Georgia, 'Times New Roman', serif; font-size: 1.8rem; color: var(--preview-title); line-height: 1;">Sample Form</div>
                                <div style="margin-top: 0.35rem; font-size: 0.9rem; color: var(--preview-muted);">Preview tema inputan sebelum disimpan</div>
                            </div>
                            <div style="display: inline-flex; align-items: center; padding: 0.5rem 0.85rem; border-radius: 999px; background: var(--preview-badge-bg); color: var(--preview-badge-text); border: 1px solid var(--preview-shell-border); font-size: 0.8rem; font-weight: 700;">
                                Front Office
                            </div>
                        </div>
                        <div style="padding: 1rem 1.2rem 1.2rem;">
                            <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.9rem;">
                                <div style="display: grid; gap: 0.4rem;">
                                    <label style="font-size: 0.82rem; font-weight: 900; letter-spacing: 0.08em; text-transform: uppercase; color: var(--preview-label);">Guest Name</label>
                                    <input type="text" value="Sample Guest" readonly style="height: calc(2.7rem + 2px); border-radius: 14px; border: 1px solid var(--preview-input-border); background: var(--preview-input-bg); color: var(--preview-text); font-weight: 600; padding: 0.6rem 0.8rem;">
                                </div>
                                <div style="display: grid; gap: 0.4rem;">
                                    <label style="font-size: 0.82rem; font-weight: 900; letter-spacing: 0.08em; text-transform: uppercase; color: var(--preview-label);">Room Number</label>
                                    <input type="text" value="1102" readonly style="height: calc(2.7rem + 2px); border-radius: 14px; border: 1px solid var(--preview-input-border); background: var(--preview-input-bg); color: var(--preview-text); font-weight: 600; padding: 0.6rem 0.8rem;">
                                </div>
                                <div style="display: grid; gap: 0.4rem;">
                                    <label style="font-size: 0.82rem; font-weight: 900; letter-spacing: 0.08em; text-transform: uppercase; color: var(--preview-label);">Payment</label>
                                    <select disabled style="height: calc(2.7rem + 2px); border-radius: 14px; border: 1px solid var(--preview-input-border); background: var(--preview-input-bg); color: var(--preview-text); font-weight: 600; padding: 0.6rem 0.8rem;">
                                        <option>OTA</option>
                                    </select>
                                </div>
                                <div style="display: grid; gap: 0.4rem;">
                                    <label style="font-size: 0.82rem; font-weight: 900; letter-spacing: 0.08em; text-transform: uppercase; color: var(--preview-label);">Phone</label>
                                    <input type="text" value="08123456789" readonly style="height: calc(2.7rem + 2px); border-radius: 14px; border: 1px solid var(--preview-input-border); background: var(--preview-input-bg); color: var(--preview-text); font-weight: 600; padding: 0.6rem 0.8rem;">
                                </div>
                            </div>

                            <div style="margin-top: 1rem; border-radius: 18px; overflow: hidden; border: 1px solid var(--preview-shell-border);">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr>
                                            <th style="padding: 0.85rem 1rem; text-align: left; background: var(--preview-table-head-bg); color: var(--preview-title); font-size: 0.73rem; letter-spacing: 0.08em; text-transform: uppercase;">Room</th>
                                            <th style="padding: 0.85rem 1rem; text-align: left; background: var(--preview-table-head-bg); color: var(--preview-title); font-size: 0.73rem; letter-spacing: 0.08em; text-transform: uppercase;">Guest</th>
                                            <th style="padding: 0.85rem 1rem; text-align: left; background: var(--preview-table-head-bg); color: var(--preview-title); font-size: 0.73rem; letter-spacing: 0.08em; text-transform: uppercase;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr style="background: var(--preview-table-odd);">
                                            <td style="padding: 0.9rem 1rem; color: var(--preview-text);">1102</td>
                                            <td style="padding: 0.9rem 1rem; color: var(--preview-text);">Sample Guest</td>
                                            <td style="padding: 0.9rem 1rem; color: var(--preview-text);">STAY</td>
                                        </tr>
                                        <tr style="background: var(--preview-table-even);">
                                            <td style="padding: 0.9rem 1rem; color: var(--preview-text);">1103</td>
                                            <td style="padding: 0.9rem 1rem; color: var(--preview-text);">Another Guest</td>
                                            <td style="padding: 0.9rem 1rem; color: var(--preview-text);">BOOKED</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div style="display: flex; gap: 0.7rem; margin-top: 1rem; flex-wrap: wrap;">
                                <button type="button" style="border: 0; border-radius: 999px; padding: 0.78rem 1.3rem; font-weight: 700; background: var(--preview-button-primary); color: #fff;">Save Check In</button>
                                <button type="button" style="border-radius: 999px; padding: 0.74rem 1.2rem; font-weight: 700; border: 1px solid var(--preview-input-border); background: var(--preview-button-secondary-bg); color: var(--preview-button-secondary-text);">Print</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    (function () {
        const previewRoot = document.getElementById('themeLivePreview');
        const themeCards = Array.from(document.querySelectorAll('.branding-theme-card'));
        const themeInputs = Array.from(document.querySelectorAll('input[name="FormTheme"]'));
        const themeMap = @json($themePreviewMap);

        if (!previewRoot || !themeInputs.length) {
            return;
        }

        function applyPreviewTheme(themeKey) {
            const theme = themeMap[themeKey] || themeMap['ocean-blue'];
            if (!theme) {
                return;
            }

            previewRoot.style.setProperty('--preview-page-bg', theme.page_bg);
            previewRoot.style.setProperty('--preview-shell-bg', theme.shell_bg);
            previewRoot.style.setProperty('--preview-shell-border', theme.shell_border);
            previewRoot.style.setProperty('--preview-shell-shadow', theme.shell_shadow);
            previewRoot.style.setProperty('--preview-header-bg', theme.header_bg);
            previewRoot.style.setProperty('--preview-heading-bg', theme.heading_bg);
            previewRoot.style.setProperty('--preview-heading-border', theme.heading_border);
            previewRoot.style.setProperty('--preview-title', theme.title);
            previewRoot.style.setProperty('--preview-text', theme.text);
            previewRoot.style.setProperty('--preview-muted', theme.muted);
            previewRoot.style.setProperty('--preview-label', theme.label);
            previewRoot.style.setProperty('--preview-badge-bg', theme.badge_bg);
            previewRoot.style.setProperty('--preview-badge-text', theme.badge_text);
            previewRoot.style.setProperty('--preview-input-bg', theme.input_bg);
            previewRoot.style.setProperty('--preview-input-border', theme.input_border);
            previewRoot.style.setProperty('--preview-button-primary', theme.button_primary);
            previewRoot.style.setProperty('--preview-button-secondary-bg', theme.button_secondary_bg);
            previewRoot.style.setProperty('--preview-button-secondary-text', theme.button_secondary_text);
            previewRoot.style.setProperty('--preview-table-head-bg', theme.table_head_bg);
            previewRoot.style.setProperty('--preview-table-odd', theme.table_odd);
            previewRoot.style.setProperty('--preview-table-even', theme.table_even);
        }

        function syncThemeSelection(themeKey) {
            themeCards.forEach((card) => {
                const radio = card.querySelector('input[name="FormTheme"]');
                card.classList.toggle('is-active', !!radio && radio.value === themeKey);
            });
            applyPreviewTheme(themeKey);
        }

        themeInputs.forEach((input) => {
            input.addEventListener('change', function () {
                if (input.checked) {
                    syncThemeSelection(input.value);
                }
            });
        });

        const initialTheme = themeInputs.find((input) => input.checked)?.value || 'ocean-blue';
        syncThemeSelection(initialTheme);
    })();
</script>
@endsection
