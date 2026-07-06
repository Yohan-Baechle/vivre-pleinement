<?php

use App\Models\Student;
use App\Notifications\StudentVerifyEmail;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

uses(LazilyRefreshDatabase::class);

it('affiche la page Mon compte', function () {
    $student = Student::factory()->create();

    $this->actingAs($student, 'student')
        ->get(route('student.account.edit'))
        ->assertOk()
        ->assertSee('Mon compte');
});

it('met à jour le nom sans toucher à la vérification e-mail', function () {
    $student = Student::factory()->create(['name' => 'Ancien Nom']);

    $this->actingAs($student, 'student')
        ->patch(route('student.account.profile'), [
            'name' => 'Nouveau Nom',
            'email' => $student->email,
        ])
        ->assertRedirect()
        ->assertSessionHas('status', 'profile-updated');

    $student->refresh();
    expect($student->name)->toBe('Nouveau Nom');
    expect($student->hasVerifiedEmail())->toBeTrue();
});

it('repasse le compte en non vérifié et renvoie un lien quand l\'e-mail change', function () {
    Notification::fake();

    $student = Student::factory()->create(['email' => 'avant@example.com']);

    $this->actingAs($student, 'student')
        ->patch(route('student.account.profile'), [
            'name' => $student->name,
            'email' => 'apres@example.com',
        ])
        ->assertSessionHas('status', 'profile-updated-email-verification');

    $student->refresh();
    expect($student->email)->toBe('apres@example.com');
    expect($student->hasVerifiedEmail())->toBeFalse();

    Notification::assertSentTo($student, StudentVerifyEmail::class);
});

it('refuse un e-mail déjà utilisé par un autre élève', function () {
    Student::factory()->create(['email' => 'pris@example.com']);
    $student = Student::factory()->create();

    $this->actingAs($student, 'student')
        ->patch(route('student.account.profile'), [
            'name' => $student->name,
            'email' => 'pris@example.com',
        ])
        ->assertSessionHasErrors('email');
});

it('change le mot de passe avec le mot de passe actuel correct', function () {
    $student = Student::factory()->create(['password' => Hash::make('motdepasse-actuel')]);

    $this->actingAs($student, 'student')
        ->put(route('student.account.password'), [
            'current_password' => 'motdepasse-actuel',
            'password' => 'nouveau-motdepasse-solide',
            'password_confirmation' => 'nouveau-motdepasse-solide',
        ])
        ->assertSessionHas('status', 'password-updated');

    expect(Hash::check('nouveau-motdepasse-solide', $student->refresh()->password))->toBeTrue();
});

it('rejette le changement de mot de passe si le mot de passe actuel est faux', function () {
    $student = Student::factory()->create(['password' => Hash::make('motdepasse-actuel')]);

    $this->actingAs($student, 'student')
        ->put(route('student.account.password'), [
            'current_password' => 'mauvais',
            'password' => 'nouveau-motdepasse-solide',
            'password_confirmation' => 'nouveau-motdepasse-solide',
        ])
        ->assertSessionHasErrors('current_password');

    expect(Hash::check('motdepasse-actuel', $student->refresh()->password))->toBeTrue();
});
