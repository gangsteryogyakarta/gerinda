<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\LotteryPrize;
use App\Services\LotteryService;
use Illuminate\Http\Request;

class LotteryController extends Controller
{
    public function __construct(
        protected LotteryService $lotteryService
    ) {}

    /**
     * Show lottery index with active events
     */
    public function index()
    {
        $events = Event::where('enable_lottery', true)
            ->whereIn('status', ['published', 'ongoing', 'completed'])
            ->with(['lotteryPrizes'])
            ->orderByDesc('event_start')
            ->get();

        return view('lottery.index', compact('events'));
    }

    /**
     * Show lottery page for specific event
     */
    public function event(Event $event)
    {
        if (!$event->enable_lottery) {
            return redirect()->route('lottery.index')
                ->with('error', 'Undian tidak diaktifkan untuk event ini.');
        }

        $prizes = $event->lotteryPrizes()->ordered()->get();
        $stats = $this->lotteryService->getLotteryStats($event);
        $history = $this->lotteryService->getLotteryHistory($event, 20);
        $eligibleCount = $this->lotteryService->getEligibleParticipants($event)->count();

        return view('lottery.event', compact('event', 'prizes', 'stats', 'history', 'eligibleCount'));
    }

    /**
     * Get names for shuffle animation (AJAX)
     */
    public function shuffleNames(Event $event)
    {
        $names = $this->lotteryService->getShuffleNames($event, 30);

        return response()->json([
            'names' => $names,
        ]);
    }

    /**
     * Draw single winner (AJAX)
     */
    public function draw(Request $request, Event $event)
    {
        $validated = $request->validate([
            'prize_id' => 'required|exists:lottery_prizes,id',
        ]);

        $prize = LotteryPrize::findOrFail($validated['prize_id']);

        try {
            $winner = $this->lotteryService->drawWinner($event, $prize, auth()->id());

            return response()->json([
                'success' => true,
                'winner' => [
                    'nama' => $winner->massa->nama_lengkap,
                    'ticket' => $winner->ticket_number,
                    'no_hp' => $winner->massa->no_hp,
                ],
                'prize' => [
                    'name' => $prize->name,
                    'remaining' => $prize->fresh()->remaining_quantity,
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
     * Manage prizes for an event
     */
    public function prizes(Event $event)
    {
        $prizes = $event->lotteryPrizes()->ordered()->get();

        return view('lottery.prizes', compact('event', 'prizes'));
    }

    /**
     * Store new prize
     */
    public function storePrize(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
        ]);

        $validated['event_id'] = $event->id;
        $validated['remaining_quantity'] = $validated['quantity'];

        LotteryPrize::create($validated);

        return back()->with('success', 'Hadiah berhasil ditambahkan!');
    }

    /**
     * Delete prize
     */
    public function deletePrize(LotteryPrize $prize)
    {
        if ($prize->draws()->exists()) {
            return back()->with('error', 'Hadiah tidak dapat dihapus karena sudah diundi.');
        }

        $prize->delete();

        return back()->with('success', 'Hadiah berhasil dihapus!');
    }
}
