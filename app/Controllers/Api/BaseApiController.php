<?php

namespace App\Controllers\Api;

use App\Controllers\IController;
use App\Utilities\IRequestValidator;
use App\Utilities\IAuthenticator;

/**
 * Class BaseApiController
 * Controlador base para endpoints de API
 */
abstract class BaseApiController implements IController
{
    /**
     * @var IAuthenticator|null
     */
    protected ?IAuthenticator $authenticator = null;

    /**
     * @var IRequestValidator|null
     */
    protected ?IRequestValidator $validator = null;

    /**
     * Constructor base para controladores API
     *
     * @param IRequestValidator|null $validator
     * @param IAuthenticator|null $authenticator
     */
    public function __construct(
        ?IRequestValidator $validator = null,
        ?IAuthenticator $authenticator = null
    ) {
        $this->validator = $validator;
        $this->authenticator = $authenticator;
    }

    /**
     * Envía una respuesta JSON exitosa
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $statusCode
     */
    protected function success($data = null, ?string $message = null, int $statusCode = 200): void
    {
        $response = [
            'success' => true,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($message !== null) {
            $response['message'] = $message;
        }

        $this->jsonResponse($response, $statusCode);
    }

    /**
     * Envía una respuesta JSON de error
     *
     * @param string $message
     * @param int $statusCode
     * @param array|null $errors
     */
    public function error(string $message, int $statusCode = 400, ?array $errors = null): void
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        $this->jsonResponse($response, $statusCode);
    }

    /**
     * Envía una respuesta JSON
     *
     * @param mixed $data
     * @param int $statusCode
     */
    public function jsonResponse($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Obtiene y valida datos JSON de la petición
     *
     * @param array $rules Reglas de validación
     * @return array
     * @throws \RuntimeException Si los datos no son válidos
     */
    protected function getJsonRequest(array $rules = []): array
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true) ?? [];

        if (!empty($rules) && $this->validator) {
            if (!$this->validator->validate($data, $rules)) {
                throw new \RuntimeException(implode(', ', $this->validator->getErrors()));
            }
        }

        return $data;
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
}
