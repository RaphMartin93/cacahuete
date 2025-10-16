<?php
// Fichier: /home/cacahuete/public_html/db_connect.php

require_once 'config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // En cas d'erreur de connexion
    // En développement, vous pouvez afficher l'erreur :
    // echo "Erreur de connexion à la base de données: " . $e->getMessage();
    // En production, affichez un message générique
    die("Désolé, une erreur interne est survenue.");
}
?>