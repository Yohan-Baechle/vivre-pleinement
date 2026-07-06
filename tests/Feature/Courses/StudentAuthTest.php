<?php

use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('inscrit un nouvel élève sur le guard student', function () {
    $response = $this->post(route('student.register.store'), [
        'name' => 'Camille Test',
        'email' => 'camille@example.com',
        'password' => 'motdepasse-solide',
        'password_confirmation' => 'motdepasse-solide',
    ]);

    $response->assertRedirect(route('student.dashboard'));
    $this->assertDatabaseHas('students', ['email' => 'camille@example.com']);
    expect(auth('student')->check())->toBeTrue();
    expect(auth('web')->check())->toBeFalse();
});

it('redirige vers la formation visée après inscription', function () {
    $course = Course::factory()->create(['slug' => 'gerer-anxiete']);

    $this->post(route('student.register.store'), [
        'name' => 'Léa',
        'email' => 'lea@example.com',
        'password' => 'motdepasse-solide',
        'password_confirmation' => 'motdepasse-solide',
        'course' => 'gerer-anxiete',
    ])->assertRedirect(route('courses.show', $course));
});

it('connecte un élève existant', function () {
    Student::factory()->create(['email' => 'pierre@example.com']);

    $this->post(route('student.login.store'), [
        'email' => 'pierre@example.com',
        'password' => 'password',
    ])->assertRedirect(route('student.dashboard'));

    expect(auth('student')->check())->toBeTrue();
});

it('refuse une connexion avec un mauvais mot de passe', function () {
    Student::factory()->create(['email' => 'pierre@example.com']);

    $this->from(route('student.login'))
        ->post(route('student.login.store'), [
            'email' => 'pierre@example.com',
            'password' => 'mauvais-mot-de-passe',
        ])
        ->assertRedirect(route('student.login'))
        ->assertSessionHasErrors('email');

    expect(auth('student')->check())->toBeFalse();
});

it('verrouille la connexion après cinq tentatives échouées', function () {
    $student = Student::factory()->create(['email' => 'pierre@example.com']);

    foreach (range(1, 5) as $attempt) {
        $this->post(route('student.login.store'), [
            'email' => 'pierre@example.com',
            'password' => 'mauvais-mot-de-passe',
        ])->assertSessionHasErrors('email');
    }

    $this->post(route('student.login.store'), [
        'email' => 'pierre@example.com',
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    expect(auth('student')->check())->toBeFalse();
});

it('ne verrouille pas un élève après une connexion réussie entre deux échecs', function () {
    Student::factory()->create(['email' => 'pierre@example.com']);

    foreach (range(1, 4) as $attempt) {
        $this->post(route('student.login.store'), [
            'email' => 'pierre@example.com',
            'password' => 'mauvais-mot-de-passe',
        ]);
    }

    $this->post(route('student.login.store'), [
        'email' => 'pierre@example.com',
        'password' => 'password',
    ])->assertRedirect(route('student.dashboard'));

    expect(auth('student')->check())->toBeTrue();
});

it('déconnecte un élève', function () {
    $student = Student::factory()->create();

    $this->actingAs($student, 'student')
        ->post(route('student.logout'))
        ->assertRedirect(route('courses.index'));

    expect(auth('student')->check())->toBeFalse();
});

it("empêche un élève d'accéder au panneau d'administration", function () {
    $student = Student::factory()->create();

    $this->actingAs($student, 'student')
        ->get('/espace-pro')
        ->assertRedirect();

    expect(auth('web')->check())->toBeFalse();
});

it("empêche un administrateur d'accéder au tableau de bord élève", function () {
    $admin = User::factory()->create();

    $this->actingAs($admin, 'web')
        ->get(route('student.dashboard'))
        ->assertRedirect(route('student.login'));
});

it('redirige un visiteur non connecté vers la connexion élève', function () {
    $this->get(route('student.dashboard'))
        ->assertRedirect(route('student.login'));
});
