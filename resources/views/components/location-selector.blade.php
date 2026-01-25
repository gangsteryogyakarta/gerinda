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
        <div class="form-group mb-3">
            <label for="{{ $provinceName }}" class="form-label">Provinsi {!! $required ? '<span class="text-danger">*</span>' : '' !!}</label>
            <select name="{{ $provinceName }}" id="{{ $provinceName }}" class="form-control select2-province" {{ $required ? 'required' : '' }}>
                <option value="">-- Pilih Provinsi --</option>
            </select>
            @error($provinceName)
                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
            @enderror
        </div>

        <!-- Regency -->
        <div class="form-group mb-3">
            <label for="{{ $regencyName }}" class="form-label">Kabupaten/Kota {!! $required ? '<span class="text-danger">*</span>' : '' !!}</label>
            <select name="{{ $regencyName }}" id="{{ $regencyName }}" class="form-control select2-regency" {{ $required ? 'required' : '' }} disabled>
                <option value="">-- Pilih Kabupaten/Kota --</option>
            </select>
            @error($regencyName)
                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
            @enderror
        </div>

        <!-- District -->
        <div class="form-group mb-3">
            <label for="{{ $districtName }}" class="form-label">Kecamatan {!! $required ? '<span class="text-danger">*</span>' : '' !!}</label>
            <select name="{{ $districtName }}" id="{{ $districtName }}" class="form-control select2-district" {{ $required ? 'required' : '' }} disabled>
                <option value="">-- Pilih Kecamatan --</option>
            </select>
            @error($districtName)
                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
            @enderror
        </div>

        <!-- Village -->
        <div class="form-group mb-3">
            <label for="{{ $villageName }}" class="form-label">Kelurahan/Desa {!! $required ? '<span class="text-danger">*</span>' : '' !!}</label>
            <select name="{{ $villageName }}" id="{{ $villageName }}" class="form-control select2-village" {{ $required ? 'required' : '' }} disabled>
                <option value="">-- Pilih Kelurahan/Desa --</option>
            </select>
            @error($villageName)
                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
            @enderror
        </div>

        <!-- Postal Code -->
        <div class="form-group mb-3 md:col-span-2">
            <label for="{{ $postalCodeName }}" class="form-label">Kode Pos</label>
            <input type="text" name="{{ $postalCodeName }}" id="{{ $postalCodeName }}" class="form-control bg-gray-100" value="{{ old($postalCodeName, $postalCode) }}" readonly placeholder="Otomatis terisi">
            @error($postalCodeName)
                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selector = document.querySelector('.location-selector');
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
        // Trigger change event if manually setting value (for dependent fields in chain)
        // if (selectedId) {
        //      element.dispatchEvent(new Event('change'));
        // }
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
                    // Trigger village change to load postal code
                    // But we have the full object in data usually? No, just ID/Name/Postal?
                    // Let's fetch postal code separately or find it in data if we store it
                    // Optimization: We can store data mapped by ID to avoid request
                    const selectedVillage = data.find(v => v.id == selectedId);
                    if (selectedVillage && selectedVillage.postal_code) {
                        postalCodeInput.value = selectedVillage.postal_code;
                    } else if (selectedId) {
                         // Fallback fetch if not in initial list (unlikely) or just to be sure
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
@endpush
