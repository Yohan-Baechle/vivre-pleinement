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

    public function store(StudentPasswordResetLinkFormRequest $request): RedirectResponse
    {
        $status = Password::broker('students')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::ResetLinkSent
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
    }
}
