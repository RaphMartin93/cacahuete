<?php
// Fichier: /home/secretsanta/public_html/dashboard.php

require_once 'config.php';
require_once 'auth_check.php';

$giver_id = $_SESSION['user_id'];
$receiver = null;
$error_message = null;

try {
    // 1. Trouver à qui l'utilisateur a pioché
    $stmt_draw = $pdo->prepare("SELECT receiver_id FROM draw WHERE giver_id = ?");
    $stmt_draw->execute([$giver_id]);
    $draw_result = $stmt_draw->fetch(PDO::FETCH_ASSOC);

    if ($draw_result) {
        $receiver_id = $draw_result['receiver_id'];

        // 2. Récupérer les informations du destinataire
        $stmt_receiver = $pdo->prepare("SELECT fullname, gift_list_url FROM users WHERE id = ?");
        $stmt_receiver->execute([$receiver_id]);
        $receiver = $stmt_receiver->fetch(PDO::FETCH_ASSOC);
        
        if (!$receiver) {
            $error_message = "Erreur: Le destinataire trouvé dans le tirage n'existe pas.";
        }
    }
    // Si $draw_result est null, le tirage n'a pas encore eu lieu, $receiver reste null.

} catch (PDOException $e) {
    $error_message = "Erreur de base de données lors de la vérification du tirage.";
}

require_once 'template/header.php';
?>

<h1 class="mb-4">Tableau de Bord</h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
    
<?php elseif ($receiver): ?>
    
    <div class="card shadow-sm p-4 mx-auto" style="max-width: 600px;">
        <div class="card-body text-center">
            <h2 class="card-title text-danger mb-4">🥳 VOUS AVEZ PIOCHÉ : </h2>
            
            <p class="fs-2 fw-bold mb-3">
                <?php echo htmlspecialchars($receiver['fullname']); ?>
            </p>

            <?php if (!empty($receiver['gift_list_url'])): ?>
                <p>
                    <a href="<?php echo htmlspecialchars($receiver['gift_list_url']); ?>" target="_blank" class="btn btn-success btn-lg mt-3">
                        Voir la liste de cadeaux de <?php echo htmlspecialchars($receiver['fullname']); ?>
                    </a>
                </p>
            <?php else: ?>
                <p class="text-muted">Cette personne n'a pas encore fourni de lien vers sa liste de cadeaux.</p>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>
    <div class="alert alert-info" role="alert">
        Le tirage au sort n'a pas encore été effectué. Veuillez patienter que l'administrateur lance la pige !
    </div>
<?php endif; ?>

<?php require_once 'template/footer.php'; ?>