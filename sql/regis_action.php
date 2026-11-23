<?php
/**
 * regis_action.php - CREATE a student account (INSERT into Student).
 * Receives the "Create an account" form from login.php.
 */
require_once __DIR__ . '/config.php';
require_post('../html/login.php#register');

$back = '../html/login.php#register';
$user_id_raw = post('u_id');
$user_name = post('u_name');
$user_email = post('u_email');
$password = $_POST['u_pass'] ?? '';
$password2 = $_POST['u_pass2'] ?? $password;

if ($user_id_raw === '' || $user_name === '' || $user_email === '' || $password === '') {
    flash('error', 'All fields are required (Student ID, username, email and password).');
} elseif (!ctype_digit($user_id_raw) || (int) $user_id_raw <= 0 || strlen($user_id_raw) > 9) {
    flash('error', 'Student ID must be a positive number.');
} elseif (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $user_name)) {
    flash('error', 'Username must be 3-30 characters: letters, numbers, "_" or ".".');
} elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL) || strlen($user_email) > 100) {
    flash('error', 'Please enter a valid email address.');
} elseif (strlen($password) < 8) {
    flash('error', 'Password must be at least 8 characters.');
} elseif ($password !== $password2) {
    flash('error', 'The two passwords do not match.');
} else {
    $ok = db_exec('INSERT INTO Student (user_id, user_name, email, password) VALUES (?,?,?,?)',
        'isss', [(int) $user_id_raw, $user_name, $user_email, password_hash($password, PASSWORD_DEFAULT)]);
    if ($ok !== false) {
        flash('success', 'Account created. You can now sign in with your username and password.');
        redirect('../html/login.php');
    }
    flash('error', db_errno() === 1062
        ? 'That Student ID, username or email is already registered. Try signing in instead.'
        : 'Could not create the account. Please try again.');
}
redirect($back);
