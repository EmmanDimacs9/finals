<?php
// BSU Index File
// This file should be placed in the BSU folder to redirect to the users system

// Check if user is trying to access a specific file
$request_uri = $_SERVER['REQUEST_URI'];
$path_parts = explode('/', trim($request_uri, '/'));

// If accessing /BSU/users/login.php, redirect to /users/login.php
if (isset($path_parts[1]) && $path_parts[1] === 'users' && isset($path_parts[2])) {
    $file_name = $path_parts[2];
    $users_path = $_SERVER['DOCUMENT_ROOT'] . '/users/' . $file_name;
    
    if (file_exists($users_path)) {
        // Include the file from users folder
        include $users_path;
        exit();
    }
}

// Default redirect to users folder
header('Location: /users/');
exit();
?> 