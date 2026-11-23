<?php
/**
 * inc/header.php - top of every page: <head>, navigation bar and flash messages.
 *
 * Variables the page sets BEFORE including this file:
 *   $page_title  text for the <title> tag and browser tab
 *   $active      which nav link is highlighted (home|browse|lost|found|dashboard|students|categories)
 */
require_once __DIR__ . '/../../sql/config.php';
$page_title = $page_title ?? 'YU Lost & Found';
$active = $active ?? '';
$nav = [
    'home'      => ['index.php',        'Home'],
    'browse'    => ['browse.php',       'Browse Items'],
    'lost'      => ['report-lost.php',  'Report Lost'],
    'found'     => ['report-found.php', 'Report Found'],
    'dashboard' => ['dashboard.php',    'Dashboard'],
];
if (is_admin()) {
    $nav['students']   = ['students.php',   'Students'];
    $nav['categories'] = ['categories.php', 'Categories'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> | YU Lost &amp; Found</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27%3E%3Ctext y=%27.9em%27 font-size=%2790%27%3E%F0%9F%8E%92%3C/text%3E%3C/svg%3E">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header" id="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="index.php"><span class="brand-mark">YU</span><span>YU Lost &amp; Found</span></a>
        <nav class="nav-links" id="main-nav" aria-label="Main navigation">
            <?php foreach ($nav as $key => [$href, $label]): ?>
                <a class="<?= $active === $key ? 'active' : '' ?>" href="<?= e($href) ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="nav-actions">
            <?php if (is_logged_in()): ?>
                <span class="user-chip" title="Signed in as <?= e($_SESSION['user_name']) ?>">👤 <?= e($_SESSION['user_name']) ?></span>
                <a class="btn btn-outline btn-sm" href="../sql/logout.php">Log out</a>
            <?php else: ?>
                <a class="btn btn-outline btn-sm" href="login.php">Log in</a>
                <a class="btn btn-sm" href="report-lost.php">＋ Report Item</a>
            <?php endif; ?>
            <button class="mobile-menu" type="button" id="menu-toggle" aria-label="Toggle menu" aria-expanded="false" aria-controls="main-nav">☰</button>
        </div>
    </div>
</header>
<?php render_flash(); ?>
