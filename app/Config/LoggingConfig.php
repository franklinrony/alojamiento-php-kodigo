<?php

namespace App\Config;

/**
 * Configuración centralizada para el sistema de logging
 */
class LoggingConfig
{
    /**
     * Configuración de niveles de log
     */
    public const LOG_LEVELS = [
        'DEBUG' => 100,
        'INFO' => 200,
        'NOTICE' => 250,
        'WARNING' => 300,
        'ERROR' => 400,
        'CRITICAL' => 500,
        'ALERT' => 550,
        'EMERGENCY' => 600
    ];

    /**
     * Configuración de archivos de log
     */
    public const LOG_FILES = [
        'app' => 'app.log',
        'error' => 'error.log',
        'user_activity' => 'user_activity.log',
        'reservations' => 'reservations.log',
        'database' => 'database.log',
        'security' => 'security.log',
        'api' => 'api.log'
    ];

    /**
     * Configuración de rotación de archivos
     */
    public const ROTATION_DAYS = [
        'app' => 30,
        'error' => 90,
        'user_activity' => 90,
        'reservations' => 365,
        'database' => 30,
        'security' => 180,
        'api' => 30
    ];

    /**
     * Nivel mínimo de log por defecto
     */
    public const DEFAULT_LOG_LEVEL = 'INFO';

    /**
     * Directorio base de logs
     */
    public static function getLogsDirectory(): string
    {
        return __DIR__ . '/../../var/logs';
    }

    /**
     * Obtiene la ruta completa de un archivo de log
     */
    public static function getLogFilePath(string $logType): string
    {
        return self::getLogsDirectory() . '/' . (self::LOG_FILES[$logType] ?? 'app.log');
    }

    /**
     * Obtiene el nivel de log configurado
     */
    public static function getLogLevel(): int
    {
        $envLevel = $_ENV['LOG_LEVEL'] ?? self::DEFAULT_LOG_LEVEL;
        return self::LOG_LEVELS[$envLevel] ?? self::LOG_LEVELS[self::DEFAULT_LOG_LEVEL];
    }

    /**
     * Verifica si el logging está habilitado
     */
    public static function isLoggingEnabled(): bool
    {
        return ($_ENV['LOG_ENABLED'] ?? 'true') === 'true';
    }

    /**
     * Obtiene el formato de fecha para los logs
     */
    public static function getDateFormat(): string
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * Obtiene el formato de mensaje para los logs
     */
    public static function getMessageFormat(): string
    {
        return "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
    }
}
