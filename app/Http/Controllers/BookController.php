<?php

namespace App\Http\Controllers;

use App\Models\AppointmentService;
use App\Models\BookOrder;
use App\Models\Product;
use App\Services\BookPaymentService;
use App\Support\BookOffers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Stripe\Exception\ApiErrorException;
use Symfony\Component\HttpFoundation\Response;

class BookController extends Controller
{
    public function __construct(
        private BookOffers $offers,
    ) {}

    public function show(): View
    {
        return view('book.index', [
            'offers' => $this->offers->active()->all(),
        ]);
    }

    /**
     * Formulaire de coordonnées. L'achat se fait en invité : le livre est
     * livré par email, un compte n'apporterait rien à l'acheteur.
     */
    public function checkout(string $offer): View
    {
        $product = $this->resolveOffer($offer);

        return view('book.checkout', ['product' => $product]);
    }

    /**
     * Crée la commande puis redirige vers le paiement. Le montant est figé
     * depuis le produit, jamais depuis la requête.
     */
    public function start(Request $request, string $offer): RedirectResponse
    {
        $product = $this->resolveOffer($offer);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email:rfc,dns', 'max:160'],
            'consent' => ['accepted'],
            'website' => ['prohibited'],
        ], [
            'first_name.required' => 'Votre prénom est requis.',
            'last_name.required' => 'Votre nom est requis.',
            'email.required' => 'Votre email est requis.',
            'email.email' => 'Cet email n\'est pas valide.',
            'consent.accepted' => 'Vous devez accepter le traitement de vos données.',
            'website.prohibited' => 'Erreur de soumission.',
        ]);

        if ($this->isRateLimited($request)) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'Trop de tentatives. Merci de réessayer dans quelques minutes.']);
        }

        $order = BookOrder::query()->create([
            'product_id' => $product->id,
            'customer_first_name' => $validated['first_name'],
            'customer_last_name' => $validated['last_name'],
            'customer_email' => $validated['email'],
            'amount_cents' => $product->price_cents,
            'currency' => $product->currency ?? 'EUR',
        ]);

        return redirect()->route('book.pay', $order->token);
    }

    public function pay(BookOrder $order, BookPaymentService $payments): View|RedirectResponse
    {
        if ($order->isPaid()) {
            return redirect()->route('book.success', $order->token);
        }

        /**
         * Le fichier a pu disparaître entre la création de la commande et le
         * paiement : on ne débite pas pour un contenu devenu indisponible.
         */
        if (! $order->product->isDeliverable()) {
            return redirect()
                ->route('book.show')
                ->with('status', 'Cette formule n\'est pas disponible à la vente pour le moment. Écrivez-moi et je vous préviens dès sa remise en ligne.');
        }

        try {
            $intent = $payments->getOrCreatePaymentIntent($order);
        } catch (ApiErrorException $e) {
            report($e);

            abort(503, 'Le paiement est momentanément indisponible. Merci de réessayer dans quelques instants.');
        }

        return view('book.pay', [
            'order' => $order->load('product'),
            'clientSecret' => $intent->client_secret,
            'stripeKey' => config('cashier.key'),
        ]);
    }

    public function success(BookOrder $order): View
    {
        return view('book.success', ['order' => $order->load('product')]);
    }

    /**
     * Sert le fichier vendu depuis le disque privé.
     *
     * Deux verrous indépendants : la signature, qui borne la durée de vie du
     * lien distribué par email, et le statut de la commande, revérifié à
     * chaque requête pour qu'un remboursement coupe l'accès immédiatement.
     *
     * Une signature périmée renvoie vers la page de commande plutôt que sur
     * une erreur : l'acheteur y régénère un lien frais sans rien demander.
     */
    public function download(Request $request, BookOrder $order): Response|RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return redirect()
                ->route('book.success', $order->token)
                ->with('status', 'Votre lien de téléchargement avait expiré, en voici un nouveau.');
        }

        abort_unless($order->isPaid(), 403, 'Cette commande ne donne pas accès au téléchargement.');

        $media = $order->product->deliverableMedia();

        abort_if($media === null, 404, 'Le fichier n\'est pas encore disponible.');

        return response()->download($media->getPath(), $media->file_name);
    }

    /**
     * Ouvre la réservation de l'heure de coaching incluse dans la formule
     * accompagnée. Le lien vaut une seule fois : dès qu'un rendez-vous est
     * rattaché à la commande, il renvoie vers ce rendez-vous.
     */
    public function coaching(BookOrder $order): View|RedirectResponse
    {
        abort_unless($order->isPaid() && $order->includesCoaching(), 403);

        if ($order->coaching_appointment_id !== null) {
            return redirect()->route('booking.confirmation', $order->coachingAppointment->token);
        }

        $service = AppointmentService::query()->where('is_active', true)->firstOrFail();

        return view('book.coaching', [
            'order' => $order,
            'service' => $service,
        ]);
    }

    /**
     * Une offre n'est achetable que si elle est active *et* livrable. Le
     * 404 est volontaire : la page du livre n'affiche déjà plus de lien vers
     * une offre indisponible, n'y arrive donc qu'une URL forcée ou un lien
     * périmé.
     */
    private function resolveOffer(string $offer): Product
    {
        $product = $this->offers->find($offer);

        abort_if($product === null, 404);
        abort_unless($product->isDeliverable(), 404);

        return $product;
    }

    /**
     * Une commande non payée reste sans effet, mais rien n'empêcherait
     * d'inonder la table depuis un formulaire public.
     */
    private function isRateLimited(Request $request): bool
    {
        $key = 'book-checkout:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 10)) {
            return true;
        }

        RateLimiter::hit($key, 900);

        return false;
    }
}
