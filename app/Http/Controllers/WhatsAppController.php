<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use App\Services\WhatsAppRateLimiter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Event;

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
        $events = Event::latest()->get();
        
        return view('whatsapp.index', [
            'status' => $status,
            'provider' => $provider,
            'isConnected' => $status['connected'] ?? false,
            'events' => $events,
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
            'image_url' => 'nullable|url',
        ]);

        // Lookup massa data for variable replacement
        $phone = $validated['phone'];
        $massa = \App\Models\Massa::where('no_hp', $phone)
            ->with(['regency', 'province'])
            ->first();
        
        // Format message with variable replacement
        $formattedMessage = $this->formatMessageForPhone($validated['message'], $massa);

        if (!empty($validated['image_url'])) {
            $result = $this->whatsapp->sendImage($phone, $validated['image_url'], $formattedMessage);
        } else {
            $result = $this->whatsapp->sendText($phone, $formattedMessage);
        }
        
        return response()->json($result);
    }

    /**
     * Send interactive template message
     */
    public function sendTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string|max:4096',
            'buttons' => 'required|array|min:1|max:3',
            'buttons.*.id' => 'required|string',
            'buttons.*.text' => 'required|string|max:20',
            'title' => 'nullable|string|max:60',
            'footer' => 'nullable|string|max:60',
        ]);

        $result = $this->whatsapp->sendButtons(
            $validated['phone'],
            $validated['message'],
            $validated['buttons'],
            $validated['title'] ?? null,
            $validated['footer'] ?? null
        );
        
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
            'regency_id' => 'nullable|integer|exists:regencies,id',
            'gender' => 'nullable|in:L,P',
            'age_min' => 'nullable|integer|min:17|max:100',
            'age_max' => 'nullable|integer|min:17|max:100',
            'limit' => 'nullable|integer|min:1|max:1000',
            'image_url' => 'nullable|url',
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

        // Advanced demographic filters
        if (!empty($validated['regency_id'])) {
            $query->where('regency_id', $validated['regency_id']);
        }

        if (!empty($validated['gender'])) {
            $query->where('jenis_kelamin', $validated['gender']);
        }

        if (!empty($validated['age_min']) || !empty($validated['age_max'])) {
            $query->whereNotNull('tanggal_lahir');
            
            if (!empty($validated['age_min'])) {
                $maxDate = now()->subYears($validated['age_min'])->format('Y-m-d');
                $query->where('tanggal_lahir', '<=', $maxDate);
            }
            
            if (!empty($validated['age_max'])) {
                $minDate = now()->subYears($validated['age_max'] + 1)->format('Y-m-d');
                $query->where('tanggal_lahir', '>', $minDate);
            }
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

        // Dispatch job
        $batchId = uniqid('massa_');

        if (!empty($validated['image_url'])) {
             \App\Jobs\BulkImageWhatsAppJob::dispatch(
                $phones,
                $validated['image_url'],
                $validated['message']
             );
        } else {
             \App\Jobs\BulkWhatsAppJob::dispatch($phones, $validated['message'], $batchId);
        }
        
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
            'status' => 'nullable|in:all,confirmed,pending,checked_in',
            'image_url' => 'nullable|url',
        ]);

        $query = \App\Models\EventRegistration::query()
            ->where('event_id', $eventId)
            ->whereHas('massa', function($q) {
                $q->whereNotNull('no_hp')->where('no_hp', '!=', '');
            })
            ->with('massa:id,no_hp,nama_lengkap');

        if (isset($validated['status']) && $validated['status'] !== 'all') {
            if ($validated['status'] === 'checked_in') {
                $query->where('attendance_status', 'checked_in');
            } else {
                $query->where('registration_status', $validated['status']);
            }
        }

        $registrations = $query->get();
        
        if ($registrations->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No registrants found matching criteria.',
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
        
        if (!empty($validated['image_url'])) {
             \App\Jobs\BulkImageWhatsAppJob::dispatch(
                $phones,
                $validated['image_url'],
                $validated['message']
             );
        } else {
             \App\Jobs\BulkWhatsAppJob::dispatch($phones, $validated['message'], $batchId);
        }
        
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

    /**
     * List all campaigns
     */
    public function campaigns(): JsonResponse
    {
        $campaigns = \App\Models\WhatsappCampaign::orderBy('created_at', 'desc')
            ->with('creator:id,name')
            ->paginate(20);
        
        return response()->json($campaigns);
    }

    /**
     * Store a new campaign
     */
    public function storeCampaign(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'message' => 'required|string|max:4096',
            'scheduled_at' => 'required|date|after:now',
            'filters' => 'nullable|array',
            'filters.province_id' => 'nullable|integer',
            'filters.regency_id' => 'nullable|integer',
            'filters.gender' => 'nullable|in:L,P',
            'filters.age_min' => 'nullable|integer|min:17',
            'filters.age_max' => 'nullable|integer|max:100',
        ]);

        // Build query to get recipients
        $query = \App\Models\Massa::query()
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '');

        $filters = $validated['filters'] ?? [];
        
        if (!empty($filters['province_id'])) {
            $query->where('province_id', $filters['province_id']);
        }
        if (!empty($filters['regency_id'])) {
            $query->where('regency_id', $filters['regency_id']);
        }
        if (!empty($filters['gender'])) {
            $query->where('jenis_kelamin', $filters['gender']);
        }
        if (!empty($filters['age_min'])) {
            $query->where('tanggal_lahir', '<=', now()->subYears($filters['age_min'])->format('Y-m-d'));
        }
        if (!empty($filters['age_max'])) {
            $query->where('tanggal_lahir', '>', now()->subYears($filters['age_max'] + 1)->format('Y-m-d'));
        }

        $recipients = $query->pluck('no_hp')->toArray();

        $campaign = \App\Models\WhatsappCampaign::create([
            'name' => $validated['name'],
            'message' => $validated['message'],
            'scheduled_at' => $validated['scheduled_at'],
            'filters' => $filters,
            'recipients' => $recipients,
            'recipient_count' => count($recipients),
            'status' => 'scheduled',
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Campaign scheduled successfully',
            'data' => $campaign,
        ]);
    }

    /**
     * Cancel a campaign
     */
    public function cancelCampaign(int $id): JsonResponse
    {
        $campaign = \App\Models\WhatsappCampaign::findOrFail($id);
        
        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel campaign in current status',
            ], 400);
        }

        $campaign->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Campaign cancelled',
        ]);
    }

    /**
     * Send image message
     */
    public function sendImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'image' => 'required|file|image|max:5120', // Max 5MB
            'caption' => 'nullable|string|max:1024',
        ]);

        // Store uploaded image temporarily
        $path = $request->file('image')->store('whatsapp-uploads', 'public');
        $imageUrl = url('/storage/' . $path);

        $result = $this->whatsapp->sendImage(
            $validated['phone'],
            $imageUrl,
            $validated['caption'] ?? ''
        );

        return response()->json($result);
    }

    /**
     * Blast image to massa
     */
    public function blastImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => 'required|file|image|max:5120',
            'caption' => 'nullable|string|max:1024',
            'filter' => 'nullable|in:all,active,province',
            'province_id' => 'nullable|integer|exists:provinces,id',
            'regency_id' => 'nullable|integer|exists:regencies,id',
            'gender' => 'nullable|in:L,P',
            'limit' => 'nullable|integer|min:1|max:500',
        ]);

        // Store uploaded image
        $path = $request->file('image')->store('whatsapp-uploads', 'public');
        $imageUrl = url('/storage/' . $path);

        // Build query for recipients
        $query = \App\Models\Massa::query()
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '');

        if (($validated['filter'] ?? 'all') === 'active') {
            $query->where('status', 'active');
        }
        if (($validated['filter'] ?? null) === 'province' && isset($validated['province_id'])) {
            $query->where('province_id', $validated['province_id']);
        }
        if (!empty($validated['regency_id'])) {
            $query->where('regency_id', $validated['regency_id']);
        }
        if (!empty($validated['gender'])) {
            $query->where('jenis_kelamin', $validated['gender']);
        }
        if (isset($validated['limit'])) {
            $query->limit($validated['limit']);
        }

        $phones = $query->pluck('no_hp')->toArray();

        if (empty($phones)) {
            return response()->json([
                'success' => false,
                'message' => 'No recipients found with the given filter.',
            ], 400);
        }

        // Dispatch job for bulk image sending
        \App\Jobs\BulkImageWhatsAppJob::dispatch(
            $phones,
            $imageUrl,
            $validated['caption'] ?? ''
        );

        return response()->json([
            'success' => true,
            'message' => "Image blast queued for " . count($phones) . " recipients.",
            'recipient_count' => count($phones),
        ]);
    }

    /**
     * Format message with variable replacement for a given phone/massa
     */
    protected function formatMessageForPhone(string $message, ?\App\Models\Massa $massa): string
    {
        if (!$massa) {
            return $this->cleanMessageVariables($message);
        }
        
        // First, process conditional logic: {if:field=value}content{else}altcontent{endif}
        $message = $this->processConditionals($message, $massa);
        
        // Then, replace simple variables
        $vars = [
            '{nama}' => $massa->nama_lengkap ?? '',
            '{name}' => $massa->nama_lengkap ?? '',
            '{nik}' => $massa->nik ?? '',
            '{no_hp}' => $massa->no_hp ?? '',
            '{panggilan}' => ($massa->jenis_kelamin === 'L') ? 'Bapak' : 'Ibu',
            '{lokasi}' => $massa->regency?->name ?? ($massa->province?->name ?? ''),
            '{alamat}' => $massa->alamat ?? '',
            '{kabupaten}' => $massa->regency?->name ?? '',
            '{provinsi}' => $massa->province?->name ?? '',
            '{jenis_kelamin}' => ($massa->jenis_kelamin === 'L') ? 'Laki-laki' : 'Perempuan',
            '{pekerjaan}' => $massa->pekerjaan ?? '',
        ];

        return str_replace(array_keys($vars), array_values($vars), $message);
    }

    /**
     * Process conditional blocks: {if:field=value}...{else}...{endif}
     */
    protected function processConditionals(string $message, \App\Models\Massa $massa): string
    {
        // Pattern: {if:field=value}content{else}altcontent{endif} or {if:field=value}content{endif}
        $pattern = '/\{if:([a-z_]+)=([^\}]+)\}(.*?)(?:\{else\}(.*?))?\{endif\}/is';
        
        return preg_replace_callback($pattern, function($matches) use ($massa) {
            $field = $matches[1];
            $expectedValue = $matches[2];
            $trueContent = $matches[3];
            $falseContent = $matches[4] ?? '';
            
            // Get the actual value from massa
            $actualValue = $massa->{$field} ?? '';
            
            // Check if condition matches
            if (strtolower($actualValue) === strtolower($expectedValue)) {
                return $trueContent;
            } else {
                return $falseContent;
            }
        }, $message);
    }

    /**
     * Clean variables if no massa data found
     */
    protected function cleanMessageVariables(string $message): string
    {
        // Remove conditional blocks entirely (use the else content if present)
        $pattern = '/\{if:([a-z_]+)=([^\}]+)\}(.*?)(?:\{else\}(.*?))?\{endif\}/is';
        $message = preg_replace_callback($pattern, function($matches) {
            return $matches[4] ?? ''; // Return else content or empty
        }, $message);
        
        // Remove any remaining simple variables
        $vars = ['{nama}', '{name}', '{nik}', '{no_hp}', '{panggilan}', '{lokasi}', 
                 '{alamat}', '{kabupaten}', '{provinsi}', '{jenis_kelamin}', '{pekerjaan}'];
        return str_replace($vars, '', $message);
    }
}
