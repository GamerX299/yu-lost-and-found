<?php
/**
 * report_helpers.php - validation shared by report_action.php (create)
 * and update_report.php (update).
 */
require_once __DIR__ . '/config.php';

/**
 * Reads and validates the report form fields from $_POST.
 * Returns [data array, error message]. $error is '' when everything is valid.
 */
function read_report_input(): array
{
    $data = [
        'status'      => post('status'),
        'title'       => post('title'),
        'category_id' => (int) post('category_id'),
        'item_date'   => post('item_date'),
        'location'    => post('location'),
        'description' => post('description'),
        'contact'     => post('contact'),
    ];

    if (!in_array($data['status'], ['lost', 'found'], true)) {
        return [$data, 'Invalid report type.'];
    }
    foreach (['title' => 'item name', 'location' => 'location', 'description' => 'description'] as $key => $label) {
        if ($data[$key] === '') {
            return [$data, "Please fill in the $label."];
        }
    }
    if (mb_strlen($data['title']) > 100 || mb_strlen($data['location']) > 120
        || mb_strlen($data['description']) > 1000 || mb_strlen($data['contact']) > 120) {
        return [$data, 'One of the fields is too long.'];
    }
    $date = DateTime::createFromFormat('Y-m-d', $data['item_date']);
    if (!$date || $date->format('Y-m-d') !== $data['item_date']) {
        return [$data, 'Please enter a valid date.'];
    }
    if ($data['item_date'] > date('Y-m-d')) {
        return [$data, 'The date cannot be in the future.'];
    }
    if (!db_select_one('SELECT category_id FROM Category WHERE category_id = ?', 'i', [$data['category_id']])) {
        return [$data, 'Please choose a category.'];
    }
    return [$data, ''];
}

/**
 * Reads the optional uploaded picture.
 * Returns [binary data|null, mime|null, error message]. No file selected is not an error.
 */
function read_uploaded_image(string $field = 'image'): array
{
    if (!isset($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [null, null, ''];
    }
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, null, 'The image could not be uploaded (is it larger than the server limit?).'];
    }
    // XAMPP's MySQL only accepts values up to max_allowed_packet (1 MB by default),
    // so pictures are limited to 1 MB (less if the server is configured lower).
    $packet = (int) (db_select_one('SELECT @@max_allowed_packet AS p')['p'] ?? 1048576);
    $limit = min(1000000, $packet - 65536);
    if ($file['size'] > $limit) {
        return [null, null, 'The image must be smaller than 1 MB. Please choose a smaller picture.'];
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);   // real type, not the browser's claim
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
        return [null, null, 'Only JPG, PNG, GIF or WebP images are allowed.'];
    }
    return [file_get_contents($file['tmp_name']), $mime, ''];
}
