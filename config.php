<?php
// Fichier: /home/cacahuete/public_html/config.php

// -------------------------------------------------------------
// SÉCURITÉ : EN-TÊTES HTTP (DOIVENT ÊTRE ENVOYÉS EN PREMIER)
// -------------------------------------------------------------
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");


// -------------------------------------------------------------
// --- GESTION DE L'ENVIRONNEMENT ET DES ERREURS ---
// -------------------------------------------------------------
define('ENVIRONMENT', 'production'); // METTEZ 'development' POUR DÉBOGUER

if (ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // Désactiver l'affichage en production
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED); 
}
// -------------------------------------------------------------


// -------------------------------------------------------------
// --- CONFIGURATION DE LA BASE DE DONNÉES ---
// -------------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'cacahuete_db'); 
define('DB_USER', 'mariadmin');
define('DB_PASS', '#aeUd4hWJvxrTcS_x9@s');
// -------------------------------------------------------------


// -------------------------------------------------------------
// --- CONFIGURATION DE L'APPLICATION ---
// -------------------------------------------------------------
define('ADMIN_USERNAME', 'admin'); 
// Chemin pour la gestion des liens si mod_rewrite n'est pas utilisé
define('BASE_PATH', '/cacahuete/'); 
// -------------------------------------------------------------


// -------------------------------------------------------------
// --- GESTION DES SESSIONS ---
// -------------------------------------------------------------
if (session_status() == PHP_SESSION_NONE) {
    // SÉCURITÉ : paramètres de session
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1); 
    
    session_start();
}
// -------------------------------------------------------------
?>