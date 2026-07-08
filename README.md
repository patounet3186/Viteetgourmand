# Vite & Gourmand - ECF 2026

Application web realisee dans le cadre de l'ECF du titre professionnel Developpeur Web et Web Mobile.

Le projet repond au besoin de l'entreprise fictive **Vite & Gourmand**, traiteur a Bordeaux, qui souhaite presenter ses menus en ligne et permettre aux utilisateurs de commander.

## Fonctionnalites deja implementees

- Page d'accueil
- Liste des menus depuis une base MySQL/MariaDB
- Filtres dynamiques sur les menus sans rechargement de page
- Detail d'un menu
- Inscription utilisateur
- Connexion / deconnexion
- Affichage conditionnel de la navigation selon la session
- Commande d'un menu par un utilisateur connecte
- Espace utilisateur avec debut de suivi des commandes

## Stack technique

- Front-end : HTML5, CSS3, Bootstrap 5, JavaScript
- Back-end : PHP avec PDO
- Base relationnelle : MySQL/MariaDB
- Base non relationnelle : a ajouter pour les statistiques administrateur
- Serveur local : XAMPP / Apache
- Hebergement cible : alwaysdata ou equivalent compatible PHP/MySQL

## Installation locale

### 1. Prerequis

- PHP 8 ou plus
- XAMPP avec Apache active
- Acces a une base MySQL/MariaDB
- Git

### 2. Cloner le depot

Depuis le dossier `htdocs` de XAMPP :

```bash
cd /c/xampp/htdocs
git clone https://github.com/patounet3186/ECF-2026.git
cd ECF-2026
```

Si le projet est deja present en local :

```bash
cd /c/xampp/htdocs/ECF-2026
git pull
```

### 3. Configurer la base de donnees

Copier le fichier d'exemple :

```bash
cp config/database.example.php config/database.php
```

Puis modifier `config/database.php` avec les informations de connexion locales ou alwaysdata :

```php
$host = 'mysql-votrecompte.alwaysdata.net';
$dbname = 'votrecompte_viteetgourmand';
$username = 'votre_utilisateur';
$password = 'votre_mot_de_passe';
```

Le fichier `config/database.php` est ignore par Git afin de ne pas publier les identifiants.

### 4. Importer la base SQL

Le fichier SQL principal est disponible ici :

```text
database/schema.sql
```

Dans phpMyAdmin :

1. Selectionner la base de donnees.
2. Aller dans l'onglet `Importer`.
3. Importer `database/schema.sql`.
4. Verifier que les tables suivantes sont creees :
   - `users`
   - `menus`
   - `dishes`
   - `menu_dishes`
   - `orders`
   - `reviews`

### 5. Lancer l'application

Demarrer Apache dans XAMPP, puis ouvrir :

```text
http://localhost/ECF-2026/public/
```

## Routes principales

| Page | URL |
| --- | --- |
| Accueil | `?page=home` |
| Liste des menus | `?page=menus` |
| Detail d'un menu | `?page=menu-show&id=1` |
| Inscription | `?page=register` |
| Connexion | `?page=login` |
| Espace utilisateur | `?page=account` |
| Commande | `?page=order-create&menu_id=1` |
| Deconnexion | `?page=logout` |

## Identifiants de test

Pour le moment, le compte utilisateur est cree depuis la page d'inscription.

Avant le rendu final, cette section devra contenir les comptes de demonstration :

- Utilisateur
- Employe
- Administrateur

Ne jamais indiquer de vrais mots de passe personnels dans ce fichier.

## Gestion Git

Organisation demandee pour l'ECF :

- `main` : branche principale stable
- `develop` : branche d'integration
- `feature/*` : branches de fonctionnalites

Exemple :

```bash
git switch develop
git switch -c feature/auth
```

Apres validation :

```bash
git add .
git commit -m "Ajout de l'authentification"
git push -u origin feature/auth
```

## Livrables ECF

Les livrables sont prepares dans le depot :

- Code source public : depot GitHub
- Fichier SQL : `database/schema.sql`
- Manuel utilisateur : `docs/manuel-utilisation.md`
- Charte graphique : `docs/charte-graphique.md`
- Documentation gestion projet : `docs/gestion-projet.md`
- Documentation technique : `docs/documentation-technique.md`

Les fichiers Markdown du dossier `docs/` pourront etre exportes en PDF avant le rendu final.

## Securite

Mesures deja presentes :

- Mots de passe haches avec `password_hash`
- Verification de mot de passe avec `password_verify`
- Requetes SQL preparees avec PDO
- Protection XSS avec `htmlspecialchars`
- Regeneration de session apres connexion
- Configuration sensible ignoree par Git

Mesures a completer :

- CSRF token sur les formulaires sensibles
- Validation plus complete des donnees
- Gestion des roles employe et administrateur
- Reset de mot de passe
- Emails automatiques
- Journalisation des statuts de commande

## Etat du projet

Le projet est en cours de developpement sur la branche `feature/auth`.

Les prochaines etapes prioritaires :

1. Finaliser l'espace utilisateur.
2. Ajouter l'espace employe.
3. Ajouter l'espace administrateur.
4. Ajouter la gestion des statuts de commande.
5. Ajouter la base NoSQL pour les statistiques.
6. Completer les documents et exporter les PDF.
