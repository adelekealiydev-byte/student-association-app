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
        Schema::create('id_cards', function (Blueprint $table) {
            $table->id('id_card_id');
            $table->foreignId('member_id')->constrained('members', 'member_id')->restrictOnDelete();
            $table->foreignId('payment_id')->unique()->constrained('payments', 'payment_id')->restrictOnDelete();
            $table->string('card_number')->unique();
            $table->string('qr_code_data');
            $table->boolean('is_eligible')->default(false);        // derived by triggers, never set by hand
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('id_cards');
    }
};

