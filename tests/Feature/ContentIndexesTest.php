<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(LazilyRefreshDatabase::class);

/**
 * Les listings publics filtrent `status` et `published_at` ensemble puis
 * trient sur `published_at` : sans index composite, le moteur ne peut exploiter
 * qu'une des deux colonnes.
 */
it('indexes status and published_at together on the content tables', function (string $table) {
    $columnSets = collect(Schema::getIndexes($table))->pluck('columns');

    expect($columnSets)->toContain(['status', 'published_at']);
})->with(['posts', 'videos', 'courses']);

/**
 * L'index composite est le préfixe gauche de `status` seul : garder ce dernier
 * ferait payer une écriture d'index supplémentaire sans jamais servir.
 */
it('drops the now redundant single-column status index', function (string $table) {
    $columnSets = collect(Schema::getIndexes($table))->pluck('columns');

    expect($columnSets)->not->toContain(['status']);
})->with(['posts', 'videos', 'courses']);
