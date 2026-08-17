<?php

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentNewPasswordFormRequest;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request): View
    {
        return view('student.auth.reset-password', [
            'request' => $request,
            'token' => $request->route('token'),
        ]);
    }

    /**
     * L'échec renvoie un message unique quelle qu'en soit la cause : distinguer
     * « aucun compte pour cette adresse » de « lien expiré » transformerait ce
     * formulaire en oracle d'énumération des comptes élèves, alors même que le
     * formulaire de demande de lien s'en garde déjà.
     */
    public function store(StudentNewPasswordFormRequest $request): RedirectResponse
    {
        $status = Password::broker('students')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($student) use ($request): void {
                $student->forceFill([
                    'password' => Hash::make($request->string('password')),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($student));
            }
        );

        return $status === Password::PasswordReset
            ? redirect()->route('student.login')->with('status', __($status))
            : back()->withInput($request->only('email'))->withErrors([
                'email' => 'Ce lien de réinitialisation n\'est plus valide. Merci d\'en demander un nouveau.',
            ]);
    }
}
