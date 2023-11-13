<?php

require_once 'vendor/autoload.php';
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
        $query = $dbCo->prepare("INSERT INTO task (text, priority) VALUES (:text, :priority);");
        $isQueryOk = $query->execute([
            'text' => $text,
            'priority' => getNewPriority()
        ]);

        if ($isQueryOk && $query->rowCount() === 1) {
            $_SESSION['notif'] = 'Tâche créée';
        } else {
            $_SESSION['error'] = 'Erreur lors de la création de la tâche';
        }
    } else {
        $_SESSION['error'] = 'Il faut saisir un texte pour la nouvelle tâche.';
    }
}
// TASK DONE
else if (isset($_GET['action']) && $_GET['action'] === 'done' && isset($_GET['id'])) {

    $id = intval(strip_tags($_GET['id']));

    if (!empty($id)) {

        $dbCo->beginTransaction();
        
        // Get priority value from the selected task.
        $priority = getPriority($id);

        // Change done value to validate the task
        $query = $dbCo->prepare("UPDATE task SET done = 1 WHERE id_task = :id;");
        $query->execute(['id' => $id]);

        if ($query->rowCount() === 1) {
            // Update priorities
            moveUpPriorityAbove($priority);
        }
        else {
            $dbCo->rollback();
        }

        $isOk = $dbCo->commit();

        if ($isOk) {
            $_SESSION['notif'] = 'Tâche effectuée';
        } else {
            $_SESSION['error'] = 'Impossible d\'effectuer cette tâche.';
        }
    } else {
        $_SESSION['error'] = 'Identifiant de tâche invalide.';
    }
}
// UPDATE TASK
else if (isset($_POST['action']) && $_POST['action'] === 'update' && isset($_POST['id']) && isset($_POST['text'])) {

    $id = intval(strip_tags($_POST['id']));
    $text = strip_tags($_POST['text']);

    if (is_int($id) && strlen($text) > 0) {
        $updateTask = $dbCo->prepare("UPDATE task SET text = :text WHERE id_task = :id;");
        $updateTask->bindValue(':id', $id, PDO::PARAM_INT);
        $updateTask->bindValue(':text', $text, PDO::PARAM_STR);
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
else if (isset($_GET['action']) && $_GET['action'] === 'delete') {

    $id = intval(strip_tags($_GET['id']));

    if (is_int($id)) {
        $dbCo->beginTransaction();

        // Get priority value from the selected task.
        $priority = getPriority($id);

        // Delete the selected task.
        $queryUpdate = $dbCo->prepare("DELETE FROM task WHERE id_task = :id;");
        $queryUpdate->execute(['id' => $id]);

        if ($queryUpdate->rowCount() === 1) {
            // Update priorities
            moveUpPriorityAbove($priority);
        }
        else {
            $dbCo->rollback();
        }

        $isOk = $dbCo->commit();

        if ($isOk) {
            $_SESSION['notif'] = 'Tâche supprimée avec succès.';
        } else {
            $_SESSION['error'] = 'Erreur lors de la suppression de la tâche.';
        }
    } else {
        $_SESSION['error'] = 'La tâche n\'a pas été supprimée.';
    }
}
// MOVE PRIORITY UP
else if (isset($_GET['action']) && $_GET['action'] === 'up') {
    $dbCo->beginTransaction();

    $idTask = intval(strip_tags($_GET['id']));
    $priority = max(getPriority($idTask), 1);

    $queryUp = $dbCo->prepare("UPDATE task SET priority = GREATEST(priority + 1, 1) WHERE priority = :priority;");
    $queryUp->execute(['priority' => $priority - 1]);

    $queryUp = $dbCo->prepare("UPDATE task SET priority = GREATEST(priority - 1, 1) WHERE id_task = :id;");
    $queryUp->execute(['id' => $idTask]);

    $isOk = $dbCo->commit();

    if ($isOk) {
        $_SESSION['notif'] = 'Tâche priorisée.';
    }
    else {
        $_SESSION['error'] = 'Erreur lors de la priorisation.';
    }
}
// MOVE PRIORITY DOWN
else if (isset($_GET['action']) && $_GET['action'] === 'down') {
    $dbCo->beginTransaction();

    $idTask = intval(strip_tags($_GET['id']));
    $priority = max(getPriority($idTask), 1);

    $queryUp = $dbCo->prepare("UPDATE task SET priority = GREATEST(priority - 1, 1) WHERE priority = :priority;");
    $queryUp->execute(['priority' => $priority + 1]);

    $queryUp = $dbCo->prepare("UPDATE task SET priority = GREATEST(priority + 1, 1) WHERE id_task = :id;");
    $queryUp->execute(['id' => $idTask]);

    $isOk = $dbCo->commit();

    if ($isOk) {
        $_SESSION['notif'] = 'Tâche priorisée.';
    }
    else {
        $_SESSION['error'] = 'Erreur lors de la priorisation.';
    }
}

header('Location: index.php');
