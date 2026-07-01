<?php
require_once __DIR__ . '/config.php';
if (!empty($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }
header('Location: auth.php?action=login');
exit;
