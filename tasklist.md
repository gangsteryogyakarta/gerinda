# Sistem Management Event Gerindra - Tasklist

## 📋 Roadmap Development

### ✅ Phase 1: Foundation (COMPLETED)

-   [x] Setup Project Laravel 11
-   [x] Setup Database MySQL 8
-   [x] Install packages (Sanctum, Spatie Permission, DomPDF, QrCode, Predis)
-   [x] Create database migrations (provinces, regencies, districts, villages, massa, events, registrations, lottery, checkin_logs, notifications, statistics)
-   [x] Create Eloquent Models with relationships
-   [x] Setup Role & Permission system
-   [x] Create Seeders (Wilayah, EventCategory, RolePermission)

### ✅ Phase 2: Core Services (COMPLETED)

-   [x] MassaService (NIK deduplication, geocoding)
-   [x] RegistrationService (registration, ticket generation, QR code)
-   [x] CheckinService (QR scan, manual input, real-time stats)
-   [x] LotteryService (random draw, prize management)
-   [x] NotificationService (WhatsApp gateway integration)

### ✅ Phase 3: API Development (COMPLETED)

-   [x] EventController API (CRUD, status management)
-   [x] RegistrationController API (registration, ticket download)
-   [x] CheckinController API (QR scan, attendance stats)
-   [x] LotteryController API (draw, prize management)
-   [x] MassaController API (CRUD, geocoding, loyalty)
-   [x] API Routes with Sanctum authentication

### ✅ Phase 4: Basic Frontend (COMPLETED)

-   [x] Dashboard Layout with modern dark theme
-   [x] Dashboard View with stats cards and charts
-   [x] Navigation sidebar with all menu items
-   [x] Web routes configuration

### ✅ Phase 5: Frontend Pages (COMPLETED)

-   [x] Event Management Pages
    -   [x] Event List with filters and pagination
    -   [x] Event Create/Edit Form with custom fields builder
    -   [x] Event Detail Page with statistics
    -   [x] Event Registrations List
-   [x] Check-in System
    -   [x] QR Scanner Page (camera-based with Html5QrCode)
    -   [x] Manual Check-in Form
    -   [x] Real-time Attendance Dashboard with live stats
-   [x] Lottery/Undian Pages
    -   [x] Prize Management (add/delete)
    -   [x] Draw Animation Page with shuffle effect
    -   [x] Winner History with confetti celebration
-   [x] Ticket Management
    -   [x] Batch Ticket Generation
    -   [x] Ticket Download (PDF)
-   [x] Massa Management Pages
    -   [x] Massa List with search and filters
    -   [x] Massa Create/Edit Form with address autocomplete
    -   [x] Massa Detail with event history and loyalty stats

### ✅ Phase 6: Advanced Features (COMPLETED)

-   [x] Dashboard WebGIS (Heatmap & Marker Cluster)
    -   [x] Integrate Leaflet.js with CartoDB tiles
    -   [x] Massa distribution heatmap (togglable)
    -   [x] Marker clustering with custom styling
    -   [x] Province filter on map
-   [x] Real-time Analytics Dashboard
    -   [x] Monthly registration trends chart
    -   [x] Weekly check-in bar chart
    -   [x] Event performance table with attendance rates
    -   [x] Province distribution visualization
-   [x] Export & Reporting
    -   [x] CSV Export for registrations
    -   [x] Conversion Rate Analytics
    -   [x] Average Attendance Rate calculation

### ✅ Phase 7: Integrations (COMPLETED)

-   [x] WhatsApp Gateway Integration
    -   [x] NotificationService with WA Gateway
    -   [x] Test WhatsApp connection from Settings
    -   [x] Configurable gateway URL and token
-   [x] Geocoding Integration
    -   [x] MassaService geocoding (Nominatim/Google)
    -   [x] Auto-geocode on massa creation
    -   [x] Configurable geocoding provider
-   [x] UI Refactor to Modern Light Theme
    -   [x] Gerindra Red branding throughout
    -   [x] Rounded sidebar with gradient
    -   [x] Premium card-based design
    -   [x] Responsive layouts
-   [x] Public Registration Page (/daftar)
    -   [x] Event listing for public
    -   [x] Registration form with NIK lookup
    -   [x] DI Yogyakarta as default location
    -   [x] Success page with confetti animation

---

## 🔐 Default Credentials

### Super Admin

-   Email: `admin@gerindra.or.id`
-   Password: `gerindra2024`

### Operator

-   Email: `operator@gerindra.or.id`
-   Password: `operator2024`

---

## 🚀 Quick Start

```bash
# Start Laravel development server
php artisan serve

# Start queue worker (for background jobs)
php artisan queue:work

# Run migrations
php artisan migrate:fresh --seed

# Clear cache
php artisan optimize:clear
```

---

## 📊 API Documentation

Base URL: `http://gerindra.test/api/v1`

### Authentication

All authenticated endpoints require Bearer token in header:

```
Authorization: Bearer {token}
```

### Main Endpoints

-   `GET /events` - List events
-   `POST /events` - Create event
-   `GET /events/{id}` - Event details
-   `POST /events/{id}/registrations` - Register massa to event
-   `POST /checkin/scan` - Check-in via QR
-   `POST /events/{id}/lottery/draw` - Draw lottery winner
-   `GET /massa` - List massa
-   `POST /massa/find-by-nik` - Find massa by NIK

---

## 🎨 Tech Stack

-   **Backend**: Laravel 11, PHP 8.2+
-   **Database**: MySQL 8
-   **Cache/Queue**: Redis
-   **Authentication**: Laravel Sanctum
-   **Authorization**: Spatie Laravel Permission
-   **PDF**: DomPDF
-   **QR Code**: Simple QrCode
-   **Frontend**: Blade Templates, Chart.js
-   **Maps**: Leaflet.js (planned)
