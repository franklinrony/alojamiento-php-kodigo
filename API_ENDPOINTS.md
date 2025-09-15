# API Endpoints Documentation

## Autenticación

### POST /api/auth/register
Registra un nuevo usuario.

**Body:**
```json
{
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Juan Pérez",
        "email": "juan@example.com"
    },
    "message": "Usuario registrado exitosamente"
}
```

### POST /api/auth/login
Inicia sesión de usuario.

**Body:**
```json
{
    "email": "juan@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Juan Pérez",
        "email": "juan@example.com"
    },
    "message": "Inicio de sesión exitoso"
}
```

### POST /api/auth/logout
Cierra la sesión del usuario autenticado.

**Headers:** Requiere autenticación

**Response:**
```json
{
    "success": true,
    "message": "Sesión cerrada exitosamente"
}
```

## Alojamientos

### GET /api/accommodations
Lista todos los alojamientos con filtros opcionales.

**Query Parameters:**
- `location` (opcional): Filtrar por ubicación
- `min_price` (opcional): Precio mínimo
- `max_price` (opcional): Precio máximo
- `page` (opcional): Número de página (default: 1)
- `limit` (opcional): Elementos por página (default: 12)

**Response:**
```json
{
    "success": true,
    "data": {
        "accommodations": [
            {
                "id": 1,
                "name": "Hotel Central",
                "description": "Hotel en el centro de la ciudad",
                "location": "Madrid",
                "price": 120.50,
                "created_at": "2024-01-01 10:00:00",
                "updated_at": "2024-01-01 10:00:00"
            }
        ],
        "pagination": {
            "current_page": 1,
            "total_pages": 5,
            "total_items": 50,
            "items_per_page": 12,
            "has_previous": false,
            "has_next": true
        }
    },
    "message": "Alojamientos obtenidos exitosamente"
}
```

### GET /api/accommodations/{id}
Obtiene un alojamiento específico.

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Hotel Central",
        "description": "Hotel en el centro de la ciudad",
        "location": "Madrid",
        "price": 120.50,
        "created_at": "2024-01-01 10:00:00",
        "updated_at": "2024-01-01 10:00:00"
    },
    "message": "Alojamiento obtenido exitosamente"
}
```

### POST /api/accommodations
Crea un nuevo alojamiento.

**Headers:** Requiere autenticación y permiso `create_accommodation`

**Body:**
```json
{
    "name": "Hotel Central",
    "description": "Hotel en el centro de la ciudad",
    "location": "Madrid",
    "price": 120.50
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Hotel Central",
        "description": "Hotel en el centro de la ciudad",
        "location": "Madrid",
        "price": 120.50,
        "created_at": "2024-01-01 10:00:00",
        "updated_at": "2024-01-01 10:00:00"
    },
    "message": "Alojamiento creado exitosamente"
}
```

### PUT /api/accommodations/{id}
Actualiza un alojamiento existente.

**Headers:** Requiere autenticación y permiso `update_accommodation`

**Body:**
```json
{
    "name": "Hotel Central Actualizado",
    "description": "Hotel renovado en el centro de la ciudad",
    "location": "Madrid",
    "price": 130.00
}
```

### DELETE /api/accommodations/{id}
Elimina un alojamiento.

**Headers:** Requiere autenticación y permiso `delete_accommodation`

**Response:**
```json
{
    "success": true,
    "message": "Alojamiento eliminado exitosamente"
}
```

## Reservas

### GET /api/reservations
Lista las reservas del usuario autenticado.

**Headers:** Requiere autenticación

**Query Parameters:**
- `status` (opcional): Filtrar por estado
- `page` (opcional): Número de página (default: 1)
- `limit` (opcional): Elementos por página (default: 10)

**Response:**
```json
{
    "success": true,
    "data": {
        "reservations": [
            {
                "id": 1,
                "accommodation_id": 1,
                "accommodation_name": "Hotel Central",
                "check_in_date": "2024-02-01",
                "check_out_date": "2024-02-05",
                "guests": 2,
                "total_price": 482.00,
                "status": "confirmed",
                "created_at": "2024-01-15 10:00:00",
                "updated_at": "2024-01-15 10:00:00"
            }
        ],
        "pagination": {
            "current_page": 1,
            "total_pages": 3,
            "total_items": 25,
            "items_per_page": 10,
            "has_previous": false,
            "has_next": true
        }
    },
    "message": "Reservas obtenidas exitosamente"
}
```

### GET /api/reservations/{id}
Obtiene una reserva específica.

**Headers:** Requiere autenticación

### POST /api/reservations
Crea una nueva reserva.

**Headers:** Requiere autenticación

**Body:**
```json
{
    "accommodation_id": 1,
    "check_in_date": "2024-02-01",
    "check_out_date": "2024-02-05",
    "guests": 2
}
```

### PUT /api/reservations/{id}
Actualiza una reserva existente.

**Headers:** Requiere autenticación

**Nota:** Esta funcionalidad está pendiente de implementación.

### POST /api/reservations/{id}/cancel
Cancela una reserva.

**Headers:** Requiere autenticación

**Response:**
```json
{
    "success": true,
    "message": "Reserva cancelada exitosamente"
}
```

### GET /api/reservations/availability/{id}
Verifica la disponibilidad de un alojamiento.

**Query Parameters:**
- `check_in`: Fecha de entrada (YYYY-MM-DD)
- `check_out`: Fecha de salida (YYYY-MM-DD)

**Response:**
```json
{
    "success": true,
    "data": {
        "available": true,
        "accommodation_id": 1,
        "check_in": "2024-02-01",
        "check_out": "2024-02-05",
        "message": "Disponible"
    },
    "message": "Disponibilidad verificada"
}
```

### GET /api/reservations/stats
Obtiene estadísticas de reservas del usuario.

**Headers:** Requiere autenticación

**Response:**
```json
{
    "success": true,
    "data": {
        "total_reservations": 10,
        "active_reservations": 3,
        "cancelled_reservations": 1,
        "completed_reservations": 6,
        "total_spent_active": 1200.00,
        "total_spent_all": 3500.00
    },
    "message": "Estadísticas obtenidas exitosamente"
}
```

## Usuarios

### GET /api/user/profile
Obtiene el perfil del usuario autenticado.

**Headers:** Requiere autenticación

### PUT /api/user/profile
Actualiza el perfil del usuario autenticado.

**Headers:** Requiere autenticación

### POST /api/user/role
Asigna un rol a un usuario.

**Headers:** Requiere autenticación y permiso `assign_roles`

## Códigos de Estado HTTP

- `200`: Éxito
- `201`: Creado exitosamente
- `400`: Error en la petición
- `401`: No autorizado
- `404`: No encontrado
- `405`: Método no permitido
- `500`: Error interno del servidor
- `501`: No implementado

## Formato de Respuesta

Todas las respuestas siguen el formato:

```json
{
    "success": true|false,
    "data": {}, // Solo en respuestas exitosas
    "message": "Mensaje descriptivo",
    "errors": [] // Solo en respuestas de error
}
```

## Autenticación

La autenticación se maneja mediante sesiones. Los endpoints que requieren autenticación deben incluir las cookies de sesión en la petición.

## CORS

El servidor está configurado para manejar peticiones CORS y preflight OPTIONS requests.
