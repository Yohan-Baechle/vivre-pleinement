<x-mail::message>
# Nouvelle réservation

**Prestation :** {{ $appointment->service->name }}\
**Format :** {{ $appointment->channel->getLabel() }}\
**Statut :** {{ $appointment->status->getLabel() }}\
**Date :** {{ $appointment->starts_at->locale('fr')->isoFormat('dddd D MMMM YYYY') }}\
**Heure :** {{ $appointment->starts_at->format('H:i') }} - {{ $appointment->ends_at->format('H:i') }}\
**Référence :** {{ $appointment->reference }}

---

@if ($appointment->customer_phone)
**Client :** {{ $appointment->customer_full_name }}\
**Email :** [{{ $appointment->customer_email }}](mailto:{{ $appointment->customer_email }})\
**Téléphone :** {{ $appointment->customer_phone }}
@else
**Client :** {{ $appointment->customer_full_name }}\
**Email :** [{{ $appointment->customer_email }}](mailto:{{ $appointment->customer_email }})
@endif

@if ($appointment->notes)
**Message :**

{!! nl2br(e($appointment->notes)) !!}
@endif

<x-mail::button :url="route('filament.admin.resources.appointments.edit', $appointment)">
Voir dans l'espace pro
</x-mail::button>
</x-mail::message>
