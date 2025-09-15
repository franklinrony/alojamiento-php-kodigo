<?php

namespace App\Middlewares;

/**
 * Interface IMiddleware
 * Interfaz base para todos los middlewares
 */
interface IMiddleware
{
    /**
     * Procesa la petición
     *
     * @param callable $next Siguiente middleware en la cadena
     * @return void
     */
    public function handle(callable $next): void;
}
