<?php
// Path configuration for BSU folder access

// Define the base paths
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/users/');
define('BSU_PATH', $_SERVER['DOCUMENT_ROOT'] . '/BSU/');

// Check if we're accessing from BSU folder
function isBSUAccess() {
    return strpos($_SERVER['REQUEST_URI'], '/BSU/') !== false;
}

// Get the correct file path
function getFilePath($file) {
    if (isBSUAccess()) {
        // If accessing from BSU, use the users folder
        return BASE_PATH . $file;
    } else {
        // Normal access
        return $file;
    }
}

// Include a file with proper path handling
function includeFile($file) {
    $file_path = getFilePath($file);
    if (file_exists($file_path)) {
        include $file_path;
    } else {
        die("File not found: $file_path");
    }
}

// Get the correct URL for assets
function getAssetUrl($path) {
    if (isBSUAccess()) {
        return '/users/' . $path;
    } else {
        return '/' . $path;
    }
}

// Get the correct URL for pages
function getPageUrl($page) {
    if (isBSUAccess()) {
        return '/BSU/users/' . $page;
    } else {
        return '/' . $page;
    }
}
?> 