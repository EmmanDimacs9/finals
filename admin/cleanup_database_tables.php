<?php
// Script to identify and optionally remove unused database tables
require_once '../includes/db.php';

// Simple database connection check
if (!$conn) {
    die("Database connection failed.");
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Table Cleanup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: orange; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .summary { background: #e9ecef; padding: 15px; border-radius: 5px; margin: 15px 0; }
        h1, h2, h3 { color: #dc3545; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn { padding: 8px 16px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-info { background: #17a2b8; color: white; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🗄️ Database Table Cleanup Analysis</h1>";

// Get all tables in the database
$tables_result = $conn->query("SHOW TABLES");
$all_tables = [];
while ($row = $tables_result->fetch_array()) {
    $all_tables[] = $row[0];
}

echo "<h2>📊 Current Database Tables</h2>";
echo "<p>Found " . count($all_tables) . " tables in the database:</p>";
echo "<table>";
echo "<tr><th>Table Name</th><th>Record Count</th><th>Status</th><th>Usage</th></tr>";

// Define tables that are actively used in the system
$used_tables = [
    'users' => 'User accounts and authentication',
    'desktop' => 'Desktop computer inventory',
    'laptops' => 'Laptop inventory',
    'printers' => 'Printer inventory',
    'accesspoint' => 'Access point inventory',
    'switch' => 'Network switch inventory',
    'telephone' => 'Telephone inventory',
    'service_requests' => 'Service request management',
    'system_requests' => 'System request management',
    'requests' => 'General request system',
    'tasks' => 'Task management',
    'activity_log' => 'System activity logging',
    'admin_logs' => 'Admin activity logs',
    'departments' => 'Department information',
    'preventive_maintenance_plans' => 'Maintenance planning',
    'preventive_maintenance_categories' => 'Maintenance categories',
    'maintenance_records' => 'Maintenance history',
    'equipment_categories' => 'Equipment categorization',
    'notifications' => 'System notifications',
    'history' => 'System history tracking',
    'logs' => 'General system logs',
    'report_requests' => 'Report generation requests',
    'service_request_signatures' => 'Digital signatures for service requests',
    'service_surveys' => 'Customer satisfaction surveys',
    'password_reset_otps' => 'Password reset OTP management'
];

$unused_tables = [];
$empty_tables = [];

foreach ($all_tables as $table) {
    // Get record count
    $count_result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
    $count = $count_result ? $count_result->fetch_assoc()['count'] : 0;
    
    // Determine status
    $status = '';
    $usage = '';
    
    if (isset($used_tables[$table])) {
        $status = '<span style="color: green;">✅ Active</span>';
        $usage = $used_tables[$table];
    } else {
        $status = '<span style="color: orange;">⚠️ Potentially Unused</span>';
        $usage = 'Not found in active table list';
        $unused_tables[] = $table;
    }
    
    if ($count == 0) {
        $status .= ' <span style="color: red;">(Empty)</span>';
        $empty_tables[] = $table;
    }
    
    echo "<tr>";
    echo "<td><strong>$table</strong></td>";
    echo "<td>$count</td>";
    echo "<td>$status</td>";
    echo "<td>$usage</td>";
    echo "</tr>";
}

echo "</table>";

// Summary
echo "<div class='summary'>";
echo "<h3>📈 Summary</h3>";
echo "<p><strong>Total tables:</strong> " . count($all_tables) . "</p>";
echo "<p><strong>Active tables:</strong> " . (count($all_tables) - count($unused_tables)) . "</p>";
echo "<p><strong>Potentially unused tables:</strong> " . count($unused_tables) . "</p>";
echo "<p><strong>Empty tables:</strong> " . count($empty_tables) . "</p>";
echo "</div>";

// Show potentially unused tables
if (!empty($unused_tables)) {
    echo "<h2>⚠️ Potentially Unused Tables</h2>";
    echo "<div class='warning'>";
    echo "<p>The following tables were not found in the active table list. Please review before removing:</p>";
    echo "<ul>";
    foreach ($unused_tables as $table) {
        $count_result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
        $count = $count_result ? $count_result->fetch_assoc()['count'] : 0;
        echo "<li><strong>$table</strong> ($count records)</li>";
    }
    echo "</ul>";
    echo "</div>";
}

// Show empty tables
if (!empty($empty_tables)) {
    echo "<h2>📭 Empty Tables</h2>";
    echo "<div class='info'>";
    echo "<p>The following tables are empty and might be safe to remove:</p>";
    echo "<ul>";
    foreach ($empty_tables as $table) {
        echo "<li><strong>$table</strong></li>";
    }
    echo "</ul>";
    echo "</div>";
}

// Manual cleanup section
echo "<h2>🧹 Manual Cleanup Options</h2>";
echo "<div class='warning'>";
echo "<p><strong>⚠️ WARNING:</strong> Only remove tables if you are absolutely sure they are not needed. Always backup your database first!</p>";
echo "</div>";

// Check for specific tables that might be safe to remove
$safe_to_remove = [];

// Check for test tables or backup tables
foreach ($all_tables as $table) {
    if (preg_match('/^(test_|backup_|temp_|old_|_backup|_old|_temp)/', $table)) {
        $safe_to_remove[] = $table;
    }
}

if (!empty($safe_to_remove)) {
    echo "<h3>🗑️ Tables That Appear Safe to Remove</h3>";
    echo "<div class='info'>";
    echo "<p>These tables appear to be test, backup, or temporary tables:</p>";
    echo "<ul>";
    foreach ($safe_to_remove as $table) {
        echo "<li><strong>$table</strong></li>";
    }
    echo "</ul>";
    echo "</div>";
}

// Recommendations
echo "<h2>💡 Recommendations</h2>";
echo "<div class='info'>";
echo "<h3>Safe Actions:</h3>";
echo "<ul>";
echo "<li>✅ Remove any tables with names like 'test_', 'backup_', 'temp_', 'old_'</li>";
echo "<li>✅ Remove empty tables that are not in the active list</li>";
echo "<li>✅ Keep all equipment tables (desktop, laptops, printers, etc.)</li>";
echo "<li>✅ Keep all request and user management tables</li>";
echo "</ul>";

echo "<h3>Caution Required:</h3>";
echo "<ul>";
echo "<li>⚠️ Tables with data should be reviewed carefully</li>";
echo "<li>⚠️ Always backup before removing any table</li>";
echo "<li>⚠️ Test the system after any changes</li>";
echo "</ul>";
echo "</div>";

// Database optimization suggestions
echo "<h2>🚀 Database Optimization</h2>";
echo "<div class='success'>";
echo "<p>After cleaning up unused tables, consider these optimizations:</p>";
echo "<ul>";
echo "<li>Run <code>OPTIMIZE TABLE</code> on remaining tables</li>";
echo "<li>Add indexes on frequently queried columns</li>";
echo "<li>Set up regular database maintenance</li>";
echo "<li>Monitor table sizes and growth</li>";
echo "</ul>";
echo "</div>";

echo "<div class='warning'>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Review the analysis above</li>";
echo "<li>Backup your database</li>";
echo "<li>Manually remove tables you're sure are unused</li>";
echo "<li>Test your system thoroughly</li>";
echo "</ol>";
echo "</div>";

echo "</div></body></html>";

$conn->close();
?>
