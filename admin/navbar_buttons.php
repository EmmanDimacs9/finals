<?php
// Shared Profile + Logout header buttons for admin. Include inside navbar-nav ms-auto.
// Design: Profile = white bg, dark text; Logout = dark gray bg, white text.
?>
<div class="admin-nav-btns">
    <a href="profile.php" class="admin-nav-btn admin-nav-btn-profile">
        <i class="fas fa-user"></i>
        <span>Profile</span>
    </a>
    <a href="logout.php" class="admin-nav-btn admin-nav-btn-logout">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>
</div>
<style>
.admin-nav-btns { display: flex; align-items: center; gap: 10px; }
.admin-nav-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 8px 16px; border-radius: 8px;
    font-weight: 500; font-size: 0.95rem;
    text-decoration: none; transition: opacity 0.2s, box-shadow 0.2s;
    border: 1px solid rgba(0,0,0,0.08);
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.admin-nav-btn:hover { opacity: 0.92; box-shadow: 0 2px 6px rgba(0,0,0,0.12); }
.admin-nav-btn-profile { background: #fff; color: #1a1a1a; }
.admin-nav-btn-profile i { color: #1a1a1a; }
.admin-nav-btn-logout { background: #4a4a4a; color: #fff; border-color: rgba(255,255,255,0.1); box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
.admin-nav-btn-logout:hover { color: #fff; }
.admin-nav-btn-logout i { color: #fff; }
</style>
<script>
(function() {
    document.querySelectorAll('.admin-nav-btn-logout').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to log out?')) e.preventDefault();
        });
    });
})();
</script>
