<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\LotteryDraw;
use App\Models\LotteryPrize;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class LotteryService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Get eligible participants for lottery (checked-in & haven't won)
     */
    public function getEligibleParticipants(Event $event): Collection
    {
        return $event->registrations()
            ->eligibleForLottery()
            ->with('massa:id,nama_lengkap,no_hp')
            ->get();
    }

    /**
     * Draw a random winner for a specific prize
     */
    public function drawWinner(Event $event, LotteryPrize $prize, int $userId): ?EventRegistration
    {
        // Validate prize
        if ($prize->event_id !== $event->id) {
            throw new \Exception('Hadiah tidak terdaftar untuk event ini.');
        }

        if (!$prize->hasStock()) {
            throw new \Exception('Stok hadiah sudah habis.');
        }

        // Get eligible participants
        $eligible = $this->getEligibleParticipants($event);

        if ($eligible->isEmpty()) {
            throw new \Exception('Tidak ada peserta yang memenuhi syarat untuk undian.');
        }

        // Random selection
        $winner = $eligible->random();

        return DB::transaction(function () use ($event, $prize, $winner, $userId) {
            // Create lottery draw record
            LotteryDraw::create([
                'event_id' => $event->id,
                'lottery_prize_id' => $prize->id,
                'event_registration_id' => $winner->id,
                'massa_id' => $winner->massa_id,
                'drawn_by' => $userId,
                'drawn_at' => now(),
            ]);

            // Update registration
            $winner->markAsWinner($prize->name);

            // Decrement prize stock
            $prize->decrementStock();

            // Send notification
            if ($event->send_wa_notification && $winner->massa->no_hp) {
                $this->notificationService->sendLotteryWinNotification($winner, $prize);
            }

            return $winner->fresh(['massa', 'event']);
        });
    }

    /**
     * Draw multiple winners at once
     */
    public function drawMultipleWinners(Event $event, LotteryPrize $prize, int $count, int $userId): array
    {
        $winners = [];
        
        for ($i = 0; $i < $count; $i++) {
            if (!$prize->fresh()->hasStock()) {
                break;
            }

            try {
                $winner = $this->drawWinner($event, $prize, $userId);
                if ($winner) {
                    $winners[] = $winner;
                }
            } catch (\Exception $e) {
                break;
            }
        }

        return $winners;
    }

    /**
     * Get lottery history for an event
     */
    public function getLotteryHistory(Event $event): Collection
    {
        return LotteryDraw::where('event_id', $event->id)
            ->with(['prize', 'massa:id,nama_lengkap', 'drawnByUser:id,name'])
            ->orderByDesc('drawn_at')
            ->get();
    }

    /**
     * Get lottery statistics
     */
    public function getLotteryStats(Event $event): array
    {
        $prizes = $event->lotteryPrizes()->get();
        $draws = LotteryDraw::where('event_id', $event->id)->get();
        $eligible = $this->getEligibleParticipants($event)->count();

        return [
            'total_prizes' => $prizes->count(),
            'total_prize_quantity' => $prizes->sum('quantity'),
            'total_drawn' => $draws->count(),
            'total_claimed' => $draws->where('is_claimed', true)->count(),
            'remaining_prizes' => $prizes->sum('remaining_quantity'),
            'eligible_participants' => $eligible,
            'prizes' => $prizes->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'quantity' => $p->quantity,
                'remaining' => $p->remaining_quantity,
                'drawn' => $p->quantity - $p->remaining_quantity,
            ])->toArray(),
        ];
    }

    /**
     * Mark prize as claimed
     */
    public function claimPrize(LotteryDraw $draw): void
    {
        if ($draw->is_claimed) {
            throw new \Exception('Hadiah sudah diklaim.');
        }

        $draw->claim();
    }

    /**
     * Undo a lottery draw (admin only)
     */
    public function undoDraw(LotteryDraw $draw): void
    {
        if ($draw->is_claimed) {
            throw new \Exception('Tidak dapat membatalkan undian yang sudah diklaim.');
        }

        DB::transaction(function () use ($draw) {
            // Restore prize stock
            $draw->prize->increment('remaining_quantity');

            // Reset registration lottery status
            $draw->registration->update([
                'won_lottery' => false,
                'lottery_prize' => null,
                'lottery_won_at' => null,
            ]);

            // Delete draw record
            $draw->delete();
        });
    }

    /**
     * Shuffle animation helper - get random names for display
     */
    public function getShuffleNames(Event $event, int $count = 20): array
    {
        $eligible = $this->getEligibleParticipants($event);
        
        if ($eligible->isEmpty()) {
            return [];
        }

        $shuffled = $eligible->shuffle()->take($count);
        
        return $shuffled->map(fn($r) => [
            'id' => $r->id,
            'nama' => $r->massa->nama_lengkap,
        ])->toArray();
    }
}
