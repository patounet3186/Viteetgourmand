# Vite & Gourmand - ECF 2026

Application web réalisée dans le cadre de l'ECF du titre professionnel Développeur Web et Web Mobile.

Le projet répond au besoin de l'entreprise fictive **Vite & Gourmand**, traiteur à Bordeaux, qui souhaite présenter ses menus en ligne, permettre aux utilisateurs de commander et gérer les avis clients.

## Fonctionnalités implémentées

- Page d'accueil
- Liste des menus depuis une base MySQL/MariaDB
- Filtres dynamiques sur les menus sans rechargement de page
- Détail d'un menu
- Inscription utilisateur
- Connexion / déconnexion
- Affichage conditionnel de la navigation selon la session et le rôle
- Commande d'un menu par un utilisateur connecté
- Espace utilisateur avec suivi des commandes
- Dépôt d'avis après livraison d'une commande
- Stockage des avis clients dans MongoDB Atlas
- Espace employé pour consulter les commandes et modifier les statuts
- Tableau de bord administrateur
- Statistiques de commandes
- Validation ou refus des avis clients

## Stack technique

- Front-end : HTML5, CSS3, Bootstrap 5, JavaScript
- Back-end : PHP 8 avec PDO
- Dépendances PHP : Composer
- Base relationnelle : MySQL/MariaDB
- Base non relationnelle : MongoDB Atlas
- Serveur local : XAMPP / Apache
- Hébergement cible : alwaysdata ou équivalent compatible PHP/MySQL

## Installation locale

### 1. Prérequis

- PHP 8.2 ou plus
- XAMPP avec Apache activé
- Accès à une base MySQL/MariaDB
- Composer
- Extension PHP `mongodb`
- Compte MongoDB Atlas
- Git

Sur Windows avec XAMPP, l'extension MongoDB doit être copiée dans :

```text
C:\xampp\php\ext
```

Puis activée dans :

```text
C:\xampp\php\php.ini
```

Avec la ligne :

```ini
extension=php_mongodb.dll
```

Après modification, redémarrer Apache.

### 2. Cloner le dépôt

Depuis le dossier `htdocs` de XAMPP :

```bash
cd /c/xampp/htdocs
git clone https://github.com/patounet3186/Viteetgourmand.git ECF-2026
cd ECF-2026
```

Si le projet est déjà présent en local :

```bash
cd /c/xampp/htdocs/ECF-2026
git pull
```

### 3. Installer les dépendances PHP

```bash
composer install
```

La dépendance principale utilisée pour MongoDB est :

```text
mongodb/mongodb
```

Le dossier `vendor/` est ignoré par Git et doit être recréé avec Composer.

### 4. Configurer la base relationnelle MySQL/MariaDB

Copier le fichier d'exemple :

```bash
cp config/database.example.php config/database.php
```

Puis modifier `config/database.php` avec les informations de connexion locales ou alwaysdata :

```php
$host = 'localhost';
$dbname = 'vite_et_gourmand';
$username = 'votre_utilisateur';
$password = 'votre_mot_de_passe';
```

Le fichier `config/database.php` est ignoré par Git afin de ne pas publier les identifiants.

### 5. Importer la base SQL

Le fichier SQL principal est disponible ici :

```text
database/schema.sql
```

Dans phpMyAdmin :

1. Sélectionner la base de données.
2. Aller dans l'onglet `Importer`.
3. Importer `database/schema.sql`.
4. Vérifier que les tables suivantes sont créées :
   - `users`
   - `menus`
   - `dishes`
   - `menu_dishes`
   - `orders`
   - `reviews`

La table `reviews` est conservée dans le schéma SQL, mais la fonctionnalité active des avis clients utilise MongoDB Atlas.

### 6. Configurer MongoDB Atlas

Créer le fichier local :

```text
config/mongodb.php
```

Avec la structure suivante :

```php
<?php

return [
    'uri' => 'mongodb+srv://UTILISATEUR:MOT_DE_PASSE@CLUSTER.mongodb.net/?retryWrites=true&w=majority',
    'database' => 'vite_et_gourmand',
    'collection' => 'reviews',
];
```

