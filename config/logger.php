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