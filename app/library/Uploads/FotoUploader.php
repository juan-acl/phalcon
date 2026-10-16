<?php

namespace App\Library\Uploads;

use Phalcon\Http\Request\File;

/**
 * Guarda la foto del alumno de forma segura:
 * - tamaño máximo 2 MB, tipo REAL por análisis de cabecera binaria (getimagesize)
 * - solo JPG/PNG (nada de SVG ni PHP)
 * - nombre aleatorio de 32 caracteres hexadecimales
 * - dimensiones limitadas para prevenir ataques de descompresión
 */
final class FotoUploader
{
    private const MIME_EXT = [
        'image/jpeg'  => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/jpg'   => 'jpg',
        'image/png'   => 'png',
        'image/x-png' => 'png',
    ];

    public function __construct(
        private string $directorio,
        private int $maxBytes = 2097152 // 2 MB
    ) {
    }

    /** @return array{0: ?string, 1: ?string} [ruta relativa a public/, mensaje de error] */
    public function guardar(File $archivo): array
    {
        $err = $archivo->getError();
        if ($err !== UPLOAD_ERR_OK) {
            return match ($err) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => [null, 'La imagen supera los 2 MB.'],
                UPLOAD_ERR_PARTIAL                        => [null, 'La imagen solo se cargó parcialmente.'],
                default                                   => [null, 'No se pudo subir la imagen (máximo 2 MB).'],
            };
        }

        if ($archivo->getSize() > $this->maxBytes) {
            return [null, 'La imagen supera los 2 MB.'];
        }

        $tmpName = $archivo->getTempName();
        if (!is_file($tmpName) || filesize($tmpName) === 0) {
            return [null, 'No se pudo subir la imagen (máximo 2 MB).'];
        }

        // Inspección real de la imagen mediante getimagesize (analiza la cabecera binaria)
        $info = @getimagesize($tmpName);
        if ($info === false) {
            return [null, 'Solo se permiten imágenes JPG o PNG.'];
        }

        // Determinar el MIME real por contenido
        $mimeDetectado = $info['mime'] ?? $archivo->getRealType();
        if (!isset(self::MIME_EXT[$mimeDetectado])) {
            return [null, 'Solo se permiten imágenes JPG o PNG.'];
        }

        // Límite de dimensiones para prevenir descompresiones masivas
        if ($info[0] > 4000 || $info[1] > 4000) {
            return [null, 'Las dimensiones de la imagen exceden el límite permitido.'];
        }

        $nombre = bin2hex(random_bytes(16)) . '.' . self::MIME_EXT[$mimeDetectado];
        $dirLimpio = rtrim($this->directorio, '/\\');
        $destino = $dirLimpio . '/' . $nombre;

        if (!is_dir($dirLimpio)) {
            @mkdir($dirLimpio, 0755, true);
        }

        $movido = @$archivo->moveTo($destino);
        if (!$movido) {
            if (is_uploaded_file($tmpName) || is_file($tmpName)) {
                $movido = @copy($tmpName, $destino);
                if ($movido) {
                    @unlink($tmpName);
                }
            }
        }

        if (!$movido || !is_file($destino)) {
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
