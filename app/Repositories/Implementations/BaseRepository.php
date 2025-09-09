<?php

namespace App\Repositories\Implementations;

use App\Models\Model;
use App\Repositories\IRepository;
use PDO;
use PDOException;

/**
 * Class BaseRepository
 * Implementación base para todos los repositorios
 */
abstract class BaseRepository implements IRepository
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var PDO
     */
    protected $db;

    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->initializeDatabase();
    }

    /**
     * Inicializa la conexión a la base de datos
     */
    protected function initializeDatabase(): void
    {
        try {
            $host = getenv('DB_HOST') ?: 'localhost';
            $dbname = getenv('DB_NAME') ?: 'alojamientos';
            $username = getenv('DB_USER') ?: 'root';
            $password = getenv('DB_PASS') ?: '';
            
            $this->db = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            // Log del error para debugging
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            
            // Crear una excepción más específica
            throw new \App\Exceptions\DatabaseConnectionException(
                "No se puede conectar a la base de datos. Verifica que el servidor de base de datos esté ejecutándose y que las credenciales sean correctas.",
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function find($id)
    {
        $table = $this->getTableName();
        $stmt = $this->db->prepare("SELECT * FROM $table WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        $result = $stmt->fetch();
        if (!$result) {
            return null;
        }

        return $this->mapToModel($result);
    }

    /**
     * @inheritDoc
     */
    public function all()
    {
        $table = $this->getTableName();
        $stmt = $this->db->query("SELECT * FROM $table");
        $results = $stmt->fetchAll();

        return array_map([$this, 'mapToModel'], $results);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data)
    {
        $table = $this->getTableName();
        $fields = array_keys($data);
        $placeholders = array_map(function($field) {
            return ":$field";
        }, $fields);

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            implode(', ', $fields),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        $id = $this->db->lastInsertId();
        return $this->find($id);
    }

    /**
     * @inheritDoc
     */
    public function update($id, array $data)
    {
        $table = $this->getTableName();
        $fields = array_map(function($field) {
            return "$field = :$field";
        }, array_keys($data));

        $sql = sprintf(
            "UPDATE %s SET %s WHERE id = :id",
            $table,
            implode(', ', $fields)
        );

        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute($data);

        return $success ? $this->find($id) : null;
    }

    /**
     * @inheritDoc
     */
    public function delete($id)
    {
        $table = $this->getTableName();
        $stmt = $this->db->prepare("DELETE FROM $table WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Obtiene el nombre de la tabla basado en el nombre del modelo
     *
     * @return string
     */
    protected function getTableName(): string
    {
        $modelClass = get_class($this->model);
        $parts = explode('\\', $modelClass);
        $modelName = end($parts);
        return strtolower($modelName) . 's';
    }

    /**
     * Mapea un array de datos a una instancia del modelo
     *
     * @param array $data
     * @return Model
     */
    protected function mapToModel(array $data): Model
    {
        $modelClass = get_class($this->model);
        $model = new $modelClass();

        // Asignar el ID primero si existe
        if (isset($data['id'])) {
            $model->setId($data['id']);
        }

        // Usar el método fill para asignar las propiedades
        $model->fill($data);

        // Log para depuración
        error_log("Mapeando modelo: " . print_r($data, true));

        return $model;
    }

    /**
     * Obtiene el nombre del método setter para una propiedad
     *
     * @param string $property
     * @return string
     */
    protected function getSetterMethod(string $property): string
    {
        return 'set' . str_replace('_', '', ucwords($property, '_'));
    }
}
