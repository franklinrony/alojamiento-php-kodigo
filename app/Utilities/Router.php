<?php

namespace App\Utilities;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use League\Container\Container;
use function FastRoute\simpleDispatcher;

/**
 * Class Router
 * Manejador de rutas de la aplicación
 */
class Router
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container Contenedor de dependencias
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->initializeDispatcher();
    }

    /**
     * Inicializa el despachador de rutas
     */
    private function initializeDispatcher(): void
    {
        $routesFile = __DIR__ . '/../../config/routes/api.php';
        $routeDefinitionCallback = require $routesFile;

        $this->dispatcher = simpleDispatcher($routeDefinitionCallback);
    }

    /**
     * Despacha la petición actual
     */
    public function dispatch(): void
    {
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getUri();

        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                http_response_code(404);
                echo json_encode(['error' => 'Not found']);
                break;

            case Dispatcher::METHOD_NOT_ALLOWED:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                break;

            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                $this->handleFoundRoute($handler, $vars);
                break;
        }
    }

    /**
     * Maneja una ruta encontrada
     *
     * @param array $handler
     * @param array $vars
     */
    private function handleFoundRoute(array $handler, array $vars): void
    {
        [$controllerClass, $method] = $handler;

        // Crear instancia del controlador con sus dependencias
        $controller = $this->resolveController($controllerClass);

        // Llamar al método del controlador con los parámetros de la ruta
        $controller->$method(...array_values($vars));
    }

    /**
     * Obtiene la URI limpia de la petición
     *
     * @return string
     */
    private function getUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'];
        
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        
        return rawurldecode($uri);
    }

    /**
     * Resuelve las dependencias del controlador
     *
     * @param string $controllerClass
     * @return object
     */
    private function resolveController(string $controllerClass): object
    {
        // Registrar el controlador en el contenedor si no existe
        if (!$this->container->has($controllerClass)) {
            $this->container->add($controllerClass);
        }

        // Resolver el controlador usando el contenedor
        return $this->container->get($controllerClass);
    }
}
