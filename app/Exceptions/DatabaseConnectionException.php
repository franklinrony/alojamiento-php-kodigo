<?php

namespace App\Exceptions;

/**
 * Class DatabaseConnectionException
 * Excepción específica para errores de conexión a la base de datos
 */
class DatabaseConnectionException extends \RuntimeException
{
    /**
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
