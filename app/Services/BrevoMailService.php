<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrevoMailService
{
    /**
     * Enviar correo usando la API HTTP de Brevo.
     */
    public static function enviar(
        string|array $to,
        string $subject,
        string $htmlContent,
        ?string $fromEmail = null,
        ?string $fromName = null
    ): bool {
        $apiKey = config('services.brevo.key');

        if (empty($apiKey)) {
            Log::error('Brevo API Key no configurada');
            return false;
        }

        // Normalizar destinatarios
        if (is_string($to)) {
            $to = [$to];
        }

        $destinatarios = collect($to)->map(fn($email) => ['email' => trim($email)])->values()->toArray();

        try {
            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'name'  => $fromName ?? config('mail.from.name', 'Central de Alarmas'),
                    'email' => $fromEmail ?? config('mail.from.address', 'noreply@example.com'),
                ],
                'to'          => $destinatarios,
                'subject'     => $subject,
                'htmlContent' => $htmlContent,
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Brevo API error: ' . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error('Brevo API exception: ' . $e->getMessage());
            return false;
        }
    }
}