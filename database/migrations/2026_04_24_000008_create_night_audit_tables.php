<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('night_audit_batches', function (Blueprint $table) {
            $table->id();
            $table->string('audit_no', 40)->unique();
            $table->date('business_date')->unique();
            $table->string('status', 30)->default('Draft');
            $table->time('hotel_day_start_time')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->string('started_by', 80)->nullable();
            $table->string('closed_by', 80)->nullable();
            $table->string('approved_by', 80)->nullable();
            $table->integer('total_rooms')->default(0);
            $table->integer('occupied_rooms')->default(0);
            $table->integer('vacant_rooms')->default(0);
            $table->integer('out_of_order_rooms')->default(0);
            $table->integer('house_use_rooms')->default(0);
            $table->integer('complimentary_rooms')->default(0);
            $table->integer('arrival_count')->default(0);
            $table->integer('departure_count')->default(0);
            $table->integer('in_house_count')->default(0);
            $table->integer('walk_in_count')->default(0);
            $table->decimal('occupancy_percent', 8, 2)->default(0);
            $table->decimal('room_revenue', 18, 2)->default(0);
            $table->decimal('package_revenue', 18, 2)->default(0);
            $table->decimal('other_revenue', 18, 2)->default(0);
            $table->decimal('gross_revenue', 18, 2)->default(0);
            $table->decimal('cash_receipt_total', 18, 2)->default(0);
            $table->decimal('non_cash_receipt_total', 18, 2)->default(0);
            $table->decimal('city_ledger_total', 18, 2)->default(0);
            $table->decimal('deposit_total', 18, 2)->default(0);
            $table->integer('exception_count')->default(0);
            $table->integer('critical_exception_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['business_date', 'status']);
        });

        Schema::create('night_audit_room_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('night_audit_batches')->cascadeOnDelete();
            $table->string('regno', 30)->nullable();
            $table->string('regno2', 60)->nullable();
            $table->string('room_code', 20);
            $table->string('room_class', 80)->nullable();
            $table->string('guest_name', 160)->nullable();
            $table->string('company_name', 160)->nullable();
            $table->string('market_segment', 80)->nullable();
            $table->string('payment_method', 80)->nullable();
            $table->string('package_code', 80)->nullable();
            $table->string('pms_status', 60)->nullable();
            $table->string('housekeeping_status', 60)->nullable();
            $table->dateTime('checkin_at')->nullable();
            $table->date('expected_checkout_date')->nullable();
            $table->integer('pax')->default(0);
            $table->integer('stay_nights')->default(0);
            $table->decimal('room_rate', 18, 2)->default(0);
            $table->decimal('discount_percent', 8, 2)->default(0);
            $table->decimal('net_room_rate', 18, 2)->default(0);
            $table->decimal('estimated_folio_balance', 18, 2)->default(0);
            $table->boolean('is_day_use')->default(false);
            $table->boolean('is_complimentary')->default(false);
            $table->boolean('is_house_use')->default(false);
            $table->string('risk_flag', 40)->nullable();
            $table->text('audit_note')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'room_code']);
            $table->index(['batch_id', 'regno']);
            $table->index(['regno2']);
        });

        Schema::create('night_audit_revenue_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('night_audit_batches')->cascadeOnDelete();
            $table->date('transaction_date');
            $table->string('source_table', 80)->nullable();
            $table->string('source_key', 120)->nullable();
            $table->string('department', 80);
            $table->string('revenue_code', 60)->nullable();
            $table->string('room_code', 20)->nullable();
            $table->string('regno', 30)->nullable();
            $table->string('regno2', 60)->nullable();
            $table->string('guest_name', 160)->nullable();
            $table->string('description', 255);
            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->decimal('net_amount', 18, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            $table->decimal('service_amount', 18, 2)->default(0);
            $table->string('currency', 10)->default('IDR');
            $table->string('status', 30)->default('Preview');
            $table->string('risk_flag', 40)->nullable();
            $table->text('audit_note')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'department']);
            $table->index(['transaction_date']);
            $table->index(['regno', 'regno2']);
        });

        Schema::create('night_audit_cashier_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('night_audit_batches')->cascadeOnDelete();
            $table->string('cashier_code', 80)->default('SYSTEM');
            $table->string('shift_code', 40)->nullable();
            $table->string('payment_type', 80);
            $table->decimal('gross_receipt', 18, 2)->default(0);
            $table->decimal('refund_amount', 18, 2)->default(0);
            $table->decimal('void_amount', 18, 2)->default(0);
            $table->decimal('cash_drop', 18, 2)->default(0);
            $table->decimal('expected_cash', 18, 2)->default(0);
            $table->decimal('variance_amount', 18, 2)->default(0);
            $table->integer('transaction_count')->default(0);
            $table->string('settlement_status', 30)->default('Open');
            $table->string('reviewed_by', 80)->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'payment_type']);
            $table->index(['cashier_code', 'shift_code']);
        });

        Schema::create('night_audit_housekeeping_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('night_audit_batches')->cascadeOnDelete();
            $table->string('room_code', 20);
            $table->string('pms_status', 60)->nullable();
            $table->string('housekeeping_status', 60)->nullable();
            $table->string('reservation_status', 60)->nullable();
            $table->string('exception_type', 80);
            $table->string('severity', 20)->default('Medium');
            $table->string('action_status', 30)->default('Open');
            $table->string('owner_department', 80)->default('Front Office');
            $table->text('notes')->nullable();
            $table->string('resolved_by', 80)->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'severity']);
            $table->index(['room_code', 'action_status']);
        });

        Schema::create('night_audit_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('night_audit_batches')->cascadeOnDelete();
            $table->integer('sequence_no');
            $table->string('section', 80);
            $table->string('task_code', 60);
            $table->string('task_name', 180);
            $table->string('control_level', 30)->default('Standard');
            $table->string('status', 30)->default('Pending');
            $table->string('required_role', 80)->nullable();
            $table->string('completed_by', 80)->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->string('evidence_reference', 180)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['batch_id', 'task_code']);
            $table->index(['batch_id', 'status']);
        });

        Schema::create('night_audit_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('night_audit_batches')->cascadeOnDelete();
            $table->string('adjustment_no', 50)->unique();
            $table->string('regno', 30)->nullable();
            $table->string('regno2', 60)->nullable();
            $table->string('room_code', 20)->nullable();
            $table->string('department', 80);
            $table->string('reason_code', 80);
            $table->string('description', 255);
            $table->decimal('amount', 18, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            $table->decimal('service_amount', 18, 2)->default(0);
            $table->string('approval_status', 30)->default('Draft');
            $table->string('requested_by', 80)->nullable();
            $table->string('approved_by', 80)->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('posted_at')->nullable();
            $table->string('source_reference', 160)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'approval_status']);
            $table->index(['regno', 'regno2']);
        });

        Schema::create('night_audit_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('night_audit_batches')->cascadeOnDelete();
            $table->integer('approval_level')->default(1);
            $table->string('role_name', 80);
            $table->string('approver_name', 80)->nullable();
            $table->string('status', 30)->default('Pending');
            $table->dateTime('approved_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['batch_id', 'approval_level', 'role_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('night_audit_approvals');
        Schema::dropIfExists('night_audit_adjustments');
        Schema::dropIfExists('night_audit_checklists');
        Schema::dropIfExists('night_audit_housekeeping_exceptions');
        Schema::dropIfExists('night_audit_cashier_summaries');
        Schema::dropIfExists('night_audit_revenue_lines');
        Schema::dropIfExists('night_audit_room_snapshots');
        Schema::dropIfExists('night_audit_batches');
    }
};
