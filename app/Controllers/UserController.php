<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\View;
use App\Models\AuditLog;
use App\Models\User;

class UserController
{
    public function index(): void
    {
        View::render('pages/users', ['title' => 'Usuarios', 'users' => User::all(), 'roles' => User::roles()]);
    }

    public function store(): void
    {
        CSRF::verify($_POST['_csrf'] ?? null);
        User::create([
            'role_id' => (int)$_POST['role_id'],
            'name' => trim($_POST['name']),
            'email' => trim($_POST['email']),
            'phone' => trim($_POST['phone']),
            'password_hash' => password_hash($_POST['password'], PASSWORD_BCRYPT),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ]);
        AuditLog::add((int)Auth::user()['id'], 'users', 0, 'create', $_POST, $_SERVER['REMOTE_ADDR'] ?? '-');
        redirect('/users');
    }

    public function update(): void
    {
        CSRF::verify($_POST['_csrf'] ?? null);
        $id = (int)$_POST['id'];
        User::update($id, [
            'role_id' => (int)$_POST['role_id'],
            'name' => trim($_POST['name']),
            'email' => trim($_POST['email']),
            'phone' => trim($_POST['phone']),
            'password_hash' => !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ]);
        AuditLog::add((int)Auth::user()['id'], 'users', $id, 'update', $_POST, $_SERVER['REMOTE_ADDR'] ?? '-');
        redirect('/users');
    }
}
