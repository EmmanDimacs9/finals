<?php
require_once "includes/session.php";
require_once "includes/db.php";

// Check if user is already logged in
if (isset($_SESSION["user_id"])) {
    header("Location: admin/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICT Inventory System - BSU</title>
    <link rel="icon" href="images/bsutneu.png" type="image/png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        /* Left Side - White Background with Logo */
        .left-side {
            flex: 1;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            position: relative;
        }

        .left-side::before {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #dc3545 0%, #ff4444 50%, #ffaa00 100%);
            clip-path: polygon(0 100%, 100% 60%, 100% 100%);
            z-index: 1;
        }

        .logo-container {
            text-align: center;
            z-index: 2;
        }

        .laptop-icon {
            width: 400px;
            padding-top: 0px;
            height: auto;
            margin-bottom: 0px;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
        }

        .system-title-left {
            font-size: 2.8rem;
            font-weight: 900;
            color: #000000;
            letter-spacing: 3px;
            text-transform: uppercase;
            padding-top: 0px;
            padding-bottom: 100px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            font-family: 'Inknut Antiqua', serif;
        }

        /* Right Side - Building Background */
        .right-side {
            flex: 1;
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url("BSU.jpg") center/cover;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px;
            position: relative;
        }

        .top-buttons {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
        }

        .top-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-block;
        }

        .top-btn:hover {
            background: #c82333;
        }

        .right-content {
            max-width: 500px;
            width: 100%;
            margin-top: 100px;
            text-align: center;
        }

        .welcome-text {
            color: white;
            font-size: 3rem;
            font-weight: 700;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.5);
            margin-bottom: 0px;
            line-height: 1.2;
        }

        .tagline {
            color: white;
            font-size: 1.8rem;
            font-weight: 500;
            text-shadow: 2px 2px 6px rgba(0,0,0,0.5);
            margin-bottom: 50px;
        }

        .login-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 18px 80px;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
            margin-bottom: 80px;
        }

        .login-btn:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.6);
        }

        .icon-row {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
        }

        .icon-circle {
            width: 70px;
            height: 70px;
            background: #dc3545;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }

        .icon-circle:hover {
            background: #c82333;
            transform: scale(1.1);
        }

        .icon-circle i {
            color: white;
            font-size: 1.8rem;
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            padding: 20px;
        }

        .modal {
            background: #ffffff;
            width: 100%;
            max-width: 700px;
            border-radius: 12px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            background: #dc3545;
            color: #ffffff;
        }

        .modal-close {
            background: transparent;
            border: none;
            color: #ffffff;
            font-size: 1.2rem;
            cursor: pointer;
        }

        .modal-body {
            padding: 20px;
            color: #212529;
            line-height: 1.6;
        }

        .modal-body h4 {
            margin: 0 0 6px 0;
        }

        .modal-body p {
            margin: 0 0 14px 0;
        }

        @media (max-width: 968px) {
            .container {
                flex-direction: column;
            }

            .left-side, .right-side {
                min-height: 50vh;
            }

            .system-title-left {
                font-size: 2rem;
            }

            .welcome-text {
                font-size: 2rem;
            }

            .tagline {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Left Side -->
        <div class="left-side">
            <div class="logo-container">
                <img src="images/Ict logs.png" alt="ICT Inventory System" class="laptop-icon">
                <h1 class="system-title-left">ICT INVENTORY SYSTEM</h1>
            </div>
        </div>

        <!-- Right Side -->
        <div class="right-side">
            <div class="top-buttons">
                <a href="#" id="btnPrivacy" class="top-btn">Privacy Policy</a>
                <a href="#" id="btnFaq" class="top-btn">FAQS</a>
            </div>

            <div class="right-content">
                <h2 class="welcome-text">Log in and let's get started!</h2>
                <p class="tagline">Welcome to BSU</p>

                <a href="landing.php" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </a>

                <div class="icon-row">
                    <div class="icon-circle" data-modal="equipment">
                        <i class="fas fa-desktop"></i>
                    </div>
                    <div class="icon-circle" data-modal="maintenance">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="icon-circle" data-modal="analytics">
                        <i class="fas fa-bars"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="app-modal" class="modal-overlay" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title" id="modal-title">Title</div>
                <button class="modal-close" id="modal-close" aria-label="Close modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body" id="modal-body">
                <!-- dynamic content -->
            </div>
        </div>
    </div>

    <script>
        (function(){
            const modalOverlay = document.getElementById('app-modal');
            const modalTitle = document.getElementById('modal-title');
            const modalBody = document.getElementById('modal-body');
            const modalClose = document.getElementById('modal-close');

            function openModal(title, html){
                modalTitle.textContent = title;
                modalBody.innerHTML = html;
                modalOverlay.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }

            function closeModal(){
                modalOverlay.style.display = 'none';
                document.body.style.overflow = 'hidden';
            }

            modalClose.addEventListener('click', closeModal);
            modalOverlay.addEventListener('click', (e)=>{
                if(e.target === modalOverlay){ closeModal(); }
            });
            document.addEventListener('keydown', (e)=>{
                if(e.key === 'Escape'){ closeModal(); }
            });

            const modalContent = {
                equipment: `
                    <h3>Equipment Management</h3>
                    <p>Track and manage all equipment with detailed information and QR codes.</p>
                `,
                maintenance: `
                    <h3>Maintenance Tracking</h3>
                    <p>Schedule and monitor maintenance activities with cost tracking.</p>
                `,
                analytics: `
                    <h3>Analytics & Reports</h3>
                    <p>Generate comprehensive reports and view real-time analytics.</p>
                `,
                privacy: `
                    <h3>Privacy Policy</h3>
                    <p>Your privacy is important to us. We collect only the information necessary to operate the ICT Inventory System, including user account data and usage logs used for authentication, auditing, and system improvement.</p>
                    <p>Data is stored securely and access is restricted to authorized personnel. We do not share your personal data with third parties except as required by law. You can request access or deletion of your data through the system administrator.</p>
                    <p>By continuing to use this system, you consent to the collection and processing of your information as described.</p>
                `,
                faqs: `
                    <h3>FAQs</h3>
                    <h4>How do I log in?</h4>
                    <p>Click the Login button and enter your university-issued credentials.</p>
                    <h4>How do I add equipment?</h4>
                    <p>After logging in, go to Equipment and click Add Equipment. Fill in the required details and save.</p>
                    <h4>How do I track maintenance?</h4>
                    <p>Open Maintenance to create schedules, record activities, and track costs.</p>
                    <h4>Where can I generate reports?</h4>
                    <p>Go to Reports to generate analytics and export documents.</p>
                    <h4>Who do I contact for support?</h4>
                    <p>Please contact the ICT office or your system administrator.</p>
                `
            };

            document.querySelectorAll('.icon-circle').forEach(el => {
                el.addEventListener('click', () => {
                    const key = el.getAttribute('data-modal');
                    const mapTitle = {
                        equipment: 'Equipment Management',
                        maintenance: 'Maintenance Tracking',
                        analytics: 'Analytics & Reports'
                    };
                    openModal(mapTitle[key], modalContent[key]);
                });
            });

            document.getElementById('btnPrivacy').addEventListener('click', (e)=>{
                e.preventDefault();
                openModal('Privacy Policy', modalContent.privacy);
            });

            document.getElementById('btnFaq').addEventListener('click', (e)=>{
                e.preventDefault();
                openModal('FAQs', modalContent.faqs);
            });
        })();
    </script>
</body>
</html>