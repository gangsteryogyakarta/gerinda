@props([
    'provinceId' => null,
    'regencyId' => null,
    'districtId' => null,
    'villageId' => null,
    'postalCode' => null,
    'provinceName' => 'province_id',
    'regencyName' => 'regency_id',
    'districtName' => 'district_id',
    'villageName' => 'village_id',
    'postalCodeName' => 'postal_code',
    'required' => false
])

<div class="location-selector" 
     data-province-id="{{ $provinceId }}" 
     data-regency-id="{{ $regencyId }}" 
     data-district-id="{{ $districtId }}"
     data-village-id="{{ $villageId }}">
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Province -->
        <div class="form-group mb-3 full-width" style="grid-column: 1 / -1;">
            <label for="{{ $provinceName }}" class="form-label">Provinsi {!! $required ? '<span class="required">*</span>' : '' !!}</label>
            <select name="{{ $provinceName }}" id="{{ $provinceName }}" class="form-input" {{ $required ? 'required' : '' }}>
                <option value="">-- Pilih Provinsi --</option>
            </select>
            @error($provinceName)
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <!-- Regency -->
        <div class="form-group mb-3">
            <label for="{{ $regencyName }}" class="form-label">Kabupaten/Kota {!! $required ? '<span class="required">*</span>' : '' !!}</label>
            <select name="{{ $regencyName }}" id="{{ $regencyName }}" class="form-input" {{ $required ? 'required' : '' }} disabled>
                <option value="">-- Pilih Kabupaten/Kota --</option>
            </select>
            @error($regencyName)
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <!-- District -->
        <div class="form-group mb-3">
            <label for="{{ $districtName }}" class="form-label">Kecamatan {!! $required ? '<span class="required">*</span>' : '' !!}</label>
            <select name="{{ $districtName }}" id="{{ $districtName }}" class="form-input" {{ $required ? 'required' : '' }} disabled>
                <option value="">-- Pilih Kecamatan --</option>
            </select>
            @error($districtName)
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <!-- Village -->
        <div class="form-group mb-3">
            <label for="{{ $villageName }}" class="form-label">Kelurahan/Desa {!! $required ? '<span class="required">*</span>' : '' !!}</label>
            <select name="{{ $villageName }}" id="{{ $villageName }}" class="form-input" {{ $required ? 'required' : '' }} disabled>
                <option value="">-- Pilih Kelurahan/Desa --</option>
            </select>
            @error($villageName)
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <!-- Postal Code -->
        <div class="form-group mb-3">
            <label for="{{ $postalCodeName }}" class="form-label">Kode Pos</label>
            <input type="text" name="{{ $postalCodeName }}" id="{{ $postalCodeName }}" class="form-input bg-gray-100" value="{{ old($postalCodeName, $postalCode) }}" readonly placeholder="Otomatis terisi">
            @error($postalCodeName)
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

@push('scripts')
<script>
    // ... script content ...
    // (Keeping the script content exactly as is, just wrapped in push)
