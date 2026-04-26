@extends('layouts.app')

@section('title', '')

@section('topbar_brand')
    <div class="checkin-topbar-brand">
        <div class="checkin-topbar-title">Check In</div>
        <div class="checkin-topbar-subtitle">Enter active guest details and choose a room with a cleaner form.</div>
    </div>
@endsection

@section('topbar_tools')
    <div class="checkin-header-side">
        <div class="checkin-room-note" id="roomHelper">
            <small>Room Status</small>
            <strong>Select an available room</strong>
        </div>
        <div class="checkin-package-note" id="packageHelper">
            <small>Package Note</small>
            <strong>Nominal will be filled automatically when a package is found</strong>
        </div>
    </div>
@endsection

@section('content')

@include('partials.crud-package-theme')

<style>
    .checkin-topbar-brand {
        display: grid;
        gap: 0.22rem;
    }

    .checkin-topbar-title {
        color: #173761;
        font-size: 2.05rem;
        line-height: 0.98;
        font-weight: 900;
        letter-spacing: -0.045em;
        font-family: "Segoe UI", "Trebuchet MS", "Helvetica Neue", Arial, sans-serif;
        text-transform: none;
        text-shadow: 0 1px 0 rgba(255, 255, 255, 0.42);
    }

    .checkin-topbar-subtitle {
        color: #516783;
        font-size: 0.94rem;
        line-height: 1.4;
        font-weight: 600;
    }

    .checkin-page {
        padding: 0 0 2rem;
        color: #10233b;
    }

    .checkin-shell + .checkin-shell {
        margin-top: 1.5rem;
    }

    #checkinDirectoryShell.is-loading {
        opacity: 0.62;
        pointer-events: none;
        transition: opacity 0.2s ease;
    }

    .checkin-directory-loading {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease;
        background: rgba(244, 248, 255, 0.72);
        backdrop-filter: blur(2px);
        z-index: 3;
    }

    #checkinDirectoryShell {
        position: relative;
    }

    #checkinDirectoryShell.is-loading .checkin-directory-loading {
        opacity: 1;
    }

    .checkin-directory-loading-card {
        display: inline-flex;
        align-items: center;
        gap: 0.8rem;
        padding: 0.85rem 1rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(128, 159, 208, 0.28);
        box-shadow: 0 12px 30px rgba(16, 35, 59, 0.12);
        color: #173761;
        font-size: 0.92rem;
        font-weight: 800;
    }

    .checkin-directory-loading-spinner {
        width: 1.05rem;
        height: 1.05rem;
        border-radius: 999px;
        border: 2px solid rgba(23, 55, 97, 0.18);
        border-top-color: #2962ff;
        animation: checkinDirectorySpin 0.8s linear infinite;
    }

    @keyframes checkinDirectorySpin {
        to {
            transform: rotate(360deg);
        }
    }

    .checkin-table {
        table-layout: fixed;
    }

    .checkin-col-reg {
        width: 17%;
    }

    .checkin-col-room {
        width: 10%;
    }

    .checkin-col-guest {
        width: 24%;
    }

    .checkin-col-date {
        width: 10%;
    }

    .checkin-col-package {
        width: 15%;
    }

    .checkin-col-nominal {
        width: 10%;
    }

    .checkin-col-action {
        width: 4%;
    }

    .checkin-cell-reg,
    .checkin-cell-room,
    .checkin-cell-date,
    .checkin-cell-package,
    .checkin-cell-nominal {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .checkin-sort-link {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        color: #4c5d78;
        text-decoration: none;
        font-weight: 800;
        transition: color 0.18s ease;
    }

    .checkin-sort-link:hover {
        color: #173761;
        text-decoration: none;
    }

    .checkin-sort-link.is-active {
        color: #173761;
    }

    .checkin-sort-link i {
        color: #8da0bb;
        font-size: 0.8rem;
    }

    .checkin-sort-link.is-active i {
        color: #2962ff;
    }

    .checkin-header-side {
        display: flex;
        align-items: stretch;
        justify-content: flex-end;
        gap: 0.75rem;
        flex-wrap: wrap;
        flex: 1 1 420px;
    }

    .checkin-room-note small,
    .checkin-package-note small {
        display: block;
        margin-bottom: 0.18rem;
        font-size: 0.68rem;
        font-weight: 900;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #8f6a2d;
    }

    .checkin-room-note strong,
    .checkin-package-note strong {
        display: block;
        font-size: 1rem;
        color: #173761;
        line-height: 1.1;
    }

    .checkin-room-note,
    .checkin-package-note {
        min-width: 220px;
        padding: 0.82rem 0.95rem;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.72);
        border: 1px solid rgba(138, 170, 219, 0.28);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65);
    }

    .registration-details-grid {
        display: grid;
        grid-template-columns: 1.05fr 1.15fr 1fr 0.82fr 1.05fr 1.2fr;
        gap: 0.95rem;
    }

    .checkin-info-item {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
        min-width: 0;
    }

    .registration-number-item .package-input {
        background: rgba(16, 35, 59, 0.05);
    }

    .checkin-info-label {
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #5f6f84;
    }

    .checkin-info-input {
        font-weight: 700;
        color: #173761;
    }

    .checkin-section {
        margin-bottom: 1.5rem;
        padding: 1.35rem;
        background: #fff;
        border: 1px solid #dbe8ff;
        border-radius: 20px;
        box-shadow: 0 12px 30px rgba(16, 35, 59, 0.06);
        overflow: hidden;
    }

    .checkin-section-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: -1.35rem -1.35rem 1rem;
        padding: 0.9rem 1.1rem;
        background: linear-gradient(180deg, #f2f7ff 0%, #e8f1ff 100%);
        border-bottom: 1px solid rgba(137, 167, 214, 0.25);
    }

    .checkin-section-header h3 {
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.7rem;
        font-size: 1.18rem;
        font-weight: 800;
        color: #172b4d;
    }

    .checkin-section-header i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 999px;
        background: rgba(107, 120, 240, 0.12);
        color: #6b78f0;
        font-size: 1rem;
    }

    .checkin-section-body {
        display: block;
        width: 100%;
    }

    .payment-checkbox-field {
        justify-content: flex-end;
    }

    .payment-checkbox-control {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        min-height: calc(3rem + 2px);
        color: #173761;
        font-size: 0.95rem;
        font-weight: 700;
    }

    .payment-checkbox-control input {
        width: 1rem;
        height: 1rem;
    }

    .guest-info-grid {
        display: grid;
        gap: 1rem;
    }

    .guest-info-row {
        display: grid;
        gap: 1rem;
    }

    .guest-info-row-two {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .guest-info-row-five {
        grid-template-columns: 1.1fr 1fr 1fr 1.05fr 1fr;
    }

    .guest-info-row-three {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .address-info-grid {
        display: grid;
        gap: 1rem;
    }

    .address-info-row {
        display: grid;
        gap: 1rem;
    }

    .address-info-row-five {
        grid-template-columns: 0.95fr 1fr 0.95fr 1fr 0.9fr;
    }

    .room-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .room-info-column {
        display: grid;
        gap: 1rem;
    }

    .ktp-flow-board {
        margin: 1.35rem 1.35rem 0;
        padding: 1.15rem;
        border: 1px solid #d8e6ff;
        border-radius: 24px;
        background: linear-gradient(180deg, #f9fbff 0%, #eef5ff 100%);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }

    .ktp-flow-title {
        margin-bottom: 1rem;
        font-size: 1.35rem;
        font-weight: 900;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #1e3764;
    }

    .ktp-flow-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr)) 1.1fr;
        gap: 0.9rem;
    }

    .ktp-flow-step,
    .ktp-flow-benefits {
        padding: 1rem;
        border-radius: 20px;
        border: 1px solid rgba(127, 156, 209, 0.28);
        background: rgba(255, 255, 255, 0.92);
    }

    .ktp-flow-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.85rem;
        height: 1.85rem;
        margin-bottom: 0.75rem;
        border-radius: 999px;
        background: #2962ff;
        color: #fff;
        font-size: 0.92rem;
        font-weight: 800;
    }

    .ktp-flow-step strong,
    .ktp-flow-benefits small {
        display: block;
        margin-bottom: 0.45rem;
        color: #173761;
        font-size: 0.96rem;
        font-weight: 800;
    }

    .ktp-flow-step p,
    .ktp-flow-benefits li {
        margin: 0;
        color: #50627e;
        font-size: 0.88rem;
        line-height: 1.55;
    }

    .ktp-flow-benefits ul {
        margin: 0;
        padding-left: 1rem;
        display: grid;
        gap: 0.4rem;
    }

    .checkin-overview-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.65fr) minmax(300px, 0.95fr);
        gap: 1rem;
        align-items: start;
    }

    .checkin-content-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.65fr) minmax(320px, 0.95fr);
        gap: 1rem;
        align-items: stretch;
    }

    .checkin-main-stack,
    .checkin-sidebar-stack {
        display: grid;
        gap: 1rem;
        align-content: start;
        min-height: 100%;
    }

    .checkin-main-stack .checkin-section {
        margin-bottom: 0;
    }

    .guest-primary-panel,
    .guest-side-stack {
        display: grid;
        gap: 1rem;
    }

    .checkin-sidebar-stack {
        grid-template-rows: auto auto auto minmax(0, 1fr);
    }

    .guest-main-row-five,
    .guest-main-row-four,
    .guest-main-row-three {
        display: grid;
        gap: 1rem;
    }

    .guest-main-row-five {
        grid-template-columns: 1.25fr 0.82fr 1fr 0.95fr 0.92fr;
    }

    .guest-main-row-four {
        grid-template-columns: 1fr 1fr 1fr 0.82fr;
    }

    .guest-main-row-three {
        grid-template-columns: 1.05fr 1fr 1fr;
    }

    .guest-main-address .package-input {
        min-height: calc(3rem + 2px);
    }

    .group-info-card,
    .guest-brief-grid,
    .scan-guide-card,
    .ktp-scan-card {
        padding: 1rem;
        border-radius: 18px;
        border: 1px solid rgba(137, 167, 214, 0.28);
        background: linear-gradient(180deg, #fbfdff 0%, #f0f5ff 100%);
    }

    .group-info-card,
    .scan-guide-card,
    .ktp-scan-card {
        display: grid;
        gap: 0.85rem;
    }

    .ktp-scan-card {
        height: 100%;
        min-height: 100%;
        grid-template-rows: auto auto minmax(170px, 1fr) auto auto;
        align-content: stretch;
    }

    .group-info-card small,
    .scan-guide-card small,
    .ktp-scan-card-head small {
        display: block;
        margin-bottom: 0.3rem;
        color: #7a8aa0;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .group-info-card strong,
    .scan-guide-card strong,
    .ktp-scan-card-head strong {
        color: #173761;
        font-size: 1.06rem;
        font-weight: 800;
    }

    .group-info-form {
        display: grid;
        gap: 0.9rem;
    }

    .group-info-remark {
        min-height: 96px;
        resize: vertical;
        padding-top: 0.85rem;
        padding-bottom: 0.85rem;
    }

    .guest-brief-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 0.9fr);
        gap: 0.85rem;
        background: transparent;
        border: none;
        padding: 0;
    }

    .guest-brief-card,
    .guest-note-card {
        padding: 0.95rem 1rem;
        border-radius: 16px;
        border: 1px solid rgba(137, 167, 214, 0.22);
        background: #fff;
    }

    .guest-brief-card {
        background: linear-gradient(180deg, #eff5ff 0%, #e9f1ff 100%);
    }

    .guest-note-card {
        background: linear-gradient(180deg, #fff8ec 0%, #fff3dc 100%);
        border-color: rgba(212, 172, 78, 0.24);
    }

    .guest-brief-card small,
    .guest-note-card small {
        display: block;
        margin-bottom: 0.25rem;
        color: #6f7f96;
        font-size: 0.73rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .guest-brief-card strong,
    .guest-note-card strong {
        display: block;
        color: #173761;
        font-size: 0.98rem;
        font-weight: 800;
    }

    .guest-brief-card span,
    .guest-note-card span {
        display: block;
        margin-top: 0.28rem;
        color: #4d6180;
        font-size: 0.85rem;
        line-height: 1.5;
    }

    .scan-guide-card ol {
        margin: 0;
        padding-left: 1rem;
        display: grid;
        gap: 0.42rem;
        color: #50627e;
        font-size: 0.88rem;
        line-height: 1.55;
    }

    .ktp-scan-card-head {
        display: grid;
        gap: 0.4rem;
    }

    .ktp-scan-status {
        margin: 0;
        padding: 0.75rem 0.85rem;
        border-radius: 14px;
        background: rgba(41, 98, 255, 0.08);
        color: #2750a8;
        font-size: 0.9rem;
        font-weight: 700;
    }

    .ktp-scan-preview {
        min-height: 170px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.85rem;
        border-radius: 18px;
        border: 1px dashed rgba(41, 98, 255, 0.35);
        background: rgba(255, 255, 255, 0.92);
        overflow: hidden;
    }

    .ktp-scan-preview img {
        width: 100%;
        max-height: 220px;
        object-fit: contain;
        border-radius: 12px;
    }

    .ktp-scan-preview-empty {
        color: #7f8da4;
        font-size: 0.9rem;
        text-align: center;
    }

    .ktp-scan-result {
        padding: 0.9rem;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.94);
        border: 1px solid rgba(137, 167, 214, 0.24);
        display: grid;
        align-content: start;
    }

    .ktp-scan-result-title {
        display: block;
        margin-bottom: 0.55rem;
        color: #173761;
        font-size: 0.9rem;
        font-weight: 800;
    }

    .ktp-scan-result ul {
        margin: 0;
        padding-left: 1rem;
        display: grid;
        gap: 0.38rem;
        color: #5c6f89;
        font-size: 0.88rem;
    }

    .ktp-scan-card-footer {
        display: flex;
        justify-content: flex-end;
        margin-top: auto;
    }

    .room-follow-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 84px;
        padding: 0.42rem 0.7rem;
        border-radius: 999px;
        background: rgba(35, 108, 255, 0.1);
        color: #224ea4;
        font-size: 0.8rem;
        font-weight: 800;
    }

    .room-follow-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        color: #173761;
        font-size: 0.86rem;
        font-weight: 700;
    }

    .room-guest-summary {
        display: grid;
        gap: 0.3rem;
    }

    .room-guest-summary-note {
        color: #7c8ba3;
        font-size: 0.76rem;
        line-height: 1.35;
    }

    .room-action-stack {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
    }

    .room-guest-detail-row td {
        padding: 0;
        background: #f6f9ff;
        border-top: none;
    }

    .room-guest-detail-row.is-collapsed {
        display: none;
    }

    .room-guest-detail-card {
        padding: 1rem;
        border-top: 1px dashed rgba(137, 167, 214, 0.34);
    }

    .room-guest-detail-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.95rem;
    }

    .room-guest-detail-head strong {
        display: block;
        color: #173761;
        font-size: 1rem;
        font-weight: 800;
    }

    .room-guest-detail-head small {
        color: #70819a;
        font-size: 0.82rem;
    }

    .room-guest-detail-actions {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .room-guest-scan-status {
        color: #56718e;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .room-guest-detail-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.9rem;
    }

    .room-guest-address-field {
        grid-column: span 2;
    }

    .address-section .checkin-section-header i {
        background: rgba(107, 120, 240, 0.12);
        color: #6b78f0;
    }

    .guest-info-field {
        display: flex;
        flex-direction: column;
        gap: 0.42rem;
        min-width: 0;
    }

    .guest-info-field label {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: #243b63;
    }

    .address-info-field {
        display: flex;
        flex-direction: column;
        gap: 0.42rem;
        min-width: 0;
    }

    .address-info-field label {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: #243b63;
    }

    .room-info-field {
        display: flex;
        flex-direction: column;
        gap: 0.42rem;
        min-width: 0;
    }

    .room-info-field label {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: #243b63;
    }

    .detail-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .additional-details-grid {
        align-items: start;
    }

    .additional-details-grid .detail-info-column {
        align-content: start;
    }

    .detail-info-column {
        display: grid;
        gap: 1rem;
    }

    .detail-info-row-two {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .detail-info-field {
        display: flex;
        flex-direction: column;
        gap: 0.42rem;
        min-width: 0;
    }

    .detail-info-field label {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: #243b63;
    }

    .room-allocation-note {
        font-size: 0.84rem;
        color: #6f7f96;
        font-weight: 600;
    }

    .room-allocation-wrap {
        border: 1px solid #e3eaf6;
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
    }

    .room-allocation-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .room-allocation-table thead th {
        padding: 0.85rem 0.95rem;
        font-size: 0.83rem;
        font-weight: 800;
        color: #4c5d78;
        text-align: left;
        background: #f8fbff;
        border-bottom: 1px solid #e3eaf6;
    }

    .room-allocation-table tbody td {
        padding: 0.7rem 0.95rem;
        border-bottom: 1px solid #edf2f9;
        vertical-align: middle;
    }

    .room-allocation-table tbody tr:last-child td {
        border-bottom: none;
    }

    .room-allocation-table .package-input,
    .room-allocation-table .package-select {
        height: calc(2.55rem + 2px);
        border-radius: 10px;
        border-color: #e2e8f0;
        background: #fff;
    }

    .room-column-room {
        width: 11%;
    }

    .room-column-guest {
        width: 23%;
    }

    .room-column-pax {
        width: 7%;
    }

    .room-column-package {
        width: 19%;
    }

    .room-column-nominal {
        width: 15%;
    }

    .room-column-position {
        width: 10%;
    }

    .room-column-same {
        width: 8%;
    }

    .room-column-action {
        width: 6%;
    }

    .room-allocation-table .room-code-cell .package-select,
    .room-allocation-table .room-code-cell .package-input {
        max-width: 7rem;
    }

    .room-allocation-table .room-package-cell .package-select,
    .room-allocation-table .room-package-cell .package-input {
        width: 100%;
        min-width: 11rem;
    }

    .room-allocation-table .room-pax-cell {
        text-align: center;
    }

    .room-allocation-table .room-pax-cell .package-input {
        width: 4.25rem;
        min-width: 4.25rem;
        margin: 0 auto;
        padding-left: 0.45rem;
        padding-right: 0.45rem;
        text-align: center;
    }

    .room-allocation-table .room-nominal-cell .package-input {
        width: 100%;
        min-width: 8rem;
    }

    .room-display-text {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        min-height: calc(2.55rem + 2px);
        color: #223a5e;
        font-weight: 700;
    }

    .room-main-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.16rem 0.45rem;
        border-radius: 999px;
        background: rgba(86, 111, 255, 0.1);
        color: #4f63dd;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .room-muted-display {
        color: #7f8ea5;
        font-weight: 600;
    }

    .room-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.4rem;
        height: 2.4rem;
        border: 1px solid rgba(220, 38, 38, 0.14);
        border-radius: 999px;
        background: rgba(239, 68, 68, 0.06);
        color: #ef4444;
        transition: background 0.18s ease, color 0.18s ease;
    }

    .room-action-btn:hover {
        background: #ef4444;
        color: #fff;
    }

    .room-summary {
        display: flex;
        align-items: center;
        gap: 2rem;
        flex-wrap: wrap;
        padding: 0.85rem 1rem;
        border-top: 1px solid #e3eaf6;
        background: #fafcff;
        font-size: 0.85rem;
        color: #4c5d78;
        font-weight: 700;
    }

    .guest-info-field .package-input,
    .guest-info-field .package-select {
        height: calc(3rem + 2px);
        border-radius: 14px;
        border-color: #e2e8f0;
        background: #fff;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.92);
    }

    .address-info-field .package-input,
    .address-info-field .package-select {
        height: calc(3rem + 2px);
        border-radius: 14px;
        border-color: #e2e8f0;
        background: #fff;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.92);
    }

    .room-info-field .package-input,
    .room-info-field .package-select {
        height: calc(3rem + 2px);
        border-radius: 14px;
        border-color: #e2e8f0;
        background: #fff;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.92);
    }

    .detail-info-field .package-input,
    .detail-info-field .package-select {
        height: calc(3rem + 2px);
        border-radius: 14px;
        border-color: #e2e8f0;
        background: #fff;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.92);
    }

    .guest-info-field .package-input::placeholder {
        color: #9ca7b8;
        font-weight: 600;
    }

    .address-info-field .package-input::placeholder {
        color: #9ca7b8;
        font-weight: 600;
    }

    .room-info-field .package-input::placeholder {
        color: #9ca7b8;
        font-weight: 600;
    }

    .detail-info-field .package-input::placeholder {
        color: #9ca7b8;
        font-weight: 600;
    }

    .guest-info-field .package-input:focus,
    .guest-info-field .package-select:focus {
        border-color: #7c89f8;
        box-shadow: 0 0 0 0.2rem rgba(124, 137, 248, 0.08);
    }

    .address-info-field .package-input:focus,
    .address-info-field .package-select:focus {
        border-color: #7c89f8;
        box-shadow: 0 0 0 0.2rem rgba(124, 137, 248, 0.08);
    }

    .room-info-field .package-input:focus,
    .room-info-field .package-select:focus {
        border-color: #7c89f8;
        box-shadow: 0 0 0 0.2rem rgba(124, 137, 248, 0.08);
    }

    .detail-info-field .package-input:focus,
    .detail-info-field .package-select:focus {
        border-color: #7c89f8;
        box-shadow: 0 0 0 0.2rem rgba(124, 137, 248, 0.08);
    }

    .guest-contact-input {
        position: relative;
    }

    .guest-contact-input .package-input {
        padding-left: 2.8rem;
    }

    .guest-contact-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #7b8798;
        font-size: 1.15rem;
        pointer-events: none;
    }

    .checkin-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.05rem 1.2rem;
    }

    .checkin-column {
        display: grid;
        gap: 0.75rem;
    }

    .checkin-row {
        display: grid;
        grid-template-columns: 132px minmax(0, 1fr) auto;
        align-items: center;
        gap: 0.7rem;
    }

    .checkin-row-label {
        font-size: 0.85rem;
        font-weight: 900;
        color: #233f6b;
        letter-spacing: 0.01em;
    }

    .checkin-row .package-input,
    .checkin-row .package-select,
    .checkin-row textarea.package-input {
        height: calc(2.55rem + 2px);
        font-size: 0.96rem;
        font-weight: 700;
        min-width: 0;
    }

    .checkin-row textarea.package-input {
        min-height: 88px;
        resize: vertical;
        padding-top: 0.7rem;
    }

    .checkin-row-note {
        min-width: 74px;
        font-size: 0.8rem;
        font-weight: 800;
        color: #8f6a2d;
        text-align: left;
    }

    .checkin-row-note.is-stack {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        color: #173761;
    }

    .checkin-row-note.is-stack input {
        width: 1rem;
        height: 1rem;
    }

    .checkin-row-note.is-empty {
        min-width: 0;
    }

    .checkin-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 1.25rem;
    }

    .checkin-actions-main,
    .checkin-actions-side {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        flex-wrap: wrap;
    }

    .checkin-directory-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    .checkin-directory-head > div {
        min-width: 0;
    }

    .checkin-search-form {
        display: flex;
        align-items: flex-end;
        gap: 0.8rem;
        flex-wrap: wrap;
        width: 100%;
    }

    .checkin-search-group {
        flex: 1 1 320px;
    }

    .checkin-search-actions {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        flex-wrap: wrap;
    }

    .checkin-table-meta {
        font-size: 0.82rem;
        color: #6b7b90;
        line-height: 1.55;
    }

    .checkin-directory-tools {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }

    .checkin-directory-result {
        font-size: 0.84rem;
        font-weight: 700;
        color: var(--package-muted);
    }

    .checkin-per-page {
        display: inline-flex;
        align-items: center;
        gap: 0.7rem;
        flex-wrap: wrap;
    }

    .checkin-per-page .package-label {
        margin-bottom: 0;
        white-space: nowrap;
    }

    .checkin-per-page .package-select {
        min-width: 110px;
    }

    .checkin-table thead th {
        background: linear-gradient(180deg, rgba(240, 246, 255, 0.98), rgba(225, 235, 248, 0.95));
        border-bottom: 2px solid rgba(30, 75, 128, 0.2);
        color: #10233b;
        font-size: 0.77rem;
        font-weight: 900;
        letter-spacing: 0.11em;
        box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.9);
    }

    .checkin-table .package-code {
        min-width: 132px;
    }

    .checkin-table .room-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 78px;
        padding: 0.36rem 0.72rem;
        border-radius: 999px;
        background: rgba(199, 165, 106, 0.13);
        color: #8f6a2d;
        font-weight: 800;
    }

    .checkin-table .guest-block strong {
        display: block;
        color: #10233b;
    }

    .checkin-table .guest-block span {
        display: block;
        margin-top: 0.2rem;
        color: #6b7b90;
        font-size: 0.8rem;
    }

    .checkin-table .nominal-cell {
        font-weight: 800;
        color: #173761;
    }

    .checkin-table tbody tr.is-active {
        background: rgba(30, 75, 128, 0.08) !important;
        box-shadow: inset 4px 0 0 #1e4b80;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        padding: 0.25rem 0.65rem;
        border-radius: 999px;
        white-space: nowrap;
    }

    .status-stay {
        background: rgba(30, 75, 128, 0.12);
        color: #173761;
    }

    .status-checkout {
        background: rgba(33, 150, 83, 0.12);
        color: #1c6b40;
    }

    .package-table .checkin-delete-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: rgba(178, 34, 34, 0.08);
        color: #aa2f2f;
        border: 1px solid rgba(178, 34, 34, 0.12);
        text-decoration: none;
    }

    .package-table .checkin-delete-link:hover {
        background: #aa2f2f;
        color: #fff;
        text-decoration: none;
    }

    .checkin-page {
        color: var(--package-text);
    }

    .checkin-topbar-title,
    .checkin-room-note strong,
    .checkin-package-note strong,
    .checkin-section-header h3,
    .group-info-card strong,
    .scan-guide-card strong,
    .ktp-scan-card-head strong,
    .ktp-scan-result-title,
    .room-guest-detail-head strong,
    .guest-brief-card strong,
    .guest-note-card strong,
    .checkin-table .guest-block strong,
    .checkin-table .nominal-cell,
    .status-stay,
    .room-display-text,
    .room-follow-toggle,
    .payment-checkbox-control {
        color: var(--package-title);
    }

    .checkin-topbar-subtitle,
    .checkin-info-label,
    .group-info-card small,
    .scan-guide-card small,
    .ktp-scan-card-head small,
    .guest-brief-card small,
    .guest-note-card small,
    .guest-brief-card span,
    .guest-note-card span,
    .scan-guide-card ol,
    .ktp-scan-result ul,
    .room-guest-summary-note,
    .room-guest-scan-status,
    .room-summary,
    .room-allocation-note,
    .checkin-table-meta,
    .checkin-table .guest-block span,
    .checkin-table .room-pill,
    .room-muted-display,
    .checkin-row-note,
    .guest-contact-icon,
    .checkin-row-label,
    .guest-info-field label,
    .address-info-field label,
    .room-info-field label,
    .detail-info-field label {
        color: var(--package-muted);
    }

    .checkin-room-note small,
    .checkin-package-note small {
        color: var(--package-label);
    }

    .checkin-room-note,
    .checkin-package-note {
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 74px;
        background: var(--package-shell-bg);
        border: 1px solid var(--package-heading-border);
        box-shadow: 0 10px 24px rgba(16, 35, 59, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .checkin-section,
    .group-info-card,
    .scan-guide-card,
    .ktp-scan-card,
    .guest-brief-card,
    .guest-note-card,
    .room-allocation-wrap,
    .ktp-scan-result {
        background: var(--package-shell-bg);
        border-color: var(--package-shell-border);
        box-shadow: 0 14px 30px rgba(16, 35, 59, 0.06), inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .checkin-section-header {
        background: var(--package-header-bg);
        border-bottom-color: var(--package-shell-border);
    }

    .checkin-section-header i,
    .address-section .checkin-section-header i {
        background: var(--package-badge-bg);
        color: var(--package-badge-text);
    }

    .group-info-card,
    .scan-guide-card,
    .ktp-scan-card,
    .guest-brief-card,
    .guest-note-card {
        height: 100%;
    }

    .guest-brief-card,
    .guest-note-card {
        border-radius: 18px;
    }

    .guest-note-card {
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.96) 0%, rgba(245, 248, 243, 0.98) 100%);
    }

    .ktp-scan-status {
        background: var(--package-badge-bg);
        color: var(--package-badge-text);
    }

    .ktp-scan-preview {
        border-color: var(--package-input-border);
        background: var(--package-input-bg);
    }

    .room-guest-detail-row td,
    .room-summary {
        background: var(--package-table-even);
    }

    .room-guest-detail-card {
        border-top-color: var(--package-shell-border);
    }

    .room-follow-badge,
    .room-main-badge,
    .checkin-table .room-pill {
        background: var(--package-badge-bg);
        color: var(--package-badge-text);
    }

    .room-allocation-wrap {
        border-color: var(--package-shell-border);
    }

    .room-allocation-table thead th,
    .checkin-table thead th {
        background: var(--package-table-head-bg);
        border-bottom-color: var(--package-shell-border);
        color: var(--package-text);
        box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.72);
    }

    .room-allocation-table tbody td,
    .checkin-table tbody td {
        border-bottom-color: rgba(16, 35, 59, 0.08);
    }

    .room-allocation-table tbody tr:nth-child(odd),
    .checkin-table tbody tr:nth-child(odd) {
        background: var(--package-table-odd);
    }

    .room-allocation-table tbody tr:nth-child(even),
    .checkin-table tbody tr:nth-child(even) {
        background: var(--package-table-even);
    }

    .room-allocation-table tbody tr:hover,
    .checkin-table tbody tr:hover {
        background: var(--package-table-hover);
    }

    .checkin-table tbody tr.is-active {
        background: var(--package-table-hover) !important;
        box-shadow: inset 4px 0 0 var(--package-table-hover-accent);
    }

    .status-stay {
        background: var(--package-badge-bg);
    }

    .status-checkout {
        background: rgba(56, 128, 86, 0.12);
        color: #295e40;
    }

    .guest-info-field .package-input,
    .guest-info-field .package-select,
    .address-info-field .package-input,
    .address-info-field .package-select,
    .room-info-field .package-input,
    .room-info-field .package-select,
    .detail-info-field .package-input,
    .detail-info-field .package-select,
    .room-allocation-table .package-input,
    .room-allocation-table .package-select {
        border-color: var(--package-input-border);
        background: var(--package-input-bg);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.82);
    }

    .guest-info-field .package-input:focus,
    .guest-info-field .package-select:focus,
    .address-info-field .package-input:focus,
    .address-info-field .package-select:focus,
    .room-info-field .package-input:focus,
    .room-info-field .package-select:focus,
    .detail-info-field .package-input:focus,
    .detail-info-field .package-select:focus {
        border-color: var(--package-input-focus);
        box-shadow: var(--package-input-focus-shadow);
    }

    @media (max-width: 1199.98px) {
        .checkin-top-info,
        .checkin-form-grid,
        .guest-info-row-five,
        .guest-info-row-three,
        .guest-main-row-five,
        .guest-main-row-four,
        .guest-main-row-three,
        .address-info-row-five,
        .room-info-grid,
        .detail-info-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .room-allocation-table {
            min-width: 820px;
        }
    }

    @media (max-width: 767.98px) {
        .checkin-page {
            padding-bottom: 1rem;
        }

        .checkin-header-side,
        .checkin-search-form,
        .checkin-directory-tools,
        .checkin-search-actions,
        .checkin-actions,
        .checkin-actions-main,
        .checkin-actions-side {
            align-items: stretch;
            flex-direction: column;
            width: 100%;
        }

        .checkin-header-side {
            justify-content: stretch;
        }

        .checkin-room-note,
        .checkin-package-note,
        .checkin-search-group,
        .checkin-search-actions .btn,
        .checkin-actions .btn {
            width: 100%;
        }

        .registration-details-grid,
        .checkin-form-grid,
        .guest-info-row-two,
        .guest-info-row-three,
        .guest-info-row-five,
        .guest-main-row-five,
        .guest-main-row-four,
        .guest-main-row-three,
        .address-info-row-five,
        .room-info-grid,
        .detail-info-grid,
        .guest-brief-grid {
            grid-template-columns: 1fr;
        }

        .detail-info-row-two {
            grid-template-columns: 1fr;
        }

        .room-allocation-wrap {
            overflow-x: auto;
        }

        .checkin-section {
            padding: 1rem;
            border-radius: 16px;
        }

        .checkin-section-header {
            margin: -1rem -1rem 0.85rem;
            padding: 0.8rem 0.95rem;
        }

        .checkin-row {
            grid-template-columns: 1fr;
            gap: 0.4rem;
            padding: 0.2rem 0;
        }

        .checkin-row-note.is-empty {
            display: none;
        }

        .checkin-row .package-input,
        .checkin-row .package-select,
        .checkin-row textarea.package-input,
        .package-date-picker {
            height: calc(2.75rem + 2px);
        }
    }

    @media (max-width: 420px) {
        .checkin-section {
            padding: 0.85rem;
        }

        .checkin-section-header h3 {
            font-size: 1.02rem;
        }

        .checkin-section-header {
            margin: -0.85rem -0.85rem 0.8rem;
            padding: 0.72rem 0.85rem;
        }

        .guest-info-field label,
        .checkin-row-label {
            font-size: 0.82rem;
        }
    }

    @media (max-width: 1280px) {
        .ktp-flow-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .checkin-overview-grid {
            grid-template-columns: 1fr;
        }

        .checkin-content-grid {
            grid-template-columns: 1fr;
            align-items: start;
        }

        .checkin-sidebar-stack {
            grid-template-rows: none;
        }

        .ktp-scan-card {
            height: auto;
            min-height: 0;
            grid-template-rows: none;
        }

        .room-guest-detail-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 860px) {
        .ktp-flow-board {
            margin: 1rem 1rem 0;
        }

        .ktp-flow-grid,
        .room-guest-detail-grid {
            grid-template-columns: 1fr;
        }

        .room-guest-address-field {
            grid-column: span 1;
        }

        .room-guest-detail-head {
            flex-direction: column;
            align-items: flex-start;
        }

        .room-guest-detail-actions {
            justify-content: flex-start;
        }
    }
