<?php
/**
 * login.php - sign in and create-account forms.
 * The forms post to sql/student_login.php and sql/regis_action.php.
 */
require_once __DIR__ . '/../sql/config.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$page_title = 'Login / Register';
$active = '';
include __DIR__ . '/inc/header.php';
?>
<main>
    <section class="page-hero">
        <div class="container"><h1>Welcome to Lost &amp; Found</h1><p>Sign in or create an account to manage your reports.</p></div>
    </section>
    <section class="container content">
        <div class="auth-wrap">
            <div class="card auth-card">
                <h1>Sign in</h1>
                <p>Use your account to continue.</p>
                <form class="auth-form" action="../sql/student_login.php" method="post">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input class="field" type="text" id="username" name="u_name" required autocomplete="username" placeholder="Enter your username">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input class="field" type="password" id="password" name="u_pass" required autocomplete="current-password" placeholder="Enter your password">
                    </div>
                    <button class="btn" type="submit">Log in →</button>
                </form>
                <div class="auth-switch">New to the platform? <a class="text-link" href="#register">Create an account</a></div>

                <hr class="divider">

                <div id="register">
                    <h2 class="auth-subtitle">Create an account</h2>
                    <p class="hint">Register with your details.</p>
                    <form class="auth-form" action="../sql/regis_action.php" method="post" data-validate="register">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label for="student-id">User ID</label>
                            <input class="field" type="number" id="student-id" name="u_id" required min="1" step="1" placeholder="Enter your ID number">
                        </div>
                        <div class="form-group">
                            <label for="name">Username</label>
                            <input class="field" id="name" name="u_name" required minlength="3" maxlength="30" pattern="[A-Za-z0-9_.]+" autocomplete="username" placeholder="Letters, numbers, _ and . only">
                        </div>
                        <div class="form-group">
                            <label for="reg-email">Email</label>
                            <input class="field" type="email" id="reg-email" name="u_email" required placeholder="name@example.com">
                        </div>
                        <div class="form-group">
                            <label for="reg-password">Create password</label>
                            <input class="field" type="password" id="reg-password" name="u_pass" required minlength="8" autocomplete="new-password" placeholder="At least 8 characters">
                        </div>
                        <div class="form-group">
                            <label for="reg-password2">Confirm password</label>
                            <input class="field" type="password" id="reg-password2" name="u_pass2" required minlength="8" autocomplete="new-password" placeholder="Repeat the password">
                        </div>
                        <button class="btn btn-outline" type="submit">Create account</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>
<?php include __DIR__ . '/inc/footer.php'; ?>