Le fichier `config/mongodb.php` est ignoré par Git.

Dans MongoDB Atlas, vérifier :

1. Un utilisateur de base de données existe dans `Database Access`.
2. L'utilisateur possède les droits `readWrite`.
3. L'adresse IP locale est autorisée dans `Network Access`.
4. La chaîne de connexion est correctement renseignée dans `config/mongodb.php`.

### 7. Lancer l'application

Démarrer Apache dans XAMPP, puis ouvrir :

```text
http://localhost/ECF-2026/public/
```

## Routes principales

| Page | URL |
| --- | --- |
| Accueil | `?page=home` |
| Liste des menus | `?page=menus` |
| Détail d'un menu | `?page=menu-show&id=1` |
| Inscription | `?page=register` |
| Connexion | `?page=login` |
| Espace utilisateur | `?page=account` |
| Commande | `?page=order-create&menu_id=1` |
| Dépôt d'avis | `?page=review-create&order_id=1` |
| Gestion employé des commandes | `?page=employee-orders` |
| Tableau de bord administrateur | `?page=admin-dashboard` |
| Déconnexion | `?page=logout` |

## Identifiants de test

Les comptes de démonstration sont créés depuis l'interface d'inscription.

Pour tester les rôles :

1. Créer un compte client depuis l'inscription.
2. Créer un compte employé depuis l'inscription.
3. Créer un compte administrateur depuis l'inscription.
4. Se connecter avec un compte administrateur.
5. Aller sur `?page=admin-users` pour gérer les rôles et l'état des comptes.

Ne jamais indiquer de vrais mots de passe personnels dans ce fichier.

## Gestion Git

Organisation demandée pour l'ECF :

- `main` : branche principale stable
- `develop` : branche d'intégration
- `feature/*` : branches de fonctionnalités

Exemples :

```bash
git switch develop
git switch -c feature/auth
git switch -c feature/orders
git switch -c feature/reviews
```

Avant chaque commit :

```bash
C:\xampp\php\php.exe -l public/index.php
git status
```

Après validation :

```bash
git add .
git commit -m "Message du commit"
git push
```

## Livrables ECF

Les livrables sont préparés dans le dépôt :

- Code source public : dépôt GitHub
- Fichier SQL : `database/schema.sql`
- Manuel utilisateur : `docs/manuel-utilisation.md`
- Charte graphique : `docs/charte-graphique.md`
- Documentation gestion projet : `docs/gestion-projet.md`
- Documentation technique : `docs/documentation-technique.md`

Les fichiers Markdown du dossier `docs/` pourront être exportés en PDF avant le rendu final.

## Sécurité

Mesures déjà présentes :

- Jeton CSRF sur les formulaires sensibles
- Mots de passe hachés avec `password_hash`
- Vérification de mot de passe avec `password_verify`
- Requêtes SQL préparées avec PDO
- Protection XSS avec `htmlspecialchars`
- Régénération de session après connexion
- Configuration sensible ignorée par Git
- Séparation des données relationnelles et NoSQL
- Contrôle d'accès par rôle pour les espaces employé et administrateur

Mesures à compléter :

- Validation plus complète des données
- Limitation des tentatives de connexion
- Reset de mot de passe
- Emails automatiques
- Journalisation des statuts de commande

## État du projet

Le projet est en cours de développement sur la branche `feature/employee-space`.

Éléments finalisés ou avancés :

1. Authentification utilisateur.
2. Affichage des menus.
3. Commande de menus.
4. Suivi des commandes côté utilisateur.
5. Gestion des commandes côté employé.
6. Tableau de bord administrateur.
7. Avis clients stockés dans MongoDB Atlas.
8. Validation ou refus des avis par l'administrateur.

Prochaines étapes prioritaires :

1. Compléter les documents du dossier `docs/`.
2. Tester le déploiement alwaysdata.
3. Exporter les documents finaux en PDF.
