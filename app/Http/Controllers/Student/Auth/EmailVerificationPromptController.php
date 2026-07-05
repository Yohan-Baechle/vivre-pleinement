<?php

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Affiche l'invitation à vérifier l'e-mail, ou redirige vers le tableau de
     * bord si l'adresse est déjà confirmée.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        return $request->user('student')->hasVerifiedEmail()
            ? redirect()->intended(route('student.dashboard'))
            : view('student.auth.verify-email');
    }
}
