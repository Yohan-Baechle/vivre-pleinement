<x-mail::message>
# Nouveau commentaire à valider

**Auteur :** {{ $authorName }}\
**Article :** {{ $postTitle }}

---

{!! nl2br(e($body)) !!}

---

Ce commentaire est **en attente** et n'apparaîtra sur le site qu'après votre validation.

<x-mail::button :url="$moderationUrl">
Modérer les commentaires
</x-mail::button>

Notification envoyée depuis vivre-pleinement.fr.
</x-mail::message>
