@php
    $brandingProfile = \App\Support\HotelBranding::profile();
    $brandingTheme = \App\Support\HotelBranding::themeVariables($brandingProfile);
@endphp
<style>
    :root {
        --package-page-bg: {{ $brandingTheme['page_bg'] }};
        --package-shell-bg: {{ $brandingTheme['shell_bg'] }};
        --package-shell-border: {{ $brandingTheme['shell_border'] }};
        --package-shell-shadow: {{ $brandingTheme['shell_shadow'] }};
        --package-header-bg: {{ $brandingTheme['header_bg'] }};
        --package-heading-bg: {{ $brandingTheme['heading_bg'] }};
        --package-heading-border: {{ $brandingTheme['heading_border'] }};
        --package-title: {{ $brandingTheme['title'] }};
        --package-text: {{ $brandingTheme['text'] }};
        --package-muted: {{ $brandingTheme['muted'] }};
        --package-label: {{ $brandingTheme['label'] }};
        --package-badge-bg: {{ $brandingTheme['badge_bg'] }};
        --package-badge-text: {{ $brandingTheme['badge_text'] }};
        --package-input-bg: {{ $brandingTheme['input_bg'] }};
        --package-input-border: {{ $brandingTheme['input_border'] }};
        --package-input-focus: {{ $brandingTheme['input_focus'] }};
        --package-input-focus-shadow: {{ $brandingTheme['input_shadow'] }};
        --package-button-primary: {{ $brandingTheme['button_primary'] }};
        --package-button-secondary-bg: {{ $brandingTheme['button_secondary_bg'] }};
        --package-button-secondary-text: {{ $brandingTheme['button_secondary_text'] }};
        --package-table-head-bg: {{ $brandingTheme['table_head_bg'] }};
        --package-table-odd: {{ $brandingTheme['table_odd'] }};
        --package-table-even: {{ $brandingTheme['table_even'] }};
        --package-table-hover: {{ $brandingTheme['table_hover'] }};
        --package-table-hover-accent: {{ $brandingTheme['table_hover_accent'] }};
        --package-row-focus-bg: #1f6b52;
        --package-row-focus-ring: #b88a34;
        --package-row-focus-text: #fffaf0;
        --package-row-focus-shadow: rgba(6, 46, 35, 0.16);
    }

    .content-wrapper {
        background: var(--package-page-bg);
        min-height: 100vh;
    }

    .content-wrapper > h3 {
        display: none;
    }

    .package-page,
    .kelas-page,
    .room-page,
    .crud-page {
        padding: 0 0 2rem;
        color: var(--package-text);
    }

    .package-shell,
    .kelas-shell,
    .room-shell,
    .crud-shell {
        background: var(--package-shell-bg);
        border: 1px solid var(--package-shell-border);
        box-shadow: var(--package-shell-shadow), inset 0 1px 0 rgba(255, 255, 255, 0.7);
        border-radius: 28px;
        overflow: hidden;
    }

    .package-shell + .package-shell,
    .kelas-shell + .kelas-shell,
    .room-shell + .room-shell,
    .crud-shell + .crud-shell {
        margin-top: 1.5rem;
    }

    .package-shell-header,
    .kelas-shell-header,
    .room-shell-header,
    .crud-shell-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.4rem 1.9rem 0.9rem;
        background: var(--package-header-bg);
        border-bottom: 1px solid var(--package-shell-border);
    }

    .package-shell-heading-block,
    .crud-shell-heading-block {
        display: inline-block;
        padding: 1rem 1.35rem 0.95rem;
        border-radius: 22px;
        background: var(--package-heading-bg);
        border: 1px solid var(--package-heading-border);
        box-shadow: 0 16px 34px rgba(16, 35, 59, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .package-shell-title,
    .kelas-shell-title,
    .room-shell-title,
    .crud-shell-title {
        margin: 0;
        font-size: 1.08rem;
        font-weight: 700;
        color: var(--package-text);
    }

    .package-shell-subtitle,
    .kelas-shell-subtitle,
    .room-shell-subtitle,
    .crud-shell-subtitle {
        margin: 0.35rem 0 0;
        font-size: 0.92rem;
        color: var(--package-muted);
    }

    .package-shell-heading-block .package-shell-title,
    .crud-shell-heading-block .crud-shell-title {
        font-family: Georgia, "Times New Roman", serif;
        font-size: 2.6rem;
        font-weight: 500;
        color: var(--package-title);
        line-height: 1;
        letter-spacing: 0.01em;
    }

    .package-shell-heading-block .package-shell-subtitle,
    .crud-shell-heading-block .crud-shell-subtitle {
        margin-top: 0.45rem;
        font-size: 1rem;
        color: var(--package-muted);
        max-width: 760px;
    }

    .package-shell-badge,
    .kelas-shell-badge,
    .room-shell-badge,
    .crud-shell-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.58rem 0.9rem;
        border-radius: 999px;
        background: var(--package-badge-bg);
        color: var(--package-badge-text);
        font-weight: 700;
        font-size: 0.8rem;
        border: 1px solid var(--package-shell-border);
    }

    .package-shell-body,
    .kelas-shell-body,
    .room-shell-body,
    .crud-shell-body {
        padding: 1.25rem 1.9rem 1.75rem;
    }

    .package-label,
    .kelas-label,
    .room-label,
    .crud-label {
        display: block;
        font-size: 0.86rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.09em;
        color: var(--package-label);
        margin-bottom: 0.55rem;
    }

    .package-input,
    .package-select,
    .kelas-input,
    .room-input,
    .crud-input,
    .crud-select {
        height: calc(2.7rem + 2px);
        border-radius: 14px;
        border: 1px solid var(--package-input-border);
        box-shadow: inset 0 1px 2px rgba(16, 35, 59, 0.04);
        background: var(--package-input-bg);
        color: var(--package-text);
        font-weight: 600;
    }

    .package-input:focus,
    .package-select:focus,
    .kelas-input:focus,
    .room-input:focus,
    .crud-input:focus,
    .crud-select:focus {
        border-color: var(--package-input-focus);
        box-shadow: var(--package-input-focus-shadow);
    }

    .package-date-group {
        position: relative;
    }

    .package-date-group .package-input {
        padding-right: 3.1rem;
    }

    .package-date-picker {
        position: absolute;
        top: 50%;
        right: 0.45rem;
        transform: translateY(-50%);
        width: 2.2rem;
        height: 2.2rem;
        border-radius: 12px;
        border: 1px solid var(--package-input-border);
        background: var(--package-heading-bg);
        color: var(--package-title);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 16px rgba(16, 35, 59, 0.08);
    }

    .package-date-picker:hover {
        background: var(--package-shell-bg);
        color: var(--package-title);
    }

    .package-date-native {
        position: absolute;
        inset: 0;
        opacity: 0;
        pointer-events: none;
    }

    .package-actions,
    .kelas-actions,
    .room-actions,
    .crud-actions {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-top: 0.5rem;
        flex-wrap: wrap;
    }

    .package-btn-primary,
    .kelas-btn-primary,
    .room-btn-primary,
    .crud-btn-primary {
        border: 0;
        border-radius: 999px;
        padding: 0.82rem 1.5rem;
        font-weight: 700;
        background: var(--package-button-primary);
        box-shadow: 0 14px 28px rgba(16, 35, 59, 0.18);
        color: #fff;
    }

    .package-btn-secondary,
    .kelas-btn-secondary,
    .room-btn-secondary,
    .crud-btn-secondary {
        border-radius: 999px;
        padding: 0.78rem 1.35rem;
        font-weight: 700;
        border: 1px solid var(--package-input-border);
        background: var(--package-button-secondary-bg);
        color: var(--package-button-secondary-text);
    }

    .package-alert,
    .kelas-alert,
    .room-alert,
    .crud-alert,
    .package-error,
    .crud-error {
        border: 0;
        border-radius: 18px;
        padding: 0.95rem 1.15rem;
        box-shadow: 0 14px 30px rgba(16, 35, 59, 0.08);
    }

    .package-alert,
    .kelas-alert,
    .room-alert,
    .crud-alert {
        background: linear-gradient(135deg, rgba(33, 150, 83, 0.16), rgba(33, 150, 83, 0.08));
        color: #1c6b40;
    }

    .package-error,
    .crud-error {
        background: linear-gradient(135deg, rgba(179, 52, 70, 0.16), rgba(179, 52, 70, 0.08));
        color: #8f2435;
    }

    .package-table-wrap,
    .kelas-table-wrap,
    .room-table-wrap,
    .crud-table-wrap {
        border-radius: 0 0 24px 24px;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
    }

    .package-table,
    .kelas-table,
    .room-table,
    .crud-table {
        margin-bottom: 0;
    }

    .package-table thead th,
    .kelas-table thead th,
    .room-table thead th,
    .crud-table thead th {
        border-top: 0;
        border-bottom: 1px solid rgba(16, 35, 59, 0.08);
        background: var(--package-table-head-bg);
        color: var(--package-title);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.74rem;
        font-weight: 700;
        padding: 1rem 1.2rem;
    }

    .package-table tbody tr,
    .kelas-table tbody tr,
    .room-table tbody tr,
    .crud-table tbody tr {
        transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
        cursor: pointer;
    }

    .package-table tbody tr:nth-child(odd),
    .kelas-table tbody tr:nth-child(odd),
    .room-table tbody tr:nth-child(odd),
    .crud-table tbody tr:nth-child(odd) {
        background: var(--package-table-odd);
    }

    .package-table tbody tr:nth-child(even),
    .kelas-table tbody tr:nth-child(even),
    .room-table tbody tr:nth-child(even),
    .crud-table tbody tr:nth-child(even) {
        background: var(--package-table-even);
    }

    .package-table tbody tr:hover,
    .kelas-table tbody tr:hover,
    .room-table tbody tr:hover,
    .crud-table tbody tr:hover,
    .package-table tbody tr:focus-within,
    .kelas-table tbody tr:focus-within,
    .room-table tbody tr:focus-within,
    .crud-table tbody tr:focus-within {
        background: var(--package-row-focus-bg);
        transform: translateY(-1px);
        box-shadow:
            0 0 0 2px var(--package-row-focus-ring),
            0 10px 22px var(--package-row-focus-shadow);
    }

    .package-table tbody tr:hover td,
    .kelas-table tbody tr:hover td,
    .room-table tbody tr:hover td,
    .crud-table tbody tr:hover td,
    .package-table tbody tr:focus-within td,
    .kelas-table tbody tr:focus-within td,
    .room-table tbody tr:focus-within td,
    .crud-table tbody tr:focus-within td {
        background: var(--package-row-focus-bg) !important;
        border-top-color: var(--package-row-focus-ring);
        border-bottom-color: var(--package-row-focus-ring);
        color: var(--package-row-focus-text);
    }

    .package-table tbody tr:hover td:first-child,
    .kelas-table tbody tr:hover td:first-child,
    .room-table tbody tr:hover td:first-child,
    .crud-table tbody tr:hover td:first-child,
    .package-table tbody tr:focus-within td:first-child,
    .kelas-table tbody tr:focus-within td:first-child,
    .room-table tbody tr:focus-within td:first-child,
    .crud-table tbody tr:focus-within td:first-child {
        box-shadow: inset 7px 0 0 var(--package-row-focus-ring);
    }

    .package-table tbody tr:hover td:last-child,
    .kelas-table tbody tr:hover td:last-child,
    .room-table tbody tr:hover td:last-child,
    .crud-table tbody tr:hover td:last-child,
    .package-table tbody tr:focus-within td:last-child,
    .kelas-table tbody tr:focus-within td:last-child,
    .room-table tbody tr:focus-within td:last-child,
    .crud-table tbody tr:focus-within td:last-child {
        box-shadow: inset -2px 0 0 var(--package-row-focus-ring);
    }

    .package-table tbody tr:hover .package-code,
    .kelas-table tbody tr:hover .kelas-code,
    .room-table tbody tr:hover .room-code,
    .crud-table tbody tr:hover .package-code,
    .package-table tbody tr:focus-within .package-code,
    .kelas-table tbody tr:focus-within .kelas-code,
    .room-table tbody tr:focus-within .room-code,
    .crud-table tbody tr:focus-within .package-code {
        background: #fff8e7;
        color: #0f513c;
        border: 1px solid var(--package-row-focus-ring);
        box-shadow: 0 4px 10px rgba(6, 46, 35, 0.18);
    }

    .package-table tbody tr:hover .kind-pill,
    .crud-table tbody tr:hover .kind-pill,
    .package-table tbody tr:focus-within .kind-pill,
    .crud-table tbody tr:focus-within .kind-pill {
        background: #fff8e7;
        color: #0f513c;
        border-color: var(--package-row-focus-ring);
    }

    .package-table tbody td,
    .kelas-table tbody td,
    .room-table tbody td,
    .crud-table tbody td {
        border-top: 1px solid rgba(16, 35, 59, 0.06);
        padding: 1rem 1.2rem;
        vertical-align: middle;
        color: var(--package-text);
    }

    .package-pagination-wrap,
    .kelas-pagination-wrap,
    .room-pagination-wrap,
    .crud-pagination-wrap {
        display: flex;
        justify-content: flex-end;
        padding: 1rem 1.4rem 1.4rem;
        border-top: 1px solid rgba(16, 35, 59, 0.08);
        background: rgba(255, 255, 255, 0.58);
    }

    .package-pagination-wrap .pagination,
    .kelas-pagination-wrap .pagination,
    .room-pagination-wrap .pagination,
    .crud-pagination-wrap .pagination {
        margin-bottom: 0;
        justify-content: flex-end;
    }

    .package-pagination-wrap .page-link,
    .kelas-pagination-wrap .page-link,
    .room-pagination-wrap .page-link,
    .crud-pagination-wrap .page-link {
        border-radius: 12px;
        margin: 0 0.2rem;
        border: 1px solid rgba(16, 35, 59, 0.12);
        color: var(--package-title);
        font-weight: 700;
        box-shadow: none;
    }

    .package-pagination-wrap .page-item.active .page-link,
    .kelas-pagination-wrap .page-item.active .page-link,
    .room-pagination-wrap .page-item.active .page-link,
    .crud-pagination-wrap .page-item.active .page-link {
        background: var(--package-button-primary);
        border-color: transparent;
        color: #fff;
    }

    .crud-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
    }

    .crud-stat-card {
        min-height: 100%;
        padding: 1.05rem 1.15rem;
        border-radius: 20px;
        background: var(--package-heading-bg);
        border: 1px solid var(--package-shell-border);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.72);
    }

    .crud-stat-label {
        display: block;
        font-size: 0.76rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        color: var(--package-title);
        margin-bottom: 0.55rem;
    }

    .crud-stat-value {
        display: block;
        font-size: 2.05rem;
        line-height: 1;
        font-weight: 700;
        color: var(--package-title);
    }

    .crud-stat-note,
    .crud-form-note {
        display: block;
        margin-top: 0.55rem;
        font-size: 0.84rem;
        color: var(--package-muted);
    }

    @media (max-width: 767.98px) {
        .package-shell,
        .kelas-shell,
        .room-shell,
        .crud-shell {
            border-radius: 12px;
        }

        .package-shell-header,
        .kelas-shell-header,
        .room-shell-header,
        .crud-shell-header {
            flex-direction: column;
            align-items: flex-start;
            padding: 1rem 1.15rem 0.8rem;
        }

        .package-shell-heading-block,
        .crud-shell-heading-block {
            width: 100%;
            padding: 0.95rem 1rem 0.9rem;
        }

        .package-shell-heading-block .package-shell-title,
        .crud-shell-heading-block .crud-shell-title {
            font-size: 1.75rem;
            line-height: 1.08;
        }

        .package-shell-body,
        .kelas-shell-body,
        .room-shell-body,
        .crud-shell-body {
            padding: 1rem 1.15rem 1.35rem;
        }

        .package-btn-primary,
        .package-btn-secondary,
        .kelas-btn-primary,
        .kelas-btn-secondary,
        .room-btn-primary,
        .room-btn-secondary,
        .crud-btn-primary,
        .crud-btn-secondary {
            width: 100%;
            justify-content: center;
            border-radius: 8px;
        }

        .package-pagination-wrap,
        .kelas-pagination-wrap,
        .room-pagination-wrap,
        .crud-pagination-wrap {
            justify-content: center;
            padding: 0.85rem;
            overflow-x: auto;
        }
    }
</style>
