# Manuel utilisateur - Vite & Gourmand

## Accès

En local :

```text
http://localhost/ECF-2026/public/
```

En production : <https://arkflo.alwaysdata.net>.

L’interface adapte la navigation au rôle connecté. Les nombres rouges ou jaunes
signalent les nouvelles commandes, les changements de statut ou les avis à
modérer.

## Visiteur

### Consulter un menu

1. Ouvrir `Menus`.
2. Saisir éventuellement un prix minimum ou maximum.
3. Choisir un thème, un régime et/ou un nombre de personnes.
4. Les résultats sont filtrés immédiatement, sans rechargement.
5. Sélectionner `Voir le détail`.

La fiche affiche les photos, la description, le thème, le régime, le minimum,
le stock, la composition, les allergènes, le prix de base et les conditions.

### Créer un compte

1. Ouvrir `Inscription`.
2. Renseigner identité, e-mail et mot de passe.
3. Utiliser au moins 10 caractères avec minuscule, majuscule, chiffre et symbole.
4. Accepter les CGV et la politique de confidentialité.
5. Valider puis se connecter.

### Réinitialiser le mot de passe

1. Depuis `Connexion`, choisir `Mot de passe oublié`.
2. Saisir l’adresse e-mail du compte.
3. Ouvrir le lien reçu, valable une heure et utilisable une fois.
4. Définir un nouveau mot de passe conforme.

Le message affiché reste identique même si l’adresse n’existe pas afin de ne pas
révéler les comptes enregistrés.

### Contacter l’entreprise

1. Ouvrir `Contact`.
2. Saisir nom, e-mail, sujet et message.
3. Valider.

La demande est enregistrée en base et transmise à l’adresse de l’entreprise.

## Client

### Passer une commande

1. Se connecter avec un compte client.
2. Ouvrir un menu disponible puis `Commander ce menu`.
3. Vérifier les coordonnées préremplies.
4. Choisir une date future, une heure, une adresse, une ville et le nombre de personnes.
5. Indiquer la distance depuis Bordeaux, ou `0` pour une livraison à Bordeaux.
6. Consulter le détail du prix calculé en direct.
7. Confirmer.

Règles de prix :

- prix proportionnel au minimum du menu ;
- remise de 10 % à partir de cinq personnes au-dessus du minimum ;
- livraison gratuite à Bordeaux ;
- livraison à 5 € plus 0,59 € par kilomètre dans les autres communes.

Une confirmation est préparée par e-mail. Le stock du menu diminue dans la même
transaction que l’enregistrement de la commande.

### Suivre une commande

1. Ouvrir `Mon espace`.
2. Repérer la commande dans `Mes commandes`.
3. Choisir `Détail`.

Le détail présente le prix, l’adresse, le statut actuel et l’historique horodaté.
L’ouverture du détail marque les notifications correspondantes comme lues.

### Modifier ou annuler

Ces actions sont possibles uniquement tant que la commande est `Nouvelle`.

- `Modifier` permet de changer date, heure, adresse, ville, distance et personnes.
- Dans le détail, `Annuler la commande` restaure automatiquement le stock.

Le menu choisi ne peut pas être remplacé : il faut annuler puis recommander.

### Déposer un avis

Quand la commande est `Terminée`, le bouton `Donner mon avis` apparaît :

1. choisir une note de 1 à 5 ;
2. saisir un commentaire d’au moins 10 caractères ;
3. envoyer l’avis.

L’avis reste invisible jusqu’à sa validation par un employé ou administrateur.

### Modifier le profil

Dans `Mon espace`, modifier les coordonnées puis choisir
`Enregistrer mes informations`. La nouvelle adresse sera utilisée pour
préremplir les commandes suivantes.

## Employé

### Gérer les commandes

1. Ouvrir `Commandes`.
2. Filtrer par statut ou rechercher le nom/e-mail d’un client.
3. Choisir le prochain statut autorisé.
4. Valider la mise à jour.

Cycle habituel :

```text
Nouvelle
→ Acceptée
→ En préparation
→ En livraison
→ Livrée
→ En attente du matériel
→ Terminée
```

Une commande livrée peut passer directement à `Terminée` si aucun matériel
n’est à récupérer.

Pour annuler, contacter d’abord le client, choisir `Téléphone` ou `E-mail`, puis
saisir un motif de 10 à 500 caractères. Le stock est restauré.

### Gérer les menus

Dans `Gestion des menus` :

- modifier rapidement le stock et la visibilité ;
- créer un menu ;
- ouvrir `Modifier` pour changer toutes ses informations ;
- archiver un menu pour le rendre inactif avec un stock à zéro.

Chaque menu doit posséder au moins une entrée, un plat et un dessert. La galerie
accepte jusqu’à six chemins locaux ou URL d’images.

### Gérer les plats

Dans `Gestion des plats` :

- ajouter une entrée, un plat ou un dessert ;
- modifier nom, catégorie, description et allergènes ;
- supprimer uniquement un plat qui n’est rattaché à aucun menu.

### Gérer les horaires

Dans `Horaires`, définir pour chaque jour :

- fermé ;
- une première plage ;
- éventuellement une seconde plage.

Les horaires sont immédiatement repris dans le pied de page public.

### Modérer les avis

Dans `Avis`, lire les avis en attente puis choisir `Valider` ou `Refuser`.
Seuls les avis validés peuvent apparaître sur l’accueil.

## Administrateur

### Gérer les employés

Dans `Accès` :

1. saisir prénom, nom, e-mail et mot de passe temporaire ;
2. créer le compte employé ;
3. activer ou désactiver un compte selon les besoins.

Un employé désactivé ne peut plus se connecter. L’administrateur n’accède pas
aux comptes clients dans cet écran ; leurs coordonnées sont visibles uniquement
lorsqu’elles sont nécessaires au traitement d’une commande.

### Consulter les statistiques

Dans `Admin` :

1. choisir éventuellement un menu ;
2. saisir une date de début et/ou de fin ;
3. choisir `Filtrer`.

La page affiche le nombre de commandes, le chiffre d’affaires, les avis en
attente, la note moyenne, un tableau et un graphique par menu. Les commandes
annulées sont exclues.

## Déconnexion

Utiliser `Déconnexion` dans la navigation ou dans `Mon espace`. La déconnexion
est envoyée par formulaire sécurisé afin d’éviter une action déclenchée par un
simple lien externe.

## Messages fréquents

| Message | Action |
| --- | --- |
| Formulaire expiré | recharger la page et recommencer |
| Accès refusé | se connecter avec le rôle attendu |
| Menu indisponible | choisir un autre menu ou contacter l’entreprise |
| Avis temporairement indisponibles | réessayer lorsque MongoDB est accessible |
| Trop de tentatives | attendre quinze minutes avant une nouvelle connexion |
