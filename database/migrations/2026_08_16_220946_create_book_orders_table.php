<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('token', 64)->unique();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('customer_first_name');
            $table->string('customer_last_name');
            $table->string('customer_email');
            $table->unsignedInteger('amount_cents');
            $table->string('currency', 3)->default('EUR');
            $table->string('status')->default('pending')->index();
            $table->string('stripe_payment_intent_id')->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();

            /**
             * Rendez-vous de coaching consommé par la formule accompagnée. Le
             * lien de réservation envoyé par email ne vaut que tant que cette
             * colonne est nulle : c'est ce qui le rend à usage unique.
             */
            $table->foreignId('coaching_appointment_id')
                ->nullable()
                ->constrained('appointments')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['customer_email', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_orders');
    }
};
