<?php

namespace App\Services;

use App\Models\NotificationLog;

class NotificationService
{
    public function __construct(private EmailProvider $email = new EmailProvider(), private WhatsAppProvider $wa = new WhatsAppProvider()) {}

    public function notifyCase(array $case, array $user, string $type, int $cooldownHours): void
    {
        $last = NotificationLog::lastSent((int)$case['id'], $type);
        if ($last && strtotime($last['created_at']) > strtotime("-{$cooldownHours} hours")) {
            return;
        }

        $subject = "[{$type}] {$case['folio']} - {$case['descripcion']}";
        $html = "<h3>Alerta {$type}</h3><p>Expediente {$case['folio']} ({$case['client_name']}) en estado {$case['status']}.</p>";
        $text = "{$type}: {$case['folio']} {$case['descripcion']} ({$case['status']})";

        if (!empty($user['email'])) {
            $res = $this->email->send($user['email'], $subject, $html);
            NotificationLog::add((int)$case['id'], (int)$user['id'], 'email', $user['email'], $type . ' ' . $case['folio'], $res['ok'] ? 'sent' : 'error', $res['response']);
        }

        if (!empty($user['phone'])) {
            $to = str_starts_with($user['phone'], 'whatsapp:') ? $user['phone'] : 'whatsapp:' . $user['phone'];
            $res = $this->wa->send($to, $text);
            NotificationLog::add((int)$case['id'], (int)$user['id'], 'whatsapp', $to, $type . ' ' . $case['folio'], $res['ok'] ? 'sent' : 'error', $res['response']);
        }
    }

    public function sendDigest(array $user, array $casesByLight): void
    {
        $summary = sprintf('Rojos: %d, Amarillos: %d, Verdes: %d', count($casesByLight['red']), count($casesByLight['yellow']), count($casesByLight['green']));
        $html = '<h3>Resumen diario</h3><p>' . $summary . '</p>';
        $text = 'Resumen diario: ' . $summary;

        if (!empty($user['email'])) {
            $res = $this->email->send($user['email'], 'Resumen diario de expedientes', $html);
            NotificationLog::add(null, (int)$user['id'], 'email', $user['email'], 'digest ' . $summary, $res['ok'] ? 'sent' : 'error', $res['response']);
        }
        if (!empty($user['phone'])) {
            $to = str_starts_with($user['phone'], 'whatsapp:') ? $user['phone'] : 'whatsapp:' . $user['phone'];
            $res = $this->wa->send($to, $text);
            NotificationLog::add(null, (int)$user['id'], 'whatsapp', $to, 'digest ' . $summary, $res['ok'] ? 'sent' : 'error', $res['response']);
        }
    }
}
