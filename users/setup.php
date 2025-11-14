<?php
// Setup script for User Management System
// Run this file in your browser to configure the system

session_start();

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// Step 1: Check requirements
if ($step === 1) {
    $requirements = [
        'PHP Version (>= 7.4)' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
        'Session Extension' => extension_loaded('session'),
        'Config Directory Writable' => is_writable('config') || is_writable('.'),
        'Assets Directory Writable' => is_writable('assets') || is_writable('.'),
    ];
    
    $allPassed = true;
    foreach ($requirements as $requirement => $passed) {
        if (!$passed) $allPassed = false;
    }
    
    if ($allPassed) {
        $success = 'All requirements are met!';
    } else {
        $error = 'Some requirements are not met. Please fix them before proceeding.';
    }
}

// Step 2: Database configuration
if ($step === 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['host'];
    $dbname = $_POST['dbname'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    try {
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
        $pdo->exec("USE `$dbname`");
        
        // Import database schema
        $sql = file_get_contents('database.sql');
        $pdo->exec($sql);
        
        // Update config file
        $configContent = "<?php
// Database configuration
define('DB_HOST', '$host');
define('DB_NAME', '$dbname');
define('DB_USER', '$username');
define('DB_PASS', '$password');

// Create database connection
function getDBConnection() {
    try {
        \$pdo = new PDO(
            \"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=utf8\",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return \$pdo;
    } catch (PDOException \$e) {
        die(\"Connection failed: \" . \$e->getMessage());
    }
}
?>";
        
        if (file_put_contents('config/database.php', $configContent)) {
            $success = 'Database configured successfully!';
        } else {
            $error = 'Could not write to config file. Please check permissions.';
        }
        
    } catch (PDOException $e) {
        $error = 'Database connection failed: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - User Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-users"></i> User Management System - Setup
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="card" style="max-width: 600px; margin: 2rem auto;">
            <div class="card-header">
                <h1 class="card-title">System Setup</h1>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($step === 1): ?>
                <h2>Step 1: System Requirements</h2>
                <div style="margin: 1rem 0;">
                    <?php foreach ($requirements as $requirement => $passed): ?>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--light-gray);">
                            <span><?php echo $requirement; ?></span>
                            <span style="color: <?php echo $passed ? 'var(--success)' : 'var(--primary-red)'; ?>">
                                <i class="fas fa-<?php echo $passed ? 'check' : 'times'; ?>"></i>
                                <?php echo $passed ? 'Pass' : 'Fail'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($allPassed): ?>
                    <div style="text-align: center; margin: 2rem 0;">
                        <a href="?step=2" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i> Continue to Database Setup
                        </a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <h3>Please fix the following issues:</h3>
                        <ul>
                            <li>Ensure PHP 7.4 or higher is installed</li>
                            <li>Enable PDO and PDO MySQL extensions</li>
                            <li>Enable session extension</li>
                            <li>Set proper file permissions for config and assets directories</li>
                        </ul>
                    </div>
                <?php endif; ?>
                
            <?php elseif ($step === 2): ?>
                <h2>Step 2: Database Configuration</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="host" class="form-label">Database Host</label>
                        <input type="text" id="host" name="host" class="form-control" value="localhost" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="dbname" class="form-label">Database Name</label>
                        <input type="text" id="dbname" name="dbname" class="form-control" value="user_management_system" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="username" class="form-label">Database Username</label>
                        <input type="text" id="username" name="username" class="form-control" value="root" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Database Password</label>
                        <input type="password" id="password" name="password" class="form-control">
                        <small style="color: var(--gray);">Leave empty if no password is set</small>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-database"></i> Configure Database
                        </button>
                    </div>
                </form>
                
            <?php elseif ($step === 3): ?>
                <h2>Step 3: Setup Complete!</h2>
                <div style="text-align: center; padding: 2rem;">
                    <i class="fas fa-check-circle" style="font-size: 4rem; color: var(--success); margin-bottom: 1rem;"></i>
                    <h3>Congratulations!</h3>
                    <p>Your User Management System has been successfully configured.</p>
                    
                    <div style="margin: 2rem 0;">
                        <h4>Default Admin Account:</h4>
                        <p><strong>Email:</strong> admin@system.com</p>
                        <p><strong>Password:</strong> password</p>
                        <div class="alert alert-warning">
                            <strong>Important:</strong> Please change the default password after your first login!
                        </div>
                    </div>
                    
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Go to Dashboard
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html> 