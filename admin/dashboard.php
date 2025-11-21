<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check if user is logged in
requireLogin();

// Equipment table mapping
$equipmentTables = [
    'desktop'     => ['name' => 'model', 'dept' => 'department_office'],
    'laptops'     => ['name' => 'hardware_specifications', 'dept' => 'department'],
    'printers'    => ['name' => 'hardware_specifications', 'dept' => 'department'],
    'accesspoint' => ['name' => 'hardware_specifications', 'dept' => 'department'],
    'switch'      => ['name' => 'hardware_specifications', 'dept' => 'department'],
    'telephone'   => ['name' => 'hardware_specifications', 'dept' => 'department']
];

// Initialize stats
$stats = [
    'total_equipment' => 0,
    'working_units' => 0,
    'not_working_units' => 0,
    'total_departments' => 0
];

$category_data = [];

// Loop through equipment tables
foreach ($equipmentTables as $table => $cols) {
    $check = $conn->query("SHOW TABLES LIKE '$table'");
    if ($check && $check->num_rows > 0) {
        // Total
        $res = $conn->query("SELECT COUNT(*) as total FROM $table");
        $count_total = $res->fetch_assoc()['total'];
        $stats['total_equipment'] += $count_total;

        // Working
        $res = $conn->query("SELECT COUNT(*) as total FROM $table WHERE remarks LIKE '%Working%'");
        $stats['working_units'] += $res->fetch_assoc()['total'];

        // Not Working
        $res = $conn->query("SELECT COUNT(*) as total FROM $table WHERE remarks NOT LIKE '%Working%'");
        $stats['not_working_units'] += $res->fetch_assoc()['total'];

        // Save category
        $category_data[] = ['name' => ucfirst($table), 'count' => $count_total];
    }
}

// Get distinct departments across all equipment tables
$departments = [];
foreach ($equipmentTables as $table => $cols) {
    $res = $conn->query("SELECT DISTINCT {$cols['dept']} as dept FROM $table WHERE {$cols['dept']} IS NOT NULL AND {$cols['dept']} != ''");
    while ($row = $res->fetch_assoc()) {
        $departments[$row['dept']] = true;
    }
}
$stats['total_departments'] = count($departments);

// Monthly acquisitions
$acquisition_data = [];
foreach ($equipmentTables as $table => $cols) {
    $res = $conn->query("SELECT DATE_FORMAT(date_acquired, '%Y-%m') as month, COUNT(*) as count FROM $table WHERE date_acquired IS NOT NULL GROUP BY DATE_FORMAT(date_acquired, '%Y-%m')");
    while ($row = $res->fetch_assoc()) {
        $month = $row['month'];
        if (!isset($acquisition_data[$month])) $acquisition_data[$month] = 0;
        $acquisition_data[$month] += $row['count'];
    }
}
ksort($acquisition_data);

// Recent equipment
$recent_equipment = [];
foreach ($equipmentTables as $table => $cols) {
    $res = $conn->query("SELECT {$cols['name']} AS equipment_name, {$cols['dept']} AS department, date_acquired, remarks FROM $table ORDER BY date_acquired DESC LIMIT 5");
    while ($row = $res->fetch_assoc()) {
        $recent_equipment[] = array_merge($row, ['table' => ucfirst($table)]);
    }
}
usort($recent_equipment, fn($a, $b) => strtotime($b['date_acquired']) - strtotime($a['date_acquired']));
$recent_equipment = array_slice($recent_equipment, 0, 5);

