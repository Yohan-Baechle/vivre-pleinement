<?php

use App\Models\Student;
use App\Notifications\StudentVerifyEmail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

uses(LazilyRefreshDatabase::class);

it('met en file d\'attente la notification de vérification d\'e-mail', function () {
    expect(new StudentVerifyEmail)->toBeInstanceOf(ShouldQueue::class);
});

it('redirige un élève non vérifié vers la page de confirmation depuis le tableau de bord', function () {
    $student = Student::factory()->unverified()->create();

    $this->actingAs($student, 'student')
        ->get(route('student.dashboard'))
        ->assertRedirect(route('student.verification.notice'));
});

it('laisse un élève vérifié accéder au tableau de bord', function () {
    $student = Student::factory()->create();

    $this->actingAs($student, 'student')
        ->get(route('student.dashboard'))
        ->assertOk();
});

it('vérifie l\'e-mail via le lien signé', function () {
    $student = Student::factory()->unverified()->create();

    $url = URL::temporarySignedRoute(
        'student.verification.verify',
        now()->addMinutes(60),
        ['id' => $student->id, 'hash' => sha1($student->getEmailForVerification())],
    );

    Event::fake();

    $this->actingAs($student, 'student')
        ->get($url)
        ->assertRedirect();

    expect($student->refresh()->hasVerifiedEmail())->toBeTrue();
    Event::assertDispatched(Verified::class);
});

it('rejette un lien de vérification au hash invalide', function () {
    $student = Student::factory()->unverified()->create();

    $url = URL::temporarySignedRoute(
        'student.verification.verify',
        now()->addMinutes(60),
        ['id' => $student->id, 'hash' => sha1('mauvais@example.com')],
    );

    $this->actingAs($student, 'student')
        ->get($url)
        ->assertForbidden();

    expect($student->refresh()->hasVerifiedEmail())->toBeFalse();
});

it('renvoie un lien de vérification à la demande', function () {
    Notification::fake();

    $student = Student::factory()->unverified()->create();

    $this->actingAs($student, 'student')
        ->post(route('student.verification.send'))
        ->assertSessionHas('status', 'verification-link-sent');

    Notification::assertSentTo($student, StudentVerifyEmail::class);
});

it('envoie la notification de vérification à l\'inscription', function () {
    Notification::fake();

    $this->post(route('student.register.store'), [
        'name' => 'Nouvelle Élève',
        'email' => 'nouvelle@example.com',
        'password' => 'motdepasse-solide',
        'password_confirmation' => 'motdepasse-solide',
    ]);

    $student = Student::where('email', 'nouvelle@example.com')->firstOrFail();
    Notification::assertSentTo($student, StudentVerifyEmail::class);
});
