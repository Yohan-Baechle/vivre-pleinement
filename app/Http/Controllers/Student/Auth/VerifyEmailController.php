<?php

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class VerifyEmailController extends Controller
{
    /**
     * Valide le lien signé et marque l'adresse e-mail de l'élève comme vérifiée.
     */
    public function __invoke(Request $request, string $id, string $hash): RedirectResponse
    {
        $student = Student::findOrFail($id);

        if (! URL::hasValidSignature($request)) {
            abort(403);
        }

        if (! hash_equals($hash, sha1($student->getEmailForVerification()))) {
            abort(403);
        }

        if ($student->hasVerifiedEmail()) {
            return redirect()->intended(route('student.dashboard').'?verified=1');
        }

        if ($student->markEmailAsVerified()) {
            event(new Verified($student));
        }

        return redirect()->intended(route('student.dashboard').'?verified=1');
    }
}
