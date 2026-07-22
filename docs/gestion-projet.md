# Documentation de gestion de projet

## Cadre

Le projet a été conduit par incréments fonctionnels : une fonctionnalité est
développée, testée dans le navigateur, relue, puis versionnée avant la suivante.
Le backlog est priorisé selon le parcours utilisateur et les exigences de l’ECF.

- Dépôt : <https://github.com/patounet3186/Viteetgourmand>
- Tableau de suivi externe : **lien à renseigner avant le rendu**
- Branche de finalisation : `feature/menu-dishes-management`

## Organisation Git

| Type | Rôle |
| --- | --- |
| `main` | version destinée à la production |
| `develop` | intégration des fonctionnalités validées |
| `feature/*` | développement isolé d’un domaine |

Branches utilisées :

```text
feature/auth
feature/employee-space
feature/employee-management
feature/ui-docs-finalization
feature/menu-dishes-management
```

Les fichiers de secrets, `vendor/`, `.vscode/` et les fichiers temporaires sont
exclus par `.gitignore`.

## Backlog final

| Epic | Élément | Priorité | État |
| --- | --- | --- | --- |
| Socle | Architecture MVC et autoload PSR-4 | Haute | Terminé |
| Socle | Routage, erreurs 404/500 et URL dynamiques | Haute | Terminé |
| Catalogue | Liste, détail, galerie et composition | Haute | Terminé |
| Catalogue | Filtres prix, thème, régime, personnes | Haute | Terminé |
| Catalogue | CRUD menus et plats, stock, archivage | Haute | Terminé |
| Comptes | Inscription, connexion, profil et rôles | Haute | Terminé |
| Comptes | Mot de passe oublié avec jeton expirant | Haute | Terminé |
| Commandes | Calcul du prix, remise et livraison | Haute | Terminé |
| Commandes | Création, modification et annulation client | Haute | Terminé |
| Commandes | Cycle employé et historique horodaté | Haute | Terminé |
| Commandes | Notifications et e-mails métier | Moyenne | Terminé |
| Avis | Dépôt et modération dans MongoDB | Haute | Terminé |
| Administration | Employés, badges et horaires | Haute | Terminé |
| Administration | Statistiques MongoDB et secours SQL | Haute | Terminé |
| Conformité | Contact, pages légales et confidentialité | Haute | Terminé |
| Qualité | CSRF, sessions, contrôles d’accès et XSS | Haute | Terminé |
| Qualité | Responsive, accessibilité et tests | Haute | Terminé |
| Livrables | Documentation et maquettes | Haute | Terminé |
| Livraison | Déploiement et recette de production | Haute | À exécuter |

## Jalons

1. **Socle public** : accueil, menus et base relationnelle.
2. **Comptes** : inscription, connexion et redirections par rôle.
3. **Commande** : parcours client et gestion employé.
4. **Administration** : employés, avis, statistiques et horaires.
5. **Consolidation MVC** : séparation contrôleurs, modèles et vues.
6. **Finalisation ECF** : sécurité, responsive, tests et documentation.
7. **Mise en production** : import SQL, variables, e-mails et recette.

## Définition de terminé

Une tâche est terminée lorsque :

1. le scénario nominal fonctionne dans le navigateur ;
2. les erreurs de saisie restent compréhensibles ;
3. les droits visiteur, client, employé et administrateur sont vérifiés ;
4. les formulaires sensibles possèdent un jeton CSRF ;
5. les données affichées sont échappées ;
6. la syntaxe PHP et JavaScript est valide ;
7. `composer test` passe ;
8. la documentation touchée est mise à jour.

## Stratégie de test

| Niveau | Outil | Couverture |
| --- | --- | --- |
| Statique | `php -l`, `node --check` | syntaxe |
| Architecture | `tests/mvc_architecture.php` | classes, vues et routage |
| Domaine | `tests/domain_rules.php` | prix, transitions et URL |
| Intégration | `tests/database_integration.php` | transactions, relations, stock, historique |
| HTTP | PowerShell + Apache local | codes 200/404 et erreurs PHP |
| Visuel | Playwright | 3 pages en ordinateur et mobile |
| Manuel | navigateur | rôles et scénarios métier |

## Risques suivis

| Risque | Réponse |
| --- | --- |
| Secret publié dans Git | fichiers réels ignorés, modèles d’exemple fournis |
| MongoDB indisponible | message clair et statistiques de secours SQL |
| Échec e-mail | journalisation sans perte de la commande |
| Double commande du dernier stock | transaction et verrou `FOR UPDATE` |
| Accès d’un client au back-office | contrôle serveur `requireRole` |
| Annulation employé sans contact | méthode et motif obligatoires |
| Dépendance à un CDN | Bootstrap et Chart.js conservés localement |
| Mise à jour d’une ancienne base | migration additive séparée |

## Historique significatif

Les commits du dépôt montrent l’évolution incrémentale, notamment :

- `Add optimized WebP images` ;
- `Add CSRF protection and admin acces management` ;
- `Ajoute la modification du profil client` ;
- `Ajoute la gestion des employés et les redirections par rôle` ;
- `refactor: structurer l'application selon le modèle MVC`.

Le lot final doit faire l’objet d’un commit dédié après recette, puis être fusionné
vers `develop` et `main` selon le flux retenu.
