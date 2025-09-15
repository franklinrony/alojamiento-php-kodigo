# Sistema de Logging

Este documento describe el sistema de logging implementado en la aplicación.

## Configuración

El sistema de logging se configura a través de variables de entorno:

- `LOG_ENABLED`: Habilita o deshabilita el logging (true/false)
- `LOG_LEVEL`: Nivel mínimo de logging (DEBUG, INFO, WARNING, ERROR, CRITICAL)

## Archivos de Log

Los logs se almacenan en la carpeta `var/logs/` con los siguientes archivos:

- `app.log`: Log principal de la aplicación
- `error.log`: Errores críticos
- `user_activity.log`: Actividades de usuarios
- `reservations.log`: Actividades de reservas
- `database.log`: Errores de base de datos
- `security.log`: Eventos de seguridad
- `api.log`: Actividades de API

## Rotación de Archivos

Los archivos de log se rotan automáticamente:

- `app.log`: 30 días
- `error.log`: 90 días
- `user_activity.log`: 90 días
- `reservations.log`: 365 días
- `database.log`: 30 días
- `security.log`: 180 días
- `api.log`: 30 días

## Uso del Sistema de Logging

### En Controladores

```php
// Obtener el logger del contenedor
$logger = $this->container->get(\App\Services\ILoggerService::class);

// Log de información
$logger->info("Usuario autenticado", ['user_id' => $userId]);

// Log de error
$logger->error("Error en operación", ['error' => $e->getMessage()]);
```

### En Servicios

```php
// El logger se inyecta en el constructor
public function __construct(ILoggerService $logger) {
    $this->logger = $logger;
}

// Usar el logger
$this->logger->warning("Operación fallida", ['details' => $details]);
```

### En Repositorios

```php
// Usar el método helper del BaseRepository
$this->logDatabaseError("Error en consulta", [
    'query' => $sql,
    'error' => $e->getMessage()
]);
```

## Métodos Especializados

### Logging de Actividades de Usuario

```php
$logger->logUserActivity($userId, 'login', ['ip' => $ip]);
```

### Logging de Actividades de Reservas

```php
$logger->logReservationActivity($userId, $reservationId, 'created', $details);
```

### Logging de Errores de Base de Datos

```php
$logger->logDatabaseError("Error de conexión", ['host' => $host]);
```

### Logging de Eventos de Seguridad

```php
$logger->logSecurityEvent("Intento de acceso no autorizado", ['ip' => $ip]);
```

### Logging de Actividades de API

```php
$logger->logApiActivity('/api/users', 'GET', 200, ['user_id' => $userId]);
```

### Logging de Excepciones

```php
$logger->logException($exception, ['context' => 'additional_info']);
```

## Formato de Logs

Los logs incluyen:

- Timestamp
- Nivel de log
- Mensaje
- Contexto (datos adicionales)
- Información del sistema (IP, User Agent, etc.)

Ejemplo:
```
[2024-01-15 10:30:45] app.INFO: Usuario autenticado {"user_id":123,"ip":"192.168.1.1","user_agent":"Mozilla/5.0..."}
```

## Configuración de Producción

Para producción, se recomienda:

1. Establecer `LOG_LEVEL=WARNING` o `ERROR`
2. Configurar `LOG_ENABLED=true`
3. Monitorear el tamaño de los archivos de log
4. Implementar limpieza automática de logs antiguos

## Monitoreo

Los logs se pueden monitorear usando herramientas como:

- `tail -f var/logs/app.log`
- Logrotate para rotación automática
- Herramientas de monitoreo como ELK Stack
