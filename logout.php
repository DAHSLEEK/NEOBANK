<?php
session_start();
require_once 'config/logger.php';
logInfo("User logged out.");
session_destroy();
header('Location: /neobank/login.php');
exit;