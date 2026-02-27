<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

#[AsCommand(name: 'clear:cache')]
class OptimizeProject extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all optimization and cache clearing commands';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Clearing cache...');
        Artisan::call('cache:clear');
        $this->info('Cache cleared.');

        $this->info('Clearing view cache...');
        Artisan::call('view:clear');
        $this->info('View cache cleared.');

        $this->info('Clearing routes cache...');
        Artisan::call('route:clear');
        $this->info('Route cache cleared.');

        $this->info('Clearing config cache...');
        Artisan::call('config:clear');
        $this->info('Config cache cleared.');

        $this->info('All optimization commands executed successfully!');

        return 0; // Indicates success
    }
}
