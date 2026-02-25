<?php

namespace App\Models;

class NotificationLog extends BaseModel
{
    public static function add(?int $caseId, ?int $userId, string $channel, string $to, string $summary, string $status, string $response = ''): void
    {
        self::db()->prepare('INSERT INTO notification_log (case_id,user_id,channel,to_address,message_summary,status,provider_response,created_at) VALUES (?,?,?,?,?,?,?,NOW())')
            ->execute([$caseId,$userId,$channel,$to,$summary,$status,$response]);
    }

    public static function lastSent(int $caseId, string $type): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM notification_log WHERE case_id=? AND message_summary LIKE ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$caseId, "%{$type}%"]);
        return $stmt->fetch() ?: null;
    }
}
