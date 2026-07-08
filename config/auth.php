<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session timeout: 30 minutes of inactivity
$sessionTimeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $sessionTimeout) {
    session_unset();
    session_destroy();
    header('Location: /neobank/login.php?reason=timeout');
    exit;
}
$_SESSION['last_activity'] = time();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: /neobank/login.php');
    exit;
}

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function verifyCsrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            die('
                <!DOCTYPE html>
                <html><head>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
                </head><body class="p-5">
                <div class="alert alert-danger">
                    <strong>Security Error.</strong> Invalid or missing CSRF token.
                    <a href="/neobank/" class="alert-link ms-2">Return to dashboard</a>
                </div>
                </body></html>
            ');
        }
    }
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
    $userRoleLevel = ROLES[$_SESSION['role']] ?? 0;
    $requiredLevel = ROLES[$minimumRole] ?? 0;
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
                <a href="/neobank/" class="alert-link ms-2">Return to dashboard</a>
            </div>
            </body></html>
        ');
    }
}

// CSRF helper for forms
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
}