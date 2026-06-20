<x-mail::message>
# Rendez-vous annulé

@if ($forAdmin)
Le rendez-vous suivant a été annulé par le client.

**Client :** {{ $appointment->customer_full_name }}
**Email :** [{{ $appointment->customer_email }}](mailto:{{ $appointment->customer_email }})
@else
Bonjour {{ $appointment->customer_first_name }},

Votre rendez-vous a bien été annulé. Voici les détails de ce qui était prévu :
@endif

**Prestation :** {{ $appointment->service->name }}
**Date :** {{ $appointment->starts_at->locale('fr')->isoFormat('dddd D MMMM YYYY') }} à {{ $appointment->starts_at->format('H\hi') }}
**Référence :** {{ $appointment->reference }}

@unless ($forAdmin)
---

Vous changez d'avis ou souhaitez un autre créneau ? Vous pouvez reprendre rendez-vous à tout moment.

<x-mail::button :url="route('booking.index')">
Reprendre rendez-vous
</x-mail::button>

À très bientôt,
Laura Baechlé
@endunless
</x-mail::message>
