<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  json_response(['ok'=>false,'error'=>'METHOD_NOT_ALLOWED'], 405);
}

$personalId = isset($_POST['personal_id']) ? (int)$_POST['personal_id'] : 0;
$imageB64 = trim((string)($_POST['image_base64'] ?? ''));
$descriptorJson = trim((string)($_POST['descriptor_json'] ?? ''));

if ($personalId <= 0 || $imageB64 === '' || $descriptorJson === '') {
  json_response(['ok'=>false,'error'=>'VALIDATION','message'=>'personal_id, image_base64, descriptor_json son obligatorios'], 400);
}

try {
  $pdo = db();
  $st = $pdo->prepare("SELECT nombre_completo FROM personal WHERE id = ?");
  $st->execute([$personalId]);
  $p = $st->fetch();
  if (!$p) json_response(['ok'=>false,'error'=>'PERSON_NOT_FOUND'], 404);

  // decode image
  if (str_contains($imageB64, ',')) [, $data] = explode(',', $imageB64, 2);
  else $data = $imageB64;

  $bin = base64_decode($data, true);
  if ($bin === false) json_response(['ok'=>false,'error'=>'INVALID_BASE64'], 400);

  // validate descriptor json
  $decoded = json_decode($descriptorJson, true);
  if (!is_array($decoded) || count($decoded) < 10) {
    json_response(['ok'=>false,'error'=>'INVALID_DESCRIPTOR_JSON'], 400);
  }

  $modelsDir = realpath(__DIR__ . '/../models');
  if ($modelsDir === false) json_response(['ok'=>false,'error'=>'MODELS_DIR_NOT_FOUND'], 500);

  // carpeta por ID (estable)
  $personDir = $modelsDir . DIRECTORY_SEPARATOR . (string)$personalId;
  if (!is_dir($personDir) && !mkdir($personDir, 0755, true)) {
    json_response(['ok'=>false,'error'=>'FAILED_TO_CREATE_PERSON_DIR'], 500);
  }

  $refPath  = $personDir . DIRECTORY_SEPARATOR . 'ref.jpg';
  $descPath = $personDir . DIRECTORY_SEPARATOR . 'descriptor.json';

  if (file_put_contents($refPath, $bin) === false) {
    json_response(['ok'=>false,'error'=>'FAILED_TO_SAVE_REF_IMAGE'], 500);
  }
  if (file_put_contents($descPath, json_encode($decoded)) === false) {
    json_response(['ok'=>false,'error'=>'FAILED_TO_SAVE_DESCRIPTOR'], 500);
  }

  json_response([
    'ok'=>true,
    'personal_id'=>$personalId,
    'ref_url'=>"/deteccionrostro/models/$personalId/ref.jpg",
    'descriptor_url'=>"/deteccionrostro/models/$personalId/descriptor.json"
  ]);

} catch (Throwable $e) {
  json_response(['ok'=>false,'error'=>'SERVER','message'=>$e->getMessage()], 500);
}
