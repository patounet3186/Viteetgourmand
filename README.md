# Vite & Gourmand - ECF 2026

Application web responsive réalisée pour l’ECF du titre professionnel
Développeur Web et Web Mobile. Vite & Gourmand est un traiteur fictif situé à
Bordeaux qui présente ses menus, prend des commandes et organise leur suivi.

- Dépôt public : <https://github.com/patounet3186/Viteetgourmand>
- URL de production : <https://arkflo.alwaysdata.net>
- Tableau de gestion de projet : <https://github.com/users/patounet3186/projects/4>

## Fonctionnalités

### Visiteur

- consulter l’accueil, les horaires et les avis validés ;
- filtrer les menus par prix minimum/maximum, thème, régime et personnes ;
- consulter la galerie, la composition, les allergènes et les conditions ;
- créer un compte, se connecter et réinitialiser son mot de passe ;
- envoyer une demande depuis le formulaire de contact ;
- consulter les mentions légales, les CGV et la confidentialité.

### Client

- modifier ses informations personnelles ;
- commander un menu disponible avec coordonnées préremplies ;
- voir le prix du menu, la remise de 10 %, la livraison calculée selon la
  distance et le total en direct ;
- modifier ou annuler une commande tant qu’elle est au statut `nouvelle` ;
- consulter le détail et l’historique horodaté de chaque commande ;
- recevoir des notifications lors des changements de statut ;
- déposer une note et un commentaire après la fin de la prestation.

### Employé

- gérer les menus, leur composition, leur galerie, leur stock et leur visibilité ;
- créer, modifier et supprimer un plat inutilisé ;
- consulter et filtrer les commandes par statut ou client ;
- faire progresser une commande selon le cycle métier ;
- annuler après avoir indiqué le moyen de contact et le motif ;
- gérer les horaires d’ouverture ;
- valider ou refuser les avis.

### Administrateur

- utiliser toutes les fonctions employé ;
- créer et activer/désactiver les comptes employés ;
- consulter le nombre de commandes et le chiffre d’affaires par menu ;
- filtrer les statistiques par menu et période ;
- utiliser MongoDB pour l’agrégation, avec un secours SQL si MongoDB est absent.

## Technologies

- PHP 8.2, PDO et architecture MVC ;
- Composer et autoload PSR-4 ;
- HTML5, CSS3, JavaScript et Bootstrap 5.3.3 local ;
- Chart.js 4.4.9 local ;
- MySQL/MariaDB pour les données métier ;
- MongoDB Atlas pour les avis et les données statistiques ;
- Docker Compose ou Apache/XAMPP en local ;
- alwaysdata comme cible d’hébergement.

Les licences des bibliothèques front-end sont conservées dans
`public/vendor/`.

## Architecture

```text
app/
  Controllers/   validations, autorisations et réponses HTTP
  Core/          contrôleur commun, URL, environnement, erreurs HTTP
  Models/        accès PDO et MongoDB
  Services/      règles métier, validations, tarification et e-mails
  Views/         vues PHP et gabarit principal
config/          connexions locales ignorées par Git
database/        schéma complet et migration additive
docs/            livrables de l’ECF
public/          contrôleur frontal, CSS, JavaScript, images, bibliothèques
tests/           contrôles d’architecture, domaine et intégration
Dockerfile       image PHP 8.2, Apache et extension MongoDB
compose.yaml     application, MariaDB et MongoDB pour le développement
```

`public/index.php` est le contrôleur frontal. Il associe la valeur du paramètre
`page` à une méthode de contrôleur. Les contrôleurs appellent les modèles et
transmettent uniquement les données nécessaires aux vues.

## Installation locale avec Docker

Cette méthode est recommandée : elle fournit les mêmes services sans installer
PHP, MariaDB ou MongoDB séparément.

Prérequis : Docker Desktop avec Docker Compose.

```bash
git clone https://github.com/patounet3186/Viteetgourmand.git ECF-2026
cd ECF-2026
docker compose up --build
```

Ouvrir ensuite <http://localhost:8080>.

Au premier démarrage, `database/schema.sql` initialise MariaDB et le script
`database/apply_mongodb_indexes.php` crée les index MongoDB. Les données sont
conservées dans deux volumes Docker.

Commandes utiles :

```bash
docker compose ps
docker compose logs app
docker compose exec app composer test
docker compose down
```

Pour repartir d’une base vide, arrêter les conteneurs puis supprimer explicitement
leurs volumes avec `docker compose down -v`. Cette dernière commande efface les
données locales Docker et ne doit jamais être utilisée sur la production.

## Installation locale avec XAMPP

### 1. Prérequis

- PHP 8.2 ou supérieur ;
- Apache et MySQL/MariaDB, par exemple avec XAMPP ;
- Composer ;
- extension PHP `mongodb` 2.3 ou compatible ;
- compte MongoDB Atlas ;
- Git.

Sous XAMPP, placer `php_mongodb.dll` dans `C:\xampp\php\ext`, ajouter
`extension=php_mongodb.dll` dans `php.ini`, puis redémarrer Apache.

### 2. Cloner et installer

```bash
cd /c/xampp/htdocs
git clone https://github.com/patounet3186/Viteetgourmand.git ECF-2026
cd ECF-2026
composer install
```

### 3. Configurer MySQL/MariaDB

Copier `config/database.example.php` vers `config/database.php`, puis saisir
les identifiants locaux. Le fichier réel est ignoré par Git.

Pour une installation vide :

1. créer et sélectionner une base dans phpMyAdmin ;
2. importer `database/schema.sql`.

Pour mettre à jour une installation antérieure du projet :

