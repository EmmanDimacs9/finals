<?php
session_start();
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = 'Dashboard';
require_once 'header.php';

// Get user information
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get user's recent activity
$activity_stmt = $conn->prepare("
    SELECT * FROM user_logins 
    WHERE user_id = ? 
    ORDER BY login_time DESC 
    LIMIT 5
");
$activity_stmt->bind_param("i", $user_id);
$activity_stmt->execute();
$activities = $activity_stmt->get_result();
?>

<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <div style="background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); padding: 30px; margin-bottom: 30px;">
        <h1 style="color: #dc3545; margin-bottom: 20px;">
            <i class="fas fa-user-circle"></i> Welcome to BSU User System
        </h1>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; border-left: 4px solid #dc3545;">
                <h3><i class="fas fa-user"></i> Profile Information</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Role:</strong> <span style="text-transform: capitalize;"><?php echo htmlspecialchars($user['role']); ?></span></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone_number']); ?></p>
                <p><strong>Member Since:</strong> <?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
            </div>
            
            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; border-left: 4px solid #28a745;">
                <h3><i class="fas fa-chart-line"></i> Quick Stats</h3>
                <?php
                // Get user's activity counts
                $stats_stmt = $conn->prepare("
                    SELECT 
                        COUNT(*) as total_logins,
                        COUNT(CASE WHEN source = 'users_folder' THEN 1 END) as user_folder_logins,
                        COUNT(CASE WHEN source = 'users_folder_registration' THEN 1 END) as registrations,
                        MAX(login_time) as last_activity
                    FROM user_logins 
                    WHERE user_id = ?
                ");
                $stats_stmt->bind_param("i", $user_id);
                $stats_stmt->execute();
                $stats = $stats_stmt->get_result()->fetch_assoc();
                ?>
                <p><strong>Total Logins:</strong> <?php echo $stats['total_logins']; ?></p>
                <p><strong>User System Logins:</strong> <?php echo $stats['user_folder_logins']; ?></p>
                <p><strong>Registrations:</strong> <?php echo $stats['registrations']; ?></p>
                <p><strong>Last Activity:</strong> 
                    <?php echo $stats['last_activity'] ? date('M d, Y H:i', strtotime($stats['last_activity'])) : 'Never'; ?>
                </p>
            </div>
        </div>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; border-left: 4px solid #007bff;">
            <h3><i class="fas fa-history"></i> Recent Activity</h3>
            <?php if ($activities->num_rows > 0): ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #e9ecef;">
                                <th style="padding: 10px; text-align: left;">Date/Time</th>
                                <th style="padding: 10px; text-align: left;">Activity</th>
                                <th style="padding: 10px; text-align: left;">Source</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($activity = $activities->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid #dee2e6;">
                                    <td style="padding: 10px;"><?php echo date('M d, Y H:i', strtotime($activity['login_time'])); ?></td>
                                    <td style="padding: 10px;">
                                        <?php 
                                        switch($activity['source']) {
                                            case 'users_folder':
                                                echo '<i class="fas fa-sign-in-alt"></i> Login';
                                                break;
                                            case 'users_folder_registration':
                                                echo '<i class="fas fa-user-plus"></i> Registration';
                                                break;
                                            case 'users_folder_logout':
                                                echo '<i class="fas fa-sign-out-alt"></i> Logout';
                                                break;
                                            default:
                                                echo '<i class="fas fa-circle"></i> Activity';
                                        }
                                        ?>
                                    </td>
                                    <td style="padding: 10px;">
                                        <span style="background: #dc3545; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem;">
                                            User System
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color: #6c757d;">No recent activity found.</p>
            <?php endif; ?>
        </div>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="../dashboard.php" style="background: #dc3545; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin-right: 10px;">
                <i class="fas fa-tachometer-alt"></i> Go to Main Dashboard
            </a>
            <a href="profile.php" style="background: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin-right: 10px;">
                <i class="fas fa-user-edit"></i> Edit Profile
            </a>
            <a href="logout.php" style="background: #6c757d; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 