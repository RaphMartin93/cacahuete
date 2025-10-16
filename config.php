<?php
// Fichier: /home/secretsanta/public_html/config.php

// -------------------------------------------------------------
// --- PARAMÈTRES DE DEBUG (À DÉSACITVER EN PRODUCTION) ---
// -------------------------------------------------------------
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// -------------------------------------------------------------


// -------------------------------------------------------------
// --- CONFIGURATION DE LA BASE DE DONNÉES ---
// -------------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'cacahuete_db'); 
define('DB_USER', 'mariadmin'); // VOTRE UTILISATEUR BDD
define('DB_PASS', '#aeUd4hWJvxrTcS_x9@s'); // VOTRE MOT DE PASSE BDD
// -------------------------------------------------------------


// -------------------------------------------------------------
// --- CONFIGURATION DE L'APPLICATION ---
// -------------------------------------------------------------
// Nom d'utilisateur du compte Administrateur (celui à exclure du tirage)
// Si votre login admin est 'admin', laissez 'admin'. Si c'est 'rocky', mettez 'rocky'.
define('ADMIN_USERNAME', 'admin'); 
// -------------------------------------------------------------


// -------------------------------------------------------------
// --- GESTION DES SESSIONS ---
// -------------------------------------------------------------
// Démarre la session si elle n'a pas déjà été démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// -------------------------------------------------------------