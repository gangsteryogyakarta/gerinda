<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Massa;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Models\Village;
use App\Services\MassaService;
use App\Services\RegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicController extends Controller
{
    public function __construct(
        protected MassaService $massaService,
        protected RegistrationService $registrationService
    ) {}

    /**
     * Show list of open events for public registration
     */
    public function index()
    {
        $events = Event::where('status', 'published')
            ->where(function($q) {
                $q->whereNull('registration_end')
                    ->orWhere('registration_end', '>', now());
            })
            ->orderBy('event_start')
            ->get();

        return view('public.index', compact('events'));
    }

    /**
     * Show registration form for specific event
     */
    public function register(Event $event)
    {
        if (!$event->registration_open) {
            return redirect()->route('public.index')
                ->with('error', 'Pendaftaran untuk event ini sudah ditutup.');
        }

        // Default to DI Yogyakarta
        $provinces = Province::orderBy('name')->get();
        $defaultProvince = Province::where('name', 'like', '%Yogyakarta%')->first();
        $regencies = $defaultProvince 
            ? Regency::where('province_id', $defaultProvince->id)->orderBy('name')->get()
            : collect();

        return view('public.register', compact('event', 'provinces', 'defaultProvince', 'regencies'));
    }

    /**
     * Process registration
     */
    public function store(Request $request, Event $event)
    {
        if (!$event->isRegistrationOpen()) {
            return redirect()->route('public.index')
                ->with('error', 'Pendaftaran untuk event ini sudah ditutup.');
        }

        $validated = $request->validate([
            'nik' => 'required|string|size:16',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'no_hp' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'alamat' => 'required|string',
            'rt' => 'nullable|string|max:5',
            'rw' => 'nullable|string|max:5',
            'province_id' => 'required|exists:provinces,id',
            'regency_id' => 'required|exists:regencies,id',
            'district_id' => 'nullable|exists:districts,id',
            'village_id' => 'nullable|exists:villages,id',
            'kode_pos' => 'nullable|string|max:10',
            'pekerjaan' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Find or create massa
            $massa = Massa::where('nik', $validated['nik'])->first();
            
            if (!$massa) {
                $massa = Massa::create($validated);
                // Try geocoding
                $this->massaService->geocodeMassa($massa);
            } else {
                // Update existing massa data
                $massa->update([
                    'no_hp' => $validated['no_hp'],
                    'email' => $validated['email'] ?? $massa->email,
                ]);
            }

            // Check if already registered
            $existingReg = $event->registrations()
                ->where('massa_id', $massa->id)
                ->first();

            if ($existingReg) {
                DB::rollBack();
                return redirect()->route('public.success', ['registration' => $existingReg])
                    ->with('info', 'Anda sudah terdaftar di event ini.');
            }

            // Register to event with WA consent from form
            $registration = $this->registrationService->register($event, $massa, [
                'registration_source' => 'public_form',
                'wa_consent' => $request->has('wa_consent'),
            ]);

            DB::commit();

            return redirect()->route('public.success', ['registration' => $registration]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show success page after registration
     */
    public function success(Request $request)
    {
        $registration = \App\Models\EventRegistration::with(['event', 'massa'])
            ->findOrFail($request->registration);

        return view('public.success', compact('registration'));
    }

    /**
     * Check NIK for existing massa or parse NIK data (AJAX)
     */
    public function checkNik(Request $request)
    {
        $nik = $request->input('nik');
        
        if (!$nik || strlen($nik) !== 16) {
            return response()->json(['found' => false, 'error' => 'NIK harus 16 digit']);
        }

        // First check local database
        $massa = Massa::where('nik', $nik)->first();

        if ($massa) {
            return response()->json([
                'found' => true,
                'data' => [
                    'nama_lengkap' => $massa->nama_lengkap,
                    'jenis_kelamin' => $massa->jenis_kelamin,
                    'tempat_lahir' => $massa->tempat_lahir,
                    'tanggal_lahir' => $massa->tanggal_lahir?->format('Y-m-d'),
                    'no_hp' => $massa->no_hp,
                    'email' => $massa->email,
                    'alamat' => $massa->alamat,
                ],
            ]);
        }

        // Parse NIK to extract embedded data
        $nikService = app(\App\Services\NikVerificationService::class);
        $result = $nikService->verify($nik);

        if ($result['success']) {
            $data = $result['data'];
            
            return response()->json([
                'found' => false,
                'parsed_data' => [
                    'jenis_kelamin' => $data['jenis_kelamin'],
                    'tanggal_lahir' => $data['tanggal_lahir'],
                    'province_name' => $data['province_name'],
                    'regency_name' => $data['regency_name'],
                    'is_yogyakarta' => $data['is_yogyakarta'],
                ],
            ]);
        }

        return response()->json([
            'found' => false,
            'error' => $result['error'] ?? 'NIK tidak valid',
        ]);
    }

    /**
     * Get regencies by province (AJAX)
     */
    public function regencies(Request $request)
    {
        $provinceId = $request->input('province_id');
        
        $regencies = Regency::where('province_id', $provinceId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($regencies);
    }

    /**
     * Get districts by regency (AJAX)
     */
    public function districts(Request $request)
    {
        $regencyId = $request->input('regency_id');
        
        $districts = District::where('regency_id', $regencyId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($districts);
    }

    /**
     * Get villages by district (AJAX)
     */
    public function villages(Request $request)
    {
        $districtId = $request->input('district_id');
        
        $villages = Village::where('district_id', $districtId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($villages);
    }
}
