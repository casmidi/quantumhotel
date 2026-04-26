<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIdentityColumn('SANDI');
        $this->addIdentityColumn('SANDI2');
        $this->addIdentityColumn('SANDI3');
        $this->addSandi3SplitMenuColumns();
        $this->backfillSandi3SplitMenuColumns();
    }

    public function down(): void
    {
        $this->dropColumnIfExists('SANDI3', 'MenuDescription');
        $this->dropColumnIfExists('SANDI3', 'MenuCode');
        $this->dropColumnIfExists('SANDI3', 'id');
        $this->dropColumnIfExists('SANDI2', 'id');
        $this->dropColumnIfExists('SANDI', 'id');
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

    private function addSandi3SplitMenuColumns(): void
    {
        DB::statement("
            IF COL_LENGTH('SANDI3', 'MenuCode') IS NULL
            BEGIN
                ALTER TABLE [SANDI3] ADD [MenuCode] NVARCHAR(10) NULL;
            END
        ");

        DB::statement("
            IF COL_LENGTH('SANDI3', 'MenuDescription') IS NULL
            BEGIN
                ALTER TABLE [SANDI3] ADD [MenuDescription] NVARCHAR(50) NULL;
            END
        ");
    }

    private function backfillSandi3SplitMenuColumns(): void
    {
        DB::statement("
            UPDATE [SANDI3]
            SET [MenuCode] = LEFT(parsed.menu_code, 10),
                [MenuDescription] = LEFT(parsed.menu_description, 50)
            FROM [SANDI3]
            CROSS APPLY (
                SELECT LTRIM(RTRIM(COALESCE([Ket], ''))) AS clean_ket
            ) cleaned
            CROSS APPLY (
                SELECT NULLIF(CHARINDEX(' ', cleaned.clean_ket), 0) AS first_space
            ) marker
            CROSS APPLY (
                SELECT
                    CASE
                        WHEN marker.first_space IS NOT NULL THEN LEFT(cleaned.clean_ket, marker.first_space - 1)
                        ELSE LEFT(cleaned.clean_ket, 3)
                    END AS menu_code,
                    CASE
                        WHEN marker.first_space IS NOT NULL THEN LTRIM(SUBSTRING(cleaned.clean_ket, marker.first_space + 1, 4000))
                        ELSE LTRIM(SUBSTRING(cleaned.clean_ket, 4, 4000))
                    END AS menu_description
            ) parsed
            WHERE (NULLIF(LTRIM(RTRIM([MenuCode])), '') IS NULL
                OR NULLIF(LTRIM(RTRIM([MenuDescription])), '') IS NULL)
              AND NULLIF(cleaned.clean_ket, '') IS NOT NULL;
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
