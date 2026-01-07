<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\LotteryPrize;
use App\Models\LotteryDraw;
use App\Services\LotteryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LotteryController extends Controller
{
    public function __construct(
        protected LotteryService $lotteryService
    ) {}

    /**
     * Get lottery info for an event
     */
    public function index(Event $event): JsonResponse
    {
        $stats = $this->lotteryService->getLotteryStats($event);
        $history = $this->lotteryService->getLotteryHistory($event);

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'history' => $history,
            ],
        ]);
    }

    /**
     * Get eligible participants count
     */
    public function eligibleCount(Event $event): JsonResponse
    {
        $eligible = $this->lotteryService->getEligibleParticipants($event);

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $eligible->count(),
                'participants' => $eligible->map(fn($r) => [
                    'id' => $r->id,
                    'nama' => $r->massa->nama_lengkap,
                    'ticket' => $r->ticket_number,
                ])->take(100), // Limit for performance
            ],
        ]);
    }

    /**
     * Get names for shuffle animation
     */
    public function shuffleNames(Event $event): JsonResponse
    {
        $names = $this->lotteryService->getShuffleNames($event, 30);

        return response()->json([
            'success' => true,
            'data' => $names,
        ]);
    }

    /**
     * Draw a single winner
     */
    public function draw(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'prize_id' => 'required|exists:lottery_prizes,id',
        ]);

        $prize = LotteryPrize::findOrFail($validated['prize_id']);

        try {
            $winner = $this->lotteryService->drawWinner($event, $prize, auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Undian berhasil!',
                'data' => [
                    'winner' => [
                        'id' => $winner->id,
                        'nama' => $winner->massa->nama_lengkap,
                        'ticket' => $winner->ticket_number,
                    ],
                    'prize' => $prize,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Draw multiple winners
     */
    public function drawMultiple(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'prize_id' => 'required|exists:lottery_prizes,id',
            'count' => 'required|integer|min:1|max:50',
        ]);

        $prize = LotteryPrize::findOrFail($validated['prize_id']);

        $winners = $this->lotteryService->drawMultipleWinners(
            $event, 
            $prize, 
            $validated['count'],
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Undian selesai. ' . count($winners) . ' pemenang terpilih.',
            'data' => [
                'winners' => collect($winners)->map(fn($w) => [
                    'id' => $w->id,
                    'nama' => $w->massa->nama_lengkap,
                    'ticket' => $w->ticket_number,
                ]),
                'prize' => $prize->fresh(),
            ],
        ]);
    }

    /**
     * Claim a prize
     */
    public function claim(LotteryDraw $draw): JsonResponse
    {
        try {
            $this->lotteryService->claimPrize($draw);

            return response()->json([
                'success' => true,
                'message' => 'Hadiah berhasil diklaim.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Undo a draw (admin only)
     */
    public function undoDraw(LotteryDraw $draw): JsonResponse
    {
        try {
            $this->lotteryService->undoDraw($draw);

            return response()->json([
                'success' => true,
                'message' => 'Undian berhasil dibatalkan.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Manage prizes - List
     */
    public function prizes(Event $event): JsonResponse
    {
        $prizes = $event->lotteryPrizes()->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $prizes,
        ]);
    }

    /**
     * Manage prizes - Create
     */
    public function createPrize(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['event_id'] = $event->id;
        $validated['remaining_quantity'] = $validated['quantity'];

        $prize = LotteryPrize::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Hadiah berhasil ditambahkan.',
            'data' => $prize,
        ], 201);
    }

    /**
     * Manage prizes - Update
     */
    public function updatePrize(Request $request, LotteryPrize $prize): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'quantity' => 'sometimes|integer|min:1',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        // Adjust remaining quantity if total quantity changed
        if (isset($validated['quantity'])) {
            $diff = $validated['quantity'] - $prize->quantity;
            $validated['remaining_quantity'] = max(0, $prize->remaining_quantity + $diff);
        }

        $prize->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Hadiah berhasil diupdate.',
            'data' => $prize,
        ]);
    }

    /**
     * Manage prizes - Delete
     */
    public function deletePrize(LotteryPrize $prize): JsonResponse
    {
        // Check if prize has been drawn
        if ($prize->draws()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Hadiah tidak dapat dihapus karena sudah diundi.',
            ], 422);
        }

        $prize->delete();

        return response()->json([
            'success' => true,
            'message' => 'Hadiah berhasil dihapus.',
        ]);
    }
}
