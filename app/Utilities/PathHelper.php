<?php

namespace App\Utilities;

class PathHelper
{
    /**
     * Devuelve la ruta absoluta al archivo .env en la raíz del proyecto
     */
    public static function envPath(): string
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
    }

    /**
     * Devuelve la ruta absoluta a la raíz del proyecto
     */
    public static function projectRoot(): string
    {
        return dirname(__DIR__, 2);
    }

    /**
     * Devuelve la ruta absoluta a cualquier archivo relativo a la raíz
     */
    public static function fromRoot(string $relativePath): string
    {
        return self::projectRoot() . DIRECTORY_SEPARATOR . ltrim($relativePath, DIRECTORY_SEPARATOR);
    }
}
