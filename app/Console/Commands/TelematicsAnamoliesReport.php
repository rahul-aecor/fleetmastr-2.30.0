<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Mail;
use Carbon\Carbon as Carbon;
use App\Models\TelematicsJourneys;
use App\Models\Vehicle;
use Maatwebsite\Excel\Facades\Excel;
use App\Custom\Helper\Common;


class TelematicsAnamoliesReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telematics:anamolies-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to generate report for all excel anamolies.';

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
        $commonHelper = new Common();
        $startDate = Carbon::yesterday()->startOfDay()->format('Y-m-d H:i:s');
        $endDate = Carbon::yesterday()->endOfDay()->format('Y-m-d H:i:s');
        $repDate = Carbon::now()->startOfDay()->format('D jS F Y');
        $vehicleList = array();

        $sheetRowsCount = array();
        $sheetArray=array();
        $excelFileDetail=array( "title" => "Telematics Anomalies Report - " . $repDate );
        $lableArray = [ 'Registration', 'Make', 'Model', 'Journey Id', 'Start time', 'End time', 'Incident count', 'Idle duration', 'Idle duration min', 'Fuel', 'CO2', 'Distance (miles)', 'Distance', 'Odometer Diff', 'Odometer start', 'Odometer end', 'Max MPH', 'Avg MPH'];

        // Where 'Max MPH' is greater than 100 MPH
        $ruleOne = TelematicsJourneys::select('vrn','make','model','journey_id',DB::raw('DATE_FORMAT(CONVERT_TZ(start_time, "UTC", "Europe/London"),"%Y-%m-%d %H:%i:%s") as start_time'),DB::raw('DATE_FORMAT(CONVERT_TZ(end_time, "UTC", "Europe/London"),"%Y-%m-%d %H:%i:%s") AS end_time'),'incident_count','gps_idle_duration',DB::raw('SEC_TO_TIME(gps_idle_duration) AS gps_idle_duration_min'),'fuel','co2',DB::raw('CASE WHEN gps_distance is null THEN 0 ELSE cast((gps_distance* 0.00062137) as decimal(10,2)) END AS gps_distance_miles'),'gps_distance',DB::raw('(odometer_end-odometer_start) as odo_diff'),'odometer_start','odometer_end',DB::raw('CASE WHEN max_speed is null THEN 0 ELSE cast((max_speed* 2.236936) as decimal(10,2)) END AS mxmph'), DB::raw('CASE WHEN avg_speed is null THEN 0 ELSE cast((avg_speed* 2.236936) as decimal(10,2)) END AS avgmph'))
                                    ->where('start_time','>=', $startDate)
                                    ->where('start_time','<=', $endDate)
                                    ->where(DB::raw('max_speed * 2.237'),'>',100)
                                    // ->where(DB::raw('max_speed * 2.237'),'>',75)
                                    ->get()
                                    ->toArray();
                                    // ->toSql();
        $sheet=array();
        $sheet['labelArray'] = $lableArray;
        $sheet['dataArray'] = $ruleOne;
        $sheet['otherParams'] = ['sheetName' => "Speed"];
        $sheet['columnFormat'] = array();
        $sheet['columnColor'] = ['Q' => 'FF0000'];
        array_push($sheetArray, $sheet);
        $sheetRowsCount['Speed'] = count($ruleOne);
        foreach ($ruleOne as $item) {
            array_push($vehicleList, $item['vrn']);
        }
