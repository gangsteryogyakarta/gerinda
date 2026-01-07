<?php

namespace App\Http\Controllers;

use App\Models\Massa;
use App\Models\Province;
use App\Services\MassaService;
use Illuminate\Http\Request;

class MassaController extends Controller
{
    public function __construct(
        protected MassaService $massaService
    ) {}

    /**
     * Display a listing of massa.
     */
    public function index(Request $request)
    {
        $query = Massa::with(['province', 'regency'])
            ->withCount('registrations');

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        // Filter by province
        if ($provinceId = $request->input('province')) {
            $query->where('province_id', $provinceId);
        }

        // Filter by geocoding status
        if ($request->input('geocoded') === 'yes') {
            $query->whereNotNull('latitude');
        } elseif ($request->input('geocoded') === 'no') {
            $query->whereNull('latitude');
        }

        $massa = $query->orderByDesc('created_at')->paginate(20);
        $provinces = Province::orderBy('name')->get();

        $stats = [
            'total' => Massa::count(),
            'geocoded' => Massa::whereNotNull('latitude')->count(),
            'this_month' => Massa::whereMonth('created_at', now()->month)->count(),
        ];

        return view('massa.index', compact('massa', 'provinces', 'stats'));
    }

    /**
     * Show the form for creating a new massa.
     */
    public function create()
    {
        $provinces = Province::orderBy('name')->get();
        
        return view('massa.create', compact('provinces'));
    }

    /**
     * Store a newly created massa.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required|string|size:16|unique:massa,nik',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'no_hp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'alamat' => 'required|string',
            'rt' => 'nullable|string|max:5',
            'rw' => 'nullable|string|max:5',
            'province_id' => 'nullable|exists:provinces,id',
            'regency_id' => 'nullable|exists:regencies,id',
            'district_id' => 'nullable|exists:districts,id',
            'village_id' => 'nullable|exists:villages,id',
            'kode_pos' => 'nullable|string|max:10',
            'pekerjaan' => 'nullable|string|max:100',
        ]);

        $massa = Massa::create($validated);

        // Try geocoding
        $this->massaService->geocodeMassa($massa);

        return redirect()->route('massa.show', $massa)
            ->with('success', 'Data massa berhasil ditambahkan!');
    }

    /**
     * Display the specified massa.
     */
    public function show(Massa $massa)
    {
        $massa->load([
            'province',
            'regency',
            'district',
            'village',
            'loyalty',
            'registrations' => fn($q) => $q->with('event')->orderByDesc('created_at'),
        ]);

        return view('massa.show', compact('massa'));
    }

    /**
     * Show the form for editing.
     */
    public function edit(Massa $massa)
    {
        $provinces = Province::orderBy('name')->get();
        
        return view('massa.edit', compact('massa', 'provinces'));
    }

    /**
     * Update the specified massa.
     */
    public function update(Request $request, Massa $massa)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'sometimes|string|max:255',
            'jenis_kelamin' => 'sometimes|in:L,P',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'no_hp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'alamat' => 'sometimes|string',
            'rt' => 'nullable|string|max:5',
            'rw' => 'nullable|string|max:5',
            'province_id' => 'nullable|exists:provinces,id',
            'regency_id' => 'nullable|exists:regencies,id',
            'district_id' => 'nullable|exists:districts,id',
            'village_id' => 'nullable|exists:villages,id',
            'kode_pos' => 'nullable|string|max:10',
            'pekerjaan' => 'nullable|string|max:100',
        ]);

        $addressChanged = $massa->alamat !== ($validated['alamat'] ?? $massa->alamat);

        $massa->update($validated);

        // Re-geocode if address changed
        if ($addressChanged) {
            $this->massaService->geocodeMassa($massa);
        }

        return redirect()->route('massa.show', $massa)
            ->with('success', 'Data massa berhasil diperbarui!');
    }

    /**
     * Remove the specified massa.
     */
    public function destroy(Massa $massa)
    {
        if ($massa->registrations()->exists()) {
            return back()->with('error', 'Massa tidak dapat dihapus karena memiliki riwayat registrasi.');
        }

        $massa->delete();

        return redirect()->route('massa.index')
            ->with('success', 'Data massa berhasil dihapus!');
    }

    /**
     * Lookup massa by NIK (AJAX)
     */
    public function lookupNik(Request $request)
    {
        $nik = $request->input('nik');
        
        if (!$nik || strlen($nik) !== 16) {
            return response()->json(['found' => false]);
        }

        $massa = Massa::where('nik', $nik)->first();

        if ($massa) {
            return response()->json([
                'found' => true,
                'data' => [
                    'id' => $massa->id,
                    'nama_lengkap' => $massa->nama_lengkap,
                    'no_hp' => $massa->no_hp,
                    'alamat' => $massa->full_address,
                ],
            ]);
        }

        return response()->json(['found' => false]);
    }
}
