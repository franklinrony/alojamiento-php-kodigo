<?php
/**
 * Cache Manager para FastRoute
 * 
 * Script CLI para gestionar el cache de FastRoute
 * 
 * Uso:
 * php cache_manager.php clear    - Limpia el cache
 * php cache_manager.php info     - Muestra información del cache
 * php cache_manager.php status   - Muestra el estado del cache
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Utilities\Router;
use App\Utilities\PathHelper;
use App\Config\DiConfig;

// Cargar variables de entorno
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

$command = $argv[1] ?? 'status';
$target = $argv[2] ?? 'all'; // all, fastroute, di

switch ($command) {
    case 'clear':
        echo "🧹 Limpiando cache...\n\n";
        
        if ($target === 'all' || $target === 'fastroute') {
            echo "📦 Limpiando cache de FastRoute...\n";
            if (Router::clearCache()) {
                echo "   ✅ Cache de FastRoute limpiado\n";
            } else {
                echo "   ❌ Error al limpiar cache de FastRoute\n";
            }
        }
        
        if ($target === 'all' || $target === 'di') {
            echo "🏗️  Limpiando cache del contenedor DI...\n";
            if (DiConfig::clearCache()) {
                echo "   ✅ Cache del contenedor DI limpiado\n";
            } else {
                echo "   ❌ Error al limpiar cache del contenedor DI\n";
            }
        }
        
        echo "\n✅ Limpieza completada\n";
        break;
        
    case 'info':
        echo "📊 Información detallada del cache:\n\n";
        
        if ($target === 'all' || $target === 'fastroute') {
            echo "🚀 Cache de FastRoute:\n";
            $info = Router::getCacheInfo();
            echo "   Existe: " . ($info['exists'] ? 'Sí' : 'No') . "\n";
            echo "   Tamaño: " . $info['size'] . " bytes\n";
            echo "   Modificado: " . ($info['modified'] ?? 'N/A') . "\n";
            echo "   Ruta: " . $info['path'] . "\n\n";
        }
        
        if ($target === 'all' || $target === 'di') {
            echo "🏗️  Cache del contenedor DI:\n";
            $info = DiConfig::getCacheInfo();
            echo "   Existe: " . ($info['exists'] ? 'Sí' : 'No') . "\n";
            echo "   Archivos: " . count($info['files']) . "\n";
            echo "   Tamaño total: " . $info['totalSize'] . " bytes\n";
            echo "   Ruta: " . $info['path'] . "\n";
            
            if (!empty($info['files'])) {
                echo "   📁 Archivos:\n";
                foreach ($info['files'] as $file) {
                    echo "      - " . $file['name'] . " (" . $file['size'] . " bytes, " . $file['modified'] . ")\n";
                }
            }
            echo "\n";
        }
        break;
        
    case 'status':
    default:
        echo "🔍 Estado del sistema de cache:\n\n";
        
        if ($target === 'all' || $target === 'fastroute') {
            echo "🚀 FastRoute:\n";
            if (Router::cacheExists()) {
                $info = Router::getCacheInfo();
                echo "   ✅ Cache activo (" . $info['size'] . " bytes)\n";
            } else {
                echo "   ❌ Cache no existe\n";
            }
        }
        
        if ($target === 'all' || $target === 'di') {
            echo "🏗️  Contenedor DI:\n";
            if (DiConfig::cacheExists()) {
                $info = DiConfig::getCacheInfo();
                echo "   ✅ Cache activo (" . $info['totalSize'] . " bytes, " . count($info['files']) . " archivos)\n";
            } else {
                echo "   ❌ Cache no existe\n";
            }
        }
        
        // Mostrar configuración
        echo "\n⚙️  Configuración:\n";
        echo "   APP_DEBUG: " . ($_ENV['APP_DEBUG'] ?? 'no definido') . "\n";
        echo "   FASTROUTE_CACHE_ENABLED: " . ($_ENV['FASTROUTE_CACHE_ENABLED'] ?? 'no definido') . "\n";
        echo "   DI_CACHE_ENABLED: " . ($_ENV['DI_CACHE_ENABLED'] ?? 'no definido') . "\n";
        
        $fastRouteCacheEnabled = $_ENV['APP_DEBUG'] !== 'true' && $_ENV['FASTROUTE_CACHE_ENABLED'] !== 'false';
        $diCacheEnabled = $_ENV['APP_DEBUG'] !== 'true' && $_ENV['DI_CACHE_ENABLED'] !== 'false';
        
        echo "   Cache FastRoute habilitado: " . ($fastRouteCacheEnabled ? 'Sí' : 'No') . "\n";
        echo "   Cache DI habilitado: " . ($diCacheEnabled ? 'Sí' : 'No') . "\n";
        break;
}

echo "\n📖 Comandos disponibles:\n";
echo "   php cache_manager.php status                    - Mostrar estado del cache\n";
echo "   php cache_manager.php status fastroute          - Estado solo de FastRoute\n";
echo "   php cache_manager.php status di                 - Estado solo del contenedor DI\n";
echo "   php cache_manager.php info                      - Información detallada\n";
echo "   php cache_manager.php info fastroute            - Info solo de FastRoute\n";
echo "   php cache_manager.php info di                   - Info solo del contenedor DI\n";
echo "   php cache_manager.php clear                     - Limpiar todo el cache\n";
echo "   php cache_manager.php clear fastroute           - Limpiar solo FastRoute\n";
echo "   php cache_manager.php clear di                  - Limpiar solo contenedor DI\n";
