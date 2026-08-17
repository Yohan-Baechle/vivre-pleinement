<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
        $courses = Course::query()
            ->published()
            ->with('media')
            ->orderBy('position')
            ->orderByDesc('published_at')
            ->get();

        return view('courses.index', [
            'courses' => $courses,
        ]);
    }

    public function show(Course $course): View
    {
        abort_unless($course->isPublished(), 404);

        $course->load([
            'media',
            'modules.lessons' => fn ($query) => $query->orderBy('position'),
        ]);

        $student = auth('student')->user();

        return view('courses.show', [
            'course' => $course,
            'hasAccess' => $student !== null && $student->hasAccessTo($course),
        ]);
    }
}
