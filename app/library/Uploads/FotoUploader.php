<?php

namespace App\Library\Uploads;

use Phalcon\Http\Request\File;

/**
 * Guarda la foto del alumno de forma segura:
 * - tamaño máximo, tipo REAL (por contenido, no por extensión ni Content-Type del cliente)
 * - solo JPG/PNG (nada de SVG ni PHP)
 * - nombre aleatorio (se ignora el nombre original)
 */
final class FotoUploader
{
    private const MIME_EXT = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

    public function __construct(
        private string $directorio,
        private int $maxBytes = 2097152 // 2 MB
    ) {
    }

    /** @return array{0: ?string, 1: ?string} [ruta relativa a public/, mensaje de error] */
    public function guardar(File $archivo): array
    {
        if ($archivo->getError() !== UPLOAD_ERR_OK) {
            return [null, 'No se pudo subir la imagen (máximo 2 MB).'];
        }
        if ($archivo->getSize() > $this->maxBytes) {
            return [null, 'La imagen supera los 2 MB.'];
        }

        $mime = $archivo->getRealType();
        $info = @getimagesize($archivo->getTempName());
        if (!isset(self::MIME_EXT[$mime]) || $info === false) {
            return [null, 'Solo se permiten imágenes JPG o PNG.'];
        }
        if ($info[0] > 4000 || $info[1] > 4000) {
            return [null, 'Las dimensiones de la imagen exceden el límite permitido.'];
        }

        $nombre = bin2hex(random_bytes(16)) . '.' . self::MIME_EXT[$mime];
        if (!$archivo->moveTo(rtrim($this->directorio, '/\\') . '/' . $nombre)) {
            return [null, 'No se pudo guardar la imagen.'];
        }

        return ['uploads/' . $nombre, null];
    }

    public function eliminar(?string $rutaRelativa): void
    {
        if (!$rutaRelativa || !preg_match('#^uploads/[a-f0-9]{32}\.(jpg|png)$#', $rutaRelativa)) {
            return; // solo borra archivos con el formato que genera guardar()
        }
        $ruta = dirname($this->directorio) . '/' . $rutaRelativa;
        if (is_file($ruta)) {
            @unlink($ruta);
        }
    }
}
