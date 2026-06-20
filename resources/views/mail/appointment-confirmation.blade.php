<x-mail::message>
# {{ $appointment->isPending() ? 'Demande de rendez-vous reçue' : 'Rendez-vous confirmé' }}

Bonjour {{ $appointment->customer_first_name }},

@if ($appointment->isPending())
Votre demande de rendez-vous a bien été reçue. Elle est **en attente de confirmation** : vous recevrez un email dès qu'elle sera validée.
@else
Votre rendez-vous est **confirmé**. Voici le récapitulatif :
@endif

**Prestation :** {{ $appointment->service->name }}
**Format :** {{ $appointment->channel->getLabel() }}
**Date :** {{ $appointment->starts_at->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
**Heure :** {{ $appointment->starts_at->format('H:i') }} - {{ $appointment->ends_at->format('H:i') }}
@if ($appointment->channel === \App\Enums\AppointmentChannel::Phone)
**Modalité :** Je vous appellerai au numéro indiqué à l'heure du rendez-vous.
@elseif ($appointment->meeting_url)
**Lien visio :** [{{ $appointment->meeting_url }}]({{ $appointment->meeting_url }})
@else
**Modalité :** En visioconférence (le lien vous sera transmis avant le rendez-vous).
@endif
**Référence :** {{ $appointment->reference }}

@if (! $appointment->service->isFree())
**Tarif :** {{ number_format($appointment->price_cents / 100, 2, ',', ' ') }} €
@endif

@if ($appointment->token)
<x-mail::button :url="route('booking.manage', $appointment->token)">
Gérer ou annuler mon rendez-vous
</x-mail::button>
@endif

Besoin d'aide ? Répondez simplement à cet email.

À très bientôt,
Laura Baechlé
</x-mail::message>
