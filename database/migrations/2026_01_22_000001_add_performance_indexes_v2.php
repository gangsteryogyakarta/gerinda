<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Performance Indexes Migration
     * 
     * This migration adds critical indexes for optimizing queries
     * to support 100-1000 concurrent users.
     */
    public function up(): void
    {
        // =====================================================
        // MASSA TABLE INDEXES
        // =====================================================
        
        // Index for NIK lookup (primary search field)
        if (!$this->indexExists('massa', 'idx_massa_nik_lookup')) {
            Schema::table('massa', function (Blueprint $table) {
                $table->index('nik', 'idx_massa_nik_lookup');
            });
        }

        // Index for status filtering
        if (!$this->indexExists('massa', 'idx_massa_status')) {
            Schema::table('massa', function (Blueprint $table) {
                $table->index('status', 'idx_massa_status');
            });
        }

        // Composite index for status + province (common filter)
        if (!$this->indexExists('massa', 'idx_massa_status_province')) {
            Schema::table('massa', function (Blueprint $table) {
                $table->index(['status', 'province_id'], 'idx_massa_status_province');
            });
        }

        // Index for phone lookup
        if (!$this->indexExists('massa', 'idx_massa_phone')) {
            Schema::table('massa', function (Blueprint $table) {
                $table->index('no_hp', 'idx_massa_phone');
            });
        }

        // Index for geocoding queries
        if (!$this->indexExists('massa', 'idx_massa_geo')) {
            Schema::table('massa', function (Blueprint $table) {
                $table->index(['latitude', 'longitude'], 'idx_massa_geo');
            });
        }

        // =====================================================
        // EVENTS TABLE INDEXES
        // =====================================================

        // Composite index for status + event_start (listing & filtering)
        if (!$this->indexExists('events', 'idx_events_status_start')) {
            Schema::table('events', function (Blueprint $table) {
                $table->index(['status', 'event_start'], 'idx_events_status_start');
            });
        }

        // Index for slug lookup (public URLs)
        if (!$this->indexExists('events', 'idx_events_slug')) {
            Schema::table('events', function (Blueprint $table) {
                $table->index('slug', 'idx_events_slug');
            });
        }

        // Index for code lookup
        if (!$this->indexExists('events', 'idx_events_code')) {
            Schema::table('events', function (Blueprint $table) {
                $table->index('code', 'idx_events_code');
            });
        }

        // =====================================================
        // EVENT_REGISTRATIONS TABLE INDEXES
        // =====================================================

        // Composite index for event + registration status
        if (!$this->indexExists('event_registrations', 'idx_reg_event_regstatus')) {
            Schema::table('event_registrations', function (Blueprint $table) {
                $table->index(['event_id', 'registration_status'], 'idx_reg_event_regstatus');
            });
        }

        // Composite index for event + attendance status (check-in queries)
        if (!$this->indexExists('event_registrations', 'idx_reg_event_attendance')) {
            Schema::table('event_registrations', function (Blueprint $table) {
                $table->index(['event_id', 'attendance_status'], 'idx_reg_event_attendance');
            });
        }

        // Index for ticket_number lookup (check-in scan)
        if (!$this->indexExists('event_registrations', 'idx_reg_ticket_lookup')) {
            Schema::table('event_registrations', function (Blueprint $table) {
                $table->index('ticket_number', 'idx_reg_ticket_lookup');
            });
        }

        // Composite index for lottery queries
        if (!$this->indexExists('event_registrations', 'idx_reg_lottery')) {
            Schema::table('event_registrations', function (Blueprint $table) {
                $table->index(['event_id', 'eligible_for_lottery', 'won_lottery', 'attendance_status'], 'idx_reg_lottery');
            });
        }

        // Index for massa_id (reverse lookup)
        if (!$this->indexExists('event_registrations', 'idx_reg_massa')) {
            Schema::table('event_registrations', function (Blueprint $table) {
                $table->index('massa_id', 'idx_reg_massa');
            });
        }

        // =====================================================
        // CHECKIN_LOGS TABLE INDEXES
        // =====================================================

        // Composite index for event + created_at (recent check-ins)
        if (!$this->indexExists('checkin_logs', 'idx_checkin_event_time')) {
            Schema::table('checkin_logs', function (Blueprint $table) {
                $table->index(['event_id', 'created_at'], 'idx_checkin_event_time');
            });
        }

        // Composite index for action filtering
        if (!$this->indexExists('checkin_logs', 'idx_checkin_action')) {
            Schema::table('checkin_logs', function (Blueprint $table) {
                $table->index(['event_id', 'action'], 'idx_checkin_action');
            });
        }

        // =====================================================
        // NOTIFICATION_LOGS TABLE INDEXES
        // =====================================================

        // Index for status (retry failed)
        if (!$this->indexExists('notification_logs', 'idx_notif_status')) {
            Schema::table('notification_logs', function (Blueprint $table) {
                $table->index('status', 'idx_notif_status');
            });
        }

        // Composite index for status + created_at
        if (!$this->indexExists('notification_logs', 'idx_notif_status_time')) {
            Schema::table('notification_logs', function (Blueprint $table) {
                $table->index(['status', 'created_at'], 'idx_notif_status_time');
            });
        }

        // =====================================================
        // LOTTERY TABLES INDEXES
        // =====================================================

        // lottery_prizes - event + is_active
        if (!$this->indexExists('lottery_prizes', 'idx_prize_event_active')) {
            Schema::table('lottery_prizes', function (Blueprint $table) {
                $table->index(['event_id', 'is_active'], 'idx_prize_event_active');
            });
        }

        // lottery_draws - event + drawn_at
        if (!$this->indexExists('lottery_draws', 'idx_draw_event_time')) {
            Schema::table('lottery_draws', function (Blueprint $table) {
                $table->index(['event_id', 'drawn_at'], 'idx_draw_event_time');
            });
        }
    }

    public function down(): void
    {
        // Drop all custom indexes
        $indexes = [
            'massa' => ['idx_massa_nik_lookup', 'idx_massa_status', 'idx_massa_status_province', 'idx_massa_phone', 'idx_massa_geo'],
            'events' => ['idx_events_status_start', 'idx_events_slug', 'idx_events_code'],
            'event_registrations' => ['idx_reg_event_regstatus', 'idx_reg_event_attendance', 'idx_reg_ticket_lookup', 'idx_reg_lottery', 'idx_reg_massa'],
            'checkin_logs' => ['idx_checkin_event_time', 'idx_checkin_action'],
            'notification_logs' => ['idx_notif_status', 'idx_notif_status_time'],
            'lottery_prizes' => ['idx_prize_event_active'],
            'lottery_draws' => ['idx_draw_event_time'],
        ];

        foreach ($indexes as $table => $tableIndexes) {
            foreach ($tableIndexes as $indexName) {
                if ($this->indexExists($table, $indexName)) {
                    Schema::table($table, function (Blueprint $table) use ($indexName) {
                        $table->dropIndex($indexName);
                    });
                }
            }
        }
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return !empty($indexes);
    }
};
