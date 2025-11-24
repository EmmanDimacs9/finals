<?php
require_once '../includes/session.php';

// Legacy entry point kept for backward compatibility.
// Forward any request to the new kanban dashboard located at indet.php
header('Location: indet.php');
exit();

