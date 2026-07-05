<?php

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredStudentController extends Controller
{
    public function create(Request $request): View
    {
        return view('student.auth.register', [
            'intendedCourse' => $request->query('course'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:students,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $student = Student::create($validated);

        event(new Registered($student));

        Auth::guard('student')->login($student);

        return redirect($this->intendedUrl($request));
    }

    /**
     * Redirige vers la page de vente de la formation visée si elle est connue,
     * sinon vers le tableau de bord élève.
     */
    private function intendedUrl(Request $request): string
    {
        $courseSlug = $request->input('course');

        if (is_string($courseSlug) && $courseSlug !== '') {
            return route('courses.show', $courseSlug);
        }

        return route('student.dashboard');
    }
}
