<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class SynchroniseController extends Controller
{
    private const SOURCE_CONNECTION = 'sqlsrv_bogor';
    private const DESTINATION_CONNECTION = 'sqlsrv';

    private const TABLES = [
        'DATA' => [
            'table' => 'DATA',
            'keys' => ['regno'],
            'label' => 'Guest / Reservation Data',
            'description' => 'Data transaksi utama dari server Bogor.',
        ],
        'DATA2' => [
            'table' => 'DATA2',
            'keys' => ['regno', 'regno2'],
            'label' => 'Guest Detail Data',
            'description' => 'Detail pendukung transaksi tamu. ID diabaikan karena hanya identity lokal.',
        ],
        'DATAMOVE' => [
            'table' => 'DATAMOVE',
            'keys' => ['RegNo2'],
            'label' => 'Room Move History',
            'description' => 'Riwayat perpindahan kamar. ID diabaikan karena hanya identity teknis lokal.',
        ],
        'NOMOR' => [
            'table' => 'NOMOR',
            'keys' => ['Nomor'],
            'label' => 'Numbering',
            'description' => 'Nomor referensi operasional. ID diabaikan karena hanya angka teknis lokal.',
        ],
        'ONLINE' => [
            'table' => 'ONLINE',
            'keys' => ['Nofak', 'Kode', 'StrTgl'],
            'label' => 'Online Booking',
            'description' => 'Data reservasi online. ID diabaikan karena hanya identity teknis lokal.',
        ],
        'PB1' => [
            'table' => 'PB1',
            'keys' => ['Invoice2', 'Regno', 'Kode', 'Tgl'],
            'label' => 'PB1 Final Posting',
            'description' => 'Fallback final posting RCR. Dipakai jika ONLINE belum tersedia atau untuk tanggal posting terakhir.',
        ],
        'EXTRABED' => [
            'table' => 'EXTRABED',
            'keys' => ['Regno2', 'Kode', 'Tgl'],
            'label' => 'Extra Bed Posting',
            'description' => 'Lookup tanggal transaksi extra bed untuk RCR.',
        ],
        'EXTRABEDD' => [
            'table' => 'EXTRABEDD',
            'keys' => ['Nofak', 'NoUrut'],
            'label' => 'Extra Bed Detail',
            'description' => 'Detail nominal extra bed. Relasi ke EXTRABED melalui Nofak.',
        ],
        'TAMBAH' => [
            'table' => 'TAMBAH',
            'keys' => ['Regno2', 'Kode', 'Tgl'],
            'label' => 'Additional Charge',
            'description' => 'Lookup tanggal transaksi additional charge untuk RCR.',
        ],
        'TAMBAHD' => [
            'table' => 'TAMBAHD',
            'keys' => ['Nofak', 'NoUrut'],
            'label' => 'Additional Charge Detail',
            'description' => 'Detail nominal additional charge. Relasi ke TAMBAH melalui Nofak.',
        ],
        'RES2' => [
            'table' => 'RES2',
            'keys' => ['Regno2', 'Kode', 'Tgl'],
            'label' => 'Restaurant / POS Posting',
            'description' => 'Lookup tanggal transaksi restaurant/POS untuk RCR.',
        ],
        'RES2D' => [
            'table' => 'RES2D',
            'keys' => ['Nofak', 'NoUrut'],
            'label' => 'Restaurant / POS Detail',
            'description' => 'Detail restaurant/POS sesuai nama tabel RES2D di database Bogor.',
        ],
        'RESD2' => [
            'table' => 'RESD2',
            'keys' => ['Nofak', 'NoUrut'],
            'label' => 'Restaurant / POS Detail Legacy',
            'description' => 'Detail restaurant/POS yang dipakai kode checkout lama. Pilih jika tabelnya bernama RESD2.',
        ],
        'PACKAGE' => [
            'table' => 'PACKAGE',
            'keys' => ['Nofak'],
            'label' => 'Package Transaction',
            'description' => 'Transaksi paket dari Bogor.',
        ],
        'PACKAGED' => [
            'table' => 'PACKAGED',
            'keys' => ['Nofak', 'NoUrut'],
            'label' => 'Package Detail',
            'description' => 'Detail item package. Qty, harga, diskon, dan jumlah bisa berubah mengikuti Nofak + NoUrut.',
        ],
        'STOCKPACKAGE' => [
            'table' => 'STOCKPACKAGE',
            'keys' => ['KodeBrg'],
            'label' => 'Package Stock',
            'description' => 'Master stok paket dan barang.',
        ],
        'SETUP' => [
            'table' => 'SETUP',
            'keys' => ['Kode'],
            'label' => 'System Setup',
            'description' => 'StatusPosting untuk periode laporan dan kontrol posting.',
        ],
    ];

    public function index(Request $request)
    {
        $inspect = $request->boolean('inspect');

        return view('synchronise.index', [
            'source' => $this->connectionSummary(self::SOURCE_CONNECTION, 'Server Bogor'),
            'destination' => $this->connectionSummary(self::DESTINATION_CONNECTION, 'Database Lokal'),
            'tables' => self::TABLES,
            'inspection' => $inspect ? $this->inspectTables() : null,
        ]);
    }

    public function run(Request $request)
    {
        $selected = array_values(array_intersect(
            array_keys(self::TABLES),
            (array) $request->input('tables', [])
        ));

        if (count($selected) === 0) {
            return redirect()
                ->route('synchronise.index')
                ->with('error', 'Pilih minimal satu tabel untuk di-import.');
        }

        $mode = in_array($request->input('mode'), ['upsert', 'replace'], true)
            ? $request->input('mode')
            : 'upsert';
        $batchSize = max(50, min(5000, (int) $request->input('batch_size', 500)));
        $dryRun = $request->boolean('dry_run');

        if ($mode === 'replace' && !$request->boolean('confirm_replace')) {
            return redirect()
                ->route('synchronise.index')
                ->with('error', 'Centang konfirmasi replace jika ingin mengosongkan tabel lokal sebelum import.');
        }

        $results = [];

        foreach ($selected as $key) {
            try {
                $results[$key] = $dryRun
                    ? $this->previewTable(self::TABLES[$key])
                    : $this->importTable(self::TABLES[$key], $mode, $batchSize);
            } catch (Throwable $exception) {
                $results[$key] = [
                    'status' => 'failed',
                    'table' => self::TABLES[$key]['table'],
                    'message' => $exception->getMessage(),
                    'source_count' => null,
                    'destination_count' => null,
                    'inserted' => 0,
                    'updated' => 0,
                    'skipped' => 0,
                ];
            }
        }

        $hasFailure = collect($results)->contains(fn ($result) => $result['status'] === 'failed');

        return redirect()
            ->route('synchronise.index', ['inspect' => 1])
            ->with($hasFailure ? 'error' : 'success', $dryRun ? 'Preview import selesai.' : 'Import data selesai.')
            ->with('sync_results', $results);
    }

    private function connectionSummary(string $connection, string $label): array
    {
        $config = config("database.connections.$connection", []);

        return [
            'label' => $label,
            'connection' => $connection,
            'driver' => 'SQL Server Native Client',
            'host' => $config['host'] ?? '-',
            'port' => $config['port'] ?? '-',
            'database' => $config['database'] ?? '-',
            'username' => $config['username'] ?? '-',
            'authentication' => empty($config['username']) ? 'Windows Authentication' : 'SQL Server Authentication',
        ];
    }

    private function inspectTables(): array
    {
        $inspection = [];

        foreach (self::TABLES as $key => $info) {
            $inspection[$key] = [
                'source_count' => $this->safeCount(self::SOURCE_CONNECTION, $info['table']),
                'destination_count' => $this->safeCount(self::DESTINATION_CONNECTION, $info['table']),
            ];
        }

        return $inspection;
    }

    private function previewTable(array $info): array
    {
        return [
            'status' => 'preview',
            'table' => $info['table'],
            'message' => 'Preview saja, belum ada data yang ditulis.',
            'source_count' => $this->safeCount(self::SOURCE_CONNECTION, $info['table']),
            'destination_count' => $this->safeCount(self::DESTINATION_CONNECTION, $info['table']),
            'inserted' => 0,
            'updated' => 0,
            'skipped' => 0,
        ];
    }

    private function importTable(array $info, string $mode, int $batchSize): array
    {
        if ($mode === 'replace') {
            return $this->replaceTable($info, $batchSize);
        }

        return $this->upsertTable($info);
    }

    private function replaceTable(array $info, int $batchSize): array
    {
        $source = DB::connection(self::SOURCE_CONNECTION)->table($info['table']);
        $destination = DB::connection(self::DESTINATION_CONNECTION);
        $inserted = 0;
        $batch = [];

        $destination->transaction(function () use ($source, $destination, $info, $batchSize, &$inserted, &$batch) {
            $this->emptyDestinationTable($destination, $info['table']);

            foreach ($source->cursor() as $row) {
                $batch[] = $this->rowToArray($row);

                if (count($batch) >= $batchSize) {
                    $destination->table($info['table'])->insert($batch);
                    $inserted += count($batch);
                    $batch = [];
                }
            }

            if (count($batch) > 0) {
                $destination->table($info['table'])->insert($batch);
                $inserted += count($batch);
                $batch = [];
            }
        });

        return [
            'status' => 'success',
            'table' => $info['table'],
            'message' => 'Tabel lokal diganti dengan data dari Bogor.',
            'source_count' => $this->safeCount(self::SOURCE_CONNECTION, $info['table']),
            'destination_count' => $this->safeCount(self::DESTINATION_CONNECTION, $info['table']),
            'inserted' => $inserted,
            'updated' => 0,
            'skipped' => 0,
        ];
    }

    private function upsertTable(array $info): array
    {
        $source = DB::connection(self::SOURCE_CONNECTION)->table($info['table']);
        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($source->cursor() as $row) {
            $rowData = $this->rowToArray($row);
            $keyValues = $this->keyValues($rowData, $info['keys']);
            $local = $this->keyedQuery(
                DB::connection(self::DESTINATION_CONNECTION)->table($info['table']),
                $keyValues
            )->first();

            if (!$local) {
                DB::connection(self::DESTINATION_CONNECTION)->table($info['table'])->insert($rowData);
                $inserted++;
                continue;
            }

            if ($this->rowChanged((array) $local, $rowData)) {
                $updateQuery = DB::connection(self::DESTINATION_CONNECTION)->table($info['table']);
                $this->keyedQuery($updateQuery, $keyValues)->update($rowData);
                $updated++;
                continue;
            }

            $skipped++;
        }

        return [
            'status' => 'success',
            'table' => $info['table'],
            'message' => 'Data baru ditambahkan, data berbeda diperbarui.',
            'source_count' => $this->safeCount(self::SOURCE_CONNECTION, $info['table']),
            'destination_count' => $this->safeCount(self::DESTINATION_CONNECTION, $info['table']),
            'inserted' => $inserted,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    private function emptyDestinationTable($connection, string $table): void
    {
        try {
            $connection->statement("TRUNCATE TABLE [$table]");
        } catch (Throwable) {
            $connection->table($table)->delete();
        }
    }

    private function safeCount(string $connection, string $table): ?int
    {
        try {
            return DB::connection($connection)->table($table)->count();
        } catch (Throwable) {
            return null;
        }
    }

    private function rowToArray(object $row): array
    {
        return collect((array) $row)
            ->map(fn ($value) => $value instanceof \DateTimeInterface ? $value->format('Y-m-d H:i:s') : $value)
            ->all();
    }

    private function keyValues(array $row, array $keys): array
    {
        $values = [];

        foreach ($keys as $key) {
            foreach ($row as $column => $value) {
                if (strtolower($column) === strtolower($key)) {
                    $values[$column] = $value;
                    continue 2;
                }
            }

            throw new \RuntimeException("Kolom key [$key] tidak ditemukan pada data sumber.");
        }

        return $values;
    }

    private function keyedQuery($query, array $keyValues)
    {
        foreach ($keyValues as $column => $value) {
            $query->where($column, $value);
        }

        return $query;
    }

    private function rowChanged(array $local, array $remote): bool
    {
        foreach ($remote as $column => $value) {
            if (!array_key_exists($column, $local)) {
                continue;
            }

            if ((string) $local[$column] !== (string) $value) {
                return true;
            }
        }

        return false;
    }
}
