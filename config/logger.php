<?php
define('LOG_FILE', __DIR__ . '/../logs/activity.log');

function writeLog(string $level, string $message): void {
    $timestamp = date('Y-m-d H:i:s');
    $userId    = $_SESSION['username'] ?? 'guest';
    $role      = $_SESSION['role'] ?? 'none';
    $ip        = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $line      = "[{$timestamp}] [{$level}] User '{$userId}' ({$role}) from {$ip}: {$message}" . PHP_EOL;
    file_put_contents(LOG_FILE, $line, FILE_APPEND | LOCK_EX);
}

function logInfo(string $message): void  { writeLog('INFO', $message); }
function logWarn(string $message): void  { writeLog('WARN', $message); }
function logError(string $message): void { writeLog('ERROR', $message); }
function auditModification(PDO $pdo, string $table, int $recordId, string $action, ?array $oldValues = null, ?array $newValues = null): void {
    $employeeId = null;

    // Get employee_id from the current logged-in user
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT employee_id FROM USER WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        $employeeId = $user['employee_id'] ?? null;
    }

    $oldVal = $oldValues ? json_encode($oldValues) : null;
    $newVal = $newValues ? json_encode($newValues) : null;

    $stmt = $pdo->prepare("
        INSERT INTO MODIFICATION_AUDIT
            (table_affected, record_id, employee_id, action_type, old_value, new_value)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$table, $recordId, $employeeId, $action, $oldVal, $newVal]);
}