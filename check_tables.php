<?php

$pdo = new PDO(
    "mysql:host=localhost;dbname=alojamientos;charset=utf8mb4",
    "alojamientos",
    "alojamientos"
);

function showTables($pdo) {
    $stmt = $pdo->query("SHOW TABLES");
    echo "Tables:\n";
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo $row[0] . "\n";
    }
}

function describeTable($pdo, $table) {
    echo "\nDescribe $table:\n";
    $stmt = $pdo->query("DESCRIBE $table");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['Field']} {$row['Type']} {$row['Null']} {$row['Key']}\n";
    }
}

showTables($pdo);
describeTable($pdo, 'users');
describeTable($pdo, 'roles');
describeTable($pdo, 'permissions');
describeTable($pdo, 'permission_role');
