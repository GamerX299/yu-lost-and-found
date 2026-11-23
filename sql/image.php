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
// Make sure nothing else (warnings, notices, stray spaces) is mixed into the picture bytes.
ini_set('display_errors', '0');
while (ob_get_level() > 0) {
    ob_end_clean();
}
$mime = $row['PhotoMime'];
if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
    $mime = (new finfo(FILEINFO_MIME_TYPE))->buffer($row['Photo']) ?: 'image/jpeg';   // detect from the bytes
}
header('Content-Type: ' . $mime);
header('Content-Length: ' . strlen($row['Photo']));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=3600');
echo $row['Photo'];
