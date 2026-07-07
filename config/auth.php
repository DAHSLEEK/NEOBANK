<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: /neobank/login.php');
    exit;
}

// Role hierarchy
define('ROLES', [
    'Admin'              => 6,
    'Branch Manager'     => 5,
    'Loans Officer'      => 4,
    'Customer Advisor'   => 3,
    'Teller'             => 2,
    'Compliance Officer' => 1,
]);

function hasRole(string $minimumRole): bool {
    $userRoleLevel    = ROLES[$_SESSION['role']] ?? 0;
    $requiredLevel    = ROLES[$minimumRole] ?? 0;
    return $userRoleLevel >= $requiredLevel;
}

function requireRole(string $minimumRole): void {
    if (!hasRole($minimumRole)) {
        http_response_code(403);
        die('
            <!DOCTYPE html>
            <html><head>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            </head><body class="p-5">
            <div class="alert alert-danger">
                <strong>Access Denied.</strong> You do not have permission to access this page.
                <a href="/neobank/index.php" class="alert-link ms-2">Return to dashboard</a>
            </div>
            </body></html>
        ');
    }
}