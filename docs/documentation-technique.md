# Documentation technique - Vite & Gourmand

## Architecture

Le projet utilise une architecture MVC avec un contrôleur frontal et un autoload PSR-4 fourni par Composer.

```text
ECF-2026/
  app/
    Controllers/
      AdminController.php
      AuthController.php
      ContactController.php
      DishController.php
      HomeController.php
      MenuController.php
      OrderController.php
      ReviewController.php
    Core/
      Controller.php
      HttpException.php
    Models/
      ContactMessage.php
      Dish.php
      Menu.php
      Model.php
      Order.php
      Review.php
      User.php
    Services/
      csrf.php
    Views/
      admin/
      auth/
      employee/
      errors/
      layouts/
      menus/
      orders/
      pages/
      reviews/
  config/
  database/
  docs/
  public/
    css/
    js/
  vendor/
```

## Point d'entrée

Le point d'entrée public est :

```text
public/index.php
```

Il lit le paramètre `page`, sélectionne une action de contrôleur, puis injecte les données retournées dans le layout principal.

Exemple :

```text
?page=menus
```

Répartition des responsabilités :

- les modèles exécutent les requêtes MySQL ou MongoDB ;
- les contrôleurs gèrent les requêtes HTTP, les droits, les validations et les redirections ;
- les vues affichent uniquement les données reçues ;
- `app/Core/Controller.php` fournit le rendu, les redirections et les contrôles d'accès communs ;
- `app/Core/HttpException.php` centralise les réponses `403` et `404`.

## Configuration

La configuration relationnelle est stockée dans :

```text
config/database.php
```

La configuration MongoDB est stockée dans :

```text
config/mongodb.php
```

Ces fichiers ne sont pas versionnés afin de protéger les identifiants.

Un modèle partageable est fourni pour MySQL/MariaDB :

```text
config/database.example.php
```

## Dépendances PHP

Le projet utilise Composer pour charger la librairie MongoDB et les classes du namespace `App` :

```bash
composer install
```

Dépendance principale :

```text
mongodb/mongodb
```

L'autoload PSR-4 associe le namespace `App\` au dossier `app/`.

L'extension PHP `mongodb` doit être activée dans XAMPP.

## Base de données relationnelle

Le schéma SQL est dans :

```text
database/schema.sql
```

Tables principales :

- `users`
- `menus`
- `dishes`
- `menu_dishes`
- `orders`
- `reviews`

La table `reviews` existe dans le schéma SQL, mais la fonctionnalité active des avis utilise MongoDB Atlas.

## Base NoSQL

La base non relationnelle utilisée est MongoDB Atlas.

Collection utilisée :

```text
reviews
```

Les avis clients sont gérés par le modèle :

```text
app/Models/Review.php
```

Exemple de document MongoDB :

```json
{
  "id": "review_abc123",
  "order_id": 1,
  "user_id": 1,
  "user_name": "Client Test",
  "menu_id": 1,
  "menu_title": "Menu Noel",
  "rating": 5,
  "comment": "Très bon service.",
  "status": "pending",
  "created_at": "2026-07-09 10:00:00"
}
```

Statuts possibles pour un avis :

- `pending` : en attente de validation ;
- `validated` : validé par l'administrateur ;
- `refused` : refusé par l'administrateur.

## Règles métier implémentées

### Menus

Un menu contient :

- titre ;
- description ;
- thème ;
- régime ;
- nombre minimum de personnes ;
- prix de base ;
- conditions ;
- stock.

### Inscription

Un compte utilisateur est créé avec le rôle `user`.

Le mot de passe doit respecter les contraintes suivantes :

- 10 caractères minimum ;
- une majuscule ;
- une minuscule ;
- un chiffre ;
- un caractère spécial.

Le mot de passe est stocké sous forme hachée avec `password_hash`.

### Connexion

La connexion utilise :

- recherche utilisateur par email ;
- vérification du mot de passe avec `password_verify` ;
- régénération de session avec `session_regenerate_id(true)`.

### Commande

Une commande est rattachée :

- à un utilisateur ;
- à un menu ;
- à une date et une heure ;
- à une adresse de livraison ;
- à un statut.

Le statut initial est `nouvelle`.

Une livraison hors Bordeaux ajoute actuellement 5 euros.

### Espace employé

L'espace employé permet :

- de consulter les commandes ;
- de filtrer les commandes par statut ;
- de modifier le statut d'une commande ;
- de créer des menus et de gérer leur stock et leur visibilité ;
- de créer et modifier les plats proposés dans les menus.

### Espace administrateur

L'espace administrateur permet :

- de consulter le nombre total de commandes ;
- de consulter le chiffre d'affaires total ;
- de consulter les statistiques par menu ;
- de consulter le nombre d'avis en attente ;
- de consulter la note moyenne ;
- de valider ou refuser les avis clients stockés dans MongoDB;
- de créer des comptes employés et de modifier leur état d'activation.

### Avis clients

Un utilisateur connecté peut déposer un avis uniquement si :

- la commande lui appartient ;
- la commande est au statut `livre` ou `terminee` ;
- aucun avis n'a déjà été déposé pour cette commande.

## Sécurité

Mesures présentes :

- PDO avec exceptions ;
- requêtes SQL préparées ;
- hachage des mots de passe ;
- jetons CSRF sur les formulaires sensibles ;
- échappement HTML avec `htmlspecialchars` ;
- fichiers de configuration ignorés par Git ;
- séparation des données relationnelles et NoSQL ;
- contrôle d'accès par rôle ;
- séparation MVC entre requêtes, traitements et affichage.
- Principe de minimisation : chaque rôle accède uniquement aux données nécessaires à sa mission.

Améliorations à ajouter :

- validation serveur plus stricte ;
- limitation des tentatives de connexion ;
- reset de mot de passe sécurisé ;
- journalisation des changements de statut.

## Déploiement

Déploiement cible possible :

- alwaysdata pour PHP/MySQL ;
- MongoDB Atlas pour NoSQL.

Étapes prévues :

1. Créer une application PHP sur alwaysdata.
2. Envoyer les fichiers du projet.
3. Configurer le dossier public vers `public/`.
4. Exécuter `composer install`.
5. Configurer l'extension PHP `mongodb` si l'hébergement le permet.
6. Créer la base MySQL/MariaDB.
7. Importer `database/schema.sql`.
8. Créer `config/database.php` sur le serveur.
9. Créer `config/mongodb.php` sur le serveur.
10. Tester les parcours principaux.

## Tests manuels

La séparation des responsabilités MVC et l'autoload peuvent être contrôlés avec :

```bash
composer test:mvc
```

Parcours à vérifier :

- ouvrir la page d'accueil ;
- consulter les menus ;
- filtrer les menus ;
- consulter le détail d'un menu ;
- créer un compte ;
- se connecter ;
- commander un menu ;
- consulter ses commandes ;
- modifier un statut depuis l'espace employé ;
- déposer un avis depuis l'espace utilisateur ;
- valider ou refuser l'avis depuis l'espace administrateur ;
- vérifier la présence de l'avis dans MongoDB Atlas ;
- créer puis modifier un plat depuis l'espace employé ;
- vérifier qu'un client reçoit une réponse `403` sur une route employé ;
- se déconnecter.
