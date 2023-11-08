<?php
require_once 'includes/_functions.php';
include 'includes/_db.php';

// Start user session
session_start();

// Check for CSRF and redirect in case of invalid token or referer
checkCSRF('index.php');

// ADD TASK
if (isset($_POST['action']) && $_POST['action'] === 'add') {

    $text = strip_tags($_POST['text']);

    if (strlen($text) > 0) {
        $query = $dbCo->prepare("INSERT INTO task (text) VALUES (:text);");
        $isQueryOk = $query->execute([
            'text' => $text
        ]);

        if ($isQueryOk && $query->rowCount() === 1) {
            $return['notif'] = 'Tâche créée';
        } else {
            $return['error'] = 'Erreur lors de la création de la tâche';
        }
    } else {
        $return['error'] = 'Il faut saisir un texte pour la nouvelle tâche.';
    }
}
// TASK DONE
else if (isset($_GET['action']) && $_GET['action'] === 'done' && isset($_GET['id'])) {

    $id = intval(strip_tags($_GET['id']));

    if (!empty($id)) {
        $query = $dbCo->prepare("UPDATE task SET done = 1 WHERE id_task = :id;");
        $isQueryOk = $query->execute([
            'id' => $id
        ]);

        if ($isQueryOk && $query->rowCount() === 1) {
            $return['notif'] = 'Tâche effectuée';
        } else {
            $return['error'] = 'Impossible d\'effectuer cette tâche.';
        }
    } else {
        $return['error'] = 'Identifiant de tâche invalide.';
    }
}
// DELETE TASK
else if (isset($_GET['action']) && $_GET['action'] === 'delete') {
}

header('Location: index.php?' . http_build_query($return));
