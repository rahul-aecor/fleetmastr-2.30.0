<?php

namespace App\Console\Commands;

use DB;
use StdClass;
use Storage;
use Illuminate\Console\Command;

class TemporaryJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'temporary:checkjson';

    protected $inputFile;

    protected $outputPath;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is for temporary processing';

    protected $vehicleList = [
        'hgv' => [],
        'nonhgv' => [
            'panelvan' => [],
            'nonpanelvan' => [],
        ]
    ];
    protected $currentCheckoutString = '';
    protected $previousCheckoutString = [
        'hgv' => '',
        'nonhgv' => [
            'panelvan' => '',
            'nonpanelvan' => '',
        ]
    ];
    protected $numOfDifferences = 0;
    protected $previousCheckId = 0;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();        
        $this->outputPath = storage_path('json/output/');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->fetchVehicleLists();
        // NON_HGV
        \Log::info('#### SECTION 1: PROCESS STARTS FOR NON-HGV ####');
        \Log::info('## SECTION 1.1: PANEL VAN : VEHICLE CHECK');
        $this->compareVehicleChecks($this->vehicleList['nonhgv']['panelvan'], 'Vehicle Check');
        \Log::info('## SECTION 1.2: PANEL VAN : RETURN CHECK');
        $this->compareVehicleChecks($this->vehicleList['nonhgv']['panelvan'], 'Return Check');
        \Log::info('## SECTION 1.3: PANEL VAN : DEFECT REPORT');
        // $this->compareVehicleChecks($this->vehicleList['nonhgv']['panelvan'], 'Defect Report');
        \Log::info('## SECTION 1.4: NONPANEL VAN : VEHICLE CHECK');
        $this->compareVehicleChecks($this->vehicleList['nonhgv']['nonpanelvan'], 'Vehicle Check');
        \Log::info('## SECTION 1.5: NONPANEL VAN : RETURN CHECK');
        $this->compareVehicleChecks($this->vehicleList['nonhgv']['nonpanelvan'], 'Return Check');
        \Log::info('## SECTION 1.6: NONPANEL VAN : DEFECT REPORT');
        // $this->compareVehicleChecks($this->vehicleList['nonhgv']['nonpanelvan'], 'Defect Report');
        \Log::info('#### PROCESS ENDS FOR NON-HGV ####');
        // HGV
        \Log::info('#### SECTION 2: PROCESS STARTS FOR HGV ####');
        \Log::info('## SECTION 2.1: HGV : VEHICLE CHECK');
        $this->compareVehicleChecks($this->vehicleList['hgv'], 'Vehicle Check');
        \Log::info('## SECTION 2.2: HGV : RETURN CHECK');
        $this->compareVehicleChecks($this->vehicleList['hgv'], 'Return Check');
        \Log::info('## SECTION 2.3: HGV : DEFECT REPORT');
        // $this->compareVehicleChecks($this->vehicleList['hgv'], 'Defect Report');
    }

    protected function fetchVehicleLists()
    {
        // Fetch HGV vehicle list
        $hgv_vehicles = DB::table('vehicles')
            ->join('vehicle_types', 'vehicle_types.id', '=', 'vehicles.vehicle_type_id')
            ->where('vehicle_types.vehicle_category', 'hgv')
            ->select('vehicles.id as _id')
            ->lists('_id');            
        $this->vehicleList['hgv'] = $hgv_vehicles;

        // Fetch NONHGV vehicle list for non panel van
        $non_hgv_nonpanelvan_vehicles = DB::table('vehicles')
            ->join('vehicle_types', 'vehicle_types.id', '=', 'vehicles.vehicle_type_id')
            ->where('vehicle_types.vehicle_category', 'non-hgv')
            ->where('vehicle_types.vehicle_type', '!=', 'Panel Van')
            ->select('vehicles.id as _id')
            ->lists('_id');
        $this->vehicleList['nonhgv']['nonpanelvan'] = $non_hgv_nonpanelvan_vehicles;

        // Fetch NONHGV vehicle list for panel van
        $non_hgv_panelvan_vehicles = DB::table('vehicles')
            ->join('vehicle_types', 'vehicle_types.id', '=', 'vehicles.vehicle_type_id')
            ->where('vehicle_types.vehicle_category', 'non-hgv')
            ->where('vehicle_types.vehicle_type', '=', 'Panel Van')
            ->select('vehicles.id as _id')
            ->lists('_id');
        $this->vehicleList['nonhgv']['panelvan'] = $non_hgv_panelvan_vehicles;

        $this->info("NON-HGV PANEL VAN vehicles:     " . count($this->vehicleList['nonhgv']['panelvan']));
        $this->info("NON-HGV NON PANEL VAN vehicles: " . count($this->vehicleList['nonhgv']['nonpanelvan']));
        $this->info("HGV PANEL VAN vehicles:         " . count($this->vehicleList['hgv']));
    }

    protected function compareVehicleChecks($vehicle_list, $check_type)
    {
        $this->previousCheckoutString = '';
        $checks = DB::table('checks')
            ->whereIn('vehicle_id', $vehicle_list)
            ->where('type', $check_type)
            ->select('id', 'new_json', 'type')
            ->orderBy('id')
            ->get();
        
        if ($check_type == 'Vehicle Check' || $check_type == 'Return Check') {            
            foreach ($checks as $key => $check) {            
                try {
                    $this->processCheckout($check, $check_type);    
                } catch (\Exception $e) {
                    \Log::info('Error when processing');
                }
                
            }
        }
        if ($check_type == 'Defect Report') {
            foreach ($checks as $key => $check) { 
                // $this->processDefectReport($check, $check_type);
            }
        }
    }

    protected function processCheckout($check, $check_type)
    {
        $json = json_decode($check->new_json);
        $currentString = '';
        foreach ($json->screens->screen as $screen) {
            // $currentString .= $screen->title . ' ' . $screen->text;            
            $currentString .= $screen->title . ' ';    
        }
        $previousString = $this->previousCheckoutString;        
        
        $previousString = str_replace(PHP_EOL, '', $previousString);
        $currentString = str_replace(PHP_EOL, '', $currentString);

        if ($currentString !== $previousString) {
            $this->numOfDifferences++;
            \Log::info("NOTE: FOUND DIFFERENCE AT {$check->id}");
            \Log::info("PREVIOUS CHECKID: {$this->previousCheckId}");
            \Log::info("PREVIOUS STRING: {$previousString}");
            \Log::info("CURRENT STRING:  {$currentString}");            
        }
        $this->previousCheckoutString = $currentString;
        $this->previousCheckId = $check->id;
    }

    protected function processDefectReport($check)
    {
        $json = json_decode($check->new_json);
        $currentString = '';
        foreach ($json->screens->screen[0]->options->optionList as $option) {
            $currentString .= $option->text . ' ' . $option->defects->title;
            foreach ($option->defects as $defect) {
                $currentString .= $defect->text;
            }            
        }
        $previousString = $this->previousCheckoutString;        
        
        $previousString = str_replace(PHP_EOL, '', $previousString);
        $currentString = str_replace(PHP_EOL, '', $currentString);

        if ($currentString !== $previousString) {
            $this->numOfDifferences++;
            \Log::info("NOTE: FOUND DIFFERENCE AT {$check->id}");
            \Log::info("PREVIOUS STRING: {$previousString}");
            \Log::info("CURRENT STRING:  {$currentString}");            
        }
        $this->previousCheckoutString = $currentString;
    }
}
