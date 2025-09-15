<?php

namespace App\Services;

/**
 * Interface ILoggerService
 * Interfaz para el servicio de logging
 */
interface ILoggerService
{
    /**
     * Registra un mensaje de información
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info(string $message, array $context = []): void;

    /**
     * Registra un mensaje de advertencia
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function warning(string $message, array $context = []): void;

    /**
     * Registra un mensaje de error
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error(string $message, array $context = []): void;

    /**
     * Registra un mensaje de debug
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug(string $message, array $context = []): void;

    /**
     * Registra un mensaje crítico
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function critical(string $message, array $context = []): void;

    /**
     * Registra un mensaje de emergencia
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function emergency(string $message, array $context = []): void;

    /**
     * Registra una actividad de usuario
     *
     * @param int $userId
     * @param string $action
     * @param array $details
     * @return void
     */
    public function logUserActivity(int $userId, string $action, array $details = []): void;

    /**
     * Registra una actividad de reserva
     *
     * @param int $userId
     * @param int $reservationId
     * @param string $action
     * @param array $details
     * @return void
     */
    public function logReservationActivity(int $userId, int $reservationId, string $action, array $details = []): void;

    /**
     * Registra un error de base de datos
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function logDatabaseError(string $message, array $context = []): void;

    /**
     * Registra un evento de seguridad
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function logSecurityEvent(string $message, array $context = []): void;

    /**
     * Registra una actividad de API
     *
     * @param string $endpoint
     * @param string $method
     * @param int $statusCode
     * @param array $context
     * @return void
     */
    public function logApiActivity(string $endpoint, string $method, int $statusCode, array $context = []): void;

    /**
     * Registra una excepción completa
     *
     * @param \Throwable $exception
     * @param array $context
     * @return void
     */
    public function logException(\Throwable $exception, array $context = []): void;
}
