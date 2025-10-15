<?php
// Fichier: /home/secretsanta/public_html/dashboard.php (Adapté à GIVER/RECEIVER IDs, Exclusion Admin)

require_once 'config.php';
require_once 'auth_check.php';
// Inclusion de la logique qui gère le tirage individuel
require_once 'draw_logic.php'; 

$recipient_name = null;
$recipient_gift_url = null;
// Récupérer le statut de la pioche depuis la session
$has_drawn = $_SESSION['has_drawn'] ?? 0; 
$is_admin = $_SESSION['is_admin'] ?? false; // On récupère aussi le statut admin

$error_message = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
$message = $_SESSION['success'] ?? '';
unset($_SESSION['success']);


// =======================================================
// A. TRAITEMENT DE L'ACTION "PIOCHER" (POST)
// =======================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reveal') {
    $user_id = $_SESSION['user_id'];
    
    // NOUVEAUTÉ : Empêcher l'admin de piocher via POST même si l'interface est modifiée
    if ($is_admin) {
        $_SESSION['error'] = "Le compte administrateur ne peut pas participer au tirage.";
    } elseif ($has_drawn) {
        // 1. Vérification finale si l'utilisateur n'a pas déjà pioché
        $_SESSION['error'] = "Vous avez déjà pioché votre Secret Santa !";
    } else {
        // 2. EXÉCUTER LE TIRAGE AU SORT INDIVIDUEL (Appel de la fonction de draw_logic.php)
        $draw_result = runSingleSecretSantaDraw($pdo, $user_id);

        if ($draw_result) {
            // Mise à jour du statut de la session après succès
            $_SESSION['has_drawn'] = 1; 
            $_SESSION['success'] = "Félicitations ! Votre Secret Santa a été pioché avec succès. Regardez ci-dessous !";
        } else {
            // L'erreur est remontée via la variable globale $error dans draw_logic.php
            $error_message_from_logic = $error; 
            $_SESSION['error'] = $error_message_from_logic; 
        }
    }
    
    // Redirection POST-Redirect-GET pour éviter la resoumission du formulaire
    header('Location: dashboard.php');
    exit;
}


// =======================================================
// B. RÉCUPÉRATION DES DONNÉES ET AFFICHAGE
// =======================================================

// L'admin n'a pas besoin de faire cette requête
if (!$is_admin && $has_drawn) { 
    try {
        // Si l'utilisateur a déjà pioché, on récupère son résultat depuis la table 'draw'
        // Utilise les noms de colonnes de votre BDD : giver_id et receiver_id
        $stmt = $pdo->prepare("
            SELECT 
                r.fullname AS recipient_fullname, 
                r.gift_list_url AS recipient_gift_url
            FROM draw d
            JOIN users r ON d.receiver_id = r.id
            WHERE d.giver_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $recipient_name = $result['recipient_fullname'];
            $recipient_gift_url = $result['recipient_gift_url'];
        } else {
            // Si has_drawn est à 1 mais qu'il n'y a pas de résultat, c'est une anomalie
            $error_message = "Erreur: Votre tirage est marqué comme effectué, mais le résultat est introuvable.";
        }
    } catch (PDOException $e) {
        $error_message = "Erreur BDD lors de la récupération des informations du destinataire.";
    }
}

require_once 'template/header.php';
?>

<h1 class="mb-4">Accueil</h1>

<?php if ($error_message): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<?php if ($message): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if ($is_admin): ?>
    <div class="card p-4 text-center shadow mx-auto" style="max-width: 500px; border: 2px solid #0d6efd;">
        <h2 class="text-primary mb-4">Bienvenue, Administrateur</h2>
        <p class="lead">Ce compte est réservé à la gestion de l'événement.</p>
        <a href="admin.php" class="btn btn-lg btn-primary mt-3">
            Accéder au Panneau d'Administration
        </a>
    </div>

<?php elseif (!$has_drawn): ?>

    <div class="card p-4 text-center shadow mx-auto" style="max-width: 500px; border: 2px solid #dc3545;">
        <h2 class="text-danger mb-4">Prêt à piocher votre Secret Santa ?</h2>
        <p class="lead">C'est le moment de découvrir qui vous allez gâter !</p>
        
        <form method="POST" action="dashboard.php">
            <input type="hidden" name="action" value="reveal">
            <button type="submit" class="btn btn-lg btn-danger mt-3">
                🎁 PIOCHER ! 🎁
            </button>
        </form>
    </div>

<?php else: ?>

    <div class="card p-4 text-center shadow mx-auto border border-success" style="max-width: 600px;">
        <div class="card-body">
            <h2 class="card-title text-success mb-3">🎉 VOTRE SECRET SANTA EST : 🎉</h2>
            <h1 class="display-3 fw-bold mb-4"><?php echo htmlspecialchars($recipient_name); ?></h1>

            <?php if (!empty($recipient_gift_url)): ?>
                <a href="<?php echo htmlspecialchars($recipient_gift_url); ?>" target="_blank" class="btn btn-lg btn-success mt-3">
                    Voir la liste de cadeaux de <?php echo htmlspecialchars($recipient_name); ?>
                </a>
            <?php else: ?>
                <p class="text-muted mt-3">
                    <?php echo htmlspecialchars($recipient_name); ?> n'a pas encore fourni sa liste de cadeaux.
                </p>
            <?php endif; ?>
        </div>
    </div>

<?php endif; ?>

<?php require_once 'template/footer.php'; ?>