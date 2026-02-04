<?php

namespace App\Http\Controllers;

use App\Models\Massa;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Models\Village;
use App\Services\MassaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MassaExport;
use App\Exports\MassaTemplateExport;
use App\Imports\MassaImport;

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
        $query = Massa::with(['province:id,name', 'regency:id,name'])
            ->select(['id', 'nik', 'nama_lengkap', 'kategori_massa', 'sub_kategori', 'jenis_kelamin', 'no_hp', 'province_id', 'regency_id', 'latitude', 'created_at'])
            ->withCount(['registrations' => fn($q) => $q->whereHas('event')]);

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

        // Filter by kategori_massa
        if ($kategori = $request->input('kategori_massa')) {
            $query->where('kategori_massa', $kategori);
        }

        // Filter by geocoding status
        if ($request->input('geocoded') === 'yes') {
            $query->whereNotNull('latitude');
        } elseif ($request->input('geocoded') === 'no') {
            $query->whereNull('latitude');
        }

        // Filter by district (Kecamatan)
        if ($districtId = $request->input('district')) {
            $query->where('district_id', $districtId);
        }

        // Filter by village (Kelurahan)
        if ($villageId = $request->input('village')) {
            $query->where('village_id', $villageId);
        }

        $massa = $query->orderByDesc('created_at')->paginate(20);
        
        $provinces = Province::orderBy('name')->get();
        
        // Load dependent dropdowns if parent is selected
        $districts = collect();
        $villages = collect();

        // Note: Assuming we mostly work with DIY (Province ID 34)
        // Ideally we filter based on selected parent. 
        // For simplicity in filter view, we might not load all upfront unless selected.
        
        if ($request->input('district')) {
            $villages = Village::where('district_id', $request->input('district'))->orderBy('name')->get();
        }
        
        // Helper queries for filter dropdowns (optional: load all if needed, but better to load dynamically via JS)
        // For now, let's just pass empty collections or handle it in the view via AJAX/Blade logic if simpler.
        // Actually, to make "All" visible, we might want to load them if a parent is selected.
        
        // Cache provinces for 24 hours (static data)
        $provinces = cache()->remember('provinces_list', 86400, function () {
            return Province::select(['id', 'name'])->orderBy('name')->get();
        });

        // Consolidated stats with single query (cache for 5 minutes)
        $stats = cache()->remember('massa_index_stats', 300, function () {
            return Massa::selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN latitude IS NOT NULL THEN 1 ELSE 0 END) as geocoded,
                SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as this_month
            ', [now()->startOfMonth()])
            ->first()
            ->toArray();
        });

        return view('massa.index', compact('massa', 'stats', 'provinces', 'districts', 'villages'));
    }

    /**
     * Show the form for creating a new massa.
     */
    public function create()
    {
        // All provinces
        $provinces = Province::orderBy('name')->select(['id', 'name'])->get();
        
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
            'kategori_massa' => 'required|in:Pengurus,Simpatisan',
            'sub_kategori' => 'required_if:kategori_massa,Pengurus|nullable|in:DPD DIY,DPC Sleman,DPC Kota Yogyakarta,DPC Bantul,DPC Kulon Progo,DPC Gunungkidul,PAC',
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
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'geocode_source' => 'nullable|string|max:30',
        ]);

        // Set geocoded_at if coordinates provided
        if (!empty($validated['latitude']) && !empty($validated['longitude'])) {
            $validated['geocoded_at'] = now();
        }

        $massa = Massa::create($validated);

        // If no coordinates, dispatch geocoding job
        if (empty($massa->latitude) || empty($massa->longitude)) {
            \App\Jobs\GeocodeAddressJob::dispatch($massa->id);
        }

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
            'registrations' => fn($q) => $q->whereHas('event')->with('event')->orderByDesc('created_at'),
        ]);

        return view('massa.show', compact('massa'));
    }

    /**
     * Show the form for editing.
     */
    public function edit(Massa $massa)
    {
        $provinces = Province::orderBy('name')->get();
        
        $regencies = collect();
        if ($massa->province_id) {
            $regencies = \App\Models\Regency::where('province_id', $massa->province_id)->orderBy('name')->get();
        }

        $districts = collect();
        if ($massa->regency_id) {
            $districts = \App\Models\District::where('regency_id', $massa->regency_id)->orderBy('name')->get();
        }

        $villages = collect();
        if ($massa->district_id) {
            $villages = \App\Models\Village::where('district_id', $massa->district_id)->orderBy('name')->get();
        }
        
        return view('massa.edit', compact('massa', 'provinces', 'regencies', 'districts', 'villages'));
    }

    /**
     * Update the specified massa.
     */
    public function update(Request $request, Massa $massa)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'sometimes|string|max:255',
            'kategori_massa' => 'sometimes|in:Pengurus,Simpatisan',
            'sub_kategori' => 'required_if:kategori_massa,Pengurus|nullable|in:DPD DIY,DPC Sleman,DPC Kota Yogyakarta,DPC Bantul,DPC Kulon Progo,DPC Gunungkidul,PAC',
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



    public function export(Request $request)
    {
        return Excel::download(new MassaExport($request), 'massa-export-' . date('Y-m-d-His') . '.xlsx');
    }

    /**
     * Show import form.
     */
    public function importForm()
    {
        return view('massa.import');
    }

    /**
     * Download import template.
     */
    public function downloadTemplate()
    {
        return Excel::download(new MassaTemplateExport, 'template-import-massa.xlsx');
    }

    /**
     * Process CSV Import.
     */
    public function importProcess(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB
        ]);

        try {
            Excel::import(new MassaImport($request->has('overwrite')), $request->file('file'));
            return redirect()->route('massa.index')->with('success', 'Import data berhasil diproses.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
             $failures = $e->failures();
             $errors = [];
             foreach ($failures as $failure) {
                 $errors[] = 'Baris ' . $failure->row() . ': ' . implode(', ', $failure->errors());
             }
             return back()->with('import_errors', array_slice($errors, 0, 50));
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified massa.
     */
    public function destroy(Massa $massa)
    {
        // if ($massa->registrations()->exists()) {
        //     return back()->with('error', 'Massa tidak dapat dihapus karena memiliki riwayat registrasi.');
        // }

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
