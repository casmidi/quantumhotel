<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->normalizeIdColumn('KELAS', 'Kode');
        $this->normalizeIdColumn('ROOM', 'Kode');
        $this->normalizeIdColumn('StockPackage', 'KodeBrg');
        $this->normalizeIdColumn('Package', 'Nofak');
        $this->normalizeIdColumn('DATA', 'RegNo');
        $this->normalizeIdColumn('DATA2', 'RegNo2');
    }

    public function down(): void
    {
        $this->dropIdDefaultAndSequence('KELAS');
        $this->dropIdDefaultAndSequence('ROOM');
        $this->dropIdDefaultAndSequence('StockPackage');
        $this->dropIdDefaultAndSequence('Package');
        $this->dropIdDefaultAndSequence('DATA');
        $this->dropIdDefaultAndSequence('DATA2');
    }

    private function normalizeIdColumn(string $table, string $businessKey): void
    {
        $sequence = "seq_{$table}_id";
        $defaultConstraint = "DF_{$table}_id";

        DB::statement("
            IF COL_LENGTH('{$table}', 'id') IS NULL
            BEGIN
                ALTER TABLE [{$table}] ADD [id] INT IDENTITY(1,1) NOT NULL;
            END
            ELSE
            BEGIN
                DECLARE @isIdentity INT = COLUMNPROPERTY(OBJECT_ID(N'{$table}'), 'id', 'IsIdentity');

                IF ISNULL(@isIdentity, 0) = 0
                BEGIN
                    DECLARE @maxExisting INT;
                    SELECT @maxExisting = ISNULL(MAX([id]), 0)
                    FROM [{$table}]
                    WHERE [id] IS NOT NULL AND [id] > 0;

                    ;WITH MissingIds AS (
                        SELECT
                            [{$businessKey}] AS [BusinessKey],
                            ROW_NUMBER() OVER (ORDER BY [{$businessKey}]) + @maxExisting AS [NewId]
                        FROM [{$table}]
                        WHERE [id] IS NULL OR [id] = 0
                    )
                    UPDATE tgt
                    SET tgt.[id] = src.[NewId]
                    FROM [{$table}] AS tgt
                    INNER JOIN MissingIds AS src
                        ON tgt.[{$businessKey}] = src.[BusinessKey];

                    IF EXISTS (
                        SELECT 1
                        FROM sys.columns
                        WHERE object_id = OBJECT_ID(N'{$table}')
                          AND name = 'id'
                          AND is_nullable = 1
                    )
                    BEGIN
                        ALTER TABLE [{$table}] ALTER COLUMN [id] INT NOT NULL;
                    END

                    DECLARE @nextId INT;
                    SELECT @nextId = ISNULL(MAX([id]), 0) + 1 FROM [{$table}];

                    IF OBJECT_ID(N'dbo.{$sequence}', N'SO') IS NULL
                    BEGIN
                        EXEC('CREATE SEQUENCE dbo.{$sequence} AS INT START WITH ' + CAST(@nextId AS VARCHAR(20)) + ' INCREMENT BY 1');
                    END
                    ELSE
                    BEGIN
                        EXEC('ALTER SEQUENCE dbo.{$sequence} RESTART WITH ' + CAST(@nextId AS VARCHAR(20)));
                    END

                    IF EXISTS (
                        SELECT 1
                        FROM sys.default_constraints
                        WHERE name = '{$defaultConstraint}'
                          AND parent_object_id = OBJECT_ID(N'{$table}')
                    )
                    BEGIN
                        ALTER TABLE [{$table}] DROP CONSTRAINT [{$defaultConstraint}];
                    END

                    ALTER TABLE [{$table}] ADD CONSTRAINT [{$defaultConstraint}] DEFAULT (NEXT VALUE FOR dbo.{$sequence}) FOR [id];
                END
            END
        ");
    }

    private function dropIdDefaultAndSequence(string $table): void
    {
        $sequence = "seq_{$table}_id";
        $defaultConstraint = "DF_{$table}_id";

        DB::statement("
            IF EXISTS (
                SELECT 1
                FROM sys.default_constraints
                WHERE name = '{$defaultConstraint}'
                  AND parent_object_id = OBJECT_ID(N'{$table}')
            )
            BEGIN
                ALTER TABLE [{$table}] DROP CONSTRAINT [{$defaultConstraint}];
            END
        ");

        DB::statement("
            IF OBJECT_ID(N'dbo.{$sequence}', N'SO') IS NOT NULL
            BEGIN
                DROP SEQUENCE dbo.{$sequence};
            END
        ");
    }
};
