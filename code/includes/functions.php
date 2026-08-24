<?php
/**
 * Shared helpers: session bootstrap, escaping, flash messages, form validation.
 */

require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Dropdown options required by the specification. */
const MATERIAL_CATEGORIES = [
    'Cement', 'Bricks', 'Sand', 'Steel', 'Gravel',
    'Concrete Blocks', 'Tiles', 'Paint', 'Wood', 'Glass', 'Pipes',
];

const MEASUREMENT_UNITS = [
    'Bags', 'Pieces', 'Tons', 'Kilograms',
    'Cubic Feet', 'Cubic Meters', 'Liters', 'Square Feet', 'Meters',
];

/** Escapes output for safe HTML rendering. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Trims a value coming from $_POST / $_GET. */
function input(array $source, string $key): string
{
    return trim((string) ($source[$key] ?? ''));
}

/** Redirects and stops execution. */
function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

/** Stores a one-time message shown on the next page load. */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Returns and clears the pending flash message. */
function get_flash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

/** True when a user is signed in. */
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

/** Name of the signed-in user, for the navigation bar. */
function current_user_name(): string
{
    return (string) ($_SESSION['user_name'] ?? '');
}

/** Blocks guests from the material management module. */
function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to access the material management module.');
        redirect('login.php');
    }
}

/** Keeps a submitted value in the field after a failed validation pass. */
function old(string $key, string $default = ''): string
{
    return isset($_POST[$key]) ? (string) $_POST[$key] : $default;
}

/* ------------------------------------------------------------------
 |  Server-side validation - mirrors assets/js/validation.js
 * ------------------------------------------------------------------ */

/**
 * Validates the registration form.
 *
 * @return array<string,string> field => error message
 */
function validate_registration(array $data): array
{
    $errors = [];

    $fullName = trim($data['full_name'] ?? '');
    $email    = trim($data['email'] ?? '');
    $password = (string) ($data['password'] ?? '');
    $confirm  = (string) ($data['confirm_password'] ?? '');
    $phone    = trim($data['phone'] ?? '');

    if ($fullName === '') {
        $errors['full_name'] = 'Full Name is required.';
    } elseif (!preg_match('/^[A-Za-z ]{3,20}$/', $fullName)) {
        $errors['full_name'] = 'Full Name must contain only alphabets and spaces (3-20 characters).';
    }

    if ($email === '') {
        $errors['email'] = 'Email Address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address (example: user@domain.com).';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must contain at least 8 characters.';
    }

    if ($confirm === '') {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password !== $confirm) {
        $errors['confirm_password'] = 'Confirm Password does not match the Password.';
    }

    if ($phone === '') {
        $errors['phone'] = 'Phone Number is required.';
    } elseif (!preg_match('/^[0-9]{1,11}$/', $phone)) {
        $errors['phone'] = 'Phone Number must contain only digits (maximum 11 digits).';
    }

    return $errors;
}

/**
 * Validates the login form.
 *
 * @return array<string,string> field => error message
 */
function validate_login(array $data): array
{
    $errors = [];

    $email    = trim($data['email'] ?? '');
    $password = (string) ($data['password'] ?? '');

    if ($email === '') {
        $errors['email'] = 'Email Address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    }

    return $errors;
}

/**
 * Validates the add-material form.
 *
 * @return array<string,string> field => error message
 */
function validate_material(array $data): array
{
    $errors = [];

    $name        = trim($data['material_name'] ?? '');
    $category    = trim($data['category'] ?? '');
    $price       = trim($data['unit_price'] ?? '');
    $quantity    = trim($data['available_quantity'] ?? '');
    $unit        = trim($data['unit_of_measurement'] ?? '');
    $description = trim($data['description'] ?? '');

    if ($name === '') {
        $errors['material_name'] = 'Material Name is required.';
    } elseif (mb_strlen($name) > 100) {
        $errors['material_name'] = 'Material Name cannot exceed 100 characters.';
    }

    if ($category === '') {
        $errors['category'] = 'Please select a category.';
    } elseif (!in_array($category, MATERIAL_CATEGORIES, true)) {
        $errors['category'] = 'Please select a valid category from the list.';
    }

    if ($price === '') {
        $errors['unit_price'] = 'Unit Price is required.';
    } elseif (!is_numeric($price)) {
        $errors['unit_price'] = 'Unit Price must be a number.';
    } elseif ((float) $price <= 0) {
        $errors['unit_price'] = 'Unit Price must be greater than zero.';
    }

    if ($quantity === '') {
        $errors['available_quantity'] = 'Available Quantity is required.';
    } elseif (!is_numeric($quantity)) {
        $errors['available_quantity'] = 'Available Quantity must be a number.';
    } elseif ((float) $quantity < 0) {
        $errors['available_quantity'] = 'Available Quantity cannot be negative.';
    }

    if ($unit === '') {
        $errors['unit_of_measurement'] = 'Please select a unit of measurement.';
    } elseif (!in_array($unit, MEASUREMENT_UNITS, true)) {
        $errors['unit_of_measurement'] = 'Please select a valid unit of measurement.';
    }

    if (mb_strlen($description) > 500) {
        $errors['description'] = 'Description should not exceed 500 characters.';
    }

    return $errors;
}

/**
 * Validates the supplier rename form.
 *
 * @return array<string,string> field => error message
 */
function validate_supplier_name(array $data): array
{
    $errors = [];

    $name = trim($data['supplier_name'] ?? '');

    if ($name === '') {
        $errors['supplier_name'] = 'Supplier Name is required.';
    } elseif (mb_strlen($name) < 3) {
        $errors['supplier_name'] = 'Supplier Name must contain at least 3 characters.';
    } elseif (mb_strlen($name) > 100) {
        $errors['supplier_name'] = 'Supplier Name cannot exceed 100 characters.';
    } elseif (!preg_match('~^[A-Za-z0-9 .,&()/\'-]+$~u', $name)) {
        $errors['supplier_name'] = 'Supplier Name may contain letters, digits, spaces and . , & ( ) / - \' only.';
    }

    return $errors;
}

/**
 * The predefined demo supplier. A single row serves the whole system, so every
 * registered user reads - and renames - the same record.
 */
function demo_supplier(bool $refresh = false): array
{
    static $supplier = null;

    if ($supplier === null || $refresh) {
        $stmt = db()->prepare(
            'SELECT id, supplier_name, contact_email, phone, address
             FROM suppliers WHERE id = ?'
        );
        $stmt->execute([DEMO_SUPPLIER_ID]);
        $supplier = $stmt->fetch() ?: ['supplier_name' => 'Demo Supplier'];
    }

    return $supplier;
}

/** Name of the predefined demo supplier, used across the material pages. */
function demo_supplier_name(bool $refresh = false): string
{
    return (string) (demo_supplier($refresh)['supplier_name'] ?? 'Demo Supplier');
}

/**
 * Renames the shared demo supplier. The name lives in one database row, so the
 * new value is what every user of the system sees from their next page load.
 */
function rename_demo_supplier(string $name): void
{
    $stmt = db()->prepare('UPDATE suppliers SET supplier_name = ? WHERE id = ?');
    $stmt->execute([$name, DEMO_SUPPLIER_ID]);

    demo_supplier(true);
}
