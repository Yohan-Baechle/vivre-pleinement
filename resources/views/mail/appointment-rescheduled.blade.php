<x-mail::message>
# {{ $forAdmin ? 'Rendez-vous reprogrammé' : 'Votre rendez-vous a été déplacé' }}

@if ($forAdmin)
Le client a reprogrammé son rendez-vous.

**Client :** {{ $appointment->customer_full_name }}
**Email :** [{{ $appointment->customer_email }}](mailto:{{ $appointment->customer_email }})
@else
Bonjour {{ $appointment->customer_first_name }},

C'est noté : votre rendez-vous a bien été déplacé. Voici votre nouveau créneau.
@endif

**Prestation :** {{ $appointment->service->name }}
**Ancien créneau :** {{ $previousStart->locale('fr')->isoFormat('dddd D MMMM YYYY') }} à {{ $previousStart->format('H\hi') }}
**Nouveau créneau :** {{ $appointment->starts_at->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
**Heure :** {{ $appointment->starts_at->format('H:i') }} - {{ $appointment->ends_at->format('H:i') }}
@if ($appointment->meeting_url)
**Lien visio :** [{{ $appointment->meeting_url }}]({{ $appointment->meeting_url }})
@endif
**Référence :** {{ $appointment->reference }}

@unless ($forAdmin)
@if ($appointment->token)
<x-mail::button :url="route('booking.manage', $appointment->token)">
Gérer mon rendez-vous
</x-mail::button>
@endif

À très bientôt,
Laura Baechlé
@else
<x-mail::button :url="route('filament.admin.resources.appointments.edit', $appointment)">
Voir dans l'espace pro
</x-mail::button>
@endunless
</x-mail::message>
