<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('setup')) {
            return;
        }

        Schema::table('setup', function (Blueprint $table) {
            if (!Schema::hasColumn('setup', 'LogoPT')) {
                $table->string('LogoPT', 255)->nullable();
            }

            if (!Schema::hasColumn('setup', 'BrandingUpdatedAt')) {
                $table->string('BrandingUpdatedAt', 30)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('setup')) {
            return;
        }

        Schema::table('setup', function (Blueprint $table) {
            if (Schema::hasColumn('setup', 'LogoPT')) {
                $table->dropColumn('LogoPT');
            }

            if (Schema::hasColumn('setup', 'BrandingUpdatedAt')) {
                $table->dropColumn('BrandingUpdatedAt');
            }
        });
    }
};
