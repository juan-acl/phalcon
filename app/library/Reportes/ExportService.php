<?php

declare(strict_types=1);

namespace App\Library\Reportes;

use Dompdf\Dompdf;
use Dompdf\Options;
use Phalcon\Http\Response;

/**
 * Servicio centralizado para la exportación de reportes a PDF y CSV.
 * Aplica configuración estricta de seguridad:
 * - PDF: sin peticiones remotas (SSRF), sin PHP embebido, chroot limitado a public/.
 * - CSV: UTF-8 con BOM, delimitador ',', protección contra inyección de fórmulas de hoja de cálculo.
 */
class ExportService
{
    /**
     * Genera una respuesta HTTP de descarga de archivo CSV.
     *
     * @param array $filas Lista de registros (arreglos asociativos)
     * @param array $columnas Mapeo clave => Nombre visible (o lista de nombres)
     * @param string $nombreBase Nombre del archivo sin extensión
     */
    public function csv(array $filas, array $columnas, string $nombreBase): Response
    {
        $fp = fopen('php://temp', 'r+');
        if ($fp === false) {
            throw new \RuntimeException('No se pudo inicializar el buffer de memoria para CSV');
        }

        // BOM UTF-8 para compatibilidad nativa con Microsoft Excel en español
        fwrite($fp, "\xEF\xBB\xBF");

        $esAsociativo = array_keys($columnas) !== range(0, count($columnas) - 1);
        $encabezados = $esAsociativo ? array_values($columnas) : $columnas;
        $claves = $esAsociativo ? array_keys($columnas) : $columnas;

        // Escribir encabezados
        fputcsv($fp, array_map([$this, 'sanitizarCeldaCsv'], $encabezados), ',');

        // Escribir filas
        foreach ($filas as $fila) {
            $linea = [];
            foreach ($claves as $clave) {
                $valor = $fila[$clave] ?? '';
                $linea[] = $this->sanitizarCeldaCsv($valor);
            }
            fputcsv($fp, $linea, ',');
        }

        rewind($fp);
        $contenido = stream_get_contents($fp);
        fclose($fp);

        $seguroNombre = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $nombreBase);

        $response = new Response();
        $response->setHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->setHeader('Content-Disposition', 'attachment; filename="' . $seguroNombre . '.csv"');
        $response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->setHeader('Pragma', 'no-cache');
        $response->setHeader('Expires', '0');
        $response->setContent($contenido !== false ? $contenido : '');

        return $response;
    }

    /**
     * Genera una respuesta HTTP con el PDF compilado por Dompdf.
     *
     * @param string $html Código HTML renderizado
     * @param string $nombreBase Nombre del archivo sin extensión
     * @param string $orientacion 'portrait' o 'landscape'
     */
    public function pdf(string $html, string $nombreBase, string $orientacion = 'portrait'): Response
    {
        $opts = new Options();
        $opts->set('isRemoteEnabled', false);        // Protección contra SSRF
        $opts->set('isPhpEnabled', false);           // Prohibir ejecución de PHP embebido
        $opts->set('chroot', BASE_PATH . '/public'); // Restringir lectura a public/
        $opts->set('defaultFont', 'DejaVu Sans');    // Soporte UTF-8 (ñ, tildes)

        $dompdf = new Dompdf($opts);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('letter', $orientacion);
        $dompdf->render();

        // Numeración de páginas institucional en canvas
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
        $ancho = $orientacion === 'landscape' ? 792 : 612;
        $alto = $orientacion === 'landscape' ? 612 : 792;
        $canvas->page_text($ancho - 95, $alto - 25, "Pág. {PAGE_NUM} de {PAGE_COUNT}", $font, 7.5, [0.39, 0.45, 0.55]);

        $salida = $dompdf->output();

        $seguroNombre = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $nombreBase);

        $response = new Response();
        $response->setHeader('Content-Type', 'application/pdf');
        $response->setHeader('Content-Disposition', 'attachment; filename="' . $seguroNombre . '.pdf"');
        $response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->setHeader('Pragma', 'no-cache');
        $response->setHeader('Expires', '0');
        $response->setContent($salida);

        return $response;
    }

    /**
     * Sanitiza celdas contra inyección de fórmulas CSV (CSV Injection / Formula Injection).
     * Si la celda inicia con =, +, -, @, \t, \r, antepone un apóstrofe simple (').
     */
    private function sanitizarCeldaCsv(mixed $valor): string
    {
        if ($valor === null) {
            return '';
        }
        $texto = (string) $valor;
        if ($texto !== '' && in_array($texto[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $texto;
        }
        return $texto;
    }
}
