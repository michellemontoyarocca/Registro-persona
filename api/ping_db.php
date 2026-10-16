<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

$pdo = db();
$ok = $pdo->query("SELECT 1")->fetchColumn();

echo json_encode([
  'ok' => $ok == 1,
  'db' => 'connected'
]);
