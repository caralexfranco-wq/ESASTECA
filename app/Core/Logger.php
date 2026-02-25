<?php

namespace App\Core;

class Logger
{
    public static function error(string $message): void
    {
        $file = storage_path('logs/app.log');
        $line = '[' . date('Y-m-d H:i:s') . "] ERROR {$message}\n";
        file_put_contents($file, $line, FILE_APPEND);
    }

    public static function info(string $message): void
    {
        $file = storage_path('logs/app.log');
        $line = '[' . date('Y-m-d H:i:s') . "] INFO {$message}\n";
        file_put_contents($file, $line, FILE_APPEND);
    }
}
