<?php

namespace App\Models;

class User extends BaseModel
{
    public static function all(): array
    {
        return self::db()->query('SELECT u.*, r.key AS role_key FROM users u JOIN roles r ON r.id=u.role_id ORDER BY u.id DESC')->fetchAll();
    }

    public static function lawyers(): array
    {
        return self::db()->query("SELECT u.id, u.name FROM users u JOIN roles r ON r.id=u.role_id WHERE u.is_active=1 AND r.key IN ('ADMIN','CAPTURISTA') ORDER BY u.name")->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM users WHERE id=?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = self::db()->prepare('SELECT u.*, r.key AS role_key FROM users u JOIN roles r ON r.id=u.role_id WHERE email=? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): void
    {
        $stmt = self::db()->prepare('INSERT INTO users (role_id,name,email,phone,password_hash,is_active,created_at,updated_at) VALUES (?,?,?,?,?,?,NOW(),NOW())');
        $stmt->execute([$data['role_id'],$data['name'],$data['email'],$data['phone'],$data['password_hash'],$data['is_active']]);
    }

    public static function update(int $id, array $data): void
    {
        $sql = 'UPDATE users SET role_id=?, name=?, email=?, phone=?, is_active=?, updated_at=NOW()';
        $params = [$data['role_id'],$data['name'],$data['email'],$data['phone'],$data['is_active']];
        if (!empty($data['password_hash'])) {
            $sql .= ', password_hash=?';
            $params[] = $data['password_hash'];
        }
        $sql .= ' WHERE id=?';
        $params[] = $id;
        self::db()->prepare($sql)->execute($params);
    }

    public static function roles(): array
    {
        return self::db()->query('SELECT * FROM roles ORDER BY id')->fetchAll();
    }
}
