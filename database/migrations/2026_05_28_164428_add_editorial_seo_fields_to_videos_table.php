<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table): void {
            $table->string('seo_description', 320)->nullable()->after('description');
            $table->text('summary')->nullable()->after('seo_description');
            $table->json('key_takeaways')->nullable()->after('summary');
            $table->longText('transcript')->nullable()->after('key_takeaways');
            $table->json('chapters')->nullable()->after('transcript');
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table): void {
            $table->dropColumn(['seo_description', 'summary', 'key_takeaways', 'transcript', 'chapters']);
        });
    }
};
