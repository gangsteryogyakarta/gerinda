// WhatsApp Page Logic v3.0

const textTemplates = {
    'event-reminder': `Halo Kader Gerindra! 🇮🇩\n\nJangan lupa hadir di acara:\n📅 {nama_event}\n📍 {lokasi}\n\nPastikan membawa KTP dan tiket.\n\nSalam Perjuangan! ✊`,
    'ticket-confirm': `Selamat! 🎉\nRegistrasi Anda terkonfirmasi:\n📌 Event: {nama_event}\n🎫 Tiket: {no_tiket}\n\nTunjukkan pesan ini saat registrasi.\nDPD Gerindra DIY`,
    'thank-you': `Terima kasih atas kehadiran Anda di {nama_event}! 🙏\nKawal terus perjuangan Partai Gerindra.\n\nSalam Indonesia Raya!`,
    'general-blast': `Salam Perjuangan! 🇮🇩\n\nKepada Yth. Kader Gerindra,\n\n{isi_pesan}\n\nTerima kasih.\nDPD Gerindra DIY`
};

let qrInterval;
let statusInterval;
let countdownInterval;
let lastQrCode = '';
let isStartingSession = false; // Flag to keep loading visible during session start
let loadingDotsInterval = null;

document.addEventListener('DOMContentLoaded', () => {
    console.log("DEBUG: External JS Loaded - v3.0");
    setupTabs();
    setupCharCounters();
    setupAdvancedFilter();
    loadResources();
    
    // Initial check
    checkHealth();
    // Poll every 5s
    statusInterval = setInterval(checkHealth, 5000);
    
    setupForms();
    
    // Template Builder
    setupTemplateBuilder();
});

function setupTabs() {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active classes
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            // Add active
            btn.classList.add('active');
            document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
        });
    });
}

function setupCharCounters() {
    ['single', 'bulk', 'event', 'template'].forEach(id => {
        const el = document.getElementById(id + (id === 'template' ? '-content' : '-message'));
        const cnt = document.getElementById(id + '-char-count');
        if(el && cnt) {
            el.addEventListener('input', () => cnt.innerText = el.value.length);
        }
    });
}

function setupAdvancedFilter() {
    const filter = document.getElementById('bulk-filter');
    if(filter) {
        filter.addEventListener('change', function() {
            const isProvince = this.value === 'province';
            document.getElementById('province-select-container').style.display = isProvince ? 'block' : 'none';
        });
    }
}

