<?php

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Renvoie le lien de vérification à l'élève connecté.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user('student')->hasVerifiedEmail()) {
            return redirect()->intended(route('student.dashboard'));
        }

        $request->user('student')->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
