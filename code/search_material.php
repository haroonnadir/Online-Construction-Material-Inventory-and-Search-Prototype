<?php
/**
 * Module 4 - Search Construction Material by name.
 *
 * The search is a partial, case-insensitive match: the term is wrapped in
 * LIKE wildcards and the utf8mb4_general_ci collation ignores letter case.
 */

require_once __DIR__ . '/includes/functions.php';
require_login();

$term      = input($_GET, 'q');
$searched  = isset($_GET['q']);
$error     = '';
$results   = [];

if ($searched) {
    if ($term === '') {
        $error = 'Please enter a material name to search.';
    } else {
        // Escape the LIKE wildcards so they are treated as plain characters.
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $term);

        $stmt = db()->prepare(
            'SELECT m.material_name, m.category, m.unit_price, m.available_quantity,
                    m.unit_of_measurement, m.description, s.supplier_name
             FROM materials m
             INNER JOIN suppliers s ON s.id = m.supplier_id
             WHERE m.supplier_id = ?
               AND m.material_name LIKE ? ESCAPE ?
             ORDER BY m.material_name ASC'
        );
        $stmt->execute([DEMO_SUPPLIER_ID, '%' . $escaped . '%', '\\']);
        $results = $stmt->fetchAll();
    }
}

/** Wraps the matched part of the name in <mark> for the result table. */
function highlight(string $text, string $term): string
{
    if ($term === '') {
        return e($text);
    }

    return preg_replace(
        '/(' . preg_quote($term, '/') . ')/iu',
        '<mark>$1</mark>',
        e($text)
    );
}

$pageTitle = 'Search Material - Construction Material Management System';
$activeNav = 'search';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <h1>Search Construction Material</h1>
    <p>Search by material name. Partial keywords work and the search is not case-sensitive.</p>
</div>

<div class="card">
    <form id="searchForm" method="get" action="search_material.php" novalidate>
        <div class="search-bar">
            <div class="grow">
                <label for="q">Material Name <span class="req">*</span></label>
                <input type="text" id="q" name="q" maxlength="100"
                       value="<?= e($term) ?>"
                       placeholder="e.g. cem  (matches Cement, Cement Blocks ...)"
                       class="<?= $error ? 'invalid' : '' ?>" autofocus>
                <span class="error-text" data-error-for="q"><?= e($error) ?></span>
            </div>
            <div style="padding-top:25px">
                <button type="submit" class="btn btn-primary">Search</button>
                <a class="btn btn-secondary" href="search_material.php">Reset</a>
            </div>
        </div>
    </form>
</div>

<?php if ($searched && !$error): ?>
    <div class="card">
        <div class="card-head">
            <h2>Search Results</h2>
            <p>
                <?= count($results) ?> <?= count($results) === 1 ? 'material' : 'materials' ?>
                matching &ldquo;<?= e($term) ?>&rdquo;
            </p>
        </div>

        <?php if ($results): ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Material Name</th>
                            <th>Category</th>
                            <th>Unit Price</th>
                            <th>Available Quantity</th>
                            <th>Unit</th>
                            <th>Supplier</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $row): ?>
                            <tr>
                                <td class="name">
                                    <?= highlight($row['material_name'], $term) ?>
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
                <strong>No matching material found</strong>
                No material name contains &ldquo;<?= e($term) ?>&rdquo;. Try a different keyword.
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
