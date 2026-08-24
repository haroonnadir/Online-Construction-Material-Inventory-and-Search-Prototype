<?php
/**
 * Database configuration and connection.
 *
 * On the very first run this file also creates the database, the tables and
 * the predefined demo supplier, so the prototype works straight after being
 * dropped into htdocs. The same statements live in sql/schema.sql if you
 * prefer to import them through phpMyAdmin.
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'construction_db');

/** Primary key of the predefined demo supplier. */
define('DEMO_SUPPLIER_ID', 1);

/**
 * Returns a shared PDO connection.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // Database not created yet - build it once, then connect again.
        install_database($options);
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    ensure_schema($pdo);

    return $pdo;
}

/**
 * Creates the database itself (runs only when the connection above fails).
 */
function install_database(array $options): void
{
    try {
        $server = new PDO('mysql:host=' . DB_HOST, DB_USER, DB_PASS, $options);
        $server->exec(
            'CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '`
             DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci'
        );
    } catch (PDOException $e) {
        die(
            '<h2 style="font-family:sans-serif">Database connection failed</h2>' .
            '<p style="font-family:sans-serif">Start Apache and MySQL in the XAMPP ' .
            'Control Panel, then reload this page.</p>' .
            '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>'
        );
    }
}

/**
 * Creates the tables and seeds the demo supplier. Guarded by a marker file so
 * the DDL runs once instead of on every request.
 */
function ensure_schema(PDO $pdo): void
{
    $marker = __DIR__ . '/../storage/.installed';
    if (is_file($marker)) {
        return;
    }

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `users` (
            `id`         INT AUTO_INCREMENT PRIMARY KEY,
            `full_name`  VARCHAR(50)  NOT NULL,
            `email`      VARCHAR(100) NOT NULL UNIQUE,
            `password`   VARCHAR(255) NOT NULL,
            `phone`      VARCHAR(11)  NOT NULL,
            `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `suppliers` (
            `id`            INT AUTO_INCREMENT PRIMARY KEY,
            `supplier_name` VARCHAR(100) NOT NULL,
            `contact_email` VARCHAR(100) DEFAULT NULL,
            `phone`         VARCHAR(11)  DEFAULT NULL,
            `address`       VARCHAR(255) DEFAULT NULL,
            `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `materials` (
            `id`                  INT AUTO_INCREMENT PRIMARY KEY,
            `supplier_id`         INT           NOT NULL,
            `material_name`       VARCHAR(100)  NOT NULL,
            `category`            VARCHAR(50)   NOT NULL,
            `unit_price`          DECIMAL(12,2) NOT NULL,
            `available_quantity`  DECIMAL(12,2) NOT NULL DEFAULT 0,
            `unit_of_measurement` VARCHAR(30)   NOT NULL,
            `description`         VARCHAR(500)  DEFAULT NULL,
            `created_by`          INT           DEFAULT NULL,
            `created_at`          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_material_name` (`material_name`),
            CONSTRAINT `fk_material_supplier` FOREIGN KEY (`supplier_id`)
                REFERENCES `suppliers`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_material_user` FOREIGN KEY (`created_by`)
                REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
    );

    // Predefined demo supplier.
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM `suppliers` WHERE `id` = ?');
    $stmt->execute([DEMO_SUPPLIER_ID]);

    if ((int) $stmt->fetchColumn() === 0) {
        $pdo->prepare(
            'INSERT INTO `suppliers` (`id`, `supplier_name`, `contact_email`, `phone`, `address`)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([
            DEMO_SUPPLIER_ID,
            'Demo Construction Suppliers Pvt. Ltd.',
            'sales@demosuppliers.com',
            '03001234567',
            'Plot 21, Industrial Area, Lahore',
        ]);
    }

    @file_put_contents($marker, date('Y-m-d H:i:s'));
}
