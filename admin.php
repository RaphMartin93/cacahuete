<?php
// Fichier: /home/cacahuete/public_html/admin.php (Modification avec Annulation fonctionnelle)

require_once 'config.php';
require_once 'auth_check.php';

// --- VÉRIFICATION DE SÉCURITÉ ADMIN ---
if (!$_SESSION['is_admin']) {
    header('Location: dashboard.php');
    exit;
}

$error = null;
$message = null;

/**
 * Gère l'annulation complète du tirage au sort (Réinitialisation).
 * @param PDO $pdo Connexion à la base de données.
 * @return bool
 */
function cancelSecretSantaDraw($pdo) {
    global $error, $message;
    
    try {
        $pdo->beginTransaction();
        
        // 1. Vider la table des résultats du tirage
        $pdo->exec("TRUNCATE TABLE draw"); 
        
        // 2. Réinitialiser le statut de pioche pour TOUS les utilisateurs (sauf l'admin)
        $pdo->exec("UPDATE users SET has_drawn = 0 WHERE username != '" . ADMIN_USERNAME . "'");
        
        $pdo->commit();
        $message = "Le tirage au sort a été annulé. Tous les résultats et les statuts de pioche ont été réinitialisés.";
        return true;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        $error = "Erreur BDD lors de l'annulation: " . $e->getMessage();
        return false;
    }
}


// =======================================================
// A. TRAITEMENT POST : Annulation du Tirage
// =======================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'cancel_draw') {
        cancelSecretSantaDraw($pdo);
        // Redirection POST-Redirect-GET
        header('Location: admin.php');
        exit;
    }
}


// ... Le reste du code de récupération des données et d'affichage est conservé ci-dessous ...


$status_participants_count = 0;
$draw_count = 0;
$has_drawn_count = 0;
$participants = [];

try {
    // 1. Compter les participants et l'état du tirage
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE username != '" . ADMIN_USERNAME . "'");
    $status_participants_count = $stmt->fetchColumn();

    $stmt_draw = $pdo->query("SELECT COUNT(*) FROM draw");
    $draw_count = $stmt_draw->fetchColumn();

    $stmt_drawn = $pdo->query("SELECT COUNT(*) FROM users WHERE has_drawn = 1 AND username != '" . ADMIN_USERNAME . "'");
    $has_drawn_count = $stmt_drawn->fetchColumn();

    // 2. Récupérer la liste des participants pour le suivi des pioches
    $stmt_list = $pdo->query("
        SELECT fullname, has_drawn 
        FROM users 
        WHERE username != '" . ADMIN_USERNAME . "' 
        ORDER BY fullname
    ");
    $participants = $stmt_list->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Erreur de base de données : " . $e->getMessage();
}


require_once 'template/header.php';
?>

<h1 class="mb-4">Panneau d'Administration</h1>

<?php if (isset($error)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if (isset($message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<h2 class="mt-4 pb-2 border-bottom text-danger">Statut Actuel</h2>

<p>Nombre de participants inscrits : <strong><?php echo $status_participants_count; ?></strong></p>
<p>Statut du tirage complet : 
    <span class="badge bg-<?php echo $draw_count > 0 ? 'success' : 'warning text-dark'; ?>">
        <?php echo $draw_count > 0 ? 'TERMINÉ (' . $draw_count . ' paires)' : 'EN ATTENTE'; ?>
    </span>
</p>
<p>Participants ayant pioché : 
    <span class="badge bg-primary">
        <?php echo $has_drawn_count; ?> / <?php echo $status_participants_count; ?>
    </span>
</p>

<h2 class="mt-4 pb-2 border-bottom text-danger">Gestion du Tirage</h2>

<?php if ($draw_count > 0): ?>
    <div class="alert alert-warning">
        <p>Le tirage est terminé. Vous pouvez l'annuler pour recommencer. Cela remettra à zéro le statut de pioche de tout le monde.</p>
        <form method="POST" action="admin.php">
            <input type="hidden" name="action" value="cancel_draw">
            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir annuler le tirage ? Cela remettra à zéro le statut de pioche des utilisateurs et les paires enregistrées.');">
                Annuler le tirage
            </button>
        </form>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        Le tirage n'a pas été effectué. Les utilisateurs peuvent piocher à tout moment.
    </div>
<?php endif; ?>


<h2 class="mt-5 pb-2 border-bottom text-danger">Qui a pioché son Père Noël secret ?</h2>

<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>Participant</th>
                <th class="text-center">Statut de la Pioche</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($participants as $p): ?>
            <tr>
                <td><?php echo htmlspecialchars($p['fullname']); ?></td>
                <td class="text-center">
                    <?php if ($p['has_drawn']): ?>
                        <span class="badge bg-success">Pioche effectuée</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">En attente</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'template/footer.php'; ?>