# Configuration Backend Laravel - Système Yateem

## Structure de la Base de Données

### Tables Principales

1. **users** - Gestion des utilisateurs (admin, manager, donors)
2. **categories** - Catégories de campagnes (Emergency Relief, Healthcare, Education, Seasonal Relief)
3. **campaigns** - Campagnes de collecte de fonds
4. **families** - Familles de veuves enregistrées
5. **orphans** - Orphelins rattachés aux familles
6. **sponsorships** - Parrainages mensuels des familles
7. **donations** - Dons effectués aux campagnes
8. **disbursements** - Décaissements vers les familles
9. **alerts** - Notifications et alertes système

## Installation et Configuration

### 1. Créer un nouveau projet Laravel

```bash
composer create-project laravel/laravel yateem-api
cd yateem-api
```

### 2. Configuration de la base de données

Modifier le fichier `.env` :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yateem_db
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Installer Laravel Sanctum (pour l'API)

```bash
php artisan install:api
```

### 4. Créer les migrations

Placez tous les fichiers de migration dans `database/migrations/` avec le format de nommage :
- `2024_01_01_000001_create_users_table.php`
- `2024_01_01_000002_create_categories_table.php`
- `2024_01_01_000003_create_campaigns_table.php`
- `2024_01_01_000004_create_families_table.php`
- `2024_01_01_000005_create_orphans_table.php`
- `2024_01_01_000006_create_sponsorships_table.php`
- `2024_01_01_000007_create_donations_table.php`
- `2024_01_01_000008_create_disbursements_table.php`
- `2024_01_01_000009_create_alerts_table.php`

### 5. Créer les Models

Placez tous les modèles dans `app/Models/` :
- `User.php`
- `Category.php`
- `Campaign.php`
- `Family.php`
- `Orphan.php`
- `Sponsorship.php`
- `Donation.php`
- `Disbursement.php`

### 6. Créer les Seeders

Placez les seeders dans `database/seeders/` :
- `UsersTableSeeder.php`
- `CategoriesTableSeeder.php`
- `DatabaseSeeder.php`

### 7. Exécuter les migrations et seeders

```bash
php artisan migrate:fresh --seed
```

## Routes API à créer

### Authentification
```php
POST   /api/register
POST   /api/login
POST   /api/logout
GET    /api/user
```

### Campagnes
```php
GET    /api/campaigns
GET    /api/campaigns/{id}
POST   /api/campaigns
PUT    /api/campaigns/{id}
DELETE /api/campaigns/{id}
GET    /api/campaigns/featured
GET    /api/campaigns/active
```

### Dons
```php
GET    /api/donations
GET    /api/donations/{id}
POST   /api/donations
GET    /api/donations/user/{userId}
GET    /api/donations/campaign/{campaignId}
```

### Familles
```php
GET    /api/families
GET    /api/families/{id}
POST   /api/families
PUT    /api/families/{id}
DELETE /api/families/{id}
GET    /api/families/{id}/orphans
```

### Orphelins
```php
GET    /api/orphans
GET    /api/orphans/{id}
POST   /api/orphans
PUT    /api/orphans/{id}
DELETE /api/orphans/{id}
```

### Parrainages
```php
GET    /api/sponsorships
GET    /api/sponsorships/{id}
POST   /api/sponsorships
PUT    /api/sponsorships/{id}
DELETE /api/sponsorships/{id}
GET    /api/sponsorships/active
```

### Dashboard / Statistiques
```php
GET    /api/dashboard/stats
GET    /api/dashboard/monthly-donations
GET    /api/dashboard/campaign-categories
GET    /api/dashboard/recent-campaigns
GET    /api/dashboard/recent-donations
```

## Permissions et Rôles

### Admin
- Toutes les permissions
- Gérer les utilisateurs
- Gérer les campagnes
- Gérer les familles
- Voir tous les dons
- Créer des décaissements

### Manager
- Gérer les campagnes
- Gérer les familles
- Voir les dons
- Créer des décaissements

### Donor
- Voir les campagnes
- Faire des dons
- Voir son historique de dons
- Gérer ses parrainages

## Prochaines Étapes

1. Créer les Controllers pour chaque ressource
2. Créer les Form Requests pour la validation
3. Créer les Resources pour formater les réponses API
4. Implémenter l'authentification avec Sanctum
5. Créer les middlewares pour les permissions
6. Implémenter les notifications par email
7. Ajouter la documentation API avec Swagger/OpenAPI
8. Configurer CORS pour le frontend
9. Implémenter le système de paiement (Stripe, PayPal, Mobile Money)
10. Créer les tests unitaires et d'intégration

## Commandes Artisan Utiles

```bash
# Créer un controller
php artisan make:controller Api/CampaignController --api

# Créer une request de validation
php artisan make:request StoreCampaignRequest

# Créer une resource pour l'API
php artisan make:resource CampaignResource

# Créer un middleware
php artisan make:middleware CheckRole

# Créer un seeder
php artisan make:seeder FamiliesTableSeeder

# Rafraîchir la base de données
php artisan migrate:fresh --seed

# Lancer le serveur
php artisan serve
```

## Configuration CORS

Dans `config/cors.php` :

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['*'],
'allowed_origins' => ['http://localhost:3000'], // URL de votre frontend
'allowed_headers' => ['*'],
'supports_credentials' => true,
```

## Variables d'environnement importantes

```env
APP_NAME=Yateem
APP_URL=http://localhost:8000

FRONTEND_URL=http://localhost:3000

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=

# Pour les paiements
STRIPE_KEY=
STRIPE_SECRET=
```


# Documentation API Yateem

## 📚 Vue d'ensemble

L'API Yateem est une API RESTful construite avec Laravel qui gère un système de dons et de parrainage pour veuves et orphelins.

**Base URL:** `http://localhost:8000/api`

## 🔐 Authentification

L'API utilise Laravel Sanctum pour l'authentification par token Bearer.

### Inscription
```http
POST /auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+237690000000",
  "address": "Douala, Cameroun"
}
```

**Réponse:**
```json
{
  "success": true,
  "message": "Inscription réussie",
  "data": {
    "user": {...},
    "token": "1|xxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

### Connexion
```http
POST /auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

### Déconnexion
```http
POST /auth/logout
Authorization: Bearer {token}
```

### Profil utilisateur
```http
GET /auth/user
Authorization: Bearer {token}
```

### Mettre à jour le profil
```http
PUT /auth/profile
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "John Doe Updated",
  "phone": "+237690000001",
  "current_password": "password123",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

## 📊 Dashboard

### Statistiques globales
```http
GET /dashboard/stats
Authorization: Bearer {token}
```

### Vue d'ensemble complète
```http
GET /dashboard/overview
Authorization: Bearer {token}
```

### Dons mensuels
```http
GET /dashboard/monthly-donations?months=6
Authorization: Bearer {token}
```

### Catégories de campagnes
```http
GET /dashboard/campaign-categories
Authorization: Bearer {token}
```

### Alertes
```http
GET /dashboard/alerts
Authorization: Bearer {token}
```

### Statistiques du donateur
```http
GET /dashboard/donor-stats
Authorization: Bearer {token}
```

## 🎯 Campagnes

### Lister les campagnes (Public)
```http
GET /campaigns?status=active&category_id=1&per_page=15
```

**Paramètres de requête:**
- `status` (optional): draft, active, completed, cancelled
- `category_id` (optional): ID de la catégorie
- `urgency` (optional): normal, urgent
- `featured` (optional): true/false
- `sort_by` (optional): created_at, goal_amount, etc.
- `sort_order` (optional): asc, desc
- `per_page` (optional): nombre d'items par page (défaut: 15)

### Campagnes en vedette
```http
GET /campaigns/featured
```

### Campagnes actives
```http
GET /campaigns/active
```

### Campagnes urgentes
```http
GET /campaigns/urgent
```

### Détails d'une campagne
```http
GET /campaigns/{id}
```

### Créer une campagne (Admin/Manager)
```http
POST /campaigns
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "category_id": 1,
  "title": "Aide d'urgence pour les familles",
  "description": "Description détaillée...",
  "goal_amount": 50000,
  "start_date": "2024-01-01",
  "end_date": "2024-12-31",
  "urgency": "urgent",
  "beneficiaries_count": 150,
  "is_featured": true,
  "image": <file>
}
```

### Mettre à jour une campagne (Admin/Manager)
```http
PUT /campaigns/{id}
Authorization: Bearer {token}
Content-Type: application/json
```

### Supprimer une campagne (Admin/Manager)
```http
DELETE /campaigns/{id}
Authorization: Bearer {token}
```

## 💰 Dons

### Lister les dons
```http
GET /donations?status=completed&campaign_id=1
Authorization: Bearer {token}
```

**Paramètres de requête:**
- `status`: pending, completed, failed, refunded
- `campaign_id`: ID de la campagne
- `user_id`: ID de l'utilisateur
- `payment_method`: card, bank_transfer, mobile_money, cash
- `search`: recherche par nom ou email
- `date_from`: date de début
- `date_to`: date de fin

### Faire un don
```http
POST /donations
Authorization: Bearer {token}
Content-Type: application/json

