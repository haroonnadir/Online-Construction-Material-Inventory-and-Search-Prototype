<?php
/**
 * Module 1 - User Registration.
 */

require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = validate_registration($_POST);

    $fullName = input($_POST, 'full_name');
    $email    = strtolower(input($_POST, 'email'));
    $password = (string) ($_POST['password'] ?? '');
    $phone    = input($_POST, 'phone');

    // The email has to be unique - checked only once the format is valid.
    if (!isset($errors['email'])) {
        $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $stmt->execute([$email]);

        if ((int) $stmt->fetchColumn() > 0) {
            $errors['email'] = 'This email address is already registered. Please log in instead.';
        }
    }

    if (!$errors) {
        $stmt = db()->prepare(
            'INSERT INTO users (full_name, email, password, phone) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $fullName,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $phone,
        ]);

        set_flash('success', 'Registration successful. You can now log in with your email and password.');
        redirect('login.php');
    }
}

$pageTitle = 'Register - Construction Material Management System';
$activeNav = 'register';
require __DIR__ . '/includes/header.php';
?>

<div class="card card-narrow">
    <div class="card-head">
        <h2>Create your account</h2>
        <p>Register to manage construction materials for the demo supplier.</p>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            Please correct the following <?= count($errors) === 1 ? 'error' : 'errors' ?>:
            <ul>
                <?php foreach ($errors as $message): ?>
                    <li><?= e($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form id="registerForm" method="post" action="register.php" novalidate>
        <div class="form-row">
            <label for="full_name">Full Name <span class="req">*</span></label>
            <input type="text" id="full_name" name="full_name" maxlength="20"
                   value="<?= e(old('full_name')) ?>"
                   placeholder="e.g. Ahmed Khan"
                   class="<?= isset($errors['full_name']) ? 'invalid' : '' ?>">
            <span class="error-text" data-error-for="full_name"><?= e($errors['full_name'] ?? '') ?></span>
        </div>

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
                   placeholder="At least 8 characters"
                   class="<?= isset($errors['password']) ? 'invalid' : '' ?>">
            <span class="error-text" data-error-for="password"><?= e($errors['password'] ?? '') ?></span>
        </div>

        <div class="form-row">
            <label for="confirm_password">Confirm Password <span class="req">*</span></label>
            <input type="password" id="confirm_password" name="confirm_password"
                   placeholder="Re-enter your password"
                   class="<?= isset($errors['confirm_password']) ? 'invalid' : '' ?>">
            <span class="error-text" data-error-for="confirm_password"><?= e($errors['confirm_password'] ?? '') ?></span>
        </div>

        <div class="form-row">
            <label for="phone">Phone Number <span class="req">*</span></label>
            <input type="text" id="phone" name="phone" maxlength="11" inputmode="numeric"
                   value="<?= e(old('phone')) ?>"
                   placeholder="e.g. 03001234567"
                   class="<?= isset($errors['phone']) ? 'invalid' : '' ?>">
            <span class="error-text" data-error-for="phone"><?= e($errors['phone'] ?? '') ?></span>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </div>

        <div class="form-footer">
            Already registered? <a href="login.php">Log in here</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
