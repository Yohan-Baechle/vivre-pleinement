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
        Schema::create('appointment_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('duration_minutes')->default(30);
            $table->unsignedInteger('price_cents')->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->unsignedInteger('buffer_minutes')->default(0);
            $table->unsignedInteger('min_notice_hours')->default(12);
            $table->unsignedInteger('max_advance_days')->default(60);
            $table->boolean('requires_confirmation')->default(false);
            $table->string('color', 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_services');
    }
};
