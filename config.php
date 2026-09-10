<?php
declare(strict_types=1);

// Development-friendly configuration. For production, use environment variables.
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', '1');
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const SITE_NAME = 'Nicolai Clothing';
const BASE_PATH = '';
const CONTACT_EMAIL = 'rnicolaifaith@gmail.com';
const CONTACT_PHONE = '+63 912 345 6789';
const CONTACT_LOCATION = 'Dumaguete City, Negros Oriental, Philippines';

// Database Configuration
const DB_HOST = 'localhost';
const DB_PORT = '3306';
const DB_NAME = 'nicolai_clothing';
const DB_USER = 'root';
const DB_PASS = '';

/**
 * Returns a shared PDO instance connected to the MySQL database.
 */
function get_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // If database does not exist yet, try to create it automatically
            try {
                $rootPdo = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4', DB_USER, DB_PASS, $options);
                $rootPdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $ex) {
                die('Database connection error: ' . htmlspecialchars($ex->getMessage()));
            }
        }
    }
    return $pdo;
}
