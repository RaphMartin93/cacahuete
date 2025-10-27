# 🥜 Cacahuète - L'Application de Tirage au Sort Secret Santa

Bienvenue sur le dépôt de **Cacahuète**, un projet simple et efficace développé en **PHP** pour gérer un tirage au sort **Secret Santa** (Père Noël Secret) au sein d'un groupe d'utilisateurs.

-----

## ✨ Fonctionnalités

  * **Gestion Utilisateurs** : Connexion/déconnexion sécurisée des participants.
  * **Connexion Administrateur** : Un compte dédié pour lancer et réinitialiser le tirage.
  * **Tirage Unique** : Algorithme garantissant que personne ne se tire au sort soi-même.
  * **Sécurité Renforcée** : Protection contre les injections SQL (via PDO), le XSS et la fixation de session.

-----

## 🛠️ Stack Technique

Le projet est minimaliste et bâti sur une stack classique et robuste :

  * **Backend** : PHP 8.3+
  * **Base de Données** : MariaDB / MySQL (via **PDO**)
  * **Serveur Web** : Apache HTTP Server

-----

## 🚀 Installation et Lancement

### Prérequis

Assurez-vous d'avoir installé les composants suivants sur votre serveur (**Rocky 10** recommandé) :

  * **PHP 8.3** avec les extensions `pdo_mysql` et `session`.
  * **MariaDB** (ou MySQL).
  * **Apache HTTP Server** avec le module `mod_rewrite` activé.

### Configuration du Serveur Web (Apache)

Pour que l'application fonctionne sous le chemin `/cacahuete`, il est impératif d'utiliser un **Virtual Host** avec une règle `mod_rewrite` :

1.  **Dossier Racine** : Cloner ce dépôt dans le dossier `/home/cacahuete/public_html/`.

2.  **Configuration Vhost** : S'assurer que votre configuration Apache contient un `Alias` et les règles `Rewrite` pour retirer le préfixe `/cacahuete/` en interne :

    Exemple simplifié de `/etc/httpd/conf.d/votredomaine.conf` :

    ```apache
    Alias /cacahuete /home/cacahuete/public_html
    <Directory /home/cacahuete/public_html>
        AllowOverride All
        RewriteEngine On
        # Assure que les requêtes votredomaine.com/cacahuete/page sont traitées comme /page
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^cacahuete/(.*)$ /$1 [L]
    </Directory>
    ```

### Configuration de la Base de Données

1.  **Créer la Base de Données** : Créez une base de données nommée `cacahuete_db` et un utilisateur `mariadmin` avec le mot de passe approprié.
2.  **Importer le Schéma** : Exécutez le schéma SQL (non inclus ici, mais nécessaire pour la table `users`).
3.  **Mettre à Jour `config.php`** :
      * Remplissez les constantes `DB_USER`, `DB_PASS`, et `DB_NAME`.
      * Assurez-vous que `ENVIRONMENT` est réglé sur `'production'` pour masquer les erreurs en ligne.

### Accès

Une fois le serveur redémarré :

Naviguez vers : $\rightarrow$ `http://votredomaine.com/cacahuete/`

-----

## 🛡️ Sécurité

Le projet intègre nativement plusieurs protections :

  * **Injections SQL** : Requêtes paramétrées avec **PDO**.
  * **XSS** : Utilisation de `htmlspecialchars()` sur toutes les sorties de données utilisateur.
  * **Sessions** : Régénération d'ID après connexion et paramètres de cookie `HttpOnly` et `Secure` (si HTTPS est activé).

-----

## 📝 Licence

Ce projet est distribué sous la **licence MIT**. Voir le fichier `LICENSE` pour plus de détails.
