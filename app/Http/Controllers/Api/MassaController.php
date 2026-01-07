<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Massa;
use App\Models\MassaLoyalty;
use App\Services\MassaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MassaController extends Controller
{
    public function __construct(
        protected MassaService $massaService
    ) {}

    /**
     * List all massa with filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = Massa::with(['province', 'regency', 'district', 'village', 'loyalty'])
            ->withCount('registrations');

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Location filter
        if ($provinceId = $request->input('province_id')) {
            $query->where('province_id', $provinceId);
        }
        if ($regencyId = $request->input('regency_id')) {
            $query->where('regency_id', $regencyId);
        }
        if ($districtId = $request->input('district_id')) {
            $query->where('district_id', $districtId);
        }

        // Gender filter
        if ($gender = $request->input('jenis_kelamin')) {
            $query->where('jenis_kelamin', $gender);
        }

        // Loyalty tier filter
        if ($tier = $request->input('loyalty_tier')) {
            $query->whereHas('loyalty', fn($q) => $q->where('loyalty_tier', $tier));
        }

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $massa = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $massa,
        ]);
    }

    /**
     * Get massa details
     */
    public function show(Massa $massa): JsonResponse
    {
        $massa->load([
            'province',
            'regency',
            'district',
            'village',
            'loyalty',
            'registrations' => fn($q) => $q->with('event:id,name,event_start,status')
                ->orderByDesc('created_at')
                ->limit(10),
        ]);

        return response()->json([
            'success' => true,
            'data' => $massa,
        ]);
    }

    /**
     * Find by NIK
     */
    public function findByNik(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nik' => 'required|string|size:16',
        ]);

        $massa = Massa::findByNik($validated['nik']);

        if (!$massa) {
            return response()->json([
                'success' => false,
                'message' => 'Massa tidak ditemukan.',
                'exists' => false,
            ], 404);
        }

        $massa->load(['province', 'regency', 'district', 'loyalty']);

        return response()->json([
            'success' => true,
            'exists' => true,
            'data' => $massa,
        ]);
    }

    /**
     * Create new massa
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nik' => 'required|string|size:16|unique:massa,nik',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'no_hp' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'alamat' => 'nullable|string',
            'rt' => 'nullable|string|max:5',
            'rw' => 'nullable|string|max:5',
            'province_id' => 'nullable|exists:provinces,id',
            'regency_id' => 'nullable|exists:regencies,id',
            'district_id' => 'nullable|exists:districts,id',
            'village_id' => 'nullable|exists:villages,id',
            'kode_pos' => 'nullable|string|max:10',
            'pekerjaan' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
        ]);

        if (!$this->massaService->validateNik($validated['nik'])) {
            return response()->json([
                'success' => false,
                'message' => 'Format NIK tidak valid.',
            ], 422);
        }

        $massa = $this->massaService->findOrCreateByNik($validated);

        // Try to geocode address
        if (!$massa->latitude && $massa->full_address) {
            $this->massaService->updateCoordinates($massa);
        }

        return response()->json([
            'success' => true,
            'message' => 'Massa berhasil ditambahkan.',
            'data' => $massa->fresh(['province', 'regency', 'district', 'loyalty']),
        ], 201);
    }

    /**
     * Update massa
     */
    public function update(Request $request, Massa $massa): JsonResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => 'sometimes|string|max:255',
            'jenis_kelamin' => 'sometimes|in:L,P',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'no_hp' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'alamat' => 'nullable|string',
            'rt' => 'nullable|string|max:5',
            'rw' => 'nullable|string|max:5',
            'province_id' => 'nullable|exists:provinces,id',
            'regency_id' => 'nullable|exists:regencies,id',
            'district_id' => 'nullable|exists:districts,id',
            'village_id' => 'nullable|exists:villages,id',
            'kode_pos' => 'nullable|string|max:10',
            'pekerjaan' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $massa->update($validated);

        // Update geocoding if address changed
        $addressFields = ['alamat', 'province_id', 'regency_id', 'district_id', 'village_id'];
        if (collect($addressFields)->some(fn($f) => isset($validated[$f]))) {
            $this->massaService->updateCoordinates($massa);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data massa berhasil diupdate.',
            'data' => $massa->fresh(),
        ]);
    }

    /**
     * Delete massa (soft delete)
     */
    public function destroy(Massa $massa): JsonResponse
    {
        $massa->delete();

        return response()->json([
            'success' => true,
            'message' => 'Massa berhasil dihapus.',
        ]);
    }

    /**
     * Get event history for a massa
     */
    public function eventHistory(Massa $massa): JsonResponse
    {
        $registrations = $massa->registrations()
            ->with(['event:id,name,event_start,event_end,status,venue_name'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $registrations,
        ]);
    }

    /**
     * Recalculate loyalty stats
     */
    public function recalculateLoyalty(Massa $massa): JsonResponse
    {
        $loyalty = $massa->loyalty ?? MassaLoyalty::create(['massa_id' => $massa->id]);
        $loyalty->recalculate();

        return response()->json([
            'success' => true,
            'message' => 'Loyalty stats berhasil diperbarui.',
            'data' => $loyalty->fresh(),
        ]);
    }

    /**
     * Get loyalty leaderboard
     */
    public function loyaltyLeaderboard(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 20);
        
        $leaders = MassaLoyalty::with('massa:id,nama_lengkap,foto')
            ->topAttenders($limit)
            ->get()
            ->map(fn($l) => [
                'massa_id' => $l->massa_id,
                'nama' => $l->massa->nama_lengkap,
                'foto' => $l->massa->foto,
                'total_attended' => $l->total_events_attended,
                'attendance_rate' => $l->attendance_rate,
                'loyalty_tier' => $l->loyalty_tier,
                'points' => $l->points,
            ]);

        return response()->json([
            'success' => true,
            'data' => $leaders,
        ]);
    }

    /**
     * Geocode massa address
     */
    public function geocode(Massa $massa): JsonResponse
    {
        $result = $this->massaService->updateCoordinates($massa);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Koordinat berhasil diupdate.',
                'data' => [
                    'latitude' => $massa->fresh()->latitude,
                    'longitude' => $massa->fresh()->longitude,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mendapatkan koordinat dari alamat.',
        ], 422);
    }

    /**
     * Get massa location stats for heatmap
     */
    public function locationStats(): JsonResponse
    {
        $stats = $this->massaService->getLocationStats();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get massa with coordinates for map
     */
    public function mapData(Request $request): JsonResponse
    {
        $query = Massa::active()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        // Filter by province/regency if needed
        if ($provinceId = $request->input('province_id')) {
            $query->where('province_id', $provinceId);
        }
        if ($regencyId = $request->input('regency_id')) {
            $query->where('regency_id', $regencyId);
        }

        $limit = $request->input('limit', 1000);
        
        $data = $query->select(['id', 'nama_lengkap', 'latitude', 'longitude', 'province_id', 'regency_id'])
            ->limit($limit)
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'nama' => $m->nama_lengkap,
                'lat' => (float) $m->latitude,
                'lng' => (float) $m->longitude,
            ]);

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
        ]);
    }
}
