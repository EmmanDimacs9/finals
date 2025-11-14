<?php
// Redirect script for BSU folder structure
// This file should be placed in the BSU folder to redirect to the users folder

// Get the current path
$current_path = $_SERVER['REQUEST_URI'];
$requested_file = $_SERVER['REQUEST_FILENAME'];

// Check if we're accessing from BSU folder
if (strpos($current_path, '/BSU/users/') !== false) {
    // Extract the file path after /BSU/users/
    $file_path = str_replace('/BSU/users/', '/users/', $current_path);
    
    // Check if the file exists in the users folder
    $users_file_path = $_SERVER['DOCUMENT_ROOT'] . '/users/' . basename($file_path);
    
    if (file_exists($users_file_path)) {
        // Include the file from users folder
        include $users_file_path;
        exit();
    } else {
        // File doesn't exist, redirect to users folder
        header('Location: /users/');
        exit();
    }
}

// If not in BSU folder, redirect to users folder
header('Location: /users/');
exit();
?> 