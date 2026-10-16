<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  json_response(['ok'=>false,'error'=>'METHOD_NOT_ALLOWED'], 405);
}

function save_capture_base64(string $b64): array {
  $b64 = trim($b64);
  if ($b64 === '') return [false, '', 'EMPTY_IMAGE'];

  if (str_contains($b64, ',')) {
    [$meta, $data] = explode(',', $b64, 2);
  } else {
    $meta = '';
    $data = $b64;
  }

  $bin = base64_decode($data, true);
  if ($bin === false) return [false, '', 'INVALID_BASE64'];

  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime = $finfo->buffer($bin);

  if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true)) {
    return [false, '', 'UNSUPPORTED_MIME:'.$mime];
  }

  $ext = match($mime) {
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    default      => 'jpg'
  };

  $capturesDir = realpath(__DIR__ . '/../captures');
  if ($capturesDir === false) return [false, '', 'CAPTURES_DIR_NOT_FOUND'];

  $name = 'cap_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
  $absPath = $capturesDir . DIRECTORY_SEPARATOR . $name;

  if (file_put_contents($absPath, $bin) === false) {
    return [false, '', 'FAILED_TO_WRITE_FILE'];
  }

  // URL relativa (evita rutas absolutas del sistema)
  $url = '/deteccionrostro/captures/' . $name;
  return [true, $url, ''];
}

/**
 * Reconocimiento facial (STUB)
 * Aquí conectarás tus modelos en /models o un microservicio externo.
 * Debe devolver: [personal_id|null, confianza(0-100)]
 */
function recognize_face(string $absoluteImagePath): array {
  // No puedo confirmar tu implementación real porque no me pasaste tu código/modelo.
  // Por defecto: no reconocido.
  return [null, 0.00];
}

$imageB64 = (string)($_POST['image_base64'] ?? '');
$manualPersonalId = isset($_POST['personal_id']) ? (int)$_POST['personal_id'] : null;

[$okSave, $captureUrl, $err] = save_capture_base64($imageB64);
if (!$okSave) {
  json_response(['ok'=>false,'error'=>'CAPTURE_SAVE_FAILED','detail'=>$err], 400);
}

$absCapturePath = realpath(__DIR__ . '/../captures/' . basename($captureUrl));
if ($absCapturePath === false) {
  json_response(['ok'=>false,'error'=>'CAPTURE_PATH_RESOLVE_FAILED'], 500);
}

// Si no mandas personal_id manual, intenta reconocer
$personalId = $manualPersonalId;
$confianza = 0.00;

if ($personalId === null) {
  [$rid, $conf] = recognize_face($absCapturePath);
  $personalId = $rid;
  $confianza = (float)$conf;
} else {
  // si es manual, puedes mandar confianza opcional
  $confianza = isset($_POST['confianza']) ? (float)$_POST['confianza'] : 100.00;
}

// Registrar en DB solo si hay persona
try {
  $pdo = db();

  $nombre = null;

  if ($personalId !== null && $personalId > 0) {
    // validar que exista
    $st = $pdo->prepare("SELECT nombre_completo FROM personal WHERE id = ?");
    $st->execute([(int)$personalId]);
    $row = $st->fetch();
    if (!$row) {
      json_response(['ok'=>false,'error'=>'PERSON_NOT_FOUND','message'=>'personal_id no existe'], 404);
    }
    $nombre = $row['nombre_completo'];

    $st = $pdo->prepare("
      INSERT INTO marcaciones (personal_id, fecha_hora, confianza, captura_url)
      VALUES (?, NOW(), ?, ?)
    ");
    $st->execute([(int)$personalId, (float)$confianza, $captureUrl]);
    $marcacionId = (int)$pdo->lastInsertId();

    json_response([
      'ok' => true,
      'reconocido' => true,
      'marcacion_id' => $marcacionId,
      'personal_id' => (int)$personalId,
      'nombre' => $nombre,
      'confianza' => (float)$confianza,
      'captura_url' => $captureUrl
    ]);
  }

  // No reconocido: igual devolvemos captura guardada (útil para debug)
  json_response([
    'ok' => true,
    'reconocido' => false,
    'personal_id' => null,
    'nombre' => null,
    'confianza' => 0.00,
    'captura_url' => $captureUrl
  ]);

} catch (Throwable $e) {
  json_response(['ok'=>false,'error'=>'SERVER','message'=>$e->getMessage()], 500);
}
