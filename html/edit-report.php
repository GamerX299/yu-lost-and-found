<?php
/**
 * edit-report.php?id=N - edit an existing report (UPDATE).
 * Only the owner of the report or an administrator may open it.
 */
require_once __DIR__ . '/../sql/config.php';
require_login();

$id = (int) ($_GET['id'] ?? 0);
$report = db_select_one('SELECT *, (Photo IS NOT NULL) AS has_photo FROM ItemReports WHERE ReportID = ?', 'i', [$id]);

if (!$report) {
    flash('error', 'That report does not exist.');
    redirect('dashboard.php');
}
if ((int) $report['user_id'] !== current_user_id() && !is_admin()) {
    flash('error', 'You can only edit your own reports.');
    redirect('dashboard.php');
}

$mode = $report['Status'];
$page_title = 'Edit Report';
$active = 'dashboard';
include __DIR__ . '/inc/header.php';
?>
<main>
    <section class="page-hero">
        <div class="container"><h1>Edit report</h1><p>Update the details of your <?= e($mode) ?> item report.</p></div>
    </section>
    <section class="container content">
        <div class="form-layout">
            <?php include __DIR__ . '/inc/report_form.php'; ?>
            <?php include __DIR__ . '/inc/report_tips.php'; ?>
        </div>
    </section>
</main>
<?php include __DIR__ . '/inc/footer.php'; ?>
