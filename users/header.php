<?php
// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - BSU User System' : 'BSU User System'; ?></title>
    <link rel="icon" href="../assets/logo/bsutneu.png" type="image/png">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .user-header {
            background: #dc3545;
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .user-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .user-logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }
        .user-nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .user-nav-link {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .user-nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .user-logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s;
        }
        .user-logout-btn:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            text-decoration: none;
        }
        .user-welcome {
            color: rgba(255,255,255,0.9);
            font-size: 0.9rem;
        }
        @media (max-width: 768px) {
            .user-nav {
                flex-direction: column;
                gap: 15px;
            }
            .user-nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
        /* Simple modal styling for users header (no Bootstrap here) */
        .simple-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1050;
        }
        .simple-modal {
            background: #fff;
            border-radius: 10px;
            width: 90%;
            max-width: 420px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .simple-modal-header {
            padding: 14px 18px;
            border-bottom: 1px solid #eee;
            font-weight: 600;
        }
        .simple-modal-body { padding: 18px; }
        .simple-modal-footer {
            padding: 12px 18px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .btn-outline { border: 1px solid #ced4da; background: #fff; color: #333; padding: 8px 14px; border-radius: 6px; }
        .btn-danger { background: #dc3545; color: #fff; border: none; padding: 8px 14px; border-radius: 6px; }
    </style>
</head>
<body>
    <header class="user-header">
        <nav class="user-nav">
            <a href="index.php" class="user-logo">
                <i class="fas fa-university"></i> BSU User System
            </a>
            
            <div class="user-nav-links">
                <?php if ($isLoggedIn): ?>
                    <span class="user-welcome">
                        <i class="fas fa-user"></i> Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </span>
                    <a href="index.php" class="user-nav-link">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a href="profile.php" class="user-nav-link">
                        <i class="fas fa-user-circle"></i> Profile
                    </a>
                    <a href="logout.php" class="user-logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="user-nav-link">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="register.php" class="user-nav-link">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                <?php endif; ?>
                <a href="../landing.php" class="user-nav-link">
                    <i class="fas fa-arrow-left"></i> Main System
                </a>
            </div>
        </nav>
    </header> 
    <!-- Logout Confirm Modal (simple) -->
    <div class="simple-modal-backdrop" id="userLogoutBackdrop" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="simple-modal" role="document">
            <div class="simple-modal-header"><i class="fas fa-sign-out-alt"></i> Confirm Logout</div>
            <div class="simple-modal-body">Are you sure you want to log out?</div>
            <div class="simple-modal-footer">
                <button type="button" class="btn-outline" id="userLogoutCancel">Cancel</button>
                <button type="button" class="btn-danger" id="userLogoutConfirm">Logout</button>
            </div>
        </div>
    </div>
    <script>
    (function() {
        document.addEventListener('click', function(e) {
            const logoutLink = e.target.closest('a[href="logout.php"]');
            if (!logoutLink) return;
            e.preventDefault();
            const backdrop = document.getElementById('userLogoutBackdrop');
            if (!backdrop) { window.location.href = logoutLink.href; return; }
            backdrop.style.display = 'flex';
            function closeModal() { backdrop.style.display = 'none'; }
            document.getElementById('userLogoutCancel')?.addEventListener('click', closeModal, { once: true });
            backdrop.addEventListener('click', function(ev) { if (ev.target === backdrop) closeModal(); }, { once: true });
            document.getElementById('userLogoutConfirm')?.addEventListener('click', function() { window.location.href = logoutLink.href; }, { once: true });
        });
    })();
    </script>