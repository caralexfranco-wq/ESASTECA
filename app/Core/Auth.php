<?php

namespace App\Core;

use App\Models\User;

class Auth
{
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return !empty($_SESSION['user']);
    }

    public static function attempt(string $email, string $password, string $ip): bool
    {
        if (!self::canLogin($ip)) {
            return false;
        }

        $user = User::findByEmail($email);
        if (!$user || !$user['is_active']) {
            self::recordLoginAttempt($ip, false);
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            self::recordLoginAttempt($ip, false);
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role_key'],
        ];
        self::recordLoginAttempt($ip, true);

        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function hasRole(array|string $roles): bool
    {
        $roles = (array)$roles;
        return in_array(self::user()['role'] ?? '', $roles, true);
    }

    private static function attemptsFile(): string
    {
        return storage_path('logs/login_attempts.json');
    }

    private static function canLogin(string $ip): bool
    {
        $attempts = self::loadAttempts();
        $window = time() - 900;
        $ipAttempts = array_filter($attempts[$ip] ?? [], fn($ts) => $ts >= $window);
        return count($ipAttempts) < 5;
    }

    private static function recordLoginAttempt(string $ip, bool $success): void
    {
        $attempts = self::loadAttempts();
        if ($success) {
            $attempts[$ip] = [];
        } else {
            $attempts[$ip][] = time();
        }
        file_put_contents(self::attemptsFile(), json_encode($attempts));
    }

    private static function loadAttempts(): array
    {
        $file = self::attemptsFile();
        if (!file_exists($file)) {
            return [];
        }
        return json_decode((string) file_get_contents($file), true) ?: [];
    }
}
