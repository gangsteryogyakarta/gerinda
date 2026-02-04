<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\PrintJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateTicketPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Event $event,
        protected PrintJob $printJob,
        protected int $offset,
        protected int $limit
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->printJob->update(['status' => 'processing']);

            // Increase memory for this job
            ini_set('memory_limit', '512M');
            set_time_limit(0);

            // Fetch registrations
            $registrations = $this->event->registrations()
                ->whereHas('massa')
                ->whereIn('registration_status', ['confirmed', 'pending', 'waitlist'])
                ->whereNotNull('ticket_number')
                ->orderBy('id')
                ->skip($this->offset)
                ->take($this->limit)
                ->with('massa')
                ->get();

            if ($registrations->isEmpty()) {
                $this->printJob->update([
                    'status' => 'failed', 
                    'error_message' => 'No registrations found for this batch.'
                ]);
                return;
            }

            // Prepare Assets
            $qrCodes = [];
            $logoPath = public_path('img/logo-gerindra.png');
            $logoBase64 = '';
            
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
            }

            foreach ($registrations as $reg) {
                if ($reg->qr_code_path && Storage::disk('public')->exists($reg->qr_code_path)) {
                    $qrContent = Storage::disk('public')->get($reg->qr_code_path);
                    $qrCodes[$reg->id] = 'data:image/svg+xml;base64,' . base64_encode($qrContent);
                }
            }

            // Generate PDF
            $pdf = Pdf::loadView('pdf.tickets_batch', [
                'event' => $this->event,
                'registrations' => $registrations,
                'qrCodes' => $qrCodes,
                'logoBase64' => $logoBase64,
            ]);
            
            $pdf->setPaper('a4', 'portrait');
            $content = $pdf->output();

            // Save to storage
            $filename = "tickets-{$this->event->code}-batch-{$this->printJob->batch_no}.pdf";
            $path = "print_jobs/{$this->event->id}/{$filename}";
            
            Storage::disk('public')->put($path, $content);

            // Update Job
            $this->printJob->update([
                'status' => 'completed',
                'file_path' => $path
            ]);

        } catch (\Exception $e) {
            $this->printJob->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
