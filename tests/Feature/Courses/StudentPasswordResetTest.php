<?php

use App\Models\Student;
use App\Notifications\StudentResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

it('envoie le lien de réinitialisation à un élève existant', function () {
    Notification::fake();
    $student = Student::factory()->create(['email' => 'camille@example.com']);

    $this->post(route('student.password.email'), ['email' => 'camille@example.com'])
        ->assertSessionHas('status');

    Notification::assertSentTo($student, StudentResetPassword::class);
});

it('ne révèle pas si un email est inconnu via une notification', function () {
    Notification::fake();
    Student::factory()->create(['email' => 'camille@example.com']);

    $this->post(route('student.password.email'), ['email' => 'inconnu@example.com']);

    Notification::assertNothingSent();
});

it('réinitialise le mot de passe avec un token valide et permet la connexion', function () {
    $student = Student::factory()->create(['email' => 'camille@example.com']);
    $token = Password::broker('students')->createToken($student);

    $this->post(route('student.password.update'), [
        'token' => $token,
        'email' => 'camille@example.com',
        'password' => 'nouveau-mot-de-passe',
        'password_confirmation' => 'nouveau-mot-de-passe',
    ])->assertRedirect(route('student.login'));

    expect(Hash::check('nouveau-mot-de-passe', $student->fresh()->password))->toBeTrue();

    $this->post(route('student.login.store'), [
        'email' => 'camille@example.com',
        'password' => 'nouveau-mot-de-passe',
    ])->assertRedirect(route('student.dashboard'));

    expect(auth('student')->check())->toBeTrue();
});

it('refuse un token invalide sans changer le mot de passe', function () {
    $student = Student::factory()->create(['email' => 'camille@example.com']);
    $originalPassword = $student->password;

    $this->post(route('student.password.update'), [
        'token' => 'token-invalide',
        'email' => 'camille@example.com',
        'password' => 'nouveau-mot-de-passe',
        'password_confirmation' => 'nouveau-mot-de-passe',
    ])->assertSessionHasErrors('email');

    expect($student->fresh()->password)->toBe($originalPassword);
});

it('affiche le formulaire de réinitialisation depuis le lien reçu', function () {
    $this->get(route('student.password.reset', ['token' => 'abc123']))
        ->assertOk()
        ->assertSee('abc123', false);
});

it('n\'utilise pas le broker des administrateurs pour les élèves', function () {
    Notification::fake();
    $student = Student::factory()->create(['email' => 'camille@example.com']);

    $this->post(route('student.password.email'), ['email' => 'camille@example.com']);

    $this->assertDatabaseHas('student_password_reset_tokens', ['email' => 'camille@example.com'])
        ->assertDatabaseMissing('password_reset_tokens', ['email' => 'camille@example.com']);
});
