<x-mail::message>
# Nouvelle vente de formation

Une formation vient d'être achetée.

**Formation :** {{ $enrollment->course->title }}\
**Élève :** {{ $enrollment->student->name }} ({{ $enrollment->student->email }})\
**Montant :** {{ \Illuminate\Support\Number::currency($enrollment->amount_paid_cents / 100, in: 'EUR', locale: 'fr') }}\
**Date :** {{ $enrollment->purchased_at?->locale('fr')->isoFormat('D MMMM YYYY à H\hi') }}

</x-mail::message>
