<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->foreignId('member_id')->constrained('members', 'member_id')->restrictOnDelete();
            $table->unsignedBigInteger('event_id')->nullable();    // FK to events is added when that table exists
            $table->enum('payment_type', ['Dues', 'Event Ticket', 'Merch']);
            $table->enum('status', ['Pending', 'Completed', 'Failed', 'Refunded'])->default('Pending');
            $table->decimal('amount', 10, 2);
            $table->string('academic_term', 20);                   // for example 2026-2027
            $table->string('provider_reference')->nullable()->unique();
            $table->timestamps();
            $table->index(['member_id', 'payment_type', 'status']);
        });

        DB::statement('ALTER TABLE payments ADD CONSTRAINT payments_amount_check CHECK (amount >= 0)');

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(<<<'SQL'
                CREATE UNIQUE INDEX payments_one_completed_dues_per_term
                ON payments (member_id, academic_term)
                WHERE payment_type = 'Dues' AND status = 'Completed'
            SQL);
        } else {
            DB::statement(<<<'SQL'
                ALTER TABLE payments ADD COLUMN completed_dues_term VARCHAR(50)
                GENERATED ALWAYS AS (CASE WHEN payment_type = 'Dues' AND status = 'Completed' THEN CONCAT(member_id, '_', academic_term) ELSE NULL END) STORED,
                ADD UNIQUE INDEX payments_one_completed_dues_per_term (completed_dues_term)
            SQL);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS payments_one_completed_dues_per_term');
        }
        Schema::dropIfExists('payments');
    }
};

