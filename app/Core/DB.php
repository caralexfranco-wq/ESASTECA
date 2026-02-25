<?php

namespace App\Core;

use PDO;
use PDOException;

class DB
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo) {
            return self::$pdo;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            env('DATABASE_HOST', '127.0.0.1'),
            env('DATABASE_PORT', '3306'),
            env('DATABASE_NAME', 'expedientes_app')
        );

        try {
            self::$pdo = new PDO($dsn, env('DATABASE_USER', 'root'), env('DATABASE_PASS', ''), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            Logger::error('DB connection failed: ' . $e->getMessage());
            die('No se pudo conectar a la base de datos. Revisa .env');
        }

        return self::$pdo;
    }
}
