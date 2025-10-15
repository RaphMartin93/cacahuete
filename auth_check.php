<?php
// Fichier: /home/secretsanta/public_html/auth_check.php
// Le fichier config.php DOIT être inclus avant celui-ci dans les pages qui l'utilisent.

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Si l'utilisateur n'est pas connecté, le rediriger vers la page de login
    header('Location: index.php');
    exit;
}

// L'utilisateur est connecté. Les variables de session (user_id, fullname, is_admin) sont disponibles.

// Vous pouvez également ajouter ici la connexion à la BDD pour un accès facile
require_once 'db_connect.php'; 
?>