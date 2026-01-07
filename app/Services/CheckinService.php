<?php

namespace App\Services;

use App\Models\CheckinLog;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventStatistic;
use Illuminate\Support\Facades\DB;

class CheckinService
{
    /**
     * Check in a participant by ticket number
     */
    public function checkinByTicket(
        string $ticketNumber, 
        int $userId = null,
        string $method = 'qr_scan',
        array $metadata = []
    ): EventRegistration {
        $registration = EventRegistration::byTicket($ticketNumber)
            ->with(['event', 'massa'])
            ->first();

        if (!$registration) {
            throw new \Exception('Tiket tidak ditemukan.');
        }

        return $this->processCheckin($registration, $userId, $method, $metadata);
    }

    /**
     * Check in by registration ID
     */
    public function checkinByRegistration(
        EventRegistration $registration,
        int $userId = null,
        string $method = 'manual',
        array $metadata = []
    ): EventRegistration {
        return $this->processCheckin($registration, $userId, $method, $metadata);
    }

    /**
     * Process the check-in
     */
    protected function processCheckin(
        EventRegistration $registration,
        ?int $userId,
        string $method,
        array $metadata
    ): EventRegistration {
        // Validate
        if ($registration->registration_status !== 'confirmed') {
            throw new \Exception('Registrasi belum dikonfirmasi.');
        }

        if ($registration->attendance_status === 'checked_in') {
            throw new \Exception('Peserta sudah check-in sebelumnya pada ' . 
                $registration->checked_in_at->format('d M Y H:i'));
        }

        $event = $registration->event;

        // Check if event is active
        if (!in_array($event->status, ['published', 'ongoing'])) {
            throw new \Exception('Event tidak aktif.');
        }

        return DB::transaction(function () use ($registration, $userId, $method, $metadata, $event) {
            // Update registration
            $registration->checkIn($userId, $method);

            // Create check-in log
            CheckinLog::create([
                'event_id' => $event->id,
                'event_registration_id' => $registration->id,
                'massa_id' => $registration->massa_id,
                'checked_by' => $userId,
                'action' => 'check_in',
                'method' => $method,
                'device_info' => $metadata['device_info'] ?? null,
                'ip_address' => $metadata['ip_address'] ?? null,
                'latitude' => $metadata['latitude'] ?? null,
                'longitude' => $metadata['longitude'] ?? null,
                'created_at' => now(),
            ]);

            // Update hourly statistics
            $stat = EventStatistic::getOrCreate($event->id, today(), now()->hour);
            $stat->incrementCheckins();

            return $registration->fresh(['event', 'massa']);
        });
    }

    /**
     * Manual override for check-in status
     */
    public function manualOverride(
        EventRegistration $registration,
        string $action,
        int $userId,
        ?string $reason = null
    ): void {
        DB::transaction(function () use ($registration, $action, $userId, $reason) {
            if ($action === 'check_in') {
                $registration->checkIn($userId, 'manual_override');
            } elseif ($action === 'undo_checkin') {
                $registration->update([
                    'attendance_status' => 'not_arrived',
                    'checked_in_at' => null,
                    'checked_in_by' => null,
                    'checkin_method' => null,
                ]);
            }

            CheckinLog::create([
                'event_id' => $registration->event_id,
                'event_registration_id' => $registration->id,
                'massa_id' => $registration->massa_id,
                'checked_by' => $userId,
                'action' => 'manual_override',
                'method' => 'admin_panel',
                'notes' => $reason,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Get real-time attendance stats for an event
     */
    public function getAttendanceStats(Event $event): array
    {
        $registrations = $event->registrations();

        return [
            'total_registered' => $registrations->count(),
            'total_confirmed' => $registrations->confirmed()->count(),
            'total_checked_in' => $registrations->checkedIn()->count(),
            'total_not_arrived' => $registrations->confirmed()->notArrived()->count(),
            'attendance_rate' => $this->calculateAttendanceRate($event),
        ];
    }

    /**
     * Get hourly check-in breakdown for real-time graph
     */
    public function getHourlyCheckins(Event $event, $date = null): array
    {
        $date = $date ?? today();

        $hourlyData = CheckinLog::forEvent($event->id)
            ->checkIns()
            ->whereDate('created_at', $date)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Fill in missing hours with 0
        $result = [];
        for ($i = 0; $i < 24; $i++) {
            $result[$i] = $hourlyData[$i] ?? 0;
        }

        return $result;
    }

    /**
     * Calculate attendance rate
     */
    protected function calculateAttendanceRate(Event $event): float
    {
        $confirmed = $event->registrations()->confirmed()->count();
        
        if ($confirmed === 0) {
            return 0;
        }

        $checkedIn = $event->registrations()->checkedIn()->count();
        
        return round(($checkedIn / $confirmed) * 100, 2);
    }

    /**
     * Validate QR code data
     */
    public function parseQrCode(string $qrData): ?array
    {
        try {
            $data = json_decode($qrData, true);
            
            if (!isset($data['type']) || $data['type'] !== 'event_registration') {
                return null;
            }

            if (!isset($data['ticket'])) {
                return null;
            }

            return $data;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get recent check-ins for live feed
     */
    public function getRecentCheckins(Event $event, int $limit = 10): array
    {
        return CheckinLog::forEvent($event->id)
            ->checkIns()
            ->with(['massa:id,nama_lengkap,foto'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn($log) => [
                'id' => $log->id,
                'nama' => $log->massa->nama_lengkap,
                'foto' => $log->massa->foto,
                'time' => $log->created_at->format('H:i:s'),
                'method' => $log->method,
            ])
            ->toArray();
    }
}
