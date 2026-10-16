<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(405);
  exit;
}

try {
  $pdo = db();
  $rows = $pdo->query("
    SELECT
      m.id AS marcacion_id,
      p.codigo,
      p.nombre_completo,
      m.fecha_hora,
      m.confianza,
      m.captura_url,
      m.creado_en
    FROM marcaciones m
    INNER JOIN personal p ON p.id = m.personal_id
    ORDER BY m.fecha_hora DESC
  ")->fetchAll(PDO::FETCH_ASSOC);

  header('Content-Type: application/vnd.ms-excel; charset=utf-8');
  header('Content-Disposition: attachment; filename="marcaciones.csv"');

  // BOM para Excel (acentos)
  echo "\xEF\xBB\xBF";

  $out = fopen('php://output', 'w');
  fputcsv($out, array_keys($rows[0] ?? [
    'marcacion_id','codigo','nombre_completo','fecha_hora','confianza','captura_url','creado_en'
  ]));

  foreach ($rows as $r) {
    fputcsv($out, $r);
  }
  fclose($out);
  exit;

} catch (Throwable $e) {
  http_response_code(500);
  echo "Error exportando: " . $e->getMessage();
  exit;
}
