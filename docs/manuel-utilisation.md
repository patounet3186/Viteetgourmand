# Manuel utilisateur - Vite & Gourmand

## Objectif

Ce manuel explique les principaux parcours possibles dans l'application Vite & Gourmand.

## Accéder à l'application

En local :

```text
http://localhost/ECF-2026/public/
```

## Parcours visiteur

Un visiteur peut :

- consulter la page d'accueil ;
- consulter la liste des menus ;
- filtrer les menus par prix, thème, régime et nombre de personnes ;
- ouvrir le détail d'un menu ;
- accéder aux pages d'inscription et de connexion.

## Création de compte

1. Cliquer sur `Inscription`.
2. Remplir les champs demandés :
   - prénom ;
   - nom ;
   - email ;
   - téléphone ;
   - adresse ;
   - code postal ;
   - ville ;
   - mot de passe.
3. Le mot de passe doit contenir au minimum :
   - 10 caractères ;
   - une majuscule ;
   - une minuscule ;
   - un chiffre ;
   - un caractère spécial.
4. Valider le formulaire.

Le compte créé possède le rôle `user`.

## Connexion

1. Cliquer sur `Connexion`.
2. Saisir l'adresse email et le mot de passe.
3. Après connexion, l'utilisateur est redirigé vers `Mon espace`.

## Commander un menu

1. Aller sur la page `Menus`.
2. Cliquer sur `Voir le détail`.
3. Lire les conditions du menu.
4. Cliquer sur `Commander ce menu`.
5. Remplir :
   - date de prestation ;
   - heure souhaitée ;
   - adresse de livraison ;
   - ville ;
   - nombre de personnes.
6. Valider la commande.

Si la ville n'est pas Bordeaux, des frais de livraison sont ajoutés.

## Espace utilisateur

Depuis `Mon espace`, l'utilisateur peut :

- consulter ses informations de session ;
- consulter ses commandes ;
- suivre le statut de ses commandes ;
- déposer un avis lorsqu'une commande est au statut `livre` ou `terminee` ;
- se déconnecter.

## Déposer un avis

1. Se connecter avec un compte client.
2. Aller dans `Mon espace`.
3. Vérifier qu'une commande est au statut `livre` ou `terminee`.
4. Cliquer sur `Déposer un avis`.
5. Choisir une note de 1 à 5.
6. Saisir un commentaire.
7. Valider.

L'avis est envoyé dans MongoDB Atlas avec le statut `pending`.

## Parcours employé

Un employé peut :

- accéder à la page `Commandes` depuis la navigation ;
- consulter les commandes clients ;
- filtrer les commandes par statut ;
- modifier le statut d'une commande.

Les statuts disponibles sont :

- `nouvelle`
- `accepte`
- `en_preparation`
- `en_livraison`
- `livre`
- `attente_materiel`
- `terminee`
- `annulee`

## Parcours administrateur

Un administrateur peut :

- accéder au tableau de bord administrateur ;
- consulter le nombre total de commandes ;
- consulter le chiffre d'affaires total ;
- consulter les statistiques par menu ;
- consulter le nombre d'avis en attente ;
- consulter la note moyenne ;
- valider ou refuser les avis clients.

## Valider ou refuser un avis

1. Se connecter avec un compte administrateur.
2. Aller dans `Admin`.
3. Consulter la section `Avis clients à valider`.
4. Cliquer sur `Valider` ou `Refuser`.

Lorsqu'un avis est validé, son statut MongoDB passe à `validated`.

Lorsqu'un avis est refusé, son statut MongoDB passe à `refused`.

## Rôles utilisateurs

Les rôles et l'état des comptes sont gérés par l'administrateur depuis la page `Accès`.

Cette page permet de modifier le rôle d'un compte et de l'activer ou le désactiver, sans afficher les données personnelles non nécessaires.