</style>

<div class="container-fluid checkin-page">
    @if (session('success'))
        <div class="alert package-alert mb-4" id="successAlert">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert package-error mb-4">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert package-error mb-4">{{ $errors->first() }}</div>
    @endif

    <section class="package-shell checkin-shell">
        <div class="package-shell-body">
            <form method="POST" action="/checkin" id="checkinForm" autocomplete="off">
                @csrf
                <input type="hidden" id="CurrentDetailKey" value="">

                <div class="checkin-section registration-details-section">
                    <div class="checkin-section-header">
                        <h3><i class="fas fa-id-card"></i> Registration Details</h3>
                    </div>
                    <div class="registration-details-grid">
                        <div class="checkin-info-item registration-number-item">
                            <span class="checkin-info-label">Reg. Number</span>
                            <input type="text" name="GeneratedRegNo" id="GeneratedRegNo"
                                class="form-control package-input checkin-info-input"
                                value="{{ old('GeneratedRegNo', $nextRegNo) }}" data-flow data-vb="xRegNo" readonly>
                        </div>
                        <div class="checkin-info-item">
                            <span class="checkin-info-label">Type of Check In</span>
                            <select name="TypeOfCheckIn" id="TypeOfCheckIn" class="form-control package-select"
                                data-flow data-vb="xTipe" required>
                                @foreach ($typeOptions as $option)
                                    <option value="{{ $option }}"
                                        {{ old('TypeOfCheckIn', $defaultTypeOfCheckIn) === $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="checkin-info-item">
                            <span class="checkin-info-label">Check In <span class="text-danger">*</span></span>
                            <div class="package-date-group" data-date-field>
                                <input type="hidden" name="CheckInDate" id="CheckInDate" value="{{ $checkInIso }}">
                                <input type="text" id="CheckInDateDisplay" class="form-control package-input"
                                    value="{{ \Carbon\Carbon::parse($checkInIso)->format('d-m-Y') }}"
                                    placeholder="dd-MM-yyyy" inputmode="numeric" data-flow data-vb="xTglIn" required>
                                <button type="button" class="package-date-picker" data-date-button
                                    aria-label="Open system date picker"><i class="fa-regular fa-calendar"></i></button>
                                <input type="date" class="package-date-native" data-date-native
                                    value="{{ $checkInIso }}" tabindex="-1" aria-hidden="true">
                            </div>
                        </div>
                        <div class="checkin-info-item">
                            <span class="checkin-info-label">Time <span class="text-danger">*</span></span>
                            <input type="text" name="CheckInTime" id="CheckInTime" class="form-control package-input"
                                value="{{ old('CheckInTime', now()->format('H:i:s')) }}" placeholder="HH:mm:ss"
                                inputmode="numeric" maxlength="8" pattern="^([01]\d|2[0-3]):[0-5]\d:[0-5]\d$"
                                data-flow data-vb="xJamIn" required>
                        </div>
                        <div class="checkin-info-item">
                            <span class="checkin-info-label">Reservation Number</span>
                            <select name="ReservationNumber" id="ReservationNumber" class="form-control package-select"
                                data-flow data-vb="xResNo">
                                <option value="">Select reservation number</option>
                                @foreach ($reservationNumberOptions as $option)
                                    <option value="{{ $option['resno'] }}"
                                        data-address="{{ $option['address'] }}"
                                        data-phone="{{ $option['phone'] }}"
                                        data-remarks="{{ $option['remarks'] }}"
                                        data-original-guest="{{ $option['original_guest'] }}"
                                        data-room-code="{{ $option['room_code'] }}"
                                        data-room-class="{{ $option['room_class'] }}"
                                        data-accept-by="{{ $option['accept_by'] }}"
                                        data-status="{{ $option['status'] }}"
                                        data-check-in-date="{{ $option['check_in_date'] }}"
                                        data-nationality="{{ $option['nationality'] }}"
                                        {{ old('ReservationNumber') === $option['resno'] ? 'selected' : '' }}>
                                        {{ $option['resno'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="checkin-info-item">
                            <span class="checkin-info-label">Estimation Checkout <span class="text-danger">*</span></span>
                            <div class="package-date-group" data-date-field>
                                <input type="hidden" name="EstimationOut" id="EstimationOut" value="{{ $checkOutIso }}">
                                <input type="text" id="EstimationOutDisplay" class="form-control package-input"
                                    value="{{ \Carbon\Carbon::parse($checkOutIso)->format('d-m-Y') }}"
                                    placeholder="dd-MM-yyyy" inputmode="numeric" data-flow data-vb="xTglKeluar" required>
                                <button type="button" class="package-date-picker" data-date-button
                                    aria-label="Open system date picker"><i class="fa-regular fa-calendar"></i></button>
                                <input type="date" class="package-date-native" data-date-native
                                    value="{{ $checkOutIso }}" tabindex="-1" aria-hidden="true">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="checkin-content-grid">
                    <div class="checkin-main-stack">
                        <div class="checkin-section guest-info-section">
                            <div class="checkin-section-header">
                                <h3><i class="fas fa-user-circle"></i> Leader / Primary Guest</h3>
                            </div>
                            <div class="guest-primary-panel">
                            <div class="guest-main-row-five">
                                <div class="guest-info-field">
                                    <label for="GuestName">Full Name<span class="text-danger">*</span></label>
                                    <input type="text" name="GuestName" id="GuestName" class="form-control package-input"
                                        value="{{ old('GuestName') }}" placeholder="Budi Santoso" data-flow data-vb="xGuest" required>
                                </div>
                                <div class="guest-info-field">
                                    <label for="TypeOfId">ID Type</label>
                                    <select name="TypeOfId" id="TypeOfId" class="form-control package-select" data-flow data-vb="xTypeId">
                                        @foreach ($idTypeOptions as $option)
                                            <option value="{{ $option }}"
                                                {{ old('TypeOfId', $defaultIdType) === $option ? 'selected' : '' }}>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="guest-info-field">
                                    <label for="IdNumber">ID Number</label>
                                    <input type="text" name="IdNumber" id="IdNumber" class="form-control package-input"
                                        value="{{ old('IdNumber') }}" placeholder="3171234567890001" data-flow data-vb="xKTP">
                                </div>
                                <div class="guest-info-field">
                                    <label for="BirthDateDisplay">Date of Birth</label>
                                    <div class="package-date-group" data-date-field>
                                        <input type="hidden" name="BirthDate" id="BirthDate" value="{{ $birthIso }}">
                                        <input type="text" id="BirthDateDisplay" class="form-control package-input"
                                            value="{{ $birthIso ? \Carbon\Carbon::parse($birthIso)->format('d-m-Y') : '' }}"
                                            placeholder="dd-MM-yyyy" inputmode="numeric" data-flow data-vb="xTglLahir">
                                        <button type="button" class="package-date-picker" data-date-button
                                            aria-label="Open system date picker"><i class="fa-regular fa-calendar"></i></button>
                                        <input type="date" class="package-date-native" data-date-native
                                            value="{{ $birthIso }}" tabindex="-1" aria-hidden="true">
                                    </div>
                                </div>
                                <div class="guest-info-field">
                                    <label for="Nationality">Nationality<span class="text-danger">*</span></label>
                                    <select name="Nationality" id="Nationality" class="form-control package-select"
                                        data-flow data-vb="xKodeNegara">
                                        @foreach ($nationalityOptions as $option)
                                            <option value="{{ $option }}"
                                                {{ old('Nationality', 'INA') === $option ? 'selected' : '' }}>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="guest-info-field guest-main-address">
                                <label for="Address">Address</label>
                                <input type="text" name="Address" id="Address" class="form-control package-input"
                                    value="{{ old('Address') }}"
                                    placeholder="Jl. Sudirman No. 123, Karet Semanggi, Setiabudi, Jakarta Selatan"
                                    data-flow data-vb="xAlamat">
                            </div>

                            <div class="guest-main-row-five">
                                <div class="guest-info-field">
                                    <label for="Kelurahan">District / Kelurahan</label>
                                    <input type="text" name="Kelurahan" id="Kelurahan" class="form-control package-input"
                                        value="{{ old('Kelurahan') }}" placeholder="Kelapa Dua" data-flow data-vb="xKelurahan">
                                </div>
                                <div class="guest-info-field">
                                        <label for="Kecamatan">Sub-Dis/Kecamatan</label>
                                    <input type="text" name="Kecamatan" id="Kecamatan" class="form-control package-input"
                                        value="{{ old('Kecamatan') }}" placeholder="Curug" data-flow data-vb="xKecamatan">
                                </div>
                                <div class="guest-info-field">
                                    <label for="KabCity">City / Regency</label>
                                    <input type="text" name="KabCity" id="KabCity" class="form-control package-input"
                                        value="{{ old('KabCity') }}" placeholder="Tangerang" data-flow data-vb="xKota">
                                </div>
                                <div class="guest-info-field">
                                    <label for="ProvinceCountry">Province / Provinsi<span class="text-danger">*</span></label>
                                    @php
                                        $oldProvince = old('ProvinceCountry');
                                        $oldProvinceSource = old('ProvinceCountrySource', 'manual');
                                    @endphp
                                    <input type="hidden" name="ProvinceCountrySource" id="ProvinceCountrySource"
                                        value="{{ $oldProvinceSource === 'scan' ? 'scan' : 'manual' }}">
                                    <select name="ProvinceCountry" id="ProvinceCountry" class="form-control package-select"
                                        data-flow data-vb="xPropinsi">
                                        <option value="">Select province / provinsi</option>
                                        @foreach ($provinceOptions as $option)
                                            <option value="{{ $option }}"
                                                {{ old('ProvinceCountry') === $option ? 'selected' : '' }}>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                        @if ($oldProvinceSource === 'scan' && filled($oldProvince) && !in_array($oldProvince, $provinceOptions, true))
                                            <option value="{{ $oldProvince }}" selected data-ocr-generated="1">{{ $oldProvince }}</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="guest-info-field">
                                    <label for="PostalCode">Postal Code</label>
                                    <input type="text" name="PostalCode" id="PostalCode" class="form-control package-input"
                                        value="{{ old('PostalCode') }}" placeholder="15810" maxlength="10" inputmode="numeric">
                                </div>
                            </div>

                            <div class="guest-main-row-four">
                                <div class="guest-info-field">
                                    <label for="Phone">Phone</label>
                                    <div class="guest-contact-input">
                                        <span class="guest-contact-icon"><i class="fa fa-phone"></i></span>
                                        <input type="text" name="Phone" id="Phone" class="form-control package-input"
                                            value="{{ old('Phone') }}" placeholder="0812-3456-7890" data-flow data-vb="xPhone">
                                    </div>
                                </div>
                                <div class="guest-info-field">
                                    <label for="Email">Email</label>
                                    <div class="guest-contact-input">
                                        <span class="guest-contact-icon"><i class="fa fa-envelope-o"></i></span>
                                        <input type="email" name="Email" id="Email" class="form-control package-input"
                                            value="{{ old('Email') }}" placeholder="budi.santoso@email.com" data-flow data-vb="xEmail">
                                    </div>
                                </div>
                                <div class="guest-info-field">
                                    <label for="Occupation">Occupation</label>
                                    <input type="text" name="Occupation" id="Occupation" class="form-control package-input"
                                        value="{{ old('Occupation') }}" placeholder="Entrepreneur" data-flow data-vb="xProfesi">
                                </div>
                                <div class="guest-info-field">
                                    <label for="ExpiredDateDisplay">ID Expiry</label>
                                    <div class="package-date-group" data-date-field>
                                        <input type="hidden" name="ExpiredDate" id="ExpiredDate" value="{{ $expiredIso }}">
                                        <input type="text" id="ExpiredDateDisplay" class="form-control package-input"
                                            value="{{ $expiredIso ? \Carbon\Carbon::parse($expiredIso)->format('d-m-Y') : '' }}"
                                            placeholder="dd-MM-yyyy" inputmode="numeric" data-flow data-vb="xExpired">
                                        <button type="button" class="package-date-picker" data-date-button
                                            aria-label="Open system date picker"><i class="fa-regular fa-calendar"></i></button>
                                        <input type="date" class="package-date-native" data-date-native
                                            value="{{ $expiredIso }}" tabindex="-1" aria-hidden="true">
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>

                        <!-- ROOMS SECTION -->
                        <div class="checkin-section">
                            <div class="checkin-section-header">
                                <h3><i class="fas fa-bed"></i> Guest & Rooms</h3>
                                <span class="room-allocation-note">Use <strong>Same as Leader</strong> when the guest follows the leader data. Uncheck it to fill guest detail manually or by scanning KTP.</span>
                                <button type="button" class="btn package-btn-add" id="addRoomRowButton"
                                    style="margin-left: auto;"><i class="fa-solid fa-plus mr-1"></i>Add Room</button>
                            </div>
                            <div class="room-allocation-wrap">
                                <table class="room-allocation-table">
                            <thead>
                                <tr>
                                    <th class="room-column-room">Room Number</th>
                                    <th class="room-column-guest">Guest Summary</th>
                                    <th class="room-column-pax">Pax</th>
                                    <th class="room-column-package">Package Code</th>
                                    <th class="room-column-nominal">Nominal</th>
                                    <th class="room-column-position">Group Position</th>
                                    <th class="room-column-same text-center">Same as Leader</th>
                                    <th class="room-column-action text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="room-code-cell">
                                        <input type="hidden" name="DetailKeyList[]" id="PrimaryDetailKey"
                                            value="{{ $firstDetailKey }}">
                                        <input type="hidden" name="SameAsLeaderList[]" id="PrimarySameAsLeader" value="1">
                                        <input type="hidden" name="RoomGuestNameList[]" value="">
                                        <input type="hidden" name="RoomGuestIdTypeList[]" value="KTP">
                                        <input type="hidden" name="RoomGuestIdNumberList[]" value="">
                                        <input type="hidden" name="RoomGuestBirthDateList[]" value="">
                                        <input type="hidden" name="RoomGuestPhoneList[]" value="">
                                        <input type="hidden" name="RoomGuestEmailList[]" value="">
                                        <input type="hidden" name="RoomGuestAddressList[]" value="">
                                        <input type="hidden" name="RoomGuestNationalityList[]" value="INA">
                                        <input type="hidden" name="RoomGroupPositionList[]" id="PrimaryRoomGroupPositionHidden" value="{{ strtoupper((string) $primaryGroupPosition) }}">
                                        <select name="RoomCodeList[]" id="RoomCode" class="form-control package-select"
                                            data-flow data-vb="xKode" required>
                                            <option value="">Select room</option>
                                            @foreach ($rooms as $room)
                                                <option value="{{ $room['kode'] }}"
                                                    {{ $firstRoomCode === $room['kode'] ? 'selected' : '' }}>
                                                    {{ $room['kode'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <div class="room-display-text">
                                            <span id="PrimaryRoomGuestName">{{ old('GuestName') ?: '-' }}</span>
                                            <span class="room-main-badge" id="PrimaryRoomBadge">Primary</span>
                                        </div>
                                    </td>
                                    <td class="room-pax-cell">
                                        <input type="number" id="PrimaryRoomPaxDisplay"
                                            class="form-control package-input text-right" value="{{ old('NumberOfPerson', 2) }}"
                                            min="1" max="20">
                                    </td>
                                    <td class="room-package-cell">
                                        <select name="PackageCodeList[]" id="PackageCode"
                                            class="form-control package-select" data-flow data-vb="xPackage">
                                            <option value="">Select package</option>
                                            @foreach ($packages as $package)
                                                <option value="{{ $package['kode'] }}"
                                                    {{ $firstPackageCode === $package['kode'] ? 'selected' : '' }}>
                                                    {{ $package['kode'] }} - {{ $package['nama'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="room-nominal-cell">
                                        <input type="text" id="NominalDisplayVisible"
                                            class="form-control package-input text-right room-muted-display"
                                            data-vb="xNominal"
                                            value="{{ $firstNominal ? number_format((float) preg_replace('/[^\d]/', '', (string) $firstNominal), 0, ',', '.') : '' }}"
                                            readonly>
                                        <input type="hidden" id="NominalDisplay" name="NominalList[]"
                                            value="{{ $firstNominal ? preg_replace('/[^\d]/', '', (string) $firstNominal) : '' }}">
                                        <input type="hidden" name="BreakfastList[]" id="Breakfast"
                                            value="{{ $firstBreakfast }}" data-vb="xBF">
                                    </td>
                                    <td>
                                        <select id="PrimaryRoomGroupPosition" class="form-control package-select room-position-input"
                                            data-flow data-vb="xPosisi">
                                            <option value="">Select position</option>
                                            @foreach ($groupPositionOptions as $option)
                                                <option value="{{ $option }}" {{ strtoupper((string) $primaryGroupPosition) === strtoupper((string) $option) ? 'selected' : '' }}>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-center">
                                        <span class="room-follow-badge">Leader</span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="room-action-btn" id="clearPrimaryRoomButton"
                                            title="Clear room"><i class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody id="additionalRoomBody"></tbody>
                                </table>
                                <div class="room-summary">
                                    <span>Total Rooms: <strong id="RoomSummaryCount">1</strong></span>
                                    <span>Total Pax: <strong id="RoomSummaryPax">2</strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- ADDITIONAL DETAILS SECTION -->
                        <div class="checkin-section">
                            <div class="checkin-section-header">
                                <h3><i class="fas fa-clipboard-list"></i> Additional Details</h3>
                            </div>
                            <div class="checkin-section-body additional-details-body">
                                <div class="detail-info-grid additional-details-grid">
                                    <div class="detail-info-column">
                                        <div class="detail-info-row-two">
                                            <div class="detail-info-field">
                                                <label for="Religion">Religion</label>
                                                <select name="Religion" id="Religion" class="form-control package-select" data-flow data-vb="xAgama">
                                                    <option value="">Select</option>
                                                    @foreach ($religionOptions as $option)
                                                        <option value="{{ $option }}" {{ old('Religion') === $option ? 'selected' : '' }}>
                                                            {{ $option }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="detail-info-field">
                                                <label for="NumberOfPerson">Number of Person</label>
                                                <input type="number" name="NumberOfPerson" id="NumberOfPerson"
                                                    class="form-control package-input text-right" value="{{ old('NumberOfPerson', 2) }}"
                                                    data-vb="xPerson"
                                                    min="1" max="20" data-flow required>
                                            </div>
                                        </div>
                                        <div class="detail-info-field">
                                            <label for="GuestName2">Name II</label>
                                            <input type="text" name="GuestName2" id="GuestName2" class="form-control package-input"
                                                value="{{ old('GuestName2') }}" placeholder="Enter secondary guest name (optional)" data-flow data-vb="xGuest2">
                                        </div>
                                    </div>

                                    <div class="detail-info-column">
                                        <div class="detail-info-row-two">
                                            <div class="detail-info-field">
                                                <label for="PlaceOfBirth">Place of Birth</label>
                                                <input type="text" name="PlaceOfBirth" id="PlaceOfBirth"
                                                    class="form-control package-input" value="{{ old('PlaceOfBirth') }}"
                                                    data-vb="xPlaceBirth" placeholder="Jakarta" data-flow>
                                            </div>
                                            <div class="detail-info-field">
                                                <label for="Segment">Segment</label>
                                                <select name="Segment" id="Segment" class="form-control package-select" data-flow data-vb="xSegment">
                                                    <option value="">Select</option>
                                                    @foreach ($segmentOptions as $option)
                                                        <option value="{{ $option }}"
                                                            {{ old('Segment', 'TRAVEL') === $option ? 'selected' : '' }}>
                                                            {{ $option }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="detail-info-field">
                                            <label for="Member">Member 1</label>
                                            <input type="text" name="Member" id="Member" class="form-control package-input"
                                                value="{{ old('Member') }}" data-flow data-vb="xMember">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PAYMENT INFORMATION SECTION -->
                        <div class="checkin-section payment-info-section">
                            <div class="checkin-section-header">
                                <h3><i class="fas fa-credit-card"></i> Payment Information</h3>
                            </div>
                            <div class="checkin-section-body payment-info-body">
                                <div class="detail-info-grid payment-info-grid">
                                    <div class="detail-info-column">
                                        <div class="detail-info-field">
                                            <label for="PaymentMethod">Payment Method</label>
                                            <select name="PaymentMethod" id="PaymentMethod" class="form-control package-select" data-flow
                                                data-vb="xPayment" required>
                                                @foreach ($paymentOptions as $option)
                                                    <option value="{{ $option }}"
                                                        {{ old('PaymentMethod', 'OTA') === $option ? 'selected' : '' }}>
                                                        {{ $option }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="detail-info-row-two">
                                            <div class="detail-info-field">
                                                <label for="CreditCardNumber">Credit Card #</label>
                                                <input type="text" name="CreditCardNumber" id="CreditCardNumber"
                                                    class="form-control package-input" value="{{ old('CreditCardNumber') }}" data-flow data-vb="xCreditCard">
                                            </div>
                                            <div class="detail-info-field payment-checkbox-field">
                                                <label for="CheckDeposit">Deposit</label>
                                                <label class="payment-checkbox-control" for="CheckDeposit"><input type="checkbox"
                                                        name="CheckDeposit" id="CheckDeposit" value="1" data-vb="xPeriksa"
                                                        {{ old('CheckDeposit') ? 'checked' : '' }}>
                                                    Check Deposit</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="detail-info-column">
                                        <div class="detail-info-field">
                                            <label for="Sales">Sales</label>
                                            <select name="Sales" id="Sales" class="form-control package-select" data-flow data-vb="xSales">
                                                <option value="">Select sales</option>
                                                @foreach ($salesOptions as $option)
                                                    <option value="{{ $option }}" {{ old('Sales') === $option ? 'selected' : '' }}>
                                                        {{ $option }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <aside class="checkin-sidebar-stack guest-side-stack">
                        <div class="group-info-card">
                            <div>
                                <small>Group Information</small>
                                <strong>Group / Billing Summary</strong>
                            </div>
                            <div class="group-info-form">
                                <div class="guest-info-field">
                                    <label for="Company">Group / Company Name</label>
                                    <select name="Company" id="Company" class="form-control package-select" data-flow data-vb="xUsaha">
                                        <option value="">Select company</option>
                                        @foreach ($companyOptions as $option)
                                            <option value="{{ $option }}" {{ old('Company') === $option ? 'selected' : '' }}>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="guest-info-field">
                                    <label for="BillingTypeDisplay">Billing Type</label>
                                    <input type="text" id="BillingTypeDisplay" class="form-control package-input"
                                        value="Direct Folio" readonly>
                                </div>
                                <div class="guest-info-field">
                                    <label for="Remarks">Remarks</label>
                                    <textarea name="Remarks" id="Remarks" class="form-control package-input group-info-remark"
                                        data-flow data-vb="xRemark"
                                        placeholder="Meeting & training for 3 days">{{ old('Remarks') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="guest-brief-grid">
                            <div class="guest-brief-card">
                                <small>Leader / Person in Charge</small>
                                <strong id="LeaderSummaryName">{{ old('GuestName') ?: '-' }}</strong>
                                <span id="LeaderSummaryPhone">{{ old('Phone') ?: '-' }}</span>
                            </div>
                            <div class="guest-note-card">
                                <small>Note</small>
                                <strong>Check-in guidance</strong>
                                <span>All group charges can follow the leader until each room is confirmed separately.</span>
                            </div>
                        </div>

                        <div class="scan-guide-card">
                            <div>
                                <small>How to Scan KTP</small>
                                <strong>Quick OCR Flow</strong>
                            </div>
                            <ol>
                                <li>Click the <em>Scan KTP</em> button below.</li>
                                <li>Choose a clear KTP image from your device.</li>
                                <li>Wait until OCR reads the main guest information.</li>
                                <li>Review the result and correct any field if needed.</li>
                            </ol>
                        </div>

                        <div class="ktp-scan-card">
                            <div class="ktp-scan-card-head">
                                <div>
                                    <small>Scan Result Preview</small>
                                    <strong>Upload KTP to fill leader data</strong>
                                </div>
                            </div>
                            <p class="ktp-scan-status" id="leaderScanStatus">No KTP scanned yet. Upload an image to start OCR.</p>
                            <div class="ktp-scan-preview" id="leaderScanPreview">
                                <span class="ktp-scan-preview-empty">KTP preview will appear here after upload.</span>
                                <img id="leaderScanImage" alt="Leader KTP preview" hidden>
                            </div>
                            <div class="ktp-scan-result" id="leaderScanResult">
                                <span class="ktp-scan-result-title">Auto-filled fields</span>
                                <ul>
                                    <li>Name and ID number</li>
                                    <li>Date of birth and nationality</li>
                                    <li>Address, district, city, province</li>
                                    <li>Religion and basic guest profile</li>
                                </ul>
                                <div id="leaderScanInlineDebug" style="margin-top: 0.85rem; display: none; padding: 0.85rem; border-radius: 14px; background: #f6f9ff; border: 1px solid rgba(137, 167, 214, 0.24);">
                                    <strong style="display: block; margin-bottom: 0.45rem; color: #173761; font-size: 0.84rem;">Hasil OCR Terdeteksi</strong>
                                    <div id="leaderScanInlineMessage" style="margin-bottom: 0.55rem; color: #41546f; font-size: 0.8rem;"></div>
                                    <pre id="leaderScanInlineRawText" style="margin: 0; max-height: 220px; overflow: auto; white-space: pre-wrap; word-break: break-word; padding: 0.75rem; border-radius: 12px; background: #fff; border: 1px solid rgba(137, 167, 214, 0.24); color: #41546f; font-size: 0.78rem;"></pre>
                                </div>
                                <details id="leaderScanDebug" style="margin-top: 0.85rem;" hidden>
                                    <summary style="cursor: pointer; font-weight: 700; color: #173761;">OCR Debug / Detail Teks</summary>
                                    <div style="margin-top: 0.75rem; display: grid; gap: 0.65rem;">
                                        <div>
                                            <strong style="display: block; margin-bottom: 0.35rem; color: #173761; font-size: 0.82rem;">Parsed Fields</strong>
                                            <pre id="leaderScanParsed" style="margin: 0; max-height: 140px; overflow: auto; white-space: pre-wrap; word-break: break-word; padding: 0.75rem; border-radius: 12px; background: #f6f9ff; border: 1px solid rgba(137, 167, 214, 0.24); color: #41546f; font-size: 0.78rem;"></pre>
                                        </div>
                                        <div>
                                            <strong style="display: block; margin-bottom: 0.35rem; color: #173761; font-size: 0.82rem;">Raw OCR Text</strong>
                                            <pre id="leaderScanRawText" style="margin: 0; max-height: 220px; overflow: auto; white-space: pre-wrap; word-break: break-word; padding: 0.75rem; border-radius: 12px; background: #f6f9ff; border: 1px solid rgba(137, 167, 214, 0.24); color: #41546f; font-size: 0.78rem;"></pre>
                                        </div>
                                    </div>
                                </details>
                            </div>
                            <div class="ktp-scan-card-footer">
                                <button type="button" class="btn package-btn-primary ktp-scan-trigger" id="leaderScanButton">
                                    <i class="fa-solid fa-camera mr-1"></i>Scan KTP
                                </button>
                                <input type="file" id="leaderKtpUpload" accept="image/*" hidden>
                            </div>
                        </div>
                    </aside>
                </div>


    <div class="checkin-actions">
        <div class="checkin-actions-main">
            <button type="submit" class="btn package-btn-primary" id="saveButton">Save Check In</button>
            <button type="button" class="btn package-btn-secondary" id="printRegistrationButton">Print</button>
            <button type="button" class="btn package-btn-secondary" id="newEntryButton">New
                Entry</button>
            <button type="button" class="btn package-btn-secondary" id="focusSearchButton">Search
                Data</button>
        </div>
    </div>
    </form>
    </div>
    </section>

    @include('checkin.partials.directory-section')

    <datalist id="packageCodeOptions">
        @foreach ($packages as $package)
            <option value="{{ $package['kode'] }}" data-name="{{ $package['nama'] }}"
                data-nominal="{{ $package['nominal'] }}" data-expired="{{ $package['expired'] }}">
                {{ $package['kode'] }} - {{ $package['nama'] }}</option>
        @endforeach
    </datalist>

    <datalist id="roomCodeOptions">
        @foreach ($rooms as $room)
            <option value="{{ $room['kode'] }}" data-kelas="{{ $room['kelas'] }}"
                data-status="{{ $room['status'] }}" data-status2="{{ $room['status2'] }}"
                data-available="{{ $room['available'] ? '1' : '0' }}">
                {{ $room['kode'] }} - {{ $room['kelas'] }}
            </option>
        @endforeach
    </datalist>
    </div>

    <template id="additionalRoomRowTemplate">
        <tr class="additional-room-row" data-room-row>
            <td>
                <input type="hidden" name="DetailKeyList[]" class="detail-key-input" value="">
                <input type="hidden" name="SameAsLeaderList[]" class="same-as-leader-input" value="1">
                <select name="RoomCodeList[]" class="form-control package-select room-code-input">
                    <option value="">Select room</option>
                    @foreach ($rooms as $room)
                        <option value="{{ $room['kode'] }}">{{ $room['kode'] }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <div class="room-guest-summary">
                    <input type="text" class="form-control package-input room-guest-display-input"
                        placeholder="Following leader data" readonly>
                    <small class="room-guest-summary-note">Use the checkbox if this room follows the leader.</small>
                </div>
            </td>
            <td>
                <input type="number" class="form-control package-input text-right room-pax-display-input"
                    min="1" max="20" value="1">
            </td>
            <td>
                <select name="PackageCodeList[]" class="form-control package-select package-code-input">
                    <option value="">Select package</option>
                    @foreach ($packages as $package)
                        <option value="{{ $package['kode'] }}">{{ $package['kode'] }} - {{ $package['nama'] }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" class="form-control package-input text-right nominal-display-input room-muted-display"
                    value="" readonly>
                <input type="hidden" name="NominalList[]" class="nominal-input" value="">
                <input type="hidden" name="BreakfastList[]" class="breakfast-input" value="0">
            </td>
            <td>
                <select name="RoomGroupPositionList[]" class="form-control package-select room-position-input">
                    <option value="">Select position</option>
                    @foreach ($groupPositionOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            </td>
            <td class="text-center">
                <label class="room-follow-toggle">
                    <input type="checkbox" class="room-same-checkbox" checked>
                    <span>Yes</span>
                </label>
            </td>
            <td class="text-center room-action-stack">
                <button type="button" class="room-action-btn room-row-expand" title="Show guest detail">
                    <i class="fa-solid fa-address-card"></i>
                </button>
                <button type="button" class="room-action-btn room-row-remove"
                    title="Remove room"><i class="fa-solid fa-trash"></i></button>
            </td>
        </tr>
        <tr class="room-guest-detail-row is-collapsed" data-room-detail-row>
            <td colspan="8">
                <div class="room-guest-detail-card">
                    <div class="room-guest-detail-head">
                        <div>
                            <strong>Guest Detail for This Room</strong>
                            <small>Uncheck <em>Same as Leader</em> to enable these fields.</small>
                        </div>
                        <div class="room-guest-detail-actions">
                            <button type="button" class="btn package-btn-secondary room-guest-scan-btn">
                                <i class="fa-solid fa-camera mr-1"></i>Scan KTP
                            </button>
                            <input type="file" class="room-guest-ktp-input" accept="image/*" hidden>
                            <span class="room-guest-scan-status">Following leader data</span>
                        </div>
                    </div>
                    <div class="room-guest-detail-grid">
                        <div class="guest-info-field">
                            <label>Full Name</label>
                            <input type="text" name="RoomGuestNameList[]" class="form-control package-input room-guest-name-input" value="">
                        </div>
                        <div class="guest-info-field">
                            <label>ID Type</label>
                            <select name="RoomGuestIdTypeList[]" class="form-control package-select room-guest-id-type-input">
                                @foreach ($idTypeOptions as $option)
                                    <option value="{{ $option }}" {{ $option === 'KTP' ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="guest-info-field">
                            <label>ID Number</label>
                            <input type="text" name="RoomGuestIdNumberList[]" class="form-control package-input room-guest-id-number-input" value="">
                        </div>
                        <div class="guest-info-field">
                            <label>Date of Birth</label>
                            <input type="date" name="RoomGuestBirthDateList[]" class="form-control package-input room-guest-birth-date-input" value="">
                        </div>
                        <div class="guest-info-field">
                            <label>Phone</label>
                            <input type="text" name="RoomGuestPhoneList[]" class="form-control package-input room-guest-phone-input" value="">
                        </div>
                        <div class="guest-info-field">
                            <label>Email</label>
                            <input type="email" name="RoomGuestEmailList[]" class="form-control package-input room-guest-email-input" value="">
                        </div>
                        <div class="guest-info-field">
                            <label>Nationality</label>
                            <select name="RoomGuestNationalityList[]" class="form-control package-select room-guest-nationality-input">
                                @foreach ($nationalityOptions as $option)
                                    <option value="{{ $option }}" {{ $option === 'INA' ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="guest-info-field room-guest-address-field">
                            <label>Address</label>
                            <input type="text" name="RoomGuestAddressList[]" class="form-control package-input room-guest-address-input" value="">
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </template>

    <script src="{{ asset('vendor/tesseract/tesseract.min.js') }}"></script>
    <script>
        function normalizeNumber(value) {
            return (value || '').toString().replace(/[^\d]/g, '');
        }

        function formatRibuan(value) {
            const normalized = normalizeNumber(value);
            return normalized ? normalized.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
        }

        function formatDisplayDate(value) {
            if (!value) {
                return '';
            }
            const normalized = value.toString().trim().replace(/\//g, '-');
            const parts = normalized.split('-');
            if (parts.length === 3 && parts[0].length === 4) {
                return [parts[2], parts[1], parts[0]].join('-');
            }
            if (parts.length === 3 && parts[2].length === 4) {
                return [parts[0].padStart(2, '0'), parts[1].padStart(2, '0'), parts[2]].join('-');
            }
            return value;
        }

        function normalizeDisplayDate(value) {
            const normalized = (value || '').toString().trim().replace(/\//g, '-');
            if (!normalized) {
                return '';
            }
            const parts = normalized.split('-');
            if (parts.length !== 3) {
                return '';
            }
            const day = parts[0].padStart(2, '0');
            const month = parts[1].padStart(2, '0');
            const year = parts[2];
            if (year.length !== 4) {
                return '';
            }
            const iso = year + '-' + month + '-' + day;
            const testDate = new Date(iso + 'T00:00:00');
            if (Number.isNaN(testDate.getTime())) {
                return '';
            }
            return testDate.getFullYear().toString() === year && (testDate.getMonth() + 1).toString().padStart(2, '0') ===
                month && testDate.getDate().toString().padStart(2, '0') === day ? iso : '';
        }

        function showCrudAlert(message) {
            if (typeof window.showCrudNotice === 'function') {
                window.showCrudNotice(message, 'Check In');
                return;
            }
            window.alert(message);
        }

        function findOptionByValue(listId, value) {
            const normalized = (value || '').toString().trim().toUpperCase();
            return Array.from(document.querySelectorAll('#' + listId + ' option')).find(option => option.value.trim()
                .toUpperCase() === normalized) || null;
        }

        const form = document.getElementById('checkinForm');
        const csrfToken = form?.querySelector('input[name="_token"]')?.value || '';
        const generatedRegNoField = document.getElementById('GeneratedRegNo');
        const currentDetailKeyField = document.getElementById('CurrentDetailKey');
        const primaryDetailKeyField = document.getElementById('PrimaryDetailKey');
        const saveButton = document.getElementById('saveButton');
        const printRegistrationButton = document.getElementById('printRegistrationButton');
        const newEntryButton = document.getElementById('newEntryButton');
        const focusSearchButton = document.getElementById('focusSearchButton');
        const addRoomRowButton = document.getElementById('addRoomRowButton');
        const additionalRoomBody = document.getElementById('additionalRoomBody');
        const additionalRoomRowTemplate = document.getElementById('additionalRoomRowTemplate');
        const searchKeyword = document.getElementById('searchKeyword');
        const roomCodeField = document.getElementById('RoomCode');
        const packageCodeField = document.getElementById('PackageCode');
        const nominalField = document.getElementById('NominalDisplay');
        const nominalVisibleField = document.getElementById('NominalDisplayVisible');
        const breakfastField = document.getElementById('Breakfast');
        const guestNameField = document.getElementById('GuestName');
        const numberOfPersonField = document.getElementById('NumberOfPerson');
        const reservationNumberField = document.getElementById('ReservationNumber');
        const primaryRoomGuestName = document.getElementById('PrimaryRoomGuestName');
        const primaryRoomBadge = document.getElementById('PrimaryRoomBadge');
        const primaryRoomPaxDisplay = document.getElementById('PrimaryRoomPaxDisplay');
        const primaryRoomPositionField = document.getElementById('PrimaryRoomGroupPosition');
        const primaryRoomPositionHiddenField = document.getElementById('PrimaryRoomGroupPositionHidden');
        const clearPrimaryRoomButton = document.getElementById('clearPrimaryRoomButton');
        const roomSummaryCount = document.getElementById('RoomSummaryCount');
        const roomSummaryPax = document.getElementById('RoomSummaryPax');
        const roomHelper = document.getElementById('roomHelper');
        const packageHelper = document.getElementById('packageHelper');
        const leaderScanButton = document.getElementById('leaderScanButton');
        const leaderKtpUpload = document.getElementById('leaderKtpUpload');
        const leaderScanStatus = document.getElementById('leaderScanStatus');
        const leaderScanPreview = document.getElementById('leaderScanPreview');
        const leaderScanImage = document.getElementById('leaderScanImage');
        const leaderScanInlineDebug = document.getElementById('leaderScanInlineDebug');
        const leaderScanInlineMessage = document.getElementById('leaderScanInlineMessage');
        const leaderScanInlineRawText = document.getElementById('leaderScanInlineRawText');
        const leaderScanDebug = document.getElementById('leaderScanDebug');
        const leaderScanParsed = document.getElementById('leaderScanParsed');
        const leaderScanRawText = document.getElementById('leaderScanRawText');
        const billingTypeDisplay = document.getElementById('BillingTypeDisplay');
        const leaderSummaryName = document.getElementById('LeaderSummaryName');
        const leaderSummaryPhone = document.getElementById('LeaderSummaryPhone');
        const defaultRegNo = @json(old('GeneratedRegNo', $nextRegNo));
        const defaultTypeOfCheckIn = @json($defaultTypeOfCheckIn);
        const defaultIdType = @json($defaultIdType);
        const defaultCheckIn = @json($checkInIso);
        const defaultCheckOut = @json($checkOutIso);
        const defaultCheckInTime = @json(old('CheckInTime', now()->format('H:i:s')));
        const oldAdditionalRoomRows = @json($oldAdditionalRoomRows);
        const initialHasOld = @json((bool) (old('GuestName') || old('RoomCodeList.0')));
        const vbFields = {};
        Array.from(document.querySelectorAll('[data-vb]')).forEach(function(field) {
            const vbName = field.dataset.vb;
            if (!vbName) {
                return;
            }

            vbFields[vbName] = field;
            if (typeof window[vbName] === 'undefined') {
                window[vbName] = field;
            }
        });
        window.vbFields = vbFields;
        const xRegNo = vbFields.xRegNo || generatedRegNoField;
        const xTipe = vbFields.xTipe || document.getElementById('TypeOfCheckIn');
        const xTglIn = vbFields.xTglIn || document.getElementById('CheckInDateDisplay');
        const xJamIn = vbFields.xJamIn || document.getElementById('CheckInTime');
        const xResNo = vbFields.xResNo || reservationNumberField;
        const xTglKeluar = vbFields.xTglKeluar || document.getElementById('EstimationOutDisplay');
        const xGuest = vbFields.xGuest || guestNameField;
        const xGuest2 = vbFields.xGuest2 || document.getElementById('GuestName2');
        const xPlaceBirth = vbFields.xPlaceBirth || document.getElementById('PlaceOfBirth');
        const xTglLahir = vbFields.xTglLahir || document.getElementById('BirthDateDisplay');
        const xTypeId = vbFields.xTypeId || document.getElementById('TypeOfId');
        const xKTP = vbFields.xKTP || document.getElementById('IdNumber');
        const xExpired = vbFields.xExpired || document.getElementById('ExpiredDateDisplay');
        const xPhone = vbFields.xPhone || document.getElementById('Phone');
        const xEmail = vbFields.xEmail || document.getElementById('Email');
        const xAlamat = vbFields.xAlamat || document.getElementById('Address');
        const xKelurahan = vbFields.xKelurahan || document.getElementById('Kelurahan');
        const xKecamatan = vbFields.xKecamatan || document.getElementById('Kecamatan');
        const xKota = vbFields.xKota || document.getElementById('KabCity');
        const xPropinsi = vbFields.xPropinsi || document.getElementById('ProvinceCountry');
        const provinceCountrySourceField = document.getElementById('ProvinceCountrySource');
        const xKodeNegara = vbFields.xKodeNegara || document.getElementById('Nationality');
        const xKode = vbFields.xKode || roomCodeField;
        const xPackage = vbFields.xPackage || packageCodeField;
        const xNominal = vbFields.xNominal || nominalVisibleField;
        const xBF = vbFields.xBF || breakfastField;
        const xPosisi = vbFields.xPosisi || primaryRoomPositionField;
        const xAgama = vbFields.xAgama || document.getElementById('Religion');
        const xPerson = vbFields.xPerson || numberOfPersonField;
        const xUsaha = vbFields.xUsaha || document.getElementById('Company');
        const xProfesi = vbFields.xProfesi || document.getElementById('Occupation');
        const xSegment = vbFields.xSegment || document.getElementById('Segment');
        const xRemark = vbFields.xRemark || document.getElementById('Remarks');
        const xPayment = vbFields.xPayment || document.getElementById('PaymentMethod');
        const xCreditCard = vbFields.xCreditCard || document.getElementById('CreditCardNumber');
        const xPeriksa = vbFields.xPeriksa || document.getElementById('CheckDeposit');
        const xMember = vbFields.xMember || document.getElementById('Member');
        const xSales = vbFields.xSales || document.getElementById('Sales');

        function normalizeTimeDisplay(value) {
            const raw = (value || '').toString().trim();
            if (!raw) {
                return '';
            }

            const directMatch = raw.match(/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/);
            if (directMatch) {
                const hours = parseInt(directMatch[1], 10);
                const minutes = parseInt(directMatch[2], 10);
                const seconds = parseInt(directMatch[3] || '0', 10);

                if (hours <= 23 && minutes <= 59 && seconds <= 59) {
                    return [hours, minutes, seconds].map(part => String(part).padStart(2, '0')).join(':');
                }
            }

            const digits = raw.replace(/[^\d]/g, '');
            if (digits.length === 6) {
                const hours = parseInt(digits.slice(0, 2), 10);
                const minutes = parseInt(digits.slice(2, 4), 10);
                const seconds = parseInt(digits.slice(4, 6), 10);

                if (hours <= 23 && minutes <= 59 && seconds <= 59) {
                    return [hours, minutes, seconds].map(part => String(part).padStart(2, '0')).join(':');
                }
            }

            if (digits.length === 4) {
                const hours = parseInt(digits.slice(0, 2), 10);
                const minutes = parseInt(digits.slice(2, 4), 10);

                if (hours <= 23 && minutes <= 59) {
                    return [hours, minutes, '00'].map(part => String(part).padStart(2, '0')).join(':');
                }
            }

            return raw;
        }

        function ensureSelectValueExists(field, label) {
            if (!field) {
                return true;
            }

            const currentValue = (field.value || '').toString().trim().toUpperCase();
            const hasOption = Array.from(field.options || []).some(function(option) {
                return (option.value || '').toString().trim().toUpperCase() === currentValue;
            });

            if (!hasOption) {
                showCrudAlert(label + ' must be selected from the available list.');
                field.focus();
                return false;
            }

            return true;
        }

        function setProvinceCountrySource(source = 'manual') {
            if (!provinceCountrySourceField) {
                return;
            }

            provinceCountrySourceField.value = source === 'scan' ? 'scan' : 'manual';
        }

        function getSelectedOption(field) {
            if (!field) {
                return null;
            }

            const currentValue = (field.value || '').toString().trim().toUpperCase();
            return Array.from(field.options || []).find(function(option) {
                return (option.value || '').toString().trim().toUpperCase() === currentValue;
            }) || null;
        }

        function ensureProvinceValueAllowed(field) {
            if (!(field?.value || '').trim()) {
                showCrudAlert('Province / Country must be filled.');
                field?.focus();
                return false;
            }

            if (!ensureSelectValueExists(field, 'Province / Country')) {
                return false;
            }

            if ((provinceCountrySourceField?.value || 'manual').toString().trim().toLowerCase() === 'scan') {
                return true;
            }

            const selectedOption = getSelectedOption(field);
            if (selectedOption?.dataset?.ocrGenerated === '1') {
                showCrudAlert('Province / Country manual input must be selected from the available list.');
                field.focus();
                return false;
            }

            return true;
        }

        function isGroupCheckInType(value) {
            return (value || '').toString().trim().toUpperCase().includes('GROUP');
        }

        function paymentRequiresCompany(value) {
            return ['OTA', 'COMPANY', 'TRAVEL'].includes((value || '').toString().trim().toUpperCase());
        }

        function paymentRequiresCreditCard(value) {
            return (value || '').toString().trim().toUpperCase() === 'CARD';
        }

        function deriveBillingType() {
            if (isGroupCheckInType(xTipe?.value)) {
                return 'Master Folio (All charges to leader)';
            }

            const payment = (xPayment?.value || '').toString().trim().toUpperCase();
            if (payment === 'OTA' || payment === 'COMPANY' || payment === 'TRAVEL') {
                return 'Direct Bill (Charges linked to company / agent)';
            }

            return 'Direct Folio (Charges stay on this registration)';
        }

        function updateBillingTypeDisplay() {
            if (billingTypeDisplay) {
                billingTypeDisplay.value = deriveBillingType();
            }
        }

        function syncPrimaryRoomPositionValue() {
            if (primaryRoomPositionHiddenField) {
                primaryRoomPositionHiddenField.value = (primaryRoomPositionField?.value || '').trim().toUpperCase();
            }
        }

        function roomPositionDefault(index) {
            return index === 0 ? 'LEADER' : 'SUB';
        }

        function allRoomPositionFields() {
            return [
                primaryRoomPositionField,
                ...Array.from(additionalRoomBody.querySelectorAll('.room-position-input')),
            ].filter(Boolean);
        }

        function updateGroupPositionAvailability() {
            const allowGroupPosition = isGroupCheckInType(xTipe?.value);
            allRoomPositionFields().forEach(function(field, index) {
                field.disabled = !allowGroupPosition;
                field.required = allowGroupPosition;

                if (!allowGroupPosition) {
                    field.value = '';
                    return;
                }

                if (!(field.value || '').trim()) {
                    const defaultValue = roomPositionDefault(index);
                    if (Array.from(field.options || []).some(option => (option.value || '').toUpperCase() === defaultValue)) {
                        field.value = defaultValue;
                    }
                }
            });
            syncPrimaryRoomPositionValue();
            updatePrimaryGuestDisplay();
        }

        function setSelectByValueOrDataName(field, value, dataAttr = 'name') {
            if (!field) {
                return;
            }

            const normalized = (value || '').toString().trim().toUpperCase();
            if (!normalized) {
                field.value = '';
                return;
            }

            const matchedOption = Array.from(field.options || []).find(function(option) {
                const optionValue = (option.value || '').toString().trim().toUpperCase();
                const optionData = (option.dataset?.[dataAttr] || '').toString().trim().toUpperCase();
                return optionValue === normalized || optionData === normalized;
            });

            field.value = matchedOption ? matchedOption.value : value;
        }

        function setSelectByLooseText(field, value) {
            if (!field) {
                return;
            }

            const normalized = (value || '').toString().trim().toUpperCase();
            const normalizedLoose = normalized.replace(/[^A-Z0-9]/g, '');
            const normalizedTokens = normalized.split(/[^A-Z0-9]+/).filter(token => token.length >= 3);
            if (!normalized) {
                field.value = '';
                return;
            }

            const options = Array.from(field.options || []);
            let matchedOption = options.find(function(option) {
                const optionValue = (option.value || '').toString().trim().toUpperCase();
                const optionText = (option.textContent || '').toString().trim().toUpperCase();
                const optionLoose = optionValue.replace(/[^A-Z0-9]/g, '');
                const optionTextLoose = optionText.replace(/[^A-Z0-9]/g, '');
                return optionValue === normalized ||
                    optionText === normalized ||
                    optionValue.includes(normalized) ||
                    optionText.includes(normalized) ||
                    normalized.includes(optionValue) ||
                    normalized.includes(optionText) ||
                    optionLoose === normalizedLoose ||
                    optionLoose.includes(normalizedLoose) ||
                    normalizedLoose.includes(optionLoose) ||
                    optionTextLoose === normalizedLoose ||
                    optionTextLoose.includes(normalizedLoose) ||
                    normalizedLoose.includes(optionTextLoose);
            });

            if (!matchedOption && normalizedTokens.length) {
                matchedOption = options.find(function(option) {
                    const optionValue = (option.value || '').toString().trim().toUpperCase();
                    const optionText = (option.textContent || '').toString().trim().toUpperCase();
                    const optionTokens = (optionValue + ' ' + optionText).split(/[^A-Z0-9]+/).filter(token => token.length >= 3);
                    return normalizedTokens.some(token => optionValue.includes(token) || optionText.includes(token)) &&
                        optionTokens.some(token => normalized.includes(token));
                });
            }

            if (!matchedOption && /JAKARTA/.test(normalized)) {
                matchedOption = options.find(function(option) {
                    const optionValue = (option.value || '').toString().trim().toUpperCase();
                    const optionText = (option.textContent || '').toString().trim().toUpperCase();
                    return /JAKARTA/.test(optionValue) || /JAKARTA/.test(optionText);
                }) || null;
            }

            if (!matchedOption) {
                const existingCustom = options.find(function(option) {
                    return option.dataset?.ocrGenerated === '1' && (option.value || '').toString().trim().toUpperCase() === normalized;
                });

                matchedOption = existingCustom || null;
                if (!matchedOption) {
                    matchedOption = document.createElement('option');
                    matchedOption.value = value;
                    matchedOption.textContent = value;
                    matchedOption.dataset.ocrGenerated = '1';
                    field.appendChild(matchedOption);
                }
            }

            Array.from(field.options || []).forEach(function(option) {
                option.selected = option === matchedOption;
            });
            field.value = matchedOption.value;
            field.selectedIndex = Array.from(field.options || []).indexOf(matchedOption);
        }

        function setSelectValueDirect(field, value) {
            if (!field) {
                return;
            }

            const normalized = (value || '').toString().trim();
            if (!normalized) {
                field.value = '';
                return;
            }

            const normalizedUpper = normalized.toUpperCase();
            const options = Array.from(field.options || []);
            let matchedOption = options.find(function(option) {
                return (option.value || '').toString().trim().toUpperCase() === normalizedUpper ||
                    (option.textContent || '').toString().trim().toUpperCase() === normalizedUpper;
            }) || null;

            if (!matchedOption) {
                matchedOption = options.find(function(option) {
                    return option.dataset?.ocrGenerated === '1' &&
                        (option.value || '').toString().trim().toUpperCase() === normalizedUpper;
                }) || null;
            }

            if (!matchedOption) {
                matchedOption = document.createElement('option');
                matchedOption.value = normalized;
                matchedOption.textContent = normalized;
                matchedOption.dataset.ocrGenerated = '1';
                field.appendChild(matchedOption);
            }

            Array.from(field.options || []).forEach(function(option) {
                option.selected = option === matchedOption;
            });
            field.value = matchedOption.value;
            field.selectedIndex = Array.from(field.options || []).indexOf(matchedOption);
        }

        function hasMeaningfulScanData(parsed) {
            if (!parsed || typeof parsed !== 'object') {
                return false;
            }

            return ['name', 'idNumber', 'birthDate', 'address', 'kelurahan', 'kecamatan', 'city', 'province']
                .some(function(key) {
                    return !!(parsed[key] || '').toString().trim();
                });
        }

        function renderLeaderScanDebug(parsed, errorMessage = '') {
            if (!leaderScanDebug || !leaderScanParsed || !leaderScanRawText) {
                return;
            }

            const debugPayload = parsed && typeof parsed === 'object'
                ? {
                    provider: parsed.provider || '',
                    correctionProvider: parsed.correctionProvider || '',
                    name: parsed.name || '',
                    idNumber: parsed.idNumber || '',
                    placeOfBirth: parsed.placeOfBirth || '',
                    birthDate: parsed.birthDate || '',
                    address: parsed.address || '',
                    kelurahan: parsed.kelurahan || '',
                    kecamatan: parsed.kecamatan || '',
                    city: parsed.city || '',
                    province: parsed.province || '',
                    religion: parsed.religion || '',
                    occupation: parsed.occupation || '',
                    postalCode: parsed.postalCode || '',
                }
                : {};

            if (errorMessage) {
                debugPayload.error = errorMessage;
            }

            leaderScanParsed.textContent = JSON.stringify(debugPayload, null, 2);
            leaderScanRawText.textContent = parsed?.rawText || '';
            leaderScanDebug.hidden = false;
            leaderScanDebug.open = true;
        }

        function renderLeaderScanInlineDebug(parsed, message = '') {
            if (!leaderScanInlineDebug || !leaderScanInlineMessage || !leaderScanInlineRawText) {
                return;
            }

            const rawText = (parsed?.rawText || '').toString().trim();
            if (!rawText && !message) {
                leaderScanInlineDebug.style.display = 'none';
                leaderScanInlineMessage.textContent = '';
                leaderScanInlineRawText.textContent = '';
                return;
            }

            leaderScanInlineMessage.textContent = message || 'Teks berhasil dibaca, tetapi field form belum cocok dengan parser saat ini.';
            leaderScanInlineRawText.textContent = rawText || '-';
            leaderScanInlineDebug.style.display = 'block';
        }

        function resetLeaderScanDebug() {
            if (leaderScanInlineDebug) {
                leaderScanInlineDebug.style.display = 'none';
            }
            if (leaderScanInlineMessage) {
                leaderScanInlineMessage.textContent = '';
            }
            if (leaderScanInlineRawText) {
                leaderScanInlineRawText.textContent = '';
            }
            if (leaderScanParsed) {
                leaderScanParsed.textContent = '';
            }
            if (leaderScanRawText) {
                leaderScanRawText.textContent = '';
            }
            if (leaderScanDebug) {
                leaderScanDebug.open = false;
                leaderScanDebug.hidden = true;
            }
        }

        function setDateGroupValue(displayField, hiddenFieldId, isoValue) {
            const normalizedValue = (isoValue || '').toString().trim();
            const hiddenField = hiddenFieldId ? document.getElementById(hiddenFieldId) : null;
            if (hiddenField) {
                hiddenField.value = normalizedValue;
            }

            if (displayField) {
                displayField.value = formatDisplayDate(normalizedValue);

                const dateGroup = typeof displayField.closest === 'function'
                    ? displayField.closest('[data-date-field]')
                    : null;
                const nativeField = dateGroup ? dateGroup.querySelector('[data-date-native]') : null;
                if (nativeField) {
                    nativeField.value = normalizedValue;
                }
            }
        }

        function loadExternalScriptOnce(src) {
            return new Promise(function(resolve, reject) {
                const existingScript = Array.from(document.scripts || []).find(function(script) {
                    return (script.src || '') === src;
                });

                if (existingScript) {
                    if (typeof window.Tesseract !== 'undefined') {
                        resolve(window.Tesseract);
                        return;
                    }

                    existingScript.addEventListener('load', function() {
                        resolve(window.Tesseract);
                    }, {
                        once: true
                    });
                    existingScript.addEventListener('error', function() {
                        reject(new Error('Failed to load OCR library from ' + src));
                    }, {
                        once: true
                    });
                    return;
                }

                const script = document.createElement('script');
                script.src = src;
                script.async = true;
                script.onload = function() {
                    resolve(window.Tesseract);
                };
                script.onerror = function() {
                    script.remove();
                    reject(new Error('Failed to load OCR library from ' + src));
                };
                document.head.appendChild(script);
            });
        }

        let tesseractLoadPromise = null;
        function ensureTesseractLoaded() {
            if (typeof window.Tesseract !== 'undefined') {
                return Promise.resolve(window.Tesseract);
            }

            if (tesseractLoadPromise) {
                return tesseractLoadPromise;
            }

            tesseractLoadPromise = (async function() {
                const sources = [
                    '{{ asset('vendor/tesseract/tesseract.min.js') }}',
                ];

                for (const source of sources) {
                    try {
                        await loadExternalScriptOnce(source);
                        if (typeof window.Tesseract !== 'undefined') {
                            return window.Tesseract;
                        }
                    } catch (error) {
                        // Try the next source if the current CDN is unavailable.
                    }
                }

                tesseractLoadPromise = null;
                throw new Error('OCR library is not available.');
            })();

            return tesseractLoadPromise;
        }

        function isoDateFromLooseText(value) {
            const normalized = (value || '').toString().trim()
                .replace(/[./\s]+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
            const match = normalized.match(/(\d{2})-(\d{2})-(\d{4})/);
            if (!match) {
                return '';
            }

            let day = match[1];
            const month = match[2];
            let year = match[3];
            const dayNumber = parseInt(day, 10);
            const currentYear = new Date().getFullYear();

            if (dayNumber > 31 && day.length === 2) {
                const candidateDay = day.replace(/^7/, '2').replace(/^9/, '2');
                if ((parseInt(candidateDay, 10) || 0) <= 31) {
                    day = candidateDay;
                }
            }

            const yearNumber = parseInt(year, 10);
            if (yearNumber && yearNumber < 1900) {
                const shortYear = yearNumber % 100;
                const candidateYears = [1900 + shortYear, 2000 + shortYear];
                const adjustedYear = candidateYears.find(function(candidate) {
                    const age = currentYear - candidate;
                    return candidate <= currentYear && age >= 17 && age <= 100;
                }) || candidateYears.find(function(candidate) {
                    const age = currentYear - candidate;
                    return candidate <= currentYear && age >= 0 && age <= 120;
                });

                if (adjustedYear) {
                    year = String(adjustedYear);
                }
            }

            return [year, month, day].join('-');
        }

        function normalizeKtpLine(value) {
            return (value || '')
                .toString()
                .replace(/[|]/g, 'I')
                .replace(/[—–]/g, '-')
                .replace(/[\u00A9\u00AE]/g, ' ')
                .replace(/[=_~`]/g, ' ')
                .replace(/[;]+/g, ':')
                .replace(/\uFB01/g, 'fi')
                .replace(/\bNAME\b/gi, 'NAMA')
                .replace(/\bNAMU\b/gi, 'NAMA')
                .replace(/\bTEMPEL\b/gi, 'TEMPAT')
                .replace(/\bTEMPAU?TG\b/gi, 'TEMPAT TGL')
                .replace(/\bLANG\b/gi, 'LAHIR')
                .replace(/\bALARA\b/gi, 'ALAMAT')
                .replace(/\bKEOWN\b/gi, 'KELDESA')
                .replace(/\bKECAMA\b/gi, 'KECAMATAN')
                .replace(/\bKOOANAT[MN]\b/gi, 'KECAMATAN')
                .replace(/\bGAMER\b/gi, 'GAMBIR')
                .replace(/\bAGA\s*MA\b/gi, 'AGAMA')
                .replace(/\bPEKARJAAN\b/gi, 'PEKERJAAN')
                .replace(/\bPEKERJA\b/gi, 'PEKERJAAN')
                .replace(/\bPEBERUAN\b/gi, 'PEKERJAAN')
                .replace(/%OVDESA/gi, 'KELDESA')
                .replace(/\bPROVING[!I]?\b/gi, 'PROVINSI')
                .replace(/\bAGARAN\b/gi, 'AGAMA')
                .replace(/\bSLAM\b/gi, 'ISLAM')
                .replace(/\bHAN\s+PETS\b/gi, 'STATUS')
                .replace(/\bTUG\s+LON\b/gi, 'TEMPAT TGL')
                .replace(/\bOKI\s+JAKARTA\b/gi, 'DKI JAKARTA')
                .replace(/\s+/g, ' ')
                .trim();
        }

        function cleanupScannedValue(value, fieldType = 'generic') {
            let cleaned = normalizeKtpLine(value).toUpperCase();
            cleaned = cleaned.replace(/^[^A-Z0-9]+/, '').replace(/[^A-Z0-9./,\-\s]+$/g, '');
            cleaned = cleaned.replace(/\s+([:.,\/-])/g, '$1').replace(/([:.,\/-])\s+/g, '$1 ');
            cleaned = cleaned.replace(/\s{2,}/g, ' ').trim();

            if (fieldType === 'name') {
                cleaned = cleaned
                    .replace(/^NAM[A-Z]*[:\-\s]*/i, '')
                    .replace(/\bAPTS?\s+YANI?\b/g, 'APRIYANI')
                    .replace(/\bAPTS?\s+YAN\b/g, 'APRIYANI')
                    .replace(/\b5\s*KOM\b/g, 'S KOM')
                    .replace(/\bS[.,]?\s*K[.,]?\s*O[.,]?\s*M\b/g, 'S KOM')
                    .replace(/\b([A-Z]+)(?:I|L)S[.,]?\s*K[.,]?\s*O[.,]?\s*M\b/g, '$1I S KOM')
                    .replace(/\s+\d$/, '')
                    .replace(/(?:\s+[:.,\/-])+$/, '')
                    .trim();
            }

            if (fieldType === 'address' || fieldType === 'kelurahan' || fieldType === 'kecamatan' || fieldType === 'city' || fieldType === 'province') {
                cleaned = cleaned
                    .replace(/^(ALAMAT|ADDRESS|KEL\/DESA|KELURAHAN|DESA|KECAMATAN|KAB\/KOTA|KABUPATEN|KOTA|PROVINSI|PROVINCE)[:\-\s]*/i, '')
                    .replace(/\bPROVINSI([A-Z])/g, '$1')
                    .replace(/\bK[PF](?=[A-Z])/g, 'KP ')
                    .replace(/\bOKI\s+JAKARTA\b/g, 'DKI JAKARTA')
                    .replace(/\bJAWABAB[A-Z]*\b/g, 'JAWA BARAT')
                    .replace(/\bN?KUNINGAN\b/g, 'KUNINGAN')
                    .replace(/\bOUR[SI]\b/g, 'DURI')
                    .replace(/\bDURS\b/g, 'DURI')
                    .replace(/\bPALO\b/g, 'PULO')
                    .replace(/\bGAMAIR\b/g, 'GAMBIR')
                    .replace(/\bGAMER\b/g, 'GAMBIR')
                    .replace(/([A-Z]{3,})(BARAT|TIMUR|UTARA|SELATAN|TENGAH|PUSAT)\b/g, '$1 $2')
                    .replace(/([A-Z]{3,})(PULO|PULAU|HILIR|HULU|LOR|KIDUL)\b/g, '$1 $2')
                    .replace(/([A-Z]{2,})NO\.?\s*(\d+)/g, '$1 NO $2')
                    .replace(/\bN[O0]\.?(?=\d)/g, 'NO ')
                    .replace(/\s+[A-Z]{1,2}\s*\d+$/, '')
                    .replace(/\s*-\s*\d+$/, '')
                    .replace(/\s*-\s*$/, '')
                    .replace(/([A-Z])(\d+)/g, '$1 $2')
                    .replace(/(?:\s+[:.,\/-])+$/, '')
                    .trim();
            }

            if (fieldType === 'religion') {
                cleaned = cleaned
                    .replace(/^AGAMA[:\-\s]*/i, '')
                    .replace(/\bLAM\b/g, 'ISLAM')
                    .replace(/\bSLAM\b/g, 'ISLAM')
                    .replace(/\bI5LAM\b/g, 'ISLAM')
                    .trim();
            }

            if (fieldType === 'occupation') {
                cleaned = cleaned
                    .replace(/^PEKERJ[A-Z]*[:\-\s]*/i, '')
                    .replace(/^PEK[A-Z]*[:\-\s]*/i, '')
                    .replace(/\bMENGURAS\b/g, 'MENGURUS')
                    .replace(/\bJAKARTA\s+PUSAT\b$/, '')
                    .replace(/\bKUNINGAN\b$/, '')
                    .trim();

                if (/\bRUMAH\s+TANGGA\b/.test(cleaned)) {
                    cleaned = 'MENGURUS RUMAH TANGGA';
                }
            }

            if (fieldType === 'placeOfBirth') {
                cleaned = cleaned
                    .replace(/\bTEMPAT\b/g, '')
                    .replace(/\bTGL\b/g, '')
                    .replace(/\bLAHIR\b/g, '')
                    .replace(/\bTAL\s+ATER\b/g, '')
                    .replace(/\.$/, '')
                    .replace(/\s{2,}/g, ' ')
                    .trim();

                if (/\bJAKARTA\b/.test(cleaned)) {
                    cleaned = 'JAKARTA';
                }
            }

            return cleaned;
        }

        function normalizeProvinceFromText(value, city = '') {
            const cleaned = cleanupScannedValue(value, 'province');
            if (cleaned) {
                if (/DKI\s*JAKARTA|JAKARTA/i.test(cleaned)) {
                    return 'DKI JAKARTA';
                }
                if (/JAWA.*BAR/i.test(cleaned)) {
                    return 'JAWA BARAT';
                }
                if (/JAWA.*TEN/i.test(cleaned)) {
                    return 'JAWA TENGAH';
                }
                return cleaned;
            }

            const cityText = cleanupScannedValue(city, 'city');
            if (/JAKARTA/i.test(cityText)) {
                return 'DKI JAKARTA';
            }
            if (/KUNINGAN/i.test(cityText)) {
                return 'JAWA BARAT';
            }

            return '';
        }

        function normalizeCityFromText(value) {
            const cleaned = cleanupScannedValue(value, 'city');
            if (!cleaned) {
                return '';
            }

            const jakartaMatch = cleaned.match(/JAKARTA\s+(PUSAT|BARAT|TIMUR|UTARA|SELATAN)/i);
            if (jakartaMatch) {
                return jakartaMatch[0].toUpperCase();
            }
            if (/KUNINGAN/i.test(cleaned)) {
                return 'KUNINGAN';
            }

            return cleaned;
        }

        function inferProvinceFromCity(city) {
            const cleanedCity = cleanupScannedValue(city, 'city');
            if (/JAKARTA/i.test(cleanedCity)) {
                return 'DKI JAKARTA';
            }
            if (/KUNINGAN/i.test(cleanedCity)) {
                return 'JAWA BARAT';
            }

            return '';
        }

        function inferKecamatanFromKelurahan(kelurahan, city = '') {
            const kelurahanKey = cleanupScannedValue(kelurahan, 'kelurahan').replace(/[^A-Z]/g, '');
            const cityKey = cleanupScannedValue(city, 'city').replace(/[^A-Z]/g, '');
            const aliasMap = {
                DURIPULO: 'GAMBIR',
                DURIPALO: 'GAMBIR',
                DUREPULO: 'GAMBIR',
                DUREPULD: 'GAMBIR',
                DURIPULD: 'GAMBIR',
            };

            if (cityKey === 'JAKARTAPUSAT' && aliasMap[kelurahanKey]) {
                return aliasMap[kelurahanKey];
            }

            return aliasMap[kelurahanKey] || '';
        }

        function extractBirthData(lines) {
            for (let index = 0; index < lines.length; index += 1) {
                const line = lines[index];
                if (!/LAHIR|TEMP|TGL|TG\s|TG\/|TGL\/|LANG/i.test(line)) {
                    continue;
                }

                const dateMatch = line.match(/(\d{2}[.\-\/\s]\d{2}[.\-\/\s]\d{4})/);
                if (dateMatch) {
                    const placeRaw = line.slice(0, dateMatch.index || 0)
                        .replace(/^TEMP[A-Z]*[:\-\s]*/i, '')
                        .replace(/^T[G6L][A-Z]*[:\-\s]*/i, '')
                        .replace(/^LANG[:\-\s]*/i, '')
                        .replace(/LAHIR/i, '')
                        .replace(/^[:\-\s,]+/, '')
                        .replace(/[:\-\s,]+$/, '')
                        .trim();

                    return {
                        placeOfBirth: cleanupScannedValue(placeRaw, 'placeOfBirth'),
                        birthDate: isoDateFromLooseText(dateMatch[1]),
                    };
                }

                const nextLine = lines[index + 1] || '';
                const nextDateMatch = nextLine.match(/(\d{2}[.\-\/\s]\d{2}[.\-\/\s]\d{4})/);
                if (nextDateMatch) {
                    const placeRaw = line
                        .replace(/^TEMP[A-Z]*[:\-\s]*/i, '')
                        .replace(/^T[G6L][A-Z]*[:\-\s]*/i, '')
                        .replace(/^LANG[:\-\s]*/i, '')
                        .replace(/LAHIR/i, '')
                        .replace(/^[:\-\s,]+/, '')
                        .replace(/[:\-\s,]+$/, '')
                        .trim();

                    return {
                        placeOfBirth: cleanupScannedValue(placeRaw, 'placeOfBirth'),
                        birthDate: isoDateFromLooseText(nextDateMatch[1]),
                    };
                }

                const placeOnlyRaw = line
                    .replace(/^TEMP[A-Z]*[:\-\s]*/i, '')
                    .replace(/^T[G6L][A-Z]*[:\-\s]*/i, '')
                    .replace(/^LANG[:\-\s]*/i, '')
                    .replace(/LAHIR/i, '')
                    .replace(/[,:]?\s*\d.*$/, '')
                    .replace(/^[:\-\s,]+/, '')
                    .replace(/[:\-\s,]+$/, '')
                    .trim();

                if (placeOnlyRaw) {
                    return {
                        placeOfBirth: cleanupScannedValue(placeOnlyRaw, 'placeOfBirth'),
                        birthDate: '',
                    };
                }
            }

            return {
                placeOfBirth: '',
                birthDate: '',
            };
        }

        function fallbackKecamatanFromStructure(lines, kelurahan, address) {
            const kelurahanIndex = lines.findIndex(line => /^KEL(?:\/|\s*)DESA\b|^KELURAHAN\b|^DESA\b|^KEL\b/i.test(line));
            const agamaIndex = lines.findIndex(line => /^AGAMA\b/i.test(line));

            if (kelurahanIndex < 0) {
                return '';
            }

            const endIndex = agamaIndex > kelurahanIndex ? agamaIndex : Math.min(kelurahanIndex + 4, lines.length);
            for (let index = kelurahanIndex + 1; index < endIndex; index += 1) {
                const sourceLine = lines[index] || '';
                const candidate = cleanupScannedValue(sourceLine, 'kecamatan');
                if (!candidate) {
                    continue;
                }

                if (/^(AGAMA|STATUS|PEKERJAAN|KEWARGANEGARAAN|RTRW|RT\/RW|KEL|DESA)/i.test(sourceLine)) {
                    continue;
                }

                if (candidate === kelurahan || candidate === address) {
                    continue;
                }

                return candidate;
            }

            return '';
        }

        function extractValueAfterLabel(lines, patterns, fieldType = 'generic') {
            for (let index = 0; index < lines.length; index += 1) {
                const line = lines[index];
                for (const pattern of patterns) {
                    if (!pattern.test(line)) {
                        continue;
                    }

                    const cleaned = cleanupScannedValue(line.replace(pattern, '').replace(/^[:\-\s]+/, '').trim(), fieldType);
                    if (cleaned) {
                        return cleaned;
                    }

                    const nextLine = cleanupScannedValue((lines[index + 1] || '').trim(), fieldType);
                    if (nextLine && !/^[A-Z\s\/.]+:?\s*$/.test(nextLine)) {
                        return nextLine;
                    }
                }
            }

            return '';
        }

        function extractValueNearKeywords(lines, patterns, fieldType = 'generic') {
            for (let index = 0; index < lines.length; index += 1) {
                const line = lines[index];
                for (const pattern of patterns) {
                    const match = line.match(pattern);
                    if (!match) {
                        continue;
                    }

                    const keyword = match[0] || '';
                    const startIndex = typeof match.index === 'number'
                        ? match.index + keyword.length
                        : line.toUpperCase().indexOf(keyword.toUpperCase()) + keyword.length;
                    const rawValue = line.slice(Math.max(startIndex, 0)).replace(/^[:\-\s]+/, '').trim();
                    const cleaned = cleanupScannedValue(rawValue, fieldType);
                    if (cleaned) {
                        return cleaned;
                    }

                    const nextLine = cleanupScannedValue((lines[index + 1] || '').trim(), fieldType);
                    if (nextLine && !/^[A-Z\s\/.]+:?\s*$/.test(nextLine)) {
                        return nextLine;
                    }
                }
            }

            return '';
        }

        function extractReligion(lines) {
            const directValue = extractValueAfterLabel(lines, [/^AGAMA\b/i, /^AGA\s*MA\b/i], 'religion') ||
                extractValueNearKeywords(lines, [/AGAMA\b/i, /AGA\s*MA\b/i], 'religion');
            if (directValue) {
                return directValue;
            }

            const joinedText = lines.join(' ');
            const joinedMatch = joinedText.match(/AGA\s*MA[:\-\s]*([A-Z]+)/i);
            if (joinedMatch) {
                return cleanupScannedValue(joinedMatch[1], 'religion');
            }

            if (/\bLAM\b|\bISLAM\b|\bI5LAM\b/i.test(joinedText)) {
                return 'ISLAM';
            }

            return '';
        }

        function getNikBirthTokens(birthDate) {
            if (!birthDate || !/^\d{4}-\d{2}-\d{2}$/.test(birthDate)) {
                return [];
            }

            const year = birthDate.slice(2, 4);
            const month = birthDate.slice(5, 7);
            const day = birthDate.slice(8, 10);
            const maleDay = String((parseInt(day, 10) || 0) + 40).padStart(2, '0');

            return [maleDay + month + year, day + month + year];
        }

        function inferBirthDataFromNik(idNumber, birthData = {}) {
            const normalizedNik = normalizeNikCandidate(idNumber, 'aggressive');
            if (normalizedNik.length !== 16) {
                return {
                    placeOfBirth: birthData.placeOfBirth || '',
                    birthDate: birthData.birthDate || '',
                };
            }

            let birthDate = birthData.birthDate || '';
            if (!birthDate) {
                const rawDay = parseInt(normalizedNik.slice(6, 8), 10);
                const rawMonth = parseInt(normalizedNik.slice(8, 10), 10);
                const rawYear = parseInt(normalizedNik.slice(10, 12), 10);

                if (rawDay && rawMonth >= 1 && rawMonth <= 12 && !Number.isNaN(rawYear)) {
                    const actualDay = rawDay > 40 ? rawDay - 40 : rawDay;
                    if (actualDay >= 1 && actualDay <= 31) {
                        const currentTwoDigitYear = new Date().getFullYear() % 100;
                        const fullYear = rawYear > currentTwoDigitYear ? 1900 + rawYear : 2000 + rawYear;
                        birthDate = [
                            String(fullYear),
                            String(rawMonth).padStart(2, '0'),
                            String(actualDay).padStart(2, '0'),
                        ].join('-');
                    }
                }
            }

            return {
                placeOfBirth: birthData.placeOfBirth || '',
                birthDate,
            };
        }

        function inferNikRegionPrefix(city, kecamatan, kelurahan, province) {
            const cityKey = cleanupScannedValue(city, 'city').replace(/[^A-Z]/g, '');
            const kecamatanKey = cleanupScannedValue(kecamatan, 'kecamatan').replace(/[^A-Z]/g, '');
            const kelurahanKey = cleanupScannedValue(kelurahan, 'kelurahan').replace(/[^A-Z]/g, '');
            const provinceKey = cleanupScannedValue(province, 'province').replace(/[^A-Z]/g, '');

            if (provinceKey === 'DKIJAKARTA' && cityKey === 'JAKARTAPUSAT' && kecamatanKey === 'GAMBIR') {
                if (kelurahanKey === 'DURIPULO') {
                    return '317101';
                }

                return '317101';
            }

            return '';
        }

        function normalizeNikCandidate(value, mode = 'lossy') {
            let raw = (value || '').toString().toUpperCase().replace(/\s+/g, '');
            raw = raw
                .replace(/[OQD]/g, '0')
                .replace(/[IL]/g, '1')
                .replace(/Z/g, '2')
                .replace(/A/g, '4')
                .replace(/S/g, '5')
                .replace(/[EG]/g, '6')
                .replace(/B/g, '8');

            if (mode === 'aggressive') {
                raw = raw.replace(/[?]/g, '7').replace(/N/g, '0');
            }

            return raw.replace(/[^\d]/g, '');
        }

        function extractNikValue(lines, context = {}) {
            const candidateLines = lines
                .filter(line => /NIK/i.test(line))
                .map(line => line.replace(/^.*NIK/i, '').trim())
                .filter(Boolean);

            const birthTokens = getNikBirthTokens(context.birthDate || '');
            const regionPrefix = inferNikRegionPrefix(context.city, context.kecamatan, context.kelurahan, context.province);

            for (const candidate of candidateLines) {
                const aggressiveDigits = normalizeNikCandidate(candidate, 'aggressive');
                if (aggressiveDigits.length === 16) {
                    return aggressiveDigits;
                }

                const lossyDigits = normalizeNikCandidate(candidate, 'lossy');
                if (lossyDigits.length === 16) {
                    return lossyDigits;
                }

                const allDigitCandidates = [lossyDigits, aggressiveDigits].filter(Boolean);
                for (const digits of allDigitCandidates) {
                    for (const token of birthTokens) {
                        const tokenIndex = digits.indexOf(token);
                        const last4 = digits.slice(-4);
                        if (tokenIndex >= 0 && regionPrefix && last4.length === 4) {
                            return regionPrefix + token + last4;
                        }
                    }
                }
            }

            return '';
        }

        function extractPostalCode(lines) {
            const rawPostalCode = extractValueAfterLabel(lines, [/^KODE\s*POS\b/i, /^POSTAL\s*CODE\b/i, /^POS\b/i]) ||
                extractValueNearKeywords(lines, [/KODE\s*POS\b/i, /POSTAL\s*CODE\b/i, /\bPOS\b/i]);
            const digits = (rawPostalCode || '').replace(/[^\d]/g, '');

            return digits.length >= 5 ? digits.slice(0, 5) : '';
        }

        function parseKtpText(text) {
            const normalizedText = (text || '')
                .replace(/\r/g, '\n')
                .replace(/[|]/g, 'I')
                .replace(/\uFB01/g, 'fi')
                .replace(/[\u00A9\u00AE]/g, ' ')
                .replace(/[=_~`]/g, ' ');
            const lines = normalizedText
                .split('\n')
                .map(line => normalizeKtpLine(line))
                .filter(Boolean);
            const digitsOnly = normalizedText.replace(/[^\d]/g, ' ');
            const nikMatch = digitsOnly.match(/\b\d{16}\b/);
            const nikLineIndex = lines.findIndex(line => /NIK/i.test(line));
            const provinceLineIndex = lines.findIndex(line => /PROVINSI/i.test(line));
            let province = provinceLineIndex >= 0 ? lines[provinceLineIndex].replace(/.*PROVINSI/i, '').replace(/^[:\-\s]+/, '').trim() : '';
            let city = '';
            if (provinceLineIndex >= 0 && lines[provinceLineIndex + 1] && !/NIK/i.test(lines[provinceLineIndex + 1])) {
                city = lines[provinceLineIndex + 1].replace(/^[:\-\s]+/, '').trim();
            }
            if ((!province || !city) && nikLineIndex > 0) {
                const headerLines = lines.slice(0, nikLineIndex).filter(line => !/NIK/i.test(line));
                if (!province && headerLines[0]) {
                    province = headerLines[0];
                }
                if (!city && headerLines[1]) {
                    city = headerLines[1];
                }
            }
            if (!city) {
                city = lines.find(line => /JAKARTA\s+(PUSAT|BARAT|TIMUR|UTARA|SELATAN)|\bKAB(?:UPATEN)?\b|\bKOTA\b/i.test(line) && !/PROVINSI|NIK/i.test(line)) || '';
            }
            city = normalizeCityFromText(city);
            province = cleanupScannedValue(province, 'province');

            const birthData = extractBirthData(lines);
            const name = extractValueAfterLabel(lines, [/^NAM[A-Z]*\b/i], 'name') ||
                extractValueNearKeywords(lines, [/NAM[A-Z]*\b/i], 'name');
            const address = extractValueAfterLabel(lines, [/^ALAMAT\b/i], 'address') ||
                extractValueNearKeywords(lines, [/ALAMAT\b/i], 'address');
            const kelurahan = extractValueAfterLabel(lines, [/^KEL(?:\/|\s*)DESA\b/i, /^KELURAHAN\b/i, /^DESA\b/i, /^KEL\b/i], 'kelurahan') ||
                extractValueNearKeywords(lines, [/KEL(?:\/|\s*)DESA\b/i, /KELDESA\b/i, /KELURAHAN\b/i, /DESA\b/i, /\bKEL\b/i], 'kelurahan');
            const kecamatan = extractValueAfterLabel(lines, [/^KECAMATAN\b/i, /^KEC[A-Z]*\b/i, /^KECAMA[A-Z]*\b/i], 'kecamatan') ||
                extractValueNearKeywords(lines, [/KECAMATAN\b/i, /\bKEC[A-Z]*\b/i, /KECAMA[A-Z]*\b/i], 'kecamatan') ||
                fallbackKecamatanFromStructure(lines, kelurahan, address);
            const religion = extractReligion(lines);
            const occupation = extractValueAfterLabel(lines, [/^PEKERJ[A-Z]*\b/i], 'occupation') ||
                extractValueNearKeywords(lines, [/PEKERJ[A-Z]*\b/i], 'occupation');
            const postalCode = extractPostalCode(lines);
            const idNumber = nikMatch ? nikMatch[0] : extractNikValue(lines, {
                birthDate: birthData.birthDate,
                city,
                kecamatan,
                kelurahan,
                province,
            });
            const resolvedBirthData = inferBirthDataFromNik(idNumber, birthData);

            return {
                idNumber,
                name,
                placeOfBirth: resolvedBirthData.placeOfBirth,
                birthDate: resolvedBirthData.birthDate,
                address,
                kelurahan,
                kecamatan,
                city,
                province,
                religion,
                occupation,
                postalCode,
                nationality: 'INA',
                rawText: normalizedText,
            };
        }

        async function loadImageElementFromFile(file) {
            const objectUrl = URL.createObjectURL(file);

            try {
                const image = await new Promise(function(resolve, reject) {
                    const img = new Image();
                    img.onload = function() {
                        resolve(img);
                    };
                    img.onerror = function() {
                        reject(new Error('Unable to load the selected KTP image.'));
                    };
                    img.src = objectUrl;
                });

                return image;
            } finally {
                URL.revokeObjectURL(objectUrl);
            }
        }

        function createProcessedKtpCanvas(image, options = {}) {
            const scale = options.scale || 2;
            const width = Math.max(1, Math.round((image.naturalWidth || image.width || 1) * scale));
            const height = Math.max(1, Math.round((image.naturalHeight || image.height || 1) * scale));
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;

            const context = canvas.getContext('2d', {
                willReadFrequently: true
            });

            if (!context) {
                return canvas;
            }

            context.filter = options.filter || 'none';
            context.drawImage(image, 0, 0, width, height);
            context.filter = 'none';

            if (options.mode === 'threshold' || options.mode === 'grayscale') {
                const imageData = context.getImageData(0, 0, width, height);
                const pixels = imageData.data;
                let minLuma = 255;
                let maxLuma = 0;

                for (let index = 0; index < pixels.length; index += 4) {
                    const luma = (pixels[index] * 0.299) + (pixels[index + 1] * 0.587) + (pixels[index + 2] * 0.114);
                    minLuma = Math.min(minLuma, luma);
                    maxLuma = Math.max(maxLuma, luma);
                }

                const range = Math.max(1, maxLuma - minLuma);
                const threshold = options.threshold || 165;

                for (let index = 0; index < pixels.length; index += 4) {
                    const luma = (pixels[index] * 0.299) + (pixels[index + 1] * 0.587) + (pixels[index + 2] * 0.114);
                    const normalized = ((luma - minLuma) / range) * 255;
                    const value = options.mode === 'threshold'
                        ? (normalized > threshold ? 255 : 0)
                        : normalized;

                    pixels[index] = value;
                    pixels[index + 1] = value;
                    pixels[index + 2] = value;
                }

                context.putImageData(imageData, 0, 0);
            }

            return canvas;
        }

        async function buildKtpOcrVariants(file) {
            const image = await loadImageElementFromFile(file);
            const variants = [{
                label: 'original',
                source: file,
            }];

            const enhancedCanvas = createProcessedKtpCanvas(image, {
                scale: 2.4,
                filter: 'grayscale(1) contrast(185%) brightness(120%) saturate(0)',
                mode: 'grayscale',
            });
            variants.push({
                label: 'enhanced',
                source: enhancedCanvas,
            });

            const thresholdCanvas = createProcessedKtpCanvas(image, {
                scale: 2.8,
                filter: 'grayscale(1) contrast(220%) brightness(130%) saturate(0)',
                mode: 'threshold',
                threshold: 155,
            });
            variants.push({
                label: 'threshold',
                source: thresholdCanvas,
            });

            return variants;
        }

        function scoreOcrText(text) {
            const content = (text || '').toString().toUpperCase();
            if (!content.trim()) {
                return 0;
            }

            let score = 0;
            const keywordMatches = [
                /\bNIK\b/,
                /\bNAMA\b/,
                /\bALAMAT\b/,
                /\bKECAMATAN\b/,
                /\bKEL(?:\/|\s*)DESA\b/,
                /\bTEMPAT\b/,
                /\bLAHIR\b/,
                /\bPROVINSI\b/,
                /\bJAKARTA\b/,
            ].reduce(function(total, pattern) {
                return total + (pattern.test(content) ? 1 : 0);
            }, 0);

            score += keywordMatches * 30;

            if (/\b\d{16}\b/.test(content.replace(/[^\d]/g, ' '))) {
                score += 120;
            }

            score += Math.min(120, content.replace(/[^A-Z]/g, '').length);
            score += Math.min(60, content.split(/\s+/).filter(Boolean).length * 2);
            score -= (content.match(/[^\w\s:/.-]/g) || []).length * 2;

            return score;
        }

        async function runServerKtpScan(file, onProgress) {
            if (!file) {
                throw new Error('No KTP image selected.');
            }

            if (typeof onProgress === 'function') {
                onProgress('Sending KTP to AI OCR...');
            }

            const formData = new FormData();
            formData.append('image', file);

            const response = await fetch('/checkin/scan-ktp', {
                method: 'POST',
                headers: Object.assign({
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }, csrfToken ? {
                    'X-CSRF-TOKEN': csrfToken,
                } : {}),
                body: formData,
            });

            let payload = null;
            try {
                payload = await response.json();
            } catch (error) {
                payload = null;
            }

            if (!response.ok || !payload?.success || !payload?.data?.parsed) {
                const error = new Error(payload?.message || 'AI OCR request failed.');
                error.status = response.status;
                throw error;
            }

            if (typeof onProgress === 'function') {
                onProgress('AI OCR completed. Filling extracted fields...');
            }

            return Object.assign({}, payload.data.parsed, {
                provider: payload?.data?.provider || 'backend_ocr',
                correctionProvider: payload?.data?.correction_provider || '',
            });
        }

        async function runKtpScan(file, onProgress) {
            try {
                return await runServerKtpScan(file, onProgress);
            } catch (serverError) {
                const message = (serverError?.message || '').toString();
                const shouldFallbackToLocal = /belum dikonfigurasi|returned empty text|paddleocr|ocr modern backend belum berhasil membaca ktp/i.test(message)
                    || (Number(serverError?.status || 0) >= 500 && !/quota|billing|api key|401|403/i.test(message));
                if (!shouldFallbackToLocal) {
                    throw serverError;
                }

                console.warn('Backend OCR is unavailable or returned empty text, switching to local OCR fallback.', serverError);
                if (typeof onProgress === 'function') {
                    onProgress('Backend OCR belum menghasilkan teks. Switching to local OCR...');
                }
            }

            const TesseractLib = await ensureTesseractLoaded();
            const variants = await buildKtpOcrVariants(file);
            let bestText = '';
            let bestScore = -1;

            for (let index = 0; index < variants.length; index += 1) {
                const variant = variants[index];
                const variantLabel = variant.label === 'original'
                    ? 'original'
                    : (variant.label === 'enhanced' ? 'enhanced' : 'high contrast');

                const result = await TesseractLib.recognize(variant.source, 'ind+eng', {
                    workerPath: '{{ asset('vendor/tesseract/worker.min.js') }}',
                    corePath: '{{ asset('vendor/tesseract/core/tesseract-core-lstm.wasm.js') }}',
                    langPath: '{{ asset('vendor/tesseract/lang-data') }}',
                    logger(message) {
                        if (typeof onProgress !== 'function') {
                            return;
                        }

                        if (message.status === 'recognizing text') {
                            onProgress('Reading KTP (' + variantLabel + ') ' + Math.round((message.progress || 0) * 100) + '%...');
                        } else if (message.status) {
                            onProgress(message.status + ' (' + variantLabel + ')...');
                        }
                    }
                });

                const text = result?.data?.text || '';
                const score = scoreOcrText(text);
                if (score > bestScore) {
                    bestScore = score;
                    bestText = text;
                }

                if (bestScore >= 220) {
                    break;
                }
            }

            return Object.assign({}, parseKtpText(bestText), {
                provider: 'tesseract_fallback',
                correctionProvider: '',
            });
        }

        function clearLeaderKtpScanFields() {
            xGuest.value = '';
            xKTP.value = '';
            xPlaceBirth.value = '';
            setDateGroupValue(xTglLahir, 'BirthDate', '');
            xAlamat.value = '';
            xKelurahan.value = '';
            xKecamatan.value = '';
            xKota.value = '';
            if (xProfesi) {
                xProfesi.value = '';
            }
            if (xPropinsi) {
                xPropinsi.value = '';
            }
            setProvinceCountrySource('manual');
            const postalCodeField = document.getElementById('PostalCode');
            if (postalCodeField) {
                postalCodeField.value = '';
            }
        }

        function applyKtpDataToLeader(data) {
            clearLeaderKtpScanFields();
            const resolvedKecamatan = data.kecamatan || inferKecamatanFromKelurahan(data.kelurahan, data.city);
            const resolvedProvince = (data.province || '').toString().trim();
            if (data.name) {
                xGuest.value = data.name;
            }
            if (data.idNumber) {
                xKTP.value = data.idNumber;
            }
            if (xTypeId) {
                xTypeId.value = 'KTP';
            }
            if (data.placeOfBirth) {
                xPlaceBirth.value = data.placeOfBirth;
            }
            if (data.birthDate) {
                setDateGroupValue(xTglLahir, 'BirthDate', data.birthDate);
            }
            if (data.address) {
                xAlamat.value = data.address;
            }
            if (data.kelurahan) {
                xKelurahan.value = data.kelurahan;
            }
            if (resolvedKecamatan) {
                xKecamatan.value = resolvedKecamatan;
            }
            if (data.city) {
                xKota.value = data.city;
            }
            if (resolvedProvince) {
                setSelectValueDirect(xPropinsi, resolvedProvince);
                setProvinceCountrySource('scan');
            }
            if (data.religion) {
                setSelectByLooseText(xAgama, data.religion);
            }
            if (data.nationality) {
                setSelectByLooseText(xKodeNegara, data.nationality);
            }
            if (data.occupation && xProfesi) {
                xProfesi.value = data.occupation;
            }
            const postalCodeField = document.getElementById('PostalCode');
            if (postalCodeField && data.postalCode) {
                postalCodeField.value = data.postalCode;
            }
            updatePrimaryGuestDisplay();
        }

        function updatePrimaryGuestDisplay() {
            primaryRoomGuestName.textContent = (xGuest?.value || '').trim() || '-';
            if (leaderSummaryName) {
                leaderSummaryName.textContent = (xGuest?.value || '').trim() || '-';
            }
            if (leaderSummaryPhone) {
                leaderSummaryPhone.textContent = (xPhone?.value || '').trim() || '-';
            }
            if (primaryRoomBadge) {
                const positionLabel = (primaryRoomPositionField?.value || '').trim().toUpperCase();
                primaryRoomBadge.textContent = positionLabel || (isGroupCheckInType(xTipe?.value) ? 'PRIMARY' : 'PRIMARY');
            }
            updateBillingTypeDisplay();
            Array.from(additionalRoomBody.querySelectorAll('[data-room-row]')).forEach(function(row) {
                updateRoomGuestRowState(row, true);
            });
        }

        function updateRoomGuestRowState(row, keepCurrentVisibility = false) {
            if (!row) {
                return;
            }

            const detailRow = row.nextElementSibling && row.nextElementSibling.matches('[data-room-detail-row]') ? row.nextElementSibling : null;
            const sameCheckbox = row.querySelector('.room-same-checkbox');
            const sameHidden = row.querySelector('.same-as-leader-input');
            const summaryInput = row.querySelector('.room-guest-display-input');
            const guestNameInput = detailRow?.querySelector('.room-guest-name-input');
            const scanStatus = detailRow?.querySelector('.room-guest-scan-status');
            const followsLeader = !!sameCheckbox?.checked;

            if (sameHidden) {
                sameHidden.value = followsLeader ? '1' : '0';
            }

            if (summaryInput) {
                summaryInput.value = followsLeader
                    ? ((xGuest?.value || '').trim() || 'Following leader data')
                    : ((guestNameInput?.value || '').trim() || 'Guest detail required');
            }

            if (scanStatus) {
                scanStatus.textContent = followsLeader ? 'Following leader data' : 'Guest detail is active';
            }

            if (detailRow) {
                if (followsLeader) {
                    detailRow.classList.add('is-collapsed');
                } else if (!keepCurrentVisibility || detailRow.classList.contains('is-collapsed')) {
                    detailRow.classList.remove('is-collapsed');
                }
            }
        }

        function applyKtpDataToRoomRow(row, data) {
            const detailRow = row?.nextElementSibling;
            if (!row || !detailRow || !detailRow.matches('[data-room-detail-row]')) {
                return;
            }

            const sameCheckbox = row.querySelector('.room-same-checkbox');
            if (sameCheckbox) {
                sameCheckbox.checked = false;
            }

            const fieldMap = {
                '.room-guest-name-input': data.name || '',
                '.room-guest-id-number-input': data.idNumber || '',
                '.room-guest-phone-input': data.phone || '',
                '.room-guest-email-input': data.email || '',
                '.room-guest-address-input': data.address || '',
            };

            Object.entries(fieldMap).forEach(function([selector, value]) {
                const field = detailRow.querySelector(selector);
                if (field && value) {
                    field.value = value;
                }
            });

            const idTypeField = detailRow.querySelector('.room-guest-id-type-input');
            if (idTypeField) {
                idTypeField.value = 'KTP';
            }

            const birthDateField = detailRow.querySelector('.room-guest-birth-date-input');
            if (birthDateField && data.birthDate) {
                birthDateField.value = data.birthDate;
            }

            const nationalityField = detailRow.querySelector('.room-guest-nationality-input');
            if (nationalityField && data.nationality) {
                setSelectByLooseText(nationalityField, data.nationality);
            }

            updateRoomGuestRowState(row);
        }

        function applyReservationNumberSelection() {
            if (!xResNo) {
                return;
            }

            const option = xResNo.options[xResNo.selectedIndex];
            if (!option || !option.value) {
                return;
            }

            if (xAlamat) {
                xAlamat.value = option.dataset.address || '';
            }

            if (xKodeNegara) {
                xKodeNegara.value = option.dataset.nationality || 'INA';
            }

            if (xPhone) {
                xPhone.value = option.dataset.phone || '';
            }

            if (xRemark) {
                xRemark.value = option.dataset.remarks || '';
            }
        }

        function updateRoomSummary() {
            const primaryRooms = roomCodeField.value.trim() ? 1 : 0;
            const extraRows = Array.from(additionalRoomBody.querySelectorAll('[data-room-row]'));
            const extraRooms = extraRows.filter(row => {
                const input = row.querySelector('.room-code-input');
                return input && input.value.trim() !== '';
            }).length;
            const primaryPax = parseInt(primaryRoomPaxDisplay.value || '0', 10) || 0;
            const extraPax = extraRows.reduce((sum, row) => {
                const input = row.querySelector('.room-pax-display-input');
                return sum + (parseInt(input?.value || '0', 10) || 0);
            }, 0);

            roomSummaryCount.textContent = String(primaryRooms + extraRooms);
            roomSummaryPax.textContent = String(primaryPax + extraPax);
        }

        function bindDateGroup(group) {
            const hidden = group.querySelector('input[type="hidden"]');
            const display = group.querySelector('input[type="text"]');
            const native = group.querySelector('[data-date-native]');
            const button = group.querySelector('[data-date-button]');
            if (!hidden || !display || !native) {
                return;
            }
            display.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    hidden.value = '';
                    native.value = '';
                    return;
                }
                const iso = normalizeDisplayDate(this.value);
                if (!iso) {
                    showCrudAlert('Tanggal harus memakai format dd-MM-yyyy.');
                    this.focus();
                    return;
                }
                hidden.value = iso;
                native.value = iso;
                this.value = formatDisplayDate(iso);
            });
            native.addEventListener('change', function() {
                hidden.value = this.value || '';
                display.value = formatDisplayDate(this.value);
            });
            if (button) {
                button.addEventListener('click', function() {
                    if (typeof native.showPicker === 'function') {
                        native.showPicker();
                    } else {
                        native.focus();
                        native.click();
                    }
                });
            }
            display.value = formatDisplayDate(hidden.value);
            native.value = hidden.value;
        }

        function updateRoomHelper() {
            const option = findOptionByValue('roomCodeOptions', roomCodeField.value);
            if (!option) {
                roomHelper.querySelector('strong').textContent = 'Select an available room';
                updateRoomSummary();
                return;
            }
            const kelas = option.dataset.kelas || '-';
            const status = option.dataset.status || '-';
            const available = option.dataset.available === '1';
            roomHelper.querySelector('strong').textContent = roomCodeField.value.toUpperCase() + ' | ' + kelas + ' | ' +
                status + (available ? '' : ' | tidak tersedia');
            updateRoomSummary();
        }

        function updatePackageHelper() {
            const option = findOptionByValue('packageCodeOptions', packageCodeField.value);
            if (!option) {
                packageHelper.querySelector('strong').textContent = 'Nominal will be filled automatically when a package is found';
                nominalField.value = '';
                nominalVisibleField.value = '';
                return;
            }
            const nominal = Number(option.dataset.nominal || 0);
            const expired = option.dataset.expired || '-';
            nominalField.value = normalizeNumber(String(nominal));
            nominalVisibleField.value = formatRibuan(String(nominal));
            packageHelper.querySelector('strong').textContent = (option.dataset.name || 'Package') + ' | Exp ' + expired +
                ' | Rp ' + formatRibuan(String(nominal));
        }

        function renumberAdditionalRows() {
            updateRoomSummary();
        }

        function bindAdditionalRow(row) {
            const roomInput = row.querySelector('.room-code-input');
            const packageInput = row.querySelector('.package-code-input');
            const nominalInput = row.querySelector('.nominal-input');
            const nominalDisplayInput = row.querySelector('.nominal-display-input');
            const breakfastInput = row.querySelector('.breakfast-input');
            const paxInput = row.querySelector('.room-pax-display-input');
            const positionInput = row.querySelector('.room-position-input');
            const sameCheckbox = row.querySelector('.room-same-checkbox');
            const expandButton = row.querySelector('.room-row-expand');
            const removeButton = row.querySelector('.room-row-remove');
            const detailRow = row.nextElementSibling && row.nextElementSibling.matches('[data-room-detail-row]') ? row.nextElementSibling : null;
            const guestNameInput = detailRow?.querySelector('.room-guest-name-input');
            const scanButton = detailRow?.querySelector('.room-guest-scan-btn');
            const scanInput = detailRow?.querySelector('.room-guest-ktp-input');
            const scanStatus = detailRow?.querySelector('.room-guest-scan-status');
            if (packageInput) {
                packageInput.addEventListener('change', function() {
                    const option = findOptionByValue('packageCodeOptions', this.value);
                    nominalInput.value = option ? normalizeNumber(String(option.dataset.nominal || '0')) : '';
                    nominalDisplayInput.value = option ? formatRibuan(String(option.dataset.nominal || '0')) : '';
                });
            }
            if (roomInput) {
                roomInput.addEventListener('change', function() {
                    updateRoomSummary();
                });
            }
            if (paxInput) {
                paxInput.addEventListener('input', function() {
                    this.value = this.value ? String(Math.max(parseInt(this.value, 10) || 1, 1)) : '1';
                    updateRoomSummary();
                });
            }
            if (positionInput) {
                positionInput.addEventListener('change', updatePrimaryGuestDisplay);
            }
            if (breakfastInput) {
                breakfastInput.value = breakfastInput.value || '0';
            }
            if (guestNameInput) {
                guestNameInput.addEventListener('input', function() {
                    updateRoomGuestRowState(row, true);
                });
            }
            if (sameCheckbox) {
                sameCheckbox.addEventListener('change', function() {
                    updateRoomGuestRowState(row);
                });
            }
            if (expandButton) {
                expandButton.addEventListener('click', function() {
                    if (sameCheckbox?.checked) {
                        sameCheckbox.checked = false;
                        updateRoomGuestRowState(row);
                        return;
                    }

                    if (detailRow) {
                        detailRow.classList.toggle('is-collapsed');
                    }
                });
            }
            if (scanButton && scanInput) {
                scanButton.addEventListener('click', function() {
                    if (sameCheckbox?.checked) {
                        sameCheckbox.checked = false;
                        updateRoomGuestRowState(row);
                    }
                    scanInput.click();
                });
                scanInput.addEventListener('change', async function() {
                    const file = this.files && this.files[0];
                    if (!file) {
                        return;
                    }

                    if (scanStatus) {
                        scanStatus.textContent = 'Preparing OCR scan...';
                    }

                    try {
                        const parsed = await runKtpScan(file, function(message) {
                            if (scanStatus) {
                                scanStatus.textContent = message;
                            }
                        });
                        applyKtpDataToRoomRow(row, parsed);
                        if (scanStatus) {
                            scanStatus.textContent = parsed.name
                                ? 'KTP scanned and guest detail has been filled.'
                                : 'KTP scanned. Please review the extracted detail.';
                        }
                    } catch (error) {
                        if (scanStatus) {
                            scanStatus.textContent = 'Unable to read KTP automatically.';
                        }
                        showCrudAlert('OCR KTP untuk guest room belum berhasil dibaca. Coba gunakan foto yang lebih jelas.');
                    } finally {
                        this.value = '';
                    }
                });
            }
            if (removeButton) {
                removeButton.addEventListener('click', function() {
                    if (detailRow) {
                        detailRow.remove();
                    }
                    row.remove();
                    renumberAdditionalRows();
                });
            }
            updateRoomGuestRowState(row, true);
        }

        function addAdditionalRoomRow(detail = {}) {
            const fragment = additionalRoomRowTemplate.content.cloneNode(true);
            const row = fragment.querySelector('[data-room-row]');
            const detailRow = fragment.querySelector('[data-room-detail-row]');
            row.querySelector('.detail-key-input').value = detail.detailKey || '';
            setSelectValueDirect(row.querySelector('.room-code-input'), detail.roomCode || '');
            row.querySelector('.room-pax-display-input').value = detail.pax || detail.breakfast || 1;
            setSelectValueDirect(row.querySelector('.package-code-input'), detail.packageCode || '');
            row.querySelector('.nominal-input').value = detail.nominal ? normalizeNumber(String(detail.nominal)) : '';
            row.querySelector('.nominal-display-input').value = detail.nominal ? formatRibuan(String(detail.nominal)) : '';
            row.querySelector('.breakfast-input').value = detail.breakfast ?? 0;
            setSelectValueDirect(
                row.querySelector('.room-position-input'),
                detail.groupPosition || (isGroupCheckInType(xTipe?.value) ? 'SUB' : '')
            );
            row.querySelector('.same-as-leader-input').value = detail.sameAsLeader === false ? '0' : '1';
            row.querySelector('.room-same-checkbox').checked = detail.sameAsLeader !== false;
            if (detailRow) {
                detailRow.querySelector('.room-guest-name-input').value = detail.guestName || '';
                detailRow.querySelector('.room-guest-id-type-input').value = detail.guestIdType || 'KTP';
                detailRow.querySelector('.room-guest-id-number-input').value = detail.guestIdNumber || '';
                detailRow.querySelector('.room-guest-birth-date-input').value = detail.guestBirthDate || '';
                detailRow.querySelector('.room-guest-phone-input').value = detail.guestPhone || '';
                detailRow.querySelector('.room-guest-email-input').value = detail.guestEmail || '';
                detailRow.querySelector('.room-guest-address-input').value = detail.guestAddress || '';
                detailRow.querySelector('.room-guest-nationality-input').value = detail.guestNationality || 'INA';
                if (detail.sameAsLeader === false) {
                    detailRow.classList.remove('is-collapsed');
                }
            }
            bindAdditionalRow(row);
            additionalRoomBody.appendChild(fragment);
            updateGroupPositionAvailability();
            renumberAdditionalRows();
        }

        function resetAdditionalRoomRows() {
            additionalRoomBody.innerHTML = '';
        }

        function setFormModeCreate(regNo = defaultRegNo) {
            form.action = '/checkin';
            currentDetailKeyField.value = '';
            primaryDetailKeyField.value = '';
            xRegNo.value = regNo || defaultRegNo;
            if (primaryRoomPositionField) {
                primaryRoomPositionField.value = '';
            }
            syncPrimaryRoomPositionValue();
            saveButton.textContent = 'Save Check In';
            if (printRegistrationButton) {
                printRegistrationButton.disabled = true;
            }
            Array.from(document.querySelectorAll('.checkin-record-row')).forEach(row => row.classList.remove('is-active'));
        }

        function applyRecord(record) {
            setFormModeCreate(record.RegNo || defaultRegNo);
            currentDetailKeyField.value = record.DetailKey || '';
            primaryDetailKeyField.value = record.DetailKey || '';
            form.action = '/checkin/' + encodeURIComponent(record.DetailKey) + '/update';
            saveButton.textContent = 'Update Check In';
            if (printRegistrationButton) {
                printRegistrationButton.disabled = !(record.DetailKey || '');
            }
            const mappings = ['ReservationNumber', 'GuestName', 'GuestName2', 'Address', 'Kelurahan', 'Kecamatan',
                'KabCity', 'ProvinceCountry', 'TypeOfId', 'IdNumber', 'TypeOfCheckIn', 'PlaceOfBirth',
                'Religion', 'Nationality', 'NumberOfPerson', 'PaymentMethod', 'Company', 'Occupation', 'CreditCardNumber', 'Segment',
                'Phone', 'Email', 'Remarks', 'Member', 'Sales'
            ];
            mappings.forEach(function(id) {
                const field = document.getElementById(id);
                if (field) {
                    field.value = record[id] ?? '';
                }
            });
            if (primaryRoomPositionField) {
                primaryRoomPositionField.value = record.GroupPosition || '';
            }
            syncPrimaryRoomPositionValue();
            setSelectByValueOrDataName(xPropinsi, record.ProvinceCountry || '', 'name');
            setProvinceCountrySource('manual');
            updateGroupPositionAvailability();
            xPeriksa.checked = String(record.CheckDeposit || '0') === '1';
            document.getElementById('CheckInDate').value = record.CheckInDate || '';
            xTglIn.value = formatDisplayDate(record.CheckInDate || '');
            xTglIn.closest('[data-date-field]').querySelector('[data-date-native]')
                .value = record.CheckInDate || '';
            document.getElementById('BirthDate').value = record.BirthDate || '';
            xTglLahir.value = formatDisplayDate(record.BirthDate || '');
            xTglLahir.closest('[data-date-field]').querySelector('[data-date-native]')
                .value = record.BirthDate || '';
            document.getElementById('ExpiredDate').value = record.ExpiredDate || '';
            xExpired.value = formatDisplayDate(record.ExpiredDate || '');
            xExpired.closest('[data-date-field]').querySelector('[data-date-native]')
                .value = record.ExpiredDate || '';
            document.getElementById('EstimationOut').value = record.EstimationOut || '';
            xTglKeluar.value = formatDisplayDate(record.EstimationOut || '');
            xTglKeluar.closest('[data-date-field]').querySelector('[data-date-native]')
                .value = record.EstimationOut || '';
            xJamIn.value = normalizeTimeDisplay(record.CheckInTime || '');
            setSelectValueDirect(xKode, record.RoomCode || '');
            setSelectValueDirect(xPackage, record.PackageCode || '');
            nominalField.value = normalizeNumber(String(record.Nominal || ''));
            xNominal.value = record.Nominal ? formatRibuan(String(record.Nominal)) : '';
            xBF.value = record.Breakfast || 0;
            primaryRoomPaxDisplay.value = record.NumberOfPerson || record.Breakfast || 1;
            updatePrimaryGuestDisplay();
            resetAdditionalRoomRows();
            if (Array.isArray(record.AdditionalRooms) && record.AdditionalRooms.length) {
                record.AdditionalRooms.forEach(function(detail) {
                    addAdditionalRoomRow(detail);
                });
            }
            updateRoomHelper();
            updatePackageHelper();
            updateRoomSummary();
        }

        function openRegistrationPrint() {
            const detailKey = (currentDetailKeyField?.value || primaryDetailKeyField?.value || '').trim();
            if (!detailKey) {
                showCrudAlert('Pilih data check in yang sudah tersimpan terlebih dahulu untuk print registrasi.');
                return;
            }

            window.open('/checkin/' + encodeURIComponent(detailKey) + '/print-registration', '_blank', 'noopener');
        }

        function resetForm() {
            const preservedRegNo = (xRegNo.value || defaultRegNo).trim().toUpperCase();
            form.reset();
            setFormModeCreate(preservedRegNo);
            resetAdditionalRoomRows();
            document.getElementById('CheckInDate').value = defaultCheckIn;
            xTglIn.value = formatDisplayDate(defaultCheckIn);
            xTglIn.closest('[data-date-field]').querySelector('[data-date-native]')
                .value = defaultCheckIn;
            document.getElementById('EstimationOut').value = defaultCheckOut;
            xTglKeluar.value = formatDisplayDate(defaultCheckOut);
            xTglKeluar.closest('[data-date-field]').querySelector('[data-date-native]')
                .value = defaultCheckOut;
            document.getElementById('BirthDate').value = '';
            xTglLahir.value = '';
            xTglLahir.closest('[data-date-field]').querySelector('[data-date-native]')
                .value = '';
            document.getElementById('ExpiredDate').value = '';
            xExpired.value = '';
            xExpired.closest('[data-date-field]').querySelector('[data-date-native]')
                .value = '';
            roomHelper.querySelector('strong').textContent = 'Select an available room';
            packageHelper.querySelector('strong').textContent = 'Nominal will be filled automatically when a package is found';
            nominalField.value = '';
            xNominal.value = '';
            xPackage.value = '';
            xKode.value = '';
            xBF.value = '2';
            xJamIn.value = defaultCheckInTime;
            xTipe.value = defaultTypeOfCheckIn;
            if (primaryRoomPositionField) {
                primaryRoomPositionField.value = '';
            }
            syncPrimaryRoomPositionValue();
            updateGroupPositionAvailability();
            xTypeId.value = defaultIdType;
            xPayment.value = 'OTA';
            xSegment.value = 'TRAVEL';
            xKodeNegara.value = 'INA';
            xPerson.value = '2';
            setProvinceCountrySource('manual');
            const postalCodeField = document.getElementById('PostalCode');
            if (postalCodeField) {
                postalCodeField.value = '';
            }
            primaryRoomPaxDisplay.value = '2';
            if (leaderScanImage) {
                leaderScanImage.hidden = true;
                leaderScanImage.removeAttribute('src');
            }
            if (leaderScanPreview && !leaderScanPreview.querySelector('.ktp-scan-preview-empty')) {
                leaderScanPreview.insertAdjacentHTML('afterbegin',
                    '<span class="ktp-scan-preview-empty">KTP preview will appear here after upload.</span>');
            }
            if (leaderScanStatus) {
                leaderScanStatus.textContent = 'No KTP scanned yet. Upload an image to start OCR.';
            }
            resetLeaderScanDebug();
            updatePrimaryGuestDisplay();
            updateRoomSummary();
            xGuest.focus();
        }

        Array.from(document.querySelectorAll('[data-date-field]')).forEach(bindDateGroup);
        updateGroupPositionAvailability();
        xGuest.addEventListener('input', updatePrimaryGuestDisplay);
        xPhone?.addEventListener('input', updatePrimaryGuestDisplay);
        xTipe?.addEventListener('change', updateBillingTypeDisplay);
        xPayment?.addEventListener('change', updateBillingTypeDisplay);
        if (leaderScanButton && leaderKtpUpload) {
            leaderScanButton.addEventListener('click', function() {
                leaderKtpUpload.click();
            });

            leaderKtpUpload.addEventListener('change', async function() {
                const file = this.files && this.files[0];
                if (!file) {
                    return;
                }

                resetLeaderScanDebug();
                if (leaderScanStatus) {
                    leaderScanStatus.textContent = 'Preparing OCR scan...';
                }
                const previewUrl = URL.createObjectURL(file);
                if (leaderScanImage) {
                    leaderScanImage.src = previewUrl;
                    leaderScanImage.hidden = false;
                }
                if (leaderScanPreview) {
                    leaderScanPreview.querySelector('.ktp-scan-preview-empty')?.remove();
                }

                try {
                    const parsed = await runKtpScan(file, function(message) {
                        if (leaderScanStatus) {
                            leaderScanStatus.textContent = message;
                        }
                    });
                    renderLeaderScanDebug(parsed);
                    applyKtpDataToLeader(parsed);
                    renderLeaderScanInlineDebug(
                        parsed,
                        hasMeaningfulScanData(parsed)
                            ? ''
                            : 'OCR berhasil dibaca. Teks mentah yang terbaca ditampilkan di bawah supaya bisa saya cocokkan parser-nya.'
                    );
                    if (leaderScanStatus) {
                        leaderScanStatus.textContent = hasMeaningfulScanData(parsed)
                            ? 'KTP scanned successfully. Leader fields have been filled.'
                            : 'OCR berhasil dibaca. Teks mentah hasil scan ditampilkan di bawah.';
                    }
                } catch (error) {
                    if (leaderScanStatus) {
                        leaderScanStatus.textContent = 'Unable to read KTP automatically.';
                    }
                    console.error('Leader KTP OCR failed:', error);
                    renderLeaderScanInlineDebug({
                        rawText: '',
                    }, error?.message || 'Unable to read KTP automatically.');
                    renderLeaderScanDebug({
                        rawText: '',
                    }, error?.message || 'Unable to read KTP automatically.');
                    showCrudAlert('OCR KTP leader belum berhasil dibaca. Coba gunakan foto yang lebih jelas.');
                } finally {
                    URL.revokeObjectURL(previewUrl);
                    this.value = '';
                }
            });
        }
        if (xTipe) {
            xTipe.addEventListener('change', updateGroupPositionAvailability);
        }
        if (primaryRoomPositionField) {
            primaryRoomPositionField.addEventListener('change', function() {
                syncPrimaryRoomPositionValue();
                updatePrimaryGuestDisplay();
            });
        }
        if (xPropinsi) {
            xPropinsi.addEventListener('change', function() {
                const selectedOption = getSelectedOption(xPropinsi);
                setProvinceCountrySource(selectedOption?.dataset?.ocrGenerated === '1' ? 'scan' : 'manual');
            });
        }
        if (xResNo) {
            xResNo.addEventListener('change', applyReservationNumberSelection);
            xResNo.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    applyReservationNumberSelection();
                }
            });
        }
        xJamIn.addEventListener('blur', function() {
            this.value = normalizeTimeDisplay(this.value);
        });
        xJamIn.value = normalizeTimeDisplay(xJamIn.value);
        if (xTypeId) {
            xTypeId.value = xTypeId.value || defaultIdType;
        }
        numberOfPersonField.addEventListener('input', function() {
            primaryRoomPaxDisplay.value = this.value || '1';
            updateRoomSummary();
        });
        primaryRoomPaxDisplay.addEventListener('input', function() {
            this.value = this.value ? String(Math.max(parseInt(this.value, 10) || 1, 1)) : '1';
            numberOfPersonField.value = this.value;
            updateRoomSummary();
        });
        roomCodeField.addEventListener('change', function() {
            updateRoomHelper();
        });
        packageCodeField.addEventListener('change', updatePackageHelper);
        addRoomRowButton.addEventListener('click', function() {
            addAdditionalRoomRow();
        });
        clearPrimaryRoomButton.addEventListener('click', function() {
            primaryDetailKeyField.value = '';
            roomCodeField.value = '';
            packageCodeField.value = '';
            nominalField.value = '';
            nominalVisibleField.value = '';
            breakfastField.value = '0';
            updateRoomHelper();
            updatePackageHelper();
        });
        newEntryButton.addEventListener('click', resetForm);
        if (printRegistrationButton) {
            printRegistrationButton.addEventListener('click', openRegistrationPrint);
            printRegistrationButton.disabled = !(currentDetailKeyField?.value || primaryDetailKeyField?.value || '').trim();
        }
        focusSearchButton.addEventListener('click', function() {
            document.getElementById('checkinDirectoryShell')?.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
            setTimeout(() => document.getElementById('searchKeyword')?.focus(), 250);
        });
        form.addEventListener('submit', function(event) {
            generatedRegNoField.value = generatedRegNoField.value.trim().toUpperCase();
            const dateDisplays = ['CheckInDateDisplay', 'BirthDateDisplay', 'ExpiredDateDisplay',
                'EstimationOutDisplay'
            ];
            for (const id of dateDisplays) {
                const display = document.getElementById(id);
                if (!display) {
                    continue;
                }
                const group = display.closest('[data-date-field]');
                const hidden = group.querySelector('input[type="hidden"]');
                if (display.value.trim() === '') {
                    hidden.value = '';
                    continue;
                }
                const iso = normalizeDisplayDate(display.value);
                if (!iso) {
                    event.preventDefault();
                    showCrudAlert('Tanggal harus memakai format dd-MM-yyyy.');
                    display.focus();
                    return;
                }
                hidden.value = iso;
                group.querySelector('[data-date-native]').value = iso;
                display.value = formatDisplayDate(iso);
            }
            if (document.getElementById('EstimationOut').value < document.getElementById('CheckInDate').value) {
                event.preventDefault();
                showCrudAlert('Estimation Out tidak boleh lebih kecil dari Check In.');
                document.getElementById('EstimationOutDisplay').focus();
                return;
            }
            if (!ensureSelectValueExists(xTypeId, 'Type of ID')) {
                event.preventDefault();
                return;
            }
            if (!ensureProvinceValueAllowed(xPropinsi)) {
                event.preventDefault();
                return;
            }
            if (!(xPhone?.value || '').trim()) {
                event.preventDefault();
                showCrudAlert('Telephone number must be filled.');
                xPhone?.focus();
                return;
            }
            if (paymentRequiresCompany(xPayment?.value) && !(xUsaha?.value || '').trim()) {
                event.preventDefault();
                showCrudAlert('Company must be filled for OTA, Company, or Travel payment.');
                xUsaha?.focus();
                return;
            }
            if (paymentRequiresCreditCard(xPayment?.value) && !(xCreditCard?.value || '').trim()) {
                event.preventDefault();
                showCrudAlert('Credit Card Number must be filled for card payment.');
                xCreditCard?.focus();
                return;
            }
            if (!(xPackage?.value || '').trim()) {
                event.preventDefault();
                showCrudAlert('Package must be selected for the main room.');
                xPackage?.focus();
                return;
            }
            if (isGroupCheckInType(xTipe?.value)) {
                const roomPositionFields = allRoomPositionFields();
                const invalidPositionField = roomPositionFields.find(function(field, index) {
                    const rowHasRoom = index === 0
                        ? (roomCodeField?.value || '').trim() !== ''
                        : (field.closest('[data-room-row]')?.querySelector('.room-code-input')?.value || '').trim() !== '';
                    return rowHasRoom && !(field.value || '').trim();
                });
                if (invalidPositionField) {
                    event.preventDefault();
                    showCrudAlert('Group Position must be selected for every room in a group check-in.');
                    invalidPositionField.focus();
                    return;
                }
                const hasInvalidGroupOption = roomPositionFields.some(function(field, index) {
                    const rowHasRoom = index === 0
                        ? (roomCodeField?.value || '').trim() !== ''
                        : (field.closest('[data-room-row]')?.querySelector('.room-code-input')?.value || '').trim() !== '';
                    return rowHasRoom && !ensureSelectValueExists(field, 'Group Position');
                });
                if (hasInvalidGroupOption) {
                    event.preventDefault();
                    return;
                }
                const leaderCount = roomPositionFields.filter(function(field, index) {
                    const rowHasRoom = index === 0
                        ? (roomCodeField?.value || '').trim() !== ''
                        : (field.closest('[data-room-row]')?.querySelector('.room-code-input')?.value || '').trim() !== '';
                    return rowHasRoom && (field.value || '').trim().toUpperCase() === 'LEADER';
                }).length;
                if (leaderCount !== 1) {
                    event.preventDefault();
                    showCrudAlert('Only one LEADER is allowed in one group registration.');
                    primaryRoomPositionField?.focus();
                    return;
                }
            } else {
                allRoomPositionFields().forEach(function(field) {
                    field.value = '';
                });
            }
            syncPrimaryRoomPositionValue();
            const invalidGuestRow = Array.from(additionalRoomBody.querySelectorAll('[data-room-row]')).find(function(row) {
                const sameCheckbox = row.querySelector('.room-same-checkbox');
                if (sameCheckbox?.checked) {
                    return false;
                }

                const detailRow = row.nextElementSibling && row.nextElementSibling.matches('[data-room-detail-row]') ? row.nextElementSibling : null;
                const guestNameInput = detailRow?.querySelector('.room-guest-name-input');
                return !guestNameInput || !guestNameInput.value.trim();
            });
            if (invalidGuestRow) {
                event.preventDefault();
                invalidGuestRow.querySelector('.room-same-checkbox').checked = false;
                updateRoomGuestRowState(invalidGuestRow);
                showCrudAlert('Guest name wajib diisi untuk room yang tidak mengikuti leader.');
                invalidGuestRow.nextElementSibling?.querySelector('.room-guest-name-input')?.focus();
                return;
            }
            const invalidPackageRow = Array.from(additionalRoomBody.querySelectorAll('[data-room-row]')).find(function(row) {
                const roomInput = row.querySelector('.room-code-input');
                const packageInput = row.querySelector('.package-code-input');
                return (roomInput?.value || '').trim() !== '' && (packageInput?.value || '').trim() === '';
            });
            if (invalidPackageRow) {
                event.preventDefault();
                showCrudAlert('Package must be selected for every room.');
                invalidPackageRow.querySelector('.package-code-input')?.focus();
                return;
            }
            roomCodeField.value = roomCodeField.value.trim().toUpperCase();
            packageCodeField.value = packageCodeField.value.trim().toUpperCase();
            nominalField.value = normalizeNumber(nominalField.value);
            Array.from(additionalRoomBody.querySelectorAll('.room-code-input')).forEach(input => input.value = input
                .value.trim().toUpperCase());
            Array.from(additionalRoomBody.querySelectorAll('.package-code-input')).forEach(input => input.value =
                input.value.trim().toUpperCase());
            Array.from(additionalRoomBody.querySelectorAll('.nominal-input')).forEach(input => input.value =
                normalizeNumber(input.value));
            numberOfPersonField.value = primaryRoomPaxDisplay.value || numberOfPersonField.value || '1';
        });
        function parseRowRecordPayload(encodedPayload) {
            const payload = (encodedPayload || '').trim();
            if (!payload) {
                throw new Error('Empty row payload');
            }

            const json = window.atob(payload);
            return JSON.parse(json);
        }

        let checkinDirectoryRequestController = null;
        const checkinDirectoryCache = new Map();
        const checkinDirectoryPrefetches = new Map();
        const checkinSortMemory = new Map();

        function getCheckinDirectoryCacheKey(url) {
            const targetUrl = new URL(url, window.location.origin);
            return targetUrl.pathname + targetUrl.search;
        }

        function buildCheckinDirectoryRequestUrl(url) {
            return new URL(url, window.location.origin);
        }

        function parseCheckinDirectoryShell(html) {
            const template = document.createElement('template');
            template.innerHTML = (html || '').trim();
            return template.content.querySelector('#checkinDirectoryShell')
                || template.content.firstElementChild
                || null;
        }

        function cloneCheckinDirectoryShell(shell) {
            if (!shell) {
                return null;
            }

            const template = document.createElement('template');
            template.innerHTML = shell.outerHTML;
            return template.content.querySelector('#checkinDirectoryShell')
                || template.content.firstElementChild
                || null;
        }

        function cacheCheckinDirectoryShell(url, shell) {
            const cacheKey = getCheckinDirectoryCacheKey(url);
            if (!cacheKey || !shell) {
                return;
            }

            checkinDirectoryCache.set(cacheKey, shell.outerHTML);
        }

        function getCachedCheckinDirectoryShell(url) {
            const markup = checkinDirectoryCache.get(getCheckinDirectoryCacheKey(url));
            return markup ? parseCheckinDirectoryShell(markup) : null;
        }

        function storeCurrentCheckinDirectorySnapshot(url = window.location.pathname + window.location.search) {
            const shell = document.getElementById('checkinDirectoryShell');
            if (!shell) {
                return;
            }

            cacheCheckinDirectoryShell(url, shell);
        }

        function getCurrentCheckinSortState() {
            const searchForm = document.getElementById('checkinSearchForm');
            const sortByField = searchForm?.querySelector('input[name="sort_by"]');
            const sortDirField = searchForm?.querySelector('input[name="sort_dir"]');

            return {
                sortBy: (sortByField?.value || 'check_in').trim(),
                sortDir: (sortDirField?.value || 'desc').trim().toLowerCase() === 'asc' ? 'asc' : 'desc',
            };
        }

        function rememberCurrentCheckinSortState() {
            const currentSort = getCurrentCheckinSortState();
            if (!currentSort.sortBy) {
                return;
            }

            checkinSortMemory.set(currentSort.sortBy, currentSort.sortDir);
        }

        function buildCheckinSortUrl(column) {
            const currentSort = getCurrentCheckinSortState();
            const targetUrl = new URL(window.location.pathname + window.location.search, window.location.origin);
            const rememberedDir = checkinSortMemory.get(column);
            let nextDir = 'asc';

            if (currentSort.sortBy === column) {
                nextDir = currentSort.sortDir === 'asc' ? 'desc' : 'asc';
            } else if (rememberedDir) {
                nextDir = rememberedDir === 'asc' ? 'desc' : 'asc';
            }

            targetUrl.searchParams.set('sort_by', column);
            targetUrl.searchParams.set('sort_dir', nextDir);
            targetUrl.searchParams.delete('page');
            checkinSortMemory.set(column, nextDir);

            return targetUrl.pathname + targetUrl.search;
        }

        function replaceCheckinDirectoryShell(nextShell) {
            const currentShell = document.getElementById('checkinDirectoryShell');
            if (!currentShell || !nextShell) {
                return;
            }

            currentShell.replaceWith(nextShell);
            rememberCurrentCheckinSortState();
            syncActiveDirectoryRow();
            warmCheckinDirectoryPaginationCache();
        }

        async function prefetchCheckinDirectory(url) {
            const cacheKey = getCheckinDirectoryCacheKey(url);
            if (!cacheKey || checkinDirectoryCache.has(cacheKey) || checkinDirectoryPrefetches.has(cacheKey)) {
                return;
            }

            const requestUrl = buildCheckinDirectoryRequestUrl(url);
            const request = fetch(requestUrl.toString(), {
                headers: {
                    'Accept': 'text/html',
                    'X-Partial-Component': 'checkin-directory',
                },
                credentials: 'same-origin',
            }).then(async function(response) {
                if (!response.ok) {
                    return;
                }

                const html = await response.text();
                const shell = parseCheckinDirectoryShell(html);
                if (shell) {
                    cacheCheckinDirectoryShell(url, shell);
                }
            }).catch(function() {
            }).finally(function() {
                checkinDirectoryPrefetches.delete(cacheKey);
            });

            checkinDirectoryPrefetches.set(cacheKey, request);
            await request;
        }

        function warmCheckinDirectoryPaginationCache() {
            const shell = document.getElementById('checkinDirectoryShell');
            if (!shell) {
                return;
            }

            const links = Array.from(shell.querySelectorAll('.package-pagination-wrap a[href]'))
                .map(link => link.getAttribute('href') || '')
                .filter(href => href && href !== '#');

            links.slice(0, 4).forEach(function(href) {
                prefetchCheckinDirectory(href);
            });
        }

        function syncActiveDirectoryRow() {
            const activeDetailKey = (currentDetailKeyField?.value || primaryDetailKeyField?.value || '').trim();
            Array.from(document.querySelectorAll('.checkin-record-row')).forEach(function(item) {
                item.classList.toggle('is-active', activeDetailKey !== '' && (item.dataset.detailKey || '').trim() === activeDetailKey);
            });
        }

        async function refreshCheckinDirectory(url, options = {}) {
            const currentShell = document.getElementById('checkinDirectoryShell');
            if (!currentShell) {
                window.location.href = url;
                return;
            }

            const targetUrl = new URL(url, window.location.origin);
            const cacheKey = getCheckinDirectoryCacheKey(targetUrl.toString());
            const requestUrl = buildCheckinDirectoryRequestUrl(targetUrl.toString());
            const cachedShell = options.forceRefresh ? null : getCachedCheckinDirectoryShell(targetUrl.toString());
            if (cachedShell) {
                replaceCheckinDirectoryShell(cachedShell);
                if (options.pushHistory !== false) {
                    window.history.pushState({
                        checkinDirectory: true
                    }, '', targetUrl.pathname + targetUrl.search + targetUrl.hash);
                }
                return;
            }

            if (checkinDirectoryRequestController) {
                checkinDirectoryRequestController.abort();
            }

            const controller = new AbortController();
            const pushHistory = options.pushHistory !== false;
            checkinDirectoryRequestController = controller;
            currentShell.setAttribute('aria-busy', 'true');
            currentShell.classList.add('is-loading');

            try {
                const response = await fetch(requestUrl.toString(), {
                    headers: {
                        'Accept': 'text/html',
                        'X-Partial-Component': 'checkin-directory',
                    },
                    credentials: 'same-origin',
                    signal: controller.signal,
                });

                if (response.status === 401) {
                    window.location.href = '/';
                    return;
                }

                if (!response.ok) {
                    throw new Error('Directory request failed.');
                }

                const html = await response.text();
                const nextShell = parseCheckinDirectoryShell(html);

                if (!nextShell) {
                    throw new Error('Directory markup is missing.');
                }

                cacheCheckinDirectoryShell(cacheKey, nextShell);
                replaceCheckinDirectoryShell(cloneCheckinDirectoryShell(nextShell));

                if (pushHistory) {
                    window.history.pushState({
                        checkinDirectory: true
                    }, '', targetUrl.pathname + targetUrl.search + targetUrl.hash);
                }
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }
                console.error('Unable to refresh check-in directory:', error);
                window.location.href = targetUrl.pathname + targetUrl.search + targetUrl.hash;
            } finally {
                if (checkinDirectoryRequestController === controller) {
                    checkinDirectoryRequestController = null;
                }
                document.getElementById('checkinDirectoryShell')?.classList.remove('is-loading');
                document.getElementById('checkinDirectoryShell')?.setAttribute('aria-busy', 'false');
            }
        }

        document.addEventListener('submit', function(event) {
            const submittedForm = event.target;
            if (!(submittedForm instanceof HTMLFormElement)) {
                return;
            }

            if (submittedForm.id !== 'checkinSearchForm' && !submittedForm.classList.contains('checkin-per-page')) {
                return;
            }

            event.preventDefault();
            const targetUrl = new URL(submittedForm.getAttribute('action') || '/checkin', window.location.origin);
            const params = new URLSearchParams(new FormData(submittedForm));

            if (submittedForm.id === 'checkinSearchForm') {
                params.delete('page');
            }

            const queryString = params.toString();
            refreshCheckinDirectory(targetUrl.pathname + (queryString ? '?' + queryString : ''));
        });

        document.addEventListener('click', function(event) {
            const directoryShell = document.getElementById('checkinDirectoryShell');
            if (!directoryShell) {
                return;
            }

            const link = event.target.closest('a');
            if (link && directoryShell.contains(link) && !link.classList.contains('checkin-delete-link')) {
                const href = link.getAttribute('href') || '';
                if (link.classList.contains('checkin-sort-link')) {
                    event.preventDefault();
                    const sortColumn = (link.dataset.sortColumn || '').trim();
                    if (sortColumn) {
                        refreshCheckinDirectory(buildCheckinSortUrl(sortColumn));
                    }
                    return;
                }
                const isDirectoryActionLink = link.closest('.package-pagination-wrap')
                    || link.closest('.checkin-search-actions');
                if (isDirectoryActionLink) {
                    event.preventDefault();
                    if (href && href !== '#') {
                        refreshCheckinDirectory(href);
                    }
                    return;
                }
            }

            const row = event.target.closest('.checkin-record-row');
            if (!row || !directoryShell.contains(row)) {
                return;
            }

            if (event.target.closest('.checkin-delete-link')) {
                return;
            }

            event.preventDefault();

            try {
                applyRecord(parseRowRecordPayload(row.dataset.record));
                syncActiveDirectoryRow();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            } catch (error) {
                showCrudAlert('Data baris tidak bisa dimuat ke form.');
            }
        });

        window.addEventListener('popstate', function() {
            if (window.location.pathname !== '/checkin') {
                return;
            }

            const cachedShell = getCachedCheckinDirectoryShell(window.location.pathname + window.location.search);
            if (cachedShell) {
                replaceCheckinDirectoryShell(cachedShell);
                return;
            }

            refreshCheckinDirectory(window.location.pathname + window.location.search, {
                pushHistory: false,
                forceRefresh: true
            });
        });

        form.addEventListener('keydown', function(event) {
            if (event.key !== 'Enter' || event.target.tagName === 'TEXTAREA') {
                return;
            }
            const fields = Array.from(form.querySelectorAll(
                'input[data-flow], select[data-flow], textarea[data-flow]')).filter(field => !field.disabled &&
                field.offsetParent !== null);
            const index = fields.indexOf(event.target);
            if (index >= 0 && index < fields.length - 1) {
                event.preventDefault();
                fields[index + 1].focus();
                fields[index + 1].select?.();
            }
        });
        if (oldAdditionalRoomRows.length) {
            oldAdditionalRoomRows.forEach(detail => addAdditionalRoomRow(detail));
        }
        updateRoomHelper();
        updatePackageHelper();
        updatePrimaryGuestDisplay();
        primaryRoomPaxDisplay.value = numberOfPersonField.value || primaryRoomPaxDisplay.value || '2';
        updateRoomSummary();
        if (initialHasOld) {
            document.getElementById('GuestName').focus();
        } else {
            resetForm();
        }
        if (window.location.pathname === '/checkin') {
            const cleanUrl = new URL(window.location.href);
            if (cleanUrl.searchParams.has('_partial')) {
                cleanUrl.searchParams.delete('_partial');
                window.history.replaceState(window.history.state, '', cleanUrl.pathname + cleanUrl.search + cleanUrl.hash);
            }
        }
        storeCurrentCheckinDirectorySnapshot();
        rememberCurrentCheckinSortState();
        syncActiveDirectoryRow();
        warmCheckinDirectoryPaginationCache();
        const successAlert = document.getElementById('successAlert');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.transition = 'opacity .3s ease, transform .3s ease';
                successAlert.style.opacity = '0';
                successAlert.style.transform = 'translateY(-8px)';
                setTimeout(() => successAlert.remove(), 300);
            }, 3000);
        }
    </script>

@endsection



