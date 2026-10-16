<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  json_response(['ok'=>false,'error'=>'METHOD_NOT_ALLOWED'], 405);
}

try {
  $pdo = db();
  $people = $pdo->query("SELECT id, nombre_completo FROM personal WHERE activo=1 ORDER BY id ASC")->fetchAll();

  $data = [];
  foreach ($people as $p) {
    $id = (int)$p['id'];
    $data[] = [
      'id' => $id,
      'nombre' => $p['nombre_completo'],
      'descriptor_url' => "/deteccionrostro/models/$id/descriptor.json"
    ];
  }

  json_response(['ok'=>true,'data'=>$data]);
} catch (Throwable $e) {
  json_response(['ok'=>false,'error'=>'SERVER','message'=>$e->getMessage()], 500);
}
