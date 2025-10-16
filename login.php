<?php
// Fichier: /home/cacahuete/public_html/login.php

require_once 'config.php';
require_once 'db_connect.php';

// Vérification de la méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['login_error'] = "Veuillez entrer votre nom d'utilisateur et mot de passe.";
    header('Location: index.php');
    exit;
}

try {
    // 1. Récupération de l'utilisateur basé sur le nom d'utilisateur
    $stmt = $pdo->prepare("SELECT id, username, password, fullname, is_admin FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // 2. Connexion réussie : Initialisation des variables de session
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['is_admin'] = (bool)$user['is_admin'];

        // 3. Redirection vers le tableau de bord
        header('Location: dashboard.php');
        exit;
    } else {
        // 4. Échec de la connexion
        $_SESSION['login_error'] = "Nom d'utilisateur ou mot de passe incorrect.";
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    // Gérer les erreurs de base de données (pour le debug)
    // echo "Erreur BDD: " . $e->getMessage();
    $_SESSION['login_error'] = "Une erreur est survenue lors de la tentative de connexion.";
    header('Location: index.php');
    exit;
}
?>