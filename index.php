<?php

// Database connection
$db = new PDO('sqlite:habits.db');

// Create habits table if it doesn't exist
$db->exec("CREATE TABLE IF NOT EXISTS habits (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL,
    description TEXT,
    completed INTEGER DEFAULT 0,
    success_goal INTEGER DEFAULT 0,
    failure_goal INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Create daily progress table if it doesn't exist
$db->exec("CREATE TABLE IF NOT EXISTS habit_progress (
    id INTEGER PRIMARY KEY,
    habit_id INTEGER NOT NULL,
    date DATE NOT NULL,
    successes INTEGER DEFAULT 0,
    failures INTEGER DEFAULT 0,
    FOREIGN KEY (habit_id) REFERENCES habits (id)
)");

// Add a new habit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $stmt = $db->prepare("INSERT INTO habits (name, description, success_goal, failure_goal) VALUES (:name, :description, :success_goal, :failure_goal)");
    $stmt->bindParam(':name', $_POST['name']);
    $stmt->bindParam(':description', $_POST['description']);
    $stmt->bindParam(':success_goal', $_POST['success_goal']);
    $stmt->bindParam(':failure_goal', $_POST['failure_goal']);
    $stmt->execute();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Update daily progress
if (isset($_GET['update_progress'])) {
    $habit_id = $_GET['update_progress'];
    $type = $_GET['type']; // 'success' ou 'failure'
    $date = $_GET['date'] ?? date('Y-m-d'); // Use a data fornecida ou a data atual

    // Check if progress exists for today
    $stmt = $db->prepare("SELECT * FROM habit_progress WHERE habit_id = :habit_id AND date = :date");
    $stmt->execute(['habit_id' => $habit_id, 'date' => $date]);
    $progress = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($progress) {
        // Update progress
        $column = $type === 'success' ? 'successes' : 'failures';
        $db->prepare("UPDATE habit_progress SET $column = $column + 1 WHERE id = :id")
            ->execute(['id' => $progress['id']]);
    } else {
        // Insert new progress
        $successes = $type === 'success' ? 1 : 0;
        $failures = $type === 'failure' ? 1 : 0;
        $db->prepare("INSERT INTO habit_progress (habit_id, date, successes, failures) VALUES (:habit_id, :date, :successes, :failures)")
            ->execute(['habit_id' => $habit_id, 'date' => $date, 'successes' => $successes, 'failures' => $failures]);
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Edit daily progress
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_progress'])) {
    $id = $_POST['progress_id'];
    $successes = $_POST['successes'];
    $failures = $_POST['failures'];

    $stmt = $db->prepare("UPDATE habit_progress SET successes = :successes, failures = :failures WHERE id = :id");
    $stmt->execute(['successes' => $successes, 'failures' => $failures, 'id' => $id]);

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Delete a habit
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM habits WHERE id = :id");
    $stmt->bindParam(':id', $_GET['delete']);
    $stmt->execute();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch all habits
$habits = $db->query("SELECT * FROM habits")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Habit Tracker</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .completed {
            text-decoration: line-through;
            color: green;
        }

        .success {
            background-color: lightgreen;
        }

        .failure {
            background-color: lightcoral;
        }

        .neutral {
            background-color: yellow;
        }
    </style>
</head>

<body>
    <h1>Habit Tracker</h1>

    <!-- Form to add a new habit -->
    <form method="POST">
        <label for="name">Habit Name:</label><br>
        <input type="text" id="name" name="name" required><br>
        <label for="description">Description:</label><br>
        <textarea id="description" name="description"></textarea><br>
        <label for="success_goal">Success Goal (times):</label><br>
        <input type="number" id="success_goal" name="success_goal" min="0" required><br>
        <label for="failure_goal">Failure Threshold (times):</label><br>
        <input type="number" id="failure_goal" name="failure_goal" min="0" required><br>
        <button type="submit">Add Habit</button>
    </form>

    <h2>Your Habits</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Success Goal</th>
                <th>Failure Threshold</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($habits as $habit): ?>
                <tr>
                    <td><?= htmlspecialchars($habit['id']) ?></td>
                    <td class="<?= $habit['completed'] ? 'completed' : '' ?>">
                        <?= htmlspecialchars($habit['name']) ?>
                    </td>
                    <td><?= htmlspecialchars($habit['description']) ?></td>
                    <td><?= htmlspecialchars($habit['success_goal']) ?></td>
                    <td><?= htmlspecialchars($habit['failure_goal']) ?></td>
                    <td>
                        <form method="GET" style="display: inline;">
                            <input type="hidden" name="update_progress" value="<?= $habit['id'] ?>">
                            <input type="hidden" name="type" value="success">
                            <input type="date" name="date" value="<?= date('Y-m-d') ?>" required>
                            <button type="submit">+ Success</button>
                        </form>

                        <form method="GET" style="display: inline;">
                            <input type="hidden" name="update_progress" value="<?= $habit['id'] ?>">
                            <input type="hidden" name="type" value="failure">
                            <input type="date" name="date" value="<?= date('Y-m-d') ?>" required>
                            <button type="submit">+ Failure</button>
                        </form>
                        <a href="?delete=<?= $habit['id'] ?>" onclick="return confirm('Are you sure you want to delete this habit?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Monthly Progress</h2>
    <?php foreach ($habits as $habit): ?>
        <h3><?= htmlspecialchars($habit['name']) ?></h3>
        <table>
            <thead>
                <tr>
                    <?php for ($i = 1; $i <= 31; $i++): ?>
                        <th><?= $i ?></th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php
                    $current_month = date('Y-m');
                    for ($i = 1; $i <= 31; $i++) {
                        $date = "$current_month-" . str_pad($i, 2, '0', STR_PAD_LEFT);
                        $stmt = $db->prepare("SELECT * FROM habit_progress WHERE habit_id = :habit_id AND date = :date");
                        $stmt->execute(['habit_id' => $habit['id'], 'date' => $date]);
                        $progress = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($progress) {
                            echo "<td class='" .
                                ($progress['successes'] > $progress['failures'] ? 'success' : ($progress['failures'] > $progress['successes'] ? 'failure' : '')) .
                                "'>".($progress['successes'] > $progress['failures'] ? '&#x2713;' . $progress['successes'] : ($progress['failures'] > $progress['successes'] ? '&#x2717;' .$progress['failures'] : '')) ."</td>";
                        } else {
                            echo "<td></td>";
                        }
                    }
                    ?>
                </tr>
            </tbody>
        </table>
    <?php endforeach; ?>

</body>

</html>
