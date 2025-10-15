<?php
// Fichier: /home/secretsanta/public_html/admin_add_user.php

require_once 'config.php';
require_once 'auth_check.php';

// --- VÉRIFICATION DE SÉCURITÉ ADMIN ---
if (!$_SESSION['is_admin']) {
    header('Location: dashboard.php');
    exit;
}

// --- INITIALISATION DES VARIABLES ---
$message = '';
$error = '';
$username = '';
$fullname = '';
// NOUVELLE INITIALISATION pour le champ de liste de cadeaux
$gift_list_url = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $initial_password = $_POST['initial_password'] ?? ''; 
    // NOUVELLE RÉCUPÉRATION du lien cadeau
    $gift_list_url = trim($_POST['gift_list_url'] ?? ''); 
    
    // Ajout d'une vérification basique pour le lien, mais le champ est optionnel si vide est autorisé
    // if (!empty($gift_list_url) && !filter_var($gift_list_url, FILTER_VALIDATE_URL)) {
    //     $error = "Veuillez entrer un lien de liste de cadeaux valide, ou laisser vide.";
    // } 
    
    // Le lien n'est pas obligatoire, nous vérifions seulement les champs essentiels
    if (empty($username) || empty($fullname) || empty($initial_password)) {
        $error = "Veuillez remplir l'Identifiant, le Nom Complet ET le Mot de Passe.";
        
    } elseif (!preg_match('/^[a-z0-9_]+$/i', $username)) {
        $error = "L'Identifiant ne doit contenir que des lettres, chiffres et underscores.";
        
    } else {
        try {
            // 1. Vérifier si l'identifiant existe déjà
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt_check->execute([$username]);

            if ($stmt_check->fetchColumn() > 0) {
                $error = "Cet identifiant ('" . htmlspecialchars($username) . "') est déjà utilisé.";
            } else {
                // 2. Procéder à l'ajout
                $password_hash = password_hash($initial_password, PASSWORD_DEFAULT);

                // La requête SQL utilise déjà 'gift_list_url', c'est parfait
                $stmt_insert = $pdo->prepare(
                    "INSERT INTO users (username, fullname, password, is_admin, gift_list_url) 
                     VALUES (?, ?, ?, 0, ?)" // Ajout d'un 5ème point d'interrogation pour le lien
                );
                
                // --- MISE À JOUR : Ajout de $gift_list_url dans le tableau d'exécution
                if ($stmt_insert->execute([$username, $fullname, $password_hash, $gift_list_url])) {
                    $message = "L'utilisateur <strong>" . htmlspecialchars($fullname) . "</strong> a été ajouté avec succès.
                                <br>Mot de passe initial défini. Rappelez au participant qu'il doit le changer.";
                    
                    $username = '';
                    $fullname = '';
                    $gift_list_url = ''; // Réinitialisation du champ après succès
                } else {
                    $error = "Erreur lors de l'insertion dans la base de données.";
                }
            }

        } catch (PDOException $e) {
            // REMETTRE LA VERSION SÉCURISÉE EN PRODUCTION
            // $error = "Erreur de base de données : " . $e->getMessage(); 
            $error = "Erreur de base de données : Une erreur est survenue."; 
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
        
        <div class="mb-3">
            <label for="initial_password" class="form-label">Mot de Passe Initial</label>
            <input type="password" class="form-control" id="initial_password" name="initial_password" 
                   required> 
            <div class="form-text">Ce mot de passe sera utilisé pour la première connexion.</div>
        </div>
        
        <div class="mb-3">
            <label for="gift_list_url" class="form-label">Lien vers la Liste de Cadeaux (Optionnel)</label>
            <input type="url" class="form-control" id="gift_list_url" name="gift_list_url" 
                   value="<?php echo htmlspecialchars($gift_list_url); ?>"> 
            <div class="form-text">Lien complet (ex: https://...) vers la liste de cadeaux du participant.</div>
        </div>
        <button type="submit" class="btn btn-primary w-100 mt-3">
            Ajouter l'utilisateur
        </button>
    </form>
</div>

<?php require_once 'template/footer.php'; ?>