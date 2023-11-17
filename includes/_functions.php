<?php


// ---------------------
// SECURITY FUNCTIONS
// ---------------------


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
 * Check for CSRF with referer and token
 *
 * @param array $data
 * @return void
 */
function checkCSRFAsync(array $data): void
{
    if (!isset($_SERVER['HTTP_REFERER']) || !str_contains($_SERVER['HTTP_REFERER'], 'http://localhost/todolist/')) {
        $error = 'error_referer';
    } else if (
        !isset($_SESSION['token']) || !isset($data['token'])
        || $data['token'] !== $_SESSION['token']
        || $_SESSION['tokenExpire'] < time()
    ) {
        $error = 'error_token';
    }
    if (!isset($error)) return;

    echo json_encode([
        'result' => false,
        'error' => $error
    ]);
    exit;
}

/**
 * Apply treatment on given array to prevent XSS fault.
 * 
 * @param array &$array
 */
function checkXSS(array &$array): void
{
    $array = array_map('strip_tags', $array);
    // foreach ($array as $key => $value) {
    //     $array[$key] = strip_tags($value);
    // }
}


// ---------------------
// TASK MANAGEMENT
// ---------------------

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

/**
 * Add an error to display and stop script
 * 
 * @param string $error
 */
function addErrorAndExit(string $error): void
{
    $_SESSION['error'] = $error;
    header('Location: index.php');
    exit;
}

/**
 * Add a notification to display
 * 
 * @param string $text
 */
function addNotification(string $text): void
{
    $_SESSION['notif'] = $text;
}

// ---------------------
// LAYOUT
// ---------------------


function getNotifHtml(): string
{
    $html = '<ul id="notification-wrapper" class="notif-wrapper">';

    if (isset($_SESSION['notif'])) {
        $html .= '<div class="notification">😀 ' . $_SESSION['notif'] . '</div>';
        unset($_SESSION['notif']);
    }

    if (isset($_SESSION['error'])) {
        $html .= '<div class="error">😨 ' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }

    $html .= '</ul>';

    return $html;
}
