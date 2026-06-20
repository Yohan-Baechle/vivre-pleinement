<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->timestamp('youtube_published_at')->nullable()->after('thumbnail_url');
            $table->boolean('is_missing')->default(false)->after('synced_at');
            $table->json('sync_locked_fields')->nullable()->after('is_missing');
        });

        if (Schema::hasColumn('videos', 'published_at')) {
            DB::statement('UPDATE videos SET youtube_published_at = published_at WHERE youtube_published_at IS NULL');
        }

        Schema::table('category_video', function (Blueprint $table) {
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn(['youtube_published_at', 'is_missing', 'sync_locked_fields']);
        });

        Schema::table('category_video', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
};