document.addEventListener('DOMContentLoaded', function() {
    const selector = document.querySelector('.location-selector');
    // ... existing JS logic ...
    const provinceSelect = document.getElementById('{{ $provinceName }}');
    const regencySelect = document.getElementById('{{ $regencyName }}');
    const districtSelect = document.getElementById('{{ $districtName }}');
    const villageSelect = document.getElementById('{{ $villageName }}');
    const postalCodeInput = document.getElementById('{{ $postalCodeName }}');

    const initialProvinceId = selector.dataset.provinceId;
    const initialRegencyId = selector.dataset.regencyId;
    const initialDistrictId = selector.dataset.districtId;
    const initialVillageId = selector.dataset.villageId;

    // Helper to populate select
    function populateSelect(element, data, selectedId = null, placeholder = '-- Pilih --') {
        element.innerHTML = `<option value="">${placeholder}</option>`;
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;
            if (selectedId && item.id == selectedId) {
                option.selected = true;
            }
            element.appendChild(option);
        });
        element.disabled = false;
    }

    function resetSelect(element, placeholder = '-- Pilih --') {
        element.innerHTML = `<option value="">${placeholder}</option>`;
        element.disabled = true;
    }

    // Load Provinces
    fetch('/api/v1/locations/provinces')
        .then(response => response.json())
        .then(data => {
            populateSelect(provinceSelect, data, initialProvinceId, '-- Pilih Provinsi --');
            if (initialProvinceId) {
                loadRegencies(initialProvinceId, initialRegencyId);
            }
        });

    // Province Change
    provinceSelect.addEventListener('change', function() {
        const provinceId = this.value;
        resetSelect(regencySelect, '-- Pilih Kabupaten/Kota --');
        resetSelect(districtSelect, '-- Pilih Kecamatan --');
        resetSelect(villageSelect, '-- Pilih Kelurahan/Desa --');
        postalCodeInput.value = '';

        if (provinceId) {
            loadRegencies(provinceId);
        }
    });

    // Load Regencies
    function loadRegencies(provinceId, selectedId = null) {
        regencySelect.disabled = true; // Show loading state visually if needed
        fetch(`/api/v1/locations/regencies/${provinceId}`)
            .then(response => response.json())
            .then(data => {
                populateSelect(regencySelect, data, selectedId, '-- Pilih Kabupaten/Kota --');
                if (selectedId) {
                    loadDistricts(selectedId, initialDistrictId);
                }
            });
    }

    // Regency Change
    regencySelect.addEventListener('change', function() {
        const regencyId = this.value;
        resetSelect(districtSelect, '-- Pilih Kecamatan --');
        resetSelect(villageSelect, '-- Pilih Kelurahan/Desa --');
        postalCodeInput.value = '';

        if (regencyId) {
            loadDistricts(regencyId);
        }
    });

    // Load Districts
    function loadDistricts(regencyId, selectedId = null) {
        districtSelect.disabled = true;
        fetch(`/api/v1/locations/districts/${regencyId}`)
            .then(response => response.json())
            .then(data => {
                populateSelect(districtSelect, data, selectedId, '-- Pilih Kecamatan --');
                if (selectedId) {
                    loadVillages(selectedId, initialVillageId);
                }
            });
    }

    // District Change
    districtSelect.addEventListener('change', function() {
        const districtId = this.value;
        resetSelect(villageSelect, '-- Pilih Kelurahan/Desa --');
        postalCodeInput.value = '';

        if (districtId) {
            loadVillages(districtId);
        }
    });

    // Load Villages
    function loadVillages(districtId, selectedId = null) {
        villageSelect.disabled = true;
        fetch(`/api/v1/locations/villages/${districtId}`)
            .then(response => response.json())
            .then(data => {
                populateSelect(villageSelect, data, selectedId, '-- Pilih Kelurahan/Desa --');
                if (selectedId) {
                    const selectedVillage = data.find(v => v.id == selectedId);
                    if (selectedVillage && selectedVillage.postal_code) {
                        postalCodeInput.value = selectedVillage.postal_code;
                    } else if (selectedId) {
                         fetchPostalCode(selectedId);
                    }
                }
            });
    }

    // Village Change
    villageSelect.addEventListener('change', function() {
        const villageId = this.value;
        postalCodeInput.value = '';
        if (villageId) {
            fetchPostalCode(villageId);
        }
    });
    
    function fetchPostalCode(villageId) {
         fetch(`/api/v1/locations/postal-code/${villageId}`)
            .then(response => response.json())
            .then(data => {
                if (data.postal_code) {
                    postalCodeInput.value = data.postal_code;
                }
            });
    }
});
</script>

<style>
/* Scoped styles for location selector to ensure consistency */
.location-selector .form-input {
    width: 100%;
    /* Ensure background matches - use white/variable fallback */
    background-color: var(--bg-secondary, #ffffff); 
    border: 1px solid var(--border-color, #CBD5E1);
    border-radius: var(--radius, 10px); /* Specific radius match */
    padding: 0.75rem 1rem; /* 12px 16px */
    color: var(--text-primary, #1E293B);
    font-size: 0.9375rem; /* ~15px */
    font-family: inherit;
    outline: none;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

/* Specific Reset for Selects */
.location-selector select.form-input {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 1rem;
    padding-right: 2.5rem;
    height: 48px; /* Force height match */
    line-height: 1.5;
}

.location-selector .form-input:focus {
    border-color: var(--primary, #DC2626);
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.location-selector .form-input:disabled {
    background-color: var(--bg-tertiary, #F1F5F9);
    cursor: not-allowed;
    opacity: 0.8;
}

.location-selector .form-label {
    display: block;
    font-size: 0.8125rem; /* 13px */
    font-weight: 600;
    margin-bottom: 0.375rem;
    color: var(--text-secondary, #64748B);
    text-transform: capitalize;
}

.location-selector .text-danger, .location-selector .required {
    color: var(--danger, #DC2626);
}

.location-selector .form-error {
    color: var(--danger, #DC2626);
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

/* Ensure empty/invalid selects look right */
.location-selector select.form-input:invalid,
.location-selector select.form-input option[value=""] {
    color: var(--text-muted, #94A3B8);
}
</style>
@endpush
