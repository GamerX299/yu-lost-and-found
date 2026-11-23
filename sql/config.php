<?php
/**
 * config.php - shared configuration and helper functions.
 *
 * Every page (html/*.php) and every handler (sql/*.php) includes this file first.
 * It provides:
 *   - the database connection  (db(), db_select(), db_select_one(), db_exec())
 *   - session / login helpers  (is_logged_in(), require_login(), require_admin(), ...)
 *   - flash messages           (flash(), render_flash())
 *   - CSRF protection          (csrf_field(), csrf_check())
 *   - small output helpers     (e(), fmt_date(), redirect())
 *
 * Database settings are read from "C:/project/db_login.php" (the team's shared
 * file) or, as a fallback, from "sql/db_login.php". If neither exists the XAMPP
 * defaults are used (root, no password, database "yu_lost_found").
 * See sql/db_login.example.php for the expected variables.
 */

mysqli_report(MYSQLI_REPORT_OFF);          // we check return values ourselves
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ------------------------------------------------------------------ */
/* Database                                                           */
/* ------------------------------------------------------------------ */

/** Returns [host, user, password, database name]. */
function db_settings(): array
{
    $db_host = 'localhost';
    $db_user = 'root';
    $db_password = '';
    $db_name = 'yu_lost_found';

    foreach (['C:/project/db_login.php', __DIR__ . '/db_login.php'] as $file) {
        if (is_file($file)) {
            include $file;   // may override the four variables above
            break;
        }
    }
    return [$db_host, $db_user, $db_password, $db_name];
}

/** Stops the request and shows a plain error page. */
function fatal_error(string $title, string $message): void
{
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>'
        . e($title) . '</title></head><body style="font-family:sans-serif;max-width:640px;margin:60px auto">'
        . '<h1>' . e($title) . '</h1><p>' . e($message) . '</p></body></html>';
    exit;
}

/** Connection to the server WITHOUT selecting a database (used by the setup scripts). */
function db_server(): mysqli
{
    [$host, $user, $pass] = db_settings();
    $conn = @mysqli_connect($host, $user, $pass);
    if (!$conn) {
        fatal_error('Database connection failed',
            'Could not connect to MySQL (' . mysqli_connect_errno() . ': ' . mysqli_connect_error()
            . '). Start MySQL in the XAMPP control panel and check db_login.php.');
    }
    mysqli_set_charset($conn, 'utf8mb4');
    return $conn;
}

/** Shared connection to the project database. */
function db(): mysqli
{
    static $conn = null;
    if ($conn instanceof mysqli) {
        return $conn;
    }
    [$host, $user, $pass, $name] = db_settings();
    $conn = @mysqli_connect($host, $user, $pass, $name);
    if (!$conn) {
        fatal_error('Database connection failed',
            'Could not connect to database "' . $name . '" (' . mysqli_connect_errno() . ': '
            . mysqli_connect_error() . '). Start MySQL in XAMPP, then open sql/setup.php once '
            . 'to create the database, tables and sample data.');
    }
    mysqli_set_charset($conn, 'utf8mb4');
    return $conn;
}

/** Error number of the last failed db_exec()/db_select() call (1062 = duplicate key). */
function db_errno(?int $set = null): int
{
    static $errno = 0;
    if ($set !== null) {
        $errno = $set;
    }
    return $errno;
}

/**
 * Prepares and runs a statement. Types follow mysqli: i, d, s and b (binary/blob).
 * Returns the executed statement or false on failure.
 */
