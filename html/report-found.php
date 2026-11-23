<?php
/**
 * report-found.php - form to report a found item (CREATE). Login required.
 * The form itself lives in inc/report_form.php; it posts to sql/report_action.php.
 */
require_once __DIR__ . '/../sql/config.php';
require_login();

$mode = 'found';
$report = null;
$page_title = 'Report Found';
$active = 'found';
include __DIR__ . '/inc/header.php';
?>
<main>
    <section class="page-hero">
        <div class="container"><h1>Report a found item</h1><p>Share a few details to help the university community identify the item.</p></div>
    </section>
    <section class="container content">
        <div class="form-layout">
            <?php include __DIR__ . '/inc/report_form.php'; ?>
            <?php include __DIR__ . '/inc/report_tips.php'; ?>
        </div>
    </section>
</main>
<?php include __DIR__ . '/inc/footer.php'; ?>
