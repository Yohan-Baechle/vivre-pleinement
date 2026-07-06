<?php

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(LazilyRefreshDatabase::class);

it('creates the admin user from configured credentials, not raw env() at runtime', function () {
    // Simule ce que fournit config/admin.php une fois passé par config:cache :
    // le seeder ne doit lire que config(), jamais env() directement (qui
    // renverrait null en production une fois la config mise en cache).
    config([
        'admin.email' => 'admin-test@example.com',
        'admin.name' => 'Test Admin',
        'admin.password' => 'super-secret',
    ]);

    (new AdminUserSeeder)->run();

    $user = User::where('email', 'admin-test@example.com')->firstOrFail();

    expect($user->name)->toBe('Test Admin')
        ->and(Hash::check('super-secret', $user->password))->toBeTrue();
});

it('is idempotent — running it twice updates rather than duplicates the admin user', function () {
    config([
        'admin.email' => 'admin-test@example.com',
        'admin.name' => 'First Name',
        'admin.password' => 'first-secret',
    ]);
    (new AdminUserSeeder)->run();

    config(['admin.name' => 'Second Name', 'admin.password' => 'second-secret']);
    (new AdminUserSeeder)->run();

    expect(User::where('email', 'admin-test@example.com')->count())->toBe(1);

    $user = User::where('email', 'admin-test@example.com')->firstOrFail();
    expect($user->name)->toBe('Second Name')
        ->and(Hash::check('second-secret', $user->password))->toBeTrue();
});
