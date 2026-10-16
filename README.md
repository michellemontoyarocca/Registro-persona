# Registro de personas · prototipo de reconocimiento facial

Interfaz web y API PHP para registrar personas, mantener modelos de referencia y consultar marcaciones. Este repositorio se reorganizó para mostrar el código fuente y los recursos de modelo por separado de las capturas y rostros de prueba.

## Estructura

- `index.html`: captura y flujo de la interfaz.
- `api/`: endpoints PHP para registro, marcación y consultas.
- `face_models/` y `js/`: recursos de inferencia utilizados por el prototipo.
- `test_api.html`: prueba manual de endpoints.

## Configuración

El backend requiere PHP con PDO MySQL y las variables de entorno `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASS`. No hay credenciales por defecto. Los directorios `models/` y `captures/` contienen datos personales cuando se ejecuta y están excluidos de Git.

## Estado y privacidad

**Prototipo de investigación, no control de acceso listo para producción.** Antes de usarlo con personas reales se necesitan consentimiento, autenticación y autorización de cada endpoint, HTTPS, política de retención, pruebas de errores por grupo y revisión del tratamiento de datos biométricos.

El ZIP publicado anteriormente incluía imágenes de rostros y capturas. Retirarlo del estado actual del repositorio **no las elimina del historial ni de copias descargadas**. Es necesario revisar permisos de publicación y, si corresponde, coordinar la retirada del historial remoto y de esas copias.
