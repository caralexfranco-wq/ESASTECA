<?php

namespace App\Core;

use Dotenv\Dotenv;

class Env
{
    public static function load(): void
    {
        $root = dirname(__DIR__, 2);
        $envFile = $root . '/.env';

        if (class_exists(Dotenv::class) && file_exists($envFile)) {
            Dotenv::createImmutable($root)->safeLoad();
            return;
        }

        if (!file_exists($envFile)) {
            return;
        }

        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
                continue;
            }
            [$k, $v] = array_map('trim', explode('=', $line, 2));
            $_ENV[$k] = trim($v, "\"'");
            $_SERVER[$k] = $_ENV[$k];
        }
    }
}
