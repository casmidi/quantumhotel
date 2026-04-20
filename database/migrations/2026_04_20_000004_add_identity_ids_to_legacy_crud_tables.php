<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIdentityColumn('KELAS');
        $this->addIdentityColumn('ROOM');
        $this->addIdentityColumn('StockPackage');
        $this->addIdentityColumn('Package');
        $this->addIdentityColumn('DATA');
        $this->addIdentityColumn('DATA2');

        $this->addUniqueIndex('KELAS', 'Kode', 'uq_kelas_kode');
        $this->addUniqueIndex('ROOM', 'Kode', 'uq_room_kode');
        $this->addUniqueIndex('StockPackage', 'KodeBrg', 'uq_stockpackage_kodebrg');
        $this->addUniqueIndex('Package', 'Nofak', 'uq_package_nofak');
        $this->addUniqueIndex('DATA', 'RegNo', 'uq_data_regno');
        $this->addUniqueIndex('DATA2', 'RegNo2', 'uq_data2_regno2');
    }

    public function down(): void
    {
        $this->dropUniqueIndex('DATA2', 'uq_data2_regno2');
        $this->dropUniqueIndex('DATA', 'uq_data_regno');
        $this->dropUniqueIndex('Package', 'uq_package_nofak');
        $this->dropUniqueIndex('StockPackage', 'uq_stockpackage_kodebrg');
        $this->dropUniqueIndex('ROOM', 'uq_room_kode');
        $this->dropUniqueIndex('KELAS', 'uq_kelas_kode');

        $this->dropColumnIfExists('DATA2', 'id');
        $this->dropColumnIfExists('DATA', 'id');
        $this->dropColumnIfExists('Package', 'id');
        $this->dropColumnIfExists('StockPackage', 'id');
        $this->dropColumnIfExists('ROOM', 'id');
        $this->dropColumnIfExists('KELAS', 'id');
    }

    private function addIdentityColumn(string $table): void
    {
        DB::statement("
            IF COL_LENGTH('{$table}', 'id') IS NULL
            BEGIN
                ALTER TABLE [{$table}] ADD [id] INT IDENTITY(1,1) NOT NULL;
            END
        ");
    }

    private function addUniqueIndex(string $table, string $column, string $indexName): void
    {
        DB::statement("
            IF NOT EXISTS (
                SELECT 1
                FROM sys.indexes
                WHERE name = '{$indexName}'
                  AND object_id = OBJECT_ID(N'{$table}')
            )
            BEGIN
                CREATE UNIQUE INDEX [{$indexName}] ON [{$table}]([{$column}]);
            END
        ");
    }

    private function dropUniqueIndex(string $table, string $indexName): void
    {
        DB::statement("
            IF EXISTS (
                SELECT 1
                FROM sys.indexes
                WHERE name = '{$indexName}'
                  AND object_id = OBJECT_ID(N'{$table}')
            )
            BEGIN
                DROP INDEX [{$indexName}] ON [{$table}];
            END
        ");
    }

    private function dropColumnIfExists(string $table, string $column): void
    {
        DB::statement("
            IF COL_LENGTH('{$table}', '{$column}') IS NOT NULL
            BEGIN
                ALTER TABLE [{$table}] DROP COLUMN [{$column}];
            END
        ");
    }
};
