<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateProposalPdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proposal:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the project proposal PDF';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating Proposal PDF...');

        $pdf = Pdf::loadView('proposal_pdf');
        
        $path = public_path('proposal_gerindra.pdf');
        $pdf->save($path);

        $this->info("PDF generated successfully at: {$path}");
        
        // Also copy to root for visibility
        copy($path, base_path('proposal_gerindra.pdf'));
        $this->info("PDF copied to root: " . base_path('proposal_gerindra.pdf'));
    }
}
