<?php

/*
|--------------------------------------------------------------------------
| Native PHP - Ambil CRUD KELAS Dengan API Bearer
|--------------------------------------------------------------------------
| File ini berdiri sendiri dan tidak memakai Laravel.
| Jalankan dari browser atau command line:
|
|   php native-api-bearer-kelas.php
|
| Jika API berjalan di lokal, ubah $baseUrl menjadi:
|
|   http://127.0.0.1:8001
|
*/

$baseUrl = 'http://quantum.or.id';
$username = 'S';
$password = '78820262026';

function apiRequest(string $method, string $url, array $headers = [], ?array $body = null): array
{
    $curl = curl_init($url);
    $requestHeaders = array_merge([
        'Accept: application/json',
        'Content-Type: application/json',
    ], $headers);

    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => $requestHeaders,
        CURLOPT_TIMEOUT => 30,
    ]);

    if ($body !== null) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $responseBody = curl_exec($curl);
    $curlError = curl_error($curl);
    $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($responseBody === false) {
        return [
            'ok' => false,
            'status' => 0,
            'json' => null,
            'raw' => '',
            'error' => $curlError ?: 'Request gagal.',
        ];
    }

    $json = json_decode($responseBody, true);

    return [
        'ok' => $statusCode >= 200 && $statusCode < 300,
        'status' => $statusCode,
        'json' => is_array($json) ? $json : null,
        'raw' => $responseBody,
        'error' => null,
    ];
}

function escapeHtml($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$login = apiRequest('POST', $baseUrl . '/api/v1/login', [], [
    'username' => $username,
    'password' => $password,
]);

$token = (string) ($login['json']['data']['access_token'] ?? '');
$kelas = null;
$rows = [];

if ($token !== '') {
    $kelas = apiRequest('GET', $baseUrl . '/api/v1/kelas', [
        'Authorization: Bearer ' . $token,
    ]);

    $rows = $kelas['json']['data']['items']
        ?? $kelas['json']['data']
        ?? [];

    if (!is_array($rows)) {
        $rows = [];
    }
}

$isCli = PHP_SAPI === 'cli';

if ($isCli) {
    echo "Login status: " . $login['status'] . PHP_EOL;

    if ($token === '') {
        echo "Login gagal:" . PHP_EOL;
        echo $login['raw'] . PHP_EOL;
        exit(1);
    }

    echo "Token: " . substr($token, 0, 12) . '...' . substr($token, -8) . PHP_EOL;
    echo "KELAS status: " . ($kelas['status'] ?? 0) . PHP_EOL;

    foreach ($rows as $row) {
        echo sprintf(
            "%s | %s | %s | %s | %s",
            $row['id'] ?? '-',
            $row['Kode'] ?? '-',
            $row['Nama'] ?? '-',
            $row['Rate1'] ?? 0,
            $row['Depo1'] ?? 0
        ) . PHP_EOL;
    }

    exit(0);
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Native PHP API Bearer KELAS</title>
    <style>
        body {
            margin: 0;
            padding: 24px;
            font-family: Arial, sans-serif;
            color: #173761;
            background: #f6f9ff;
        }

        .card {
            max-width: 1100px;
            margin: 0 auto 18px;
            background: #fff;
            border: 1px solid #dbe8ff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 12px 30px rgba(16, 35, 59, 0.06);
        }

        .head {
            padding: 16px 18px;
            border-bottom: 1px solid #dbe8ff;
            background: #eef5ff;
        }

        .head h1,
        .head h2 {
            margin: 0;
            font-size: 20px;
        }

        .body {
            padding: 18px;
        }

        .badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-weight: 700;
            background: #e7f7ef;
            color: #12805c;
        }

        .badge.error {
            background: #fff1f0;
            color: #b42318;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e4eefc;
            text-align: left;
        }

        th {
            background: #f6f9ff;
            font-size: 12px;
            text-transform: uppercase;
        }

        .right {
            text-align: right;
        }

        pre {
            margin: 0;
            white-space: pre-wrap;
            overflow: auto;
            color: #173761;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="head">
            <h1>Native PHP API Bearer KELAS</h1>
        </div>
        <div class="body">
            <p>Base URL: <strong><?= escapeHtml($baseUrl) ?></strong></p>
            <p>Login status:
                <span class="badge <?= $login['ok'] ? '' : 'error' ?>">
                    <?= escapeHtml($login['status']) ?>
                </span>
            </p>

            <?php if ($token !== ''): ?>
                <p>Bearer token: <strong><?= escapeHtml(substr($token, 0, 12) . '...' . substr($token, -8)) ?></strong></p>
                <p>KELAS status:
                    <span class="badge <?= ($kelas && $kelas['ok']) ? '' : 'error' ?>">
                        <?= escapeHtml($kelas['status'] ?? 0) ?>
                    </span>
                </p>
            <?php else: ?>
                <p class="badge error">Token tidak didapat. Cek username/password atau endpoint login.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="head">
            <h2>Grid KELAS</h2>
        </div>
        <div class="body">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th class="right">Rate</th>
                        <th class="right">Deposit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows): ?>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><?= escapeHtml($row['id'] ?? '-') ?></td>
                                <td><?= escapeHtml($row['Kode'] ?? '-') ?></td>
                                <td><?= escapeHtml($row['Nama'] ?? '-') ?></td>
                                <td class="right"><?= number_format((float) ($row['Rate1'] ?? 0), 0, ',', '.') ?></td>
                                <td class="right"><?= number_format((float) ($row['Depo1'] ?? 0), 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">Data KELAS kosong atau request gagal.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="head">
            <h2>Raw JSON Login</h2>
        </div>
        <div class="body">
            <pre><?= escapeHtml(json_encode($login['json'] ?? $login['raw'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?></pre>
        </div>
    </div>

    <div class="card">
        <div class="head">
            <h2>Raw JSON KELAS</h2>
        </div>
        <div class="body">
            <pre><?= escapeHtml(json_encode($kelas['json'] ?? ($kelas['raw'] ?? null), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?></pre>
        </div>
    </div>
</body>
</html>
