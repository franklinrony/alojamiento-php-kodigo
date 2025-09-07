<?php

namespace App\Utilities;

/**
 * Class RequestValidator
 * Implementación del validador de peticiones
 */
class RequestValidator implements IRequestValidator
{
    /**
     * @var array
     */
    private array $errors = [];

    /**
     * @inheritDoc
     */
    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $rule) {
            if (isset($rule['required']) && $rule['required'] && !isset($data[$field])) {
                $this->errors[$field] = "El campo {$field} es requerido";
                continue;
            }

            if (!isset($data[$field])) {
                continue;
            }

            if (isset($rule['type'])) {
                if (!$this->validateType($data[$field], $rule['type'])) {
                    $this->errors[$field] = "El campo {$field} debe ser de tipo {$rule['type']}";
                }
            }

            if (isset($rule['min']) && strlen($data[$field]) < $rule['min']) {
                $this->errors[$field] = "El campo {$field} debe tener al menos {$rule['min']} caracteres";
            }

            if (isset($rule['max']) && strlen($data[$field]) > $rule['max']) {
                $this->errors[$field] = "El campo {$field} debe tener máximo {$rule['max']} caracteres";
            }

            if (isset($rule['email']) && $rule['email'] && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field] = "El campo {$field} debe ser un email válido";
            }
        }

        return empty($this->errors);
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @inheritDoc
     */
    public function sanitize(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    /**
     * Valida el tipo de un valor
     *
     * @param mixed $value
     * @param string $type
     * @return bool
     */
    private function validateType($value, string $type): bool
    {
        switch ($type) {
            case 'string':
                return is_string($value);
            case 'int':
            case 'integer':
                return is_numeric($value) && (int)$value == $value;
            case 'float':
            case 'double':
                return is_numeric($value);
            case 'bool':
            case 'boolean':
                return is_bool($value);
            case 'array':
                return is_array($value);
            default:
                return true;
        }
    }
}
