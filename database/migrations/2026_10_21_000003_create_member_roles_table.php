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
        Schema::create('member_roles', function (Blueprint $table) {
            $table->foreignId('member_id')->constrained('members', 'member_id')->cascadeOnDelete();
            $table->unsignedSmallInteger('role_id');
            $table->foreign('role_id')->references('role_id')->on('roles')->restrictOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('members', 'member_id')->nullOnDelete();
            $table->timestamps();
            $table->primary(['member_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_roles');
    }
};

