        </div>

    <!-- Footer Navigation -->
    <nav class="footer-nav">
        <div class="nav-container">
            <a href="indet.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'indet.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="qr.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'qr.php' ? 'active' : ''; ?>">
                <i class="fas fa-qrcode"></i>
                <span>QR</span>
            </a>
            <a href="reports.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
            <a href="history.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'history.php' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i>
                <span>History</span>
            </a>
            <a href="activity_logs.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'activity_logs.php' ? 'active' : ''; ?>">
                <i class="fas fa-list-alt"></i>
                <span>Activity Logs</span>
            </a>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 