<x-mail::message>
# Merci pour notre échange

Bonjour {{ $appointment->customer_first_name }},

J'espère que notre rendez-vous (**{{ $appointment->service->name }}**) vous a été utile.

Si vous avez la moindre question, ou si vous souhaitez poursuivre l'accompagnement, n'hésitez pas à me répondre directement.

<x-mail::button :url="route('booking.index')">
Reprendre rendez-vous
</x-mail::button>

Prenez soin de vous,
Laura Baechlé
</x-mail::message>