function toggleAdvanced() {
    const body = document.getElementById('advanced-body');
    const icon = document.getElementById('advanced-icon');
    if (body.style.display === 'none') {
        body.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        body.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function loadResources() {
    // Load Provinces
    fetch('/api/v1/locations/provinces')
        .then(r => r.json())
        .then(data => {
            const select = document.getElementById('bulk-province');
            if(select) {
                const items = Array.isArray(data) ? data : (data.data || []);
                if(Array.isArray(items)) {
                    items.forEach(p => select.innerHTML += `<option value="${p.id}">${p.name}</option>`);
                }
            }
        });

    // Load Events
    fetch('/api/v1/events')
        .then(r => r.json())
        .then(data => {
            const select = document.getElementById('event-select');
            if(select) {
                const items = Array.isArray(data) ? data : (data.data || []);
                if(Array.isArray(items)) {
                    items.forEach(e => select.innerHTML += `<option value="${e.id}">${e.name}</option>`);
                }
            }
        });
        
    // Load Regencies for DIY (ID 34)
    fetch('/api/v1/locations/provinces')
        .then(r => r.json())
        .then(data => {
            const provinces = Array.isArray(data) ? data : (data.data || []);
            const diy = provinces.find(p => p.code == '34');
            if (diy) {
                return fetch(`/api/v1/locations/regencies/${diy.id}`); 
            }
            return fetch('/api/v1/locations/regencies/34'); 
        })
        .then(r => r.json())
        .then(data => {
            const select = document.getElementById('bulk-regency');
            if(select) {
                const items = Array.isArray(data) ? data : (data.data || []);
                if(Array.isArray(items)) {
                    items.forEach(r => select.innerHTML += `<option value="${r.id}">${r.name}</option>`);
                }
            }
        })
        .catch(err => console.error('Error loading regencies:', err));
}

function useTemplate(key) {
    const tmpl = textTemplates[key];
    const activeTab = document.querySelector('.tab-content.active');
    if(activeTab) {
        const textarea = activeTab.querySelector('textarea');
        if(textarea && tmpl) {
            textarea.value = tmpl;
            textarea.dispatchEvent(new Event('input')); // Update counter
        }
    }
}

// --- WhatsApp Logic ---
function checkHealth() {
    fetch('/whatsapp/health')
        .then(r => r.json())
        .then(renderStatus)
        .catch(() => renderStatus({ connected: false, status: 'offline' }));
}

function renderStatus(data) {
    console.log("DEBUG: renderStatus called", data);
    const loading = document.getElementById('session-loading');
    const start = document.getElementById('start-container');
    const qr = document.getElementById('qr-container');
    const connected = document.getElementById('connected-container');
    
    const pillText = document.getElementById('connection-text');
    const pill = document.getElementById('connection-status-pill');

    if(!loading || !start || !qr || !connected) return;

    // Hide all EXCEPT when starting session (keep loading visible)
    if (!isStartingSession) {
        loading.style.display = 'none';
    }
    start.style.display = 'none';
    qr.style.display = 'none';
    connected.style.display = 'none';

    const status = data.status || {};
    const state = status.status || 'disconnected';
    const isConnected = status.connected || false;

    if (isConnected) {
        connected.style.display = 'flex';
        
        // Strict ES5 Safe Check
        let userId = 'Unknown';
        if (status.user && status.user.id) {
             const rawId = String(status.user.id);
             if (rawId.indexOf(':') > -1) {
                 userId = rawId.split(':')[0];
             } else {
                 userId = rawId;
             }
        }

        const detailEl = document.getElementById('connected-detail');
        if(detailEl) detailEl.innerText = 'Terhubung: ' + userId;
        
        const phoneStatEl = document.getElementById('connected-phone-stat');
        if(phoneStatEl) phoneStatEl.innerText = (userId === 'Unknown') ? 'Online' : userId;
        
        if(pill) pill.className = 'connection-pill connected';
        if(pillText) pillText.innerText = 'Terhubung';
        
        // Stop QR rotation if running
        if (qrInterval) {
            clearInterval(qrInterval);
            qrInterval = null;
        }
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
    } else if (state === 'qr' || state === 'scan_qr_code') {
        // QR is ready - stop "starting session" mode and hide loading
        if (isStartingSession) {
            isStartingSession = false;
            stopLoadingDots();
            loading.style.display = 'none';
        }
        
        qr.style.display = 'flex';
        if(pill) pill.className = 'connection-pill disconnected';
        if(pillText) pillText.innerText = 'Scan QR';
        
        // Start QR rotation if not running - 60 second intervals for easier scanning
        if (!qrInterval) {
            refreshQR(); // Immediate fetch
            qrInterval = setInterval(refreshQR, 60000); // Poll every 60s (was 20s)
            startCountdown(60);
        }
    } else {
        // Only show start container if NOT currently starting a session
        if (!isStartingSession) {
            start.style.display = 'flex';
            loading.style.display = 'none';
        } else {
            // Keep loading visible during session initialization
            loading.style.display = 'flex';
        }
        
        if(pill) pill.className = 'connection-pill disconnected';
        if(pillText) pillText.innerText = isStartingSession ? 'Memuat...' : 'Terputus';
        
        // Stop QR rotation
        if (qrInterval) {
            clearInterval(qrInterval);
            qrInterval = null;
        }
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
    }
}

function startSession() {
    const btn = document.getElementById('btn-start-session');
    if(!btn) return;
    
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="btn-spinner"></span> Memulai Sesi...';
    
    // Set flag to keep loading visible until QR appears
    isStartingSession = true;
    
    // Show loading state in the connection card
    const startContainer = document.getElementById('start-container');
    const loadingContainer = document.getElementById('session-loading');
    
    if(startContainer) startContainer.style.display = 'none';
    if(loadingContainer) {
        loadingContainer.style.display = 'flex';
        const loadingText = loadingContainer.querySelector('p');
        if(loadingText) {
            loadingText.innerText = 'Menghasilkan QR Code';
            startLoadingDots(loadingText, 'Menghasilkan QR Code');
        }
    }

    fetch('/whatsapp/session/start', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
    })
    .then(r => r.json())
    .then((data) => {
        console.log('Session start response:', data);
        // Poll health frequently until QR appears (max 60 seconds)
        let pollCount = 0;
        const maxPolls = 60; // 60 polls x 1 second = 60 seconds max wait
        
        const fastPoll = setInterval(() => {
            checkHealth();
            pollCount++;
            
            // Stop polling if QR appeared (isStartingSession will be false) or timeout
            if (!isStartingSession || pollCount >= maxPolls) {
                clearInterval(fastPoll);
                
                // If still waiting after 60s, show timeout message
                if (isStartingSession && pollCount >= maxPolls) {
                    isStartingSession = false;
                    stopLoadingDots();
                    if(startContainer) startContainer.style.display = 'flex';
                    if(loadingContainer) loadingContainer.style.display = 'none';
                    alert('QR Code tidak dapat dibuat. Silakan coba lagi.');
                }
            }
        }, 1000);
    })
    .catch(err => {
        isStartingSession = false;
        stopLoadingDots();
        alert('Gagal memulai sesi: ' + err);
        if(startContainer) startContainer.style.display = 'flex';
        if(loadingContainer) loadingContainer.style.display = 'none';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = original;
    });
}

// Animated loading dots
function startLoadingDots(element, baseText) {
    let dots = 0;
    stopLoadingDots(); // Clear any existing
    loadingDotsInterval = setInterval(() => {
        dots = (dots + 1) % 4;
        element.innerText = baseText + '.'.repeat(dots);
    }, 500);
}

function stopLoadingDots() {
    if (loadingDotsInterval) {
        clearInterval(loadingDotsInterval);
        loadingDotsInterval = null;
    }
}

function refreshQR() {
    fetch('/whatsapp/qr')
        .then(r => r.json())
        .then(data => {
            if(data.success && data.qr) {
                 // Only update if changed
                 if (data.qr !== lastQrCode) {
                     const qrImg = document.getElementById('qr-image');
                     if(qrImg) qrImg.src = data.qr;
                     lastQrCode = data.qr;
                     
                     // Reset countdown
                     startCountdown(20);
                 }
            }
        });
}

function startCountdown(seconds) {
    if (countdownInterval) clearInterval(countdownInterval);
    
    let timeLeft = seconds;
    const timerEl = document.getElementById('qr-timer');
    if(timerEl) timerEl.innerText = timeLeft;
    
    countdownInterval = setInterval(() => {
        timeLeft--;
        if(timerEl) timerEl.innerText = timeLeft;
        if (timeLeft <= 0) {
            clearInterval(countdownInterval);
        }
    }, 1000);
}

function logout() {
    if(!confirm('Putuskan koneksi WhatsApp?')) return;
    
    const container = document.getElementById('connected-container');
    if(!container) return;
    
    const btn = container.querySelector('.btn-danger');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader-2" class="animate-spin"></i> Memutuskan...';
    
    fetch('/whatsapp/logout', {
         method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            checkHealth();
        } else {
            alert('Gagal memutuskan koneksi: ' + (data.error || 'Unknown error'));
            btn.disabled = false;
            btn.innerHTML = originalText;
            if(window.lucide) window.lucide.createIcons();
        }
    })
    .catch(err => {
        alert('Error: ' + err);
        btn.disabled = false;
        btn.innerHTML = originalText;
        if(window.lucide) window.lucide.createIcons();
    });
}

