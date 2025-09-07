<?php

namespace App\Middlewares;

/**
 * Class MiddlewareDispatcher
 * Maneja la ejecución de la cadena de middleware
 */
class MiddlewareDispatcher
{
    /**
     * @var array Lista de middlewares
     */
    private array $middlewares = [];

    /**
     * @var callable $handler El manejador final
     */
    private $handler;

    /**
     * Constructor
     * @param callable $handler El manejador final
     */
    public function __construct(callable $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Agrega un middleware a la cadena
     * @param IMiddleware $middleware
     * @return self
     */
    public function addMiddleware(IMiddleware $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * Ejecuta la cadena de middlewares
     */
    public function handle(): void
    {
        $this->next(0)();
    }

    /**
     * Crea una función anidada para el siguiente middleware
     * @param int $index
     * @return callable
     */
    private function next(int $index): callable
    {
        if ($index >= count($this->middlewares)) {
            return $this->handler;
        }

        $middleware = $this->middlewares[$index];
        return function () use ($middleware, $index) {
            $middleware->handle($this->next($index + 1));
        };
    }
}
