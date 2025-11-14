<?php
// Get current page to highlight active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="col-md-3 col-lg-2 sidebar p-3">
    <ul class="nav nav-pills flex-column">
        <!-- Main Admin Menu Items -->
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        
        <li class="nav-item">
            <a href="equipment.php" class="nav-link <?= $current_page === 'equipment.php' ? 'active' : '' ?>">
                <i class="fas fa-laptop"></i> Equipment
            </a>
        </li>
        
        <li class="nav-item">
            <a href="departments.php" class="nav-link <?= $current_page === 'departments.php' ? 'active' : '' ?>">
                <i class="fas fa-building"></i> Departments
            </a>
        </li>
        
        <li class="nav-item">
            <a href="reports.php" class="nav-link <?= $current_page === 'reports.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
        </li>
        
        <li class="nav-item">
            <a href="request.php" class="nav-link <?= $current_page === 'request.php' ? 'active' : '' ?>">
                <i class="fas fa-envelope"></i> Requests
            </a>
        </li>
        
        <!-- ICT Service Management Dropdown -->
        <li class="nav-item">
            <a class="nav-link dropdown-toggle <?= in_array($current_page, ['indet.php', 'qr.php', 'history.php', 'mytasks.php', 'profile.php']) ? 'active' : '' ?>" 
               data-bs-toggle="collapse" href="#ictServiceMenu" role="button" aria-expanded="<?= in_array($current_page, ['indet.php', 'qr.php', 'history.php', 'mytasks.php', 'profile.php']) ? 'true' : 'false' ?>" 
               aria-controls="ictServiceMenu">
                <i class="fas fa-desktop"></i> ICT Service <i class="fas fa-chevron-down ms-1" style="font-size: 0.7rem;"></i>
            </a>
            <div class="collapse <?= in_array($current_page, ['indet.php', 'qr.php', 'history.php', 'mytasks.php', 'profile.php']) ? 'show' : '' ?>" id="ictServiceMenu">
                <ul class="nav flex-column ms-3 mt-2">
                    <li class="nav-item">
                        <a href="../technician/indet.php" class="nav-link <?= $current_page === 'indet.php' ? 'active' : '' ?>">
                            <i class="fas fa-tasks"></i> Service Request Board
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../technician/qr.php" class="nav-link <?= $current_page === 'qr.php' ? 'active' : '' ?>">
                            <i class="fas fa-qrcode"></i> QR Scanner
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../technician/history.php" class="nav-link <?= $current_page === 'history.php' ? 'active' : '' ?>">
                            <i class="fas fa-history"></i> Service History
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../technician/mytasks.php" class="nav-link <?= $current_page === 'mytasks.php' ? 'active' : '' ?>">
                            <i class="fas fa-clipboard-list"></i> My Tasks
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../technician/profile.php" class="nav-link <?= $current_page === 'profile.php' ? 'active' : '' ?>">
                            <i class="fas fa-user-circle"></i> Technician Profile
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        
        <li class="nav-item">
            <a href="system_logs.php" class="nav-link <?= $current_page === 'system_logs.php' ? 'active' : '' ?>">
                <i class="fas fa-clipboard-list"></i> System Logs
            </a>
        </li>
        
        <li class="nav-item">
            <a href="users.php" class="nav-link <?= $current_page === 'users.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Users
            </a>
        </li>
        
        <li class="nav-item">
            <a href="admin_accounts.php" class="nav-link <?= $current_page === 'admin_accounts.php' ? 'active' : '' ?>">
                <i class="fas fa-user-shield"></i> Admin Accounts
            </a>
        </li>
    </ul>
</div>

<style>
.sidebar {
    background: white;
    min-height: calc(100vh - 56px);
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
}

.sidebar .nav-link {
    color: var(--secondary-color);
    margin: 4px 10px;
    border-radius: 8px;
    padding: 8px 12px;
    transition: all 0.3s ease;
}

.sidebar .nav-link:hover,
.sidebar .nav-link.active {
    background: var(--primary-color);
    color: #fff;
}

.sidebar .nav-link.dropdown-toggle {
    cursor: pointer;
    position: relative;
}

.sidebar .nav-link.dropdown-toggle[aria-expanded="true"] {
    background: var(--primary-color);
    color: #fff;
}

.sidebar .nav-link.dropdown-toggle[aria-expanded="true"] .fa-chevron-down {
    transform: rotate(180deg);
    transition: transform 0.3s ease;
}

.sidebar .collapse .nav-link {
    font-size: 0.9rem;
    padding: 6px 12px;
    margin: 2px 0;
}

.sidebar .collapse .nav-link:hover,
.sidebar .collapse .nav-link.active {
    background: rgba(220, 53, 69, 0.1);
    color: var(--primary-color);
    font-weight: 600;
}
</style>

