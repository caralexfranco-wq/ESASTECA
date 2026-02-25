<?php

namespace App\Models;

class AuditLog extends BaseModel
{
    public static function add(?int $userId, string $entity, int $entityId, string $action, array $diff, string $ip): void
    {
        self::db()->prepare('INSERT INTO audit_log (actor_user_id,entity,entity_id,action,diff_json,ip,created_at) VALUES (?,?,?,?,?,?,NOW())')
            ->execute([$userId,$entity,$entityId,$action,json_encode($diff, JSON_UNESCAPED_UNICODE),$ip]);
    }

    public static function latest(int $limit = 100): array
    {
        $stmt = self::db()->prepare('SELECT a.*, u.name as actor_name FROM audit_log a LEFT JOIN users u ON u.id=a.actor_user_id ORDER BY a.id DESC LIMIT ?');
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
