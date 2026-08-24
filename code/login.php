<?php
/**
 * Module 2 - User Login.
 */

require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors      = [];
$loginFailed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = validate_login($_POST);

    if (!$errors) {
        $email    = strtolower(input($_POST, 'email'));
        $password = (string) ($_POST['password'] ?? '');

        $stmt = db()->prepare('SELECT id, full_name, email, password FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            $_SESSION['user_id']    = (int) $user['id'];
            $_SESSION['user_name']  = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];

            set_flash('success', 'Welcome back, ' . $user['full_name'] . '.');
            redirect('dashboard.php');
        }

        // Same message for a wrong email and a wrong password.
        $loginFailed = true;
    }
}

$pageTitle = 'Login - Construction Material Management System';
$activeNav = 'login';
require __DIR__ . '/includes/header.php';
?>

<div class="card card-narrow">
    <div class="card-head">
        <h2>Log in to your account</h2>
        <p>Only authenticated users can access the material management module.</p>
    </div>

    <?php if ($loginFailed): ?>
        <div class="alert alert-error">
            Invalid email address or password. Please try again.
        </div>
    <?php elseif ($errors): ?>
        <div class="alert alert-error">
            Please correct the following <?= count($errors) === 1 ? 'error' : 'errors' ?>:
            <ul>
                <?php foreach ($errors as $message): ?>
                    <li><?= e($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form id="loginForm" method="post" action="login.php" novalidate>
        <div class="form-row">
            <label for="email">Email Address <span class="req">*</span></label>
            <input type="email" id="email" name="email" maxlength="100"
                   value="<?= e(old('email')) ?>"
                   placeholder="e.g. ahmed@example.com"
                   class="<?= isset($errors['email']) ? 'invalid' : '' ?>">
            <span class="error-text" data-error-for="email"><?= e($errors['email'] ?? '') ?></span>
        </div>

        <div class="form-row">
            <label for="password">Password <span class="req">*</span></label>
            <input type="password" id="password" name="password"
                   placeholder="Enter your password"
                   class="<?= isset($errors['password']) ? 'invalid' : '' ?>">
            <span class="error-text" data-error-for="password"><?= e($errors['password'] ?? '') ?></span>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </div>

        <div class="form-footer">
            New here? <a href="register.php">Create an account</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
