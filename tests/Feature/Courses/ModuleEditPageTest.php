<?php

use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

test('la page edit du module se charge sans erreur', function () {
    $admin = User::factory()->create();
    $module = Module::factory()->create();
    $this->actingAs($admin, 'web')
        ->get("/espace-pro/modules/{$module->id}/edit")
        ->assertOk()
        ->assertSee($module->title);
})->uses(LazilyRefreshDatabase::class);
