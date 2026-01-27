<?php

namespace App\Http\Controllers;

use App\Models\WhatsappCampaign;
use App\Models\WhatsappMessageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WhatsappAnalyticsController extends Controller
{
    /**
     * Show main analytics dashboard
     */
    public function dashboard()
    {
        // 1. Overall Stats
        $totalSent = WhatsappMessageLog::count();
        $totalDelivered = WhatsappMessageLog::whereNotNull('delivered_at')->count();
        $totalRead = WhatsappMessageLog::whereNotNull('read_at')->count();
        $totalFailed = WhatsappMessageLog::where('status', 'failed')->count();

        // Calculate Rates
        $deliveryRate = $totalSent > 0 ? round(($totalDelivered / $totalSent) * 100, 1) : 0;
        $readRate = $totalDelivered > 0 ? round(($totalRead / $totalDelivered) * 100, 1) : 0;

        // 2. Recent Campaigns with stats
        $recentCampaigns = WhatsappCampaign::withCount(['messages as log_count'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($campaign) {
                // Calculate quick stats for this campaign
                $total = $campaign->log_count;
                $read = $campaign->messages()->whereNotNull('read_at')->count();
                $rate = $total > 0 ? round(($read / $total) * 100) : 0;
                $campaign->read_rate = $rate;
                return $campaign;
            });

        return view('whatsapp.analytics.dashboard', compact(
            'totalSent', 'totalDelivered', 'totalRead', 'totalFailed',
            'deliveryRate', 'readRate', 'recentCampaigns'
        ));
    }

    /**
     * Get Chart Data (AJAX) for last 30 days
     */
    public function getChartData()
    {
        $days = 30;
        $start = now()->subDays($days)->startOfDay();

        $data = WhatsappMessageLog::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN read_at IS NOT NULL THEN 1 ELSE 0 END) as read_count'),
            DB::raw('SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_count')
        )
        ->where('created_at', '>=', $start)
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        // Fill missing dates
        $chartData = [
            'labels' => [],
            'sent' => [],
            'read' => [],
            'failed' => []
        ];

        for ($i = 0; $i <= $days; $i++) {
            $date = $start->copy()->addDays($i)->format('Y-m-d');
            $record = $data->firstWhere('date', $date);

            $chartData['labels'][] = date('d M', strtotime($date));
            $chartData['sent'][] = $record ? $record->total : 0;
            $chartData['read'][] = $record ? $record->read_count : 0;
            $chartData['failed'][] = $record ? $record->failed_count : 0;
        }

        return response()->json($chartData);
    }

    /**
     * Show detailed report for a specific campaign
     */
    public function show(WhatsappCampaign $campaign)
    {
        // Load stats
        $stats = [
            'sent' => $campaign->messages()->count(),
            'delivered' => $campaign->messages()->whereNotNull('delivered_at')->count(),
            'read' => $campaign->messages()->whereNotNull('read_at')->count(),
            'failed' => $campaign->messages()->where('status', 'failed')->count(),
        ];

        // Detailed logs with pagination
        $logs = $campaign->messages()->latest()->paginate(20);

        return view('whatsapp.analytics.show', compact('campaign', 'stats', 'logs'));
    }
}
