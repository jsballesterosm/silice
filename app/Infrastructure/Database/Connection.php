<?php
// app/Infrastructure/Connection.php

// Patron de diseño utilizado: Factory Method
// tenemos una unica responsabilidad: crear y configurar la conexion a la base de datos.

namespace App\Infrastructure\Database;

use PDO;
use PDOException;

class Connection
{
    public static function make(): PDO
    {
        $config = require __DIR__ . '/../../../config_mysql.php';

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;port=%d;charset=%s',
            $config['host'],
            $config['database'],
            $config['port'],
            $config['charset']
        );

        return new PDO(
            $dsn,
            $config['user'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }
}
