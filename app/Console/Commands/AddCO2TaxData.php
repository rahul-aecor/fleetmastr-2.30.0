<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Notification;
use Illuminate\Console\Command;
use App\Models\Settings;

class AddCO2TaxData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:co2taxdata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add CO2 Tax Data';

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
        $today = Carbon::today();
        $currentyear = date_format(Carbon::now(),"Y");
        $lastYearKey = 'hmrc_co2_'.($currentyear - 1).'_'.$currentyear;

        $last_co2_setting = Settings::where('key', '=', $lastYearKey)->first();
        $new_c02_setting = $last_co2_setting->replicate();

        $hmrcdata = json_decode($new_c02_setting->value);
        $hmrcdata->year = $currentyear .'-'.($currentyear + 1);
        $hmrcdata->edited_by = 'System';
        $hmrcdata->edited_at = date_format(Carbon::now(),"Y-m-d H:i:s");
        $new_c02_setting->key = 'hmrc_co2_'.$currentyear.'_'.($currentyear + 1);
        $new_c02_setting->value = json_encode($hmrcdata);
        $new_c02_setting->save();
    }
}
