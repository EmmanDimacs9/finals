<?php
// Profile modal popup for admin navbar. Include in navbar-nav ms-auto.
// Requires session (user_name, user_email, profile_image) and Bootstrap 5 + Font Awesome.

$profileImageUrl = '';
if (!empty($_SESSION['profile_image'])) {
    $pi = $_SESSION['profile_image'];
    if (preg_match('/^https?:\/\//', $pi)) {
        $profileImageUrl = $pi;
    } elseif (strpos($pi, 'uploads/') === 0) {
        $profileImageUrl = '../' . $pi;
    } elseif (strpos($pi, '../uploads/') === 0 || strpos($pi, '/uploads/') === 0) {
        $profileImageUrl = $pi;
    } else {
        $profileImageUrl = '../uploads/profiles/' . $pi;
    }
}
$userName = $_SESSION['user_name'] ?? 'User';
$userEmail = $_SESSION['user_email'] ?? '';
$avatarSrc = $profileImageUrl ?: ('https://via.placeholder.com/60x60/6c757d/ffffff?text=' . strtoupper(substr($userName, 0, 1)));
$avatarSmall = $profileImageUrl ?: ('https://via.placeholder.com/32x32/6c757d/ffffff?text=' . strtoupper(substr($userName, 0, 1)));
?>
<div class="admin-profile-trigger-wrap">
    <div class="admin-profile-trigger" id="adminProfileTrigger" tabindex="0" role="button" aria-haspopup="dialog" aria-expanded="false" aria-controls="adminProfileModal">
        <img src="<?php echo htmlspecialchars($avatarSmall); ?>" alt="Profile" class="admin-profile-picture-small">
    </div>
</div>

<!-- Profile Modal (custom pop modal) -->
<div class="admin-profile-modal-backdrop" id="adminProfileModalBackdrop" aria-hidden="true"></div>
<div class="admin-profile-modal-wrap" id="adminProfileModal" role="dialog" aria-modal="true" aria-labelledby="adminProfileModalLabel" aria-hidden="true">
    <div class="admin-profile-modal-dialog">
        <div class="admin-profile-modal-content">
            <button type="button" class="admin-profile-modal-close" id="adminProfileModalClose" aria-label="Close">&times;</button>
            <div class="admin-profile-modal-header">
                <img src="<?php echo htmlspecialchars($avatarSrc); ?>" alt="Profile" class="admin-profile-modal-avatar">
                <div class="admin-profile-modal-user-info">
                    <h6 id="adminProfileModalLabel"><?php echo htmlspecialchars($userName); ?></h6>
                    <p><?php echo htmlspecialchars($userEmail); ?></p>
                </div>
            </div>
            <div class="admin-profile-modal-menu">
                <a href="profile.php" class="admin-profile-modal-item">
                    <i class="fas fa-user"></i>
                    <span>My Profile</span>
                    <i class="fas fa-chevron-right admin-profile-chevron"></i>
                </a>
                <a href="#" class="admin-profile-modal-item" id="adminSettingsLink">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                    <i class="fas fa-chevron-right admin-profile-chevron"></i>
                </a>
                <a href="#" class="admin-profile-modal-item admin-profile-modal-logout" id="adminLogoutLink" data-logout-href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Log Out</span>
                    <i class="fas fa-chevron-right admin-profile-chevron"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.admin-profile-trigger-wrap { display: inline-block; }
.admin-profile-trigger {
    display: flex; align-items: center; justify-content: center; cursor: pointer;
    padding: 6px; border-radius: 50%; width: 42px; height: 42px;
    transition: background-color 0.2s ease, transform 0.15s ease; outline: none;
}
.admin-profile-trigger:hover { background-color: rgba(255,255,255,0.15); }
.admin-profile-trigger:active { transform: scale(0.92); }
.admin-profile-picture-small {
    width: 32px; height: 32px; border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.35); object-fit: cover;
}

/* Modal backdrop */
.admin-profile-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    z-index: 1040;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.25s ease, visibility 0.25s;
}
.admin-profile-modal-backdrop.show {
    opacity: 1;
    visibility: visible;
}

