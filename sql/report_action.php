<?php
/**
 * report_action.php - CREATE a lost/found report (INSERT into ItemReports).
 * Receives the form from report-lost.php / report-found.php.
 */
require_once __DIR__ . '/report_helpers.php';
require_login('../html/login.php');
require_post('../html/report-lost.php');

[$data, $error] = read_report_input();
[$photo, $mime, $upload_error] = read_uploaded_image();
$error = $error ?: $upload_error;

$back = $data['status'] === 'found' ? '../html/report-found.php' : '../html/report-lost.php';
if ($error !== '') {
    flash('error', $error);
    redirect($back);
}

$contact = $data['contact'] !== '' ? $data['contact'] : ($_SESSION['user_email'] ?? null);
$ok = db_exec(
    'INSERT INTO ItemReports (user_id, category_id, Title, Location, ItemDate, Description, Contact, Photo, PhotoMime, Status)
     VALUES (?,?,?,?,?,?,?,?,?,?)',
    'iisssssbss',
    [current_user_id(), $data['category_id'], $data['title'], $data['location'], $data['item_date'],
     $data['description'], $contact, $photo, $mime, $data['status']]);

if ($ok === false) {
    flash('error', 'Could not save your report. Please try again.');
    redirect($back);
}
flash('success', 'Your ' . $data['status'] . ' report was submitted successfully.');
redirect('../html/item-details.php?id=' . db_insert_id());
