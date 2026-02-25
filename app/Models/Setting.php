<?php

namespace App\Models;

class Setting extends BaseModel
{
    public static function get(): array
    {
        return self::db()->query('SELECT * FROM settings LIMIT 1')->fetch() ?: [];
    }

    public static function update(array $d): void
    {
        self::db()->prepare('UPDATE settings SET warning_days=?, digest_time=?, cooldown_hours=?, whatsapp_enabled=?, email_enabled=?, updated_at=NOW() WHERE id=1')
            ->execute([$d['warning_days'],$d['digest_time'],$d['cooldown_hours'],$d['whatsapp_enabled'],$d['email_enabled']]);
    }
}
