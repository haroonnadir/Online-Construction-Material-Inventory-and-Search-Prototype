<?php
/**
 * Demo supplier settings - rename the predefined supplier.
 *
 * The system keeps a single supplier row that every registered user works with,
 * so a name saved here replaces the old one for all users, not just this one.
 */

require_once __DIR__ . '/includes/functions.php';
require_login();

$errors   = [];
$supplier = demo_supplier();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = validate_supplier_name($_POST);

    if (!$errors) {
        $newName = input($_POST, 'supplier_name');

        if ($newName === (string) $supplier['supplier_name']) {
            set_flash('info', 'The supplier name was left unchanged.');
        } else {
            rename_demo_supplier($newName);
            set_flash(
                'success',
                'Supplier name changed to "' . $newName . '". Every user now sees the new name.'
            );
        }

        redirect('supplier.php');
    }

    set_flash('error', 'The supplier name could not be saved. Please correct the highlighted field.');
}

$pageTitle = 'Demo Supplier - Construction Material Management System';
$activeNav = 'supplier';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <h1>Demo Supplier</h1>
    <p>Rename the predefined supplier that the whole system shares.</p>
</div>

<div class="supplier-line">
    Current supplier name: <strong><?= e(demo_supplier_name()) ?></strong>
</div>

<div class="card">
    <div class="card-head">
        <h2>Change Supplier Name</h2>
        <p>Fields marked with <span class="req">*</span> are mandatory.</p>
    </div>

    <form id="supplierForm" method="post" action="supplier.php" novalidate>
        <div class="form-row">
            <label for="supplier_name">Supplier Name <span class="req">*</span></label>
            <input type="text" id="supplier_name" name="supplier_name" maxlength="100"
                   value="<?= e(old('supplier_name', (string) $supplier['supplier_name'])) ?>"
                   placeholder="e.g. Al-Madina Building Materials"
                   class="<?= isset($errors['supplier_name']) ? 'invalid' : '' ?>">
            <span class="error-text" data-error-for="supplier_name"><?= e($errors['supplier_name'] ?? '') ?></span>
            <span class="hint">
                One supplier record serves every registered user, so this name is shared:
                the change shows up for all users and on every material already saved.
            </span>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Supplier Name</button>
            <a class="btn btn-secondary" href="dashboard.php">Cancel</a>
        </div>
    </form>

    <div class="form-footer">
        Other demo supplier details (fixed in this prototype):
        <?= e((string) ($supplier['contact_email'] ?? '-')) ?> &middot;
        <?= e((string) ($supplier['phone'] ?? '-')) ?> &middot;
        <?= e((string) ($supplier['address'] ?? '-')) ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
