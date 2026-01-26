<?php

namespace App\Http\Controllers;

use App\Models\Massa;
use App\Models\Province;
use App\Services\MassaService;
use Illuminate\Support\Facades\DB;
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
        $query = Massa::with(['province:id,name', 'regency:id,name'])
            ->select(['id', 'nik', 'nama_lengkap', 'jenis_kelamin', 'no_hp', 'province_id', 'regency_id', 'latitude', 'created_at'])
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

        return view('massa.index', compact('massa', 'provinces', 'stats'));
    }

    /**
     * Show the form for creating a new massa.
     */
    public function create()
    {
        // Use cached provinces (24 hours)
        $provinces = cache()->remember('provinces_list', 86400, function () {
            return Province::select(['id', 'name'])->orderBy('name')->get();
        });
        
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
     * Export massa to CSV.
     */
    public function export(Request $request)
    {
        $query = Massa::with(['province', 'regency', 'district', 'village'])
            ->orderByDesc('created_at');

        // Apply same filters as index
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        if ($provinceId = $request->input('province')) {
            $query->where('province_id', $provinceId);
        }

        $filename = 'massa-export-' . date('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            
            // BOM for UTF-8 Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Headers
            fputcsv($handle, [
                'NIK', 'Nama Lengkap', 'Jenis Kelamin', 'Tempat Lahir', 'Tanggal Lahir',
                'No HP', 'Email', 'Alamat', 'RT', 'RW', 
                'Provinsi', 'Kabupaten/Kota', 'Kecamatan', 'Desa/Kelurahan',
                'Kode Pos', 'Pekerjaan', 'Terdaftar Pada'
            ]);

            $query->chunk(500, function ($massaChunk) use ($handle) {
                foreach ($massaChunk as $m) {
                    fputcsv($handle, [
                        "'" . $m->nik, // Force string in Excel
                        $m->nama_lengkap,
                        $m->jenis_kelamin,
                        $m->tempat_lahir,
                        $m->tanggal_lahir?->format('Y-m-d'),
                        $m->no_hp,
                        $m->email,
                        $m->alamat,
                        $m->rt,
                        $m->rw,
                        $m->province?->name,
                        $m->regency?->name,
                        $m->district?->name,
                        $m->village?->name,
                        $m->kode_pos,
                        $m->pekerjaan,
                        $m->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
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
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM
            
            // Headers matches validation keys mapping
            fputcsv($handle, [
                'nik', 'nama_lengkap', 'jenis_kelamin', 'no_hp', 
                'alamat', 'rt', 'rw', 'tempat_lahir', 'tanggal_lahir', 
                'email', 'pekerjaan'
            ]);
            
            // Dummy Data
            fputcsv($handle, [
                "'3201012010900001", "Contoh Nama", "L", "081234567890", 
                "Jl. Merdeka No. 1", "001", "002", "Jakarta", "1990-01-01", 
                "email@contoh.com", "Wiraswasta"
            ]);

            fclose($handle);
        }, 'template-import-massa.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Process CSV Import.
     */
    public function importProcess(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'overwrite' => 'nullable|boolean',
        ]);

        $file = $request->file('file');
        $resource = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($resource); // Get header
        
        // Remove BOM if exists in header[0]
        if (isset($header[0]) && str_starts_with($header[0], "\xEF\xBB\xBF")) {
            $header[0] = substr($header[0], 3);
        }

        $overwrite = $request->boolean('overwrite');
        $successCount = 0;
        $errors = [];
        $rowNumber = 1; // Header is row 1

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($resource)) !== false) {
                $rowNumber++;
                
                // Combine header with row data
                if (count($header) !== count($row)) {
                    $errors[] = "Baris $rowNumber: Jumlah kolom tidak sesuai.";
                    continue;
                }
                
                $data = array_combine($header, $row);
                
                // Basic cleanup
                $nik = preg_replace('/[^0-9]/', '', $data['nik'] ?? '');
                
                if (strlen($nik) !== 16) {
                    $errors[] = "Baris $rowNumber: NIK tidak valid ($nik)";
                    continue;
                }

                // Check duplicate
                $existing = Massa::where('nik', $nik)->first();
                if ($existing && !$overwrite) {
                    $errors[] = "Baris $rowNumber: NIK $nik sudah ada (Skipped)";
                    continue;
                }

                // Prepare Data
                $massaData = [
                    'nik' => $nik,
                    'nama_lengkap' => $data['nama_lengkap'] ?? null,
                    'jenis_kelamin' => in_array(strtoupper($data['jenis_kelamin'] ?? ''), ['L', 'P']) ? strtoupper($data['jenis_kelamin']) : 'L',
                    'no_hp' => $data['no_hp'] ?? null,
                    'alamat' => $data['alamat'] ?? 'Alamat belum diisi',
                    'rt' => $data['rt'] ?? null,
                    'rw' => $data['rw'] ?? null,
                    'tempat_lahir' => $data['tempat_lahir'] ?? null,
                    'tanggal_lahir' => isset($data['tanggal_lahir']) ? date('Y-m-d', strtotime($data['tanggal_lahir'])) : null,
                    'email' => $data['email'] ?? null,
                    'pekerjaan' => $data['pekerjaan'] ?? null,
                ];

                if (!$massaData['nama_lengkap']) {
                    $errors[] = "Baris $rowNumber: Nama Lengkap wajib diisi.";
                    continue;
                }
                
                if ($existing && $overwrite) {
                    $existing->update($massaData);
                } else {
                    Massa::create($massaData);
                }
                $successCount++;
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        } finally {
            fclose($resource);
        }

        $message = "Import selesai. Berhasil: $successCount.";
        if (count($errors) > 0) {
            $message .= " Gagal: " . count($errors) . ". Silakan cek detail di bawah."; // In real app, maybe log or flash list
            // For simplicity, passing errors to session limited
            return back()->with(['success' => $message, 'import_errors' => array_slice($errors, 0, 50)]);
        }

        return redirect()->route('massa.index')->with('success', $message);
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
