<?php

namespace App\Controllers;

/**
 * Class BaseController
 * Controlador base con funcionalidad común
 */
abstract class BaseController
{
    /**
     * Devuelve una respuesta JSON
     *
     * @param mixed $data
     * @param int $statusCode
     * @return void
     */
    protected function jsonResponse($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Obtiene el contenido JSON del cuerpo de la petición
     *
     * @return array
     */
    protected function getJsonRequest(): array
    {
        $json = file_get_contents('php://input');
        return json_decode($json, true) ?? [];
    }

    /**
     * Devuelve un error en formato JSON
     *
     * @param string $message
     * @param int $statusCode
     * @return void
     */
    protected function error(string $message, int $statusCode = 400): void
    {
        $this->jsonResponse([
            'error' => true,
            'message' => $message
        ], $statusCode);
    }

    /**
     * Verifica si el método de la petición coincide con el esperado
     *
     * @param string $method
     * @return bool
     */
    protected function isMethod(string $method): bool
    {
        return $_SERVER['REQUEST_METHOD'] === strtoupper($method);
    }

    /**
     * Obtiene un parámetro de la URL
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected function getParam(string $name, $default = null)
    {
        return $_GET[$name] ?? $default;
    }

    /**
     * Verifica si una petición está autenticada
     *
     * @return bool
     */
    protected function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Obtiene el ID del usuario autenticado
     *
     * @return int|null
     */
    protected function getAuthUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }
}
