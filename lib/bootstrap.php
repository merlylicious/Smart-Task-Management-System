<?php
declare(strict_types=1);

const APP_ROOT = __DIR__ . '/..';
const DB_PATH = APP_ROOT . '/data/tasks.sqlite';

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    if (!extension_loaded('pdo_sqlite')) {
        throw new RuntimeException('PDO SQLite extension is required. Enable pdo_sqlite in php.ini.');
    }
    $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $pdo->exec('PRAGMA busy_timeout=5000; PRAGMA foreign_keys=ON;');
    $pdo->exec('CREATE TABLE IF NOT EXISTS tasks (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        description TEXT NOT NULL DEFAULT \'\',
        status TEXT NOT NULL DEFAULT \'todo\' CHECK(status IN (\'todo\',\'in_progress\',\'done\')),
        priority TEXT NOT NULL DEFAULT \'medium\' CHECK(priority IN (\'low\',\'medium\',\'high\')),
        due_date TEXT DEFAULT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');
    return $pdo;
}
function jsonResponse(array $data, int $code=200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
function validatedTask(array $data): array {
    $title = trim((string)($data['title'] ?? ''));
    $description = trim((string)($data['description'] ?? ''));
    $status = (string)($data['status'] ?? 'todo');
    $priority = (string)($data['priority'] ?? 'medium');
    $due = $data['due_date'] ?? null;
    if ($title === '' || mb_strlen($title) > 120) throw new InvalidArgumentException('Title must contain 1–120 characters.');
    if (mb_strlen($description) > 1500) throw new InvalidArgumentException('Description must be at most 1,500 characters.');
    if (!in_array($status, ['todo','in_progress','done'], true)) throw new InvalidArgumentException('Invalid status.');
    if (!in_array($priority, ['low','medium','high'], true)) throw new InvalidArgumentException('Invalid priority.');
    if ($due === '') $due = null;
    if ($due !== null && (!is_string($due) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $due) || !DateTimeImmutable::createFromFormat('!Y-m-d', $due) || DateTimeImmutable::createFromFormat('!Y-m-d', $due)->format('Y-m-d') !== $due)) {
        throw new InvalidArgumentException('Due date must be a valid YYYY-MM-DD date.');
    }
    return compact('title','description','status','priority') + ['due_date'=>$due];
}
