<?php

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterStudentFormRequest;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredStudentController extends Controller
{
    public function create(Request $request): View
    {
        return view('student.auth.register', [
            'intendedCourse' => $request->query('course'),
        ]);
    }

    public function store(RegisterStudentFormRequest $request): RedirectResponse
    {
        $student = Student::create($request->validated());

        event(new Registered($student));

        Auth::guard('student')->login($student);

        return redirect($this->intendedUrl($request));
    }

    /**
     * Redirige vers la page de vente de la formation visée si elle est connue,
     * sinon vers le tableau de bord élève. Le slug reçu du formulaire n'est
     * suivi que s'il correspond à une formation publiée : sans cette
     * vérification, n'importe quelle valeur atterrirait dans l'URL de
     * redirection.
     */
    private function intendedUrl(Request $request): string
    {
        $courseSlug = $request->string('course')->trim()->value();

        if ($courseSlug !== '' && Course::query()->published()->where('slug', $courseSlug)->exists()) {
            return route('courses.show', $courseSlug);
        }

        return route('student.dashboard');
    }
}
