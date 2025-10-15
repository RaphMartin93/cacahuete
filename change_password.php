<?php
// Fichier: /home/secretsanta/public_html/change_password.php

require_once 'config.php';
require_once 'auth_check.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_id = $_SESSION['user_id'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Veuillez remplir tous les champs.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Le nouveau mot de passe et sa confirmation ne correspondent pas.";
    } 
    // LA CONDITION DE LONGUEUR MINIMALE (strlen($new_password) < 8) EST RETIRÉE
    else {
        try {
            // 1. Récupérer le hash du mot de passe actuel
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($current_password, $user['password'])) {
                // 2. Le mot de passe actuel est correct, on met à jour
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt_update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                
                if ($stmt_update->execute([$new_hash, $user_id])) {
                    $message = "Votre mot de passe a été modifié avec succès.";
                } else {
                    $error = "Erreur lors de la mise à jour dans la base de données.";
                }

            } else {
                $error = "Le mot de passe actuel est incorrect.";
            }

        } catch (PDOException $e) {
            $error = "Erreur de base de données : " . $e->getMessage();
        }
    }
}

require_once 'template/header.php';
?>

<h1 class="mb-4">Changer votre mot de passe</h1>

<?php if ($message): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card p-4 shadow mx-auto" style="max-width: 500px;">
    
    <form method="POST" action="change_password.php">
        
        <div class="mb-3">
            <label for="current_password" class="form-label">Mot de passe actuel</label>
            <input type="password" class="form-control" id="current_password" name="current_password" required>
        </div>

        <div class="mb-3">
            <label for="new_password" class="form-label">Nouveau mot de passe</label>
            <input type="password" class="form-control" id="new_password" name="new_password" required>
        </div>

        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        
        <button type="submit" class="btn btn-primary w-100 mt-3">
            Modifier le mot de passe
        </button>
    </form>
</div>

<?php require_once 'template/footer.php'; ?>