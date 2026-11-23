<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/bootstrap.php';

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $pdo = db();
    if ($method === 'GET') {
        $rows = $pdo->query('SELECT * FROM tasks ORDER BY CASE priority WHEN \'high\' THEN 0 WHEN \'medium\' THEN 1 ELSE 2 END, due_date IS NULL, due_date ASC, id DESC')->fetchAll();
        jsonResponse(['tasks'=>$rows]);
    }
    // Reject cross-site requests, including requests without an Origin or Referer.
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $expected = $scheme . '://' . $host;
    $source = $origin ?: $referer;
    if ($host === '' || $source === '' || !str_starts_with($source, $expected . '/') && $source !== $expected) {
        jsonResponse(['error'=>'Cross-origin request blocked.'],403);
    }
    if (!in_array($method,['POST','PUT','DELETE'],true)) jsonResponse(['error'=>'Method not allowed.'],405);
    if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== 0) jsonResponse(['error'=>'Use application/json.'],415);
    $raw = file_get_contents('php://input');
    if ($raw === false || strlen($raw) > 20000) jsonResponse(['error'=>'Request too large.'],413);
    $data = json_decode($raw, true);
    if (!is_array($data)) jsonResponse(['error'=>'Invalid JSON.'],400);
    if ($method === 'POST') {
        $task = validatedTask($data);
        $stmt = $pdo->prepare('INSERT INTO tasks(title,description,status,priority,due_date) VALUES(:title,:description,:status,:priority,:due_date)');
        $stmt->execute($task);
        jsonResponse(['success'=>true,'id'=>(int)$pdo->lastInsertId()],201);
    }
    $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
    if ($id === false || $id === null) throw new InvalidArgumentException('Invalid task ID.');
    if ($method === 'DELETE') {
        $stmt = $pdo->prepare('DELETE FROM tasks WHERE id=:id');
        $stmt->execute(['id'=>$id]);
        jsonResponse(['success'=>true,'deleted'=>$stmt->rowCount()>0]);
    }
    $task = validatedTask($data);
    $stmt = $pdo->prepare('UPDATE tasks SET title=:title, description=:description, status=:status, priority=:priority, due_date=:due_date, updated_at=CURRENT_TIMESTAMP WHERE id=:id');
    $stmt->execute($task + ['id'=>$id]);
    if (!$stmt->rowCount()) {
        $check=$pdo->prepare('SELECT id FROM tasks WHERE id=?');$check->execute([$id]);
        if (!$check->fetch()) jsonResponse(['error'=>'Task not found.'],404);
    }
    jsonResponse(['success'=>true]);
} catch (InvalidArgumentException $e) {
    jsonResponse(['error'=>$e->getMessage()],422);
} catch (Throwable $e) {
    error_log((string)$e);
    jsonResponse(['error'=>'Server error. Check PHP logs and SQLite support.'],500);
}