1. faire une sauvegarde de la base ;
2. sélectionner la base ;
3. importer `database/migrations/20260718_complete_ecf.sql` une seule fois.
4. importer `database/migrations/20260803_delivery_distance.sql` une seule fois.

Les migrations ajoutent les galeries, l’historique des statuts, les
notifications, les jetons de mot de passe, les horaires et la distance de
livraison sans supprimer les données existantes.

### 4. Configurer MongoDB

Copier `config/mongodb.example.php` vers `config/mongodb.php`, puis renseigner :

```php
return [
    'uri' => 'mongodb+srv://UTILISATEUR:MOT_DE_PASSE@cluster.mongodb.net/',
    'database' => 'vite_et_gourmand',
    'collection' => 'reviews',
    'analytics_collection' => 'order_analytics',
];
```

Dans MongoDB Atlas, l’utilisateur doit avoir le droit `readWrite` et l’adresse
IP du serveur doit être autorisée. Le fichier réel est ignoré par Git.

Exécuter ensuite une fois `php database/apply_mongodb_indexes.php` ou le script
`database/mongodb-indexes.js` dans `mongosh` pour garantir un seul avis et une
seule projection par commande.

### 5. Configurer l’environnement

Copier `.env.example` vers `.env` :

```dotenv
APP_ENV=development
APP_URL=http://localhost/ECF-2026/public
MAIL_FROM=no-reply@vite-et-gourmand.fr
COMPANY_EMAIL=contact@vite-et-gourmand.fr
COMPANY_LEGAL_NAME="Vite & Gourmand"
COMPANY_ADDRESS="Bordeaux, France"
COMPANY_SIRET=
```

Le chargeur n’écrase pas les variables déjà définies par Apache ou alwaysdata.
En production, utiliser `APP_ENV=production` et une URL HTTPS.

L’envoi repose sur `mail()`. Le serveur doit donc disposer d’un transport e-mail
configuré. Un échec est journalisé sans interrompre la commande.

### 6. Lancer

Démarrer Apache, puis ouvrir :

```text
http://localhost/ECF-2026/public/
```

La racine web de production doit idéalement pointer vers le dossier `public/`.

## Premier administrateur

1. Créer un compte client depuis l’interface.
2. Pour l’amorçage uniquement, modifier son rôle en `admin` dans la base.
3. Se reconnecter.
4. Créer ensuite les employés depuis `Administration > Accès`.

L’application ne permet pas de créer un autre administrateur depuis le front.

## Routes principales

| Fonction | Route |
| --- | --- |
| Accueil | `?page=home` |
| Menus et filtres | `?page=menus` |
| Détail menu | `?page=menu-show&id=1` |
| Inscription / connexion | `?page=register`, `?page=login` |
| Mot de passe oublié | `?page=forgot-password` |
| Espace client | `?page=account` |
| Détail / modification commande | `?page=order-show&id=1`, `?page=order-edit&id=1` |
| Commandes employé | `?page=employee-orders` |
| Menus / plats / horaires | `?page=employee-menus`, `employee-dishes`, `employee-hours` |
| Modération des avis | `?page=employee-reviews` |
| Statistiques admin | `?page=admin-dashboard` |
| Comptes employés | `?page=admin-users` |
| Pages légales | `?page=legal-notice`, `terms`, `privacy` |

## Tests

Tests rapides, sans écriture en base :

```bash
composer test
```

Ils vérifient l’architecture MVC, les règles métier, la présence des jetons
CSRF et les principaux noms accessibles des formulaires et images.

Test d’intégration optionnel sur la base configurée :

```bash
composer test:integration
```

Pour lancer les tests rapides puis l’intégration en une commande :

```bash
composer test:all
```

Ce dernier crée des données marquées `example.test`, vérifie menu, composition,
prix, commande, stock et historique, puis les supprime dans tous les cas.

Contrôles complémentaires :

```bash
composer validate --no-check-publish
C:/xampp/php/php.exe -l public/index.php
node --check public/js/app.js
node --check public/js/menu-filters.js
```

## Sécurité et accessibilité

- mots de passe hachés avec `password_hash` ;
- jetons CSRF sur les actions sensibles ;
- requêtes PDO préparées ;
- échappement HTML des données affichées ;
- limitation de cinq échecs de connexion pendant quinze minutes ;
- jetons de réinitialisation hachés, expirables et à usage unique ;
- sessions `HttpOnly`, `SameSite=Lax` et `Secure` sous HTTPS ;
- autorisations contrôlées côté serveur pour chaque rôle ;
- en-têtes de sécurité et erreurs détaillées masquées en production ;
- politique CSP et HSTS lorsque la connexion utilise HTTPS ;
- lien d’évitement, structure sémantique, labels et focus visible ;
- interface responsive contrôlée à 1440 × 1000 et 390 × 844.

## Livrables

- SQL : `database/schema.sql` et `database/migrations/` ;
- manuel : `docs/manuel-utilisation.md` ;
- charte : `docs/charte-graphique.md` ;
- gestion de projet : `docs/gestion-projet.md` ;
- documentation projet : `docs/documentation-projet.md` ;
- documentation technique : `docs/documentation-technique.md` ;
- déploiement : `docs/deploiement.md` ;
- maquettes et wireframes : `docs/maquettes.md` ;
- synthèse d’étude : `docs/synthese-revision.md`.

Les neuf exports prêts à remettre sont regroupés dans `docs/pdf/`.

Avant une nouvelle remise, il reste seulement à confirmer l’identité légale
définitive de l’éditeur dans les variables de production et les mentions légales.