function setupForms() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const handle = (formId, url, btnId, payloadFn) => {
        const form = document.getElementById(formId);
        if(!form) return;
        
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            if(!confirm('Kirim pesan ini?')) return;
            
            const btn = document.getElementById(btnId);
            const original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Mengirim...';
            
            const payload = payloadFn();
            const imgInput = form.querySelector('input[placeholder*="image"]');
            if(imgInput && imgInput.value) {
                 payload.image_url = imgInput.value;
            }

            let targetUrl = url;
            if(formId === 'event-message-form') {
                 // dynamic url
                 const eventId = payload.event_id || document.getElementById('event-select').value;
                 targetUrl = '/whatsapp/event/' + eventId + '/notify';
            }

            fetch(targetUrl, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(d => {
                if(d.success) {
                    alert('Berhasil: ' + (d.message || 'Pesan dikirim/antre.'));
                    form.reset();
                } else {
                    alert('Gagal: ' + (d.error || d.message));
                }
            })
            .catch(e => alert('Error: ' + e))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = original;
            });
        });
    };

    if(document.getElementById('single-message-form')) {
        handle('single-message-form', '/whatsapp/send', 'btn-send-single', () => ({
            phone: document.getElementById('single-phone').value,
            message: document.getElementById('single-message').value
        }));
    }

    if(document.getElementById('bulk-message-form')) {
        handle('bulk-message-form', '/whatsapp/blast', 'btn-send-bulk', () => ({
            filter: document.getElementById('bulk-filter').value,
            province_id: document.getElementById('bulk-province').value,
             regency_id: document.getElementById('bulk-regency').value,
             gender: document.getElementById('bulk-gender').value,
             age_min: document.getElementById('bulk-age-min').value,
            age_max: document.getElementById('bulk-age-max').value,
            limit: document.getElementById('bulk-limit').value,
            message: document.getElementById('bulk-message').value
        }));
    }
    
    if(document.getElementById('event-message-form')) {
        handle('event-message-form', '', 'btn-send-event', () => ({
             status: document.getElementById('event-status').value,
             message: document.getElementById('event-message').value
        }));
    }
}

