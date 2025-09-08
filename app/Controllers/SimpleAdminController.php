<?php

namespace App\Controllers;

class SimpleAdminController
{
    public function index(): void
    {
        echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Admin Simple</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #e8f5e8; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2d5a2d; }
        .success { color: #28a745; font-weight: bold; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🏠 Admin Simple - Sin Dependencias</h1>
        <p><strong>Estado:</strong> <span class='success'>✓ CONTROLADOR SIMPLE FUNCIONA</span></p>
        <p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>
        <p><strong>Clase:</strong> SimpleAdminController</p>
        <p><strong>Método:</strong> index()</p>
        
        <h2>Diagnóstico:</h2>
        <ul>
            <li>✓ Router funciona</li>
            <li>✓ Controlador simple funciona</li>
            <li>✓ Sin dependencias complejas</li>
        </ul>
        
        <h2>Enlaces:</h2>
        <ul>
            <li><a href='/admin/accommodations'>Gestionar Alojamientos</a></li>
            <li><a href='/admin/accommodation/add'>Agregar Alojamiento</a></li>
            <li><a href='/'>Volver al Inicio</a></li>
        </ul>
        
        <p><strong>Si ves esto, el problema está en BaseController o Twig</strong></p>
    </div>
</body>
</html>";
    }
}
