<?php

namespace App\Http\Middleware;

use App\Models\Course;
use App\Models\Lesson;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEnrolled
{
    /**
     * Autorise l'accès si l'élève possède une inscription active à la
     * formation, ou si la leçon ciblée est un aperçu gratuit. Sinon, renvoie
     * vers la page de vente.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $course = $request->route('course');
        $lesson = $request->route('lesson');

        if ($lesson instanceof Lesson && $lesson->is_free_preview) {
            return $next($request);
        }

        $student = $request->user('student');

        if ($course instanceof Course && $student !== null && $student->hasAccessTo($course)) {
            return $next($request);
        }

        if ($course instanceof Course) {
            return redirect()->route('courses.show', $course)
                ->with('status', "Vous n'avez pas encore accès à cette formation.");
        }

        return redirect()->route('courses.index');
    }
}
