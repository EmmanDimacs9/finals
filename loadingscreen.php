<?php

session_start();
  $loadingMessages = [
    "Initializing...",
    "Loading inventory data...",
    "Preparing dashboard...",
    "Almost ready..."
  ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inventory Management System - Loading</title>
  <link rel="icon" href="images/bsutneu.png" type="image/png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
  <style>
    body {
      margin: 0;
      background: linear-gradient(15deg, rgba(0, 0, 0, 0.65) 0%, rgba(201, 44, 44, 0.65) 47%, rgba(244, 162, 97, 0.65) 100%);
      font-family: Arial, sans-serif;
      color: white;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }
    .logo-container {
      margin-bottom: 4rem;
    }
    .logo {
      width: 80px;
      height: 80px;
      background-color: #2563eb;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }
    .loading-text {
      font-size: 14px;
      color: rgba(255, 255, 255, 0.8);
      min-height: 20px;
      text-align: center;
    }
    .progress-bar {
      width: 256px;
      height: 4px;
      background-color: rgba(255, 255, 255, 0.2);
      border-radius: 4px;
      overflow: hidden;
    }
    .progress-bar-fill {
      height: 100%;
      background: linear-gradient(to right, #f87171, #dc2626);
      width: 0%;
      transition: width 0.3s ease-out;
    }
    .branding {
      position: absolute;
      bottom: 64px;
      text-align: center;
      color: #9ca3af;
    }
    .branding span {
      color: #3b82f6;
      font-weight: 600;
    }
    .spinner {
      position: absolute;
      width: 96px;
      height: 96px;
      border: 2px solid rgba(255, 255, 255, 0.3);
      border-radius: 50%;
      animation: spin 1.5s linear infinite;
    }
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
  </style>
</head>
<body>

  <div class="logo-container">
    <img src="images/Ict logs.png" alt="laptop" />
    <i data-lucide="laptop" style="width: 40px; height: 40px; stroke: white;"></i>
  </div>

  <div style="position: relative; display: flex; flex-direction: column; align-items: center; gap: 2rem;">
    <div class="spinner-border" role="status"><span class="sr-only"></span></div>
    <div class="text-center">
      <h2 style="font-size: 1.25rem; font-weight: 600;">Inventory Management System</h2>
      <p class="loading-text" id="loadingText">Initializing...</p>
      <div class="progress-bar"><div class="progress-bar-fill" id="progressBar"></div></div>
      <p id="progressPercent" style="font-size: 12px; color: rgba(255, 255, 255, 0.6); font-family: monospace;">0%</p>
    </div>
  </div>

  <div class="branding"><p>from</p><span>InventoryPro</span></div>

  <script>
    const messages = <?php echo json_encode($loadingMessages, JSON_HEX_TAG); ?>;
    let progress = 0, msgIndex = 0;
    const progressBar = document.getElementById('progressBar');
    const loadingText = document.getElementById('loadingText');
    const progressPercent = document.getElementById('progressPercent');

    const progressInterval = setInterval(() => {
      if (progress >= 100) {
        clearInterval(progressInterval);
        window.location.href = 'index.php'; // Redirect after load
        return;
      }
      progress += 2;
      progressBar.style.width = progress + "%";
      progressPercent.textContent = progress + "%";
    }, 60);

    setInterval(() => {
      msgIndex = (msgIndex + 1) % messages.length;
      loadingText.textContent = messages[msgIndex];
    }, 800);
  </script>

  <script src="https://cdn.jsdelivr.net/npm/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script>lucide.createIcons();</script>

</body>
</html>
