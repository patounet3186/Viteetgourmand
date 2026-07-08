# Documentation technique - Vite & Gourmand

## Architecture

Le projet utilise une architecture PHP simple avec un routeur frontal.

```text
ECF-2026/
  app/
    Views/
      auth/
      layouts/
      menus/
      orders/
      pages/
  config/
  database/
  docs/
  public/
    css/
    js/
```

## Point d'entree

Le point d'entree public est :

```text
public/index.php
```

Il lit le parametre `page` dans l'URL et charge la vue correspondante.

Exemple :

```text
?page=menus
```

## Configuration

Le fichier de connexion est :

```text
config/database.php
```

Il n'est pas versionne pour proteger les identifiants.

Un modele partageable est fourni :

```text
config/database.example.php
```

## Base de donnees relationnelle

Le schema SQL est dans :

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

## Regles metier implementees

### Menus

Un menu contient :

- titre ;
- description ;
- theme ;
- regime ;
- nombre minimum de personnes ;
- prix de base ;
- conditions ;
- stock.

### Inscription

Un compte utilisateur est cree avec le role `user`.

Le mot de passe doit respecter les contraintes suivantes :

- 10 caracteres minimum ;
- une majuscule ;
- une minuscule ;
- un chiffre ;
- un caractere special.

Le mot de passe est stocke sous forme hachee avec `password_hash`.

### Connexion

La connexion utilise :

- recherche utilisateur par email ;
- verification du mot de passe avec `password_verify` ;
- regeneration de session avec `session_regenerate_id(true)`.

### Commande

Une commande est rattachee :

- a un utilisateur ;
- a un menu ;
- a une date et une heure ;
- a une adresse de livraison ;
- a un statut.

Le statut initial est `nouvelle`.

Une livraison hors Bordeaux ajoute actuellement 5 euros.

## Securite

Mesures presentes :

- PDO avec exceptions ;
- requetes preparees ;
- hachage des mots de passe ;
- echappement HTML avec `htmlspecialchars` ;
- fichier de configuration ignore par Git.

Ameliorations a ajouter :

- jetons CSRF ;
- verification serveur plus stricte ;
- limitation des tentatives de connexion ;
- reset de mot de passe securise ;
- controle d'acces par role ;
- journalisation des changements de statut.

## Base NoSQL

Le sujet demande une base non relationnelle.

Proposition :

- MongoDB Atlas pour stocker des statistiques de commandes par menu ;
- collection possible : `menu_statistics`.

Exemple de document :

```json
{
  "menu_id": 1,
  "menu_title": "Menu Noel",
  "orders_count": 12,
  "turnover": 2160,
  "updated_at": "2026-07-08T10:00:00Z"
}
```

Cette partie reste a implementer.

## Deploiement

Deploiement cible possible :

- alwaysdata pour PHP/MySQL ;
- MongoDB Atlas pour NoSQL.

Etapes prevues :

1. Creer une application PHP sur alwaysdata.
2. Envoyer les fichiers du projet.
3. Configurer le dossier public vers `public/`.
4. Creer la base MySQL/MariaDB.
5. Importer `database/schema.sql`.
6. Creer `config/database.php` sur le serveur avec les identifiants de production.
7. Tester les parcours principaux.

## Tests manuels

Parcours a verifier :

- ouvrir la page d'accueil ;
- consulter les menus ;
- filtrer les menus ;
- consulter le detail d'un menu ;
- creer un compte ;
- se connecter ;
- commander un menu ;
- consulter ses commandes ;
- se deconnecter.
