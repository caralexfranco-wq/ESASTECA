<?php

namespace App\Models;

class Client extends BaseModel
{
    public static function all(): array
    {
        return self::db()->query('SELECT * FROM clients ORDER BY id DESC')->fetchAll();
    }

    public static function active(): array
    {
        return self::db()->query('SELECT id, razon_social FROM clients WHERE is_active=1 ORDER BY razon_social')->fetchAll();
    }

    public static function create(array $d): void
    {
        self::db()->prepare('INSERT INTO clients (type,razon_social,rfc,contacto,email,phone,address,is_active,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,NOW(),NOW())')
            ->execute([$d['type'],$d['razon_social'],$d['rfc'],$d['contacto'],$d['email'],$d['phone'],$d['address'],$d['is_active']]);
    }

    public static function update(int $id, array $d): void
    {
        self::db()->prepare('UPDATE clients SET type=?, razon_social=?, rfc=?, contacto=?, email=?, phone=?, address=?, is_active=?, updated_at=NOW() WHERE id=?')
            ->execute([$d['type'],$d['razon_social'],$d['rfc'],$d['contacto'],$d['email'],$d['phone'],$d['address'],$d['is_active'],$id]);
    }
}
