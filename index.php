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
    'branches'     => ['minRole' => 'Compliance Officer', 'file' => 'pages/branches.php'],
    'employees'    => ['minRole' => 'Compliance Officer', 'file' => 'pages/employees.php'],
];

if (!array_key_exists($page, $allowedPages)) {
    $page = 'dashboard';
}

$pageConfig = $allowedPages[$page];

if (!hasRole($pageConfig['minRole'])) {
    http_response_code(403);
    require_once 'includes/header.php';
    echo '<div class="alert alert-danger"><strong>Access Denied.</strong> You do not have permission to view this page.</div>';
    require_once 'includes/footer.php';
    exit;
}

require_once $pageConfig['file'];