<?php

namespace App\Console\Commands;

use App\Services\SettingsService;
use Illuminate\Console\Command;

class GenerateBrandStyles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:branding';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to generate the branding styles with values from the settings table.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $service = app(SettingsService::class);
        $service->writeBrandingStylesForColour(setting('primary_colour'));
    }
}
