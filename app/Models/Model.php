<?php

namespace App\Models;

/**
 * Class Model
 * Clase base para todos los modelos
 */
abstract class Model
{
    /**
     * ID del registro
     *
     * @var int
     */
    protected $id;

    /**
     * Timestamp de creación
     *
     * @var string
     */
    protected $created_at;

    /**
     * Timestamp de actualización
     *
     * @var string
     */
    protected $updated_at;

    /**
     * Obtiene el ID del registro
     *
     * @return int
     */
    public function getId(): int
    {
        return (int) $this->id;
    }

    /**
     * Establece el ID del registro
     *
     * @param int $id
     * @return void
     */
    public function setId($id): void
    {
        $this->id = (int) $id;
    }

    /**
     * Obtiene la fecha de creación
     *
     * @return string
     */
    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    /**
     * Establece la fecha de creación
     *
     * @param string|null $createdAt
     */
    public function setCreatedAt(?string $createdAt): void
    {
        $this->created_at = $createdAt;
    }

    /**
     * Obtiene la fecha de actualización
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updated_at;
    }

    /**
     * Establece la fecha de actualización
     *
     * @param string|null $updatedAt
     */
    public function setUpdatedAt(?string $updatedAt): void
    {
        $this->updated_at = $updatedAt;
    }

    /**
     * Convierte un array asociativo en propiedades del modelo
     *
     * @param array $data
     * @return void
     */
    public function fill(array $data): void
    {
        foreach ($data as $key => $value) {
            // Evitar sobreescribir el ID si ya está establecido
            if ($key === 'id' && $this->id !== null) {
                continue;
            }

            // Intentar usar el setter si existe
            $setter = 'set' . str_replace('_', '', ucwords($key, '_'));
            if (method_exists($this, $setter)) {
                $this->$setter($value);
                continue;
            }

            // Si no hay setter pero la propiedad existe, asignarla directamente
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Convierte el modelo a un array
     *
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
