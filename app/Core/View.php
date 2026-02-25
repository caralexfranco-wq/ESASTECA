<?php

namespace App\Core;

class View
{
    public static function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = base_path('views/' . $view . '.php');
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View {$view} not found");
        }
        include base_path('views/layout.php');
    }
}
