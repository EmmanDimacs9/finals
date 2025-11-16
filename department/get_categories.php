<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

requireLogin();

header('Content-Type: application/json');

// Only include the default hardcoded categories that map to database tables
// These are the only 6 categories we want to show
$defaultCategories = [
    ['id' => 'desktop', 'name' => 'Desktop', 'description' => 'Desktop computers and workstations'],
    ['id' => 'laptop', 'name' => 'Laptop', 'description' => 'Laptop computers'],
    ['id' => 'printer', 'name' => 'Printer', 'description' => 'Printing devices'],
    ['id' => 'telephone', 'name' => 'Telephone', 'description' => 'Telephone devices'],
    ['id' => 'accesspoint', 'name' => 'Access Point', 'description' => 'Wireless network access points'],
    ['id' => 'switch', 'name' => 'Switch', 'description' => 'Network switching devices']
];

// Use only the default categories - don't merge with database categories
$allCategories = $defaultCategories;

echo json_encode([
    'success' => true,
    'categories' => $allCategories
]);
?>

