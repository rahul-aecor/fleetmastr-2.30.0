<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleAssignment;
use Illuminate\Http\Request;
Use Auth;
use JavaScript;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use View;
use App\Models\Defect;
use App\Models\Settings;
use App\Models\P11dReport;
use App\Models\VehicleUsageHistory;
use Carbon\Carbon as Carbon;
use App\Custom\Helper\Common;
use App\Custom\Helper\P11dReportHelper;
use App\Services\VehicleService;
use App\Services\Report;
use App\Models\VehicleRegions;
use App\Models\VehicleArchiveHistory;
use App\Models\User;

class ReportsController extends Controller
{
    public $title= 'Reports';

    public function __construct(VehicleService $vehicleService, Report $reportService) {
        View::share( 'title', $this->title );
        $this->vehicleService = $vehicleService;
        $this->reportService = $reportService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $p11dReportHelper = new P11dReportHelper();
        $taxYearList = P11dReport::orderBy('tax_year', 'desc')->lists('tax_year', 'url')->toArray();
        $currTaxYear = $p11dReportHelper->calcTaxYear();
        $currTaxYearParts = explode('-', $currTaxYear);
        $prevTaxYearParts = [$currTaxYearParts[0]-1, $currTaxYearParts[1]-1];
        $prevTaxYear = implode('-', $prevTaxYearParts);
        if(!in_array($prevTaxYear, $taxYearList)){
            $taxYearList = [$prevTaxYear => $prevTaxYear] + $taxYearList;
        }
        $taxYearList = [$p11dReportHelper->calcTaxYear() => $p11dReportHelper->calcTaxYear()] + $taxYearList;
        $userAccessibleRegions = $this->vehicleService->getDivisionRegionLinkedData();
        $vehicleRegions = VehicleRegions::all()->lists('id')->count();
        $flag = 0;
        if($vehicleRegions == count($userAccessibleRegions)) {
            $flag=1;
        }

        return redirect("customreports");
        // return view('reports.index',compact('taxYearList','userAccessibleRegions','flag'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function downloadReport($name, $period="curr")
    {
        $name_region = array(
            "c" => "North",
            "e" => "East",
            "f" => "South",
            "g" => "West",
            "h" => "Scotland",
            "i" => "Head Office"
        );

        switch ($name) {
            case 'a':
                $lableArray = [
                    'Registration',
                    'HGV/Non-HGV',
                    'Type',
                    'Manufacturer',
                    'Model',
                    'Vehicle Location',
                    'Vehicle Region',
                    'Repair/Maintenance Location',
                    'Defect Date',
                    'Defect Number',
                    'Odometer',
                    'Defect Category',
                    'Defect',
                    'Vehicle Status',
                    'Defect Status',
                    'Last Comment Date',
                    'Last Comment',                    
                ];
                $startDate = (new Carbon('first day of this month'))->format('Y-m-d 00:00:00');
                $endDate = (new Carbon('now'))->toDateTimeString();
                if(strtolower($period) == "prev"){
                    $startDate = (new Carbon('first day of last month'))->format('Y-m-d 00:00:00');
                    $endDate = (new Carbon('first day of this month'))->format('Y-m-d 00:00:00');
                }
                $dataArray = [];
                $defects = Defect::with('history')
                    ->whereBetween('report_datetime',[$startDate,$endDate])
                    ->whereHas('vehicle', function($q)
                    {
                        // $q->whereIn('vehicle_region', config('config-variables.userAccessibleRegionsForQuery'));
                        $q->whereIn('vehicle_region_id', Auth::user()->regions->lists('id')->toArray());
                    })
                    // ->orderBy('created_at','desc')
                    ->orderBy('report_datetime')
                    ->get();
                foreach ($defects as $defect) {
                    if (empty($defect->history->first())) {
                        $last_comment_date = "N/A";
                        $last_comment = "N/A";
                    }
                    else {
                        $last_comment_date = $defect->history->first()->report_datetime->format('d-m-Y');
                        $last_comment = $defect->history->first()->comments;
                    }
                    $data = [
                        $defect->vehicle->registration,
                        ($defect->vehicle->type->vehicle_category == "hgv")?"HGV":"Non-HGV",
                        $defect->vehicle->type->vehicle_type,
                        $defect->vehicle->type->manufacturer,
                        $defect->vehicle->type->model,
                        (!is_null($defect->vehicle->location)) ? $defect->vehicle->location->name : "",
                        //$defect->vehicle->vehicle_region,
                        $defect->vehicle->region->name,
                        (!is_null($defect->vehicle->repair_location)) ? $defect->vehicle->repair_location->name: "",
                        $defect->report_datetime->format('d-m-Y'),
                        $defect->id,
                        $defect->vehicle->last_odometer_reading,
                        $defect->defectMaster->page_title,
                        $defect->defectMaster->defect,
                        $defect->vehicle->status,
                        $defect->status,
                        $last_comment_date,
                        $last_comment,
                    ];
                    array_push($dataArray, $data);
                }
                $otherParams = [
                    'sheetTitle' => "Month To Date Defect Report (All Regions)",
                    'sheetSubTitle_lable' => "Month:",
                    'sheetSubTitle_value' => (strtolower($period)=="curr")?(new Carbon('this month'))->format('F Y'):(new Carbon('last month'))->format('F Y'),
                    'sheetName' => (strtolower($period)=="curr")?(new Carbon('this month'))->format('F Y'):(new Carbon('last month'))->format('F Y'),
                    'boldLastRow' => false
                ];
                $this->toExcel($lableArray,$dataArray,$otherParams);
                break;

            case 'b':
                $lableArray = ['Defect Category', 'Defect'];
                $allRegions = Auth::user()->regions->lists('name')->toArray();
                $lableArray = array_merge($lableArray, $allRegions, ['Grand Total']);
                $startDate = Carbon::now()->startOfWeek()->format('Y-m-d 00:00:00');
                $endDate = Carbon::now()->endOfWeek()->format('Y-m-d 23:59:59');
                if(strtolower($period) == "prev"){
                    $startDate = Carbon::now()->addWeeks(-1)->startOfWeek()->format('Y-m-d 00:00:00');
                    $endDate = Carbon::now()->addWeeks(-1)->endOfWeek()->format('Y-m-d 23:59:59');
                }
                $dataArray = [];
                $userRegions = Auth::user()->regions->lists('id')->toArray();
                $defects = Defect::select('defect_master.page_title','defect_master.defect','vehicles.vehicle_region_id as vehicle_region', \DB::raw('COUNT(defects.id) cnt'))
                ->join('defect_master','defects.defect_master_id','=','defect_master.id')
                ->join('vehicles','defects.vehicle_id','=','vehicles.id')
                ->leftjoin('vehicle_divisions','vehicles.vehicle_division_id', '=', 'vehicle_divisions.id')
                ->leftjoin('vehicle_regions','vehicles.vehicle_region_id', '=', 'vehicle_regions.id')
                ->whereBetween('defects.report_datetime',[$startDate,$endDate])
                ->whereIn('vehicles.vehicle_region_id', $userRegions)
                ->groupBy(['defect_master.page_title','vehicle_region'])
                ->get();
                $ddarray = array();
                foreach ($defects as $defect) {
                    $ddarray[$defect->page_title][$defect->defect][$defect->vehicle_region] = $defect->cnt;
                }
                $dataArray = array();
                foreach ($defects as $defect) {
                    $rowData = [];
                    // add initial defect data
                    $rowData[] = $defect->page_title;
                    $rowData[] = $defect->defect;
                    $rowGrandTotal = 0;
                    foreach ($userRegions as $regionId) {
                        $regionData = 0;
                        if(isset($ddarray[$defect->page_title][$defect->defect][$regionId])) {
                            $regionData = (int) $ddarray[$defect->page_title][$defect->defect][$regionId];
                        }
                        // add region wise data
                        $rowData[] = $regionData;
                        $rowGrandTotal += $regionData;
                    }
                    // Grand total
                    $rowData[] = $rowGrandTotal;
                    array_push($dataArray, $rowData);
                }
                $dataArray = array_unique($dataArray, SORT_REGULAR);
                if(!empty($dataArray)){
                    $lastRow = 5 + ((count($dataArray) == 0)?1:count($dataArray));
                    $ddata = [
                        "Grand Total",
                        ""
                    ];
                    for ($i=2; $i < (count($dataArray[0])); $i++) {
                        $excelColumnName = $this->getNameFromNumber($i);
                        $ddata[] = "=SUM(".$excelColumnName."6:".$excelColumnName.$lastRow.")";
                    }

                    array_push($dataArray, $ddata);
                }
                $otherParams = [
                    'sheetTitle' => "Week To Date VOR Defect Report (All Regions)",
                    'sheetSubTitle_lable' => "WC:",
                    'sheetSubTitle_value' => (strtolower($period)=="curr") ? Carbon::now()->startOfWeek()->format('d-m-Y') : Carbon::now()->addWeeks(-1)->startOfWeek()->format('d-m-Y'),
                    'sheetName' => "VOR All Regions",
                    'boldLastRow' => true
                ];
                $this->toExcel($lableArray,$dataArray,$otherParams);
                break;

            // case 'c':
            // case 'e':
            // case 'f':
            // case 'g':
            // case 'h':
            // case 'i':
            //     $region = $name_region[$name];
            //     $lableArray = [
            //         'Defect Category',
            //         'Defect',
            //         $region
            //     ];
            //     $startDate = Carbon::now()->startOfWeek()->format('Y-m-d 00:00:00');
            //     $endDate = Carbon::now()->endOfWeek()->format('Y-m-d 23:59:59');
            //     if(strtolower($period) == "prev"){
            //         $startDate = Carbon::now()->addWeeks(-1)->startOfWeek()->format('Y-m-d 00:00:00');
            //         $endDate = Carbon::now()->addWeeks(-1)->endOfWeek()->format('Y-m-d 23:59:59');
            //     }
            //     $dataArray = [];
            //     $defects = Defect::select('defect_master.page_title','defect_master.defect', \DB::raw('COUNT(defects.id) cnt'))
            //     ->join('defect_master','defects.defect_master_id','=','defect_master.id')
            //     ->join('vehicles','defects.vehicle_id','=','vehicles.id')
            //     ->whereBetween('defects.report_datetime',[$startDate,$endDate])
            //     ->where('vehicles.vehicle_region',$region)
            //     ->groupBy('defect_master.page_title','defect_master.defect')
            //     ->get();
            //     // dd($defects);
            //     $dataArray = array();
            //     foreach ($defects as $defect) {
            //         $ddata = [
            //             $defect->page_title,
            //             $defect->defect,
            //             $defect->cnt
            //         ];
            //         array_push($dataArray, $ddata);
            //     }
            //     $dataArray = array_unique($dataArray, SORT_REGULAR);
            //     if(!empty($dataArray)){
            //         $lastRow = 5 + ((count($dataArray) == 0)?1:count($dataArray));
            //         $ddata = [
            //             "Grand Total",
            //             "",
            //             "=SUM(C6:C".$lastRow.")"
            //         ];
            //         array_push($dataArray, $ddata);
            //     }
            //     $otherParams = [
            //         'sheetTitle' => "Week To Date VOR Defect Report (".$region.")",
            //         'sheetSubTitle_lable' => "WC:",
            //         'sheetSubTitle_value' => (strtolower($period)=="curr") ? Carbon::now()->startOfWeek()->format('d-m-Y') : Carbon::now()->addWeeks(-1)->startOfWeek()->format('d-m-Y'),
            //         'sheetName' => "VOR ".$region,
            //         'boldLastRow' => true
            //     ];
            //     $this->toExcel($lableArray,$dataArray,$otherParams);
            //     break;

            case 'd':
                $lableArray = [
                    'Registration',
                    'HGV/Non-HGV',
                    'Type',
                    'Manufacturer',
                    'Model',
                    'Vehicle Location',
                    'Vehicle Region',
                    'Repair/Maintenance Location',
                    'Dated VOR\'d',
                    'VOR Duration (days)',
                    'Vehicle Status',
                    'Defect Category',
                    'Defect',
                    'Defect Number',
                    'Estimated Completion Date',
                    'Last Comment Date',
                    'Last Comment',                    
                ];
                $startDate = Carbon::now()->startOfWeek()->format('Y-m-d 00:00:00');
                $endDate = Carbon::now()->endOfWeek()->format('Y-m-d 23:59:59');
                if(strtolower($period) == "prev"){
                    $startDate = Carbon::now()->addWeeks(-1)->startOfWeek()->format('Y-m-d 00:00:00');
                    $endDate = Carbon::now()->addWeeks(-1)->endOfWeek()->format('Y-m-d 23:59:59');
                }
                $dataArray = [];
                $defects = Defect::with('history')
                            ->whereBetween('report_datetime',[$startDate,$endDate])
                            ->whereHas('vehicle', function ($query) {
                                $query->whereIn('status', ['VOR','VOR - Accident damage','VOR - MOT', 'VOR - Bodyshop', 'VOR - Bodybuilder', 'VOR - Service', 'VOR - Quarantined'])
                                // ->whereIn('vehicle_region', config('config-variables.userAccessibleRegionsForQuery'));
                                ->whereIn('vehicle_region_id', Auth::user()->regions->lists('id')->toArray());
                                // $query->whereIn('status', ['Roadworthy']);
                            })
                            ->orderBy('report_datetime')
                            ->orderBy('vehicle_id')->get();
                foreach ($defects as $defect) {
                    $duration = '';
                    $vorLogs = $defect->vehicle->vorLogs;
                    if(count($vorLogs)) {
                        $duration = $vorLogs->first()->dt_off_road;
                    }
                    
                    $vorDuration = 'N/A';
                    if(!empty($duration)){
                        $now = Carbon::now();
                        $vorDuration = ($duration->diff($now)->days < 1)? 'Today': $duration->diff($now)->days." Days";
                    }
                    if (empty($defect->history->first())) {
                        $last_comment_date = "N/A";
                        $last_comment = "N/A";
                    }
                    else {
                        $last_comment_date = $defect->history->first()->report_datetime->format('d-m-Y');
                        $last_comment = $defect->history->first()->comments;
                    }
                    $data = [
                        // $defect->report_datetime->format('d-m-Y'),
                        $defect->vehicle->registration,
                        ($defect->vehicle->type->vehicle_category == "hgv")?"HGV":"Non-HGV",
                        $defect->vehicle->type->vehicle_type,
                        $defect->vehicle->type->manufacturer,
                        $defect->vehicle->type->model,
                        (!is_null($defect->vehicle->location)) ? $defect->vehicle->location->name : "",
                        //$defect->vehicle->vehicle_region,
                        $defect->vehicle->region->name,
                        (!is_null($defect->vehicle->repair_location)) ? $defect->vehicle->repair_location->name: "",
                        $duration != '' ? $duration->format('d-m-Y') : $duration,
                        $vorDuration,
                        $defect->vehicle->status,
                        $defect->defectMaster->page_title,
                        $defect->defectMaster->defect,
                        $defect->id,
                        $defect->est_completion_date,
                        $last_comment_date,
                        $last_comment,
                    ];
                    // $dataArray["VOR - ".$defect->report_datetime->format('l')][] = $data;
                    array_push($dataArray, $data);
                }
                $otherParams = [
                    'sheetTitle' => "Week To Date VOR Report (All Regions)",
                    'sheetSubTitle_lable' => "WC:",
                    'sheetSubTitle_value' => (strtolower($period)=="curr") ? Carbon::now()->startOfWeek()->format('d-m-Y') : Carbon::now()->addWeeks(-1)->startOfWeek()->format('d-m-Y'),
                    'sheetName' => "VOR",
                    'boldLastRow' => false
                ];
                // $this->toExcelMulti($lableArray,$dataArray,$otherParams);
                $this->toExcel($lableArray,$dataArray,$otherParams);
                break;

            case 'j':
                $lableArray = [
                    'First Name',
                    'Last Name',
                    'Username/Email',
                    'Region',
                    'Vehicle Take Out',
                    'Vehicle Return',                    
                ];
                $startDate = Carbon::now()->startOfWeek()->format('Y-m-d 00:00:00');
                $endDate = Carbon::now()->endOfWeek()->format('Y-m-d 23:59:59');
                if(strtolower($period) == "prev"){
                    $startDate = Carbon::now()->addWeeks(-1)->startOfWeek()->format('Y-m-d 00:00:00');
                    $endDate = Carbon::now()->addWeeks(-1)->endOfWeek()->format('Y-m-d 23:59:59');
                }
                $dataArray = [];
                $users = \DB::table('users')
                    ->select(\DB::raw('users.first_name, users.last_name, users.email, user_regions.name as region,
                        (SELECT COUNT(checks.id) FROM checks WHERE checks.created_by = users.id AND checks.type = "Vehicle Check" AND checks.created_at BETWEEN "' . $startDate . '" AND "' . $endDate . '") AS totalVehicleCheck,
                        (SELECT COUNT(checks.id) FROM checks WHERE checks.created_by = users.id AND checks.type = "Return Check" AND checks.created_at BETWEEN "' . $startDate . '" AND "' . $endDate . '") AS totalReturnCheck'))
                    ->leftJoin('user_regions', 'users.user_region_id', '=', 'user_regions.id')
                    ->whereNull('users.deleted_at')
                    ->orderBy('totalVehicleCheck', 'DESC')
                    ->orderBy('totalReturnCheck', 'DESC')
                    ->get();
                $dataArray = json_decode(json_encode($users), true);
                $otherParams = [
                    'sheetTitle' => "Week To Date Activity Report (All Regions)",
                    'sheetSubTitle_lable' => "WC:",
                    'sheetSubTitle_value' => (strtolower($period)=="curr") ? Carbon::now()->startOfWeek()->format('d-m-Y') : Carbon::now()->addWeeks(-1)->startOfWeek()->format('d-m-Y'),
                    'sheetName' => Carbon::now()->startOfWeek()->format('F Y'),
                    'boldLastRow' => false
                ];
                // $this->toExcelMulti($lableArray,$dataArray,$otherParams);
                $this->toExcel($lableArray,$dataArray,$otherParams);
                break;
            case 'p11dreport':
                $p11dReportHelper = new P11dReportHelper();
                $commonHelper = new Common();

                $reportfile = $p11dReportHelper->generateReport($period,'yes');
                //$reportFile = $commonHelper->downloadDesktopExcel($excelFileDetail,$sheetArray,'xlsx','yes');
                break;

            default:
                return view('reports.invalid');
                break;
        }
    }

    private function toExcel($lableArray, $dataArray, $otherParams)
    {
        \Excel::create(str_slug($otherParams['sheetTitle']), function($excel) use($lableArray, $dataArray, $otherParams) {
            $excel->setTitle($otherParams['sheetTitle']);
            $excel->sheet($otherParams['sheetName'], function($sheet) use($lableArray, $dataArray, $otherParams) {
                $sheet->row(2, array($otherParams['sheetTitle']));
                $sheet->row(2, function($row){
                    $row->setFontColor(setting('primary_colour'));
                    $row->setFontWeight('bold');
                    $row->setFontFamily('Arial');
                    $row->setFontSize(16);
                });
                $sheet->mergeCells('A2:C2');
                $sheet->row(3, array($otherParams['sheetSubTitle_lable'], $otherParams['sheetSubTitle_value']));
                $sheet->row(3, function($row){
                    $row->setFontColor(setting('primary_colour'));
                    $row->setFontWeight('bold');
                    $row->setFontFamily('Arial');
                    $row->setFontSize(16);
                });
                $sheet->mergeCells('B3:C3');

                $sheet->row(5, $lableArray);
                $sheet->row(5, function($row){
                    $row->setBackground(setting('primary_colour'));
                    $row->setFontColor('#ffffff');
                    $row->setFontWeight('bold');
                    $row->setFontFamily('Arial');
                    $row->setFontSize(10);
                });
                // $sheet->setHeight(5, 30);
                // $sheet->cells('A5', function($cells) {
                //     $cells->setAlignment('center');
                //     $cells->setValignment('middle');
                // });

                $row_no = 6;
                foreach ($dataArray as $data) {
                    $sheet->row($row_no, $data);
                    $row_no++;
                }

                if($otherParams['boldLastRow']){
                    $sheet->row(($row_no-1), function($row){
                        $row->setFontWeight('bold');
                    });
                }
            });
        })->export('xlsx');
    }

    private function toExcelMulti($lableArray, $dataArrays, $otherParams)
    {
        \Excel::create(str_slug($otherParams['sheetTitle']), function($excel) use($lableArray, $dataArrays, $otherParams) {
            $excel->setTitle($otherParams['sheetTitle']);
            foreach ($dataArrays as $key => $dataArray) {
                $excel->sheet($key, function($sheet) use($lableArray, $dataArray, $otherParams) {
                    $sheet->row(2, array($otherParams['sheetTitle']));
                    $sheet->row(2, function($row){
                        $row->setFontColor(setting('primary_colour'));
                        $row->setFontWeight('bold');
                        $row->setFontFamily('Arial');
                        $row->setFontSize(16);
                    });
                    $sheet->mergeCells('A2:C2');
                    // $sheet->row(3, array($otherParams['sheetSubTitle_lable'], $otherParams['sheetSubTitle_value']));
                    $sheet->row(3, array($otherParams['sheetSubTitle_lable'], $dataArray[0][0]));
                    $sheet->row(3, function($row){
                        $row->setFontColor(setting('primary_colour'));
                        $row->setFontWeight('bold');
                        $row->setFontFamily('Arial');
                        $row->setFontSize(16);
                    });
                    $sheet->mergeCells('B3:C3');

                    $sheet->row(5, $lableArray);
                    $sheet->row(5, function($row){
                        $row->setBackground(setting('primary_colour'));
                        $row->setFontColor('#ffffff');
                        $row->setFontWeight('bold');
                        $row->setFontFamily('Arial');
                        $row->setFontSize(10);
                    });
                    // $sheet->setHeight(5, 30);
                    // $sheet->cells('A5', function($cells) {
                    //     $cells->setAlignment('center');
                    //     $cells->setValignment('middle');
                    // });

                    $row_no = 6;
                    foreach ($dataArray as $data) {
                        unset($data[0]);
                        $sheet->row($row_no, $data);
                        $row_no++;
                    }

                    if($otherParams['boldLastRow']){
                        $sheet->row(($row_no-1), function($row){
                            $row->setFontWeight('bold');
                        });
                    }
                });
            }
        })->export('xlsx');
    }
    public function downloadReportRegionwise($id,$period='curr')
    {
        $region = VehicleRegions::where('id',$id)->select('name')->first();
        $lableArray = [
            'Defect Category',
            'Defect',
            $region->name
        ];
        $startDate = Carbon::now()->startOfWeek()->format('Y-m-d 00:00:00');
        $endDate = Carbon::now()->endOfWeek()->format('Y-m-d 23:59:59');
        if(strtolower($period) == "prev"){
            $startDate = Carbon::now()->addWeeks(-1)->startOfWeek()->format('Y-m-d 00:00:00');
            $endDate = Carbon::now()->addWeeks(-1)->endOfWeek()->format('Y-m-d 23:59:59');
        }
        $dataArray = [];
        $defects = Defect::select('defect_master.page_title','defect_master.defect', \DB::raw('COUNT(defects.id) cnt'))
        ->join('defect_master','defects.defect_master_id','=','defect_master.id')
        ->join('vehicles','defects.vehicle_id','=','vehicles.id')
        ->whereBetween('defects.report_datetime',[$startDate,$endDate])
        ->where('vehicles.vehicle_region_id',$id)
        ->groupBy('defect_master.page_title','defect_master.defect')
        ->get();
        $dataArray = array();
        foreach ($defects as $defect) {
            $ddata = [
                $defect->page_title,
                $defect->defect,
                $defect->cnt
            ];
            array_push($dataArray, $ddata);
        }
        $dataArray = array_unique($dataArray, SORT_REGULAR);
        if(!empty($dataArray)){
            $lastRow = 5 + ((count($dataArray) == 0)?1:count($dataArray));
            $ddata = [
                "Grand Total",
                "",
                "=SUM(C6:C".$lastRow.")"
            ];
            array_push($dataArray, $ddata);
        }
        $otherParams = [
            'sheetTitle' => "Week To Date VOR Defect Report (".$region->name.")",
            'sheetSubTitle_lable' => "WC:",
            'sheetSubTitle_value' => (strtolower($period)=="curr") ? Carbon::now()->startOfWeek()->format('d-m-Y') : Carbon::now()->addWeeks(-1)->startOfWeek()->format('d-m-Y'),
            'sheetName' => "VOR ".$region->name,
            'boldLastRow' => true
        ];
        $this->toExcel($lableArray,$dataArray,$otherParams);
    }

    public function getNameFromNumber($num) {
        $numeric = $num % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval($num / 26);
        if ($num2 > 0) {
            return $this->getNameFromNumber($num2 - 1) . $letter;
        } else {
            return $letter;
        }
    }

    public function downloadFleetCostReport($period = 'thisMonth') {

        Excel::create('Monthly Fleet Cost Report', function($excel) use ($period) {

            // Set the title
            $excel->setTitle('Fleet Cost Report');

            // Chain the setters
            $excel->setCreator('FleetMaster')
                ->setCompany('iMaster');

            // Call them separately
            $excel->setDescription('This report keeps a track of all the defects recorded within a calendar month as they accumulate.');

            $excel->sheet('Report', function($sheet) use ($period) {

                if ($period == 'thisMonth') {
                    $startDate = Carbon::now()->firstOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                } else {
                    $startOfPerviousMonth = Carbon::now()->firstOfMonth()->subDays(2);
                    $startDate = Carbon::parse($startOfPerviousMonth)->firstOfMonth();
                    $endDate = Carbon::parse($startOfPerviousMonth)->endOfMonth();
                }

                $sheet->setAutoSize(true);

                $sheet->freezeFirstColumn();
                $sheet->cell('A1', function($cell) {$cell->setValue('Fleet Costs')->setFontWeight('bold');   });
                $sheet->cell('C1', function($cell) {$cell->setValue('From:')->setFontWeight('bold');   });
                $sheet->cell('D1', function($cell) use ($startDate) {$cell->setValue($startDate->format('d M Y'))->setFontWeight('bold');   });
                $sheet->cell('E1', function($cell) {$cell->setValue('To:')->setFontWeight('bold');   });
                $sheet->cell('F1', function($cell) use ($endDate) {$cell->setValue($endDate->format('d M Y'))->setFontWeight('bold');   });
                $sheet->cell('H1', function($cell) {$cell->setValue('New')->setFontWeight('bold')->setBackground('#92d050');   });
                $sheet->cell('I1', function($cell) {$cell->setValue('Archived/Sold')->setFontWeight('bold')->setBackground('#ff0000');   });
                $sheet->cell('J1', function($cell) {$cell->setValue('Transfer')->setFontWeight('bold')->setBackground('#ffff00');   });
                //$sheet->cell('K1', function($cell) {$cell->setValue('No driver')->setFontWeight('bold')->setBackground('#0070c0');   });

                $sheet->cell('M2', function($cell) {
                    $cell->setValue('Cost')->setFontWeight('bold')->setBorder('solid');
                });

                $sheet->cells("A1:K1", function ($cells) {

                    $cells->setFont(array(
                        'family' => 'Calibri',
                        'size' => '11',
                        'bold' => true
                    ));
                });

                $sheet->mergeCells('M2:Z2');

                $sheet->cells("M2:Z2", function ($cells) {

                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '11',
                        'bold'       =>  true
                    ));
                    $cells->setFontColor("#000000");
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                    $cells->setBorder('medium', 'medium', 'medium', 'medium');
                });

                $secondHeadings = [
                    'Registration',
                    'Type',
                    'Operator License',
                    //'Nominated Driver',
                    'Ownership Status',
                    'Vehicle Status',
                    'Division',
                    'Region',
                    'Location',
                    'Date Added To Fleet',
                    'Location From',
                    'Location To',
                    '',
                    'Hire Cost',
                    'Management Cost',
                    'Depreciation Cost',
                    'Vehicle Tax',
                    'Insurance Cost',
                    'Telematics Cost',
                    'Manual Cost Adj',
                    'Fuel',
                    'Oil',
                    'AdBlue',
                    'Screen Wash',
                    'Fleet Livery',
                    'Defects',
                    'Total',
                    'Transfer'
                ];


                $sheet->row(3,$secondHeadings);
                $sheet->cells("A3:AA3", function ($cells) {
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '11',
                        'bold'       =>  true
                    ));
                });

                $startRow = 4;
                $startColumn = 'A';
                $lastRow='Z';
                $data = $this->getFleetCostDataForReport($period);

                $sheet->fromArray($data['data'], null, $startColumn.$startRow, true, false);

                $TotalRow = count($data['data'])+$startRow-1;

                if (count($data['blue'])) {
                    foreach ($data['blue'] as $key) {
                        $rowNumber = $key+($startRow);
                        $sheet->cells('E'.$rowNumber.":E".$rowNumber, function ($cells) {
                            $cells->setBackground("#0070c0");
                            $cells->setFontColor('#ffffff');
                        });
                    }
                }

                if (count($data['green'])) {
                    foreach ($data['green'] as $key) {
                        $rowNumber = $key+($startRow);
                        $sheet->cells('I'.$rowNumber.":I".$rowNumber, function ($cells) {
                            $cells->setBackground("#92d050");
                            //$cells->setFontColor('#ffffff');
                        });
                    }
                }

                if (count($data['yellow'])) {
                    foreach ($data['yellow'] as $key) {
                        $rowNumber = $key+($startRow);
                        $sheet->cells('AA'.$rowNumber.":AA".$rowNumber, function ($cells) {
                            $cells->setBackground("#ffff00");
                        });
                    }
                }
                if (count($data['red'])) {
                    foreach ($data['red'] as $key) {
                        $rowNumber = $key+($startRow);
                        $sheet->cells('E'.$rowNumber.":E".$rowNumber, function ($cells) {
                            $cells->setBackground("#ff0000");
                        });
                    }
                }

                $rowNumber = $startRow;
                $endNumber = $startRow + count($data['data'])-1;
                $sheet->cells('M'.$rowNumber.":Z".$endNumber, function ($cells) {
                    $cells->setAlignment('right');
                });

                $sheet->cells('Z'.$rowNumber.":Z".$endNumber, function ($cells) {
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '11',
                        'bold'       =>  true
                    ));
                });

                $sheet->getStyle('M'.$rowNumber.":Z".$endNumber)->getNumberFormat()->setFormatCode('[$£-809]#,##0.00;[RED]-[$£-809]#,##0.00');

                $sheet->cells('A'.$TotalRow.":Z".$TotalRow, function ($cells) {
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '11',
                        'bold'       =>  true
                    ));
                });

            });

        })->download('xlsx');
    }

    private function  getFleetCostDataForReport($type) {
        if ($type == 'thisMonth') {
            $startDate = Carbon::now()->firstOfMonth();
            $endDate = Carbon::now()->endOfMonth();
            $dateObj = Carbon::now();
        } else {
            $startOfPerviousMonth = Carbon::now()->firstOfMonth()->subDays(2);
            $startDate = Carbon::parse($startOfPerviousMonth)->firstOfMonth();
            $endDate = Carbon::parse($startOfPerviousMonth)->endOfMonth();
            $dateObj = Carbon::now()->firstOfMonth()->subDays(2);
        }

        $date = $dateObj->format('Y-m-d');

        $ignoreIds = [];

        $ignoreEntries = VehicleAssignment::selectRaw('vehicle_id,from_date,COUNT(vehicle_assignment.id), GROUP_CONCAT(vehicle_assignment.id ORDER BY vehicle_assignment.id DESC) as ids')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($query) use ($startDate, $endDate) {
                    $query->whereDate('vehicle_assignment.from_date', '>=', $startDate->format('Y-m-d'));
                    $query->whereDate('vehicle_assignment.from_date', '<=', $endDate->format('Y-m-d'));
                });
                $query->orWhere(function ($query) use ($startDate, $endDate) {
                    $query->whereDate('vehicle_assignment.to_date', '>=', $startDate->format('Y-m-d'));
                    $query->whereDate('vehicle_assignment.to_date', '<=', $endDate->format('Y-m-d'));
                });
            })
            ->groupBy('vehicle_id','from_date')
            ->havingRaw('COUNT(vehicle_assignment.id)>1')
            ->orderBy('id','DESC')
            ->get();

        foreach ($ignoreEntries as $entry) {
            $ids = explode(",",$entry->ids);
            unset($ids[0]);
            $ignoreIds = array_merge($ignoreIds,$ids);
        }

        $vehicles = Vehicle::withTrashed()->with('type','division','region','location','nominatedDriver','defects')
            ->leftJoin('vehicle_assignment', function($join) use ($startDate,$endDate){

                $join->on('vehicle_assignment.vehicle_id','=','vehicles.id');
                $join->on(function ($query) use ($startDate,$endDate){
                    $query->on(function ($query) use ($startDate, $endDate) {
                        $query->where('vehicle_assignment.from_date', '>=', $startDate->format('Y-m-d'));
                        $query->where('vehicle_assignment.from_date', '<=', $endDate->format('Y-m-d'));

                    });
                    $query->orOn(function ($query) use ($startDate, $endDate) {
                        $query->where('vehicle_assignment.to_date', '>=', $startDate->format('Y-m-d'));
                        $query->where('vehicle_assignment.to_date', '<=', $endDate->format('Y-m-d'));
                    });
                   // $query->orOn('vehicle_assignment.id',null);
                });

            })
            ->leftJoin('vehicle_locations','vehicle_locations.id','=','vehicle_assignment.vehicle_location_id')
            ->leftJoin('vehicle_regions','vehicle_regions.id','=','vehicle_assignment.vehicle_region_id')
            ->leftJoin('vehicle_divisions','vehicle_divisions.id','=','vehicle_assignment.vehicle_division_id')
            ->selectRaw('
            vehicle_assignment.*,
            vehicles.*,
            vehicles.id as vehId,
            vehicle_assignment.id as vehAssId,
            vehicle_assignment.vehicle_division_id as vdid,
            vehicle_assignment.vehicle_region_id as vrid,
            vehicle_assignment.vehicle_location_id as vlid,
            vehicle_locations.name,
            vehicle_regions.name as region_name,
            vehicle_divisions.name as division_name,
            vehicles.id as id'
            );


        //->whereRaw('((DATE(vehicle_assignment.from_date) <= DATE(vehicles.archived_date) AND vehicles.archived_date IS NOT NULL AND vehicle_assignment.id IS NOT NULL) OR (vehicles.archived_date IS NULL OR vehicle_assignment.id IS NULL))');
        //->whereRaw('(DATE(vehicle_assignment.from_date) <= DATE(vehicles.archived_date) AND vehicle_assignment.id IS NOT NULL) OR vehicle_assignment.id IS NULL')
        //->whereRaw('(archived_date >= "'.$startDate->format('Y-m-d').'" OR archived_date IS NULL)');
        //dd($startDate->format('Y-m-d'));
        //->whereRaw('DATE(dt_added_to_fleet) <= "'.$startDate->format('Y-m-d').'"');

        $vehicles->where(function ($query) use ($startDate,$endDate){
           $query->where('archived_date','>=',$endDate->format('Y-m-d'));
           $query->orWhere('archived_date','>=',$startDate->format('Y-m-d'));
           $query->orWhere('archived_date','=',null);
        });

        if(count($ignoreIds) > 0) {
            $vehicles->where(function ($query) use ($ignoreIds) {
                $query->whereNotIn('vehicle_assignment.id',$ignoreIds);
                $query->orWhere('vehicle_assignment.id', null);
            });
        }

        $vehicles = $vehicles->orderByRaw('vehicle_assignment.vehicle_id,`vehicle_assignment`.`from_date`,`vehicle_assignment`.`to_date`  ASC')
            ->havingRaw('dt_added_to_fleet <= "'.$endDate->format('Y-m-d').'"')
            ->get();


        $finalArray = [];
        $finalArray['data'] = [];
        $finalArray['blue'] = [];
        $finalArray['green'] = [];
        $finalArray['yellow'] = [];
        $finalArray['red'] = [];


        $commonHelper = new Common();
        $vehicles = $vehicles->groupBy('registration');

        $previosMonthEntries = Vehicle::leftJoin('vehicle_assignment','vehicle_assignment.vehicle_id','=','vehicles.id')
            ->leftJoin('vehicle_locations','vehicle_locations.id','=','vehicle_assignment.vehicle_location_id')
            ->whereRaw('MONTH(to_date) = '.$dateObj->subMonth(1)->format('m'))
            ->orWhereRaw('MONTH(from_date) = '.$dateObj->subMonth(1)->format('m'))
            ->orderBy('vehicle_assignment.to_date','DESC')
            ->select('vehicles.id','vehicle_assignment.vehicle_location_id','vehicle_assignment.from_date','vehicle_assignment.to_date','vehicle_locations.name')
            ->get();

        $archiveHistory = DB::select('SELECT m1.*
                                    FROM vehicle_archive_history m1 LEFT JOIN vehicle_archive_history m2
                                     ON (m1.vehicle_id = m2.vehicle_id AND m1.id < m2.id)
                                    WHERE m2.id IS NULL');

        $archiveHistory = collect($archiveHistory)->groupBy('vehicle_id')->toArray();

        $previosMonthEntries = $previosMonthEntries->groupBy('id')->toArray();
        $i = 0;
        $previousToDate = '';
        $previousFromDate = '';
        $previousVehicleId = '';
        $fleetCost = Settings::where('key', 'fleet_cost_area_detail')->first();
        $fleetCostJson = $fleetCost->value;
        $fleetCostData = json_decode($fleetCostJson, true);

        //Total cost row
        $totalRow['registration'] = 'Totals';
        $totalRow['type'] = '';
        $totalRow['operator_license'] = '';
        $totalRow['ownership_status'] = '';
        $totalRow['vehicle_status'] = '';
        $totalRow['division'] =  '';
        $totalRow['region'] = '';
        $totalRow['location'] =  '';
        $totalRow['date_added_on_fleet'] = '';
        $totalRow['location_from'] = '';
        $totalRow['location_to'] = '';
        $totalRow['blank'] = '';
        $totalRow['lease_cost'] = 0;
        $totalRow['maintenance_cost'] = 0;
        $totalRow['depreciation_cost'] = 0;
        $totalRow['vehicle_tax'] = 0;
        $totalRow['insurance_cost'] = 0;
        $totalRow['telematics_cost'] = 0;
        $totalRow['manual_cost_adjustment'] = 0;
        $totalRow['fuel'] = 0;
        $totalRow['oil'] = 0;
        $totalRow['adBlue'] = 0;
        $totalRow['screen_wash_use'] = 0;
        $totalRow['fleet_livery_wash'] = 0;
        $totalRow['defects'] = 0;
        $totalRow['total'] = 0;

        foreach ($vehicles as $registration => $group) {
            $group = $group->sortByDesc('vehAssId');

            foreach ($group as $key => $vehicle) {

                $vehicleId = $vehicle->vehId;
                $vehicleArchiveHistory = VehicleArchiveHistory::where('vehicle_id',$vehicleId)->orderBy('id','DESC')->first();
                $single = array();

                $originalFromDate = $vehicle->from_date;
                $originalToDate = $vehicle->to_date;
                $single['registration'] = $vehicle->registration;
                $single['type'] = $vehicle->type->vehicle_type;
                $single['operator_license'] = $vehicle->type->vehicle_type == 'HGV' ? $vehicle->operator_license : 'N/A';
                //$single['nominated_driver'] = isset($vehicle->nominatedDriver->email) ? $vehicle->nominatedDriver->first_name . " " . $vehicle->nominatedDriver->last_name : 'Unassigned';

                /* if ($single['nominated_driver'] == 'Unassigned') {
                     array_push($finalArray['blue'], $i);
                 }*/

                $single['ownership_status'] = $vehicle->staus_owned_leased;
                $single['vehicle_status'] = $vehicle->status;
              
                $single['division'] =  $vehicle->division_name != null ? $vehicle->division_name : (isset($vehicle->division->name) ? $vehicle->division->name : '');
                $single['region'] = $vehicle->region_name != null ? $vehicle->region_name : (isset($vehicle->region->name) ? $vehicle->region->name : '');
                $single['location'] =  $vehicle->name != null ? $vehicle->name : (isset($vehicle->location->name) ? $vehicle->location->name : '');
                $single['date_added_on_fleet'] = $vehicle->dt_added_to_fleet;

                $dateAddedOnFleet = $vehicle->dt_added_to_fleet != null ? Carbon::parse($vehicle->dt_added_to_fleet) : null;
                if ($vehicle->vlid != null) {

                    if (count($group) == 1 && isset($previosMonthEntries[$vehicle->vehId])) {
                        $single['location_from'] = $previosMonthEntries[$vehicle->vehId][0]['name'];
                        $single['location_to'] = $vehicle->name;
                    } else {
                        if ($key == 0) {
                            $single['location_from'] = $vehicle->name;
                            if ($vehicle->to_date != null) {
                                $single['location_to'] = isset($group[$key + 1]) ? $group[$key + 1]->name : 'N/A';
                                $vehicle->from_date = isset($group[$key + 1]) ?  $group[$key + 1]->from_date :  $group[$key]->from_date;
                            } else {
                                $single['location_to'] = $vehicle->name;
                                $single['location_from'] = (isset($group[$key - 1]->name) && $group[$key - 1]->name!="") ? $group[$key - 1]->name : 'N/A';
                            }

                        } else {
                            $single['location_from'] = (isset($group[$key - 1]->name) && $group[$key - 1]->name!="") ? $group[$key - 1]->name : 'N/A';
                            $single['location_to'] = $vehicle->name;
                        }
                    }
                } else {
                    $single['location_from'] = 'N/A';
                    $single['location_to'] = 'N/A';
                }

                $single['blank'] = '';

                if ($previousVehicleId == $vehicleId && $previousToDate == $vehicle->to_date && $previousFromDate == $vehicle->from_date) {
                    $single['lease_cost'] = 'N/A';
                    $single['maintenance_cost'] = 'N/A';
                    $single['depreciation_cost'] = 'N/A';
                    $single['vehicle_tax'] = 'N/A';
                    $single['insurance_cost'] = 'N/A';
                    $single['telematics_cost'] = 'N/A';
                    $single['manual_cost_adjustment'] = 'N/A';
                    $single['fuel'] = 'N/A';
                    $single['oil'] = 'N/A';
                    $single['adBlue'] = 'N/A';
                    $single['screen_wash_use'] = 'N/A';
                    $single['fleet_livery_wash'] = 'N/A';
                    $single['defects'] = 'N/A';
                    $single['total'] = 'N/A';
                    $single['transfer'] = $vehicle->from_date != null ? Carbon::parse($vehicle->from_date)->format('d M Y') : '';
                    if ($single['transfer'] != '') {
                        // array_push($finalArray['yellow'], $i);
                    }
                } else {

                    if($dateAddedOnFleet!= null && $dateAddedOnFleet->format('m-Y') == Carbon::parse($date)->format('m-Y')) {
                        array_push($finalArray['green'], $i);
                    }

                    if (strpos($vehicle->status, 'Archived') === false) {

                    } else {
                        array_push($finalArray['red'], $i);
                        if (isset($archiveHistory[$vehicleId])) {
                            if(Carbon::parse($vehicle->to_date)->gt(Carbon::parse($archiveHistory[$vehicleId][0]->event_date_time))) {
                                $vehicle->to_date = $archiveHistory[$vehicleId][0]->event_date_time;
                                $originalToDate = Carbon::parse($vehicle->to_date)->format('Y-m-d');
                            }
                        }
                    }

                    //$lease = $commonHelper->calcMonthlyCurrentData($vehicle->lease_cost, $date, $vehicleId, $vehicleArchiveHistory,false,'lease');
                    $single['lease_cost'] = $this->fleetCostReportCost($vehicle->lease_cost,'currentCost',$originalFromDate,$originalToDate,$startDate,$vehicleId,$vehicleArchiveHistory);

                    //$maintenance = $commonHelper->calcMonthlyCurrentData($vehicle->maintenance_cost, $date, $vehicleId, $vehicleArchiveHistory);
                    $single['maintenance_cost'] =  $this->fleetCostReportCost($vehicle->maintenance_cost,'currentCost',$originalFromDate,$originalToDate,$startDate,$vehicleId,$vehicleArchiveHistory,null,null,1);
                    //$depreciation = $commonHelper->calcMonthlyCurrentData($vehicle->monthly_depreciation_cost, $date, $vehicleId, $vehicleArchiveHistory);
                    $single['depreciation_cost'] = $this->fleetCostReportCost($vehicle->monthly_depreciation_cost,'currentCost',$originalFromDate,$originalToDate,$startDate,$vehicleId,$vehicleArchiveHistory);

                    //$taxCost = $commonHelper->calcMonthlyCurrentData($vehicle->type->vehicle_tax, $date,$vehicleId,$vehicleArchiveHistory,$vehicle->dt_added_to_fleet,'N/A','N/A',null,'vehicle_tax');

                    $tax = $this->fleetCostReportCost($vehicle->type->vehicle_tax,'currentCost',$originalFromDate,$originalToDate,$startDate,$vehicleId,$vehicleArchiveHistory,$vehicle->dt_added_to_fleet);

                    $single['vehicle_tax'] = $tax;

                    $insuranceValueJsonValue = '';
                    // if(isset($fleetCostData['annual_insurance_cost']) && $vehicle->is_insurance_cost_override != 1) {
                    //     $insuranceValueJsonValue = json_encode($fleetCostData['annual_insurance_cost']);
                    // } else if($vehicle->is_insurance_cost_override == 1 && $vehicle->insurance_cost == ''){
                    //     $insuranceValueJsonValue = json_encode($fleetCostData['annual_insurance_cost']);
                    // } else if($vehicle->is_insurance_cost_override == 1 && $vehicle->insurance_cost != ''){
                    //     $insuranceValueJsonValue = $vehicle->insurance_cost;
                    // } else {
                    //     if(isset($fleetCostData['annual_insurance_cost'])){
                    //         $insuranceValueJsonValue = json_encode($fleetCostData['annual_insurance_cost']);
                    //     }
                    // }
                    if(isset($vehicle->type->annual_insurance_cost) && $vehicle->is_insurance_cost_override != 1) {
                        $insuranceValueJsonValue = $vehicle->type->annual_insurance_cost;
                    } else if($vehicle->is_insurance_cost_override == 1 && $vehicle->insurance_cost == ''){
                        $insuranceValueJsonValue = $vehicle->type->annual_insurance_cost;
                    } else if($vehicle->is_insurance_cost_override == 1 && $vehicle->insurance_cost != ''){
                        $insuranceValueJsonValue = $vehicle->insurance_cost;
                    } else {
                        if(isset($vehicle->type->annual_insurance_cost)){
                            $insuranceValueJsonValue = $vehicle->type->annual_insurance_cost;
                        }
                    }
                    //$insurance = $commonHelper->calcMonthlyCurrentData($insuranceValueJsonValue, $date, $vehicleId, $vehicleArchiveHistory,$vehicle->dt_added_to_fleet,$vehicle->is_insurance_cost_override);
                    $single['insurance_cost'] = $this->fleetCostReportCost($insuranceValueJsonValue,'currentCost',$originalFromDate,$originalToDate,$startDate,$vehicleId,$vehicleArchiveHistory,$vehicle->dt_added_to_fleet,$vehicle->is_insurance_cost_override);

                    // TelematicsInsurance Use
                    if ($vehicle->is_telematics_enabled == 1) {
                        $telematicsJsonValue = '';
                        if (isset($fleetCostData['telematics_insurance_cost']) && $vehicle->is_telematics_cost_override != 1) {
                            $telematicsJsonValue = json_encode($fleetCostData['telematics_insurance_cost']);
                        } else if ($vehicle->is_telematics_cost_override == 1 && $vehicle->telematics_cost == '') {
                            $telematicsJsonValue = json_encode($fleetCostData['telematics_insurance_cost']);
                        } else if ($vehicle->is_telematics_cost_override == 1 && $vehicle->telematics_cost != '') {
                            $telematicsJsonValue = $vehicle->telematics_cost;
                        } else {
                            if (isset($fleetCostData['telematics_insurance_cost'])) {
                                $telematicsJsonValue = json_encode($fleetCostData['telematics_insurance_cost']);
                            }
                        }
                        //$telematics = $commonHelper->calcMonthlyCurrentData($telematicsJsonValue, $date, $vehicleId, $vehicleArchiveHistory,$vehicle->dt_added_to_fleet,'N/A',$vehicle->is_telematics_cost_override);
                        //$single['telematics_cost'] = $this->fleetCostReportCost($telematics, 'currentCost, $originalFromDate, $originalToDate, $startDate,$vehicleId,'telematics_cost',$vehicle->dt_added_to_fleet,'N/A',$vehicle->is_telematics_cost_override);
                        $single['telematics_cost'] = $this->fleetCostReportCost($telematicsJsonValue,'currentCost',$originalFromDate,$originalToDate,$startDate,$vehicleId,$vehicleArchiveHistory,$vehicle->dt_added_to_fleet,$vehicle->is_telematics_cost_override);
                    } else {
                        $single['telematics_cost'] = 'N/A';
                    }

                    $manualCostAdjustment = json_decode($vehicle->manual_cost_adjustment, true);
                    if (isset($manualCostAdjustment)) {
                        //$manualCostAdjustment = $commonHelper->calcCurrentMonthBasedOnPeriod($manualCostAdjustment, $date, $vehicleId, $vehicleArchiveHistory,'manual_cost_adjustment');
                        //$single['manual_cost_adjustment'] = $this->calculateCost($manualCostAdjustment,'currentCost',$originalFromDate,$originalToDate,$startDate,$vehicleId,'manual_cost_adjustment');
                        $single['manual_cost_adjustment'] = $this->fleetCostReportCost($manualCostAdjustment,'manualCost',$originalFromDate,$originalToDate,$startDate,$vehicleId,$vehicleArchiveHistory);
                    } else {
                        $single['manual_cost_adjustment'] = '0.00';
                    }

                    $fuelCost = json_decode($vehicle->fuel_use, true);
                    if (isset($fuelCost)) {
                        //$fuelCost = $commonHelper->calcCurrentMonthBasedOnPeriod($fuelCost, $date, $vehicleId, $vehicleArchiveHistory);
                        //$single['fuel'] = $this->calculateCost($fuelCost,'manualCost',$originalFromDate,$originalToDate,$startDate,$vehicleId,'fuel');
                        $single['fuel'] = $this->fleetCostReportCost($fuelCost,'manualCost',$originalFromDate,$originalToDate,$startDate,$vehicleId,$vehicleArchiveHistory);
                    } else {
                        $single['fuel'] = (string)'0.00';
                    }

                    $oilCost = json_decode($vehicle->oil_use, true);

                    if (isset($oilCost)) {
                        //$oilCost = $commonHelper->calcCurrentMonthBasedOnPeriod($oilCost, $date, $vehicleId, $vehicleArchiveHistory);
                        //$single['oil'] = $this->calculateCost($oilCost,'manualCost',$originalFromDate,$originalToDate,$startDate);
                        $single['oil'] = $this->fleetCostReportCost($oilCost,'manualCost',$originalFromDate,$originalToDate,$startDate,$vehicleId,$vehicleArchiveHistory);
                    } else {
                        $single['oil'] = (string)'0.00';
                    }

                    $adBlueCost = json_decode($vehicle->adblue_use, true);

                    if (isset($adBlueCost)) {
                        //$adBlue = $commonHelper->calcCurrentMonthBasedOnPeriod($adBlueCost, $date, $vehicleId, $vehicleArchiveHistory);
                        $single['adBlue'] = $this->fleetCostReportCost($adBlueCost,'manualCost',$originalFromDate,$originalToDate,$startDate,$vehicleId,$vehicleArchiveHistory);
                    } else {
                        $single['adBlue'] = (string)'0.00';
                    }

                    $screenWashCost = json_decode($vehicle->screen_wash_use, true);

                    if (isset($screenWashCost)) {
                        //$screenUse = $commonHelper->calcCurrentMonthBasedOnPeriod(json_decode($vehicle->screen_wash_use, true), $date, $vehicleId, $vehicleArchiveHistory);
                        $single['screen_wash_use'] = $this->fleetCostReportCost($screenWashCost,'manualCost',$originalFromDate,$originalToDate,$startDate,$vehicleId,$vehicleArchiveHistory);
                    } else {
                        $single['screen_wash_use'] = (string)'0.00';
                    }

                    $liveryUseCost = json_decode($vehicle->fleet_livery_wash, true);

                    if (isset($liveryUseCost)) {
                        //$liveryUse = $commonHelper->calcCurrentMonthBasedOnPeriod($liveryUseCost, $date, $vehicleId, $vehicleArchiveHistory);
                        $single['fleet_livery_wash'] = $this->fleetCostReportCost($liveryUseCost,'manualCost',$originalFromDate,$originalToDate,$startDate,$vehicleId,$vehicleArchiveHistory);
                    } else {
                        $single['fleet_livery_wash'] = (string)'0.00';
                    }

                    /*$defects = $vehicle->defects;
                    $defectDamageCostValue = 0;
                    $defectDamageArray = [];
                    $defectDamageCompareDate = '';
                    $actualDefectCost = 0;
                    foreach ($defects as $defect) {
                        $defectFromDate = Carbon::parse($defect['report_datetime'])->format('M Y');
                        $defectDamageArrayFromDate = Carbon::parse($defect['report_datetime'])->format('m-Y');
                        $defectCostValue = $defect->actual_defect_cost_value ? $defect->actual_defect_cost_value : 0;

                        if( Carbon::parse($startDate)->format('M Y') == $defectFromDate) {
                            $defectDamageCostValue += $defectCostValue;
                        }

                        if(!isset($defectDamageArray[$defectDamageArrayFromDate])) {
                            $defectDamageArray[$defectDamageArrayFromDate] = 0;
                        }
                        $defectDamageArray[$defectDamageArrayFromDate] += $defectCostValue;
                    }
                    $totalDays = Carbon::parse($startDate)->daysInMonth;

                    $defectsCostArray = [
                        "currentCost" => $defectDamageCostValue,
                        "currentDate" => "",
                        "currentDateValue" => "",
                        "totalDaysCount" => $totalDays,
                        "currentMonthDates" => []
                    ];

                    $single['defects'] = $this->calculateCost($defectsCostArray,'manualCost',$originalFromDate,$originalToDate,$startDate);*/
                    $defectCost = $this->getDefectCost($vehicleId,$originalFromDate,$originalToDate,$startDate);
                    $single['defects'] = $defectCost == 0 ? '0.00' : $defectCost;

                    $single['total'] = 'N/A';
                    $single['transfer'] = $vehicle->from_date != null && Carbon::parse($vehicle->from_date)->format('m') ==  Carbon::parse($date)->format('m') ? Carbon::parse($vehicle->from_date)->format('d M Y') : '';
                    if ($single['transfer'] != '') {
                        array_push($finalArray['yellow'], $i);
                    }

                    $single['lease_cost'] = $this->currencyFormat($single['lease_cost']);
                    $single['maintenance_cost'] = $this->currencyFormat($single['maintenance_cost']);
                    $single['depreciation_cost'] = $this->currencyFormat($single['depreciation_cost']);
                    $single['vehicle_tax'] = $this->currencyFormat($single['vehicle_tax']);
                    $single['insurance_cost'] = $this->currencyFormat($single['insurance_cost']);
                    $single['telematics_cost'] = $this->currencyFormat($single['telematics_cost']);
                    $single['manual_cost_adjustment'] = $this->currencyFormat($single['manual_cost_adjustment']);
                    $single['fuel'] = $this->currencyFormat($single['fuel']);
                    $single['oil'] = $this->currencyFormat($single['oil']);
                    $single['adBlue'] = $this->currencyFormat($single['adBlue']);
                    $single['screen_wash_use'] = $this->currencyFormat($single['screen_wash_use']);
                    $single['fleet_livery_wash'] = $this->currencyFormat($single['fleet_livery_wash']);
                    $single['defects'] = $this->currencyFormat($single['defects']);
                    $rowNumber = count($finalArray['data'])+4;
                    $single['total'] = '=SUM(M'.$rowNumber.':Y'.$rowNumber.')';

                    array_push($finalArray['data'], $single);
                    $i++;
                }

                $previousVehicleId = $vehicleId;
                $previousToDate = $vehicle->to_date;
                $previousFromDate = $vehicle->from_date;

            }

        }

        $blank = array();
        array_push($finalArray['data'],$blank);

        $endRow = count($finalArray['data'])+3;
        $totalRow['lease_cost'] = '=SUM(M4:M'.$endRow.')';
        $totalRow['maintenance_cost'] = '=SUM(N4:N'.$endRow.')';
        $totalRow['depreciation_cost'] = '=SUM(O4:O'.$endRow.')';
        $totalRow['vehicle_tax'] = '=SUM(P4:P'.$endRow.')';
        $totalRow['insurance_cost'] = '=SUM(Q4:Q'.$endRow.')';
        $totalRow['telematics_cost'] = '=SUM(R4:R'.$endRow.')';
        $totalRow['manual_cost_adjustment'] = '=SUM(S4:S'.$endRow.')';
        $totalRow['fuel'] = '=SUM(T4:T'.$endRow.')';
        $totalRow['oil'] = '=SUM(U4:U'.$endRow.')';
        $totalRow['adBlue'] = '=SUM(V4:V'.$endRow.')';
        $totalRow['screen_wash_use'] = '=SUM(W4:W'.$endRow.')';
        $totalRow['fleet_livery_wash'] = '=SUM(X4:X'.$endRow.')';
        $totalRow['defects'] = '=SUM(Y4:Y'.$endRow.')';
        $totalRow['total'] = '=SUM(Z4:Z'.$endRow.')';

        array_push($finalArray['data'],$totalRow);

        return $finalArray;
    }

    public function getDefectCost($vehicleId,$originalStartDate,$originalEndDate,$startDate) {
        if ($originalStartDate == null) {
            $originalStartDate = Carbon::parse($startDate)->firstOfMonth();
        }

        if ($originalEndDate == null) {
            $originalEndDate = Carbon::parse($startDate)->endOfMonth();
        }

        $defectCost = Defect::whereDate('report_datetime','>=',$originalStartDate)
            ->whereDate('report_datetime','<=',$originalEndDate)
            ->where('vehicle_id',$vehicleId)->where('status','Resolved')
            ->sum('actual_defect_cost_value');

        return $defectCost;
    }

    private function currencyFormat($cost) {
        return (float)$cost;
    }

    private function calculateCost($costArray,$vlid,$from_date,$to_date,$startDate,$vehicleId = 0,$type ='',$vehicleDtAddedToFleet='N/A',$isInsuranceCostOverride = 'N/A',$isTelematicsCostOverride='N/A') {

        if ((float)$costArray['currentCost'] == 0) {
            return 0;
        }

        if ($vlid == null && $from_date == null && count($costArray['currentMonthDates']) < 1) {
            return $costArray['currentCost'];
        }

        if ($from_date == null) {
            $fromDate = Carbon::parse($startDate)->firstOfMonth();
        } else {

            $fromDate = Carbon::parse($from_date);

            if ($fromDate->lt(Carbon::parse($startDate)->firstOfMonth())) {
                $fromDate = Carbon::parse($startDate)->firstOfMonth();
            }

        }

        if ($to_date == null) {
            $toDate = Carbon::parse($startDate)->lastOfMonth();
        } else {
            $toDate =  Carbon::parse($to_date);
            if ($toDate->gt(Carbon::parse($startDate)->lastOfMonth())) {
                $toDate = Carbon::parse($startDate)->lastOfMonth();
            }
        }


        $days = $fromDate->diffInDays($toDate) + 1;



        if ((float)$days == (float)$costArray['totalDaysCount']) {
            return $costArray['currentCost'];
        }

        $finalDates = [];

        if (count($costArray['currentMonthDates']) > 0 ) {

            $isFall = 0;
            $assFromDate = new \DateTime($fromDate->format('Y-m-d'));
            $assToDate = new \DateTime($toDate->format('Y-m-d'));
            foreach ($costArray['currentMonthDates'] as $date) {

                $isFall = 0;
                $jsonFromDate = new \DateTime($date['start_date']);
                $jsonToDate = new \DateTime($date['end_date']);
                //dump(1 <= 5 && 10 >= 5 OR 1 <= 15 && 10 >= 15);
                if ( ($jsonFromDate <= $assFromDate && $jsonToDate >= $assFromDate) OR ($jsonFromDate <= $assToDate && $jsonToDate >= $assToDate) ) {
                    $isFall = 1;
                }

                if ($jsonFromDate >= $assFromDate && $jsonFromDate <=$assToDate &&  $jsonToDate >= $assFromDate && $jsonToDate <= $assToDate) {
                    $isFall = 1;
                }

                if ($isFall == 1) {
                    $single = array(
                        'start_date' => $date['start_date'],
                        'end_date' => $date['end_date']
                    );
                    array_push($finalDates,$single);
                }
            }

            //$dates = collect($costArray['currentMonthDates']);
            $dates = collect($costArray['currentMonthDates']);
            $costFromDate = Carbon::parse($dates->min('start_date'));
            $costToDate = Carbon::parse($dates->max('end_date'));

            if ($costFromDate->lt(Carbon::parse($startDate)->firstOfMonth())){
                $costFromDate = Carbon::parse($startDate)->firstOfMonth();
            }

            if ($costToDate->gt(Carbon::parse($startDate)->endOfMonth())) {
                $costToDate = Carbon::parse($startDate)->endOfMonth();
            }

            if ($fromDate->lte($costFromDate) && $toDate->gte($costToDate)) {
                return $costArray['currentCost'];
            } else {

                if ($fromDate->lt($costFromDate)) {
                    $fromDate = $costFromDate;
                }

                if ($costToDate->lt($toDate)) {
                    $toDate = $costToDate;
                }

                $checkDateAddedToFleet = 0;

                if ($type == 'tax' && $vehicleDtAddedToFleet != null) {
                    $checkDateAddedToFleet = 1;
                }

                if ($isInsuranceCostOverride === 0 && $vehicleDtAddedToFleet != null) {
                    $checkDateAddedToFleet = 1;
                }

                if ($isTelematicsCostOverride === 0 && $vehicleDtAddedToFleet != null) {
                    $checkDateAddedToFleet = 1;
                }

                if ($checkDateAddedToFleet == 1) {
                    $vehicleDtAddedToFleetObject = Carbon::parse($vehicleDtAddedToFleet);
                    if ($fromDate->lt($vehicleDtAddedToFleetObject)) {
                        $fromDate = $vehicleDtAddedToFleetObject;
                    }
                }

                $days = $fromDate->diffInDays($toDate)+1;

            }

            $ranges = $finalDates;

            $makeArray = array();
            foreach ($ranges as $entry)
            {
                $entry = (object)$entry;
                $temp = \Spatie\Period\Period::make(Carbon::parse($entry->start_date), Carbon::parse($entry->end_date),\Spatie\Period\Precision::DAY);
                array_push($makeArray,$temp);
            }

            $collection = new \Spatie\Period\PeriodCollection(...$makeArray);
            //$boundaries = $collection->boundaries();
            $gaps = $collection->gaps();
            $gapInDays=  0;
            foreach ($gaps as $gap)
            {
                $startTime = Carbon::parse($gap->getStart()->format('Y-m-d'));
                $endTime = Carbon::parse($gap->getEnd()->format('Y-m-d'));
                $gapInDays += $startTime->diffInDays($endTime);
                $gapInDays += 1;
            }

            $days = $days - $gapInDays;
        }

        if ($toDate->lt($fromDate)) {
            $cost = 0;
        } else {
            $cost = getVehicleCostForDays($costArray['currentCost'],$days,$costArray['totalDaysCount']);
        }
        return $cost;
    }

    private function fleetCostReportCost($costArray,$type = 'currentCost',$from_date,$to_date,$startDate,$vehicleId = 0,$vehicleArchiveHistory = null,$vehicleDtAddedToFleet = null, $isOverrideCost = null,$debug = 0) {

            if ($type == 'currentCost') {
                $costArray = json_decode($costArray, true);
            }


            $totalDaysInMonth = Carbon::parse($startDate)->daysInMonth;
            $cost = 0;

            if($from_date == null) {
                $fromDateObj = Carbon::parse($startDate)->firstOfMonth();
            } else {
                $fromDateObj = Carbon::parse($from_date);
            }

            if ($fromDateObj->lt(Carbon::parse($startDate)->firstOfMonth())) {
                $fromDateObj = Carbon::parse($startDate)->firstOfMonth();
            }

            $currentMonthYear = Carbon::parse($startDate)->format('m-Y');

            if ($to_date == null ) {
                $toDateObj = Carbon::parse($startDate)->endOfMonth();
            } else {
                $toDateObj = Carbon::parse($to_date);
            }

            if ($toDateObj->gt(Carbon::parse($startDate)->endOfMonth())) {
                $toDateObj = Carbon::parse($startDate)->endOfMonth();
            }


            if ($vehicleArchiveHistory != null && $vehicleArchiveHistory->event == 'Archived') {
                $archiveDate = Carbon::parse($vehicleArchiveHistory->event_date_time)->format('Y-m-d');
                $archiveDateObj = Carbon::parse($archiveDate);

                if ($archiveDateObj->lt($fromDateObj)) {
                    return (float) 0;
                }

                if ($toDateObj->gt($archiveDateObj)) {
                    $toDateObj = Carbon::parse($archiveDate);
                }

                if ($toDateObj->gt(Carbon::parse($startDate)->endOfMonth())) {
                    $toDateObj = Carbon::parse($startDate)->endOfMonth();
                }
            }

            $checkDateAddedToFleet = 0;

            if (isset($costArray[0]['json_type']) && $costArray[0]['json_type'] == 'monthlyVehicleTax' && $vehicleDtAddedToFleet != null) {
                $checkDateAddedToFleet = 1;
            }

            if ($isOverrideCost === 0 && $vehicleDtAddedToFleet != null) {
                $checkDateAddedToFleet = 1;
            }

            if ($checkDateAddedToFleet == 1) {
                $vehicleDtAddedToFleetObject = Carbon::parse($vehicleDtAddedToFleet);

                if ($vehicleDtAddedToFleetObject->gt($fromDateObj)) {
                    $fromDateObj =  Carbon::parse($vehicleDtAddedToFleet);
                }

                /*if ($fromDateObj->lt(Carbon::parse($startDate)->firstOfMonth())) {
                    $fromDateObj = Carbon::parse($startDate)->firstOfMonth();
                }*/

            }

            if ($fromDateObj->gt($toDateObj)) {
                return 0;
            }

            if($costArray != null && count($costArray) > 0) {

                foreach ($costArray as $singleCost) {

                    $costFromDateObj = Carbon::parse($singleCost['cost_from_date']);

                    if (isset($singleCost['cost_continuous']) && $singleCost['cost_continuous'] == 'true') {
                        $costToDateObj = Carbon::parse($startDate)->endOfMonth();
                        $costToMonthYear =  Carbon::parse($startDate)->endOfMonth()->format('m-Y');
                    } else {
                        $costToDateObj = Carbon::parse($singleCost['cost_to_date']);
                        $costToMonthYear =  Carbon::parse($singleCost['cost_to_date'])->format('m-Y');
                    }

                    $costFromMonthYear = Carbon::parse($singleCost['cost_from_date'])->format('m-Y');

                    if ($checkDateAddedToFleet ==  1 && $costToDateObj->lt($vehicleDtAddedToFleetObject)) {
                        continue;
                    }

                    if ($costToDateObj->lt($fromDateObj)) {
                        continue;
                    }

                    if (
                        $costFromDateObj->lt(Carbon::parse($startDate)->firstOfMonth())
                        && $costToDateObj->gt(Carbon::parse($startDate)->endOfMonth())
                        && $costFromDateObj->lte($toDateObj)
                    ) {
                        $days = $fromDateObj->diffInDays($toDateObj) + 1;

                    } else {

                        if ($currentMonthYear == $costFromMonthYear || $currentMonthYear == $costToMonthYear) {

                            if (
                                $fromDateObj->gte($costFromDateObj) && $fromDateObj->lte($costToDateObj)
                                &&
                                $toDateObj->gte($costFromDateObj) && $toDateObj->lte($costToDateObj)
                            ) {
                                $days = $fromDateObj->diffInDays($toDateObj) + 1;

                            } else if (
                                $fromDateObj->gte($costFromDateObj) && $toDateObj->gte($costToDateObj)
                            ) {
                                $days = $fromDateObj->diffInDays($costToDateObj) + 1;

                            } else if (
                                $fromDateObj->lte($costFromDateObj) && $toDateObj->lte($costToDateObj) && $toDateObj->gte($costFromDateObj)
                            ) {
                                $days = $costFromDateObj->diffInDays($toDateObj) + 1;

                            } else {
                                $days = 0;


                                if ($toDateObj->lt($costFromDateObj)) {
                                    $days = 0;
                                } else {
                                    $days = $costFromDateObj->diffInDays($costToDateObj) + 1;
                                }

                            }
                        } else {
                            $days = 0 ;
                        }
                    }

                    if ($type == 'currentCost') {
                        $currentCost = (float)$singleCost['cost_value']/$totalDaysInMonth*$days;
                    } else {
                        $totalRangeDays = $costFromDateObj->diffInDays($costToDateObj) + 1;
                        $currentCost = (float)$singleCost['cost_value']/$totalRangeDays*$days;
                    }


                    $cost = (float)$cost + (float)$currentCost;
                }

                return $cost;
            } else {
                return 0;
            }
    }

    public function downloadLastLogin()
    {
        $sheetDetail = [];
        $labelArray = [
            'First Name',
            'Last Name',
            'Company',
            'Division',
            'Username',
            'Email',
            'Mobile Number',
            'Roles',
            'Last Login',
            'Is Archived?'
        ];
        $dataArray = [];
        $users = User::withDisabled()->with('company')
                ->where('workshops_user_flag', '=', '0')
                ->orWhere('workshops_user_flag', '=', '2')
                ->whereNotExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('role_user')
                        ->leftJoin('roles', 'role_user.role_id', '=', 'roles.id')
                        ->whereRaw('role_user.user_id = users.id')
                        ->where('roles.name', '=', 'App version handling');
                })
                ->get();
        $dataArray = array();
        foreach ($users as $user) {
            $roles = $user->roles()->get()->pluck('id')->toArray();
            $role = "";
            if (in_array('1', $roles)) {
                $role = "Super admin";
            }
            if (in_array('14', $roles)) {
                $role = "User information only";
            }
            if (in_array('8', $roles)) {
                $role = "App access only";
            }
            if ($user->isHavingBespokeAccess()) {
                $role = "Super admin";
            }

            $ddata = [
                $user->first_name,
                $user->last_name,
                $user->company->name,
                $user->division ? $user->division->name : '',
                $user->username,
                $user->email,
                $user->mobile,
                $role,
                $user->last_login,
                $user->is_disabled ? "Yes" : "No"
            ];
            array_push($dataArray, $ddata);
        }
        $excelFileDetail = [
            'title' => "Last Login Report",
        ];
        $sheetArray[] = [
            'labelArray' => $labelArray,
            'dataArray' => $dataArray,
            'otherParams' => [
                'sheetName' => "Last Login Report",
            ],
        ];

        return toExcel($excelFileDetail, $sheetArray, 'xlsx', 'no');
    }
}