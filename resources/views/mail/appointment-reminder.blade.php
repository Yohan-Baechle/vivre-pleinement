<x-mail::message>
# {{ $when === '1h' ? 'Votre rendez-vous, c\'est bientôt' : 'Petit rappel pour demain' }}

Bonjour {{ $appointment->customer_first_name }},

@if ($when === '1h')
Votre rendez-vous a lieu **dans 1 heure environ**. Voici les détails pour vous y préparer :
@else
Je vous rappelle votre rendez-vous **prévu demain**. Voici le récapitulatif :
@endif

**Prestation :** {{ $appointment->service->name }}
**Date :** {{ $appointment->starts_at->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
**Heure :** {{ $appointment->starts_at->format('H:i') }} - {{ $appointment->ends_at->format('H:i') }}
@if ($appointment->meeting_url)
**Lien visio :** [{{ $appointment->meeting_url }}]({{ $appointment->meeting_url }})
@endif

@if ($appointment->meeting_url)
<x-mail::button :url="$appointment->meeting_url">
Rejoindre la visioconférence
</x-mail::button>
@endif

@if ($appointment->token)
Un empêchement ? [Gérer ou annuler mon rendez-vous]({{ route('booking.manage', $appointment->token) }}).
@endif

À très vite,
Laura Baechlé
</x-mail::message>
