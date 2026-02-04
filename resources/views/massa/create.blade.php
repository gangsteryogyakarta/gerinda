@extends('layouts.app')

@section('title', 'Tambah Massa')

@section('content')
    <!-- Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1>➕ Tambah Massa Baru</h1>
            <p>Isi form di bawah untuk menambahkan data massa baru</p>
        </div>
        <div class="page-header-right">
            <a href="{{ route('massa.index') }}" class="btn btn-secondary">
                <i data-lucide="arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>

    <form action="{{ route('massa.store') }}" method="POST">
        @csrf
        
        <div class="form-grid">
            <!-- Main Form -->
            <div class="form-main">
                <!-- Identity -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">🪪 Data Identitas</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">NIK <span class="required">*</span></label>
                            <input type="text" name="nik" class="form-input @error('nik') is-invalid @enderror" 
                                   value="{{ old('nik') }}" placeholder="16 digit NIK" maxlength="16" required>
                            @error('nik')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                            <span class="form-hint">NIK harus unik dan terdiri dari 16 digit</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-input @error('nama_lengkap') is-invalid @enderror" 
                                   value="{{ old('nama_lengkap') }}" placeholder="Nama sesuai KTP" required>
                            @error('nama_lengkap')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Kategori Massa <span class="required">*</span></label>
                            <select name="kategori_massa" id="kategori_massa" class="form-input" required onchange="toggleSubKategori()">
                                <option value="Simpatisan" {{ old('kategori_massa') === 'Simpatisan' ? 'selected' : '' }}>Simpatisan</option>
                                <option value="Pengurus" {{ old('kategori_massa') === 'Pengurus' ? 'selected' : '' }}>Pengurus</option>
                            </select>
                        </div>

                        <div class="form-group" id="sub_kategori_group" style="display: none;">
                            <label class="form-label">Sub Kategori <span class="required">*</span></label>
                            <select name="sub_kategori" id="sub_kategori" class="form-input">
                                <option value="">-- Pilih --</option>
                                <option value="DPD DIY" {{ old('sub_kategori') === 'DPD DIY' ? 'selected' : '' }}>DPD DIY</option>
                                <option value="DPC Sleman" {{ old('sub_kategori') === 'DPC Sleman' ? 'selected' : '' }}>DPC Sleman</option>
                                <option value="DPC Kota Yogyakarta" {{ old('sub_kategori') === 'DPC Kota Yogyakarta' ? 'selected' : '' }}>DPC Kota Yogyakarta</option>
                                <option value="DPC Bantul" {{ old('sub_kategori') === 'DPC Bantul' ? 'selected' : '' }}>DPC Bantul</option>
                                <option value="DPC Kulon Progo" {{ old('sub_kategori') === 'DPC Kulon Progo' ? 'selected' : '' }}>DPC Kulon Progo</option>
                                <option value="DPC Gunungkidul" {{ old('sub_kategori') === 'DPC Gunungkidul' ? 'selected' : '' }}>DPC Gunungkidul</option>
                                <option value="PAC" {{ old('sub_kategori') === 'PAC' ? 'selected' : '' }}>PAC</option>
                            </select>
                        </div>

                        <script>
                            function toggleSubKategori() {
                                const kategori = document.getElementById('kategori_massa').value;
                                const subGroup = document.getElementById('sub_kategori_group');
                                const subInput = document.getElementById('sub_kategori');
                                
                                if (kategori === 'Pengurus') {
                                    subGroup.style.display = 'block';
                                    subInput.required = true;
                                } else {
                                    subGroup.style.display = 'none';
                                    subInput.required = false;
                                    subInput.value = '';
                                }
                            }
                            
                            // Initialize on load
                            document.addEventListener('DOMContentLoaded', toggleSubKategori);
                        </script>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Jenis Kelamin <span class="required">*</span></label>
                                <select name="jenis_kelamin" class="form-input" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="L" {{ old('jenis_kelamin') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('jenis_kelamin') === 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" class="form-input" value="{{ old('tanggal_lahir') }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-input" value="{{ old('tempat_lahir') }}" placeholder="Kota kelahiran">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Pekerjaan</label>
                            <input type="text" name="pekerjaan" class="form-input" value="{{ old('pekerjaan') }}" placeholder="Jenis pekerjaan">
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">📍 Alamat</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">Alamat Lengkap <span class="required">*</span></label>
                            <textarea name="alamat" class="form-input @error('alamat') is-invalid @enderror" rows="3" 
                                      placeholder="Nama jalan, nomor rumah, perumahan, dll..." required>{{ old('alamat') }}</textarea>
                            @error('alamat')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-row" style="grid-template-columns: 1fr 1fr 1fr;">
                            <div class="form-group">
                                <label class="form-label">RT</label>
                                <input type="text" name="rt" class="form-input" value="{{ old('rt') }}" placeholder="001">
                            </div>
                            <div class="form-group">
                                <label class="form-label">RW</label>
                                <input type="text" name="rw" class="form-input" value="{{ old('rw') }}" placeholder="001">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Kode Pos</label>
                                <input type="text" name="kode_pos" class="form-input" value="{{ old('kode_pos') }}" placeholder="12345">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Provinsi</label>
                                <select name="province_id" id="province_id" class="form-input">
                                    <option value="">-- Pilih Provinsi --</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->id }}" {{ old('province_id') == $province->id ? 'selected' : '' }}>
                                            {{ $province->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Kabupaten/Kota</label>
                                <select name="regency_id" id="regency_id" class="form-input">
                                    <option value="">-- Pilih Kabupaten --</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Kecamatan</label>
                                <select name="district_id" id="district_id" class="form-input">
                                    <option value="">-- Pilih Kecamatan --</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Kelurahan/Desa</label>
                                <select name="village_id" id="village_id" class="form-input">
                                    <option value="">-- Pilih Kelurahan --</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="form-sidebar">
                <!-- Contact -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">📞 Kontak</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">Nomor HP</label>
                            <input type="tel" name="no_hp" class="form-input" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx">
                            <span class="form-hint">Untuk pengiriman tiket via WhatsApp</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-input" value="{{ old('email') }}" placeholder="email@example.com">
                        </div>
                    </div>
                </div>

                <!-- Info -->
                <div class="card">
                    <div class="card-body">
                        <div class="info-box">
                            <i data-lucide="info" style="color: var(--info);"></i>
                            <div>
                                <strong>Geocoding Otomatis</strong>
                                <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">
                                    Sistem akan otomatis menentukan koordinat lokasi berdasarkan alamat yang dimasukkan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i data-lucide="save"></i>
                            Simpan Data
                        </button>
                        <a href="{{ route('massa.index') }}" class="btn btn-secondary btn-block" style="margin-top: 12px;">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('styles')
<style>
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 24px;
    }

    @media (max-width: 1024px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }

    .form-main .card,
    .form-sidebar .card {
        margin-bottom: 24px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group:last-child {
        margin-bottom: 0;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .form-label .required {
        color: var(--primary);
    }

    .form-input {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 12px 16px;
        color: var(--text-primary);
        font-size: 14px;
        width: 100%;
        outline: none;
    }

    /* Style dropdown options to be light grey as requested */
    select.form-input option {
        background-color: #e5e7eb; /* Light Grey (Tailwind gray-200) */
        color: #1f2937; /* Dark Grey Text */
        padding: 8px;
    }

    .form-input:focus {
        border-color: var(--primary);
    }

    .form-error {
        display: block;
        color: var(--danger);
        font-size: 12px;
        margin-top: 6px;
    }

    .form-hint {
        display: block;
        color: var(--text-muted);
        font-size: 12px;
        margin-top: 6px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .btn-block {
        width: 100%;
    }

    textarea.form-input {
        resize: vertical;
    }

    .info-box {
        display: flex;
        gap: 12px;
        padding: 16px;
        background: rgba(59, 130, 246, 0.1);
        border-radius: var(--radius);
    }
</style>
@endpush

@push('scripts')
<script>
    // Cascading location selects
    document.getElementById('province_id').addEventListener('change', async function() {
        const regencySelect = document.getElementById('regency_id');
        const districtSelect = document.getElementById('district_id');
        const villageSelect = document.getElementById('village_id');
        
        regencySelect.innerHTML = '<option value="">-- Pilih Kabupaten --</option>';
        districtSelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
        villageSelect.innerHTML = '<option value="">-- Pilih Kelurahan --</option>';
        
        if (!this.value) return;
        
        try {
            // Updated endpoint structure: /locations/regencies/{province_id}
            const response = await fetch(`/api/v1/locations/regencies/${this.value}`);
            const data = await response.json();
            
            // API returns direct array, not wrapped in data
            const items = Array.isArray(data) ? data : (data.data || []);
            
            items.forEach(item => {
                regencySelect.innerHTML += `<option value="${item.id}">${item.name}</option>`;
            });
        } catch (error) {
            console.error('Error loading regencies:', error);
        }
    });

    document.getElementById('regency_id').addEventListener('change', async function() {
        const districtSelect = document.getElementById('district_id');
        const villageSelect = document.getElementById('village_id');
        
        districtSelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
        villageSelect.innerHTML = '<option value="">-- Pilih Kelurahan --</option>';
        
        if (!this.value) return;
        
        try {
            // Updated endpoint structure: /locations/districts/{regency_id}
            const response = await fetch(`/api/v1/locations/districts/${this.value}`);
            const data = await response.json();
            
            const items = Array.isArray(data) ? data : (data.data || []);
            
            items.forEach(item => {
                districtSelect.innerHTML += `<option value="${item.id}">${item.name}</option>`;
            });
        } catch (error) {
            console.error('Error loading districts:', error);
        }
    });

    document.getElementById('district_id').addEventListener('change', async function() {
        const villageSelect = document.getElementById('village_id');
        
        villageSelect.innerHTML = '<option value="">-- Pilih Kelurahan --</option>';
        
        if (!this.value) return;
        
        try {
            // Updated endpoint structure: /locations/villages/{district_id}
            const response = await fetch(`/api/v1/locations/villages/${this.value}`);
            const data = await response.json();
            
            const items = Array.isArray(data) ? data : (data.data || []);
            
            items.forEach(item => {
                villageSelect.innerHTML += `<option value="${item.id}">${item.name}</option>`;
            });
        } catch (error) {
            console.error('Error loading villages:', error);
        }
    });

    // Auto-fill postal code on village change
    document.getElementById('village_id').addEventListener('change', async function() {
        const postalCodeInput = document.querySelector('input[name="kode_pos"]');
        if (!this.value || postalCodeInput.value) return; // Don't overwrite if not empty? Maybe optional.
        
        try {
            const response = await fetch(`/api/v1/locations/postal-code/${this.value}`);
            const data = await response.json();
            if (data.postal_code) {
                postalCodeInput.value = data.postal_code;
            }
        } catch (error) {
            console.error('Error loading postal code:', error);
        }
    });
</script>
@endpush
