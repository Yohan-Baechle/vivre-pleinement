<?php

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(LazilyRefreshDatabase::class);

it('creates the admin user from configured credentials, not raw env() at runtime', function () {
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

it('refuses to seed an admin account without an explicit password', function () {
    config(['admin.email' => 'admin-test@example.com', 'admin.password' => null]);

    expect(fn () => (new AdminUserSeeder)->run())
        ->toThrow(RuntimeException::class);

    $this->assertDatabaseMissing('users', ['email' => 'admin-test@example.com']);
});

it('exposes no default password in the shipped configuration', function () {
    expect(config('admin.password'))->toBeNull();
})->skip(fn () => filled(env('ADMIN_PASSWORD')), 'ADMIN_PASSWORD est défini dans cet environnement.');

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
