# Tester le paiement Stripe des rendez-vous

Procédure pour valider le flux de paiement de bout en bout en **mode test** Stripe,
avant la mise en production.

## 1. Récupérer les clés de test

1. Crée/ouvre ton compte sur [dashboard.stripe.com](https://dashboard.stripe.com).
2. Active le **mode Test** (interrupteur en haut à droite).
3. Dans **Développeurs → Clés API**, copie :
   - **Clé publiable** `pk_test_...`
   - **Clé secrète** `sk_test_...`

Renseigne-les dans `.env` :

```env
STRIPE_KEY=pk_test_xxx
STRIPE_SECRET=sk_test_xxx
CASHIER_CURRENCY=eur
CASHIER_CURRENCY_LOCALE=fr_FR
```

Puis : `vendor/bin/sail artisan config:clear`

## 2. Brancher le webhook en local (Stripe CLI)

Le webhook confirme le RDV après paiement. En local, on utilise la Stripe CLI.

```bash
# Installer la CLI : https://stripe.com/docs/stripe-cli
stripe login
stripe listen --forward-to localhost/stripe/webhook
```

La commande affiche un secret `whsec_...` **propre à cette session**. Copie-le dans `.env` :

```env
STRIPE_WEBHOOK_SECRET=whsec_xxx
```

Puis `vendor/bin/sail artisan config:clear` et **laisse `stripe listen` tourner** dans un terminal.

> ⚠️ Ce `whsec_` de la CLI est différent de celui d'un endpoint créé dans le Dashboard.
> Pour la prod, voir §5.

## 3. Tester un RDV payant

1. Assure-toi qu'une prestation **payante** existe (ex. « Séance individuelle », 70 €).
   Sinon, dans `/espace-pro → Rendez-vous → Prestations`, mets un prix > 0.
2. Va sur `/reservation`, choisis la séance payante, un créneau, remplis le formulaire.
3. Tu es redirigé vers **Stripe Checkout**. Paie avec la carte de test :
   - Numéro : `4242 4242 4242 4242`
   - Date : n'importe quelle date future · CVC : 3 chiffres · Code postal : 5 chiffres
4. Après paiement → redirection vers la page de **confirmation**.
5. Vérifie :
   - Terminal `stripe listen` : event `checkout.session.completed` reçu, réponse `200`.
   - `/espace-pro → Rendez-vous` : le RDV est **Confirmé** + **Payé**.
   - Emails (Mailpit, `localhost:8025` avec Sail) : confirmation client + notification Laura.

### Cartes de test utiles
| Scénario | Carte |
|---|---|
| Paiement réussi | `4242 4242 4242 4242` |
| 3D Secure requis | `4000 0027 6000 3184` |
| Paiement refusé | `4000 0000 0000 0002` |

## 4. Tester les cas limites

- **Abandon du paiement** : sur Checkout, clique « Retour ». Tu arrives sur
  `/reservation/paiement-annule/...`. Le RDV reste `unpaid`/`Pending` (non confirmé).
- **RDV gratuit** : réserver le « RDV découverte » (0 €) ne passe pas par Stripe – confirmation directe.
- **Double-booking au paiement** (rare) : si le créneau est pris pendant le paiement, le
  webhook rembourse automatiquement et envoie l'email « créneau plus disponible ».

## 4 bis. Activer PayPal (via Stripe)

PayPal s'affiche sur la même page Checkout que la carte. Le code l'active déjà
(`payment_method_types: ['card', 'paypal']`) ; il reste à l'activer côté Stripe :

1. Dashboard Stripe → **Paramètres → Moyens de paiement** (Settings → Payment methods).
2. Active **PayPal** (en mode Test d'abord, puis Live).
3. C'est tout : le client verra « Carte » + « PayPal » sur l'écran de paiement.

> ⚠️ Conditions Stripe pour PayPal : compte Stripe éligible, **devise EUR** (OK ici),
> et selon le pays du compte. Si PayPal n'apparaît pas sur le Checkout, c'est presque
> toujours qu'il n'est pas activé dans le dashboard ou non disponible pour ta devise/pays.
>
> L'argent PayPal arrive sur ton **compte Stripe** (pas ton compte PayPal Business) –
> c'est le compromis de cette approche, choisi pour sa simplicité.

En test, PayPal propose un compte sandbox pour simuler le paiement. Le webhook
`checkout.session.completed` est identique quel que soit le moyen de paiement :
le reste du flux (confirmation, emails, remboursement) ne change pas.

## 5. Passage en production

1. Dans le Dashboard Stripe (**mode Live**), crée un endpoint webhook :
   - URL : `https://TON-DOMAINE/stripe/webhook`
   - Événement minimum : `checkout.session.completed`
   - (ou via `vendor/bin/sail artisan cashier:webhook` qui crée l'endpoint avec les events Cashier)
2. Copie le **signing secret** de cet endpoint dans le `.env` de prod (`STRIPE_WEBHOOK_SECRET`).
3. Mets les clés **Live** (`pk_live_`, `sk_live_`).
4. Vérifie que le **worker de queue** tourne (les emails sont en file `database`) :
   `php artisan queue:work` (ou Supervisor/Horizon).
5. Vérifie que le **cron** est actif pour les rappels :
   `* * * * * cd /chemin && php artisan schedule:run >> /dev/null 2>&1`

## Dépannage

- **`api_key cannot be the empty string`** : clés Stripe absentes du `.env` (config à vider).
- **Signature webhook invalide** : mauvais `STRIPE_WEBHOOK_SECRET` (celui de la CLI ≠ celui du Dashboard).
- **RDV reste `unpaid` après paiement** : le webhook n'arrive pas → vérifier que `stripe listen`
  tourne (local) ou que l'endpoint est bien configuré (prod), et regarder `storage/logs/laravel.log`.
- **Emails non reçus** : le worker de queue ne tourne pas (`queue:work`).
