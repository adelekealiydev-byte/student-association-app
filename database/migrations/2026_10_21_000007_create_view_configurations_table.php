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
        Schema::create('view_configurations', function (Blueprint $table) {
            $table->id('view_config_id');
            $table->unsignedSmallInteger('role_id');
            $table->foreign('role_id')->references('role_id')->on('roles')->cascadeOnDelete();
            $table->string('view_name');
            $table->json('config_json')->nullable();
            $table->timestamps();
            $table->unique(['role_id', 'view_name']);              // one config per role and view
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('view_configurations');
    }
};

