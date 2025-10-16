<?php

// Fichier: /home/cacahuete/public_html/draw_logic.php

/**
 * draw_logic.php
 * Contient la logique pour effectuer un tirage au sort (Secret Santa) pour un seul utilisateur (à la volée).
 * Ce script est inclus dans dashboard.php.
 */

// Nécessite $pdo (la connexion BDD) et $user_id (l'ID du tireur)
// La variable $error est utilisée comme variable globale pour remonter les messages d'erreur.

/**
 * Exécute le tirage au sort Secret Santa pour un utilisateur donné.
 *
 * @param PDO $pdo Connexion à la base de données.
 * @param int $user_id ID de l'utilisateur qui effectue la pioche (le 'Gifter').
 * @return array|false Retourne les données du destinataire pioché ou false en cas d'échec.
 */
function runSingleSecretSantaDraw($pdo, $user_id) {
    global $error;
    $error = null; // Réinitialiser l'erreur pour la fonction

    try {
        // 1. Démarrer la transaction
        $pdo->beginTransaction();

        // 2. Récupérer l'ID de tous les participants valides
        $stmt_participants = $pdo->prepare("
            SELECT id FROM users 
            WHERE username != ? 
            ORDER BY id
        ");
        // ADMIN_USERNAME est supposé être défini dans config.php
        $stmt_participants->execute([ADMIN_USERNAME]);
        $all_participants_ids = $stmt_participants->fetchAll(PDO::FETCH_COLUMN, 0);

        if (count($all_participants_ids) < 2) {
            $error = "Erreur : La liste de participants est trop petite pour effectuer un tirage.";
            $pdo->rollBack();
            return false;
        }

        // 3. Récupérer les ID des destinataires (Recipients) qui ont déjà été attribués.
        // Cela garantit que chaque personne est piochée une seule fois.
        $stmt_assigned = $pdo->query("SELECT receiver_id FROM draw");
        $assigned_recipients_ids = $stmt_assigned->fetchAll(PDO::FETCH_COLUMN, 0);

        // 4. Identifier les destinataires POTENTIELS (ceux qui n'ont pas encore été piochés)
        $potential_receiver_ids = array_diff($all_participants_ids, $assigned_recipients_ids);
        
        if (empty($potential_receiver_ids)) {
            $error = "Erreur : Toutes les personnes ont déjà été piochées ! Annulez et relancez le tirage.";
            $pdo->rollBack();
            return false;
        }
        
        // 5. Filtrer le tireur lui-même des potentiels destinataires (Évite l'auto-attribution)
        $final_candidates = array_diff($potential_receiver_ids, [$user_id]);

        if (empty($final_candidates)) {
            // Cas complexe de la dernière personne restante qui est le tireur lui-même.
            // Le tirage est bloqué.
            $error = "Erreur de boucle logique : Le dernier destinataire disponible est vous-même. Contactez l'administrateur.";
            $pdo->rollBack();
            return false;
        }


        // 6. Tirage au sort du destinataire final
        $random_key = array_rand($final_candidates);
        $receiver_id = $final_candidates[$random_key];

        // 7. Enregistrement de la paire dans la table 'draw'
        $stmt_insert = $pdo->prepare("INSERT INTO draw (giver_id, receiver_id) VALUES (?, ?)");
        $stmt_insert->execute([$user_id, $receiver_id]);

        // 8. Marquer l'utilisateur (gifter) comme ayant pioché (has_drawn = 1)
        $stmt_update = $pdo->prepare("UPDATE users SET has_drawn = 1 WHERE id = ?");
        $stmt_update->execute([$user_id]);
        
        $pdo->commit();

        // 9. Retourner les infos du destinataire pour affichage immédiat
        $stmt_recipient = $pdo->prepare("SELECT fullname, gift_list_url FROM users WHERE id = ?");
        $stmt_recipient->execute([$receiver_id]);
        return $stmt_recipient->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = "Erreur BDD critique lors du tirage: " . $e->getMessage();
        return false;
    }
}