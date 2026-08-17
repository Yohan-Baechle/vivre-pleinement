<?php

use App\Models\Student;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

uses(LazilyRefreshDatabase::class);

/**
 * Une session volée doit mourir avec le mot de passe qu'elle a servi à ouvrir :
 * changer son mot de passe est le geste réflexe de la victime, il ne doit pas
 * laisser l'attaquant connecté.
 */
it('déconnecte les autres sessions quand le mot de passe change', function () {
    $student = Student::factory()->create(['password' => Hash::make('ancien-mot-de-passe')]);

    $this->actingAs($student, 'student')
        ->get(route('student.account.edit'))
        ->assertOk();

    $stolenSession = $this->app['session.store']->all();

    $this->put(route('student.account.password'), [
        'current_password' => 'ancien-mot-de-passe',
        'password' => 'nouveau-mot-de-passe-solide',
        'password_confirmation' => 'nouveau-mot-de-passe-solide',
    ])->assertSessionHas('status', 'password-updated');

    $this->flushSession();
    $this->session($stolenSession);

    $this->get(route('student.account.edit'))->assertRedirect(route('student.login'));
});

it('laisse la session courante active après son propre changement de mot de passe', function () {
    $student = Student::factory()->create(['password' => Hash::make('ancien-mot-de-passe')]);

    $this->actingAs($student, 'student')
        ->put(route('student.account.password'), [
            'current_password' => 'ancien-mot-de-passe',
            'password' => 'nouveau-mot-de-passe-solide',
            'password_confirmation' => 'nouveau-mot-de-passe-solide',
        ])->assertSessionHas('status', 'password-updated');

    $this->get(route('student.account.edit'))->assertOk();
});

it('déconnecte les autres sessions après une réinitialisation de mot de passe', function () {
    $student = Student::factory()->create();

    $this->actingAs($student, 'student')
        ->get(route('student.account.edit'))
        ->assertOk();

    $stolenSession = $this->app['session.store']->all();

    $token = Password::broker('students')->createToken($student);

    /**
     * La réinitialisation se fait depuis un autre navigateur, déconnecté :
     * la route est réservée aux invités du guard « student ».
     */
    auth('student')->logout();
    $this->flushSession();

    $this->post(route('student.password.update'), [
        'token' => $token,
        'email' => $student->email,
        'password' => 'mot-de-passe-reinitialise',
        'password_confirmation' => 'mot-de-passe-reinitialise',
    ])->assertRedirect(route('student.login'));

    $this->flushSession();
    $this->session($stolenSession);

    $this->get(route('student.account.edit'))->assertRedirect(route('student.login'));
});

it('ne perturbe pas un visiteur non connecté', function () {
    $this->get(route('courses.index'))->assertOk();
});
