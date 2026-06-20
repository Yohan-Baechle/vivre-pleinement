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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_service_id')->constrained()->restrictOnDelete();
            $table->string('reference')->unique();
            $table->string('customer_first_name');
            $table->string('customer_last_name')->nullable();
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('status')->default('confirmed');
            $table->unsignedInteger('price_cents')->default(0);
            $table->string('payment_status')->default('unpaid');
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('starts_at');
            $table->index(['starts_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
