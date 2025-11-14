<?php
// Database connection settings
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'bss';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
} 