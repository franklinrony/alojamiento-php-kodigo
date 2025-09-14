<?php

namespace App\Utilities;

use App\Services\ILoggerService;
use App\Config\LoggingConfig;
use DI\Container;

/**
 * Manejador de errores personalizado que usa el sistema de logging
 */
class ErrorHandler
{
    /**
     * @var ILoggerService
     */
    private static $logger;

    /**
     * @var Container
     */
    private static $container;

    /**
     * Inicializa el manejador de errores
     *
     * @param Container $container
     * @return void
     */
    public static function initialize(Container $container): void
    {
        self::$container = $container;
        
        // Configurar el manejador de errores de PHP
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
        
        // Configurar para no mostrar errores en pantalla
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
        // NO configurar error_log aquí para evitar que todos los error_log() vayan al archivo de error
    }

    /**
     * Obtiene el logger del contenedor
     *
     * @return ILoggerService|null
     */
    private static function getLogger(): ?ILoggerService
    {
        if (self::$logger === null && self::$container) {
            try {
                self::$logger = self::$container->get(ILoggerService::class);
            } catch (\Exception $e) {
                // Si no podemos obtener el logger, usar error_log como fallback
                error_log("Error getting logger: " . $e->getMessage());
            }
        }
        return self::$logger;
    }

    /**
     * Maneja errores de PHP
     *
     * @param int $severity
     * @param string $message
     * @param string $file
     * @param int $line
     * @return bool
     */
    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        // No manejar errores que han sido suprimidos con @
        if (!(error_reporting() & $severity)) {
            return false;
        }

        $context = [
            'severity' => $severity,
            'file' => $file,
            'line' => $line,
            'error_type' => self::getErrorType($severity)
        ];

        $logger = self::getLogger();
        if ($logger) {
            switch ($severity) {
                case E_ERROR:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                    $logger->critical("PHP Error: {$message}", $context);
                    break;
                case E_WARNING:
                case E_CORE_WARNING:
                case E_COMPILE_WARNING:
                case E_USER_WARNING:
                    $logger->warning("PHP Warning: {$message}", $context);
                    break;
                case E_NOTICE:
                case E_USER_NOTICE:
                    $logger->info("PHP Notice: {$message}", $context);
                    break;
                case E_DEPRECATED:
                case E_USER_DEPRECATED:
                    $logger->warning("PHP Deprecated: {$message}", $context);
                    break;
                default:
                    $logger->error("PHP Error: {$message}", $context);
                    break;
            }
        } else {
            // Fallback a error_log si no hay logger disponible
            error_log("PHP Error [{$severity}]: {$message} in {$file} on line {$line}");
        }

        // No ejecutar el manejador de errores interno de PHP
        return true;
    }

    /**
     * Maneja excepciones no capturadas
     *
     * @param \Throwable $exception
     * @return void
     */
    public static function handleException(\Throwable $exception): void
    {
        $logger = self::getLogger();
        if ($logger) {
            $logger->logException($exception, [
                'uncaught' => true,
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
            ]);
        } else {
            // Fallback a error_log
            error_log("Uncaught exception: " . $exception->getMessage() . 
                     " in " . $exception->getFile() . ":" . $exception->getLine());
        }

        // Renderizar página de error
        if (self::$container) {
            try {
                ErrorRenderer::renderErrorPage(
                    self::$container,
                    'errors/500.twig',
                    [
                        'pageTitle' => 'Error del Servidor',
                        'error' => $_ENV['APP_DEBUG'] ? $exception->getMessage() : 'Ha ocurrido un error interno del servidor',
                        'debug' => $_ENV['APP_DEBUG'] ?? false
                    ]
                );
            } catch (\Exception $renderError) {
                // Si incluso el renderizado falla, mostrar error básico
                http_response_code(500);
                echo '<h1>Error del Servidor</h1>';
                echo '<p>Ha ocurrido un error interno del servidor.</p>';
            }
        } else {
            http_response_code(500);
            echo '<h1>Error del Servidor</h1>';
            echo '<p>Ha ocurrido un error interno del servidor.</p>';
        }
    }

    /**
     * Maneja errores fatales durante el shutdown
     *
     * @return void
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $logger = self::getLogger();
            if ($logger) {
                $logger->critical("Fatal Error: {$error['message']}", [
                    'file' => $error['file'],
                    'line' => $error['line'],
                    'type' => $error['type'],
                    'fatal' => true
                ]);
            } else {
                error_log("Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}");
            }
        }
    }

    /**
     * Obtiene el tipo de error como string
     *
     * @param int $severity
     * @return string
     */
    private static function getErrorType(int $severity): string
    {
        $types = [
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED'
        ];

        return $types[$severity] ?? 'UNKNOWN';
    }
}
