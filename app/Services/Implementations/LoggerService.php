<?php

namespace App\Services\Implementations;

use App\Services\ILoggerService;
use App\Config\LoggingConfig;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\IntrospectionProcessor;

/**
 * Class LoggerService
 * Implementación del servicio de logging usando Monolog
 * Solo escribe a archivos, no a consola
 */
class LoggerService implements ILoggerService
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Logger
     */
    private $userActivityLogger;

    /**
     * @var Logger
     */
    private $reservationLogger;

    /**
     * @var Logger
     */
    private $databaseLogger;

    /**
     * @var Logger
     */
    private $securityLogger;

    /**
     * @var Logger
     */
    private $apiLogger;

    /**
     * @var bool
     */
    private $loggingEnabled;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->loggingEnabled = LoggingConfig::isLoggingEnabled();
        if ($this->loggingEnabled) {
            $this->initializeLoggers();
        }
    }

    /**
     * Inicializa los loggers
     */
    private function initializeLoggers(): void
    {
        // Logger principal de la aplicación
        $this->logger = new Logger('app');
        $this->setupMainLogger();

        // Logger específico para actividades de usuario
        $this->userActivityLogger = new Logger('user_activity');
        $this->setupUserActivityLogger();

        // Logger específico para actividades de reservas
        $this->reservationLogger = new Logger('reservations');
        $this->setupReservationLogger();

        // Logger específico para base de datos
        $this->databaseLogger = new Logger('database');
        $this->setupDatabaseLogger();

        // Logger específico para seguridad
        $this->securityLogger = new Logger('security');
        $this->setupSecurityLogger();

        // Logger específico para API
        $this->apiLogger = new Logger('api');
        $this->setupApiLogger();
    }

    /**
     * Configura el logger principal
     */
    private function setupMainLogger(): void
    {
        // Handler para archivo principal con rotación
        $mainHandler = new RotatingFileHandler(
            LoggingConfig::getLogFilePath('app'),
            LoggingConfig::ROTATION_DAYS['app'],
            LoggingConfig::getLogLevel()
        );

        // Handler para errores críticos
        $errorHandler = new StreamHandler(
            LoggingConfig::getLogFilePath('error'),
            Logger::ERROR
        );

        // Formateador personalizado
        $formatter = new LineFormatter(
            LoggingConfig::getMessageFormat(),
            LoggingConfig::getDateFormat()
        );

        $mainHandler->setFormatter($formatter);
        $errorHandler->setFormatter($formatter);

        $this->logger->pushHandler($mainHandler);
        $this->logger->pushHandler($errorHandler);

        // Procesadores adicionales - SOLO EN MODO DEBUG para evitar memory leaks
        if ($_ENV['APP_DEBUG'] === 'true') {
            $this->logger->pushProcessor(new UidProcessor());
            $this->logger->pushProcessor(new WebProcessor());
            $this->logger->pushProcessor(new MemoryUsageProcessor());
            $this->logger->pushProcessor(new IntrospectionProcessor());
        }
    }

    /**
     * Configura el logger de actividades de usuario
     */
    private function setupUserActivityLogger(): void
    {
        $handler = new RotatingFileHandler(
            LoggingConfig::getLogFilePath('user_activity'),
            LoggingConfig::ROTATION_DAYS['user_activity'],
            Logger::INFO
        );

        $formatter = new LineFormatter(
            "[%datetime%] USER_ACTIVITY: %message% %context%\n",
            LoggingConfig::getDateFormat()
        );

        $handler->setFormatter($formatter);
        $this->userActivityLogger->pushHandler($handler);
    }

    /**
     * Configura el logger de reservas
     */
    private function setupReservationLogger(): void
    {
        $handler = new RotatingFileHandler(
            LoggingConfig::getLogFilePath('reservations'),
            LoggingConfig::ROTATION_DAYS['reservations'],
            Logger::INFO
        );

        $formatter = new LineFormatter(
            "[%datetime%] RESERVATION: %message% %context%\n",
            LoggingConfig::getDateFormat()
        );

        $handler->setFormatter($formatter);
        $this->reservationLogger->pushHandler($handler);
    }

    /**
     * Configura el logger de base de datos
     */
    private function setupDatabaseLogger(): void
    {
        $handler = new RotatingFileHandler(
            LoggingConfig::getLogFilePath('database'),
            LoggingConfig::ROTATION_DAYS['database'],
            Logger::WARNING
        );

        $formatter = new LineFormatter(
            "[%datetime%] DATABASE: %message% %context%\n",
            LoggingConfig::getDateFormat()
        );

        $handler->setFormatter($formatter);
        $this->databaseLogger->pushHandler($handler);
    }

    /**
     * Configura el logger de seguridad
     */
    private function setupSecurityLogger(): void
    {
        $handler = new RotatingFileHandler(
            LoggingConfig::getLogFilePath('security'),
            LoggingConfig::ROTATION_DAYS['security'],
            Logger::WARNING
        );

        $formatter = new LineFormatter(
            "[%datetime%] SECURITY: %message% %context%\n",
            LoggingConfig::getDateFormat()
        );

        $handler->setFormatter($formatter);
        $this->securityLogger->pushHandler($handler);
    }

    /**
     * Configura el logger de API
     */
    private function setupApiLogger(): void
    {
        $handler = new RotatingFileHandler(
            LoggingConfig::getLogFilePath('api'),
            LoggingConfig::ROTATION_DAYS['api'],
            Logger::INFO
        );

        $formatter = new LineFormatter(
            "[%datetime%] API: %message% %context%\n",
            LoggingConfig::getDateFormat()
        );

        $handler->setFormatter($formatter);
        $this->apiLogger->pushHandler($handler);
    }

    /**
     * Registra un mensaje de información
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info(string $message, array $context = []): void
    {
        if ($this->loggingEnabled && $this->logger) {
            $this->logger->info($message, $context);
        }
    }

    /**
     * Registra un mensaje de advertencia
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function warning(string $message, array $context = []): void
    {
        if ($this->loggingEnabled && $this->logger) {
            $this->logger->warning($message, $context);
        }
    }

    /**
     * Registra un mensaje de error
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error(string $message, array $context = []): void
    {
        if ($this->loggingEnabled && $this->logger) {
            $this->logger->error($message, $context);
        }
    }

    /**
     * Registra un mensaje de debug
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        if ($this->loggingEnabled && $this->logger) {
            $this->logger->debug($message, $context);
        }
    }

    /**
     * Registra un mensaje crítico
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function critical(string $message, array $context = []): void
    {
        if ($this->loggingEnabled && $this->logger) {
            $this->logger->critical($message, $context);
        }
    }

    /**
     * Registra un mensaje de emergencia
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function emergency(string $message, array $context = []): void
    {
        if ($this->loggingEnabled && $this->logger) {
            $this->logger->emergency($message, $context);
        }
    }

    /**
     * Registra una actividad de usuario
     *
     * @param int $userId
     * @param string $action
     * @param array $details
     * @return void
     */
    public function logUserActivity(int $userId, string $action, array $details = []): void
    {
        if (!$this->loggingEnabled) {
            return;
        }

        $context = [
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];

        $message = "User {$userId} performed action: {$action}";
        
        if ($this->userActivityLogger) {
            $this->userActivityLogger->info($message, $context);
        }
        if ($this->logger) {
            $this->logger->info($message, $context);
        }
    }

    /**
     * Registra una actividad de reserva
     *
     * @param int $userId
     * @param int $reservationId
     * @param string $action
     * @param array $details
     * @return void
     */
    public function logReservationActivity(int $userId, int $reservationId, string $action, array $details = []): void
    {
        if (!$this->loggingEnabled) {
            return;
        }

        $context = [
            'user_id' => $userId,
            'reservation_id' => $reservationId,
            'action' => $action,
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $message = "Reservation {$reservationId}: User {$userId} performed action: {$action}";
        
        if ($this->reservationLogger) {
            $this->reservationLogger->info($message, $context);
        }
        if ($this->logger) {
            $this->logger->info($message, $context);
        }
    }

    /**
     * Registra un error de base de datos
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function logDatabaseError(string $message, array $context = []): void
    {
        if (!$this->loggingEnabled) {
            return;
        }

        $context['type'] = 'database_error';
        $context['timestamp'] = date('Y-m-d H:i:s');
        
        if ($this->databaseLogger) {
            $this->databaseLogger->error($message, $context);
        }
        if ($this->logger) {
            $this->logger->error($message, $context);
        }
    }

    /**
     * Registra un evento de seguridad
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function logSecurityEvent(string $message, array $context = []): void
    {
        if (!$this->loggingEnabled) {
            return;
        }

        $context['type'] = 'security_event';
        $context['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $context['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $context['timestamp'] = date('Y-m-d H:i:s');
        
        if ($this->securityLogger) {
            $this->securityLogger->warning($message, $context);
        }
        if ($this->logger) {
            $this->logger->warning($message, $context);
        }
    }

    /**
     * Registra una actividad de API
     *
     * @param string $endpoint
     * @param string $method
     * @param int $statusCode
     * @param array $context
     * @return void
     */
    public function logApiActivity(string $endpoint, string $method, int $statusCode, array $context = []): void
    {
        if (!$this->loggingEnabled) {
            return;
        }

        $context['endpoint'] = $endpoint;
        $context['method'] = $method;
        $context['status_code'] = $statusCode;
        $context['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $context['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $context['timestamp'] = date('Y-m-d H:i:s');

        $message = "API {$method} {$endpoint} - Status: {$statusCode}";
        
        if ($this->apiLogger) {
            $this->apiLogger->info($message, $context);
        }
        if ($this->logger) {
            $this->logger->info($message, $context);
        }
    }

    /**
     * Registra una excepción completa
     *
     * @param \Throwable $exception
     * @param array $context
     * @return void
     */
    public function logException(\Throwable $exception, array $context = []): void
    {
        if (!$this->loggingEnabled) {
            return;
        }

        $context['exception_class'] = get_class($exception);
        $context['exception_message'] = $exception->getMessage();
        $context['exception_file'] = $exception->getFile();
        $context['exception_line'] = $exception->getLine();
        $context['exception_trace'] = $exception->getTraceAsString();
        $context['timestamp'] = date('Y-m-d H:i:s');

        $message = "Exception: {$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}";
        
        if ($this->logger) {
            $this->logger->error($message, $context);
        }
    }
}
