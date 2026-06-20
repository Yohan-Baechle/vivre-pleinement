<x-mail::message>
# Nouveau message depuis le site

**Objet :** {{ $subjectLabel }}

**De :** {{ $firstName }} {{ $lastName }}
**Email :** [{{ $email }}](mailto:{{ $email }})
@if ($phone)
**Téléphone :** {{ $phone }}
@endif

---

{!! nl2br(e($messageBody)) !!}

---

<x-mail::button :url="'mailto:'.$email">
Répondre à {{ $firstName }}
</x-mail::button>

Message envoyé depuis le formulaire de contact de vivre-pleinement.fr.
</x-mail::message>
