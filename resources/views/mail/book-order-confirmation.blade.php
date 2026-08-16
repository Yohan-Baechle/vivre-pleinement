<x-mail::message>
# Votre livre vous attend

Bonjour {{ $order->customer_first_name }},

Votre paiement a bien été reçu. Merci de votre confiance.

**Commande :** {{ $order->product->name }}\
**Référence :** {{ $order->reference }}\
**Montant :** {{ \Illuminate\Support\Number::currency($order->amount_cents / 100, in: 'EUR', locale: 'fr') }}

@if ($order->product->isDeliverable())
Le lien ci-dessous vous est personnel et reste valable
{{ \App\Models\BookOrder::DOWNLOAD_LINK_DAYS }} jours. Passé ce délai, il vous
en proposera automatiquement un nouveau : gardez simplement cet email.

<x-mail::button :url="$order->downloadUrl()">
Télécharger le livre
</x-mail::button>
@else
Je finalise la préparation de votre fichier et vous l'envoie très vite,
directement par email.
@endif

@if ($order->canBookCoaching())
## Votre heure de coaching

Votre formule comprend une heure d'accompagnement individuel. Elle est déjà
réglée : il ne reste qu'à choisir le créneau qui vous arrange.

<x-mail::button :url="route('book.coaching', $order->token)" color="success">
Réserver ma séance
</x-mail::button>

Ce lien vous est personnel et ne vaut que pour une réservation.
@endif

Si rien dans ce livre ne vous parle, répondez simplement à cet email : je vous
rembourse, sans justification.

À très bientôt,
Laura Baechlé
</x-mail::message>
