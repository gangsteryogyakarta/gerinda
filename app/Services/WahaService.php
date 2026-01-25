<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WahaService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $session;

    public function __construct()
    {
        $this->baseUrl = config('services.waha.url', 'http://localhost:3000');
        $this->apiKey = config('services.waha.api_key', 'gerindra-secret-key-2026');
        $this->session = config('services.waha.session', 'gerindra');
    }

    /**
     * Get HTTP client with auth headers
     */
    protected function client()
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'X-Api-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(30);
    }

    /**
     * Check if WAHA is running
     */
    public function ping(): bool
    {
        try {
            $response = $this->client()->get('/api/health');
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WAHA Health Check Failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get session status
     */
    public function getSessionStatus(): array
    {
        try {
            $response = $this->client()->get("/api/sessions/{$this->session}");
            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('WAHA Get Session Failed', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Start a new session
     */
    public function startSession(): array
    {
        try {
            $response = $this->client()->post('/api/sessions', [
                'name' => $this->session,
                'config' => [
                    'proxy' => null,
                    'webhooks' => [],
                ]
            ]);
            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('WAHA Start Session Failed', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Get QR Code for authentication
     */
    public function getQrCode(): ?string
    {
        try {
            $response = $this->client()->get("/api/sessions/{$this->session}/auth/qr", [
                'format' => 'image'
            ]);
            
            if ($response->successful()) {
                return base64_encode($response->body());
            }
            return null;
        } catch (\Exception $e) {
            Log::error('WAHA Get QR Failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Send text message
     */
    public function sendText(string $phone, string $message): array
    {
        try {
            // Format phone number (remove leading 0, add 62 for Indonesia)
            $chatId = $this->formatPhoneNumber($phone) . '@c.us';
            
            $response = $this->client()->post("/api/sendText", [
                'session' => $this->session,
                'chatId' => $chatId,
                'text' => $message,
            ]);

            $result = $response->json();
            
            Log::info('WAHA Message Sent', [
                'phone' => $phone,
                'status' => $response->status(),
            ]);

            return [
                'success' => $response->successful(),
                'data' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('WAHA Send Message Failed', [
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
     * Send image with caption
     */
    public function sendImage(string $phone, string $imageUrl, string $caption = ''): array
    {
        try {
            $chatId = $this->formatPhoneNumber($phone) . '@c.us';
            
            $response = $this->client()->post("/api/sendImage", [
                'session' => $this->session,
                'chatId' => $chatId,
                'file' => [
                    'url' => $imageUrl,
                ],
                'caption' => $caption,
            ]);

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('WAHA Send Image Failed', [
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
     * Send document/file
     */
    public function sendDocument(string $phone, string $fileUrl, string $filename): array
    {
        try {
            $chatId = $this->formatPhoneNumber($phone) . '@c.us';
            
            $response = $this->client()->post("/api/sendFile", [
                'session' => $this->session,
                'chatId' => $chatId,
                'file' => [
                    'url' => $fileUrl,
                    'filename' => $filename,
                ],
            ]);

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('WAHA Send Document Failed', [
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
     * Bulk send messages
     */
    public function bulkSend(array $recipients, string $message, int $delayMs = 2000): array
    {
        $results = [
            'total' => count($recipients),
            'success' => 0,
            'failed' => 0,
            'details' => [],
        ];

        foreach ($recipients as $index => $phone) {
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

            // Delay between messages to avoid rate limiting
            if ($index < count($recipients) - 1) {
                usleep($delayMs * 1000);
            }
        }

        return $results;
    }

    /**
     * Check if number is registered on WhatsApp
     */
    public function checkNumber(string $phone): array
    {
        try {
            $chatId = $this->formatPhoneNumber($phone) . '@c.us';
            
            $response = $this->client()->get("/api/contacts/check-exists", [
                'session' => $this->session,
                'phone' => $chatId,
            ]);

            return [
                'exists' => $response->json('exists') ?? false,
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            return [
                'exists' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format Indonesian phone number
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove spaces, dashes, and other characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If starts with 0, replace with 62
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        
        // If starts with +62, remove the +
        if (str_starts_with($phone, '+62')) {
            $phone = substr($phone, 1);
        }
        
        // If doesn't start with 62, add it
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }
        
        return $phone;
    }

    /**
     * Logout session
     */
    public function logout(): array
    {
        try {
            $response = $this->client()->post("/api/sessions/{$this->session}/logout");
            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Stop session
     */
    public function stopSession(): array
    {
        try {
            $response = $this->client()->post("/api/sessions/{$this->session}/stop");
            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
