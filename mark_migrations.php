<?php

$pdo = new PDO(
    "mysql:host=localhost;dbname=alojamientos;charset=utf8mb4",
    "alojamientos",
    "alojamientos"
);

// Marcar las migraciones anteriores como ejecutadas
$sql = "INSERT INTO phinxlog (version, migration_name, start_time, end_time) 
        VALUES 
        (20250907170104, 'CreatePermissionsTables', NOW(), NOW()),
        (20250907170105, 'UpdateRolesPermissionsStructure', NOW(), NOW()),
        (20250907170106, 'UpdatePermissionRoleTable', NOW(), NOW())";

try {
    $pdo->exec($sql);
    echo "Migraciones anteriores marcadas como ejecutadas\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
