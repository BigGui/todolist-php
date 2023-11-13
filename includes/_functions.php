<?php

/**
 * Generate a valid token in $_SESSION
 *
 * @return void
 */
function generateToken()
{
    if (!isset($_SESSION['token']) || time() > $_SESSION['tokenExpire']) {
        $_SESSION['token'] = md5(uniqid(mt_rand(), true));
        $_SESSION['tokenExpire'] = time() + 15 * 60;
    }
}

/**
 * Check for CSRF with referer and token
 * Redirect to the given page in case of error
 *
 * @param string $url The page to redirect
 * @return void
 */
function checkCSRF(string $url): void
{
    if (!isset($_SERVER['HTTP_REFERER']) || !str_contains($_SERVER['HTTP_REFERER'], 'http://localhost/todolist/')) {
        $_SESSION['error'] = 'error_referer';
    } else if (
        !isset($_SESSION['token']) || !isset($_REQUEST['token'])
        || $_REQUEST['token'] !== $_SESSION['token']
        || $_SESSION['tokenExpire'] < time()
    ) {
        $_SESSION['error'] = 'error_token';
    }
    if (!isset($_SESSION['error'])) return;

    header('Location: ' . $url);
    exit;
}

/**
 * Get the priority value for a new task.
 *
 * @return integer|null
 */
function getNewPriority(): ?int
{
    global $dbCo;

    $query = $dbCo->prepare("SELECT IFNULL(MAX(priority), 0) + 1 AS new_priority FROM task;");
    $isOk = $query->execute();
    return $isOk ? $query->fetchColumn() : null;
}

/**
 * Get the priority of the given task
 *
 * @param integer $idTask Id of the task
 * @return integer priority value
 */
function getPriority(int $idTask): int
{
    global $dbCo;

    $query = $dbCo->prepare("SELECT priority FROM task WHERE id_task = :id;");
    $query->execute(['id' => $idTask]);
    return $query->fetchColumn();
}

/**
 * Move up the priority value of all task above the given priority value
 *
 * @param integer $minPriority
 * @return boolean
 */
function moveUpPriorityAbove(int $minPriority): bool
{
    global $dbCo;

    $query = $dbCo->prepare("UPDATE task SET priority = priority - 1 WHERE priority > :priority;");
    return $query->execute(['priority' => $minPriority]);
}
