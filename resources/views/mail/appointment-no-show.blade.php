<x-mail::message>
# On vous a manqué

Bonjour {{ $appointment->customer_first_name }},

Vous aviez rendez-vous le {{ $appointment->starts_at->locale('fr')->isoFormat('dddd D MMMM YYYY') }} à {{ $appointment->starts_at->format('H\hi') }}, mais nous n'avons pas pu nous retrouver à ce moment-là.

Ce n'est pas grave : un imprévu, un oubli, ça arrive à tout le monde. Si vous le souhaitez toujours, je serais ravie de convenir d'un nouveau créneau qui vous convient mieux.

<x-mail::button :url="route('booking.index')">
Reprendre rendez-vous
</x-mail::button>

Et si quelque chose vous a empêché de venir ou vous fait hésiter, vous pouvez simplement répondre à cet email : nous en parlerons ensemble, sans pression.

À très bientôt, je l'espère,
Laura Baechlé
</x-mail::message>
