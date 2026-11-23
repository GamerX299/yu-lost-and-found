<?php
/**
 * student_login.php - checks username + password and starts the session.
 * Receives the "Sign in" form from login.php.
 */
require_once __DIR__ . '/config.php';
require_post('../html/login.php');

$user_name = post('u_name');
$password = $_POST['u_pass'] ?? '';

if ($user_name === '' || $password === '') {
    flash('error', 'Username and password are required.');
    redirect('../html/login.php');
}

$user = db_select_one('SELECT user_id, user_name, email, password, role FROM Student WHERE user_name = ? LIMIT 1', 's', [$user_name]);

if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);                       // protects against session fixation
    $_SESSION['user_id']    = (int) $user['user_id'];
    $_SESSION['user_name']  = $user['user_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['role']       = $user['role'];
    flash('success', 'Welcome back, ' . $user['user_name'] . '!');
    redirect('../html/dashboard.php');
}

flash('error', 'Invalid username or password. Please check your credentials and try again.');
redirect('../html/login.php');
