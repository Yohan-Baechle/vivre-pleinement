<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Post::query()
            ->withTrashed()
            ->whereNull('reading_time_minutes')
            ->select(['id', 'content'])
            ->chunkById(200, function ($posts) {
                foreach ($posts as $post) {
                    $post->newQuery()
                        ->whereKey($post->id)
                        ->update(['reading_time_minutes' => Post::computeReadingTimeMinutes((string) $post->content)]);
                }
            });
    }

    /**
     * Reverse the migrations. Rien à défaire : reading_time_minutes redevient
     * simplement calculable à la volée si non renseigné.
     */
    public function down(): void {}
};
