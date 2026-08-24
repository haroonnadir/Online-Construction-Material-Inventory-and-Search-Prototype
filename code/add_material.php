<?php
/**
 * Module 3 - Add Construction Material (for the predefined demo supplier).
 */

require_once __DIR__ . '/includes/functions.php';
require_login();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = validate_material($_POST);

    if (!$errors) {
        $description = input($_POST, 'description');

        $stmt = db()->prepare(
            'INSERT INTO materials
                (supplier_id, material_name, category, unit_price,
                 available_quantity, unit_of_measurement, description, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            DEMO_SUPPLIER_ID,
            input($_POST, 'material_name'),
            input($_POST, 'category'),
            (float) input($_POST, 'unit_price'),
            (float) input($_POST, 'available_quantity'),
            input($_POST, 'unit_of_measurement'),
            $description !== '' ? $description : null,
            $_SESSION['user_id'],
        ]);

        set_flash(
            'success',
            'Material "' . input($_POST, 'material_name') . '" has been added successfully.'
        );
        redirect('materials.php');
    }

    set_flash('error', 'The material could not be saved. Please correct the highlighted fields.');
}

$pageTitle = 'Add Material - Construction Material Management System';
$activeNav = 'add';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <h1>Add Construction Material</h1>
    <p>Fields marked with <span class="req">*</span> are mandatory.</p>
</div>

<div class="supplier-line">
    The material will be added for: <strong><?= e(demo_supplier_name()) ?></strong>
</div>

<div class="card">
    <form id="materialForm" method="post" action="add_material.php" novalidate>
        <div class="form-grid">
            <div class="form-row">
                <label for="material_name">Material Name <span class="req">*</span></label>
                <input type="text" id="material_name" name="material_name" maxlength="100"
                       value="<?= e(old('material_name')) ?>"
                       placeholder="e.g. Ordinary Portland Cement"
                       class="<?= isset($errors['material_name']) ? 'invalid' : '' ?>">
                <span class="error-text" data-error-for="material_name"><?= e($errors['material_name'] ?? '') ?></span>
            </div>

            <div class="form-row">
                <label for="category">Category <span class="req">*</span></label>
                <select id="category" name="category"
                        class="<?= isset($errors['category']) ? 'invalid' : '' ?>">
                    <option value="">-- Select Category --</option>
                    <?php foreach (MATERIAL_CATEGORIES as $category): ?>
                        <option value="<?= e($category) ?>" <?= old('category') === $category ? 'selected' : '' ?>>
                            <?= e($category) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="error-text" data-error-for="category"><?= e($errors['category'] ?? '') ?></span>
            </div>

            <div class="form-row">
                <label for="unit_price">Unit Price (Rs) <span class="req">*</span></label>
                <input type="number" id="unit_price" name="unit_price" step="0.01" min="0.01"
                       value="<?= e(old('unit_price')) ?>"
                       placeholder="e.g. 1250.00"
                       class="<?= isset($errors['unit_price']) ? 'invalid' : '' ?>">
                <span class="error-text" data-error-for="unit_price"><?= e($errors['unit_price'] ?? '') ?></span>
            </div>

            <div class="form-row">
                <label for="available_quantity">Available Quantity <span class="req">*</span></label>
                <input type="number" id="available_quantity" name="available_quantity" step="0.01" min="0"
                       value="<?= e(old('available_quantity')) ?>"
                       placeholder="e.g. 500"
                       class="<?= isset($errors['available_quantity']) ? 'invalid' : '' ?>">
                <span class="error-text" data-error-for="available_quantity"><?= e($errors['available_quantity'] ?? '') ?></span>
            </div>

            <div class="form-row">
                <label for="unit_of_measurement">Unit of Measurement <span class="req">*</span></label>
                <select id="unit_of_measurement" name="unit_of_measurement"
                        class="<?= isset($errors['unit_of_measurement']) ? 'invalid' : '' ?>">
                    <option value="">-- Select Unit --</option>
                    <?php foreach (MEASUREMENT_UNITS as $unit): ?>
                        <option value="<?= e($unit) ?>" <?= old('unit_of_measurement') === $unit ? 'selected' : '' ?>>
                            <?= e($unit) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="error-text" data-error-for="unit_of_measurement"><?= e($errors['unit_of_measurement'] ?? '') ?></span>
            </div>

            <div class="form-row">
                <label for="supplier">Supplier</label>
                <input type="text" id="supplier" value="<?= e(demo_supplier_name()) ?>" readonly>
                <span class="hint">Predefined demo supplier - shared by all users.
                      <a href="supplier.php">Rename it</a> on the Supplier page.</span>
            </div>

            <div class="form-row full">
                <label for="description">Description <span class="hint" style="display:inline">(optional)</span></label>
                <textarea id="description" name="description" maxlength="500"
                          placeholder="Additional details about the material (maximum 500 characters)"
                          class="<?= isset($errors['description']) ? 'invalid' : '' ?>"><?= e(old('description')) ?></textarea>
                <span class="error-text" data-error-for="description"><?= e($errors['description'] ?? '') ?></span>
                <span class="hint" id="descCount">0 / 500 characters</span>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Add Material</button>
            <a class="btn btn-secondary" href="materials.php">Cancel</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
