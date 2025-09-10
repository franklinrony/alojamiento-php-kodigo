<?php

namespace App\Services\Implementations;

use App\Services\ILoggerService;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\MemoryUsageProcessor;

/**
 * Class LoggerService
 * Implementación del servicio de logging usando Monolog
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
     * Constructor
     */
    public function __construct()
    {
        $this->initializeLoggers();
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
    }

    /**
     * Configura el logger principal
     */
    private function setupMainLogger(): void
    {
        // Handler para archivo principal con rotación
        $mainHandler = new RotatingFileHandler(
            __DIR__ . '/../../../var/logs/app.log',
            30, // Mantener 30 días
            Logger::DEBUG
        );

        // Handler para errores críticos
        $errorHandler = new StreamHandler(
            __DIR__ . '/../../../var/logs/error.log',
            Logger::ERROR
        );

        // Formateador personalizado
        $formatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'Y-m-d H:i:s'
        );

        $mainHandler->setFormatter($formatter);
        $errorHandler->setFormatter($formatter);

        $this->logger->pushHandler($mainHandler);
        $this->logger->pushHandler($errorHandler);

        // Procesadores adicionales
        $this->logger->pushProcessor(new UidProcessor());
        $this->logger->pushProcessor(new WebProcessor());
        $this->logger->pushProcessor(new MemoryUsageProcessor());
    }

    /**
     * Configura el logger de actividades de usuario
     */
    private function setupUserActivityLogger(): void
    {
        $handler = new RotatingFileHandler(
            __DIR__ . '/../../../var/logs/user_activity.log',
            90, // Mantener 90 días para actividades de usuario
            Logger::INFO
        );

        $formatter = new LineFormatter(
            "[%datetime%] USER_ACTIVITY: %message% %context%\n",
            'Y-m-d H:i:s'
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
            __DIR__ . '/../../../var/logs/reservations.log',
            365, // Mantener 1 año para reservas
            Logger::INFO
        );

        $formatter = new LineFormatter(
            "[%datetime%] RESERVATION: %message% %context%\n",
            'Y-m-d H:i:s'
        );

        $handler->setFormatter($formatter);
        $this->reservationLogger->pushHandler($handler);
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
        $this->logger->info($message, $context);
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
        $this->logger->warning($message, $context);
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
        $this->logger->error($message, $context);
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
        $this->logger->debug($message, $context);
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
        $context = [
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];

        $message = "User {$userId} performed action: {$action}";
        
        $this->userActivityLogger->info($message, $context);
        $this->logger->info($message, $context);
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
        
        $this->reservationLogger->info($message, $context);
        $this->logger->info($message, $context);
    }
}
