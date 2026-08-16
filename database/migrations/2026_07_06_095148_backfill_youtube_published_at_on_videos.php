<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('videos', 'published_at')) {
            DB::statement('UPDATE videos SET youtube_published_at = published_at WHERE youtube_published_at IS NULL');
        }
    }

    /**
     * Reverse the migrations.
     *
     * Pure data backfill with no schema changes to undo; the original
     * `youtube_published_at` values it overwrote cannot be recovered, so
     * there is nothing meaningful to reverse here.
     */
    public function down(): void {}
};
