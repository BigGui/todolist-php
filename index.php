<?php

require_once 'vendor/autoload.php';
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
    <header class="main-header">
        <h1 class="main-ttl">AU BOULOT !</h1>
    </header>
    <?= getNotifHtml() ?>
    <ul class="task-list" id="tasksList">

        <?php
        $query = $dbCo->prepare("SELECT `id_task`, `text`, `done` FROM `task` WHERE done = 0 ORDER BY priority ASC, date_create DESC;");

        $isQueryOk = $query->execute();

        foreach ($query->fetchAll() as $task) {
            $isEdit = isset($_GET['action']) && $_GET['action'] === 'edit' && intval($_GET['id']) === intval($task['id_task']);
        ?>
            <li class="task" data-id-task="<?= $task['id_task'] ?>">

                <?php
                if ($task['done'] == 1) { ?>
                    <a class="task__lnk" href="action.php?action=undone&id=<?= $task['id_task'] ?>&token=<?= $_SESSION['token'] ?>" title="décocher">✔️</a>
                <?php } else { ?>
                    <button type="button" class="task__btn js-validate-btn">⭕</button>
                <?php
                }

                if ($isEdit) {
                ?>
                    <form class="inline-form" action="action.php" method="post">
                        <input type="hidden" name="id" value="<?= $task['id_task'] ?>">
                        <input class="inline-form__fld" type="text" name="text" id="text" value="<?= $task['text'] ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                        <input class="inline-form__btn" type="submit" value="🖊️">
                    </form>
                <?php
                } else {
                ?>
                    <h3 class="task__text"><?= $task['text'] ?></h3>
                <?php
                }
                ?>
                <ul class="task__utils">
                    <?php
                    if (!$isEdit) {
                    ?>
                        <li>
                            <a class="task__lnk" href="?action=edit&id=<?= $task['id_task'] ?>" title="modifier">🖊️</a>
                        </li>
                    <?php
                    }
                    ?>
                    <li>
                        <a class="task__lnk" href="action.php?action=delete&id=<?= $task['id_task'] ?>&token=<?= $_SESSION['token'] ?>" title="supprimer">❌</a>
                    </li>
                    <li>
                        <a class="task__lnk" href="action.php?action=up&id=<?= $task['id_task'] ?>&token=<?= $_SESSION['token'] ?>" title="monter">👍🏼</a>
                    </li>
                    <li>
                        <a class="task__lnk" href="action.php?action=down&id=<?= $task['id_task'] ?>&token=<?= $_SESSION['token'] ?>" title="descendre">👎🏼</a>
                    </li>
                </ul>
            </li>
        <?php
        }
        ?>
    </ul>

    <form id="formAdd" class="form-add" action="" method="POST">
        <label class="form-add__lbl" for="text">Nouvelle tâche</label>
        <div class="form-add__wrap">
            <input class="form-add__fld" type="text" name="text" id="text">
            <input type="hidden" name="action" value="add">
            <input id="tokenField" type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
            <input class="form-add__btn" type="submit" value="👉🏻">
        </div>
    </form>

    <template id="taskTemplate">
        <li class="task" data-id-task="">
            <button type="button" class="task__btn js-validate-btn">⭕</button>
            <h3 class="task__text" data-content="text"></h3>
            <ul class="task__utils">
                <li>
                    <a class="task__lnk" href="#" title="modifier">🖊️</a>
                </li>
                <li>
                    <a class="task__lnk" href="#" title="supprimer">❌</a>
                </li>
                <li>
                    <a class="task__lnk" href="#" title="monter">👍🏼</a>
                </li>
                <li>
                    <a class="task__lnk" href="#" title="descendre">👎🏼</a>
                </li>
            </ul>
        </li>
    </template>

    <script src="assets/js/script.js"></script>
</body>

</html>