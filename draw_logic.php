<?php

/**
 * draw_logic.php
 * Logique de tirage Secret Santa pour un seul utilisateur.
 * Ce script est inclus dans dashboard.php.
 *
 * On exclut :
 *  - l’admin
 *  - les enfants (is_child = 1)
 */

function runSingleSecretSantaDraw(PDO $pdo, int $user_id)
{
    // Variable globale pour remonter les erreurs vers dashboard.php
    global $error;

    try {
        $pdo->beginTransaction();

        // 1. Vérifier que l'utilisateur existe et qu'il n'est pas un enfant
        $stmt = $pdo->prepare("
            SELECT id, fullname, is_admin, is_child, has_drawn
            FROM users
            WHERE id = ?
            FOR UPDATE
        ");
        $stmt->execute([$user_id]);
        $current_user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$current_user) {
            $error = "Utilisateur introuvable.";
            $pdo->rollBack();
            return false;
        }

        if ((int)$current_user['is_child'] === 1) {
            $error = "Les enfants ne participent pas au tirage.";
            $pdo->rollBack();
            return false;
        }

        if ((int)$current_user['is_admin'] === 1) {
            $error = "L'administrateur ne participe pas au tirage.";
            $pdo->rollBack();
            return false;
        }

        if ((int)$current_user['has_drawn'] === 1) {
            $error = "Vous avez déjà effectué votre tirage.";
            $pdo->rollBack();
            return false;
        }

        // 2. Récupérer tous les participants éligibles (ni admin, ni enfant)
        $stmt_participants = $pdo->query("
            SELECT id
            FROM users
            WHERE is_admin = 0
              AND is_child = 0
        ");
        $all_participants_ids = $stmt_participants->fetchAll(PDO::FETCH_COLUMN, 0);

        if (count($all_participants_ids) < 2) {
            $error = "Erreur : pas assez de participants pour effectuer un tirage.";
            $pdo->rollBack();
            return false;
        }

        // 3. Récupérer les destinataires déjà attribués
        $stmt_assigned = $pdo->query("SELECT receiver_id FROM draw");
        $assigned_recipients_ids = $stmt_assigned->fetchAll(PDO::FETCH_COLUMN, 0);

        // 4. Calculer la liste des destinataires possibles :
        //    - pas déjà piochés
        //    - pas soi-même
        $potential_receiver_ids = array_diff(
            $all_participants_ids,
            $assigned_recipients_ids,
            [$user_id]
        );

        if (empty($potential_receiver_ids)) {
            $error = "Plus aucun destinataire disponible pour ce tirage.";
            $pdo->rollBack();
            return false;
        }

        // 5. Tirage aléatoire d'un destinataire
        shuffle($potential_receiver_ids);
        $receiver_id = $potential_receiver_ids[0];

        // 6. Enregistrer le tirage dans la table draw
        $stmt_insert = $pdo->prepare("
            INSERT INTO draw (gifter_id, receiver_id, draw_date)
            VALUES (?, ?, NOW())
        ");
        $stmt_insert->execute([$user_id, $receiver_id]);

        // 7. Marquer l'utilisateur comme ayant pioché
        $stmt_update = $pdo->prepare("
            UPDATE users
            SET has_drawn = 1
            WHERE id = ?
        ");
        $stmt_update->execute([$user_id]);

        $pdo->commit();

        // 8. Renvoyer les infos du destinataire
        $stmt_recipient = $pdo->prepare("
            SELECT fullname, gift_list_url
            FROM users
            WHERE id = ?
        ");
        $stmt_recipient->execute([$receiver_id]);

        return $stmt_recipient->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = "Erreur BDD critique lors du tirage : " . $e->getMessage();
        return false;
    }
}
