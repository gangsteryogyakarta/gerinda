<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $provider;
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        $this->provider = config('services.whatsapp.provider', 'baileys');
        
        if ($this->provider === 'baileys') {
            $this->baseUrl = config('services.whatsapp.baileys_url', 'http://localhost:3001');
            $this->token = '';
        } elseif ($this->provider === 'fonnte') {
            $this->baseUrl = 'https://api.fonnte.com';
            $this->token = config('services.whatsapp.fonnte_token', '');
        } elseif ($this->provider === 'waha') {
            $this->baseUrl = config('services.waha.url', 'http://localhost:3000');
            $this->token = config('services.waha.api_key', '');
        } else {
            $this->baseUrl = config('services.wa_gateway.url', '');
            $this->token = config('services.wa_gateway.token', '');
        }
    }

    /**
     * Send text message
     */
    public function sendText(string $phone, string $message): array
    {
        try {
            if ($this->provider === 'baileys') {
                return $this->sendViaBaileys($phone, $message);
            } elseif ($this->provider === 'fonnte') {
                return $this->sendViaFonnte($phone, $message);
            } elseif ($this->provider === 'waha') {
                return $this->sendViaWaha($phone, $message);
            } else {
                return $this->sendViaGeneric($phone, $message);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp Send Failed', [
                'provider' => $this->provider,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send via Baileys (Node.js server)
     */
    protected function sendViaBaileys(string $phone, string $message): array
    {
        $response = Http::timeout(30)->post($this->baseUrl . '/send', [
            'phone' => $phone,
            'message' => $message,
        ]);

        $data = $response->json();

        Log::info('Baileys Response', [
            'phone' => $phone,
            'status' => $response->status(),
            'success' => $data['success'] ?? false,
        ]);

        return [
            'success' => $response->successful() && ($data['success'] ?? false),
            'data' => $data,
            'error' => $data['error'] ?? null,
        ];
    }

    /**
     * Send via Fonnte API
     */
    protected function sendViaFonnte(string $phone, string $message): array
    {
        $phone = $this->formatPhoneNumber($phone);
        
        $response = Http::withHeaders([
            'Authorization' => $this->token,
        ])->post($this->baseUrl . '/send', [
            'target' => $phone,
            'message' => $message,
            'countryCode' => '62',
        ]);

        $data = $response->json();

        return [
            'success' => $response->successful() && ($data['status'] ?? false),
            'data' => $data,
            'error' => $data['reason'] ?? null,
        ];
    }

    /**
     * Send via WAHA (Docker)
     */
    protected function sendViaWaha(string $phone, string $message): array
    {
        $phone = $this->formatPhoneNumber($phone);
        $session = config('services.waha.session', 'gerindra');
        $chatId = $phone . '@c.us';

        $response = Http::withHeaders([
            'X-Api-Key' => $this->token,
        ])->post($this->baseUrl . '/api/sendText', [
            'session' => $session,
            'chatId' => $chatId,
            'text' => $message,
        ]);

        return [
            'success' => $response->successful(),
            'data' => $response->json(),
        ];
    }

    /**
     * Send via Generic WA Gateway
     */
    protected function sendViaGeneric(string $phone, string $message): array
    {
        $phone = $this->formatPhoneNumber($phone);
        
        $response = Http::withToken($this->token)
            ->post($this->baseUrl . '/send', [
                'phone' => $phone,
                'message' => $message,
            ]);

        return [
            'success' => $response->successful(),
            'data' => $response->json(),
        ];
    }

    /**
     * Send image with caption
     */
    public function sendImage(string $phone, string $imageUrl, string $caption = ''): array
    {
        try {
            if ($this->provider === 'baileys') {
                $response = Http::timeout(30)->post($this->baseUrl . '/send-image', [
                    'phone' => $phone,
                    'imageUrl' => $imageUrl,
                    'caption' => $caption,
                ]);
                return [
                    'success' => $response->successful() && ($response->json('success') ?? false),
                    'data' => $response->json(),
                ];
            } elseif ($this->provider === 'fonnte') {
                $phone = $this->formatPhoneNumber($phone);
                $response = Http::withHeaders([
                    'Authorization' => $this->token,
                ])->post($this->baseUrl . '/send', [
                    'target' => $phone,
                    'message' => $caption,
                    'url' => $imageUrl,
                    'countryCode' => '62',
                ]);
                return [
                    'success' => $response->successful(),
                    'data' => $response->json(),
                ];
            }
            return ['success' => false, 'error' => 'Image sending not supported for this provider'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Bulk send messages (via Baileys server)
     */
    public function bulkSend(array $phones, string $message, int $delayMs = 2000): array
    {
        if ($this->provider === 'baileys') {
            // Use Baileys bulk endpoint
            try {
                $response = Http::timeout(300)->post($this->baseUrl . '/bulk-send', [
                    'phones' => $phones,
                    'message' => $message,
                    'delay' => $delayMs,
                ]);
                return [
                    'success' => $response->successful(),
                    'total' => count($phones),
                    'queued' => $response->json('queued') ?? count($phones),
                    'data' => $response->json(),
                ];
            } catch (\Exception $e) {
                return ['success' => false, 'error' => $e->getMessage()];
            }
        }

        // Fallback: send one by one
        $results = [
            'total' => count($phones),
            'success' => 0,
            'failed' => 0,
            'details' => [],
        ];

        foreach ($phones as $index => $phone) {
            $result = $this->sendText($phone, $message);
            
            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
            
            $results['details'][] = [
                'phone' => $phone,
                'success' => $result['success'],
                'error' => $result['error'] ?? null,
            ];

            if ($index < count($phones) - 1) {
                usleep($delayMs * 1000);
            }
        }

        return $results;
    }

    /**
     * Check connection status
     */
    /**
     * Check connection status
     */
    public function checkStatus(): array
    {
        try {
            if ($this->provider === 'baileys') {
                $response = Http::timeout(5)->get($this->baseUrl . '/status');
                $data = $response->json();
                return [
                    'connected' => $data['connected'] ?? false,
                    'status' => $data['status'] ?? 'unknown',
                    'user' => $data['user'] ?? null,
                ];
            } elseif ($this->provider === 'fonnte') {
                $response = Http::withHeaders([
                    'Authorization' => $this->token,
                ])->post($this->baseUrl . '/device');
                $data = $response->json();
                return [
                    'connected' => $data['status'] ?? false,
                    'device' => $data['device'] ?? null,
                    'quota' => $data['quota'] ?? null,
                ];
            } elseif ($this->provider === 'waha') {
                $session = config('services.waha.session', 'gerindra');
                $response = Http::timeout(5)->get($this->baseUrl . '/api/sessions/' . $session);
                
                if ($response->successful()) {
                    $data = $response->json();
                    // WAHA statuses: 'STOPPED', 'STARTING', 'SCAN_QR_CODE', 'WORKING', 'FAILED'
                    $wahaStatus = $data['status'] ?? 'STOPPED';
                    $connected = $wahaStatus === 'WORKING';
                    
                    // Map WAHA status to our frontend expectations
                    $status = match($wahaStatus) {
                        'WORKING' => 'connected',
                        'SCAN_QR_CODE' => 'qr',
                        default => strtolower($wahaStatus)
                    };

                    return [
                        'connected' => $connected,
                        'status' => $status,
                        'user' => $data['me'] ?? null, // WAHA returns 'me' object with info
                    ];
                }
                
                return ['connected' => false, 'status' => 'disconnected'];
            }
            return ['connected' => false, 'error' => 'Status check not supported'];
        } catch (\Exception $e) {
            return ['connected' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get QR code
     */
    public function getQrCode(): array
    {
        try {
            if ($this->provider === 'baileys') {
                $response = Http::timeout(5)->get($this->baseUrl . '/qr');
                $data = $response->json();
                return [
                    'success' => $data['success'] ?? false,
                    'qr' => $data['qr'] ?? null,
                    'status' => $data['status'] ?? 'unknown',
                ];
            } elseif ($this->provider === 'waha') {
                $session = config('services.waha.session', 'gerindra');
                // Fetch QR as image
                $response = Http::timeout(10)->get($this->baseUrl . '/api/sessions/' . $session . '/auth/qr?format=image');
                
                if ($response->successful()) {
                    // Convert image binary to base64 data URI
                    $type = $response->header('Content-Type');
                    $base64 = base64_encode($response->body());
                    $dataUri = 'data:' . $type . ';base64,' . $base64;
                    
                    return [
                        'success' => true,
                        'qr' => $dataUri,
                        'status' => 'qr',
                    ];
                }
                
                return ['success' => false, 'error' => 'Failed to fetch QR'];
            }
            
            return ['success' => false, 'error' => 'QR code not supported for this provider'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Start session
     */
    public function startSession(): array
    {
        if ($this->provider === 'waha') {
            $session = config('services.waha.session', 'gerindra');
            
            // Check if session exists first
            $check = Http::get($this->baseUrl . '/api/sessions/' . $session);
            if ($check->successful()) {
                // If stopped, start it? Usually WAHA auto-starts but explicit start might be needed or ignored
                return ['success' => true, 'message' => 'Session already exists'];
            }

            // Create/Start session
            $response = Http::withHeaders(['X-Api-Key' => $this->token])
                ->post($this->baseUrl . '/api/sessions', [
                    'name' => $session,
                    'config' => [
                        'proxy' => null,
                        'noweb' => [
                            'store' => [
                                'enabled' => true,
                                'full_sync' => false
                            ]
                        ]
                    ]
                ]);
            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        }
        
        return ['success' => true, 'message' => 'Auto-started for this provider'];
    }

    /**
     * Logout session
     */
    public function logout(): array
    {
        try {
            if ($this->provider === 'baileys') {
                $response = Http::timeout(10)->post($this->baseUrl . '/logout');
                return [
                    'success' => $response->successful(),
                    'data' => $response->json(),
                ];
            } elseif ($this->provider === 'waha') {
                $session = config('services.waha.session', 'gerindra');
                $response = Http::timeout(10)
                    ->withHeaders(['X-Api-Key' => $this->token])
                    ->post($this->baseUrl . '/api/sessions/' . $session . '/logout');
                    
                return [
                    'success' => $response->successful(),
                    'data' => $response->json(),
                ];
            }
            return ['success' => false, 'error' => 'Logout not supported for this provider'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check if number exists on WhatsApp
     */
    public function checkNumber(string $phone): array
    {
        if ($this->provider !== 'baileys') {
            return ['exists' => null, 'error' => 'Check number only for Baileys'];
        }

        try {
            $response = Http::timeout(10)->post($this->baseUrl . '/check-number', [
                'phone' => $phone,
            ]);
            $data = $response->json();
            return [
                'exists' => $data['exists'] ?? false,
                'jid' => $data['jid'] ?? null,
            ];
        } catch (\Exception $e) {
            return ['exists' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Format Indonesian phone number
     */
    protected function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        
        if (str_starts_with($phone, '+62')) {
            $phone = substr($phone, 1);
        }
        
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }
        
        return $phone;
    }

    /**
     * Get provider info
     */
    public function getProviderInfo(): array
    {
        return [
            'provider' => $this->provider,
            'url' => $this->baseUrl,
            'configured' => $this->provider === 'baileys' || !empty($this->token),
        ];
    }
}
