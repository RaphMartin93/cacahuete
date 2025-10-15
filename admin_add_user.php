<?php
// Fichier: /home/secretsanta/public_html/admin_add_user.php

require_once 'config.php';
require_once 'auth_check.php';

// --- VÉRIFICATION DE SÉCURITÉ ADMIN ---
if (!$_SESSION['is_admin']) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    // Le mot de passe initial est souvent simple ou généré par l'admin. 
    // Ici, nous utilisons un mot de passe par défaut.
    $initial_password = 'password123'; // Définissez un mot de passe initial sécurisé !

    if (empty($username) || empty($fullname)) {
        $error = "Veuillez remplir l'Identifiant et le Nom Complet.";
    } elseif (!preg_match('/^[a-z0-9_]+$/i', $username)) {
        $error = "L'Identifiant ne doit contenir que des lettres, chiffres et underscores.";
    } else {
        try {
            // Vérifier si l'identifiant existe déjà
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt_check->execute([$username]);

            if ($stmt_check->fetchColumn() > 0) {
                $error = "Cet identifiant ('" . htmlspecialchars($username) . "') est déjà utilisé.";
            } else {
                // Hachage du mot de passe
                $password_hash = password_hash($initial_password, PASSWORD_DEFAULT);

                // Insertion du nouvel utilisateur
                $stmt_insert = $pdo->prepare(
                    "INSERT INTO users (username, fullname, password_hash, is_admin, gift_list_url) 
                     VALUES (?, ?, ?, 0, '')"
                );
                
                if ($stmt_insert->execute([$username, $fullname, $password_hash])) {
                    $message = "L'utilisateur <strong>" . htmlspecialchars($fullname) . "</strong> a été ajouté avec succès.
                                <br>Mot de passe initial : <code>" . htmlspecialchars($initial_password) . "</code> (Il devra le changer).";
                    
                    // Optionnel : Réinitialiser les champs du formulaire après succès
                    $username = '';
                    $fullname = '';
                } else {
                    $error = "Erreur lors de l'insertion dans la base de données.";
                }
            }

        } catch (PDOException $e) {
            $error = "Erreur de base de données : " . $e->getMessage();
        }
    }
}

require_once 'template/header.php';
?>

<h1 class="mb-4">Administration : Ajouter un Participant</h1>

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card p-4 shadow mx-auto" style="max-width: 500px;">
    <h2 class="card-title text-danger mb-4">Informations du nouveau participant</h2>

    <form method="POST" action="admin_add_user.php">
        
        <div class="mb-3">
            <label for="username" class="form-label">Identifiant (pour la connexion)</label>
            <input type="text" class="form-control" id="username" name="username" 
                   value="<?php echo htmlspecialchars($username); ?>" required>
            <div class="form-text">Lettres, chiffres, ou underscore seulement.</div>
        </div>

        <div class="mb-3">
            <label for="fullname" class="form-label">Nom Complet (pour l'affichage)</label>
            <input type="text" class="form-control" id="fullname" name="fullname" 
                   value="<?php echo htmlspecialchars($fullname); ?>" required>
        </div>
        
        <button type="submit" class="btn btn-primary w-100 mt-3">
            Ajouter l'utilisateur
        </button>
    </form>
</div>

<?php require_once 'template/footer.php'; ?>