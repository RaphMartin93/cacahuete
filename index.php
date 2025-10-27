<?php

session_start(); // S'assurer que la session est démarrée pour les messages d'erreur

require_once 'config.php';

// Si l'utilisateur est déjà connecté, le rediriger vers le tableau de bord
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Récupérer un éventuel message d'erreur après une tentative de login ratée
$error_message = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']); // Supprimer le message après affichage

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Cacahuète - Connexion</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <link rel="icon" type="image/x-icon" href="img/favicon.ico">

    <style>
        body {
            /* Assure que la page utilise toute la hauteur de la fenêtre */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa; /* Fond gris clair de Bootstrap */
        }
        .login-card {
            max-width: 400px;
            width: 100%;
        }
    </style>
</head>
<body class="login-page"> 

    <div class="card p-4 shadow-lg login-card border-danger">
        <h2 class="card-title text-center text-danger mb-4">Connexion à votre compte</h2>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            
            <div class="mb-3">
                <label for="username" class="form-label">Nom d'utilisateur</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-danger w-100 mt-3">
                Se connecter
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>