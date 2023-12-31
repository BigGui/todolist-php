<?php

require_once 'vendor/autoload.php';
require_once 'includes/_functions.php';

if (!isset($_REQUEST['action'])) addErrorAndExit('Aucune action');

include 'includes/_db.php';

// Start user session
session_start();

// Check for CSRF and redirect in case of invalid token or referer
checkCSRF('index.php');

// Prevent XSS fault
checkXSS($_REQUEST);

// UPDATE TASK
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_REQUEST['action'] === 'update' && isset($_REQUEST['id']) && isset($_REQUEST['text'])) {

    $id = intval($_REQUEST['id']);

    if (is_int($id) && strlen($_REQUEST['text']) > 0) {
        $updateTask = $dbCo->prepare("UPDATE task SET text = :text WHERE id_task = :id;");
        $updateTask->bindValue(':id', $id, PDO::PARAM_INT);
        $updateTask->bindValue(':text', $_REQUEST['text'], PDO::PARAM_STR);
        $isOk = $updateTask->execute();

        if ($isOk) {
            $_SESSION['notif'] = 'Tâche modifiée avec succès.';
        } else {
            $_SESSION['error'] = 'Erreur lors de la modification de la tâche.';
        }
    } else {
        $_SESSION['error'] = 'Merci de saisir un texte.';
    }
}
// DELETE TASK
else if ($_REQUEST['action'] === 'delete') {

    $id = intval($_REQUEST['id']);

    if (is_int($id)) {
        try {
            $dbCo->beginTransaction();

            // Get priority value from the selected task.
            $priority = getPriority($id);

            // Delete the selected task.
            $queryUpdate = $dbCo->prepare("DELETE FROM task WHERE id_task = :id;");
            $queryUpdate->execute(['id' => $id]);

            if ($queryUpdate->rowCount() !== 1) {
                throw new Exception('Nombre incohérent de lignes affectées par la suppression.');
            }
            
            // Update priorities
            moveUpPriorityAbove($priority);

            if ($dbCo->commit()) {
                $_SESSION['notif'] = 'Tâche supprimée avec succès.';
            }
        } catch (Exception $e) {
            $dbCo->rollBack();
            $_SESSION['error'] = 'Erreur lors de la suppression de la tâche.';
        }

    } else {
        $_SESSION['error'] = 'La tâche n\'a pas été supprimée.';
    }
}
// MOVE PRIORITY UP
else if ($_REQUEST['action'] === 'up') {
    $dbCo->beginTransaction();

    $idTask = intval($_REQUEST['id']);
    $priority = max(getPriority($idTask), 1);

    $queryUp = $dbCo->prepare("UPDATE task SET priority = GREATEST(priority + 1, 1) WHERE priority = :priority;");
    $queryUp->execute(['priority' => $priority - 1]);

    $queryUp = $dbCo->prepare("UPDATE task SET priority = GREATEST(priority - 1, 1) WHERE id_task = :id;");
    $queryUp->execute(['id' => $idTask]);

    $isOk = $dbCo->commit();

    if ($isOk) {
        $_SESSION['notif'] = 'Tâche priorisée.';
    } else {
        $_SESSION['error'] = 'Erreur lors de la priorisation.';
    }
}
// MOVE PRIORITY DOWN
else if ($_REQUEST['action'] === 'down') {
    $dbCo->beginTransaction();

    $idTask = intval($_REQUEST['id']);
    $priority = max(getPriority($idTask), 1);

    $queryUp = $dbCo->prepare("UPDATE task SET priority = GREATEST(priority - 1, 1) WHERE priority = :priority;");
    $queryUp->execute(['priority' => $priority + 1]);

    $queryUp = $dbCo->prepare("UPDATE task SET priority = GREATEST(priority + 1, 1) WHERE id_task = :id;");
    $queryUp->execute(['id' => $idTask]);

    $isOk = $dbCo->commit();

    if ($isOk) {
        $_SESSION['notif'] = 'Tâche priorisée.';
    } else {
        $_SESSION['error'] = 'Erreur lors de la priorisation.';
    }
}

header('Location: index.php');
