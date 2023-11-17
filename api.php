<?php

require_once 'vendor/autoload.php';
require_once 'includes/_functions.php';

header('content-type:application/json');


$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['action'])) {
    echo json_encode([
        'result' => false,
        'error' => 'Aucune action'
    ]);
    exit;
}

include 'includes/_db.php';

// Start user session
session_start();

// Check for CSRF and redirect in case of invalid token or referer
checkCSRFAsync($data);

// Prevent XSS fault
checkXSS($data);

if ($data['action'] === 'done' && isset($data['id'])) {

    $id = intval($data['id']);

    if (empty($id)) {
        echo json_encode([
            'result' => false,
            'error' => 'Tâche inconnue'
        ]);
        exit;
    };
    
    $dbCo->beginTransaction();

    // Get priority value from the selected task.
    $priority = getPriority($id);

    // Change done value to validate the task
    $query = $dbCo->prepare("UPDATE task SET done = 1 WHERE id_task = :id;");
    $query->execute(['id' => $id]);

    if ($query->rowCount() !== 1) {
        $dbCo->rollback();
        echo json_encode([
            'result' => false,
            'error' => 'Problème de requête'
        ]);
        exit;
    }

    moveUpPriorityAbove($priority);

    if (!$dbCo->commit()) {
        echo json_encode([
            'result' => false,
            'error' => 'Problème de requête'
        ]);
        exit;
    };
    
    echo json_encode([
        'result' => true,
        'notification' => 'La tâche a bien été effectuée.'
    ]);
    exit;
}