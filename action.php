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

// ADD TASK
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_REQUEST['action'] === 'add') {

    if (strlen($_REQUEST['text']) <= 0) addErrorAndExit('Il faut saisir un texte pour la nouvelle tâche');

    $query = $dbCo->prepare("INSERT INTO task (text, priority) VALUES (:text, :priority);");
    $isQueryOk = $query->execute([
        'text' => $_REQUEST['text'],
        'priority' => getNewPriority()
    ]);

    if (!$isQueryOk || $query->rowCount() !== 1) addErrorAndExit('Erreur lors de la création de la tâche');

    addNotification('Tâche créée');
}
// TASK DONE
else if ($_REQUEST['action'] === 'done' && isset($_REQUEST['id'])) {

    $id = intval($_REQUEST['id']);

    if (empty($id)) addErrorAndExit('Identifiant de tâche invalide.');

    $dbCo->beginTransaction();

    // Get priority value from the selected task.
    $priority = getPriority($id);

    // Change done value to validate the task
    $query = $dbCo->prepare("UPDATE task SET done = 1 WHERE id_task = :id;");
    $query->execute(['id' => $id]);

    if ($query->rowCount() !== 1) {
        $dbCo->rollback();
        addErrorAndExit('Erreur lors de la modif de la tache');
    }

    moveUpPriorityAbove($priority);

    if (!$dbCo->commit()) addErrorAndExit('Impossible d\'effectuer cette tâche.');

    addNotification('Tâche effectuée');
}
// UPDATE TASK
else if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_REQUEST['action'] === 'update' && isset($_REQUEST['id']) && isset($_REQUEST['text'])) {

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
