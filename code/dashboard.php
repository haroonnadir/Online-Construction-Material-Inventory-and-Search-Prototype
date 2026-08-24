<?php
/**
 * Landing page for authenticated users.
 */

require_once __DIR__ . '/includes/functions.php';
require_login();

$stmt = db()->prepare(
    'SELECT COUNT(*)                      AS total_materials,
            COUNT(DISTINCT category)      AS total_categories,
            COALESCE(SUM(unit_price * available_quantity), 0) AS stock_value
     FROM materials
     WHERE supplier_id = ?'
);
$stmt->execute([DEMO_SUPPLIER_ID]);
$stats = $stmt->fetch();

$pageTitle = 'Dashboard - Construction Material Management System';
$activeNav = 'dashboard';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <h1>Dashboard</h1>
    <p>Welcome, <?= e(current_user_name()) ?>. Here is an overview of the demo supplier inventory.</p>
</div>

<div class="supplier-line">
    Predefined demo supplier: <strong><?= e(demo_supplier_name()) ?></strong>
    &mdash; one shared record, so its name is the same for every registered user.
    <a href="supplier.php">Change name</a>
</div>

<div class="stats">
    <div class="stat">
        <div class="label">Total Materials</div>
        <div class="value"><?= (int) $stats['total_materials'] ?></div>
    </div>
    <div class="stat">
        <div class="label">Categories in Stock</div>
        <div class="value"><?= (int) $stats['total_categories'] ?></div>
    </div>
    <div class="stat">
        <div class="label">Total Stock Value</div>
        <div class="value">Rs <?= number_format((float) $stats['stock_value'], 2) ?></div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h2>Material Management</h2>
        <p>Choose an action below.</p>
    </div>

    <div class="actions">
        <a class="action-tile" href="add_material.php">
            <strong>Add Construction Material</strong>
            <span>Register a new material for the demo supplier.</span>
        </a>
        <a class="action-tile" href="search_material.php">
            <strong>Search Material</strong>
            <span>Find materials by name using partial keywords.</span>
        </a>
        <a class="action-tile" href="materials.php">
            <strong>Construction Material List</strong>
            <span>View every material available in the inventory.</span>
        </a>
        <a class="action-tile" href="supplier.php">
            <strong>Change Supplier Name</strong>
            <span>Rename the demo supplier for all users of the system.</span>
        </a>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
