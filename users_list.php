<?php
// Fichier: /home/secretsanta/public_html/users_list.php

require_once 'config.php';
require_once 'auth_check.php';

$users = [];
$error_message = '';

try {
    // Récupérer la liste des utilisateurs (exclure l'admin)
    // On sélectionne fullname et gift_list_url
    $stmt = $pdo->prepare("SELECT fullname, gift_list_url FROM users WHERE username != ? ORDER BY fullname ASC");
    $stmt->execute([ADMIN_USERNAME]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Erreur de base de données lors de la récupération des utilisateurs.";
}

require_once 'template/header.php';
?>

<h1 class="mb-4">Liste des Participants</h1>

<?php if ($error_message): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<?php if (empty($users)): ?>
    <div class="alert alert-info" role="alert">
        Aucun participant trouvé pour le moment (à part l'administrateur).
    </div>
<?php else: ?>
    <p>Voici la liste de tous les participants au Secret Santa. </p>

    <div class="table-responsive">
        <table class="table table-striped table-hover shadow-sm">
            <thead class="table-danger"> <tr>
                    <th scope="col">Nom</th>
                    <th scope="col" class="text-center">Liste de Cadeaux</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                        
                        <?php if (!empty($user['gift_list_url'])): ?>
                            <td class="text-center">
                                <a href="<?php echo htmlspecialchars($user['gift_list_url']); ?>" target="_blank" class="btn btn-sm btn-success">
                                    Voir la liste
                                </a>
                            </td>
                        <?php else: ?>
                            <td class="text-center text-muted fst-italic">
                                Lien non fourni
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once 'template/footer.php'; ?>