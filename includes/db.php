<?php
// Set PHP timezone to Philippines
date_default_timezone_set('Asia/Manila');

// Database connection settings
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'bss';

// Try to connect directly to the target database first
$mysqli = @new mysqli($host, $user, $password, $dbname);

// If database is unknown (errno 1049), create it then reconnect
if ($mysqli instanceof mysqli && $mysqli->connect_errno === 1049) {
    $bootstrap = @new mysqli($host, $user, $password);
    if ($bootstrap->connect_error) {
        die('Database bootstrap connection failed: ' . $bootstrap->connect_error);
    }
    $bootstrap->query("CREATE DATABASE IF NOT EXISTS `" . $bootstrap->real_escape_string($dbname) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $bootstrap->close();
    $mysqli = new mysqli($host, $user, $password, $dbname);
}

if ($mysqli->connect_error) {
    die('Database connection failed: ' . $mysqli->connect_error);
}

// Set timezone to match PHP
$mysqli->query("SET time_zone = '+08:00'"); // Philippines timezone

// Expose a single $conn variable as the rest of the app expects
$conn = $mysqli; 