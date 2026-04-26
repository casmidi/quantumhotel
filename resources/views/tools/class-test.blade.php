@extends('layouts.app')

@section('title', '')

@section('topbar_brand')
    <div class="class-test-topbar-title">Class Test</div>
@endsection

@section('content')
@include('partials.crud-package-theme')

<style>
    .class-test-topbar-title {
        color: var(--package-title);
        font-family: "Segoe UI", "Trebuchet MS", "Helvetica Neue", Arial, sans-serif;
        font-size: 2rem;
        font-weight: 900;
        line-height: 1;
    }

    .class-test-page {
        display: grid;
        gap: 1.25rem;
        max-width: 1180px;
    }

    .class-test-card {
        overflow: hidden;
        border: 1px solid var(--package-shell-border);
        border-radius: 8px;
        background: var(--package-shell-bg);
        box-shadow: var(--package-shell-shadow), inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .class-test-head {
        padding: 1rem 1.2rem;
        border-bottom: 1px solid var(--package-shell-border);
        background: var(--package-header-bg);
    }

    .class-test-head h3 {
        margin: 0;
        color: var(--package-title);
        font-size: 1.08rem;
        font-weight: 850;
    }

    .class-test-head p {
        margin: 0.25rem 0 0;
        color: var(--package-muted);
        font-size: 0.88rem;
    }

    .class-test-form {
        display: grid;
        grid-template-columns: minmax(180px, 1fr) minmax(180px, 1fr) auto;
        gap: 0.8rem;
        align-items: end;
        padding: 1.1rem 1.2rem;
    }

    .class-test-field {
        display: grid;
        gap: 0.4rem;
    }

    .class-test-field label {
        margin: 0;
        color: var(--package-label);
        font-size: 0.78rem;
        font-weight: 850;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .class-test-token {
        padding: 0 1.2rem 1.1rem;
        color: var(--package-muted);
        font-size: 0.9rem;
        overflow-wrap: anywhere;
    }

    .class-test-table-wrap {
        overflow: auto;
    }

    .class-test-table {
        margin: 0;
    }

    .class-test-table thead th {
        background: var(--package-table-head-bg);
        color: var(--package-title);
        font-size: 0.76rem;
        font-weight: 850;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .class-test-table tbody tr:nth-child(odd) {
        background: var(--package-table-odd);
    }

    .class-test-table tbody tr:nth-child(even) {
        background: var(--package-table-even);
    }

    .class-test-json {
        max-height: 260px;
        margin: 0;
        overflow: auto;
        border-top: 1px solid var(--package-shell-border);
        padding: 1rem 1.2rem;
        background: #fbfdff;
        color: var(--package-text);
        font-family: Consolas, "Courier New", monospace;
        font-size: 0.84rem;
        line-height: 1.5;
        white-space: pre-wrap;
    }

    .class-test-status {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.7rem;
        border-radius: 999px;
        background: var(--package-badge-bg);
        color: var(--package-badge-text);
        font-weight: 850;
        font-size: 0.8rem;
    }

    @media (max-width: 767.98px) {
        .class-test-form {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="class-test-page">
    <div class="class-test-card">
        <div class="class-test-head">
            <h3>Bearer API Class JSON</h3>
            <p>Login ke /api/v1/login, tangkap token, teruskan token ke /api/v1/kelas, lalu tampilkan JSON kelas dalam grid.</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-warning m-3">{{ $errors->first() }}</div>
        @endif

        @if ($message)
            <div class="alert {{ $status && $status < 400 ? 'alert-success' : 'alert-warning' }} m-3">
                @if ($status)
                    <span class="class-test-status">Response {{ $status }}</span>
                @endif
                <span class="ml-2">{{ $message }}</span>
            </div>
        @endif

        <form method="POST" action="/tools/class-test" class="class-test-form">
            @csrf
            <div class="class-test-field">
                <label for="username">Username SANDI</label>
                <input type="text" name="username" id="username" class="form-control package-input" value="{{ old('username', $username) }}" autocomplete="off" required>
            </div>

            <div class="class-test-field">
                <label for="password">Password SANDI</label>
                <input type="password" name="password" id="password" class="form-control package-input" autocomplete="current-password" required>
            </div>

            <button type="submit" class="btn package-btn-primary">Get Kelas JSON</button>
        </form>

        @if ($token)
            <div class="class-test-token">
                Bearer token captured: {{ substr($token, 0, 12) }}...{{ substr($token, -8) }}
            </div>
        @endif
    </div>

    <div class="class-test-card">
        <div class="class-test-head">
            <h3>Grid Result</h3>
            <p>Data di bawah berasal dari response JSON endpoint /api/v1/kelas.</p>
        </div>

        <div class="class-test-table-wrap">
            <table class="table table-hover class-test-table">
                <thead>
                    <tr>
                        <th style="width: 90px;">ID</th>
                        <th style="width: 150px;">Kode</th>
                        <th>Nama</th>
                        <th class="text-right" style="width: 160px;">Rate</th>
                        <th class="text-right" style="width: 160px;">Deposit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kelasRows as $row)
                        <tr>
                            <td>{{ data_get($row, 'id', '-') }}</td>
                            <td>{{ data_get($row, 'Kode', '-') }}</td>
                            <td>{{ data_get($row, 'Nama', '-') }}</td>
                            <td class="text-right">{{ number_format((float) data_get($row, 'Rate1', 0), 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format((float) data_get($row, 'Depo1', 0), 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada data. Isi username/password lalu klik Get Kelas JSON.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($kelasJson)
            <pre class="class-test-json">{{ json_encode($kelasJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
        @endif
    </div>
</section>
@endsection
