<?php
// Suppress errors from showing to users
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: /neobank/');
    exit;
}

require_once 'config/db.php';
$pdo = getDBConnection();

$error  = '';
$reason = $_GET['reason'] ?? '';

// Max failed attempts before lockout
define('MAX_ATTEMPTS', 5);
define('LOCKOUT_MINUTES', 15);

function getRecentFailedAttempts(PDO $pdo, string $username, string $ip): int {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM LOGIN_ATTEMPT
        WHERE (username = ? OR ip_address = ?)
        AND success = 0
        AND attempted_at >= DATE_SUB(NOW(), INTERVAL " . LOCKOUT_MINUTES . " MINUTE)
    ");
    $stmt->execute([$username, $ip]);
    return (int) $stmt->fetchColumn();
}

function logAttempt(PDO $pdo, string $username, string $ip, bool $success): void {
    $pdo->prepare("
        INSERT INTO LOGIN_ATTEMPT (username, ip_address, success)
        VALUES (?, ?, ?)
    ")->execute([$username, $ip, $success ? 1 : 0]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $ip       = $_SERVER['REMOTE_ADDR'];

    // Check brute force lockout
    $recentFails = getRecentFailedAttempts($pdo, $username, $ip);
    if ($recentFails >= MAX_ATTEMPTS) {
        $error = "Too many failed login attempts. Please try again in " . LOCKOUT_MINUTES . " minutes.";
    } else {
        $stmt = $pdo->prepare("
            SELECT u.*, e.full_name, e.branch_id
            FROM USER u
            JOIN EMPLOYEE e ON e.employee_id = u.employee_id
            WHERE u.username = ? AND u.is_active = 1
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            logAttempt($pdo, $username, $ip, true);

            // Session fixation protection
            session_regenerate_id(true);

            $_SESSION['user_id']      = $user['user_id'];
            $_SESSION['username']     = $user['username'];
            $_SESSION['role']         = $user['role'];
            $_SESSION['full_name']    = $user['full_name'];
            $_SESSION['branch_id']    = $user['branch_id'];
            $_SESSION['last_activity']= time();
            $_SESSION['csrf_token']   = bin2hex(random_bytes(32));

            header('Location: /neobank/');
            exit;
        } else {
            logAttempt($pdo, $username, $ip, false);
            $remainingAttempts = MAX_ATTEMPTS - ($recentFails + 1);
            $error = "Invalid username or password. " . max(0, $remainingAttempts) . " attempt(s) remaining before lockout.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeoBank Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .login-card { max-width: 420px; margin: 100px auto; }
        .card-header {
            background-color: #212529;
            color: white;
            text-align: center;
            font-size: 1.4rem;
            font-weight: bold;
            padding: 1.2rem;
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="card shadow">
        <div class="card-header">NeoBank Staff Portal</div>
        <div class="card-body p-4">
            <?php if ($reason === 'timeout'): ?>
                <div class="alert alert-warning">Your session expired due to inactivity. Please log in again.</div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-dark w-100">Log In</button>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>