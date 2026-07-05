<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function edit(Request $request): View
    {
        return view('student.account.edit', [
            'student' => $request->user('student'),
        ]);
    }

    /**
     * Met à jour le nom et l'e-mail. Un changement d'e-mail repasse le compte en
     * « non vérifié » et renvoie un lien de confirmation.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var Student $student */
        $student = $request->user('student');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('students', 'email')->ignore($student->id)],
        ]);

        $emailChanged = $validated['email'] !== $student->email;

        $student->fill($validated);

        if ($emailChanged) {
            $student->email_verified_at = null;
        }

        $student->save();

        if ($emailChanged) {
            $student->sendEmailVerificationNotification();

            return back()->with('status', 'profile-updated-email-verification');
        }

        return back()->with('status', 'profile-updated');
    }

    /**
     * Change le mot de passe après vérification du mot de passe actuel.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var Student $student */
        $student = $request->user('student');

        $validated = $request->validate([
            'current_password' => ['required', 'current_password:student'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $student->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}
