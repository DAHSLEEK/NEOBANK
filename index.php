<?php
// Suppress errors from showing to users
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

require_once 'config/auth.php';
require_once 'config/db.php';
$pdo = getDBConnection();

// Verify CSRF on every POST
verifyCsrf();

$page = $_GET['page'] ?? 'dashboard';

$allowedPages = [
    'dashboard'    => ['minRole' => 'Teller',             'file' => 'pages/dashboard.php'],
    'customers'    => ['minRole' => 'Teller',             'file' => 'pages/customers.php'],
    'accounts'     => ['minRole' => 'Teller',             'file' => 'pages/accounts.php'],
    'transactions' => ['minRole' => 'Teller',             'file' => 'pages/transactions.php'],
    'branches'     => ['minRole' => 'Branch Manager',     'file' => 'pages/branches.php',   'allowedRoles' => ['Admin', 'Branch Manager', 'Compliance Officer']],
    'employees'    => ['minRole' => 'Branch Manager',     'file' => 'pages/employees.php',  'allowedRoles' => ['Admin', 'Branch Manager', 'Compliance Officer']],
    'reports'      => ['minRole' => 'Branch Manager',     'file' => 'pages/reports.php',    'allowedRoles' => ['Admin', 'Branch Manager', 'Compliance Officer']],
];

if (!array_key_exists($page, $allowedPages)) {
    $page = 'dashboard';
}

$pageConfig = $allowedPages[$page];

// Check access: either allowedRoles list or minRole weight
$hasAccess = false;
if (isset($pageConfig['allowedRoles'])) {
    $hasAccess = in_array($_SESSION['role'] ?? '', $pageConfig['allowedRoles']);
} else {
    $hasAccess = hasRole($pageConfig['minRole']);
}

if (!$hasAccess) {
    http_response_code(403);
    require_once 'includes/header.php';
    echo '<div class="alert alert-danger"><strong>Access Denied.</strong> You do not have permission to view this page.</div>';
    require_once 'includes/footer.php';
    exit;
}

require_once $pageConfig['file'];