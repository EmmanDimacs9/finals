<?php
session_start();
require_once "includes/session.php";
require_once "includes/db.php";

// Check if user is already logged in
if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
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
        body, html {
            height: 100%;
            min-height: 100vh;
        }
        body {
            width: 100vw;
            height: 100vh;
            min-height: 100vh;
            font-family: 'Inknut Antiqua', serif;
            position: relative;
            background: url('images/BSU.jpg') no-repeat center center fixed; 
            background-size: cover;
        }
        .overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.4);
            z-index: 1;
            width: 100vw;
            height: 100vh;
        }
        .main-content {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            min-height: 100vh;
            z-index: 2;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100vw;
        }
        .top-buttons {
            position: absolute;
            top: 30px;
            right: 30px;
            z-index: 4;
            display: flex;
            gap: 12px;
        }
        .top-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 9px 18px;
            border-radius: 16px;
            font-weight: 700;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            transition: background 0.2s;
            box-shadow: 0 2px 8px rgba(220, 53, 69, .12);
            letter-spacing: 1px;
        }
        .top-btn:hover {
            background: #c82333;
        }
        .logo-main {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .logo-main img {
            width: 420px;
            max-width: 90vw;
            height: auto;
            margin-bottom: 20px;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.12));
        }
        .system-title {
            font-size: 2.2rem;
            font-weight: 900;
            color: #fff;
            text-shadow: 2px 2px 4px #111, 0 1px 15px #000a;
            letter-spacing: 3px;
            text-align: center;
            text-transform: uppercase;
        }
        .welcome-text {
            color: #fff;
            font-size: 1.7rem;
            font-weight: 700;
            text-shadow: 1px 1px 6px #222c, 0 2px 16px #000a;
            text-align: center;
            margin-top: 10px;
            margin-bottom: 30px;
        }
        .login-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 18px 70px;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 40px;
            box-shadow: 0 4px 18px rgba(220, 53, 69, 0.20);
            transition: background 0.2s, transform 0.13s;
        }
        .login-btn:hover {
            background: #c82333;
            transform: translateY(-2px) scale(1.03);
        }
        .icon-row {
            display: flex;
            justify-content: center;
            gap: 32px;
        }
        .icon-circle {
            width: 66px;
            height: 66px;
            background: #dc3545;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s, transform .13s;
            box-shadow: 0 3px 10px rgba(0,0,0,0.20);
        }
        .icon-circle:hover {
            background: #c82333;
            transform: scale(1.11);
        }
        .icon-circle i {
            color: white;
            font-size: 2.1rem;
        }
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
            max-width: 810px;
            border-radius: 14px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.28);
            overflow: hidden;
            max-height: 95vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 26px 18px 22px;
            background: #dc3545;
            color: #fff;
            font-size: 1.21rem;
            font-weight: bold;
            text-align: left;
            border-radius: 14px 14px 0 0;
            letter-spacing: 0.3px;
        }
        .modal-title {
            font-size: 1.10em;
            font-weight: bold;
            text-align: left;
        }
        .modal-close {
            background: transparent;
            border: none;
            color: #ffffff;
            font-size: 1.35rem;
            cursor: pointer;
            margin-left: 18px;
            margin-top: -4px;
        }
        .modal-body {
            padding: 34px 32px 28px 32px;
            color: #222;
            line-height: 1.7;
        }
        .modal-body h3, .modal-body h4 {
            font-weight: bold;
            font-size: 1.02em;
            margin-bottom: 5px;
            margin-top: 21px;
            color: #1a1a1a;
            text-align: left;
        }
        .modal-body h3:first-child, .modal-body h4:first-child {
            margin-top: 0;
        }
        .modal-body p {
            margin-bottom: 15px;
            font-size: 1.09em;
            color: #303030;
            text-align: left;
        }
        @media (max-width: 768px) {
            .logo-main img { width: 300px; }
            .system-title { font-size: 1.1rem; }
            .welcome-text { font-size: 1.13rem; }
            .login-btn { font-size: 1rem; padding: 14px 30px; }
            .icon-circle { width: 45px; height: 45px; }
            .icon-circle i { font-size: 1.25rem; }
            .top-buttons {
                top: 10px;
                right: 8px;
            }
            .modal { max-width: 98vw; }
            .modal-header { padding: 14px 15px 14px 14px; font-size: 1.10rem; }
            .modal-body { padding: 24px 11px 22px 11px; font-size: 0.97em; }
        }
        @media (max-width: 480px) {
            .logo-main img { width: 410px; }
            .main-content {
                padding: 10px;
            }
            .icon-row { gap: 11px; }
            .system-title { font-size: 0.96rem; letter-spacing: 1px; }
            .login-btn { font-size: 0.91rem; padding: 8px 20px; }
        }
    </style>
</head>
<body>
    <div class="overlay"></div>
    <div class="main-content">
        <div class="top-buttons">
            <a href="#" id="btnPrivacy" class="top-btn">Privacy Policy</a>
            <a href="#" id="btnFaq" class="top-btn">FAQS</a>
        </div>
        <div class="logo-main">
            <img src="images/landing logo.png" alt="ICT Inventory Logo">
        </div>
        <div class="system-title">ICT INVENTORY SYSTEM</div>
        <div class="welcome-text">Log in and let's get started!</div>
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
    <div id="app-modal" class="modal-overlay" role="dialog" aria-modal="true" aria-hidden="true" style="z-index:10000;">
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