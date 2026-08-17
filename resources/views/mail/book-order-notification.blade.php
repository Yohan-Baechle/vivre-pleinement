<x-mail::message>
# Nouvelle commande du livre

**Formule :** {{ $order->product->name }}\
**Montant :** {{ \Illuminate\Support\Number::currency($order->amount_cents / 100, in: 'EUR', locale: 'fr') }}\
**Référence :** {{ $order->reference }}

**Client :** {{ $order->customerName() }}\
**Email :** {{ $order->customer_email }}

@if ($order->includesCoaching())
Cette formule comprend **1 h de coaching**. Le lien de réservation à usage
unique est parti avec l'email de confirmation.
@endif

@if (! $order->product->isDeliverable())
⚠️ **Aucun fichier n'est rattaché à ce produit** : le client n'a pas pu
télécharger son livre. Ajoutez le PDF dans la fiche produit, puis renvoyez-lui
le lien.
@endif

<x-mail::button :url="route('filament.admin.resources.book-orders.index')">
Voir les commandes
</x-mail::button>
</x-mail::message>
