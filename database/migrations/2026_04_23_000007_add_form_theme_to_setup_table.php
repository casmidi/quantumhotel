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
            if (!Schema::hasColumn('setup', 'FormTheme')) {
                $table->string('FormTheme', 40)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('setup')) {
            return;
        }

        Schema::table('setup', function (Blueprint $table) {
            if (Schema::hasColumn('setup', 'FormTheme')) {
                $table->dropColumn('FormTheme');
            }
        });
    }
};
