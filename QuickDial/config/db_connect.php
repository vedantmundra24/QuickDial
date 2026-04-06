<?php
/**
 * QuickDial - Database Connection
 * File: config/db_connect.php
 * Description: PDO database connection configuration
 */

// ── Database credentials ──────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');         // Change to your MySQL username
define('DB_PASS', '');             // Change to your MySQL password
define('DB_NAME', 'quickdial_db');
define('DB_CHARSET', 'utf8mb4');

// ── DSN ───────────────────────────────────────────────────────
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

// ── PDO options ───────────────────────────────────────────────
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Return assoc arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                   // Use real prepared statements
];

// ── Create connection ─────────────────────────────────────────
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // In production, log this instead of echoing
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]));
}
