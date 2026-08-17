<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Les scopes `published()` de Post, Video et Course filtrent `status` ET
 * `published_at`, puis trient sur `published_at`. Deux index à colonne unique
 * obligent le moteur à n'en retenir qu'un et à traiter le reste hors index.
 *
 * L'index composite couvre le filtre et le tri d'un seul parcours. L'index
 * `status` seul devient alors redondant : il est le préfixe gauche du nouvel
 * index, qui le remplace pour toute requête ne filtrant que sur `status`.
 * L'index `published_at` seul est conservé, il sert aux tris sans filtre de
 * statut (sitemaps, flux RSS).
 */
return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $tables = ['posts', 'videos', 'courses'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                $blueprint->index(['status', 'published_at'], "{$table}_status_published_at_index");
                $blueprint->dropIndex("{$table}_status_index");
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                $blueprint->index('status', "{$table}_status_index");
                $blueprint->dropIndex("{$table}_status_published_at_index");
            });
        }
    }
};
