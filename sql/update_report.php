<?php
/**
 * update_report.php - UPDATE an existing report (owner or administrator only).
 * Receives the form from edit-report.php.
 */
require_once __DIR__ . '/report_helpers.php';
require_login('../html/login.php');
require_post('../html/dashboard.php');

$id = (int) post('report_id');
$report = db_select_one('SELECT user_id FROM ItemReports WHERE ReportID = ?', 'i', [$id]);
if (!$report) {
    flash('error', 'That report does not exist.');
    redirect('../html/dashboard.php');
}
if ((int) $report['user_id'] !== current_user_id() && !is_admin()) {
    flash('error', 'You can only edit your own reports.');
    redirect('../html/dashboard.php');
}

[$data, $error] = read_report_input();
[$photo, $mime, $upload_error] = read_uploaded_image();
$error = $error ?: $upload_error;
if ($error !== '') {
    flash('error', $error);
    redirect('../html/edit-report.php?id=' . $id);
}

$ok = db_exec(
    'UPDATE ItemReports SET category_id = ?, Title = ?, Location = ?, ItemDate = ?, Description = ?, Contact = ?, Status = ?
      WHERE ReportID = ?',
    'issssssi',
    [$data['category_id'], $data['title'], $data['location'], $data['item_date'],
     $data['description'], $data['contact'] ?: null, $data['status'], $id]);

if ($ok !== false && $photo !== null) {                       // a new picture replaces the old one
    db_exec('UPDATE ItemReports SET Photo = ?, PhotoMime = ? WHERE ReportID = ?', 'bsi', [$photo, $mime, $id]);
} elseif ($ok !== false && post('remove_photo') === '1') {    // or the user ticked "remove image"
    db_exec('UPDATE ItemReports SET Photo = NULL, PhotoMime = NULL WHERE ReportID = ?', 'i', [$id]);
}

if ($ok === false) {
    flash('error', 'Could not update the report. Please try again.');
    redirect('../html/edit-report.php?id=' . $id);
}
flash('success', 'Report updated.');
redirect('../html/item-details.php?id=' . $id);
