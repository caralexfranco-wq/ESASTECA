<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\View;

class AuthController
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            redirect('/dashboard');
        }
        View::render('pages/login', ['title' => 'Acceso']);
    }

    public function login(): void
    {
        CSRF::verify($_POST['_csrf'] ?? null);
        $ok = Auth::attempt(trim($_POST['email'] ?? ''), $_POST['password'] ?? '', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
        if (!$ok) {
            $_SESSION['flash_error'] = 'Credenciales inválidas o demasiados intentos.';
            redirect('/login');
        }
        redirect('/dashboard');
    }

    public function logout(): void
    {
        CSRF::verify($_POST['_csrf'] ?? null);
        Auth::logout();
        redirect('/login');
    }
}