// Maintenance alerts
$maintenance_alerts = [];
foreach ($equipmentTables as $table => $cols) {
    $res = $conn->query("SELECT {$cols['name']} AS equipment_name, {$cols['dept']} AS department, remarks, date_acquired FROM $table WHERE remarks NOT LIKE '%Working%' ORDER BY date_acquired DESC LIMIT 5");
    while ($row = $res->fetch_assoc()) {
        $maintenance_alerts[] = array_merge($row, ['table' => ucfirst($table)]);
    }
}
usort($maintenance_alerts, fn($a, $b) => strtotime($b['date_acquired']) - strtotime($a['date_acquired']));
$maintenance_alerts = array_slice($maintenance_alerts, 0, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - BSU Inventory Management System</title>
    <link rel="icon" href="../images/bsutneu.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
	
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
         :root { --primary-color: #dc3545; --secondary-color: #343a40; }
        .navbar { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); }
        .sidebar { background: white; min-height: calc(100vh - 56px); box-shadow: 2px 0 10px rgba(0,0,0,0.1); }
        .sidebar .nav-link { color: var(--secondary-color); margin: 4px 10px; border-radius: 8px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: var(--primary-color); color: #fff; }
        .main-content { padding: 20px; }
        .card { border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
		 .stats-card { background: white; border-radius: 15px; padding: 20px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .chart-container { background: white; border-radius: 15px; padding: 20px; margin-bottom: 20px; }
    
    #categoryChart { max-height: 250px; }
    </style>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <img src="../images/Ict logs.png" alt="Logo" style="height:40px;"> BSU Inventory System
            </a>
            <div class="navbar-nav ms-auto">
                <a href="profile.php" class="btn btn-light me-2"><i class="fas fa-user-circle"></i> Profile</a>
                <a href="logout.php" class="btn btn-outline-light"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

      <!-- Main Content -->
      <div class="col-md-10 p-4">
        <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>

        <!-- Stats -->
        <div class="row mb-4">
          <div class="col-md-3"><a href="equipment.php" class="text-decoration-none text-dark"><div class="stats-card"><h3><?php echo $stats['total_equipment']; ?></h3><p>Total Equipment</p></div></a></div>
          <div class="col-md-3"><a href="equipment.php?status=working" class="text-decoration-none"><div class="stats-card"><h3 class="text-success"><?php echo $stats['working_units']; ?></h3><p>Working</p></div></a></div>
          <div class="col-md-3"><a href="equipment.php?status=notworking" class="text-decoration-none"><div class="stats-card"><h3 class="text-danger"><?php echo $stats['not_working_units']; ?></h3><p>Not Working</p></div></a></div>
          <div class="col-md-3"><a href="departments.php" class="text-decoration-none"><div class="stats-card"><h3 class="text-info"><?php echo $stats['total_departments']; ?></h3><p>Departments</p></div></a></div>
        </div>

        <div class="row">
          <div class="col-md-6"><div class="chart-container"><h5>Equipment by Category</h5><canvas id="categoryChart"></canvas></div></div>
          <div class="col-md-6"><div class="chart-container"><h5>Monthly Acquisitions</h5><canvas id="acquisitionChart"></canvas></div></div>
        </div>

        <div class="row">
          <!-- Recent Equipment -->
          <div class="col-md-6"><div class="chart-container">
            <h5>Recent Equipment Added</h5>
            <table class="table table-striped"><thead><tr><th>Equipment</th><th>Department</th><th>Date</th></tr></thead><tbody>
            <?php foreach ($recent_equipment as $eq): ?>
              <tr><td><?php echo $eq['equipment_name']; ?></td><td><?php echo $eq['department']; ?></td><td><?php echo date("M d, Y", strtotime($eq['date_acquired'])); ?></td></tr>
            <?php endforeach; ?>
            </tbody></table>
          </div></div>

          <!-- Maintenance Alerts -->
          <div class="col-md-6"><div class="chart-container">
            <h5>Maintenance Alerts</h5>
            <table class="table table-striped"><thead><tr><th>Equipment</th><th>Department</th><th>Status</th></tr></thead><tbody>
            <?php foreach ($maintenance_alerts as $eq): ?>
              <tr><td><?php echo $eq['equipment_name']; ?></td><td><?php echo $eq['department']; ?></td><td><span class="badge bg-danger"><?php echo $eq['remarks']; ?></span></td></tr>
            <?php endforeach; ?>
            </tbody></table>
          </div></div>
        </div>
      </div>
    </div>
  </div>

  <script>
    new Chart(document.getElementById('categoryChart'), {
      type: 'doughnut',
      data: { 
        labels: <?php echo json_encode(array_column($category_data, 'name')); ?>, 
        datasets: [{ 
          data: <?php echo json_encode(array_column($category_data, 'count')); ?>, 
          backgroundColor: ['#dc3545','#6c757d','#198754','#ffc107','#4169e1','#fd7e14'] 
        }] 
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    });

    new Chart(document.getElementById('acquisitionChart'), {
      type: 'bar',
      data: { labels: <?php echo json_encode(array_keys($acquisition_data)); ?>, datasets: [{ label: 'Units Acquired', data: <?php echo json_encode(array_values($acquisition_data)); ?>, backgroundColor: '#dc3545' }] },
      options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
  </script>
  
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
  // Logout confirmation
  document.addEventListener('click', function(e) {
      const logoutLink = e.target.closest('a[href="logout.php"]');
      if (!logoutLink) return;
      e.preventDefault();
      if (confirm('Are you sure you want to log out?')) {
          window.location.href = logoutLink.href;
      }
  });
  </script>
</body>
</html>
