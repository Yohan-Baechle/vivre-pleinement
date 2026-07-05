<?php

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\CoursePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseCheckoutController extends Controller
{
    /**
     * Démarre l'achat : crée (ou réutilise) une inscription en attente puis
     * redirige vers la page de paiement Stripe.
     */
    public function start(Request $request, Course $course): RedirectResponse
    {
        abort_unless($course->isPublished(), 404);

        $student = $request->user('student');

        if ($student->hasAccessTo($course)) {
            return redirect()->route('student.course', $course);
        }

        Enrollment::query()->firstOrCreate(
            ['student_id' => $student->id, 'course_id' => $course->id],
            ['status' => EnrollmentStatus::Pending, 'currency' => $course->currency],
        );

        return redirect()->route('courses.checkout.pay', $course);
    }

    public function pay(Request $request, Course $course, CoursePaymentService $payments): View|RedirectResponse
    {
        abort_unless($course->isPublished(), 404);

        $student = $request->user('student');

        if ($student->hasAccessTo($course)) {
            return redirect()->route('student.course', $course);
        }

        $enrollment = Enrollment::query()
            ->where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

        $intent = $payments->createPaymentIntent($enrollment);

        return view('courses.pay', [
            'course' => $course,
            'clientSecret' => $intent->client_secret,
            'stripeKey' => config('cashier.key'),
        ]);
    }

    public function success(Request $request, Course $course): View
    {
        $student = $request->user('student');

        return view('courses.success', [
            'course' => $course,
            'hasAccess' => $student->hasAccessTo($course),
        ]);
    }
}
