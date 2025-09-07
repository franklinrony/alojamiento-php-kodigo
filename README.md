# Alojamientos Kodigo

Aplicación web en PHP 7.4+ para la gestión de alojamientos turísticos.

## Estructura del proyecto
- app/Controllers
- app/Models
- app/Repositories
- app/Services
- app/Views
- config/database
- config/routes
- public/
- var/cache
- var/logs
- vendor/

## Descripción
Permite a usuarios crear cuentas, seleccionar y gestionar alojamientos, y a administradores agregar nuevas opciones. Incluye autenticación, roles, migraciones con Phinx, logs con Monolog, y frontend con Bootstrap 5.

## Instalación rápida
1. Clona el repositorio
2. Copia `.env.example` a `.env` y configura tus variables
3. Instala dependencias con Composer
4. Ejecuta migraciones con Phinx

## Requerimientos
- PHP 7.4+
- MySQL
- Composer

## Licencia
MIT