{
  "campaign_id": 1,
  "amount": 10000,
  "donor_name": "John Doe",
  "donor_email": "john@example.com",
  "donor_phone": "+237690000000",
  "payment_method": "mobile_money",
  "is_anonymous": false,
  "is_recurring": false,
  "message": "Pour aider les familles"
}
```

### Mes dons
```http
GET /donations/my-donations
Authorization: Bearer {token}
```

### Dons par campagne
```http
GET /donations/campaign/{campaignId}
Authorization: Bearer {token}
```

### Statistiques des dons
```http
GET /donations/statistics
Authorization: Bearer {token}
```

### Mettre à jour le statut d'un don (Admin)
```http
PATCH /donations/{id}/status
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "completed"
}
```

## 👨‍👩‍👧 Familles (Admin/Manager)

### Lister les familles
```http
GET /families?status=active&city=Douala
Authorization: Bearer {token}
```

**Paramètres:**
- `status`: active, inactive, pending
- `city`: filtrer par ville
- `region`: filtrer par région
- `search`: rechercher par nom, code ou email

### Créer une famille
```http
POST /families
Authorization: Bearer {token}
Content-Type: application/json

{
  "widow_name": "Marie Dupont",
  "widow_phone": "+237690000000",
  "widow_email": "marie@example.com",
  "widow_date_of_birth": "1980-05-15",
  "address": "123 Rue Example",
  "city": "Douala",
  "region": "Littoral",
  "orphans_count": 3,
  "status": "pending",
  "notes": "Notes supplémentaires"
}
```

### Détails d'une famille
```http
GET /families/{id}
Authorization: Bearer {token}
```

### Orphelins d'une famille
```http
GET /families/{id}/orphans
Authorization: Bearer {token}
```

### Mettre à jour le statut
```http
PATCH /families/{id}/status
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "active"
}
```

### Statistiques des familles
```http
GET /families/statistics
Authorization: Bearer {token}
```

## 👶 Orphelins (Admin/Manager)

### Lister les orphelins
```http
GET /orphans?family_id=1&gender=male&is_sponsored=false
Authorization: Bearer {token}
```

### Orphelins parrainés
```http
GET /orphans/sponsored
Authorization: Bearer {token}
```

### Orphelins non parrainés
```http
GET /orphans/unsponsored
Authorization: Bearer {token}
```

### Créer un orphelin
```http
POST /orphans
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "family_id": 1,
  "first_name": "Jean",
  "last_name": "Dupont",
  "date_of_birth": "2015-03-20",
  "gender": "male",
  "school_name": "École Primaire",
  "school_level": "CM2",
  "health_status": "Bon état de santé",
  "special_needs": null,
  "is_sponsored": false,
  "photo": <file>
}
```

### Statistiques des orphelins
```http
GET /orphans/statistics
Authorization: Bearer {token}
```

## 🤝 Parrainages

### Lister les parrainages
```http
GET /sponsorships?status=active
Authorization: Bearer {token}
```

### Parrainages actifs
```http
GET /sponsorships/active
Authorization: Bearer {token}
```

### Mes parrainages
```http
GET /sponsorships/my-sponsorships
Authorization: Bearer {token}
```

### Créer un parrainage
```http
POST /sponsorships
Authorization: Bearer {token}
Content-Type: application/json

