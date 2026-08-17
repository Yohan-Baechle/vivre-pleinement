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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable();
            $table->longText('description')->nullable();
            $table->json('outcomes')->nullable();
            $table->unsignedInteger('price_cents');
            $table->string('currency', 3)->default('EUR');
            $table->string('intro_video_provider')->nullable();
            $table->string('intro_video_id')->nullable();
            $table->string('level')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
