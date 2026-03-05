# 🚀 Guide de Déploiement — Yateem Backend

## Prérequis

| Outil | Version |
|-------|---------|
| Docker Desktop | ≥ 4.x |
| Docker Compose | ≥ 3.8 |
| Git | ≥ 2.x |

> [!IMPORTANT]
> **Docker Desktop doit être lancé et en cours d'exécution** avant de commencer.

---

## Architecture Docker

| Service | Container | Port | Description |
|---------|-----------|------|-------------|
| PHP-FPM 8.2 | `laravel_app` | 9000 (interne) | Application Laravel |
| Nginx | `nginx_server` | **8000** | Serveur web |
| MySQL 8.0 | `mysql_db` | 3307 → 3306 | Base de données |
| phpMyAdmin | `yateem-pma` | **8080** | Interface DB |

---

## Étape 1 — Cloner le projet

```bash
git clone <URL_DU_REPO> yateem_backend
cd yateem_backend
```

---

## Étape 2 — Configurer l'environnement

```bash
cp .env.example .env
```

Modifier le fichier `.env` avec les valeurs suivantes :

```env
APP_NAME=Yateem
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=yateem
DB_USERNAME=master
DB_PASSWORD=secret

FILESYSTEM_DISK=public

NOTCHPAY_PUBLIC_KEY=votre_cle_publique
NOTCHPAY_PRIVATE_KEY=votre_cle_privee
NOTCHPAY_HASH=votre_hash
```

> [!WARNING]
> En production, mettez `APP_DEBUG=false` et `APP_ENV=production`.

---

## Étape 3 — Construire et démarrer Docker

```bash
# Construire les images (première fois ou après modification du Dockerfile)
docker-compose build

# Démarrer tous les services
docker-compose up -d
```

Vérifier que tout tourne :

```bash
docker-compose ps
```

Vous devriez voir 4 containers : `laravel_app`, `nginx_server`, `mysql_db`, `yateem-pma`.

---

## Étape 4 — Installer l'extension `intl` (requis pour Filament)

```bash
docker exec -u root laravel_app sh -c \
  "apt-get update && apt-get install -y libicu-dev && \
   docker-php-ext-configure intl && \
   docker-php-ext-install intl"
```

> [!NOTE]
> Pour rendre cette installation persistante, ajoutez ces lignes dans le `Dockerfile` avant `RUN apt-get clean`.

---

## Étape 5 — Installer les dépendances PHP

```bash
docker exec laravel_app composer install
```

Si des erreurs de sécurité bloquent l'installation :

```bash
docker exec laravel_app composer install --no-audit
```

---

## Étape 6 — Configurer l'application

```bash
# Générer la clé d'application
docker exec laravel_app php artisan key:generate

# Exécuter les migrations
docker exec laravel_app php artisan migrate

# Créer le lien symbolique pour le stockage (upload d'images)
docker exec laravel_app php artisan storage:link
```

---

## Étape 7 — Installer le Back Office Filament

```bash
# Installer les panels Filament
docker exec laravel_app php artisan filament:install --panels

# Installer Shield (RBAC / gestion des rôles)
docker exec laravel_app php artisan shield:install

# Générer les permissions Shield pour toutes les resources
docker exec laravel_app php artisan shield:generate --all

# Créer les rôles et utilisateurs admin par défaut
docker exec laravel_app php artisan db:seed --class=ShieldSeeder
```

---

## Étape 8 — (Optionnel) Charger les données de test

```bash
docker exec laravel_app php artisan db:seed
```

---

## Étape 9 — Vérification

| URL | Description |
|-----|-------------|
| http://localhost:8000 | API Backend |
| http://localhost:8000/admin | 🔐 Back Office Filament |
| http://localhost:8080 | phpMyAdmin |

### Comptes par défaut

| Rôle | Email | Mot de passe |
|------|-------|------------|
| **Super Admin** | `superadmin@yateem.org` | `password` |
| **Admin** | `admin@yateem.org` | `password` |

> [!CAUTION]
> Changez immédiatement ces mots de passe en production !

---

## Commandes Utiles

```bash
# Voir les logs de l'application
docker exec laravel_app php artisan pail

# Relancer les migrations (reset)
docker exec laravel_app php artisan migrate:fresh --seed

# Vider le cache
docker exec laravel_app php artisan optimize:clear

# Arrêter les containers
docker-compose down

# Arrêter et supprimer les données (volumes inclus)
docker-compose down -v

# Reconstruire un container spécifique
docker-compose build app
docker-compose up -d app
```

---

## Déploiement en Production

### 1. Préparer l'environnement

```bash
# Mettre à jour le .env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com
```

### 2. Optimiser l'application

```bash
docker exec laravel_app php artisan config:cache
docker exec laravel_app php artisan route:cache
docker exec laravel_app php artisan view:cache
docker exec laravel_app php artisan event:cache
docker exec laravel_app composer install --no-dev --optimize-autoloader
```

### 3. Configurer HTTPS

Utiliser un reverse proxy (Nginx, Traefik, Caddy) ou un service cloud (AWS ALB, Cloudflare) devant le port 8000.

### 4. Sauvegardes

```bash
# Sauvegarder la base de données
docker exec mysql_db mysqldump -u root -proot yateem > backup_$(date +%Y%m%d).sql
```

---

## Résolution de Problèmes

| Problème | Solution |
|----------|----------|
| `Cannot connect to Docker daemon` | Lancez Docker Desktop |
| `intl extension missing` | Relancer l'étape 4 |
| `composer` timeout/lent | Utiliser `--prefer-dist` ou `--no-audit` |
| Permission denied sur storage | `docker exec laravel_app chmod -R 775 storage bootstrap/cache` |
| `SQLSTATE: table not found` | `docker exec laravel_app php artisan migrate` |
| Port 8000 déjà utilisé | Modifier le port dans `docker-compose.yml` ligne `"8000:80"` |
