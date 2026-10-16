<?php
declare(strict_types=1);
require_once __DIR__ . '/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  json_response(['ok'=>false,'error'=>'METHOD_NOT_ALLOWED'], 405);
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) json_response(['ok'=>false,'error'=>'VALIDATION','message'=>'id requerido'], 400);

$modelsDir = realpath(__DIR__ . '/../models');
if ($modelsDir === false) json_response(['ok'=>false,'error'=>'MODELS_DIR_NOT_FOUND'], 500);

$personDir = $modelsDir . DIRECTORY_SEPARATOR . (string)$id;
if (!is_dir($personDir)) {
  json_response(['ok'=>true,'data'=>[]]); // no hay nada aún
}

$allowed = ['jpg','jpeg','png','webp'];
$files = array_values(array_filter(scandir($personDir) ?: [], function($f) use ($personDir, $allowed){
  if ($f === '.' || $f === '..') return false;
  $path = $personDir . DIRECTORY_SEPARATOR . $f;
  if (!is_file($path)) return false;
  $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
  return in_array($ext, $allowed, true);
}));

// URLs públicas
$data = array_map(fn($f) => "/deteccionrostro/models/$id/" . rawurlencode($f), $files);

json_response(['ok'=>true,'data'=>$data]);
