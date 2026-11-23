<?php
/**
 * delete_report.php - DELETE a report (owner or administrator only).
 * Its claims are removed automatically by the ON DELETE CASCADE foreign key.
 */
require_once __DIR__ . '/config.php';
require_login('../html/login.php');
require_post('../html/dashboard.php');

$id = (int) post('report_id');
$report = db_select_one('SELECT user_id, Title FROM ItemReports WHERE ReportID = ?', 'i', [$id]);

if (!$report) {
    flash('error', 'That report does not exist.');
} elseif ((int) $report['user_id'] !== current_user_id() && !is_admin()) {
    flash('error', 'You can only delete your own reports.');
} elseif (db_exec('DELETE FROM ItemReports WHERE ReportID = ?', 'i', [$id]) === false) {
    flash('error', 'Could not delete the report.');
} else {
    flash('success', 'Report "' . $report['Title'] . '" was deleted.');
}
redirect('../html/dashboard.php');