function db_run(string $sql, string $types = '', array $params = [])
{
    db_errno(0);
    $stmt = mysqli_prepare(db(), $sql);
    if (!$stmt) {
        db_errno(mysqli_errno(db()));
        error_log('SQL prepare failed: ' . mysqli_error(db()));
        return false;
    }
    if ($types !== '') {
        $blobs = [];
        foreach (str_split($types) as $i => $t) {
            if ($t === 'b') {                 // send blobs in a separate packet
                $blobs[$i] = $params[$i];
                $params[$i] = null;
            }
        }
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        foreach ($blobs as $i => $data) {
            if ($data !== null) {
                mysqli_stmt_send_long_data($stmt, $i, $data);
            }
        }
    }
    if (!mysqli_stmt_execute($stmt)) {
        db_errno(mysqli_stmt_errno($stmt));
        error_log('SQL execute failed: ' . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }
    return $stmt;
}

/** SELECT many rows -> array of associative arrays (empty array on failure). */
function db_select(string $sql, string $types = '', array $params = []): array
{
    $stmt = db_run($sql, $types, $params);
    if (!$stmt) {
        return [];
    }
    $result = mysqli_stmt_get_result($stmt);
    $rows = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
    mysqli_stmt_close($stmt);
    return $rows;
}

/** SELECT one row -> associative array or null. */
function db_select_one(string $sql, string $types = '', array $params = []): ?array
{
    $rows = db_select($sql, $types, $params);
    return $rows[0] ?? null;
}

/** INSERT / UPDATE / DELETE -> affected rows, or false on failure. */
function db_exec(string $sql, string $types = '', array $params = [])
{
    $stmt = db_run($sql, $types, $params);
    if (!$stmt) {
        return false;
    }
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    return $affected;
}

/** Id generated by the last INSERT. */
function db_insert_id(): int
{
    return (int) mysqli_insert_id(db());
}

/* ------------------------------------------------------------------ */
/* Output helpers                                                     */
/* ------------------------------------------------------------------ */

/** HTML-escape a value (always use this when printing user data). */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function fmt_date(?string $date): string
{
    return $date ? date('M d, Y', strtotime($date)) : '';
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/** Cycles the coloured background of an item card by category. */
function visual_class(int $category_id): string
{
    $classes = ['', 'gold', 'green', 'purple'];
    return $classes[$category_id % count($classes)];
}

/* ------------------------------------------------------------------ */
/* Login / roles                                                      */
/* ------------------------------------------------------------------ */

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function current_user_id(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

function is_admin(): bool
{
    return ($_SESSION['role'] ?? '') === 'admin';
}

/** Redirects guests to the login page. $back is relative to the html/ folder. */
function require_login(string $login_url = 'login.php'): void
{
    if (!is_logged_in()) {
        flash('error', 'Please log in to continue.');
        redirect($login_url);
    }
}

function require_admin(string $home_url = 'dashboard.php'): void
{
    require_login();
    if (!is_admin()) {
        flash('error', 'Only administrators can open that page.');
        redirect($home_url);
    }
}

/* ------------------------------------------------------------------ */
/* Flash messages (one-time notices shown after a redirect)           */
/* ------------------------------------------------------------------ */

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function render_flash(): void
{
    foreach ($_SESSION['flash'] ?? [] as $f) {
        $class = $f['type'] === 'success' ? 'flash-success' : 'flash-error';
        echo '<div class="container"><div class="flash ' . $class . '" role="alert">'
            . e($f['message']) . '<button type="button" class="flash-close" aria-label="Dismiss">×</button></div></div>';
    }
    unset($_SESSION['flash']);
}

/* ------------------------------------------------------------------ */
/* CSRF protection for every POST form                                */
/* ------------------------------------------------------------------ */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** Call at the top of every handler: only accepts POST requests with a valid token. */
function require_post(string $back_url = '../html/index.php'): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        flash('error', 'Invalid request. Please use the form on the page.');
        redirect($back_url);
    }
    $sent = (string) ($_POST['csrf'] ?? '');
    if ($sent === '' || !hash_equals(csrf_token(), $sent)) {
        flash('error', 'Your session expired. Please try again.');
        redirect($back_url);
    }
}

/** Trimmed string from $_POST. */
function post(string $key): string
{
    return trim((string) ($_POST[$key] ?? ''));
}
