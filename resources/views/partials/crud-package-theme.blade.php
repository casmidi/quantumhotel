<style>
.content-wrapper {
    background:
        radial-gradient(circle at top right, rgba(183, 148, 92, 0.12), transparent 22%),
        radial-gradient(circle at left top, rgba(17, 24, 39, 0.08), transparent 28%),
        linear-gradient(180deg, #f8f4ec 0%, #eef1f6 45%, #e7edf5 100%);
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
    color: #10233b;
}

.package-shell,
.kelas-shell,
.room-shell,
.crud-shell {
    background: linear-gradient(180deg, rgba(255, 252, 246, 0.98), rgba(255, 255, 255, 0.96));
    border: 1px solid rgba(199, 165, 106, 0.58);
    box-shadow: 0 24px 60px rgba(125, 96, 42, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.7);
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
    background: linear-gradient(180deg, rgba(232, 215, 174, 0.24), rgba(255, 251, 244, 0.72));
    border-bottom: 1px solid rgba(199, 165, 106, 0.22);
}

.package-shell-heading-block,
.crud-shell-heading-block {
    display: inline-block;
    padding: 1rem 1.35rem 0.95rem;
    border-radius: 22px;
    background: linear-gradient(180deg, rgba(233, 213, 162, 0.34), rgba(255, 248, 235, 0.96));
    border: 1px solid rgba(199, 165, 106, 0.42);
    box-shadow: 0 16px 34px rgba(182, 148, 79, 0.12), inset 0 1px 0 rgba(255, 255, 255, 0.7);
}

.package-shell-title,
.kelas-shell-title,
.room-shell-title,
.crud-shell-title {
    margin: 0;
    font-size: 1.08rem;
    font-weight: 700;
    color: #10233b;
}

.package-shell-subtitle,
.kelas-shell-subtitle,
.room-shell-subtitle,
.crud-shell-subtitle {
    margin: 0.35rem 0 0;
    font-size: 0.92rem;
    color: #6b7b90;
}

.package-shell-heading-block .package-shell-title,
.crud-shell-heading-block .crud-shell-title {
    font-family: Georgia, "Times New Roman", serif;
    font-size: 2.6rem;
    font-weight: 500;
    color: #233f6b;
    line-height: 1;
    letter-spacing: 0.01em;
}

.package-shell-heading-block .package-shell-subtitle,
.crud-shell-heading-block .crud-shell-subtitle {
    margin-top: 0.45rem;
    font-size: 1rem;
    color: #c19a58;
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
    background: rgba(199, 165, 106, 0.14);
    color: #8f6a2d;
    font-weight: 700;
    font-size: 0.8rem;
    border: 1px solid rgba(199, 165, 106, 0.2);
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
    color: #233f6b;
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
    border: 1px solid rgba(199, 165, 106, 0.34);
    box-shadow: inset 0 1px 2px rgba(16, 35, 59, 0.04);
    background: rgba(255, 255, 255, 0.95);
    color: #10233b;
    font-weight: 600;
}

.package-input:focus,
.package-select:focus,
.kelas-input:focus,
.room-input:focus,
.crud-input:focus,
.crud-select:focus {
    border-color: rgba(199, 165, 106, 0.88);
    box-shadow: 0 0 0 0.2rem rgba(199, 165, 106, 0.12);
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
    border: 1px solid rgba(199, 165, 106, 0.42);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(247, 239, 222, 0.96));
    color: #b38a51;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 16px rgba(179, 138, 81, 0.12);
}

.package-date-picker:hover {
    background: linear-gradient(180deg, rgba(255, 255, 255, 1), rgba(244, 232, 202, 0.98));
    color: #8f6a2d;
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
    background: linear-gradient(135deg, #cba246 0%, #d8b86a 55%, #b58a36 100%);
    box-shadow: 0 14px 28px rgba(201, 164, 83, 0.24);
    color: #fff;
}

.package-btn-secondary,
.kelas-btn-secondary,
.room-btn-secondary,
.crud-btn-secondary {
    border-radius: 999px;
    padding: 0.78rem 1.35rem;
    font-weight: 700;
    border: 1px solid rgba(199, 165, 106, 0.52);
    background: rgba(255, 255, 255, 0.9);
    color: #bb9857;
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
    overflow: hidden;
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
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(245, 239, 228, 0.78));
    color: #53657d;
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
    background: rgba(16, 35, 59, 0.045);
}

.package-table tbody tr:nth-child(even),
.kelas-table tbody tr:nth-child(even),
.room-table tbody tr:nth-child(even),
.crud-table tbody tr:nth-child(even) {
    background: rgba(255, 255, 255, 0.96);
}

.package-table tbody tr:hover,
.kelas-table tbody tr:hover,
.room-table tbody tr:hover,
.crud-table tbody tr:hover {
    background: rgba(179, 138, 81, 0.06);
    transform: translateY(-1px);
    box-shadow: inset 4px 0 0 #b38a51;
}

.package-table tbody td,
.kelas-table tbody td,
.room-table tbody td,
.crud-table tbody td {
    border-top: 1px solid rgba(16, 35, 59, 0.06);
    padding: 1rem 1.2rem;
    vertical-align: middle;
    color: #10233b;
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
    color: #173761;
    font-weight: 700;
    box-shadow: none;
}

.package-pagination-wrap .page-item.active .page-link,
.kelas-pagination-wrap .page-item.active .page-link,
.room-pagination-wrap .page-item.active .page-link,
.crud-pagination-wrap .page-item.active .page-link {
    background: linear-gradient(135deg, #173761 0%, #1e4b80 55%, #b38a51 150%);
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
    background: linear-gradient(180deg, rgba(233, 213, 162, 0.16), rgba(255, 255, 255, 0.78));
    border: 1px solid rgba(199, 165, 106, 0.28);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.72);
}

.crud-stat-label {
    display: block;
    font-size: 0.76rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    color: #8f6a2d;
    margin-bottom: 0.55rem;
}

.crud-stat-value {
    display: block;
    font-size: 2.05rem;
    line-height: 1;
    font-weight: 700;
    color: #173761;
}

.crud-stat-note,
.crud-form-note {
    display: block;
    margin-top: 0.55rem;
    font-size: 0.84rem;
    color: #7a8a9d;
}

@media (max-width: 767.98px) {
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
        font-size: 2rem;
    }

    .package-shell-body,
    .kelas-shell-body,
    .room-shell-body,
    .crud-shell-body {
        padding: 1rem 1.15rem 1.35rem;
    }
}
</style>

