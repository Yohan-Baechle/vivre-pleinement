# Déploiement privé — VM Proxmox / Docker derrière Tailscale

Mise à disposition de l'application à une cliente, sans indexation Google ni
accès public : l'app n'est joignable que via le réseau privé Tailscale (tailnet).

## Principe

L'application tourne dans des conteneurs Docker sur une VM Proxmox. Le port n'est
exposé que sur l'interface Tailscale de la VM. Google ne peut pas indexer ce qu'il
ne peut pas atteindre : aucune URL publique n'existe. Aucun mot de passe à gérer,
l'isolement réseau suffit.

## Prérequis sur la VM

- Docker + Docker Compose installés
- Tailscale installé et connecté (`tailscale up`)
- Le dépôt cloné sur la VM

## Étapes

### 1. Cloner le dépôt

```bash
git clone git@github.com:Yohan-Baechle/vivre-pleinement.git
cd vivre-pleinement
```

### 2. Configurer l'environnement

```bash
cp .env.production.example .env
```

Éditer `.env` et définir au minimum :

- `DB_PASSWORD` — un mot de passe fort
- `APP_URL` — le nom Tailscale de la VM, ex. `http://ma-vm.ton-tailnet.ts.net:8080`
- `BIND_IP` — l'IP Tailscale de la VM (`tailscale ip -4`) pour n'exposer que sur le tailnet

`APP_KEY` est laissée vide : elle est générée automatiquement au premier démarrage.

### 3. Construire et démarrer

```bash
docker compose -f compose.prod.yaml up -d --build
```

Au premier démarrage, l'entrypoint attend MariaDB, joue les migrations, charge le
contenu éditorial (`seed.sql` via le seeder), crée le lien des médias et met les
caches en place. Le seed n'est joué qu'une fois (témoin `storage/app/.seeded`).

### 4. Suivre l'initialisation

```bash
docker compose -f compose.prod.yaml logs -f app
```

Attendre la ligne `🚀 Application prête.`

### 5. Donner l'accès à la cliente

1. Inviter la cliente sur le tailnet depuis l'admin Tailscale (partage d'appareil
   ou invitation utilisateur).
2. Elle installe Tailscale et accepte l'invitation.
3. Elle ouvre l'`APP_URL` (nom MagicDNS de la VM).

## Exploitation

| Action | Commande |
|---|---|
| Voir les logs | `docker compose -f compose.prod.yaml logs -f` |
| Redémarrer | `docker compose -f compose.prod.yaml restart` |
| Arrêter | `docker compose -f compose.prod.yaml down` |
| Mettre à jour le code | `git pull && docker compose -f compose.prod.yaml up -d --build` |
| Recharger le contenu depuis zéro | supprimer le volume `mariadb-data` + le témoin, puis rebuild |
| Compte admin | créé par le seeder (voir `AdminUserSeeder`), accès sur `/espace-pro` |

## Passer en public plus tard

Quand le site sera prêt pour le vrai lancement, il faudra : un hébergement public,
un nom de domaine, le HTTPS, les vraies clés Stripe/YouTube/SMTP, et autoriser
l'indexation dans `public/robots.txt`. Ce déploiement Tailscale reste utile comme
environnement de préproduction privé.
