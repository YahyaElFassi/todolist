<?php
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'todolist');
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['task'])) {
        $task = trim($_POST['task']);
        if (!empty($task)) {
            $stmt = $conn->prepare("INSERT INTO todo (title) VALUES (?)");
            $stmt->bind_param("s", $task);
            $stmt->execute();
            $stmt->close();
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if (isset($_POST['action'], $_POST['task_id'])) {
        $task_id = (int)$_POST['task_id'];

        if ($_POST['action'] === 'done') {
            $status_result = $conn->query("SELECT done FROM todo WHERE id = $task_id");
            if ($status_result && $row = $status_result->fetch_assoc()) {
                $new_done = $row['done'] ? 0 : 1;
                $stmt = $conn->prepare("UPDATE todo SET done = ? WHERE id = ?");
                $stmt->bind_param("ii", $new_done, $task_id);
                $stmt->execute();
                $stmt->close();
            }
        } elseif ($_POST['action'] === 'delete') {
            $stmt = $conn->prepare("DELETE FROM todo WHERE id = ?");
            $stmt->bind_param("i", $task_id);
            $stmt->execute();
            $stmt->close();
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

$result = $conn->query("SELECT * FROM todo ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Todo List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="Title1">TodoList</div>
    <form action="" method="POST">
        <div id="AddDiv">
            <input type="text" name="task" placeholder="Enter your task..." required />
            <button id="Add" type="submit">Add</button>
        </div>
    </form>

    <div id="Table">
        <ul>
            <?php while ($row = $result->fetch_assoc()): ?>
                <li style="<?= $row['done'] ? 'text-decoration: line-through;' : '' ?>">
                    <div class="task-row">
                        <span class="task-title"><?= htmlspecialchars($row['title']) ?></span>
                        <form method="POST" class="inline-form">
                        <input type="hidden" name="task_id" value="<?= $row['id'] ?>" />
                        <button name="action" value="done" type="submit">
                            <?= $row['done'] ? "Undo" : "Done" ?>
                        </button>
                        </form>
                        <form method="POST" class="inline-form">
                        <input type="hidden" name="task_id" value="<?= $row['id'] ?>" />
                        <button name="action" value="delete" type="submit">Delete</button>
                        </form>
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</body>
</html>

<?php $conn->close(); ?>
