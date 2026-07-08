# Manuel utilisateur - Vite & Gourmand

## Objectif

Ce manuel explique les principaux parcours possibles dans l'application Vite & Gourmand.

## Acceder a l'application

En local :

```text
http://localhost/ECF-2026/public/
```

## Parcours visiteur

Un visiteur peut :

- consulter la page d'accueil ;
- consulter la liste des menus ;
- filtrer les menus par prix, theme, regime et nombre de personnes ;
- ouvrir le detail d'un menu ;
- acceder aux pages d'inscription et de connexion.

## Creation de compte

1. Cliquer sur `Inscription`.
2. Remplir les champs demandes :
   - prenom ;
   - nom ;
   - email ;
   - telephone ;
   - adresse ;
   - code postal ;
   - ville ;
   - mot de passe.
3. Le mot de passe doit contenir au minimum :
   - 10 caracteres ;
   - une majuscule ;
   - une minuscule ;
   - un chiffre ;
   - un caractere special.
4. Valider le formulaire.

Le compte cree possede le role `user`.

## Connexion

1. Cliquer sur `Connexion`.
2. Saisir l'adresse email et le mot de passe.
3. Apres connexion, l'utilisateur est redirige vers `Mon espace`.

## Commander un menu

1. Aller sur la page `Menus`.
2. Cliquer sur `Voir le detail`.
3. Lire les conditions du menu.
4. Cliquer sur `Commander ce menu`.
5. Remplir :
   - date de prestation ;
   - heure souhaitee ;
   - adresse de livraison ;
   - ville ;
   - nombre de personnes.
6. Valider la commande.

Si la ville n'est pas Bordeaux, des frais de livraison sont ajoutes.

## Espace utilisateur

Depuis `Mon espace`, l'utilisateur peut :

- consulter ses informations de session ;
- consulter ses commandes ;
- se deconnecter.

Fonctionnalites a completer :

- modification des informations personnelles ;
- annulation de commande tant que le statut le permet ;
- suivi detaille des statuts ;
- depot d'avis apres commande terminee.

## Parcours employe

A completer.

L'employe devra pouvoir :

- gerer les menus ;
- gerer les plats ;
- modifier les horaires ;
- consulter et filtrer les commandes ;
- mettre a jour les statuts de commande ;
- valider ou refuser les avis clients.

## Parcours administrateur

A completer.

L'administrateur devra pouvoir :

- creer des comptes employes ;
- desactiver des comptes employes ;
- acceder aux fonctionnalites employe ;
- consulter des statistiques ;
- consulter le chiffre d'affaires par menu.
