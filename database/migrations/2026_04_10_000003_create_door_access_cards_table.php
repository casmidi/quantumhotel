<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('door_access_cards', function (Blueprint $table) {
            $table->id();
            $table->string('regno', 30);
            $table->string('regno2', 60)->unique();
            $table->string('room_code', 10);
            $table->string('guest_name', 120);
            $table->dateTime('checkin_at');
            $table->dateTime('checkout_at');
            $table->dateTime('expires_at');
            $table->decimal('deposit_amount', 18, 2)->default(0);
            $table->string('sector_number', 30)->nullable();
            $table->string('status', 30)->default('Draft');
            $table->string('access_state', 30)->default('Pending');
            $table->string('card_uid', 60)->nullable();
            $table->string('issued_by', 80)->nullable();
            $table->timestamp('written_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['regno', 'room_code']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('door_access_cards');
    }
};