<?php
/**
 * image.php?id=N - sends the picture stored in ItemReports.Photo to the browser.
 * Used as <img src="../sql/image.php?id=N"> on the cards and detail page.
 */
require_once __DIR__ . '/config.php';

$row = db_select_one('SELECT Photo, PhotoMime FROM ItemReports WHERE ReportID = ? AND Photo IS NOT NULL', 'i', [(int) ($_GET['id'] ?? 0)]);
if (!$row) {
    http_response_code(404);
    exit;
}
header('Content-Type: ' . $row['PhotoMime']);
header('Content-Length: ' . strlen($row['Photo']));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=3600');
echo $row['Photo'];
