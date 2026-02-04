<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Massa;
use App\Jobs\GeocodeAddressJob;

class RetryGeocoding extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geocoding:retry-missing {--limit=500}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch geocoding jobs for Massa with missing coordinates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $this->info("Finding Massa without coordinates (Limit: {$limit})...");
        
        $massaList = Massa::whereNull('latitude')
            ->orWhereNull('longitude')
            ->limit($limit)
            ->get();
            
        $count = $massaList->count();
        $this->info("Found {$count} records.");
        
        if ($count === 0) {
            return;
        }

        $bar = $this->output->createProgressBar($count);
        
        foreach ($massaList as $massa) {
            GeocodeAddressJob::dispatch($massa->id)->onQueue('default'); // Use default queue to run immediately if worker is listening
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Dispatched {$count} jobs.");
    }
}