{
  "user_id": 1,
  "family_id": 1,
  "monthly_amount": 50000,
  "start_date": "2024-01-01",
  "end_date": null,
  "payment_frequency": "monthly",
  "notes": "Parrainage mensuel"
}
```

### Mettre à jour le statut
```http
PATCH /sponsorships/{id}/status
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "paused"
}
```

### Statistiques des parrainages
```http
GET /sponsorships/statistics
Authorization: Bearer {token}
```

## 💸 Décaissements (Admin/Manager)

### Lister les décaissements
```http
GET /disbursements?type=cash&family_id=1
Authorization: Bearer {token}
```

### Décaissements du mois
```http
GET /disbursements/this-month
Authorization: Bearer {token}
```

### Créer un décaissement
```http
POST /disbursements
Authorization: Bearer {token}
Content-Type: application/json

{
  "campaign_id": 1,
  "family_id": 1,
  "amount": 25000,
  "disbursement_date": "2024-01-15",
  "type": "cash",
  "description": "Aide alimentaire mensuelle",
  "notes": "Notes supplémentaires"
}
```

### Statistiques des décaissements
```http
GET /disbursements/statistics
Authorization: Bearer {token}
```

## 📁 Catégories

### Lister les catégories (Public)
```http
GET /categories
```

### Campagnes par catégorie
```http
GET /categories/{id}/campaigns
```

### Créer une catégorie (Admin/Manager)
```http
POST /categories
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Nouvelle Catégorie",
  "description": "Description...",
  "color": "#10B981",
  "is_active": true
}
```

## 📝 Codes de statut HTTP

- `200 OK` - Requête réussie
- `201 Created` - Ressource créée avec succès
- `401 Unauthorized` - Non authentifié
- `403 Forbidden` - Accès refusé
- `404 Not Found` - Ressource non trouvée
- `422 Unprocessable Entity` - Erreur de validation
- `500 Internal Server Error` - Erreur serveur

## 🔒 Permissions

### R

analyse le projet et construit le back offfice avec filament php pour administrer tout (gestion des veuves et orphelins (ajoute aussi la fonctionnalite d'ajout de l'image pour un orphelin ou une veuve), gestions des dons, gestions des sponsorisations, tableau de bord, gestion des utilisateurs et role (je veux les roles: admin et super admin avec la gestion des niveaux d'acces ou d'operation qu'il peuvent effectuer))
