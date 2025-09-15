<?php

namespace App\Utilities;

use App\Config\DiConfig;
use DI\Container;

/**
 * Wrapper para PHP-DI Container
 * Reemplaza el antiguo ContainerBuilder con League Container
 */
class DiContainer
{
    private static ?Container $instance = null;

    /**
     * Obtiene la instancia del contenedor PHP-DI
     */
    public static function getInstance(): Container
    {
        if (self::$instance === null) {
            self::$instance = DiConfig::getContainer();
        }
        return self::$instance;
    }

    /**
     * Método de compatibilidad con el código existente
     * @deprecated Use getInstance() instead
     */
    public static function getContainer(): Container
    {
        return self::getInstance();
    }

    /**
     * Limpia la instancia del contenedor (útil para testing y evitar memory leaks)
     */
    public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * Limpia recursos del contenedor para evitar memory leaks
     */
    public static function cleanup(): void
    {
        if (self::$instance !== null) {
            // Limpiar referencias circulares
            self::$instance = null;
        }
    }
}
