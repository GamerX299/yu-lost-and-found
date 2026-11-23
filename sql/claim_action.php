<?php
/**
 * claim_action.php - everything that changes the Claims table.
 *   create  (CREATE) a student sends a claim / message on someone else's open report
 *   approve (UPDATE) the report owner accepts a claim -> report becomes "returned",
 *                    other pending claims on that report are rejected
 *   reject  (UPDATE) the report owner refuses a claim
 *   delete  (DELETE) the claimant withdraws their own claim
 */
require_once __DIR__ . '/config.php';
require_login('../html/login.php');
require_post('../html/dashboard.php');

$action = post('action');
$uid = current_user_id();

if ($action === 'create') {
    $report_id = (int) post('report_id');
    $message = post('message');
    $back = '../html/item-details.php?id=' . $report_id;
    $report = db_select_one('SELECT user_id, ReportState FROM ItemReports WHERE ReportID = ?', 'i', [$report_id]);

    if (!$report) {
        flash('error', 'That report does not exist.');
        redirect('../html/browse.php');
    }
    if ((int) $report['user_id'] === $uid) {
        flash('error', 'You cannot claim your own report.');
    } elseif ($report['ReportState'] !== 'open') {
        flash('error', 'This item has already been returned.');
    } elseif (mb_strlen($message) < 10 || mb_strlen($message) > 500) {
        flash('error', 'Your message must be between 10 and 500 characters.');
    } elseif (db_exec('INSERT INTO Claims (ReportID, user_id, Message) VALUES (?,?,?)', 'iis', [$report_id, $uid, $message]) === false) {
        flash('error', db_errno() === 1062 ? 'You already sent a claim for this item.' : 'Could not send your claim.');
    } else {
        flash('success', 'Your message was sent to the owner of the report.');
    }
    redirect($back);
}

$claim_id = (int) post('claim_id');
$claim = db_select_one(
    'SELECT cl.ClaimID, cl.ReportID, cl.user_id AS claimant, r.user_id AS owner
       FROM Claims cl JOIN ItemReports r ON r.ReportID = cl.ReportID
      WHERE cl.ClaimID = ?', 'i', [$claim_id]);

if (!$claim) {
    flash('error', 'That claim does not exist.');
} elseif ($action === 'delete') {
    if ((int) $claim['claimant'] !== $uid && !is_admin()) {
        flash('error', 'You can only withdraw your own claims.');
    } elseif (db_exec('DELETE FROM Claims WHERE ClaimID = ?', 'i', [$claim_id]) === false) {
        flash('error', 'Could not withdraw the claim.');
    } else {
        flash('success', 'Claim withdrawn.');
    }
} elseif ($action === 'approve' || $action === 'reject') {
    if ((int) $claim['owner'] !== $uid && !is_admin()) {
        flash('error', 'Only the owner of the report can answer claims.');
    } elseif ($action === 'reject') {
        db_exec("UPDATE Claims SET ClaimStatus = 'rejected' WHERE ClaimID = ?", 'i', [$claim_id]);
        flash('success', 'Claim rejected.');
    } else {
        db_exec("UPDATE Claims SET ClaimStatus = 'approved' WHERE ClaimID = ?", 'i', [$claim_id]);
        db_exec("UPDATE Claims SET ClaimStatus = 'rejected' WHERE ReportID = ? AND ClaimID <> ? AND ClaimStatus = 'pending'", 'ii', [$claim['ReportID'], $claim_id]);
        db_exec("UPDATE ItemReports SET ReportState = 'returned' WHERE ReportID = ?", 'i', [$claim['ReportID']]);
        flash('success', 'Claim approved - the report is now marked as returned.');
    }
} else {
    flash('error', 'Unknown action.');
}
redirect('../html/dashboard.php');
