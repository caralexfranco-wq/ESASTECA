<?php

namespace App\Services;

class WhatsAppProvider
{
    public function send(string $to, string $text): array
    {
        if (!(bool) filter_var(env('WHATSAPP_ENABLED', 'false'), FILTER_VALIDATE_BOOL)) {
            return ['ok' => true, 'response' => 'whatsapp deshabilitado'];
        }

        $provider = env('WHATSAPP_PROVIDER', 'twilio');
        return $provider === 'cloud' ? $this->sendCloud($to, $text) : $this->sendTwilio($to, $text);
    }

    private function sendTwilio(string $to, string $text): array
    {
        $sid = env('TWILIO_ACCOUNT_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $from = env('TWILIO_WHATSAPP_FROM');
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

        $payload = http_build_query(['From' => $from, 'To' => $to, 'Body' => $text]);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $sid . ':' . $token,
        ]);
        $resp = curl_exec($ch);
        $ok = curl_getinfo($ch, CURLINFO_HTTP_CODE) < 300;
        curl_close($ch);

        return ['ok' => $ok, 'response' => (string)$resp];
    }

    private function sendCloud(string $to, string $text): array
    {
        $url = 'https://graph.facebook.com/v18.0/' . env('WA_CLOUD_PHONE_NUMBER_ID') . '/messages';
        $payload = json_encode([
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $text]
        ]);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . env('WA_CLOUD_TOKEN'),
            ],
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $resp = curl_exec($ch);
        $ok = curl_getinfo($ch, CURLINFO_HTTP_CODE) < 300;
        curl_close($ch);
        return ['ok' => $ok, 'response' => (string)$resp];
    }
}
