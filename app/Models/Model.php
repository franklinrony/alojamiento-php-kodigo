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
        return $this->id;
    }

    /**
     * Obtiene la fecha de creación
     *
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    /**
     * Obtiene la fecha de actualización
     *
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return $this->updated_at;
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
