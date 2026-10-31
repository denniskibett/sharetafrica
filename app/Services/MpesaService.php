<?php
// app/Services/MpesaService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaService
{
    protected $consumerKey;
    protected $consumerSecret;
    protected $baseUrl;
    protected $environment;
    protected $shortcode;
    protected $initiator;
    protected $securityCredential;
    protected $passkey;

    public function __construct()
    {
        $this->consumerKey = env('MPESA_CONSUMER_KEY');
        $this->consumerSecret = env('MPESA_CONSUMER_SECRET');
        $this->environment = env('MPESA_ENVIRONMENT', 'sandbox');
        $this->shortcode = env('MPESA_SHORTCODE', '174379');
        $this->initiator = env('MPESA_INITIATOR', 'sharet_api');
        $this->securityCredential = env('MPESA_SECURITY_CREDENTIAL', '');
        $this->passkey = env('MPESA_PASSKEY', '');
        
        // Set base URL based on environment
        $this->baseUrl = $this->environment === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    /**
     * Get OAuth Access Token
     */

    public function getAccessToken()
    {
        // Try to get from cache first
        $cacheKey = 'mpesa_access_token';
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            $cachedToken = \Illuminate\Support\Facades\Cache::get($cacheKey);
            Log::info('M-Pesa: Using cached access token');
            return $cachedToken;
        }

        $url = $this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials';
        
        Log::info('M-Pesa: Requesting access token', [
            'url' => $url,
            'environment' => $this->environment,
            'consumer_key' => substr($this->consumerKey, 0, 10) . '...'
        ]);
        
        try {
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->timeout(30)
                ->retry(3, 1000)
                ->get($url);

            $statusCode = $response->status();
            $responseBody = $response->body();

            Log::info('M-Pesa: Access token response', [
                'status' => $statusCode,
                'body' => substr($responseBody, 0, 500)
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['access_token'])) {
                    // Cache for 50 minutes (tokens expire after 1 hour)
                    \Illuminate\Support\Facades\Cache::put($cacheKey, $data['access_token'], 3000);
                    
                    Log::info('M-Pesa: Access token generated and cached successfully');
                    return $data['access_token'];
                } else {
                    Log::error('M-Pesa: No access_token in response', ['response' => $data]);
                    return null;
                }
            }

            // Handle specific error cases
            $errorMessage = 'Unknown error';
            if ($response->json() && isset($response->json()['errorMessage'])) {
                $errorMessage = $response->json()['errorMessage'];
            } elseif ($response->json() && isset($response->json()['error_description'])) {
                $errorMessage = $response->json()['error_description'];
            }

            Log::error('M-Pesa: Failed to get access token', [
                'status' => $statusCode,
                'error' => $errorMessage,
                'response' => $responseBody
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('M-Pesa: Access token request exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    // =========================================================
    // 📱 STK PUSH (Lipa Na M-Pesa Online)
    // =========================================================


    /**
     * Send STK Push to customer's phone
     */
    public function stkPush($phone, $amount, $accountReference, $transactionDesc = 'Payment', $invoiceId = null, $userId = null)
    {
        $token = $this->getAccessToken();
        
        if (!$token) {
            return [
                'success' => false,
                'message' => 'Failed to get access token'
            ];
        }

        $url = $this->baseUrl . '/mpesa/stkpush/v1/processrequest';
        
        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
        $phone = $this->formatPhoneNumber($phone);

        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int) $amount,
            'PartyA' => $phone,
            'PartyB' => $this->shortcode,
            'PhoneNumber' => $phone,
            'CallBackURL' => env('MPESA_CALLBACK_URL'),
            'AccountReference' => $accountReference,
            'TransactionDesc' => $transactionDesc,
        ];

        Log::info('M-Pesa STK Push Request', [
            'phone' => $phone,
            'amount' => $amount,
            'account_reference' => $accountReference,
            'invoice_id' => $invoiceId
        ]);

        try {
            $response = Http::withToken($token)
                ->post($url, $payload);

            $responseData = $response->json();

            Log::info('M-Pesa STK Push Response', [
                'response' => $responseData
            ]);

            if ($response->successful() && isset($responseData['ResponseCode']) && $responseData['ResponseCode'] === '0') {
                $checkoutRequestId = $responseData['CheckoutRequestID'] ?? null;
                $merchantRequestId = $responseData['MerchantRequestID'] ?? null;
                
                if ($checkoutRequestId) {
                    try {
                        // ✅ ONLY create MpesaStk record - NO PendingMpesaPayment
                        \App\Models\MpesaStk::createFromRequest(
                            $checkoutRequestId,
                            $merchantRequestId,
                            $phone,
                            $amount,
                            $invoiceId,
                            $userId
                        );
                        
                        Log::info('✅ M-Pesa STK record stored in database', [
                            'checkout_request_id' => $checkoutRequestId,
                            'invoice_id' => $invoiceId
                        ]);
                    } catch (\Exception $e) {
                        Log::error('❌ Failed to store M-Pesa STK record: ' . $e->getMessage(), [
                            'checkout_request_id' => $checkoutRequestId
                        ]);
                    }
                }

                return [
                    'success' => true,
                    'data' => $responseData,
                    'message' => $responseData['CustomerMessage'] ?? 'STK Push sent successfully',
                    'checkout_request_id' => $checkoutRequestId,
                    'merchant_request_id' => $merchantRequestId
                ];
            }

            return [
                'success' => false,
                'message' => $responseData['ResponseDescription'] ?? $responseData['errorMessage'] ?? 'STK Push failed',
                'data' => $responseData
            ];

        } catch (\Exception $e) {
            Log::error('M-Pesa STK Push Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Query STK Push Status
     */
    public function queryStatus($checkoutRequestId)
    {
        $token = $this->getAccessToken();
        
        if (!$token) {
            return [
                'success' => false,
                'message' => 'Failed to get access token'
            ];
        }

        $url = $this->baseUrl . '/mpesa/stkpushquery/v1/query';
        
        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId
        ];

        Log::info('M-Pesa Query Status Request', [
            'checkout_request_id' => $checkoutRequestId
        ]);

        try {
            $response = Http::withToken($token)
                ->post($url, $payload);

            $responseData = $response->json();

            Log::info('M-Pesa Query Status Response', [
                'response' => $responseData
            ]);

            if ($response->successful() && isset($responseData['ResponseCode']) && $responseData['ResponseCode'] === '0') {
                return [
                    'success' => true,
                    'data' => $responseData,
                    'message' => $responseData['ResultDesc'] ?? 'Query successful',
                    'status' => $responseData['ResultCode'] ?? null
                ];
            }

            return [
                'success' => false,
                'message' => $responseData['ResponseDescription'] ?? $responseData['errorMessage'] ?? 'Query failed',
                'data' => $responseData
            ];

        } catch (\Exception $e) {
            Log::error('M-Pesa Query Status Error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // =========================================================
    // 💰 B2B (Business PayBill) API
    // =========================================================

    /**
     * Business PayBill (B2B) API
     */
    public function businessPayBill(array $data)
    {
        $token = $this->getAccessToken();
        
        if (!$token) {
            return [
                'success' => false,
                'message' => 'Failed to get access token'
            ];
        }

        $url = $this->baseUrl . '/mpesa/b2b/v1/paymentrequest';
        
        $payload = [
            'Initiator' => $data['initiator'] ?? $this->initiator,
            'SecurityCredential' => $data['security_credential'] ?? $this->securityCredential,
            'CommandID' => 'BusinessPayBill',
            'SenderIdentifierType' => $data['sender_type'] ?? '4',
            'ReceiverIdentifierType' => $data['receiver_type'] ?? '4',
            'Amount' => $data['amount'],
            'PartyA' => $data['party_a'] ?? $this->shortcode,
            'PartyB' => $data['party_b'],
            'AccountReference' => $data['account_reference'],
            'Requester' => $data['requester'] ?? null,
            'Remarks' => $data['remarks'] ?? 'OK',
            'QueueTimeOutURL' => $data['queue_timeout_url'] ?? env('MPESA_QUEUE_URL'),
            'ResultURL' => $data['result_url'] ?? env('MPESA_B2B_RESULT_URL'),
        ];

        // Remove null values
        $payload = array_filter($payload, function ($value) {
            return !is_null($value);
        });

        Log::info('M-Pesa B2B Request', [
            'url' => $url,
            'payload' => $payload
        ]);

        try {
            $response = Http::withToken($token)
                ->post($url, $payload);

            $responseData = $response->json();

            Log::info('M-Pesa B2B Response', [
                'response' => $responseData
            ]);

            if ($response->successful() && isset($responseData['ResponseCode']) && $responseData['ResponseCode'] === '0') {
                return [
                    'success' => true,
                    'data' => $responseData,
                    'message' => $responseData['ResponseDescription'] ?? 'Transaction initiated successfully'
                ];
            }

            return [
                'success' => false,
                'message' => $responseData['ResponseDescription'] ?? $responseData['errorMessage'] ?? 'Transaction failed',
                'data' => $responseData
            ];

        } catch (\Exception $e) {
            Log::error('M-Pesa B2B Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Pay a bill (simplified wrapper for B2B)
     */
    public function payBill($amount, $partyB, $accountReference, $requester = null)
    {
        return $this->businessPayBill([
            'amount' => $amount,
            'party_b' => $partyB,
            'account_reference' => $accountReference,
            'requester' => $requester,
            'remarks' => 'Bill Payment',
        ]);
    }

    // =========================================================
    // 🏦 WALLET & ACCOUNT
    // =========================================================

    /**
     * Check wallet balance
     */
    public function getBalance(): ?array
    {
        try {
            $response = $this->client->get('/wallet/balance', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ],
            ]);
            
            return json_decode($response->getBody(), true);
            
        } catch (\Exception $e) {
            Log::error('M-Pesa Balance Check Error', [
                'error' => $e->getMessage(),
            ]);
            
            return null;
        }
    }

    // =========================================================
    // 📞 HELPER METHODS
    // =========================================================

    /**
     * Format phone number to 254XXXXXXXXX format
     */
    public function formatPhoneNumber($phone): string
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If starts with 0, replace with 254
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        }
        
        // If starts with +, remove it
        if (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }
        
        // If starts with 7, add 254
        if (str_starts_with($phone, '7')) {
            $phone = '254' . $phone;
        }
        
        return $phone;
    }
}