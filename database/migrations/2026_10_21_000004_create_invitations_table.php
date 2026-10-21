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
        Schema::create('invitations', function (Blueprint $table) {
            $table->id('invitation_id');
            $table->foreignId('member_id')->constrained('members', 'member_id')->restrictOnDelete();
            $table->string('invitation_token')->unique();          // SHA-256 hash, never the plain token
            $table->enum('status', ['Pending', 'Sent', 'Form_Submitted', 'Expired', 'Revoked'])->default('Pending');
            $table->timestamp('expires_at');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->index(['member_id', 'status']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(<<<'SQL'
                CREATE UNIQUE INDEX invitations_one_open_per_member
                ON invitations (member_id) WHERE status IN ('Pending', 'Sent')
            SQL);
        } else {
            DB::statement(<<<'SQL'
                ALTER TABLE invitations ADD COLUMN open_member_id BIGINT UNSIGNED
                GENERATED ALWAYS AS (CASE WHEN status IN ('Pending', 'Sent') THEN member_id ELSE NULL END) STORED,
                ADD UNIQUE INDEX invitations_one_open_per_member (open_member_id)
            SQL);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS invitations_one_open_per_member');
        }
        Schema::dropIfExists('invitations');
    }
};

