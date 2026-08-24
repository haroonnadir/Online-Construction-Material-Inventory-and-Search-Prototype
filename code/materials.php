<?php
/**
 * Module 5 - Construction Material List for the predefined demo supplier.
 */

require_once __DIR__ . '/includes/functions.php';
require_login();

$stmt = db()->prepare(
    'SELECT m.material_name, m.category, m.unit_price, m.available_quantity,
            m.unit_of_measurement, m.description, m.created_at, s.supplier_name
     FROM materials m
     INNER JOIN suppliers s ON s.id = m.supplier_id
     WHERE m.supplier_id = ?
     ORDER BY m.created_at DESC, m.id DESC'
);
$stmt->execute([DEMO_SUPPLIER_ID]);
$materials = $stmt->fetchAll();

$pageTitle = 'Material List - Construction Material Management System';
$activeNav = 'list';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <h1>Construction Material List</h1>
    <p>All construction materials available in the predefined demo supplier inventory.</p>
</div>

<div class="supplier-line">
    Supplier: <strong><?= e(demo_supplier_name()) ?></strong>
    &nbsp;&bull;&nbsp; <?= count($materials) ?> <?= count($materials) === 1 ? 'material' : 'materials' ?> listed
</div>

<div class="card">
    <?php if ($materials): ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Material Name</th>
                        <th>Category</th>
                        <th>Unit Price</th>
                        <th>Available Quantity</th>
                        <th>Unit of Measurement</th>
                        <th>Demo Supplier Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materials as $index => $row): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td class="name">
                                <?= e($row['material_name']) ?>
                                <?php if (!empty($row['description'])): ?>
                                    <span class="desc"><?= e($row['description']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge"><?= e($row['category']) ?></span></td>
                            <td class="num">Rs <?= number_format((float) $row['unit_price'], 2) ?></td>
                            <td class="num"><?= number_format((float) $row['available_quantity'], 2) ?></td>
                            <td><?= e($row['unit_of_measurement']) ?></td>
                            <td><?= e($row['supplier_name']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty">
            <strong>No materials added yet</strong>
            The demo supplier inventory is empty.
            <p><a class="btn btn-primary" href="add_material.php">Add the first material</a></p>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
