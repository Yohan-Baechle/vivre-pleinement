<x-mail::message>
# Toutes mes excuses

Bonjour {{ $appointment->customer_first_name }},

Malheureusement, le créneau que vous aviez choisi pour **{{ $appointment->service->name }}**
({{ $appointment->starts_at->locale('fr')->isoFormat('dddd D MMMM') }} à {{ $appointment->starts_at->format('H\hi') }})
vient d'être réservé par quelqu'un d'autre au même moment.

@if ($refunded)
**Votre paiement a été intégralement remboursé.** Le remboursement apparaîtra sur votre relevé sous quelques jours.
@endif

Je suis sincèrement désolée pour ce contretemps. Vous pouvez choisir un nouveau créneau quand vous le souhaitez :

<x-mail::button :url="route('booking.show', $appointment->service->slug)">
Choisir un autre créneau
</x-mail::button>

Encore toutes mes excuses,
Laura Baechlé
</x-mail::message>
