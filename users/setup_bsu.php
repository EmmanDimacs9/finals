<?php
// Quick BSU setup script
// Run this file to automatically create the BSU folder structure

echo "<h1>BSU Folder Setup</h1>";

// Define the BSU path
$bsu_path = $_SERVER['DOCUMENT_ROOT'] . '/BSU/users/';

echo "<p>Setting up BSU folder at: $bsu_path</p>";

// Create BSU directory if it doesn't exist
if (!is_dir($bsu_path)) {
    if (mkdir($bsu_path, 0755, true)) {
        echo "<p style='color: green;'>✓ Created BSU directory</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create BSU directory</p>";
        exit();
    }
} else {
    echo "<p style='color: blue;'>ℹ BSU directory already exists</p>";
}

// List of files to create redirects for
$files = [
    'index.php' => 'Main dashboard',
    'login.php' => 'Login page',
    'register.php' => 'Registration page',
    'tasks.php' => 'Task management',
    'qr.php' => 'QR scanner',
    'history.php' => 'History page',
    'profile.php' => 'Profile page',
    'logout.php' => 'Logout functionality'
];

$success_count = 0;

foreach ($files as $file => $description) {
    $redirect_content = "<?php\n// Redirect to users folder\n// File: $file - $description\ninclude \$_SERVER['DOCUMENT_ROOT'] . '/users/$file';\n?>";
    
    if (file_put_contents($bsu_path . $file, $redirect_content)) {
        echo "<p style='color: green;'>✓ Created $file ($description)</p>";
        $success_count++;
    } else {
        echo "<p style='color: red;'>✗ Failed to create $file</p>";
    }
}

echo "<hr>";
echo "<h2>Setup Complete!</h2>";
echo "<p>Successfully created $success_count redirect files.</p>";

echo "<h3>You can now access the system at:</h3>";
echo "<ul>";
echo "<li><a href='/BSU/users/' target='_blank'>http://localhost/BSU/users/</a> - Main dashboard</li>";
echo "<li><a href='/BSU/users/login.php' target='_blank'>http://localhost/BSU/users/login.php</a> - Login page</li>";
echo "<li><a href='/BSU/users/register.php' target='_blank'>http://localhost/BSU/users/register.php</a> - Registration page</li>";
echo "<li><a href='/BSU/users/tasks.php' target='_blank'>http://localhost/BSU/users/tasks.php</a> - Task management</li>";
echo "<li><a href='/BSU/users/qr.php' target='_blank'>http://localhost/BSU/users/qr.php</a> - QR scanner</li>";
echo "<li><a href='/BSU/users/history.php' target='_blank'>http://localhost/BSU/users/history.php</a> - History</li>";
echo "<li><a href='/BSU/users/profile.php' target='_blank'>http://localhost/BSU/users/profile.php</a> - Profile</li>";
echo "</ul>";

echo "<h3>Notes:</h3>";
echo "<ul>";
echo "<li>All files in the BSU folder are redirects to the main users folder</li>";
echo "<li>Database and assets remain in the original users folder</li>";
echo "<li>Both URLs will work: /users/ and /BSU/users/</li>";
echo "<li>Session management works across both paths</li>";
echo "</ul>";

echo "<p><strong>Default admin account:</strong></p>";
echo "<ul>";
echo "<li>Email: admin@system.com</li>";
echo "<li>Password: password</li>";
echo "</ul>";

echo "<p style='color: orange;'><strong>Important:</strong> Make sure you have imported the database.sql file into your MySQL database before testing!</p>";
?> 