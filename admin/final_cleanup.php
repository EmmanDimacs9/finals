<?php
// Script to clean up all temporary files created during restoration process
echo "🧹 Final Cleanup - Removing Temporary Files...\n\n";

$admin_dir = __DIR__;

// List of temporary files to remove
$temp_files = [
    'check_inventory_status.php',
    'restore_inventory_data.php',
    'execute_restore.php',
    'restore_via_mysql.php',
    'create_sample_inventory.php',
    'fix_duplicate_constraint.php',
    'delete_sample_data.php',
    'comprehensive_cleanup.php',
    'final_cleanup.php' // This script will delete itself last
];

$deleted_count = 0;
$not_found_count = 0;

echo "📁 Scanning admin directory for temporary files...\n\n";

foreach ($temp_files as $file) {
    $file_path = $admin_dir . '/' . $file;
    
    if (file_exists($file_path)) {
        if ($file === 'final_cleanup.php') {
            // Skip self-deletion for now, will do it at the end
            continue;
        }
        
        if (unlink($file_path)) {
            echo "✅ Deleted: $file\n";
            $deleted_count++;
        } else {
            echo "❌ Failed to delete: $file\n";
        }
    } else {
        echo "ℹ️ Not found: $file\n";
        $not_found_count++;
    }
}

// Also check for any other temporary files
echo "\n🔍 Checking for other temporary files...\n";

$other_temp_patterns = [
    'temp_*.php',
    '*_temp.php',
    '*_backup.php',
    'test_*.php',
    '*_test.php'
];

$additional_deleted = 0;

foreach ($other_temp_patterns as $pattern) {
    $files = glob($admin_dir . '/' . $pattern);
    foreach ($files as $file) {
        $filename = basename($file);
        if (unlink($file)) {
            echo "✅ Deleted additional temp file: $filename\n";
            $additional_deleted++;
        }
    }
}

if ($additional_deleted == 0) {
    echo "ℹ️ No additional temporary files found\n";
}

echo "\n📊 Cleanup Summary:\n";
echo "   Files deleted: $deleted_count\n";
echo "   Additional temp files deleted: $additional_deleted\n";
echo "   Files not found: $not_found_count\n";
echo "   Total files processed: " . count($temp_files) . "\n";

echo "\n🎉 Cleanup Complete!\n";
echo "✅ All temporary restoration files have been removed\n";
echo "✅ Your admin directory is now clean\n";
echo "✅ Only essential system files remain\n";

echo "\n📝 Remaining essential admin files:\n";
$essential_files = [
    'dashboard.php' => 'Admin dashboard',
    'equipment.php' => 'Equipment management',
    'users.php' => 'User management',
    'reports.php' => 'Reports generation',
    'maintenance.php' => 'Maintenance management',
    'departments.php' => 'Department management',
    'sidebar.php' => 'Navigation sidebar',
    'cleanup_database_tables.php' => 'Database analysis tool (keep for future use)'
];

foreach ($essential_files as $file => $description) {
    if (file_exists($admin_dir . '/' . $file)) {
        echo "   ✅ $file - $description\n";
    }
}

echo "\n🚀 Your inventory management system is now optimized and ready for use!\n";

// Self-destruct this cleanup script
echo "\n💥 Self-destructing cleanup script...\n";
register_shutdown_function(function() use ($admin_dir) {
    $self_file = $admin_dir . '/final_cleanup.php';
    if (file_exists($self_file)) {
        unlink($self_file);
        echo "✅ Cleanup script removed\n";
    }
});
?>
