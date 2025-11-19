<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pdo = getDBConnection();
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$message = '';
$message_type = '';

// Handle QR code processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['qr_input'])) {
        $qr_code = trim($_POST['qr_input']);
        
        if (!empty($qr_code)) {
            try {
                // Check if equipment exists
                $stmt = $pdo->prepare("SELECT * FROM equipment WHERE qr_code = ?");
                $stmt->execute([$qr_code]);
                $equipment = $stmt->fetch();
                
                if ($equipment) {
                    // Log the scan
                    $stmt = $pdo->prepare("INSERT INTO equipment_history (equipment_id, user_id, action, description) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$equipment['id'], $_SESSION['user_id'], 'QR Scan', "Equipment scanned via QR code: {$equipment['name']}"]);
                    
                    $message = "Equipment found: {$equipment['name']} - {$equipment['description']}";
                    $message_type = 'success';
                } else {
                    $message = "Equipment not found for QR code: $qr_code";
                    $message_type = 'danger';
                }
            } catch (PDOException $e) {
                $message = 'Database error: ' . $e->getMessage();
                $message_type = 'danger';
            }
        } else {
            $message = 'Please enter a QR code.';
            $message_type = 'danger';
        }
    }
}

// Get recent equipment history for current user
$stmt = $pdo->prepare("
    SELECT eh.*, e.name as equipment_name, e.qr_code
    FROM equipment_history eh
    JOIN equipment e ON eh.equipment_id = e.id
    WHERE eh.user_id = ?
    ORDER BY eh.created_at DESC
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id']]);
$recent_history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Scanner - User Management System</title>
    <link rel="icon" href="../images/bsutneu.png" type="image/png">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-users"></i> User Management System
            </div>
            <div>
                
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="nav">
        <div class="nav-container">
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">
                        <i class="fas fa-home nav-icon"></i>
                        <span class="nav-text">Home</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="tasks.php" class="nav-link">
                        <i class="fas fa-tasks nav-icon"></i>
                        <span class="nav-text">My Task</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="qr.php" class="nav-link active">
                        <i class="fas fa-qrcode nav-icon"></i>
                        <span class="nav-text">QR</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="history.php" class="nav-link">
                        <i class="fas fa-history nav-icon"></i>
                        <span class="nav-text">History</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link">
                        <i class="fas fa-user nav-icon"></i>
                        <span class="nav-text">Profile</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">QR Code Scanner</h1>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- QR Scanner Section -->
            <div class="qr-container">
                <div id="reader" style="width: 100%; max-width: 400px; margin: 0 auto;"></div>
                
                <div style="margin: 2rem 0; text-align: center;">
                    <h3>Or manually enter QR code:</h3>
                    <form method="POST" class="qr-input">
                        <div class="form-group">
                            <input type="text" name="qr_input" class="form-control" placeholder="Enter QR code manually" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search Equipment
                        </button>
                    </form>
                </div>
                
                <!-- File Upload Section -->
                <div style="margin: 2rem 0; text-align: center;">
                    <h3>Or upload QR code image:</h3>
                    <div class="form-group">
                        <input type="file" id="qr-file" accept="image/*" class="form-control" style="max-width: 300px; margin: 0 auto;">
                    </div>
                    <button type="button" id="upload-btn" class="btn btn-secondary">
                        <i class="fas fa-upload"></i> Upload and Scan
                    </button>
                </div>
            </div>
            
            <!-- Recent Scans -->
            <div style="margin-top: 3rem;">
                <h2 style="margin-bottom: 1rem; color: var(--dark-gray);">Recent Scans</h2>
                <?php if ($recent_history): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Equipment</th>
                                    <th>QR Code</th>
                                    <th>Action</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_history as $record): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($record['equipment_name']); ?></td>
                                        <td><code><?php echo htmlspecialchars($record['qr_code']); ?></code></td>
                                        <td><?php echo htmlspecialchars($record['action']); ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($record['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: var(--gray); padding: 2rem;">No recent scans.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        // QR Code Scanner
        function onScanSuccess(decodedText, decodedResult) {
            // Handle the scanned code
            console.log(`Code scanned = ${decodedText}`);
            
            // Submit the scanned code
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `<input type="hidden" name="qr_input" value="${decodedText}">`;
            document.body.appendChild(form);
            form.submit();
        }

        function onScanFailure(error) {
            // Handle scan failure
            console.warn(`Code scan error = ${error}`);
        }

        // Initialize QR Scanner
        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", 
            { 
                fps: 10, 
                qrbox: {width: 250, height: 250} 
            },
            /* verbose= */ false
        );
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);

        // File upload handling
        document.getElementById('upload-btn').addEventListener('click', function() {
            const fileInput = document.getElementById('qr-file');
            const file = fileInput.files[0];
            
            if (!file) {
                alert('Please select a file first.');
                return;
            }
            
            // Here you would typically send the file to a server endpoint
            // For now, we'll just show a message
            alert('File upload functionality would be implemented here. Please use the manual input or camera scanner.');
        });
    </script>
</body>
</html> 