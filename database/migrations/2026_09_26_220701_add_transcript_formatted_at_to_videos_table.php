<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute la date de mise en forme de la transcription.
     *
     * Une transcription brute tient en un seul paragraphe, celle reponctuée
     * par l'étape IA en compte plusieurs : c'est ce qui sert à rattraper les
     * vidéos déjà traitées à la main.
     */
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table): void {
            $table->timestamp('transcript_formatted_at')
                ->nullable()
                ->after('transcript');
        });

        DB::table('videos')
            ->where('transcript', 'like', '%</p>%<p>%')
            ->update(['transcript_formatted_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table): void {
            $table->dropColumn('transcript_formatted_at');
        });
    }
};
