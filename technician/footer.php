        </div>

    <!-- Footer Navigation -->
    <nav class="footer-nav">
        <div class="nav-container">
            <a href="indet.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'indet.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="mytasks.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'mytasks.php' ? 'active' : ''; ?>" style="display:none">
                <i class="fas fa-tasks"></i>
                <span>My Task</span>
            </a>
            <a href="qr.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'qr.php' ? 'active' : ''; ?>">
                <i class="fas fa-qrcode"></i>
                <span>QR</span>
            </a>
            <a href="history.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'history.php' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i>
                <span>History</span>
            </a>
            <a href="profile.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 