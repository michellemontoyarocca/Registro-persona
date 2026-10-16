<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  json_response(['ok'=>false,'error'=>'METHOD_NOT_ALLOWED'], 405);
}

try {
  $pdo = db();
  $rows = $pdo->query("
    SELECT id, codigo, nombre_completo, activo, creado_en
    FROM personal
    ORDER BY id DESC
  ")->fetchAll();

  json_response(['ok'=>true,'data'=>$rows]);
} catch (Throwable $e) {
  json_response(['ok'=>false,'error'=>'SERVER','message'=>$e->getMessage()], 500);
}
