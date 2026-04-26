<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('position_menu_defaults')) {
            Schema::create('position_menu_defaults', function (Blueprint $table) {
                $table->id();
                $table->string('position', 120);
                $table->string('ket', 120);
                $table->string('kunci', 255)->nullable();
                $table->string('source_user_code', 80)->nullable();
                $table->timestamps();

                $table->unique(['position', 'ket']);
                $table->index('position');
            });
        }

        DB::statement("
            IF EXISTS (
                SELECT 1
                FROM sys.indexes
                WHERE name = 'position_menu_defaults_position_ket_unique'
                  AND object_id = OBJECT_ID(N'position_menu_defaults')
            )
            BEGIN
                DROP INDEX [position_menu_defaults_position_ket_unique] ON [position_menu_defaults];
            END
        ");

        DB::statement("
            IF EXISTS (
                SELECT 1
                FROM sys.indexes
                WHERE name = 'position_menu_defaults_position_index'
                  AND object_id = OBJECT_ID(N'position_menu_defaults')
            )
            BEGIN
                DROP INDEX [position_menu_defaults_position_index] ON [position_menu_defaults];
            END
        ");

        DB::statement('ALTER TABLE position_menu_defaults ALTER COLUMN [position] NVARCHAR(120) NOT NULL');
        DB::statement('ALTER TABLE position_menu_defaults ALTER COLUMN [ket] NVARCHAR(120) NOT NULL');
        DB::statement('ALTER TABLE position_menu_defaults ALTER COLUMN [kunci] NVARCHAR(255) NULL');
        DB::statement('ALTER TABLE position_menu_defaults ALTER COLUMN [source_user_code] NVARCHAR(80) NULL');

        DB::statement("
            IF NOT EXISTS (
                SELECT 1
                FROM sys.indexes
                WHERE name = 'position_menu_defaults_position_ket_unique'
                  AND object_id = OBJECT_ID(N'position_menu_defaults')
            )
            BEGIN
                CREATE UNIQUE INDEX [position_menu_defaults_position_ket_unique]
                ON [position_menu_defaults]([position], [ket]);
            END
        ");

        DB::statement("
            IF NOT EXISTS (
                SELECT 1
                FROM sys.indexes
                WHERE name = 'position_menu_defaults_position_index'
                  AND object_id = OBJECT_ID(N'position_menu_defaults')
            )
            BEGIN
                CREATE INDEX [position_menu_defaults_position_index]
                ON [position_menu_defaults]([position]);
            END
        ");

        DB::statement("
            INSERT INTO position_menu_defaults (position, ket, kunci, source_user_code, created_at, updated_at)
            SELECT src.position,
                   src.ket,
                   src.kunci,
                   src.source_user_code,
                   GETDATE(),
                   GETDATE()
            FROM (
                SELECT RTRIM(s.Nama) AS position,
                       RTRIM(s2.Ket) AS ket,
                       RTRIM(MAX(s2.Kunci)) AS kunci,
                       RTRIM(MIN(s.Kode)) AS source_user_code
                FROM dbo.SANDI s
                INNER JOIN dbo.SANDI2 s2 ON RTRIM(s2.Kode) = RTRIM(s.Kode)
                WHERE RTRIM(s2.Boleh) = '*'
                GROUP BY RTRIM(s.Nama), RTRIM(s2.Ket)
            ) src
            WHERE NOT EXISTS (
                SELECT 1
                FROM position_menu_defaults defaults
                WHERE RTRIM(defaults.position) = src.position
                  AND RTRIM(defaults.ket) = src.ket
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('position_menu_defaults');
    }
};
