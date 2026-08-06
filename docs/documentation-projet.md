# Documentation projet - Vite & Gourmand

## Contexte

Julie et José dirigent Vite & Gourmand, un traiteur fictif situé à Bordeaux.
L’entreprise souhaite remplacer la prise de commande dispersée par une
application unique qui présente son catalogue et suit chaque prestation.

## Objectifs

- rendre les menus et leurs conditions accessibles en ligne ;
- permettre une commande autonome et un calcul tarifaire transparent ;
- donner au client une visibilité sur l’avancement ;
- fournir aux employés des outils de production simples ;
- permettre à l’administrateur de gérer l’équipe et de suivre l’activité ;
- publier uniquement les avis modérés ;
- disposer d’une application responsive, accessible et déployable.

## Acteurs

| Acteur | Besoin principal |
| --- | --- |
| Visiteur | découvrir l’offre et contacter le traiteur |
| Client | commander, suivre et évaluer une prestation |
| Employé | préparer l’offre et traiter les commandes |
| Administrateur | piloter l’activité et gérer les accès |

## Périmètre fonctionnel

### Catalogue public

- accueil avec identité, horaires et avis validés ;
- catalogue filtrable sans rechargement ;
- fiche détaillée avec galerie, plats, allergènes, prix et conditions.

### Comptes

- inscription client ;
- authentification et déconnexion ;
- réinitialisation du mot de passe ;
- modification des coordonnées ;
- redirection vers la page utile selon le rôle.

### Commandes

- coordonnées client préremplies ;
- date, heure, adresse, ville, distance depuis Bordeaux et nombre de personnes ;
- minimum du menu ;
- remise de 10 % à partir du minimum plus cinq personnes ;
- frais de livraison distincts ;
- suivi par statut et historique ;
- modification et annulation avant acceptation ;
- notifications et e-mails.

### Back-office

- gestion des menus, galeries, plats, stocks et horaires ;
- filtres et traitement des commandes ;
- annulation documentée après contact client ;
- modération des avis ;
- création et désactivation des employés ;
- statistiques par menu et période.

## User stories et acceptation

### US01 - Filtrer les menus

**En tant que** visiteur, **je veux** filtrer les menus **afin de** trouver une
offre adaptée.

Critères :

- prix minimum et maximum disponibles ;
- thème, régime et nombre de personnes combinables ;
- résultat mis à jour sans rechargement ;
- message clair lorsqu’aucun menu ne correspond.

### US02 - Commander

**En tant que** client, **je veux** commander un menu **afin de** réserver une
prestation.

Critères :

- connexion obligatoire ;
- menu actif et stock positif ;
- date future et minimum respecté ;
- prix détaillé avant confirmation ;
- stock et commande enregistrés atomiquement ;
- historique et confirmation créés.

### US03 - Modifier avant acceptation

**En tant que** client, **je veux** corriger ma commande **afin de** rectifier
les informations de livraison.

Critères :

- action visible uniquement au statut `nouvelle` ;
- menu non remplaçable ;
- prix recalculé côté serveur ;
- modification ajoutée à l’historique.

### US04 - Traiter une commande

**En tant qu’** employé, **je veux** faire évoluer le statut **afin de** suivre
la préparation.

Critères :

- transitions limitées au cycle défini ;
- chaque changement est horodaté ;
- client notifié ;
- annulation avec contact et motif obligatoires.

### US05 - Gérer le catalogue

**En tant qu’** employé, **je veux** gérer les menus et plats **afin de** tenir
l’offre à jour.

Critères :

- menu avec entrée, plat et dessert ;
- galerie de six images maximum ;
- plat utilisé non supprimable ;
- menu archivable sans casser les anciennes commandes.

### US06 - Déposer et modérer un avis

**En tant que** client, **je veux** évaluer une prestation terminée.

Critères :

- une commande appartient au client ;
- statut `terminee` requis ;
- un seul avis par commande ;
- note de 1 à 5 et commentaire d’au moins 10 caractères ;
- publication uniquement après validation.

### US07 - Gérer les employés

**En tant qu’** administrateur, **je veux** créer ou désactiver un employé.

Critères :

- aucun administrateur créé depuis l’interface ;
- e-mail unique ;
- compte inactif incapable de se connecter ;
- clients non modifiables depuis cette gestion.

### US08 - Consulter l’activité

**En tant qu’** administrateur, **je veux** filtrer les statistiques **afin de**
mesurer les ventes.

Critères :

- filtre menu et période ;
- commandes annulées exclues ;
- nombre et chiffre d’affaires par menu ;
- graphique et tableau ;
- secours SQL si MongoDB échoue.

## Exigences non fonctionnelles

| Domaine | Exigence |
| --- | --- |
| Responsive | parcours utilisable à 390 px et sur ordinateur |
| Accessibilité | structure sémantique, clavier, labels, contrastes |
| Sécurité | CSRF, PDO préparé, XSS, sessions, rôles |
| Performance | images WebP et ressources front-end locales |
| Maintenabilité | MVC, PSR-4, services et migration séparée |
| Résilience | panne MongoDB non bloquante pour le cœur SQL |
| Traçabilité | historique des statuts et documentation Git |

## Choix structurants

### PHP MVC sans framework

Ce choix rend visibles les notions attendues à l’ECF : routage, contrôleurs,
modèles, vues, sessions, PDO et sécurité. L’autoload PSR-4 évite les inclusions
manuelles dispersées.

### MySQL et MongoDB

MySQL porte les relations et transactions critiques. MongoDB répond au besoin
NoSQL pour les avis et les agrégations statistiques.

### Archivage

Les menus ne sont pas supprimés lorsqu’ils peuvent être liés à des commandes.
L’archivage protège l’historique.

### Ressources locales

Bootstrap et Chart.js sont inclus dans le dépôt afin de garantir la présentation
même si un CDN est indisponible.

## Hors périmètre

- paiement bancaire en ligne ;
- génération de facture comptable ;
- planification de tournées de livraison ;
- gestion fine de permissions au-delà des trois rôles ;
- administration de plusieurs établissements.

## Livrables associés

- code et historique Git ;
- SQL complet et migration ;
- README d’installation ;
- manuel utilisateur ;
- charte graphique ;
- wireframes et maquettes ;
- documentation technique avec diagrammes ;
- procédure de déploiement ;
- synthèse de révision.

## Liens de livraison

Les éléments externes associés au projet sont :

1. application : <https://arkflo.alwaysdata.net> ;
2. dépôt : <https://github.com/patounet3186/Viteetgourmand> ;
3. tableau de suivi : <https://github.com/users/patounet3186/projects/4> ;
4. captures de recette : `docs/maquettes/`.

L’identité légale définitive de l’éditeur reste à confirmer dans la
configuration de production et les mentions légales.
