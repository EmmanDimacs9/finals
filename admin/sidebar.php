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
            <a href="maintenance.php" class="nav-link <?= $current_page === 'maintenance.php' ? 'active' : '' ?>">
                <i class="fas fa-tools"></i> Maintenance
            </a>
        </li>
        
        <li class="nav-item">
            <a href="system_logs.php" class="nav-link <?= $current_page === 'system_logs.php' ? 'active' : '' ?>">
                <i class="fas fa-clipboard-list"></i> Logs
            </a>
        </li>
        
        <!-- ICT Services -->
        <li class="nav-item">
            <a class="nav-link dropdown-toggle <?= in_array($current_page, ['prevention_maintenance.php', 'request.php', 'system_request.php', 'inventory.php', 'reports.php', 'users.php']) ? 'active' : '' ?>" 
               data-bs-toggle="collapse" href="#ictServiceMenu" role="button" aria-expanded="<?= in_array($current_page, ['prevention_maintenance.php', 'request.php', 'system_request.php', 'inventory.php', 'reports.php', 'users.php']) ? 'true' : 'false' ?>" 
               aria-controls="ictServiceMenu">
                <i class="fas fa-desktop"></i> ICT Services <i class="fas fa-chevron-down ms-1" style="font-size: 0.7rem;"></i>
            </a>
            <div class="collapse <?= in_array($current_page, ['prevention_maintenance.php', 'request.php', 'system_request.php', 'inventory.php', 'reports.php', 'users.php']) ? 'show' : '' ?>" id="ictServiceMenu">
                <ul class="nav flex-column ms-3 mt-2">
                    <li class="nav-item">
                        <a href="prevention_maintenance.php" class="nav-link <?= $current_page === 'prevention_maintenance.php' ? 'active' : '' ?>">
                            <i class="fas fa-calendar-check"></i> Prevention Maintenance Plan Creation
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="request.php" class="nav-link <?= $current_page === 'request.php' ? 'active' : '' ?>">
                            <i class="fas fa-wrench"></i> Service Request
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="system_request.php" class="nav-link <?= $current_page === 'system_request.php' ? 'active' : '' ?>">
                            <i class="fas fa-cog"></i> System Request
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="inventory.php" class="nav-link <?= $current_page === 'inventory.php' ? 'active' : '' ?>">
                            <i class="fas fa-boxes"></i> Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="reports.php" class="nav-link <?= $current_page === 'reports.php' ? 'active' : '' ?>">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="users.php" class="nav-link <?= $current_page === 'users.php' ? 'active' : '' ?>">
                            <i class="fas fa-user-plus"></i> Account Creation
                        </a>
                    </li>
                </ul>
            </div>
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

