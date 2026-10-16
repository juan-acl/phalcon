<?php

namespace App\Library\Security;

/**
 * Validador y detector de caracteres hostiles, inyecciones SQL y scripts para campos de texto.
 */
final class InputValidator
{
    /**
     * Palabras clave de SQL peligrosas en campos de texto convencionales.
     */
    private const SQL_KEYWORDS = [
        'select', 'insert', 'update', 'delete', 'drop', 'alter', 'create',
        'table', 'database', 'where', 'union', 'benchmark', 'sleep',
        'load_file', 'outfile', 'dumpfile', 'information_schema',
    ];

    /**
     * Valida nombres y apellidos de personas (alumnos, docentes).
     * Solo permite letras (incluye tildes, ñ, ü), espacios y opcionalmente apóstrofe/guión.
     * Rechaza números, etiquetas HTML, caracteres especiales e intentos de SQL injection.
     */
    public static function validarNombrePersona(string $valor, string $campo = 'nombres'): ?string
    {
        $trim = trim($valor);
        if ($trim === '') {
            return "El campo {$campo} es obligatorio.";
        }

        if (mb_strlen($trim, 'UTF-8') > 100) {
            return "El campo {$campo} no puede exceder 100 caracteres.";
        }

        // Detección de inyección SQL con comentarios o escapes (-- , /*, ;, comillas combinadas)
        if (str_contains($trim, '--') || str_contains($trim, '/*') || str_contains($trim, '*/') || str_contains($trim, ';')) {
            return "El campo {$campo} contiene caracteres especiales de terminación o comentarios SQL no permitidos.";
        }

        // Detección de etiquetas HTML / scripts (<, >, ", \, {, }, [, ])
        if (preg_match('/[<>{}\[\]"\\\\=$^*~|]/', $trim)) {
            return "El campo {$campo} contiene caracteres especiales no permitidos (evite signos <, >, comillas o símbolos).";
        }

        // Detección de dígitos / números en nombres de personas
        if (preg_match('/[0-9]/', $trim)) {
            return "El campo {$campo} solo debe contener letras y no puede incluir números.";
        }

        // Detección de palabras clave de SQL injection (DROP, UNION, SELECT, SLEEP...)
        if (self::contieneSqlKeywords($trim)) {
            return "El campo {$campo} contiene palabras reservadas o posibles instrucciones SQL no permitidas.";
        }

        // Solo permitir letras del alfabeto (con soporte Unicode completo), espacios, guiones, puntos y apóstrofes válidos
        if (!preg_match('/^[\p{L}\s\'.\-]+$/u', $trim)) {
            return "El campo {$campo} solo debe contener letras y espacios válidos.";
        }

        return null;
    }

    /**
     * Valida nombres de catálogos (Carreras, Cursos, Secciones, Semestres).
     * Permite letras, números, espacios, acentos, guiones, dos puntos, paréntesis y barras.
     * Rechaza etiquetas HTML, comillas, punto y coma, comentarios SQL e inyecciones.
     */
    public static function validarTextoCatalogo(string $valor, string $etiqueta = 'nombre', int $max = 100): ?string
    {
        $trim = trim($valor);
        if ($trim === '') {
            return "El nombre de {$etiqueta} es obligatorio.";
        }

        if (mb_strlen($trim, 'UTF-8') > $max) {
            return "El nombre de {$etiqueta} no puede exceder {$max} caracteres.";
        }

        // Rechazar comentarios SQL o secuencias de terminación
        if (str_contains($trim, '--') || str_contains($trim, '/*') || str_contains($trim, '*/') || str_contains($trim, ';')) {
            return "El nombre de {$etiqueta} contiene caracteres de terminación o comentarios SQL no permitidos.";
        }

        // Rechazar inyección SQL y scripting: comillas, backslash, signos menor/mayor, llaves
        if (preg_match('/[<>{}\[\]"\'`\\\\=$*~]/', $trim)) {
            return "El nombre de {$etiqueta} contiene caracteres especiales no permitidos (evite comillas, punto y coma o etiquetas).";
        }

        // Detección de palabras clave SQL peligrosas
        if (self::contieneSqlKeywords($trim)) {
            return "El nombre de {$etiqueta} contiene palabras reservadas o instrucciones SQL no permitidas.";
        }

        // Caracteres válidos para catálogos: letras, números, espacios, guión, paréntesis, dos puntos, barra, punto, coma, numeral
        if (!preg_match('/^[\p{L}0-9\s\.\,\:\-\(\)\/\#]+$/u', $trim)) {
            return "El nombre de {$etiqueta} contiene caracteres no válidos para este catálogo.";
        }

        return null;
    }

    /**
     * Valida nombres de usuario para el sistema (alfanumérico, punto, guion, guion bajo).
     */
    public static function validarUsuario(string $usuario): ?string
    {
        $trim = trim($usuario);
        if ($trim === '') {
            return "El nombre de usuario es obligatorio.";
        }

        if (strlen($trim) < 3 || strlen($trim) > 50) {
            return "El nombre de usuario debe tener entre 3 y 50 caracteres.";
        }

        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $trim)) {
            return "El nombre de usuario solo puede contener letras, números, puntos y guiones (sin espacios ni caracteres especiales).";
        }

        if (self::contieneSqlKeywords($trim)) {
            return "El nombre de usuario no puede contener palabras reservadas de SQL.";
        }

        return null;
    }

    /**
     * Revisa si el texto contiene palabras clave de inyección SQL delimitadas por palabras.
     */
    private static function contieneSqlKeywords(string $texto): bool
    {
        $lower = mb_strtolower($texto, 'UTF-8');
        foreach (self::SQL_KEYWORDS as $palabra) {
            if (preg_match('/\b' . preg_quote($palabra, '/') . '\b/i', $lower)) {
                return true;
            }
        }
        return false;
    }
}
