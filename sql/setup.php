<?php
/**
 * setup.php - one-click installer. Open it once in the browser:
 *      http://localhost/project322/sql/setup.php
 *
 * It creates the database, the 4 tables and the sample data.
 * Add ?reset=1 to delete everything and start from scratch (localhost only).
 */
require_once __DIR__ . '/setup_lib.php';

$messages = [];
$is_local = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true) || PHP_SAPI === 'cli';

if (isset($_GET['reset']) && $is_local) {
    [, , , $name] = db_settings();
    $conn = db_server();
    mysqli_query($conn, 'DROP DATABASE IF EXISTS `' . str_replace('`', '``', $name) . '`');
    mysqli_close($conn);
    $messages[] = "Database `$name` was deleted.";
}

$messages = array_merge($messages, create_database(), create_tables(), seed_database());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup | Lost &amp; Found</title>
    <link rel="stylesheet" href="../html/css/style.css">
</head>
<body>
<main class="container content">
    <div class="card form-card">
        <h2>Database setup</h2>
        <ul>
            <?php foreach ($messages as $m): ?>
                <li><?= e($m) ?></li>
            <?php endforeach; ?>
        </ul>
        <p class="hint">Demo logins: <strong>admin / Admin@1234</strong> and <strong>sara_ali / Student@123</strong></p>
        <a class="btn" href="../html/index.php">Open the website →</a>
    </div>
</main>
</body>
</html>
