# Procédure de déploiement - alwaysdata

Cette procédure cible une application PHP 8.2, une base MariaDB/MySQL alwaysdata
et MongoDB Atlas.

Documentation officielle consultée :

- [Configurer PHP](https://help.alwaysdata.com/fr/docs/hebergement-web/langages/php/configuration/)
- [Créer un site web](https://help.alwaysdata.com/fr/docs/hebergement-web/sites/)
- [Installer une extension PHP](https://help.alwaysdata.com/fr/hebergement-web/langages/php/installer-une-extension/)
- [Installer les dépendances Composer](https://help.alwaysdata.com/fr/docs/hebergement-web/langages/php/installer-un-paquet/)
- [Connexion MariaDB/MySQL](https://help.alwaysdata.com/fr/docs/hebergement-web/bases-de-donnees/mariadb/)
- [Accès SSH](https://help.alwaysdata.com/fr/docs/hebergement-web/acces-distant/ssh/)
- [Messagerie alwaysdata](https://help.alwaysdata.com/fr/docs/emails/)

## 1. Préparer la version

En local :

```bash
composer validate --no-check-publish
composer test
git status
```

Fusionner la branche validée vers `main`, puis pousser. Ne jamais ajouter
`config/database.php`, `config/mongodb.php` ou `.env`.

Effectuer une sauvegarde de la base avant toute migration.

## 2. Configurer PHP

Dans l’administration alwaysdata :

1. ouvrir `Environnement > PHP` ;
2. choisir PHP 8.2 ou une version supérieure compatible ;
3. vérifier `pdo_mysql`, `mbstring`, `openssl` et `json`.

Installer l’extension MongoDB depuis SSH :

```bash
cd ~
ad_install_pecl mongodb
```

La commande indique le chemin du fichier `.so`. Ajouter ce chemin dans la
configuration PHP du compte ou du site, par exemple :

```ini
extension=/home/COMPTE/mongodb.so
```

Redémarrer le site, puis contrôler :

```bash
php -m | grep mongodb
php --ri mongodb
```

L’extension doit correspondre à la version majeure utilisée par
`mongodb/mongodb` dans `composer.lock`.

## 3. Transférer le code

Connexion :

```bash
ssh COMPTE@ssh-COMPTE.alwaysdata.net
```

Premier déploiement :

```bash
cd ~/www
git clone https://github.com/patounet3186/Viteetgourmand.git vite-et-gourmand
cd vite-et-gourmand
git switch main
composer install --no-dev --optimize-autoloader
```

Mise à jour :

```bash
cd ~/www/vite-et-gourmand
git pull --ff-only
composer install --no-dev --optimize-autoloader
```

## 4. Déclarer le site

Dans `Web > Sites` :

1. ajouter l’adresse en `*.alwaysdata.net` ou le domaine final ;
2. choisir le type `PHP` ;
3. sélectionner PHP 8.2 ;
4. définir la racine sur :

```text
/home/COMPTE/www/vite-et-gourmand/public
```

La racine publique ne doit pas être le dossier du dépôt : `config/`, `database/`
et `docs/` ne doivent pas être servis directement.

Activer HTTPS et la redirection HTTP vers HTTPS.

## 5. Configurer MySQL/MariaDB

Dans `Bases de données > MySQL`, créer la base et l’utilisateur, puis attribuer
les droits complets à cet utilisateur. La création se fait dans l’administration,
pas dans phpMyAdmin.

Créer `config/database.php` sur le serveur à partir de l’exemple :

```php
<?php

function getDatabase(): PDO
{
    return new PDO(
        'mysql:host=mysql-COMPTE.alwaysdata.net;dbname=COMPTE_BASE;charset=utf8mb4',
        'UTILISATEUR',
        'MOT_DE_PASSE',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
}
```

Protéger le fichier :

```bash
chmod 600 config/database.php
```

Pour une base vide :

```bash
mysql -h mysql-COMPTE.alwaysdata.net -u UTILISATEUR -p COMPTE_BASE \
  < database/schema.sql
```

Pour une base déjà utilisée par une ancienne version :

```bash
mysql -h mysql-COMPTE.alwaysdata.net -u UTILISATEUR -p COMPTE_BASE \
  < database/migrations/20260718_complete_ecf.sql

mysql -h mysql-COMPTE.alwaysdata.net -u UTILISATEUR -p COMPTE_BASE \
  < database/migrations/20260803_delivery_distance.sql
```

Ne pas importer le schéma complet par-dessus une base en production sans
sauvegarde.

## 6. Configurer MongoDB Atlas

Créer `config/mongodb.php` :

```php
<?php

return [
    'uri' => 'mongodb+srv://UTILISATEUR:MOT_DE_PASSE@CLUSTER.mongodb.net/',
    'database' => 'vite_et_gourmand',
    'collection' => 'reviews',
    'analytics_collection' => 'order_analytics',
];
```

Puis :

```bash
chmod 600 config/mongodb.php
```

Dans Atlas :

1. créer un utilisateur limité à `readWrite` sur la base ;
2. autoriser l’adresse IP sortante du serveur alwaysdata ;
3. exécuter `php database/apply_mongodb_indexes.php` ou
   `database/mongodb-indexes.js` dans la base ;
4. vérifier la connexion depuis la page d’accueil et l’administration.

Ne pas utiliser `0.0.0.0/0` durablement.

## 7. Variables et e-mails

Créer `.env` :

```dotenv
APP_ENV=production
APP_URL=https://VOTRE-DOMAINE
MAIL_FROM=no-reply@VOTRE-DOMAINE
COMPANY_EMAIL=contact@VOTRE-DOMAINE
COMPANY_LEGAL_NAME="Vite & Gourmand"
COMPANY_ADDRESS="ADRESSE COMPLÈTE"
COMPANY_SIRET=NUMÉRO_SIRET
```

```bash
chmod 600 .env
```

Créer les adresses nécessaires dans `Emails > Adresses`. Une application
hébergée sur les serveurs alwaysdata peut utiliser leur serveur d’envoi sans
authentification SMTP externe. Vérifier le domaine, SPF, DKIM et la réception.

Tester :

1. formulaire de contact ;
2. confirmation de commande ;
3. changement de statut ;
4. mot de passe oublié.

Consulter l’historique des e-mails et les logs en cas d’échec.

## 8. Recette de production

| Scénario | Résultat attendu |
| --- | --- |
| Accueil et menus | HTTP 200, images et styles visibles |
| Route inconnue | HTTP 404 |
| Inscription et connexion | redirection selon le rôle |
| Mot de passe oublié | e-mail reçu, lien valable une heure |
| Commande | total exact, stock décrémenté, historique créé |
| Annulation | stock restauré |
| Employé | accès commandes, menus, plats, horaires, avis |
| Client sur back-office | refus d’accès |
| Admin | employés et statistiques accessibles |
| MongoDB coupé | accueil disponible et statistiques SQL de secours |
| Mobile | aucune coupure à 390 px |

Commandes serveur :

```bash
composer test
php -l public/index.php
php -r "require 'vendor/autoload.php'; echo 'autoload ok', PHP_EOL;"
```

Le test d’intégration est réversible, mais il écrit brièvement dans la base :

```bash
composer test:integration
```

## 9. Logs et diagnostic

Consulter :

- l’extrait `Logs` dans l’administration ;
- `$HOME/admin/logs/php/php.log` ;
- les logs Apache du compte ;
- l’historique des e-mails ;
- MongoDB Atlas `Network Access` et `Database Access`.

L’application masque le détail des exceptions en production et les envoie dans
le journal PHP.

## 10. Retour arrière

1. noter le commit actuellement déployé ;
2. restaurer le commit stable précédent avec Git sans supprimer les fichiers de
   configuration locaux ;
3. exécuter `composer install --no-dev --optimize-autoloader` ;
4. restaurer la sauvegarde SQL uniquement si une migration de données l’exige ;
5. redémarrer le site et refaire la recette prioritaire.

Après validation, renseigner l’URL de production dans le README, le dossier de
preuve et le document remis au jury.
