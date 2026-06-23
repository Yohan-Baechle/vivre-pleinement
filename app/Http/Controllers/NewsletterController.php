<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewsletterFormRequest;
use App\Support\BrevoNewsletter;
use App\Support\SubmissionThrottle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class NewsletterController extends Controller
{
    public function store(NewsletterFormRequest $request, BrevoNewsletter $newsletter): RedirectResponse|JsonResponse
    {
        $key = 'newsletter:'.$request->ip();
        if (SubmissionThrottle::exceeded($key)) {
            $seconds = SubmissionThrottle::availableIn($key);

            return $this->failure($request, "Trop d'envois. Réessayez dans {$seconds}s.");
        }
        SubmissionThrottle::hit($key);

        $data = $request->validated();

        try {
            $newsletter->subscribeToVideoList(
                email: $data['email'],
                firstName: $data['first_name'],
                redirectionUrl: route('newsletter.confirmed'),
            );
        } catch (RuntimeException $e) {
            Log::warning('Échec inscription newsletter Brevo.', ['message' => $e->getMessage()]);

            return $this->failure($request, "L'inscription a échoué. Réessayez dans un instant.");
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'pending']);
        }

        return redirect()->to(route('home').'#capture')->with('newsletter_status', 'pending');
    }

    /**
     * Réponse d'échec : JSON pour les requêtes AJAX, redirection sinon.
     */
    private function failure(NewsletterFormRequest $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['errors' => ['email' => [$message]]], 422);
        }

        return redirect()
            ->to(route('home').'#capture')
            ->withInput($request->only(['first_name', 'email']))
            ->withErrors(['email' => $message]);
    }
}
