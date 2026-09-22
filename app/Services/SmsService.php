<?php

namespace App\Services;

use App\Helpers\PhoneHelper;
use App\Models\SmsLog;
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
     * Send an SMS via KenyaSMS and persist every attempt to `sms_logs`.
     *
     * @param  string|null $phone
     * @param  string      $message
     * @param  string|null $type     transactional | promotional
     * @param  string|null $context  short tag like 'waitlist_welcome', 'invite'
     * @param  int|null    $userId   optionally link to a user record
     * @return array{success: bool, message_id?: string, status?: string, error?: string}
     */
    public function send(
        ?string $phone,
        string $message,
        ?string $type = null,
        ?string $context = null,
        ?int $userId = null
    ): array {
        $messageType = $type ?: $this->defaultType;
        $normalized  = PhoneHelper::format($phone);

        // ---- 1. Invalid phone: log and bail -------------------------------
        if (!$normalized) {
            Log::warning('SmsService: invalid phone number', ['phone' => $phone]);

            SmsLog::create([
                'user_id'      => $userId,
                'recipient'    => (string) $phone,
                'message'      => $message,
                'message_type' => $messageType,
                'provider'     => 'kenyasms',
                'status'       => SmsLog::STATUS_SKIPPED,
                'error'        => 'Invalid phone number',
                'context'      => $context,
            ]);

            return ['success' => false, 'error' => 'Invalid phone number'];
        }

        // ---- 2. Missing API key: log and bail -----------------------------
        if (empty($this->apiKey)) {
            Log::error('SmsService: KENYASMS_KEY is not configured');

            SmsLog::create([
                'user_id'      => $userId,
                'recipient'    => $normalized,
                'message'      => $message,
                'message_type' => $messageType,
                'provider'     => 'kenyasms',
                'status'       => SmsLog::STATUS_FAILED,
                'error'        => 'SMS provider not configured',
                'context'      => $context,
            ]);

            return ['success' => false, 'error' => 'SMS provider not configured'];
        }

        // ---- 3. Create the log row in "queued" state ----------------------
        $logRow = SmsLog::create([
            'user_id'      => $userId,
            'recipient'    => $normalized,
            'message'      => $message,
            'message_type' => $messageType,
            'provider'     => 'kenyasms',
            'status'       => SmsLog::STATUS_QUEUED,
            'context'      => $context,
        ]);

        // ---- 4. Build the request -----------------------------------------
        $payload = [
            'sender_id'    => $this->senderId,
            'recipient'    => $normalized,
            'message'      => $message,
            'message_type' => $messageType,
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];

        if ($this->sandbox) {
            $headers['X-Sandbox-Mode'] = 'true';
        }

        // ---- 5. Send ------------------------------------------------------
        try {
            $response = Http::withHeaders($headers)
                ->withoutVerifying()
                ->connectTimeout(5)
                ->timeout(10)
                ->post($this->baseUrl . '/sms/send', $payload);

            $body = $response->json() ?? [];

            // 5a. Success path
            if ($response->successful()) {
                $messageId = $body['data']['message_id'] ?? $body['message_id'] ?? null;
                $status    = $body['data']['status'] ?? $body['status'] ?? 'queued';
                $cost      = $body['data']['cost'] ?? $body['cost'] ?? null;

                $logRow->update([
                    'status'              => $status === 'queued' ? SmsLog::STATUS_QUEUED : SmsLog::STATUS_SENT,
                    'provider_message_id' => $messageId,
                    'provider_response'   => $body,
                    'cost'                => $cost,
                ]);

                Log::info('SmsService: SMS sent', [
                    'phone'      => $normalized,
                    'message_id' => $messageId,
                    'status'     => $status,
                    'context'    => $context,
                ]);

                return [
                    'success'    => true,
                    'message_id' => $messageId,
                    'status'     => $status,
                ];
            }

            // 5b. API rejected (4xx/5xx)
            $errorMessage = $body['message'] ?? $body['error'] ?? 'SMS provider rejected the request';

            $logRow->update([
                'status'            => SmsLog::STATUS_REJECTED,
                'provider_response' => $body,
                'error'             => $errorMessage,
                'failed_at'         => now(),
            ]);

            Log::error('SmsService: KenyaSMS API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
                'phone'  => $normalized,
                'context'=> $context,
            ]);

            return [
                'success' => false,
                'error'   => $errorMessage,
            ];

        } catch (\Throwable $e) {
            // 5c. Network / timeout / exception
            $logRow->update([
                'status'    => SmsLog::STATUS_FAILED,
                'error'     => $e->getMessage(),
                'failed_at' => now(),
            ]);

            Log::error('SmsService: exception', [
                'phone'   => $normalized,
                'error'   => $e->getMessage(),
                'context' => $context,
            ]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Send only if the recipient is a Safaricom number.
     */
    public function sendToSafaricom(
        ?string $phone,
        string $message,
        ?string $type = null,
        ?string $context = null,
        ?int $userId = null
    ): array {
        if (!PhoneHelper::isSafaricom($phone)) {
            SmsLog::create([
                'user_id'      => $userId,
                'recipient'    => (string) $phone,
                'message'      => $message,
                'message_type' => $type ?: $this->defaultType,
                'provider'     => 'kenyasms',
                'status'       => SmsLog::STATUS_SKIPPED,
                'error'        => 'not_safaricom',
                'context'      => $context,
            ]);

            return ['success' => false, 'skipped' => true, 'error' => 'not_safaricom'];
        }

        return $this->send($phone, $message, $type, $context, $userId);
    }
}