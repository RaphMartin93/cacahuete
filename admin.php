<?php
// Fichier: /home/secretsanta/public_html/admin.php

require_once 'config.php';
require_once 'auth_check.php';

// --- VÉRIFICATION DE SÉCURITÉ ADMIN ---
if (!$_SESSION['is_admin']) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$error = '';
$draw_exists = false;
$participants = [];


// --- FONCTION DE TIRAGE AU SORT ---
function runSecretSantaDraw($pdo) {
    global $message, $error; 

    // 1. Récupérer la liste de tous les IDs des participants (NON-ADMINS)
    try {
        // La constante ADMIN_USERNAME est directement accessible car elle est définie dans config.php
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username != ?");
        $stmt->execute([ADMIN_USERNAME]); 
        $all_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        $error = "Erreur BDD lors de la récupération des participants: " . $e->getMessage();
        return false;
    }

    $count = count($all_ids);

    if ($count < 2) {
        $error = "Il faut au moins deux participants (hors admin) pour effectuer un tirage au sort.";
        return false;
    }
    
    $MAX_ATTEMPTS = 100;
    $attempt = 0;
    $draw_successful = false;
    $draw_results = [];

    // Boucle principale pour garantir un tirage sans auto-attribution
    while ($attempt < $MAX_ATTEMPTS && !$draw_successful) {
        $attempt++;
        $draw_successful = true;
        $draw_results = [];
        
        $givers = $all_ids;
        $receivers = $all_ids;
        
        shuffle($givers);
        
        foreach ($givers as $giver_id) {
            $possible_receivers = array_diff($receivers, [$giver_id]);
            
            if (empty($possible_receivers) && count($receivers) == 1) {
                $draw_successful = false;
                break;
            }
            
            $receiver_id = $possible_receivers[array_rand($possible_receivers)];
            
            $draw_results[] = ['giver_id' => $giver_id, 'receiver_id' => $receiver_id];
            $receivers = array_diff($receivers, [$receiver_id]);
        }
        
        if (!empty($receivers)) {
            $draw_successful = false;
        }
    }

    if (!$draw_successful) {
        $error = "Erreur après $MAX_ATTEMPTS tentatives: Le tirage est bloqué. Veuillez vérifier vos participants.";
        return false;
    }
    
    // 4. Enregistrement des résultats (si le tirage a réussi)
    try {
        $pdo->beginTransaction();
        $pdo->exec("TRUNCATE TABLE draw"); 

        $sql = "INSERT INTO draw (giver_id, receiver_id) VALUES (:giver, :receiver)";
        $stmt = $pdo->prepare($sql);
        
        foreach ($draw_results as $result) {
            $stmt->execute([':giver' => $result['giver_id'], ':receiver' => $result['receiver_id']]);
        }
        
        $pdo->commit();
        $message = "Le tirage au sort a été effectué avec succès pour " . $count . " participants !";
        return true;

    } catch (PDOException $e) {
        // Correction du rollBack + affichage de l'erreur propre
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = "Erreur BDD lors de l'enregistrement du tirage: " . $e->getMessage();
        return false;
    }
}


// --- LOGIQUE DE L'APPLICATION (Action POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'run_draw') {
    $stmt_check = $pdo->query("SELECT COUNT(*) FROM draw");
    if ($stmt_check->fetchColumn() > 0) {
        $error = "Un tirage existe déjà. Veuillez l'annuler avant d'en lancer un nouveau.";
    } else {
        runSecretSantaDraw($pdo);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_draw') {
    try {
        $pdo->exec("TRUNCATE TABLE draw");
        $message = "Le tirage au sort a été annulé avec succès. La page de dashboard affichera le statut 'En attente'.";
    } catch (PDOException $e) {
        $error = "Erreur BDD lors de l'annulation du tirage: " . $e->getMessage();
    }
}

// --- VÉRIFICATION DU STATUT ACTUEL DU TIRAGE ---
try {
    $stmt_status = $pdo->query("SELECT COUNT(*) FROM draw");
    $draw_count = $stmt_status->fetchColumn();
    $draw_exists = ($draw_count > 0);

    $stmt_participants = $pdo->prepare("SELECT fullname FROM users WHERE username != ? ORDER BY fullname ASC");
    $stmt_participants->execute([ADMIN_USERNAME]);
    $participants = $stmt_participants->fetchAll(PDO::FETCH_COLUMN);
    $participants_count = count($participants); 

} catch (PDOException $e) {
    $error = "Erreur lors de la vérification du statut du tirage.";
}

require_once 'template/header.php';
?>

<h1 class="mb-4">Panneau d'Administration</h1>

<?php if ($message): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php 
// Nettoyage de l'erreur "no active transaction" si le tirage est un succès
$display_error = $error;
if ($draw_exists && strpos($error, 'no active transaction') !== false) {
    $display_error = '';
}
?>

<?php if ($display_error): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($display_error); ?></div>
<?php endif; ?>

<div class="card p-3 mb-4 shadow-sm">
    <h2 class="card-title text-danger mb-3">Statut Actuel</h2>
    <div class="card-body">
        <p>Nombre de participants inscrits : <strong><?php echo $participants_count; ?></strong></p>
        <p>Statut du tirage : 
            <?php if ($draw_exists): ?>
                <span class="badge bg-success fs-6">TERMINÉ (<?php echo $draw_count; ?> paires)</span>
            <?php else: ?>
                <span class="badge bg-danger fs-6">EN ATTENTE</span>
            <?php endif; ?>
        </p>
    </div>
</div>

<div class="card p-4 mb-4 border-primary shadow-sm">
    <h2 class="card-title text-primary">Action de Tirage</h2>
    
    <?php if (!$draw_exists): ?>
        <p>Cliquez ci-dessous pour lancer le tirage au sort Secret Santa.</p>
        <form method="POST" action="admin.php" onsubmit="return confirm('Êtes-vous sûr de vouloir lancer le tirage ? Cette action est irréversible (sauf annulation manuelle).');">
            <input type="hidden" name="action" value="run_draw">
            <button type="submit" class="btn btn-primary btn-lg mt-2">
                Lancer le Tirage au Sort
            </button>
        </form>
    <?php else: ?>
        <p>Le tirage est terminé. Vous pouvez l'annuler si nécessaire pour relancer un nouveau cycle.</p>
        <form method="POST" action="admin.php" onsubmit="return confirm('ATTENTION : Êtes-vous sûr d\'ANNULER le tirage ? Tous les participants verront le statut "En Attente".');">
            <input type="hidden" name="action" value="cancel_draw">
            <button type="submit" class="btn btn-danger btn-lg mt-2">
                Annuler le Tirage Actuel
            </button>
        </form>
    <?php endif; ?>
</div>

<div class="mt-5">
    <h2>Liste des Participants</h2>
    <p>Voici les personnes qui seront incluses dans le tirage :</p>
    
    <ul class="list-group">
        <?php foreach ($participants as $p): ?>
            <li class="list-group-item"><?php echo htmlspecialchars($p); ?></li>
        <?php endforeach; ?>
    </ul>
</div>

<?php require_once 'template/footer.php'; ?>