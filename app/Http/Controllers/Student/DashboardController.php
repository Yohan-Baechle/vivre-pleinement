<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Support\CourseProgress;
use App\Support\StudentAnonymizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user('student');

        $courses = $student->courses()
            ->with('media')
            ->orderByPivot('purchased_at', 'desc')
            ->get();

        $progress = CourseProgress::percentForCourses($student, $courses);

        return view('student.dashboard', [
            'student' => $student,
            'courses' => $courses,
            'progress' => $progress,
        ]);
    }

    /**
     * Anonymise le compte élève (droit à l'effacement RGPD) tout en conservant
     * les inscriptions pour les obligations comptables. Voir StudentAnonymizer.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $student = $request->user('student');

        StudentAnonymizer::anonymize($student);

        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('courses.index')
            ->with('status', 'Votre compte a été supprimé. À bientôt.');
    }
}
