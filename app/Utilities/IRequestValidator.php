<?php

namespace App\Utilities;

/**
 * Interface IRequestValidator
 * Interfaz para validación de peticiones
 */
interface IRequestValidator
{
    /**
     * Valida los datos contra un conjunto de reglas
     *
     * @param array $data Los datos a validar
     * @param array $rules Las reglas de validación
     * @return bool
     */
    public function validate(array $data, array $rules): bool;

    /**
     * Obtiene los errores de la última validación
     *
     * @return array
     */
    public function getErrors(): array;

    /**
     * Limpia los datos según las reglas especificadas
     *
     * @param array $data
     * @return array
     */
    public function sanitize(array $data): array;
}
