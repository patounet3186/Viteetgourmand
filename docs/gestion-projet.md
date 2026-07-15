# Documentation de gestion de projet

## Méthode retenue

La gestion de projet est organisée sous forme de backlog simple avec priorisation des fonctionnalités.

Les fonctionnalités sont développées par branches Git afin de respecter les bonnes pratiques demandées dans le sujet.

## Branches Git

- `main` : version stable.
- `develop` : version de développement.
- `feature/*` : branche par fonctionnalité.

Exemples :

```bash
feature/auth
feature/orders
feature/employee-space
feature/reviews
feature/menu-dishes-management
```

La branche de travail actuelle est :

```text
feature/menu-dishes-management
```

## Backlog prioritaire

| Priorité | Fonctionnalité | Statut |
| --- | --- | --- |
| 1 | Structure PHP et routing simple | Fait |
| 2 | Liste des menus | Fait |
| 3 | Détail menu | Fait |
| 4 | Filtres dynamiques JS | Fait |
| 5 | Inscription | Fait |
| 6 | Connexion / déconnexion | Fait |
| 7 | Commande menu | Fait |
| 8 | Espace utilisateur | Fait |
| 9 | Espace employé | Fait |
| 10 | Espace administrateur | Fait |
| 11 | Avis clients NoSQL avec MongoDB Atlas | Fait |
| 12 | Validation/refus des avis | Fait |
| 13 | Documentation finale | En cours |
| 14 | Déploiement | À faire |

## Règles de validation

Avant chaque merge :

1. Tester la fonctionnalité dans le navigateur.
2. Vérifier la syntaxe PHP :

```bash
C:/xampp/php/php.exe -l chemin/du/fichier.php
```

3. Vérifier qu'aucun secret n'est suivi par Git :

```bash
git status --ignored
```

4. Faire un commit clair.
5. Pousser la branche sur GitHub.

## Commits récents

Dernière évolution technique validée :

```text
Refactorisation du projet vers une architecture MVC
```

Cette évolution ajoute :

- un contrôleur frontal dans `public/index.php` ;
- des contrôleurs par domaine dans `app/Controllers` ;
- des modèles MySQL et MongoDB dans `app/Models` ;
- un autoload PSR-4 avec Composer ;
- des vues limitées à l'affichage ;
- une gestion commune des redirections et des erreurs HTTP.

## Outil de suivi

Un tableau de suivi peut être tenu dans :

- GitHub Projects ;
- Trello ;
- Notion ;
- Jira.

Pour le rendu final, ajouter ici le lien vers l'outil choisi.
