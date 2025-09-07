<?php

namespace App\Controllers;

/**
 * Interface IController
 * Interfaz base para controladores
 */
interface IController
{
    /**
     * Devuelve una respuesta JSON
     *
     * @param mixed $data
     * @param int $statusCode
     */
    public function jsonResponse($data, int $statusCode = 200): void;

    /**
     * Devuelve un error en formato JSON
     *
     * @param string $message
     * @param int $statusCode
     */
    public function error(string $message, int $statusCode = 400): void;
}
