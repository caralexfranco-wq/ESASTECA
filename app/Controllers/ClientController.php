<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\View;
use App\Models\AuditLog;
use App\Models\Client;

class ClientController
{
    public function index(): void
    {
        View::render('pages/clients', ['title' => 'Clientes', 'clients' => Client::all()]);
    }

    public function store(): void
    {
        CSRF::verify($_POST['_csrf'] ?? null);
        Client::create([
            'type' => $_POST['type'],
            'razon_social' => trim($_POST['razon_social']),
            'rfc' => trim($_POST['rfc']),
            'contacto' => trim($_POST['contacto']),
            'email' => trim($_POST['email']),
            'phone' => trim($_POST['phone']),
            'address' => trim($_POST['address']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ]);
        $id = (int)Client::active()[0]['id'];
        AuditLog::add((int)Auth::user()['id'], 'clients', $id, 'create', $_POST, $_SERVER['REMOTE_ADDR'] ?? '-');
        redirect('/clients');
    }

    public function update(): void
    {
        CSRF::verify($_POST['_csrf'] ?? null);
        $id = (int)$_POST['id'];
        Client::update($id, [
            'type' => $_POST['type'],
            'razon_social' => trim($_POST['razon_social']),
            'rfc' => trim($_POST['rfc']),
            'contacto' => trim($_POST['contacto']),
            'email' => trim($_POST['email']),
            'phone' => trim($_POST['phone']),
            'address' => trim($_POST['address']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ]);
        AuditLog::add((int)Auth::user()['id'], 'clients', $id, 'update', $_POST, $_SERVER['REMOTE_ADDR'] ?? '-');
        redirect('/clients');
    }
}