/* Modal pop */
.admin-profile-modal-wrap {
    position: fixed;
    inset: 0;
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    pointer-events: none;
    visibility: hidden;
}
.admin-profile-modal-wrap.show {
    pointer-events: auto;
    visibility: visible;
}
.admin-profile-modal-dialog {
    max-width: 340px;
    width: 100%;
    pointer-events: auto;
}
.admin-profile-modal-content {
    position: relative;
    background: #fff;
    border: none;
    border-radius: 20px;
    box-shadow: 0 12px 48px rgba(0,0,0,0.18), 0 4px 16px rgba(0,0,0,0.08);
    overflow: hidden;
    transform: scale(0.85);
    opacity: 0;
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.25s ease;
}
.admin-profile-modal-wrap.show .admin-profile-modal-content {
    transform: scale(1);
    opacity: 1;
}
.admin-profile-modal-close {
    position: absolute;
    top: 12px;
    right: 12px;
    z-index: 1;
    width: 32px;
    height: 32px;
    padding: 0;
    border: none;
    background: transparent;
    color: #6c757d;
    font-size: 1.5rem;
    line-height: 1;
    cursor: pointer;
    border-radius: 8px;
    transition: background 0.15s, color 0.15s;
}
.admin-profile-modal-close:hover {
    background: #f1f3f5;
    color: #1a1a1a;
}

.admin-profile-modal-header {
    padding: 24px 22px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    border-bottom: 1px solid #e9ecef;
    background: #fff;
}
.admin-profile-modal-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e9ecef;
    flex-shrink: 0;
}
.admin-profile-modal-user-info { min-width: 0; }
.admin-profile-modal-user-info h6 {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 700;
    color: #1a1a1a;
}
.admin-profile-modal-user-info p {
    margin: 6px 0 0 0;
    font-size: 0.875rem;
    color: #6c757d;
    font-weight: 400;
}

.admin-profile-modal-menu { background: #fff; }
.admin-profile-modal-item {
    display: flex;
    align-items: center;
    padding: 15px 22px;
    text-decoration: none;
    color: #1a1a1a;
    transition: background-color 0.15s;
    border-bottom: 1px solid #f1f3f5;
    gap: 14px;
}
.admin-profile-modal-item:last-child { border-bottom: none; }
.admin-profile-modal-item:hover { background-color: #f5f6f8; color: #1a1a1a; }
.admin-profile-modal-item i:first-of-type {
    width: 22px;
    color: #5a5a5a;
    font-size: 1.05rem;
    flex-shrink: 0;
    text-align: center;
}
.admin-profile-modal-item span { flex: 1; font-weight: 500; }
.admin-profile-modal-item .admin-profile-chevron {
    color: #adb5bd;
    font-size: 0.7rem;
}
.admin-profile-modal-item.admin-profile-modal-logout { color: #dc3545; }
.admin-profile-modal-item.admin-profile-modal-logout:hover { background-color: #fff5f5; color: #c82333; }
.admin-profile-modal-item.admin-profile-modal-logout i,
.admin-profile-modal-item.admin-profile-modal-logout .admin-profile-chevron { color: #dc3545; }
</style>

<script>
(function() {
    var trigger = document.getElementById('adminProfileTrigger');
    var backdrop = document.getElementById('adminProfileModalBackdrop');
    var modal = document.getElementById('adminProfileModal');
    var closeBtn = document.getElementById('adminProfileModalClose');
    var logoutLink = document.getElementById('adminLogoutLink');
    var settingsLink = document.getElementById('adminSettingsLink');

    if (!trigger || !modal || !backdrop) return;

    function onEscape(e) {
        if (e.key === 'Escape' && modal.classList.contains('show')) closeModal();
    }

    function openModal() {
        backdrop.classList.add('show');
        backdrop.setAttribute('aria-hidden', 'false');
        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
        trigger.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
        document.addEventListener('keydown', onEscape);
    }

    function closeModal() {
        backdrop.classList.remove('show');
        backdrop.setAttribute('aria-hidden', 'true');
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        trigger.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        document.removeEventListener('keydown', onEscape);
    }

    trigger.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        openModal();
    });
    trigger.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            openModal();
        }
    });

    closeBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);

    if (logoutLink) {
        logoutLink.addEventListener('click', function(e) {
            e.preventDefault();
            var href = logoutLink.getAttribute('data-logout-href') || logoutLink.getAttribute('href');
            if (!href || href === '#') return;
            if (confirm('Are you sure you want to log out?')) {
                closeModal();
                window.location.href = href;
            }
        });
    }

    if (settingsLink) {
        settingsLink.addEventListener('click', function(e) {
            e.preventDefault();
            closeModal();
        });
    }
})();
</script>