// ============ TEMPLATE BUILDER ============

let templateVariables = {};
let currentTemplateId = null;
let previewDebounce = null;

function setupTemplateBuilder() {
    loadTemplateVariables();
    
    // Auto-refresh preview on typing
    const contentEl = document.getElementById('template-content');
    if (contentEl) {
        contentEl.addEventListener('input', () => {
            clearTimeout(previewDebounce);
            previewDebounce = setTimeout(refreshPreview, 500);
        });
    }
}

function loadTemplateVariables() {
    fetch('/whatsapp/templates/variables')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                templateVariables = data.data;
                renderVariablesPanel(data.data);
            }
        })
        .catch(err => console.error('Failed to load variables:', err));
}

function renderVariablesPanel(variables) {
    const container = document.getElementById('variables-list');
    if (!container) return;
    
    let html = '';
    for (const group in variables) {
        html += '<div class="var-group-label">' + group + '</div>';
        variables[group].forEach(v => {
            html += '<button type="button" class="var-chip" onclick="insertVariable(\'' + v.key + '\')" title="' + v.example + '">{' + v.key + '}</button>';
        });
    }
    
    // Add condition shortcut
    html += '<div class="var-group-label">Kondisi</div>';
    html += '<button type="button" class="var-chip" onclick="insertCondition()" title="Tambah kondisi if-else">{if:...}</button>';
    
    container.innerHTML = html;
    
    // Refresh lucide icons
    if (window.lucide) lucide.createIcons();
}

function insertVariable(key) {
    const textarea = document.getElementById('template-content');
    if (!textarea) return;
    
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    const insert = '{' + key + '}';
    
    textarea.value = text.substring(0, start) + insert + text.substring(end);
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = start + insert.length;
    
    // Trigger char counter update
    textarea.dispatchEvent(new Event('input'));
}

function insertCondition() {
    const textarea = document.getElementById('template-content');
    if (!textarea) return;
    
    const start = textarea.selectionStart;
    const text = textarea.value;
    const insert = '{if:kategori=Pengurus}\nKonten khusus pengurus\n{else}\nKonten umum\n{endif}';
    
    textarea.value = text.substring(0, start) + insert + text.substring(start);
    textarea.focus();
    textarea.dispatchEvent(new Event('input'));
}