/*
        // Where 'Idling Min' is greater than 1 hour
        $ruleTwo = TelematicsJourneys::select('vrn','make','model','journey_id',DB::raw('DATE_FORMAT(CONVERT_TZ(start_time, "UTC", "Europe/London"),"%Y-%m-%d %H:%i:%s") as start_time'),DB::raw('DATE_FORMAT(CONVERT_TZ(end_time, "UTC", "Europe/London"),"%Y-%m-%d %H:%i:%s") AS end_time'),'incident_count','gps_idle_duration',DB::raw('SEC_TO_TIME(gps_idle_duration) AS gps_idle_duration_min'),'fuel','co2',DB::raw('CASE WHEN gps_distance is null THEN 0 ELSE cast((gps_distance* 0.00062137) as decimal(10,2)) END AS gps_distance_miles'),'gps_distance',DB::raw('(odometer_end-odometer_start) as odo_diff'),'odometer_start','odometer_end',DB::raw('CASE WHEN max_speed is null THEN 0 ELSE cast((max_speed* 2.236936) as decimal(10,2)) END AS mxmph'), DB::raw('CASE WHEN avg_speed is null THEN 0 ELSE cast((avg_speed* 2.236936) as decimal(10,2)) END AS avgmph'))
                                    ->where('start_time','>=', $startDate)
                                    ->where('start_time','<=', $endDate)
                                    ->where('gps_idle_duration','>',3600)
                                    // ->where('gps_idle_duration','>',3000)
                                    ->get()
                                    ->toArray();
                                    // ->toSql();
        $sheet=array();
        $sheet['labelArray'] = $lableArray;
        $sheet['dataArray'] = $ruleTwo;
        $sheet['otherParams'] = ['sheetName' => "Idling"];
        $sheet['columnFormat'] = array();
        $sheet['columnColor'] = ['I' => 'FF0000'];
        array_push($sheetArray, $sheet);
        $sheetRowsCount['Idling'] = count($ruleTwo);
*/
        // Where 'Distance (Miles)' is greater than 1 AND 'Fuel (Litres)' value is less than 0.05
        $ruleThree = TelematicsJourneys::select('vrn','make','model','journey_id',DB::raw('DATE_FORMAT(CONVERT_TZ(start_time, "UTC", "Europe/London"),"%Y-%m-%d %H:%i:%s") as start_time'),DB::raw('DATE_FORMAT(CONVERT_TZ(end_time, "UTC", "Europe/London"),"%Y-%m-%d %H:%i:%s") AS end_time'),'incident_count','gps_idle_duration',DB::raw('SEC_TO_TIME(gps_idle_duration) AS gps_idle_duration_min'),'fuel','co2',DB::raw('CASE WHEN gps_distance is null THEN 0 ELSE cast((gps_distance* 0.00062137) as decimal(10,2)) END AS gps_distance_miles'),'gps_distance',DB::raw('(odometer_end-odometer_start) as odo_diff'),'odometer_start','odometer_end',DB::raw('CASE WHEN max_speed is null THEN 0 ELSE cast((max_speed* 2.236936) as decimal(10,2)) END AS mxmph'), DB::raw('CASE WHEN avg_speed is null THEN 0 ELSE cast((avg_speed* 2.236936) as decimal(10,2)) END AS avgmph'))
                                    ->where('start_time','>=', $startDate)
                                    ->where('start_time','<=', $endDate)
                                    ->where('gps_distance','>',1610)
                                    ->where('fuel','=',0)
                                    ->get()
                                    ->toArray();
                                    // ->toSql();
        $sheet=array();
        $sheet['labelArray'] = $lableArray;
        $sheet['dataArray'] = $ruleThree;
        $sheet['otherParams'] = ['sheetName' => "Fuel"];
        $sheet['columnFormat'] = array();
        $sheet['columnColor'] = ['J' => 'FF0000'];
        array_push($sheetArray, $sheet);
        $sheetRowsCount['Fuel'] = count($ruleThree);
        foreach ($ruleThree as $item) {
            array_push($vehicleList, $item['vrn']);
        }

        // Where 'Distance (Miles)' is greater than 1 AND 'CO2 (Kg)' value is 0.
        $ruleFour = TelematicsJourneys::select('vrn','make','model','journey_id',DB::raw('DATE_FORMAT(CONVERT_TZ(start_time, "UTC", "Europe/London"),"%Y-%m-%d %H:%i:%s") as start_time'),DB::raw('DATE_FORMAT(CONVERT_TZ(end_time, "UTC", "Europe/London"),"%Y-%m-%d %H:%i:%s") AS end_time'),'incident_count','gps_idle_duration',DB::raw('SEC_TO_TIME(gps_idle_duration) AS gps_idle_duration_min'),'fuel','co2',DB::raw('CASE WHEN gps_distance is null THEN 0 ELSE cast((gps_distance* 0.00062137) as decimal(10,2)) END AS gps_distance_miles'),'gps_distance',DB::raw('(odometer_end-odometer_start) as odo_diff'),'odometer_start','odometer_end',DB::raw('CASE WHEN max_speed is null THEN 0 ELSE cast((max_speed* 2.236936) as decimal(10,2)) END AS mxmph'), DB::raw('CASE WHEN avg_speed is null THEN 0 ELSE cast((avg_speed* 2.236936) as decimal(10,2)) END AS avgmph'))
                                    ->where('start_time','>=', $startDate)
                                    ->where('start_time','<=', $endDate)
                                    ->where('gps_distance','>',1610)
                                    ->where('co2',0)
                                    ->get()
                                    ->toArray();
                                    // ->toSql();
        $sheet=array();
        $sheet['labelArray'] = $lableArray;
        $sheet['dataArray'] = $ruleFour;
        $sheet['otherParams'] = ['sheetName' => "CO2"];
        $sheet['columnFormat'] = array();
        $sheet['columnColor'] = ['K' => 'FF0000'];
        array_push($sheetArray, $sheet);
        $sheetRowsCount['CO2'] = count($ruleFour);
        foreach ($ruleFour as $item) {
            array_push($vehicleList, $item['vrn']);
        }

        // Where 'Odo (End)' value minus 'Odo (Start)' value is greater than 'Distance (Miles)' (apply +1 and -1 margin)
        $ruleFive = TelematicsJourneys::select('vrn','make','model','journey_id',DB::raw('DATE_FORMAT(CONVERT_TZ(start_time, "UTC", "Europe/London"),"%Y-%m-%d %H:%i:%s") as start_time'),DB::raw('DATE_FORMAT(CONVERT_TZ(end_time, "UTC", "Europe/London"),"%Y-%m-%d %H:%i:%s") AS end_time'),'incident_count','gps_idle_duration',DB::raw('SEC_TO_TIME(gps_idle_duration) AS gps_idle_duration_min'),'fuel','co2',DB::raw('CASE WHEN gps_distance is null THEN 0 ELSE cast((gps_distance* 0.00062137) as decimal(10,2)) END AS gps_distance_miles'),'gps_distance',DB::raw('(odometer_end-odometer_start) as odo_diff'),'odometer_start','odometer_end',DB::raw('CASE WHEN max_speed is null THEN 0 ELSE cast((max_speed* 2.236936) as decimal(10,2)) END AS mxmph'), DB::raw('CASE WHEN avg_speed is null THEN 0 ELSE cast((avg_speed* 2.236936) as decimal(10,2)) END AS avgmph'))
                                    ->where('start_time','>=', $startDate)
                                    ->where('start_time','<=', $endDate)
                                    ->whereNotBetween(DB::raw('gps_distance-(odometer_end-odometer_start)'), [-1610,1610])
                                    ->get()
                                    ->toArray();
                                    // ->toSql();

        $sheet=array();
        $sheet['labelArray'] = $lableArray;
        $sheet['dataArray'] = $ruleFive;
        $sheet['otherParams'] = ['sheetName' => "Odo"];
        $sheet['columnFormat'] = array();
        $sheet['columnColor'] = ['M' => 'FF0000', 'N' => 'FF0000'];
        array_push($sheetArray, $sheet);
        $sheetRowsCount['Odo'] = count($ruleFive);
        foreach ($ruleFive as $item) {
            array_push($vehicleList, $item['vrn']);
        }

        // List of RIFD which are not mapped.
        $rifds = DB::table('telematics_journeys')
                        ->select(DB::raw("distinct telematics_journeys.user_id, telematics_journeys.vrn, REPLACE(JSON_EXTRACT(telematics_journey_details.raw_json, '$.driver1_id'),'\"','') as 'RFID'"))
                        ->join('telematics_journey_details', 'telematics_journeys.id', '=', 'telematics_journey_details.telematics_journey_id')
                        ->where('telematics_journey_details.ns','tm8.jny.sum.ex1')
                        ->where('telematics_journeys.start_time','>=', $startDate)
                        ->where('telematics_journeys.start_time','<=', $endDate)
                        ->where('telematics_journeys.user_id', 1)
                        ->groupBy('telematics_journeys.id')
                        ->having('RFID',"<>","")
                        ->get();

        $rifdData = [];
        foreach($rifds as $rifd )
        {
            $rifdArr = (array) $rifd;
            unset($rifdArr['user_id']);
            //unset($rifdArr['vrn']);
            array_push($rifdData, $rifdArr);
        }

        $sheet=array();
        $sheet['labelArray'] = ['Registration', 'RIFD'];
        $sheet['dataArray'] = $rifdData;
        $sheet['otherParams'] = ['sheetName' => "RIFD"];
        $sheet['columnFormat'] = array();
        array_push($sheetArray, $sheet);
        $sheetRowsCount['rifd'] = count($rifdData);

        $dedupeVehicles = array();
        $vehicleList = array_values(array_unique($vehicleList));
        sort($vehicleList);
        foreach ($vehicleList as $value) {
            array_push($dedupeVehicles,['vehicle'=>$value]);
        }

        $sheet=array();
        $sheet['labelArray'] = ['Vehicle'];
        $sheet['dataArray'] = $dedupeVehicles;
        $sheet['otherParams'] = ['sheetName' => "Vehicle List"];
        $sheet['columnFormat'] = array();
        array_push($sheetArray, $sheet);
        $sheetRowsCount['vehicle'] = count($dedupeVehicles);

        // Where RIDF are not recorded for the Journeys
        $journeyWithoutRIFD = DB::table('telematics_journeys')
                        ->select('telematics_journeys.vrn','telematics_journeys.make','telematics_journeys.model','telematics_journeys.journey_id','telematics_journeys.uid',DB::raw('REPLACE(JSON_EXTRACT(telematics_journey_details.raw_json, "$.driver1_id"),"\"","") as rifd'))
                        ->join('telematics_journey_details', 'telematics_journeys.id', '=', 'telematics_journey_details.telematics_journey_id')
                        ->where('telematics_journey_details.ns','tm8.jny.sum.ex1')
                        ->where('telematics_journeys.start_time','>=', $startDate)
                        ->where('telematics_journeys.start_time','<=', $endDate)
                        ->where(DB::raw('REPLACE(JSON_EXTRACT(telematics_journey_details.raw_json, "$.driver1_id"),"\"","")'), "=", "")
                        ->orderBy('telematics_journeys.vrn')
                        ->orderBy('telematics_journeys.journey_id')
                        ->get();

        $journeyWithoutRIFDData = [];
        foreach($journeyWithoutRIFD as $journey )
        {
            array_push($journeyWithoutRIFDData, (array) $journey);
        }

        $sheet=array();
        $sheet['labelArray'] = ['Registration', 'Make', 'Model', 'Journey Id', 'Device ID', 'RIFD'];
        $sheet['dataArray'] = $journeyWithoutRIFDData;
        $sheet['otherParams'] = ['sheetName' => "Journeys without RIFD"];
        $sheet['columnFormat'] = array();
        array_push($sheetArray, $sheet);

        // Summary Count for Vehicle Journeys
        $allVehicles = Vehicle::where('is_telematics_enabled', '1')
                            ->lists('id','registration')
                            ->toArray();


        $vehicleTotalJourneys = TelematicsJourneys::where('start_time','>=', $startDate)
                                    ->where('start_time','<=', $endDate)
                                    ->select('vrn', DB::raw('count(id) as cnt'))
                                    ->groupBy('vrn')
                                    ->get();

        $vehicleTotalJourneysArray = [];
        foreach ($vehicleTotalJourneys as $veh) {
            $vehicleTotalJourneysArray[$veh->vrn] = $veh->cnt;
        }

        $vehicleRFIDJourneys = DB::table('telematics_journeys')
                        ->select('telematics_journeys.vrn',DB::raw('REPLACE(JSON_EXTRACT(telematics_journey_details.raw_json, "$.driver1_id"),"\"","") as rfid'), DB::raw('count(telematics_journeys.id) as cnt'))
                        ->join('telematics_journey_details', 'telematics_journeys.id', '=', 'telematics_journey_details.telematics_journey_id')
                        ->where('telematics_journey_details.ns','tm8.jny.sum.ex1')
                        ->where('telematics_journeys.start_time','>=', $startDate)
                        ->where('telematics_journeys.start_time','<=', $endDate)
                        ->where(DB::raw('REPLACE(JSON_EXTRACT(telematics_journey_details.raw_json, "$.driver1_id"),"\"","")'), "<>", "")
                        ->groupBy('telematics_journeys.vrn')
                        ->groupBy('rfid')
                        ->get();

        $vehicleRFIDJourneysArray = [];
        foreach($vehicleRFIDJourneys as $journey )
        {
            $vehicleRFIDJourneysArray[$journey->vrn] = ['rfid'=>$journey->rfid, 'cnt'=> $journey->cnt];
        }

        $allVehiclesData = [];
        foreach ($allVehicles as $veh => $vid) {
            $vehTotal = isset($vehicleTotalJourneysArray[$veh]) ? $vehicleTotalJourneysArray[$veh] : 0;
            $vehWithRFID = isset($vehicleRFIDJourneysArray[$veh]) ? $vehicleRFIDJourneysArray[$veh]['cnt'] : 0;
            $vehRFID = isset($vehicleRFIDJourneysArray[$veh]) ? $vehicleRFIDJourneysArray[$veh]['rfid'] : '';
            $vehWithoutRFID = $vehTotal - $vehWithRFID;
            array_push($allVehiclesData, ['vrn'=> $veh, 'total'=> $vehTotal, 'withRFID' =>$vehWithRFID, 'withoutRFID'=>$vehWithoutRFID, 'rfid'=>$vehRFID]);
        }

        $sheet=array();
        $sheet['labelArray'] = ['Registration', 'Total Journeys', 'Journeys with RFID', 'Journeys without RFID', 'RFID'];
        $sheet['dataArray'] = $allVehiclesData;
        $sheet['otherParams'] = ['sheetName' => "VRN Journey Summary"];
        $sheet['columnFormat'] = array();
        array_push($sheetArray, $sheet);

        // List of Journyes For which we didn't got journey_start
        $journeyWithoutStart = DB::table('telematics_temp_journeys')
                    ->select(DB::raw('DISTINCT journey_id, vrn, uid, date(created_at)'))
                    ->where('created_at','>=', $startDate)
                    ->where('created_at','<=', $endDate)
                    ->get();

        $journeyWithoutStart = array_map(function ($value) {
            return (array)$value;
        }, $journeyWithoutStart);

        $sheet=array();
        $sheet['labelArray'] = ['journey_id', 'VRN', 'Device UID', 'Date'];
        $sheet['dataArray'] = $journeyWithoutStart;
        $sheet['otherParams'] = ['sheetName' => "Journey Without Start"];
        $sheet['columnFormat'] = array();
        array_push($sheetArray, $sheet);


        // List of vehicles which are not moved since 17 days
        $unmovedVehicles = Vehicle::join('vehicle_regions','vehicles.vehicle_region_id', '=', 'vehicle_regions.id')
         ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
         ->leftjoin('users as nominatedDriver', 'vehicles.nominated_driver', '=', 'nominatedDriver.id')
         ->where('is_telematics_enabled','1')
         ->where('telematics_latest_journey_time','<=', Carbon::today()->subDays(17)->startOfDay())
         ->whereNotNull('vehicles.vehicle_region_id')
         ->whereNotIn('vehicles.status',['Archived','Archived - De-commissioned','Archived - Written off'])
         ->select('vehicles.registration','vehicle_regions.name as vehicle_region_name',
            DB::raw("CASE WHEN vehicles.nominated_driver IS NULL THEN 'Unassigned' ELSE CONCAT(nominatedDriver.first_name, ' ', nominatedDriver.last_name) END as nominatedDriverName"),
            DB::raw('CASE WHEN vehicle_types.vehicle_category = "non-hgv" THEN "Non-HGV" ELSE "HGV" END AS vehicle_category'), 
            'vehicle_type', 
            DB::raw("CASE WHEN vehicles.telematics_ns IN ('".implode("','",config('config-variables.moving_events'))."') THEN 'Driving' WHEN vehicles.telematics_ns IN ('".implode("','",config('config-variables.start_events'))."') THEN 'Driving' WHEN vehicles.telematics_ns IN ('".implode("','",config('config-variables.stopped_events'))."') THEN 'Stopped' WHEN vehicles.telematics_ns IN ('".implode("','",config('config-variables.idling_events'))."') THEN 'Idling' ELSE '' END as telematics_ns_label"),
            DB::raw('DATE_FORMAT(CONVERT_TZ(telematics_latest_journey_time, "UTC", "'.config('config-variables.format.displayTimezone').'"),"%Y-%m-%d %H:%i:%s") as telematics_latest_journey_time'),
            DB::raw('DATE_FORMAT(CONVERT_TZ(telematics_latest_location_time, "UTC", "'.config('config-variables.format.displayTimezone').'"),"%Y-%m-%d %H:%i:%s") as telematics_latest_location_time'),
            DB::raw("CONCAT(telematics_street,', ',telematics_town,', ',telematics_postcode) AS teleamtics_journey_details")
            )->get()->toArray();

        $unmovedVehicles = array_map(function ($value) {
            return (array)$value;
        }, $unmovedVehicles);

        $sheet=array();
        $sheet['labelArray'] = ['Registration','Region','Nominated Driver','Category','Type','Status','Last Journey','Last Location Date','Last Location'];
        $sheet['dataArray'] = $unmovedVehicles;
        $sheet['otherParams'] = ['sheetName' => "Journeys"];
        $sheet['columnFormat'] = array();
        array_push($sheetArray, $sheet);

        $sheetRowsCount['journeyWithoutStart'] = count($journeyWithoutStart);
        $sheetRowsCount['unmovedVehicles'] = count($unmovedVehicles);

        $exportFile=$commonHelper->toExcel($excelFileDetail, $sheetArray, 'xlsx', 'no');

        Mail::send('emails.telematics_anamolies_response_email', ['counts'=>$sheetRowsCount], function ($message) use($exportFile, $repDate) {
                    $message->to(explode(",",env('TELEMATICS_ANAMOLIES_RESPONSE_EMAIL', 'ndeopura@aecordigital.com')));
                    $message->subject(strtoupper(env('BRAND_NAME')) . ' - Telematics - Data Issues');
                    $message->attach($exportFile, ['as' => 'Telematics Anomalies Report - ' . $repDate]);
                });
    }
}
