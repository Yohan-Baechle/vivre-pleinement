<?php

namespace App\Http\Controllers;

use App\Models\AppointmentService;
use App\Models\BookOrder;
use App\Models\Product;
use App\Services\BookPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Stripe\Exception\ApiErrorException;
use Symfony\Component\HttpFoundation\Response;

class BookController extends Controller
{
    /**
     * Slugs des deux produits vendus depuis la page du livre. C'est le seul
     * contrat entre le catalogue et le tunnel d'achat : les renommer en admin
     * casse le paiement (visiblement, en 404, jamais en silence).
     *
     * @var list<string>
     */
    private const OFFER_SLUGS = ['livre', 'livre-coaching'];

    public function show(): View
    {
        return view('book.index', [
            'offers' => $this->offers(),
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
     * Sert le fichier vendu depuis le disque privé. Le token de la commande
     * fait office de clé, et le statut est revérifié à chaque requête : un
     * remboursement coupe le lien immédiatement, même déjà distribué.
     */
    public function download(BookOrder $order): Response
    {
        abort_unless($order->isPaid(), 403, 'Cette commande ne donne pas accès au téléchargement.');

        $media = $order->product->getFirstMedia('download');

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
     * @return array<string, Product>
     */
    private function offers(): array
    {
        return Product::query()
            ->whereIn('slug', self::OFFER_SLUGS)
            ->where('is_active', true)
            ->get()
            ->keyBy('slug')
            ->all();
    }

    private function resolveOffer(string $offer): Product
    {
        abort_unless(in_array($offer, self::OFFER_SLUGS, true), 404);

        return Product::query()
            ->where('slug', $offer)
            ->where('is_active', true)
            ->firstOrFail();
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
