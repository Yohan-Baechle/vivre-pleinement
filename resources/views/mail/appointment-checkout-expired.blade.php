<x-mail::message>
# Votre réservation n'a pas été finalisée

Bonjour {{ $appointment->customer_first_name }},

Vous aviez commencé à réserver un rendez-vous, mais le paiement n'a pas été finalisé. Nous avons donc libéré le créneau afin qu'il reste disponible pour vous ou pour d'autres personnes.

Voici le rendez-vous qui était en cours de réservation :

**Prestation :** {{ $appointment->service->name }}\
**Créneau :** {{ $appointment->starts_at->locale('fr')->isoFormat('dddd D MMMM YYYY') }} à {{ $appointment->starts_at->format('H\hi') }}

Aucun montant ne vous a été débité. Si vous le souhaitez toujours, vous pouvez reprendre votre réservation en quelques clics :

<x-mail::button :url="route('booking.index')">
Reprendre rendez-vous
</x-mail::button>

Si vous avez rencontré une difficulté lors du paiement, répondez simplement à cet email : je suis là pour vous aider.

À très bientôt,
Laura Baechlé
</x-mail::message>
