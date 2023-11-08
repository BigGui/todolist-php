<?php

require_once 'includes/_functions.php';
include 'includes/_db.php';

session_start();
generateToken();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AU BOULOT!</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php

    if (isset($_GET['notif'])) {
        $msg[] = urldecode($_GET['notif']);
    }
    if (isset($msg) && !empty($msg)) {
        echo '<div class="notification">😀 ' . implode(' | ', $msg) . '</div>';
    }

    if (isset($_GET['error'])) {
        $error[] = urldecode($_GET['error']);
    }
    if (isset($error) && !empty($error)) {
        echo '<div class="error">😨 ' . implode(' | ', $error) . '</div>';
    }

    ?>
    <ul class="task-list">

        <?php
        $query = $dbCo->prepare("SELECT `id_task`, `text` FROM `task` WHERE done = 0 ORDER BY date_create DESC;");

        $isQueryOk = $query->execute();

        foreach ($query->fetchAll() as $task) {
        ?>
            <li class="task">
                <a class="task__lnk" href="action.php?action=done&id=<?= $task['id_task'] ?>&token=<?= $_SESSION['token'] ?>" title="effectuée">⭕</a>
                <h3 class="task__text"><?= $task['text'] ?></h3>
                <ul class="task__utils">
                    <li>
                        <a class="task__lnk" href="#" title="modifier">🖊️</a>
                    </li>
                    <li>
                        <a class="task__lnk"/ href="action.php?action=delete&id=<?= $task['id_task'] ?>" title="supprimer">❌</a>
                    </li>
                    <li>
                        <a class="task__lnk" href="#" title="monter">👍🏼</a>
                    </li>
                    <li>
                        <a class="task__lnk" href="#" title="descendre">👎🏼</a>
                    </li>
                </ul>
            </li>
        <?php
        }
        ?>
    </ul>

    <form class="form-add" action="action.php" method="POST">
        <label class="form-add__lbl" for="text">Nouvelle tâche</label>
        <div class="form-add__wrap">
            <input class="form-add__fld" type="text" name="text" id="text">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
            <input class="form-add__btn" type="submit" value="👉🏻">
        </div>
    </form>
</body>

</html>