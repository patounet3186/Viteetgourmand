# Documentation de gestion de projet

## Methode retenue

La gestion de projet est organisee sous forme de backlog simple avec priorisation des fonctionnalites.

Les fonctionnalites sont developpees par branches Git afin de respecter les bonnes pratiques demandees dans le sujet.

## Branches Git

- `main` : version stable.
- `develop` : version de developpement.
- `feature/*` : branche par fonctionnalite.

Exemples :

```bash
feature/auth
feature/orders
feature/admin
feature/reviews
```

## Backlog prioritaire

| Priorite | Fonctionnalite | Statut |
| --- | --- | --- |
| 1 | Structure PHP et routing simple | Fait |
| 2 | Liste des menus | Fait |
| 3 | Detail menu | Fait |
| 4 | Filtres dynamiques JS | Fait |
| 5 | Inscription | Fait |
| 6 | Connexion / deconnexion | Fait |
| 7 | Commande menu | En cours |
| 8 | Espace utilisateur | En cours |
| 9 | Espace employe | A faire |
| 10 | Espace administrateur | A faire |
| 11 | Statistiques NoSQL | A faire |
| 12 | Deploiement | A faire |

## Regles de validation

Avant chaque merge :

1. Tester la fonctionnalite dans le navigateur.
2. Verifier la syntaxe PHP :

```bash
C:/xampp/php/php.exe -l chemin/du/fichier.php
```

3. Verifier qu'aucun secret n'est suivi par Git :

```bash
git status --ignored
```

4. Faire un commit clair.

## Outil de suivi

Un tableau de suivi peut etre tenu dans :

- GitHub Projects ;
- Trello ;
- Notion ;
- Jira.

Pour le rendu final, ajouter ici le lien vers l'outil choisi.
