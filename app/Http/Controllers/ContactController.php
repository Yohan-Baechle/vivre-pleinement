<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactFormRequest;
use App\Mail\ContactMessage;
use App\Support\SubmissionThrottle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('contact.index');
    }

    public function send(ContactFormRequest $request): RedirectResponse
    {
        $key = 'contact:'.$request->ip();
        if (SubmissionThrottle::exceeded($key)) {
            $seconds = SubmissionThrottle::availableIn($key);

            return back()
                ->withInput($request->except(['website', 'consent', 'ts']))
                ->withErrors(['message' => "Trop d'envois. Réessayez dans {$seconds}s."]);
        }
        SubmissionThrottle::hit($key);

        $data = $request->validated();

        Mail::to(config('mail.contact_to', 'contact@vivre-pleinement.fr'))->send(
            new ContactMessage(
                firstName: $data['first_name'],
                lastName: $data['last_name'] ?? null,
                email: $data['email'],
                phone: $data['phone'] ?? null,
                subjectLabel: $request->subjectLabel(),
                messageBody: $data['message'],
            )
        );

        return redirect()->route('contact.thanks');
    }

    public function thanks(): View
    {
        return view('contact.thanks');
    }
}