function refreshPreview() {
    const content = document.getElementById('template-content');
    const previewEl = document.getElementById('template-preview');
    if (!content || !previewEl) return;
    
    const text = content.value.trim();
    if (!text) {
        previewEl.innerHTML = '<div class="preview-placeholder"><i data-lucide="message-circle"></i><p>Preview akan muncul di sini</p></div>';
        if (window.lucide) lucide.createIcons();
        return;
    }
    
    fetch('/whatsapp/templates/preview', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ content: text })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            previewEl.innerText = data.preview;
        } else {
            previewEl.innerHTML = '<div style="color: #f87171;">Error: ' + (data.message || 'Preview failed') + '</div>';
        }
    })
    .catch(err => {
        previewEl.innerHTML = '<div style="color: #f87171;">Error loading preview</div>';
    });
}

function saveTemplate() {
    const name = document.getElementById('template-name');
    const category = document.getElementById('template-category');
    const content = document.getElementById('template-content');
    
    if (!name || !content) return;
    
    if (!name.value.trim()) {
        alert('Nama template harus diisi');
        name.focus();
        return;
    }
    
    if (!content.value.trim()) {
        alert('Konten template harus diisi');
        content.focus();
        return;
    }
    
    const payload = {
        name: name.value.trim(),
        category: category ? category.value : 'umum',
        content: content.value
    };
    
    const url = currentTemplateId 
        ? '/whatsapp/templates/' + currentTemplateId 
        : '/whatsapp/templates';
    const method = currentTemplateId ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Template berhasil disimpan');
            currentTemplateId = data.data.id;
            loadExistingTemplates();
        } else {
            alert('Gagal: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(err => alert('Error: ' + err));
}

function loadExistingTemplates() {
    const body = document.getElementById('templates-library-body');
    const grid = document.getElementById('templates-grid');
    if (!grid) return;
    
    // Show library if hidden
    if (body) body.style.display = 'block';
    
    grid.innerHTML = '<p style="color: var(--text-muted);">Memuat template...</p>';
    
    fetch('/whatsapp/templates')
        .then(r => r.json())
        .then(data => {
            if (data.success && Array.isArray(data.data)) {
                if (data.data.length === 0) {
                    grid.innerHTML = '<p style="color: var(--text-muted);">Belum ada template tersimpan</p>';
                    return;
                }
                
                let html = '';
                data.data.forEach(t => {
                    const preview = t.content.substring(0, 100) + (t.content.length > 100 ? '...' : '');
                    const isSystem = t.is_system ? '<span class="template-card-category">Sistem</span>' : '';
                    
                    html += '<div class="template-card" onclick="useTemplate(' + t.id + ')">' +
                        '<div class="template-card-header">' +
                        '<span class="template-card-title">' + t.name + '</span>' +
                        isSystem +
                        '</div>' +
                        '<div class="template-card-preview">' + preview.replace(/</g, '&lt;') + '</div>' +
                        '</div>';
                });
                
                grid.innerHTML = html;
            } else {
                grid.innerHTML = '<p style="color: #f87171;">Gagal memuat template</p>';
            }
        })
        .catch(err => {
            grid.innerHTML = '<p style="color: #f87171;">Error: ' + err + '</p>';
        });
}

function useTemplate(id) {
    fetch('/whatsapp/templates/' + id)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const t = data.data;
                document.getElementById('template-name').value = t.name;
                document.getElementById('template-category').value = t.category;
                document.getElementById('template-content').value = t.content;
                
                // Only set currentTemplateId if NOT a system template
                currentTemplateId = t.is_system ? null : t.id;
                
                // Trigger char counter
                document.getElementById('template-content').dispatchEvent(new Event('input'));
                
                // Refresh preview
                refreshPreview();
            }
        })
        .catch(err => alert('Error loading template: ' + err));
}

function toggleTemplatesLibrary() {
    const body = document.getElementById('templates-library-body');
    const icon = document.getElementById('library-icon');
    if (!body) return;
    
    if (body.style.display === 'none') {
        body.style.display = 'block';
        if (icon) icon.setAttribute('data-lucide', 'chevron-up');
        loadExistingTemplates();
    } else {
        body.style.display = 'none';
        if (icon) icon.setAttribute('data-lucide', 'chevron-down');
    }
    
    if (window.lucide) lucide.createIcons();
}

