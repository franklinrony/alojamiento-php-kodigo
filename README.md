## Alojamientos Kodigo

Aplicación web para la gestión de alojamientos turísticos construida en PHP con Twig, FastRoute, PHP‑DI, Monolog y Phinx para migraciones.

### Características principales
- Autenticación de usuarios (registro, login, logout) y sesiones.
- Roles y permisos (admin/usuario) con control de acceso por middlewares.
- Gestión de alojamientos y reservas (CRUD básico y vistas en Twig).
- Migraciones y seeders con Phinx.
- Logging con Monolog y archivos en `var/logs`.
- Arquitectura por capas: `Controllers`, `Services`, `Repositories`, `Models` y `Views`.

### Requisitos
- PHP 7.4 o superior (recomendado PHP 8.1+)
- Extensiones PHP comunes: pdo_mysql, mbstring, json, openssl, tokenizer
- MySQL 5.7+ / MariaDB 10.3+
- Composer 2+
- Servidor web apuntando a `public/` (Apache o Nginx)

### Estructura del proyecto (alto nivel)
- `app/` código de aplicación (MVC + DI + Middlewares)
- `config/database/` migraciones y seeders de Phinx
- `config/routes/` rutas web y API
- `public/` front controller `index.php`, assets
- `var/cache` y `var/logs` caché y logs de la app
- `vendor/` dependencias Composer

---

## Instalación local (desarrollo)

1) Clonar e instalar dependencias

```bash
git clone https://github.com/franklinrony/alojamiento-php-kodigo
cd Alojamientos-kodigo-PHP
composer install
```

2) Crear base de datos

```sql
CREATE DATABASE alojamientos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3) Configurar variables de entorno

Renombra o copia `\.env.example` a `\.env` en la raíz del proyecto y ajusta los valores. Si no existe `\.env.example`, crea `\.env` con al menos:

```ini
APP_NAME="Alojamientos Kodigo"
APP_ENV=development
APP_DEBUG=true

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=alojamientos
DB_USER=root
DB_PASS=

# Performance/caché (desarrollo: desactivar)
FASTROUTE_CACHE_ENABLED=false
DI_CACHE_ENABLED=false
TWIG_CACHE_ENABLED=false

# Logging
LOG_ENABLED=true
LOG_LEVEL=info
```

4) Ejecutar migraciones y seeders

```bash
# Windows (desde la raíz del proyecto)
vendor\bin\phinx.bat migrate -e development -c phinx.php
vendor\bin\phinx.bat seed:run -e development -c phinx.php -s UserSeeder

# Linux/macOS
vendor/bin/phinx migrate -e development -c phinx.php
vendor/bin/phinx seed:run -e development -c phinx.php -s UserSeeder
```

5) Entorno de desarrollo recomendado en Windows con XAMPP (VirtualHost)

Se recomienda usar Apache de XAMPP con VirtualHost y `mod_rewrite` activado en lugar del servidor embebido de PHP. El servidor embebido suele desconectarse bajo carga por los recursos que requiere el ruteo, Twig y DI.

Pasos:

1. Activar VirtualHosts y `mod_rewrite` en Apache

   - Abrir `C:\xampp\apache\conf\httpd.conf` y verificar:
     - La línea de inclusión de vhosts no esté comentada:
       `Include conf/extra/httpd-vhosts.conf`
     - El módulo de rewrite esté habilitado (sin `#`):
       `LoadModule rewrite_module modules/mod_rewrite.so`

2. Crear el VirtualHost

   Editar `C:\xampp\apache\conf\extra\httpd-vhosts.conf` y añadir:

   ```apache
   <VirtualHost *:80>
       ServerName alojamientos.local
       DocumentRoot "C:/xampp/htdocs/Alojamientos-kodigo-PHP/public"

       <Directory "C:/xampp/htdocs/Alojamientos-kodigo-PHP/public">
           AllowOverride All
           Require all granted
       </Directory>

       ErrorLog "logs/alojamientos_error.log"
       CustomLog "logs/alojamientos_access.log" combined
   </VirtualHost>
   ```

3. Agregar entrada en el archivo hosts de Windows

   - Abrir como administrador `C:\Windows\System32\drivers\etc\hosts` y añadir:
     `127.0.0.1    alojamientos.local`

4. Reiniciar Apache desde el XAMPP Control Panel

5. Probar en el navegador

   - Visitar `http://alojamientos.local/`

6. Asegurar reescrituras (`.htaccess`)

   Si fuera necesario, colocar un `.htaccess` en `public/` con:

   ```apache
   Options -MultiViews
   RewriteEngine On
   RewriteBase /
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^ index.php [L]
   ```

Notas:
- Este enfoque evita problemas de desconexión del servidor embebido y replica mejor un entorno real.
- Asegúrate de que el `DocumentRoot` apunte a la carpeta `public/`.

---

## Usuarios de prueba (seeders)

Se crean automáticamente con `UserSeeder`:

- Admin: `admin@demo.com` / contraseña: `qwerty`
- Usuario: `user@demo.com` / contraseña: `qwerty`

Permisos y roles:
- Rol `admin` con permisos: `access-admin`, `manage-users`, `manage-accommodations`, `view-accommodations`.
- Rol `user` con permiso: `view-accommodations`.

Rutas clave:
- Inicio: `/`
- Login: `/auth/login`
- Panel admin: `/admin` (requiere login y permiso `access-admin`)

---

## Comandos útiles

```bash
# Migraciones (desarrollo)
vendor/bin/phinx migrate -e development -c phinx.php

# Seeders (desarrollo)
vendor/bin/phinx seed:run -e development -c phinx.php -s UserSeeder

# Crear nueva migración
vendor/bin/phinx create AddSomethingToTables -c phinx.php

# Ejecutar tests
vendor/bin/phpunit
```

En Windows usa `vendor\bin\phinx.bat` y `vendor\bin\phpunit.bat`.

---

## (Nota sobre producción)
Por las limitaciones de recursos en equipos locales, se recomienda trabajar en desarrollo con XAMPP (VirtualHost) y, cuando corresponda, desplegar en un servidor real (VPS/Cloud). La guía de producción se omite aquí a petición; si la necesitas, puedo volver a incluirla.

---

## Solución de problemas
- "Error de conexión a la base de datos": verifica `DB_HOST/DB_NAME/DB_USER/DB_PASS` y que el servicio MySQL esté activo.
- Pantalla en blanco en producción: revisa logs en `var/logs/` y desactiva `APP_DEBUG`.
- Rutas que devuelven 404 en Apache: confirma que `DocumentRoot` apunta a `public/` y `AllowOverride All` habilita `.htaccess`.

---

## Licencia
MIT
