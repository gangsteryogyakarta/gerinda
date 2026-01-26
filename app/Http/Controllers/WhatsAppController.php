<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use App\Services\WhatsAppRateLimiter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WhatsAppController extends Controller
{
    protected WhatsAppService $whatsapp;
    protected WhatsAppRateLimiter $rateLimiter;

    public function __construct(WhatsAppService $whatsapp, WhatsAppRateLimiter $rateLimiter)
    {
        $this->whatsapp = $whatsapp;
        $this->rateLimiter = $rateLimiter;
    }

    /**
     * Show WhatsApp Dashboard
     */
    public function index()
    {
        $status = $this->whatsapp->checkStatus();
        $provider = $this->whatsapp->getProviderInfo();
        
        return view('whatsapp.index', [
            'status' => $status,
            'provider' => $provider,
            'isConnected' => $status['connected'] ?? false,
        ]);
    }

    /**
     * Check WhatsApp service health
     */
    public function health(): JsonResponse
    {
        $status = $this->whatsapp->checkStatus();
        $provider = $this->whatsapp->getProviderInfo();

        return response()->json([
            'provider' => $provider['provider'],
            'status' => $status,
        ]);
    }

    /**
     * Get QR Code for authentication (Baileys)
     */
    public function qrCode(): JsonResponse
    {
        $result = $this->whatsapp->getQrCode();
        
        return response()->json($result);
    }

    /**
     * Start WhatsApp session
     */
    public function startSession(): JsonResponse
    {
        $result = $this->whatsapp->startSession();
        
        return response()->json($result);
    }

    /**
     * Stop WhatsApp session
     */
    public function stopSession(): JsonResponse
    {
        $result = $this->whatsapp->logout();
        
        return response()->json($result);
    }

    /**
     * Logout WhatsApp session
     */
    public function logout(): JsonResponse
    {
        $result = $this->whatsapp->logout();
        
        return response()->json($result);
    }

    /**
     * Send single message
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string|max:4096',
        ]);

        $result = $this->whatsapp->sendText($validated['phone'], $validated['message']);
        
        return response()->json($result);
    }

    /**
     * Send bulk messages
     */
    public function bulkSend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phones' => 'required|array|min:1|max:100',
            'phones.*' => 'required|string',
            'message' => 'required|string|max:4096',
            'delay' => 'nullable|integer|min:1000|max:10000',
        ]);

        $result = $this->whatsapp->bulkSend(
            $validated['phones'],
            $validated['message'],
            $validated['delay'] ?? 2000
        );
        
        return response()->json($result);
    }

    /**
     * Check if phone number exists on WhatsApp
     */
    public function checkNumber(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string',
        ]);

        $result = $this->whatsapp->checkNumber($validated['phone']);
        
        return response()->json($result);
    }

    /**
     * Blast to all registered massa
     */
    public function blastToMassa(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:4096',
            'filter' => 'nullable|in:all,active,province',
            'province_id' => 'nullable|integer|exists:provinces,id',
            'limit' => 'nullable|integer|min:1|max:1000',
        ]);

        // Build query based on filter
        $query = \App\Models\Massa::query()
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '');

        if (($validated['filter'] ?? 'all') === 'active') {
            $query->where('status', 'active');
        }

        if (($validated['filter'] ?? null) === 'province' && isset($validated['province_id'])) {
            $query->where('province_id', $validated['province_id']);
        }

        if (isset($validated['limit'])) {
            $query->limit($validated['limit']);
        }

        $phones = $query->pluck('no_hp')->toArray();

        if (empty($phones)) {
            return response()->json([
                'success' => false,
                'message' => 'No recipients found with the given filter.',
            ]);
        }

        // Check rate limit
        $rateStatus = $this->rateLimiter->getStatus();
        if (!$this->rateLimiter->canSend(count($phones))) {
            return response()->json([
                'success' => false,
                'message' => 'Daily limit would be exceeded. Remaining quota: ' . $rateStatus['remaining'],
                'rate_limit' => $rateStatus,
            ], 429);
        }

        // Dispatch job (no delay parameter - uses config)
        $batchId = uniqid('massa_');
        \App\Jobs\BulkWhatsAppJob::dispatch($phones, $validated['message'], $batchId);
        
        return response()->json([
            'success' => true,
            'message' => 'Blast started for ' . count($phones) . ' recipients. Process running in background.',
            'batch_id' => $batchId,
            'total' => count($phones),
            'rate_limit' => $rateStatus,
        ]);
    }

    /**
     * Send event notification to registrants
     */
    public function notifyEventRegistrants(Request $request, int $eventId): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:4096',
            'status' => 'nullable|in:all,confirmed,pending',
        ]);

        $query = \App\Models\EventRegistration::query()
            ->where('event_id', $eventId)
            ->whereHas('massa', function($q) {
                $q->whereNotNull('no_hp')->where('no_hp', '!=', '');
            })
            ->with('massa:id,no_hp,nama_lengkap');

        if (isset($validated['status']) && $validated['status'] !== 'all') {
            $query->where('registration_status', $validated['status']);
        }

        $registrations = $query->get();
        
        if ($registrations->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No registrants found.',
            ]);
        }

        $phones = $registrations->pluck('massa.no_hp')->filter()->toArray();

        // Check rate limit
        $rateStatus = $this->rateLimiter->getStatus();
        if (!$this->rateLimiter->canSend(count($phones))) {
            return response()->json([
                'success' => false,
                'message' => 'Daily limit would be exceeded. Remaining quota: ' . $rateStatus['remaining'],
                'rate_limit' => $rateStatus,
            ], 429);
        }

        // Dispatch job
        $batchId = uniqid('event_');
        \App\Jobs\BulkWhatsAppJob::dispatch($phones, $validated['message'], $batchId);
        
        return response()->json([
            'success' => true,
            'message' => 'Notification started for ' . count($phones) . ' registrants in background.',
            'batch_id' => $batchId,
            'total' => count($phones),
            'rate_limit' => $rateStatus,
        ]);
    }

    /**
     * Get rate limit status
     */
    public function rateLimitStatus(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'rate_limit' => $this->rateLimiter->getStatus(),
        ]);
    }

    /**
     * Get blast progress status
     */
    public function blastStatus(): JsonResponse
    {
        $status = cache()->get('bulk_whatsapp_status');
        $lastResult = cache()->get('bulk_whatsapp_last_result');

        return response()->json([
            'success' => true,
            'current' => $status,
            'last_result' => $lastResult,
        ]);
    }
}
