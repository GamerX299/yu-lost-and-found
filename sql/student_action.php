<?php
/**
 * student_action.php - CREATE / UPDATE / DELETE rows of the Student table.
 * Used only by students.php, so every action requires the administrator role.
 */
require_once __DIR__ . '/config.php';
require_admin('../html/dashboard.php');
require_post('../html/students.php');

$action = post('action');
$id = (int) post('u_id');
$name = post('u_name');
$email = post('u_email');
$password = $_POST['u_pass'] ?? '';
$role = post('role') === 'admin' ? 'admin' : 'student';
$back = '../html/students.php';

/** Number of administrator accounts (we never allow it to reach zero). */
$admin_count = fn() => (int) db_select_one("SELECT COUNT(*) AS n FROM Student WHERE role = 'admin'")['n'];

if ($action === 'delete') {
    $target = db_select_one('SELECT role, user_name FROM Student WHERE user_id = ?', 'i', [$id]);
    if (!$target) {
        flash('error', 'No student found with that ID.');
    } elseif ($id === current_user_id()) {
        flash('error', 'You cannot delete your own account.');
    } elseif ($target['role'] === 'admin' && $admin_count() <= 1) {
        flash('error', 'You cannot delete the last administrator.');
    } elseif (db_exec('DELETE FROM Student WHERE user_id = ?', 'i', [$id]) === false) {
        flash('error', 'Could not delete the student.');
    } else {
        flash('success', 'Student ' . $target['user_name'] . ' was deleted.');
    }
    redirect($back);
}

if ($action !== 'create' && $action !== 'update') {
    flash('error', 'Unknown action.');
    redirect($back);
}

// ---- shared validation for create + update -----------------------------
$error = '';
if ($id <= 0) {
    $error = 'Student ID must be a positive number.';
} elseif (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $name)) {
    $error = 'Username must be 3-30 characters: letters, numbers, "_" or ".".';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
    $error = 'Please enter a valid email address.';
} elseif (($action === 'create' || $password !== '') && strlen($password) < 8) {
    $error = 'Password must be at least 8 characters.';
}
if ($error !== '') {
    flash('error', $error);
    redirect($action === 'update' ? $back . '?edit=' . $id : $back);
}

if ($action === 'create') {
    $ok = db_exec('INSERT INTO Student (user_id, user_name, email, password, role) VALUES (?,?,?,?,?)',
        'issss', [$id, $name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
    if ($ok === false) {
        flash('error', db_errno() === 1062 ? 'That ID, username or email already exists.' : 'Could not add the student.');
    } else {
        flash('success', 'Student ' . $name . ' was added.');
    }
    redirect($back);
}

// ---- update --------------------------------------------------------------
$target = db_select_one('SELECT role FROM Student WHERE user_id = ?', 'i', [$id]);
if (!$target) {
    flash('error', 'No student found with that ID.');
    redirect($back);
}
if ($target['role'] === 'admin' && $role === 'student' && $admin_count() <= 1) {
    flash('error', 'You cannot remove the role of the last administrator.');
    redirect($back . '?edit=' . $id);
}

$ok = db_exec('UPDATE Student SET user_name = ?, email = ?, role = ? WHERE user_id = ?', 'sssi', [$name, $email, $role, $id]);
if ($ok !== false && $password !== '') {
    db_exec('UPDATE Student SET password = ? WHERE user_id = ?', 'si', [password_hash($password, PASSWORD_DEFAULT), $id]);
}
if ($ok === false) {
    flash('error', db_errno() === 1062 ? 'That username or email is already used by someone else.' : 'Could not update the student.');
    redirect($back . '?edit=' . $id);
}
if ($id === current_user_id()) {                         // keep our own session in sync
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['role'] = $role;
}
flash('success', 'Student ' . $name . ' was updated.');
redirect($back);
