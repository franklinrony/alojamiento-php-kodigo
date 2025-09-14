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
            // Log del error usando el sistema de logging
            if (class_exists('\App\Services\ILoggerService')) {
                try {
                    $container = \App\Utilities\DiContainer::getInstance();
                    if ($container->has(\App\Services\ILoggerService::class)) {
                        $logger = $container->get(\App\Services\ILoggerService::class);
                        $logger->logDatabaseError("Error de conexión a la base de datos", [
                            'message' => $e->getMessage(),
                            'code' => $e->getCode(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        ]);
                    }
                } catch (\Exception $logError) {
                    // Fallback a error_log si el sistema de logging falla
                    error_log("Error de conexión a la base de datos: " . $e->getMessage());
                }
            } else {
                error_log("Error de conexión a la base de datos: " . $e->getMessage());
            }
            
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
    public function all(?int $limit = null, ?int $offset = null)
    {
        $table = $this->getTableName();
        $sql = "SELECT * FROM $table";
        
        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            if ($offset !== null) {
                $sql .= " OFFSET :offset";
            }
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            
            if ($limit !== null) {
                $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
                if ($offset !== null) {
                    $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
                }
            }
            
            $stmt->execute();
            
            $results = $stmt->fetchAll();

            return array_map([$this, 'mapToModel'], $results);
        } catch (\PDOException $e) {
            $this->logDatabaseError("Error en consulta all()", [
                'sql' => $sql,
                'limit' => $limit,
                'offset' => $offset,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw $e;
        }
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

        // Log para depuración SOLO en modo debug y para errores
        if ($_ENV['APP_DEBUG'] === 'true' && class_exists('\App\Services\ILoggerService')) {
            try {
                $container = \App\Utilities\DiContainer::getInstance();
                if ($container->has(\App\Services\ILoggerService::class)) {
                    $logger = $container->get(\App\Services\ILoggerService::class);
                    $logger->debug("Mapeando modelo", [
                        'model_class' => get_class($model),
                        'data' => $data
                    ]);
                }
            } catch (\Exception $logError) {
                // Fallback silencioso para evitar errores en el mapeo
            }
        }

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

    /**
     * Log de errores de base de datos
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logDatabaseError(string $message, array $context = []): void
    {
        if (class_exists('\App\Services\ILoggerService')) {
            try {
                $container = \App\Utilities\DiContainer::getInstance();
                if ($container->has(\App\Services\ILoggerService::class)) {
                    $logger = $container->get(\App\Services\ILoggerService::class);
                    $logger->logDatabaseError($message, $context);
                }
            } catch (\Exception $logError) {
                // Fallback a error_log si el sistema de logging falla
                error_log("Database Error: {$message} - " . json_encode($context));
            }
        } else {
            error_log("Database Error: {$message} - " . json_encode($context));
        }
    }
}
