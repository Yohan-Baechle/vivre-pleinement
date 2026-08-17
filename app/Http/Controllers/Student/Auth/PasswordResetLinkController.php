<?php

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentPasswordResetLinkFormRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('student.auth.forgot-password');
    }

    /**
     * Envoie le lien de réinitialisation.
     *
     * La réponse est volontairement identique que l'adresse existe ou non : un
     * message d'erreur différencié transformerait ce formulaire en oracle
     * permettant d'énumérer les comptes élèves.
     */
    public function store(StudentPasswordResetLinkFormRequest $request): RedirectResponse
    {
        Password::broker('students')->sendResetLink($request->only('email'));

        return back()->with('status', __(Password::ResetLinkSent));
    }
}
