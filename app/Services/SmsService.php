<?php

namespace App\Services;

use App\Helpers\PhoneHelper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $senderId;
    protected string $defaultType;
    protected bool $sandbox;

    public function __construct()
    {
        $this->baseUrl     = rtrim((string) config('sms.kenyasms.base_url', 'https://kenyasms.com/api/v1'), '/');
        $this->apiKey      = (string) config('sms.kenyasms.api_key', '');
        $this->senderId    = (string) config('sms.kenyasms.sender_id', 'SHARETENT');
        $this->defaultType = (string) config('sms.kenyasms.default_type', 'transactional');
        $this->sandbox     = (bool) config('sms.kenyasms.sandbox', false);
    }

    /**
     * Send an SMS via KenyaSMS.
     *
     * @return array{success: bool, message_id?: string, status?: string, error?: string}
     */
    public function send(?string $phone, string $message, ?string $type = null): array
    {
        $normalized = PhoneHelper::format($phone);

        if (!$normalized) {
            Log::warning('SmsService: invalid phone number', ['phone' => $phone]);
            return ['success' => false, 'error' => 'Invalid phone number'];
        }

        if (empty($this->apiKey)) {
            Log::error('SmsService: KENYASMS_KEY is not configured');
            return ['success' => false, 'error' => 'SMS provider not configured'];
        }

        $payload = [
            'sender_id'    => $this->senderId,
            'recipient'    => $normalized,
            'message'      => $message,
            'message_type' => $type ?: $this->defaultType,
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];

        if ($this->sandbox) {
            $headers['X-Sandbox-Mode'] = 'true';
        }

        try {
            $response = Http::withHeaders($headers)
                ->withoutVerifying()
                ->timeout(30)
                ->post($this->baseUrl . '/sms/send', $payload);

            $body = $response->json() ?? [];

            if ($response->successful()) {
                $messageId = $body['data']['message_id'] ?? $body['message_id'] ?? null;
                $status    = $body['data']['status'] ?? $body['status'] ?? 'queued';

                Log::info('SmsService: SMS sent', [
                    'phone'      => $normalized,
                    'message_id' => $messageId,
                    'status'     => $status,
                ]);

                return [
                    'success'    => true,
                    'message_id' => $messageId,
                    'status'     => $status,
                ];
            }

            Log::error('SmsService: KenyaSMS API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
                'phone'  => $normalized,
            ]);

            return [
                'success' => false,
                'error'   => $body['message'] ?? $body['error'] ?? 'SMS provider rejected the request',
            ];
        } catch (\Throwable $e) {
            Log::error('SmsService: exception', [
                'phone' => $normalized,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Send only if the recipient is a Safaricom number.
     * Returns a skipped response for non-Safaricom numbers.
     */
    public function sendToSafaricom(?string $phone, string $message, ?string $type = null): array
    {
        if (!PhoneHelper::isSafaricom($phone)) {
            return ['success' => false, 'skipped' => true, 'error' => 'not_safaricom'];
        }

        return $this->send($phone, $message, $type);
    }
}