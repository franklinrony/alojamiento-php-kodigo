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
        $this->dispatcher = simpleDispatcher(function(RouteCollector $r) {
            // Cargar rutas web
            $webRoutes = require PathHelper::fromRoot('config/routes/web.php');
            $webRoutes($r);
            
            // Cargar rutas API
            $apiRoutes = require PathHelper::fromRoot('config/routes/api.php');
            $apiRoutes($r);
        });
    }

    /**
     * Despacha la petición actual
     */
    public function dispatch(): void
    {
        // Obtener método y URI de la petición actual
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getUri();
        
        error_log("Procesando ruta: {$uri} con método {$httpMethod}");
        
        // Obtener información de la ruta
        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                http_response_code(404);
                if ($this->isApiRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode(['error' => true, 'message' => 'Ruta no encontrada']);
                } else {
                    // Para rutas web, mostrar una página 404
                    if ($this->container->has(\Twig\Environment::class)) {
                        $twig = $this->container->get(\Twig\Environment::class);
                        echo $twig->render('errors/404.twig', [
                            'pageTitle' => 'Página no encontrada'
                        ]);
                    } else {
                        echo 'Página no encontrada';
                    }
                }
                break;

            case Dispatcher::METHOD_NOT_ALLOWED:
                http_response_code(405);
                if ($this->isApiRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode(['error' => true, 'message' => 'Método no permitido']);
                } else {
                    // Para rutas web, mostrar una página 405
                    if ($this->container->has(\Twig\Environment::class)) {
                        $twig = $this->container->get(\Twig\Environment::class);
                        echo $twig->render('errors/405.twig', [
                            'pageTitle' => 'Método no permitido'
                        ]);
                    } else {
                        echo 'Método no permitido';
                    }
                }
                break;

            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                
                if (is_array($handler)) {
                    // Si el handler es un array, procesar con middleware
                    $this->handleRouteWithMiddleware($handler, $vars);
                } else {
                    // Si es una ruta simple, manejarla directamente
                    $this->handleFoundRoute($handler, $vars);
                }
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

    /**
     * Maneja una ruta con middleware
     * 
     * @param array $handler
     * @param array $vars
     */
    private function handleRouteWithMiddleware(array $handler, array $vars): void
    {
        [$controllerClass, $method] = $handler;
        
        // Instanciar el controlador usando el contenedor
        $controller = $this->container->get($controllerClass);
        
        // Crear el manejador final que ejecutará el método del controlador
        $finalHandler = function() use ($controller, $method, $vars) {
            return $controller->$method(...array_values($vars));
        };
        
        // Crear el dispatcher de middlewares
        $middlewareDispatcher = new \App\Middlewares\MiddlewareDispatcher($finalHandler);
        
        // Agregar middleware de CORS por defecto
        $middlewareDispatcher->addMiddleware($this->container->get(\App\Middlewares\CorsMiddleware::class));
        
        // Si la ruta requiere autenticación, agregar el middleware de auth
        if (isset($handler['middleware']) && in_array('auth', $handler['middleware'])) {
            $middlewareDispatcher->addMiddleware($this->container->get(\App\Middlewares\AuthMiddleware::class));
        }
        
        // Si hay un permiso específico requerido
        if (isset($handler['permission'])) {
            $permissionMiddleware = new \App\Middlewares\PermissionMiddleware(
                $this->container->get(\App\Services\IUserService::class),
                $handler['permission']
            );
            $middlewareDispatcher->addMiddleware($permissionMiddleware);
        }
        
        // Ejecutar la cadena de middlewares
        $middlewareDispatcher->dispatch();
    }

    /**
     * Determina si la petición actual es una petición a la API
     *
     * @return bool
     */
    private function isApiRequest(): bool
    {
        $uri = $this->getUri();
        return strpos($uri, '/api/') === 0 || 
               strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
    }
}
