<?php
/**
 * report_status.php - UPDATE a report's state: open <-> returned
 * (owner or administrator only).
 */
require_once __DIR__ . '/config.php';
require_login('../html/login.php');
require_post('../html/dashboard.php');

$id = (int) post('report_id');
$state = post('state');
$report = db_select_one('SELECT user_id FROM ItemReports WHERE ReportID = ?', 'i', [$id]);

if (!$report || !in_array($state, ['open', 'returned'], true)) {
    flash('error', 'Invalid request.');
} elseif ((int) $report['user_id'] !== current_user_id() && !is_admin()) {
    flash('error', 'You can only change your own reports.');
} elseif (db_exec('UPDATE ItemReports SET ReportState = ? WHERE ReportID = ?', 'si', [$state, $id]) === false) {
    flash('error', 'Could not update the report.');
} else {
    flash('success', $state === 'returned' ? 'Report marked as returned.' : 'Report re-opened.');
}
redirect('../html/dashboard.php');
