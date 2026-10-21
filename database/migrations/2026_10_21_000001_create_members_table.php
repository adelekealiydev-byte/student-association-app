<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id('member_id');
            $table->string('email')->unique();
            $table->string('student_number')->unique();
            $table->string('username')->nullable()->unique();      // set when the form is submitted
            $table->string('password_hash')->nullable();           // set when the form is submitted
            $table->enum('membership_status', ['Pending Invite', 'Active', 'Suspended', 'Alumni', 'Expired'])
                  ->default('Pending Invite')->index();
            $table->boolean('is_verified')->default(false);        // the gate: see InvitationService
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};

