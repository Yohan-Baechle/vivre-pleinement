<x-mail::message>
# Bienvenue dans votre formation

Bonjour {{ $enrollment->student->name }},

Votre paiement a bien été reçu : vous avez désormais **accès à vie** à la formation suivante.

**Formation :** {{ $enrollment->course->title }}\
**Montant :** {{ \Illuminate\Support\Number::currency($enrollment->amount_paid_cents / 100, in: 'EUR', locale: 'fr') }}

Vous pouvez commencer dès maintenant, à votre rythme, depuis votre espace.

<x-mail::button :url="route('student.course', $enrollment->course)">
Accéder à ma formation
</x-mail::button>

Une question ? Répondez simplement à cet email.

À très bientôt,
Laura Baechlé
</x-mail::message>
