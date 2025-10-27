<?php 
// Fichier: /home/cacahuete/public_html/template/header.php

// Ce fichier DOIT être inclus après auth_check.php pour avoir accès aux variables de session

$is_admin = $_SESSION['is_admin'] ?? false;
$username_display = htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username'] ?? 'Utilisateur');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Cacahuète - Accueil</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <link rel="icon" type="image/x-icon" href="img/favicon.ico">

</head>
<body class="logged-in">

<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-danger fixed-top shadow">
        <div class="container-fluid">
            <span class="navbar-text welcome-message me-auto text-white">
                Bienvenue, <?php echo $username_display; ?>
            </span>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto"> 
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="users_list.php">Liste des Participants</a></li>
                    <li class="nav-item"><a class="nav-link" href="change_password.php">Changer MDP</a></li>
                    <?php if ($is_admin): ?>
                        <li class="nav-item"><a class="nav-link" href="admin.php">Administration</a></li>
                        <li class="nav-item"><a class="nav-link" href="admin_add_user.php">Ajouter Participant</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<div class="container pt-5 mt-4">