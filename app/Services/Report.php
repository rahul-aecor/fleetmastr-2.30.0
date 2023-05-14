<?php
namespace App\Services;
use App\Models\Work;
use App\Models\Timesheet;
use Storage;
use Mail;
use Carbon\Carbon;
use DB;
use App\Models\Vehicle;
use App\Models\VehicleAssignment;
use App\Models\Defect;
use App\Models\Settings;
use App\Models\P11dReport;
use App\Models\VehicleUsageHistory;
use App\Custom\Helper\Common;
use App\Custom\Helper\P11dReportHelper;
use App\Services\VehicleService;
use App\Services\Report;
use App\Services\UserService;
use App\Services\TelematicsService;
use App\Models\VehicleRegions;
use App\Models\VehicleArchiveHistory;
use App\Models\User;
use App\Models\TelematicsJourneys;
use App\Models\TelematicsJourneyDetails;
use App\Models\Check;
use App\Models\VehicleDivisions;
use App\Models\VehicleLocations;
use App\Models\MaintenanceEvents;
use App\Models\VehicleMaintenanceHistory;
use App\Repositories\CustomReportRepository;

use Maatwebsite\Excel\Facades\Excel;
use Auth;

class Report
{
    private $commonHelper;
    protected $obj_telematics_journeys;

    public function __construct(Common $commonHelper, TelematicsService $telematicsService, CustomReportRepository $customReportRepository) {
       $this->commonHelper=$commonHelper;
       $this->obj_telematics_journeys = new TelematicsJourneys();
       $this->telematicsService = new TelematicsService();
       $this->customReportRepository = $customReportRepository;
    }

    public function __destruct() {
       unset($this->commonHelper);
    }

    public function dailyWorkReport ()
    {
        $workResult = Work::join('users', 'works.created_by', '=', 'users.id')
            ->whereRaw('CONVERT_TZ(works.created_at, "UTC","'.config('config-variables.format.displayTimezone').'") >= CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY)," ","04:00:00")')
            ->whereRaw('CONVERT_TZ(works.created_at, "UTC","'.config('config-variables.format.displayTimezone').'") <= CONCAT(CURDATE()," ","04:00:00")')
            ->with('user.company')
            ->select('works.*')
            ->get()
            ->toArray();

        $excelFileDetail=array(
            'title' => "Job Viewer Daily Work Report"
            );

        $sheetArray=array();

        $sheet=array();
        $sheet['labelArray'] = [
            'Created date','Created time' ,'Start date', 'Start time','Completion date', 'Completion time', 'Created by', 'Engineer ID', 'Company', 'Region', 'Work status', 'Vistec ID', 'Activity type', 'Pipe length (m)', 'Pipe diameter (mm)', 'Area (m/sq)', 'DA call out', 'Flooding status', 'Abstract water', 'Redline captured', 'GPS', 'URL of work record'
        ];
        $sheet['dataArray'] = array();
        foreach ($workResult as $work) {
            $workUrl=action('WorksController@index', ['id' => $work['id']]);
            $workReportData = [
                $work['created_at']!==null ? date_format(date_create($work['created_at']),'d M Y') : '',
                $work['created_at']!==null ? date_format(date_create($work['created_at']),'H:i') : '',
                $work['started_at']!==null ? date_format(date_create($work['started_at']),'d M Y') : '',
                $work['started_at']!==null ? date_format(date_create($work['started_at']),'H:i') : '',
                $work['completed_at']!==null ? date_format(date_create($work['completed_at']),'d M Y') : '',
                $work['completed_at']!==null ? date_format(date_create($work['completed_at']),'H:i') : '',
                $work['user']['email'],
                $work['user']['engineer_id'],
                $work['user']['company']['name'],
                $work['user']['region'],
                $work['status'],
                $work['reference_id'],
                $work['activity_type'],
                $work['pipe_length']!==null ? $work['pipe_length'] : 0,
                $work['pipe_diameter']!==null ? $work['pipe_diameter'] : 0,
                $work['area'],
                $work['DA_call_out'],
                $work['flooding_status'],
                $work['abstract_water'],
                $work['is_redline_captured']==1 ? "Yes" : "No",
                $work['location_latitude'].', '.$work['location_longitude'],
                $workUrl
            ];
            array_push($sheet['dataArray'], $workReportData);
        }
        $sheet['otherParams'] = [
            'sheetName' => "Daily work report"
        ];
        $sheet['columnFormat'] = array();
        array_push($sheetArray, $sheet);

        $exportFile=$this->commonHelper->toExcel($excelFileDetail, $sheetArray, 'xlsx', 'no');
        \Log::info('sending daily work report');
        Mail::send('emails.daily_work_report', [], function ($message) use ($exportFile) {
            $message->to(config('config-variables.email_recepients.automated_reports'));
            $message->subject('Job Viewer daily work report - '.Carbon::now()->format('j F Y'));
            $message->attach($exportFile, [
                        'as' => "JV daily work report ".Carbon::now()->format('Y-m-d').".xlsx"
                    ]);
        });
    }

    public function monthlyWorkReport ()
    {
        $workResult = Work::join('users', 'works.created_by', '=', 'users.id')
            ->whereRaw('YEAR(CONVERT_TZ(works.created_at, "UTC","'.config('config-variables.format.displayTimezone').'")) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)')
            ->whereRaw('MONTH(CONVERT_TZ(works.created_at, "UTC","'.config('config-variables.format.displayTimezone').'")) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)')
            ->with('user.company')
            ->orderBy('works.created_at', 'ASC')
            ->select('works.*')
            ->get()
            ->toArray();

        $excelFileDetail=array(
            'title' => "Job Viewer Month Work Report - " .date('F Y', strtotime("-1 month"))
            );

        $sheetArray=array();

        $sheet=array();
        $sheet['labelArray'] = [
            'Created date', 'Created time', 'Created by', 'Engineer ID', 'Company', 'Work status', 'Vistec ID', 'Activity type', 'Pipe length (m)', 'Pipe diameter (mm)', 'Area (m/sq)', 'DA call out', 'Flooding status', 'Redline captured', 'Abstract water', 'URL of work record'
        ];
        $sheet['dataArray'] = array();
        foreach ($workResult as $work) {
            $workUrl=action('WorksController@index', ['id' => $work['id']]);
            $workReportData=[
                $work['created_at']!==null ? date_format(date_create($work['created_at']),'d M Y') : '',
                $work['created_at']!==null ? date_format(date_create($work['created_at']),'H:i') : '',
                $work['user']['email'],
                $work['user']['engineer_id'],
                $work['user']['company']['name'],
                $work['status'],
                $work['reference_id'],
                $work['activity_type'],
                $work['pipe_length']!==null ? $work['pipe_length'] : 0,
                $work['pipe_diameter']!==null ? $work['pipe_diameter'] : 0,
                $work['area'],
                $work['DA_call_out'],
                $work['flooding_status'],
                $work['is_redline_captured']==1 ? "Yes" : "No",
                $work['abstract_water'],
                $workUrl
            ];
            array_push($sheet['dataArray'], $workReportData);
        }
        $sheet['otherParams'] = [
            'sheetName' => "Work data"
        ];
        $sheet['columnFormat'] = array();
        array_push($sheetArray, $sheet);

        $exportFile=$this->commonHelper->toExcel($excelFileDetail, $sheetArray, 'xlsx', 'no');
        \Log::info('sending monthly work report');
        Mail::send('emails.monthly_work_report', [], function ($message) use ($exportFile) {
            $message->to(config('config-variables.email_recepients.automated_reports'));
            $message->subject('Job Viewer month work report - '.Carbon::now()->subMonth()->format('F Y'));
            $message->attach($exportFile, [
                        'as' => "JV month work report ".Carbon::now()->subMonth()->format('Y-m').".xlsx"
                    ]);
        });
    }

    public function dailyTimesheetReport ()
    {
        $timesheetResult = Timesheet::join('users as user_for', 'timesheets.record_for', '=', 'user_for.id')
            ->whereRaw('WEEKOFYEAR(CONVERT_TZ(timesheets.started_at, "UTC","'.config('config-variables.format.displayTimezone').'")) = WEEKOFYEAR(DATE_SUB(CURDATE(), INTERVAL 1 DAY))')
            ->whereRaw('CONVERT_TZ(timesheets.started_at, "UTC","'.config('config-variables.format.displayTimezone').'") <= CONCAT(CURDATE()," ","00:00:00")')
            ->where('user_for.company_id', '=', env('LANES_COMPANY_ID'))
            ->whereRaw('1 = (SELECT COUNT(*) FROM role_user WHERE role_id='.env('APP_ACCESS_ID').' AND user_id = user_for.id)')
            ->orderBy('user_for.first_name', 'ASC')
            ->orderBy('user_for.last_name', 'ASC')
            ->orderBy('user_for.email', 'ASC')
            ->orderBy('timesheets.started_at', 'ASC')
            ->select('timesheets.id', 'timesheets.started_at', 'timesheets.record_for', 'timesheets.activity_type', 'timesheets.reference_id', 'timesheets.is_overriden', 'timesheets.created_by', 'user_for.email', 'user_for.first_name', 'user_for.last_name', 'user_for.region', DB::raw('DATE_FORMAT(CONVERT_TZ(timesheets.started_at, "UTC","'.config('config-variables.format.displayTimezone').'"),"%d-%m-%Y") AS started_at_date'))
            ->get()
            ->toArray();

        $excelFileDetail=array(
            'title' => "Job Viewer Daily Timesheet Report"
            );

        $sheetArray=array();

        $sheet=array();
        $sheet['labelArray'] = [
            'Record for','Name' ,'Date', 'Company', 'Region', 'Hour00', 'Hour01', 'Hour02', 'Hour03', 'Hour04', 'Hour05', 'Hour06', 'Hour07', 'Hour08', 'Hour09', 'Hour10', 'Hour11', 'Hour12', 'Hour13', 'Hour14', 'Hour15', 'Hour16', 'Hour17', 'Hour18', 'Hour19', 'Hour20', 'Hour21', 'Hour22', 'Hour23', 'Total work hours for day', 'Total break hours for day', 'Standby', 'High hours', 'Manual time override', 'Auto corrected data'
        ];

        $tempRecordSet=$this->calculateDailyHours($timesheetResult);
        $sheetDataWithBackground=$this->getDailyHoursDataWithBackground($tempRecordSet);
        $sheet['dataArray'] = $sheetDataWithBackground['dataArray'];
        $sheet['cellBackgroundArray'] = $sheetDataWithBackground['cellBackgroundArray'];

        $sheet['otherParams'] = [
            'sheetName' => "Daily hours",
            'freezePane' => "E2"
        ];
        $sheet['columnFormat']= array(
                'F' => '0.00', 'G' => '0.00', 'H' => '0.00', 'I' => '0.00', 'J' => '0.00', 'K' => '0.00',
                'L' => '0.00', 'M' => '0.00', 'N' => '0.00', 'O' => '0.00', 'P' => '0.00', 'Q' => '0.00',
                'R' => '0.00', 'S' => '0.00', 'T' => '0.00', 'U' => '0.00', 'V' => '0.00', 'W' => '0.00',
                'X' => '0.00', 'Y' => '0.00', 'Z' => '0.00', 'AA' => '0.00', 'AB' => '0.00', 'AC' => '0.00',
                'AD' => '0.00', 'AE' => '0.00'
            );
        array_push($sheetArray, $sheet);

        $exportFile=$this->commonHelper->toExcel($excelFileDetail, $sheetArray, 'xlsx', 'no');
        \Log::info('sending daily timesheet report');
        Mail::send('emails.daily_timesheet_report', [], function ($message) use ($exportFile) {
            $message->to(config('config-variables.email_recepients.automated_reports'));
            $message->subject('Job Viewer daily timesheet report - '.Carbon::now()->format('j F Y'));
            $message->attach($exportFile, [
                        'as' => "JV daily timesheet report ".Carbon::now()->format('Y-m-d').".xlsx"
                    ]);
        });
    }

    public function weeklyTimesheetReport ()
    {
        $excelFileDetail=array(
            'title' => "Job Viewer Weekly Timesheet Report"
            );

        $worksCompleted=array();
        $sheetArray=array();

        $workResult = Work::join('users as user_for', 'works.created_by', '=', 'user_for.id')
            ->whereRaw('WEEKOFYEAR(CONVERT_TZ(works.completed_at, "UTC","'.config('config-variables.format.displayTimezone').'")) = WEEKOFYEAR(DATE_SUB(CURDATE(), INTERVAL 1 DAY))')
            ->where('user_for.company_id', '=', env('LANES_COMPANY_ID'))
            ->where('works.status','=', 'Completed')
            ->whereRaw('1 = (SELECT COUNT(*) FROM role_user WHERE role_id='.env('APP_ACCESS_ID').' AND user_id = user_for.id)')
            ->orderBy('user_for.first_name', 'ASC')
            ->orderBy('user_for.last_name', 'ASC')
            ->orderBy('user_for.email', 'ASC')
            ->select('user_for.email', 'user_for.first_name', 'user_for.last_name', 'user_for.region', 'works.status', 'works.created_by', 'works.reference_id', DB::raw('DATE_FORMAT(CONVERT_TZ(works.completed_at, "UTC","'.config('config-variables.format.displayTimezone').'"),"%d-%m-%Y") AS work_completed_at_date'))
            ->get()
            ->toArray();

        foreach ($workResult as $work) {
            $workCompletedDate=Carbon::parse($work['work_completed_at_date'])->timestamp;
            !isset($worksCompleted[$work['created_by']]) ? $worksCompleted[$work['created_by']]=array() : "";
            !isset($worksCompleted[$work['created_by']][$workCompletedDate]) ? $worksCompleted[$work['created_by']][$workCompletedDate]=array() : "";
            
            if(!in_array($work['reference_id'], $worksCompleted))
                array_push($worksCompleted[$work['created_by']][$workCompletedDate], $work['reference_id']);
        }

        $timesheetResult1 = Timesheet::join('users as user_for', 'timesheets.record_for', '=', 'user_for.id')
            ->whereRaw('WEEKOFYEAR(CONVERT_TZ(timesheets.started_at, "UTC","'.config('config-variables.format.displayTimezone').'")) = WEEKOFYEAR(DATE_SUB(CURDATE(), INTERVAL 1 DAY))')
            ->where('user_for.company_id', '=', env('LANES_COMPANY_ID'))
            ->whereRaw('1 = (SELECT COUNT(*) FROM role_user WHERE role_id='.env('APP_ACCESS_ID').' AND user_id = user_for.id)')
            ->orderBy('user_for.first_name', 'ASC')
            ->orderBy('user_for.last_name', 'ASC')
            ->orderBy('user_for.email', 'ASC')
            ->orderBy('timesheets.started_at', 'ASC')
            ->select('timesheets.id', 'timesheets.started_at', 'timesheets.record_for', 'timesheets.activity_type', 'timesheets.reference_id', 'timesheets.is_overriden', 'timesheets.created_by', 'user_for.email', 'user_for.first_name', 'user_for.last_name', 'user_for.region', DB::raw('DATE_FORMAT(CONVERT_TZ(timesheets.started_at, "UTC","'.config('config-variables.format.displayTimezone').'"),"%d-%m-%Y") AS started_at_date'))
            ->get()
            ->toArray();
        $sheet1=array();
        $sheet1['labelArray'] = [
            'Record for', 'Company', 'Region', 'Basic pay hours', 'Overtime one hours', 'Overtime two hours', 'Overtime three hours', 'Overtime four hours', 'Standby weekday count', 'Standby weekend count', 'Standby bank holiday count', 'Days in lieu', 'Guaranteed paid hours', 'Additional payment', 'Christmas additional count', 'Break payments', 'Paid travel hours', 'Jobs completed', 'High hours', 'Manual time override', 'Auto corrected data'
        ];
        $tempRecordSet1=$this->calculateDailyHours($timesheetResult1);
        $timesheetHoursData=$this->getTimesheetHoursData($tempRecordSet1,$worksCompleted);        
        $sheet1['dataArray'] = $timesheetHoursData['dataArray'];        
        $sheet1['otherParams'] = [
            'sheetName' => "Timesheet hours"
        ];
        $sheet1['columnFormat'] = array(
                'D' => '0.00', 'E' => '0.00', 'F' => '0.00', 'G' => '0.00', 'H' => '0.00' , 'I' => '0.00',
                'J' => '0.00', 'K' => '0.00', 'M' => '0.00', 'N' => '0.00', 'O' => '0.00' , 'P' => '0.00',
                'Q' => '0.00'
            );
        array_push($sheetArray, $sheet1);

        //Hours On Shift Sheet
        $timesheetResult2 = Timesheet::join('users as user_for', 'timesheets.record_for', '=', 'user_for.id')
            ->whereRaw('WEEKOFYEAR(CONVERT_TZ(timesheets.started_at, "UTC","'.config('config-variables.format.displayTimezone').'")) = WEEKOFYEAR(DATE_SUB(CURDATE(), INTERVAL 1 DAY))')
            ->where('user_for.company_id', '=', env('LANES_COMPANY_ID'))
            ->whereRaw('1 = (SELECT COUNT(*) FROM role_user WHERE role_id='.env('APP_ACCESS_ID').' AND user_id = user_for.id)')
            ->orderBy('started_at_date', 'ASC')
            ->orderBy('user_for.first_name', 'ASC')
            ->orderBy('user_for.last_name', 'ASC')
            ->orderBy('user_for.email', 'ASC')
            ->orderBy('timesheets.started_at', 'ASC')
            ->select('timesheets.id', 'timesheets.started_at', 'timesheets.record_for', 'timesheets.activity_type', 'timesheets.reference_id', 'timesheets.is_overriden', 'timesheets.created_by', 'user_for.email', 'user_for.first_name', 'user_for.last_name', 'user_for.region', DB::raw('DATE_FORMAT(CONVERT_TZ(timesheets.started_at, "UTC","'.config('config-variables.format.displayTimezone').'"),"%d-%m-%Y") AS started_at_date'))
            ->get()
            ->toArray();

        $sheet2=array();
        $sheet2['labelArray'] = [
            'Date', 'Record for', 'Company', 'Region', 'Total hours', 'Work hours', 'Break hours', 'Paid travel hours', 'Work completed'
        ];
        $tempRecordSet2=$this->calculateDailyHours($timesheetResult2);
        $hoursOnShiftData=$this->getHoursOnShiftData($tempRecordSet2, $worksCompleted);
        $sheet2['dataArray'] = $hoursOnShiftData['dataArray'];
        $sheet2['otherParams'] = [
            'sheetName' => "Hours on shift"
        ];
        $sheet2['columnFormat'] = array();
        array_push($sheetArray, $sheet2);

        //Daily Hours Sheet
        $sheet3=array();
        $sheet3['labelArray'] = [
            'Record for','Name' ,'Date', 'Company', 'Region', 'Hour00', 'Hour01', 'Hour02', 'Hour03', 'Hour04', 'Hour05', 'Hour06', 'Hour07', 'Hour08', 'Hour09', 'Hour10', 'Hour11', 'Hour12', 'Hour13', 'Hour14', 'Hour15', 'Hour16', 'Hour17', 'Hour18', 'Hour19', 'Hour20', 'Hour21', 'Hour22', 'Hour23', 'Total work hours for day', 'Total break hours for day', 'Standby', 'High hours', 'Manual time override', 'Auto corrected data'
        ];
        $dailyHoursDataWithBackground=$this->getDailyHoursDataWithBackground($tempRecordSet1);
        $sheet3['dataArray'] = $dailyHoursDataWithBackground['dataArray'];
        $sheet3['cellBackgroundArray'] = $dailyHoursDataWithBackground['cellBackgroundArray'];

        $sheet3['otherParams'] = [
            'sheetName' => "Daily hours",
            'freezePane' => "E2"
        ];
        $sheet3['columnFormat'] = array(
                'F' => '0.00', 'G' => '0.00', 'H' => '0.00', 'I' => '0.00', 'J' => '0.00', 'K' => '0.00',
                'L' => '0.00', 'M' => '0.00', 'N' => '0.00', 'O' => '0.00', 'P' => '0.00', 'Q' => '0.00',
                'R' => '0.00', 'S' => '0.00', 'T' => '0.00', 'U' => '0.00', 'V' => '0.00', 'W' => '0.00',
                'X' => '0.00', 'Y' => '0.00', 'Z' => '0.00', 'AA' => '0.00', 'AB' => '0.00', 'AC' => '0.00',
                'AD' => '0.00', 'AE' => '0.00'
            );
        array_push($sheetArray, $sheet3);

        $exportFile=$this->commonHelper->toExcel($excelFileDetail, $sheetArray, 'xlsx', 'no');
        \Log::info('sending weekly timesheet report');
        Mail::send('emails.weekly_timesheet_report', [], function ($message) use ($exportFile) {
            $message->to(config('config-variables.email_recepients.automated_reports'));
            $message->subject('Job Viewer weekly timesheet report - week commencing '.Carbon::now()->subDay()->startOfWeek()->format('j F Y'));
            $message->attach($exportFile, [
                        'as' => "JV weekly timesheet report ".Carbon::now()->subDay()->startOfWeek()->format('Y-m-d').".xlsx"
                    ]);
        });
    }

    public function weeklyWorkTravelReport ()
    {
        $workTravelResult = Timesheet::join('users as user_for', 'timesheets.record_for', '=', 'user_for.id')
            ->whereRaw('WEEKOFYEAR(CONVERT_TZ(timesheets.started_at, "UTC","'.config('config-variables.format.displayTimezone').'")) = WEEKOFYEAR(DATE_SUB(CURDATE(), INTERVAL 1 DAY))')
            ->whereIn('timesheets.activity_type', ['Work Start','Work End','Paid Travel Start','Paid Travel End'])
            ->orderBy('user_for.email', 'ASC')
            ->orderBy('timesheets.reference_id', 'ASC')
            ->orderBy('timesheets.started_at', 'ASC')
            ->select('timesheets.id', 'timesheets.started_at', 'timesheets.record_for', 'timesheets.activity_type', 'email', 'timesheets.reference_id')
            ->get()
            ->toArray();

        $excelFileDetail=array(
            'title' => "Job Viewer Weekly Work Travel Report"
            );

        $sheetArray=array();

        $sheet=array();
        $sheet['labelArray'] = [
            'Record for', 'Vistec ID', 'Paid travel start', 'Paid travel end', 'Travel duration (min)', 'Work start', 'Work end', 'Work duration (min)'
        ];
        $sheet['dataArray'] = array();
        $tempRecordSet=array();
        $i = $j = 0;
        foreach ($workTravelResult as $workTravel) {
            $tempRecordSet[$i]['email'] = $workTravel['email'];
            $tempRecordSet[$i]['reference_id'] = $workTravel['reference_id'];
            
            if($workTravel['activity_type'] == "Paid Travel Start")
                $tempRecordSet[$i]['paid_start'] = $workTravel['started_at'];
            
            if($workTravel['activity_type'] == "Paid Travel End")
                $tempRecordSet[$i]['paid_end'] = $workTravel['started_at'];
            
            if($workTravel['activity_type'] == "Work Start")
                $tempRecordSet[$i]['work_start'] = $workTravel['started_at'];
            
            if($workTravel['activity_type'] == "Work End")
                $tempRecordSet[$i]['work_end'] = $workTravel['started_at'];

            if( (array_key_exists($j+1, $workTravelResult) && ($workTravelResult[$j]['reference_id'] != $workTravelResult[$j+1]['reference_id'] || $workTravelResult[$j]['record_for'] != $workTravelResult[$j+1]['record_for']) ) || 
                    (array_key_exists('paid_start', $tempRecordSet[$i]) && array_key_exists('paid_end', $tempRecordSet[$i]) && array_key_exists('work_start', $tempRecordSet[$i]) && array_key_exists('work_end', $tempRecordSet[$i]))
                )  {
                $i++;
            }
            $j++;
        }

        foreach ($tempRecordSet as $tempData) {
            $paidStart = array_key_exists('paid_start', $tempData) ? strtotime($tempData['paid_start']) : 0;
            $paidEnd = array_key_exists('paid_end', $tempData) ? strtotime($tempData['paid_end']) : 0;
            $paidDuration   = round(abs($paidEnd - $paidStart) / 60);
            $workStart = array_key_exists('work_start', $tempData) ? strtotime($tempData['work_start']) : 0;
            $workEnd = array_key_exists('work_end', $tempData) ? strtotime($tempData['work_end']) : 0;
            $workDuration   = round(abs($workEnd - $workStart) / 60);
            $tempDataArr = [
                $tempData['email'],
                $tempData['reference_id'],
                array_key_exists('paid_start', $tempData) ? date_format(date_create($tempData['paid_start']),'Y-m-d H:i:s') : '',
                array_key_exists('paid_end', $tempData) ? date_format(date_create($tempData['paid_end']),'Y-m-d H:i:s') : '',
                $paidDuration,
                array_key_exists('work_start', $tempData) ? date_format(date_create($tempData['work_start']),'Y-m-d H:i:s') : '',
                array_key_exists('work_end', $tempData) ? date_format(date_create($tempData['work_end']),'Y-m-d H:i:s') : '',
                $workDuration
            ];
            array_push($sheet['dataArray'], $tempDataArr) ;
        }

        $sheet['otherParams'] = [
            'sheetName' => "Work travel report"
        ];
        $sheet['columnFormat'] = array();
        array_push($sheetArray, $sheet);

        $exportFile=$this->commonHelper->toExcel($excelFileDetail, $sheetArray, 'xlsx', 'no');
        \Log::info('sending weekly work travel report');
        Mail::send('emails.weekly_work_travel_report', [], function ($message) use ($exportFile) {
            $message->to(config('config-variables.email_recepients.automated_reports'));
            $message->subject('Job Viewer weekly work travel report - '.Carbon::now()->subDay()->startOfWeek()->format('j F Y'));
            $message->attach($exportFile, [
                        'as' => "JV weekly work travel report ".Carbon::now()->subDay()->startOfWeek()->format('Y-m-d').".xlsx"
                    ]);
        });
    }

    public function monthlyWorkTravelReport ()
    {
        $workTravelResult = Timesheet::join('users as user_for', 'timesheets.record_for', '=', 'user_for.id')
            ->whereRaw('YEAR(CONVERT_TZ(timesheets.started_at, "UTC","'.config('config-variables.format.displayTimezone').'")) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)')
            ->whereRaw('MONTH(CONVERT_TZ(timesheets.started_at, "UTC","'.config('config-variables.format.displayTimezone').'")) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)')
            ->whereIn('timesheets.activity_type', ['Work Start','Work End','Paid Travel Start','Paid Travel End'])
            ->orderBy('user_for.email', 'ASC')
            ->orderBy('timesheets.reference_id', 'ASC')
            ->orderBy('timesheets.started_at', 'ASC')
            ->select('timesheets.id', 'timesheets.started_at', 'timesheets.record_for', 'timesheets.activity_type', 'email', 'timesheets.reference_id')
            ->get()
            ->toArray();

        $excelFileDetail=array(
            'title' => "Job Viewer Monthly Work Travel Report"
            );

        $sheetArray=array();

        $sheet=array();
        $sheet['labelArray'] = [
            'Record for', 'Vistec ID', 'Paid travel start', 'Paid travel end', 'Travel duration (min)', 'Work start', 'Work end', 'Work duration (min)'
        ];
        $sheet['dataArray'] = array();
        $tempRecordSet=array();
        $i = $j = 0;
        foreach ($workTravelResult as $workTravel) {
            $tempRecordSet[$i]['email'] = $workTravel['email'];
            $tempRecordSet[$i]['reference_id'] = $workTravel['reference_id'];
            
            if($workTravel['activity_type'] == "Paid Travel Start")
                $tempRecordSet[$i]['paid_start'] = $workTravel['started_at'];
            
            if($workTravel['activity_type'] == "Paid Travel End")
                $tempRecordSet[$i]['paid_end'] = $workTravel['started_at'];
            
            if($workTravel['activity_type'] == "Work Start")
                $tempRecordSet[$i]['work_start'] = $workTravel['started_at'];
            
            if($workTravel['activity_type'] == "Work End")
                $tempRecordSet[$i]['work_end'] = $workTravel['started_at'];

            if( (array_key_exists($j+1, $workTravelResult) && ($workTravelResult[$j]['reference_id'] != $workTravelResult[$j+1]['reference_id'] || $workTravelResult[$j]['record_for'] != $workTravelResult[$j+1]['record_for']) ) || 
                    (array_key_exists('paid_start', $tempRecordSet[$i]) && array_key_exists('paid_end', $tempRecordSet[$i]) && array_key_exists('work_start', $tempRecordSet[$i]) && array_key_exists('work_end', $tempRecordSet[$i]))
                )  {
                $i++;
            }
            $j++;
        }

        foreach ($tempRecordSet as $tempData) {
            $paidStart = array_key_exists('paid_start', $tempData) ? strtotime($tempData['paid_start']) : 0;
            $paidEnd = array_key_exists('paid_end', $tempData) ? strtotime($tempData['paid_end']) : 0;
            $paidDuration   = round(abs($paidEnd - $paidStart) / 60);
            $workStart = array_key_exists('work_start', $tempData) ? strtotime($tempData['work_start']) : 0;
            $workEnd = array_key_exists('work_end', $tempData) ? strtotime($tempData['work_end']) : 0;
            $workDuration   = round(abs($workEnd - $workStart) / 60);
            $tempDataArr = [
                $tempData['email'],
                $tempData['reference_id'],
                array_key_exists('paid_start', $tempData) ? date_format(date_create($tempData['paid_start']),'Y-m-d H:i:s') : '',
                array_key_exists('paid_end', $tempData) ? date_format(date_create($tempData['paid_end']),'Y-m-d H:i:s') : '',
                $paidDuration,
                array_key_exists('work_start', $tempData) ? date_format(date_create($tempData['work_start']),'Y-m-d H:i:s') : '',
                array_key_exists('work_end', $tempData) ? date_format(date_create($tempData['work_end']),'Y-m-d H:i:s') : '',
                $workDuration
            ];
            array_push($sheet['dataArray'], $tempDataArr);
        }

        $sheet['otherParams'] = [
            'sheetName' => "Work travel report"
        ];
        $sheet['columnFormat'] = array();
        array_push($sheetArray, $sheet);

        $exportFile=$this->commonHelper->toExcel($excelFileDetail, $sheetArray, 'xlsx', 'no');
        \Log::info('sending montly work travel report');
        Mail::send('emails.monthly_work_travel_report', [], function ($message) use ($exportFile) {
            $message->to(config('config-variables.email_recepients.automated_reports'));
            $message->subject('Job Viewer monthly work travel report - '.Carbon::now()->subMonth()->format('F Y'));
            $message->attach($exportFile, [
                        'as' => "JV monthly work travel report ".Carbon::now()->subMonth()->format('Y-m').".xlsx"
                    ]);
        });
    }

    public function calculateDuration($startHour, $endHour, $startMinute, $endMinute, $startSecond, $endSecond, &$tempRecordSet, $durationType) {
        if($startHour==$endHour) {
            $timeKey=sprintf("%02d", $startHour);
            $tempRecordSet[$durationType.$timeKey]=isset($tempRecordSet[$durationType.$timeKey]) ? $tempRecordSet[$durationType.$timeKey]+($endMinute-$startMinute) : ($endMinute-$startMinute);
        } else {
            $tempHour=$startHour;
            while($tempHour<=$endHour) {
                $timeKey=sprintf("%02d", $tempHour);
                if($tempHour==$startHour) {
                    $tempRecordSet[$durationType.$timeKey]=isset($tempRecordSet[$durationType.$timeKey]) ? $tempRecordSet[$durationType.$timeKey]+(60-$startMinute) : (60-$startMinute);
                } else if($tempHour==$endHour) {
                    $tempRecordSet[$durationType.$timeKey]=isset($tempRecordSet[$durationType.$timeKey]) ? $tempRecordSet[$durationType.$timeKey]+$endMinute : $endMinute;
                } else {
                    $tempRecordSet[$durationType.$timeKey]=60;
                }
                if($tempRecordSet[$durationType.$timeKey]>60) {
                    $tempRecordSet[$durationType.$timeKey]=60;
                }
                $tempHour++;
            }
        }
    }

    public function calculateDailyHours($timesheetResult) {
        $tempRecordSet=array();
        $i = $j = 0;
        $tempCallbackRecord=0;
        if(count($timesheetResult) > 0) {
            $tempRecordSet[$i]=array();
            $tempRecordSet[$i]['standbycount']=0;
        }
        $callStartFlag=0;
        for($j=0;$j<count($timesheetResult);$j++) {
            $timesheet=$timesheetResult[$j];
            $startHour=-1;
            $endHour=-1;
            $startMinute=-1;
            $endMinute=-1;
            $startSecond=-1;
            $endSecond=-1;

            if($timesheet['activity_type'] == "On-call Start") {
                $callStartFlag=1;
                $tempRecordSet[$i]['standbycount']=1;
            }

            if($timesheet['activity_type'] == "Shift Start")
                $tempRecordSet[$i]['shift_start']=$timesheet['started_at'];
            if($timesheet['activity_type'] == "Shift End") {
                if(array_key_exists('shift_start',$tempRecordSet[$i])) {
                    $startHour=date_format(date_create($tempRecordSet[$i]['shift_start']),'H');
                    $endHour=date_format(date_create($timesheet['started_at']),'H');
                    $startMinute=date_format(date_create($tempRecordSet[$i]['shift_start']),'i');
                    $endMinute=date_format(date_create($timesheet['started_at']),'i');
                    $startSecond=date_format(date_create($tempRecordSet[$i]['shift_start']),'s');
                    $endSecond=date_format(date_create($timesheet['started_at']),'s');
                    unset($tempRecordSet[$i]['shift_start']);
                } else {
                    $startHour=0;
                    $endHour=date_format(date_create($timesheet['started_at']),'H');
                    $startMinute=0;
                    $endMinute=date_format(date_create($timesheet['started_at']),'i');
                    $startSecond=0;
                    $endSecond=date_format(date_create($timesheet['started_at']),'s');
                }
                $this->calculateDuration($startHour, $endHour, $startMinute, $endMinute, $startSecond, $endSecond, $tempRecordSet[$i], 'shifthour');
            }

            if($timesheet['activity_type'] == "Break Start")
                $tempRecordSet[$i]['break_start']=$timesheet['started_at'];
            if($timesheet['activity_type'] == "Break End") {
                if(array_key_exists('break_start',$tempRecordSet[$i])) {
                    $startHour=date_format(date_create($tempRecordSet[$i]['break_start']),'H');
                    $endHour=date_format(date_create($timesheet['started_at']),'H');
                    $startMinute=date_format(date_create($tempRecordSet[$i]['break_start']),'i');
                    $endMinute=date_format(date_create($timesheet['started_at']),'i');
                    $startSecond=date_format(date_create($tempRecordSet[$i]['break_start']),'s');
                    $endSecond=date_format(date_create($timesheet['started_at']),'s');
                    unset($tempRecordSet[$i]['break_start']);
                } else {
                    $startHour=0;
                    $endHour=date_format(date_create($timesheet['started_at']),'H');
                    $startMinute=0;
                    $endMinute=date_format(date_create($timesheet['started_at']),'i');
                    $startSecond=0;
                    $endSecond=date_format(date_create($timesheet['started_at']),'s');
                }
                $this->calculateDuration($startHour, $endHour, $startMinute, $endMinute, $startSecond, $endSecond, $tempRecordSet[$i], 'breakhour');
            }

            if($callStartFlag==1 && $timesheet['activity_type'] == "Work Start")
                $tempRecordSet[$i]['work_start']=$timesheet['started_at'];
            if($callStartFlag==1 && $timesheet['activity_type'] == "Work End") {
                if(array_key_exists('work_start',$tempRecordSet[$i])) {
                    $startHour=date_format(date_create($tempRecordSet[$i]['work_start']),'H');
                    $endHour=date_format(date_create($timesheet['started_at']),'H');
                    $startMinute=date_format(date_create($tempRecordSet[$i]['work_start']),'i');
                    $endMinute=date_format(date_create($timesheet['started_at']),'i');
                    $startSecond=date_format(date_create($tempRecordSet[$i]['work_start']),'s');
                    $endSecond=date_format(date_create($timesheet['started_at']),'s');
                    unset($tempRecordSet[$i]['work_start']);
                } else {
                    $startHour=0;
                    $endHour=date_format(date_create($timesheet['started_at']),'H');
                    $startMinute=0;
                    $endMinute=date_format(date_create($timesheet['started_at']),'i');
                    $startSecond=0;
                    $endSecond=date_format(date_create($timesheet['started_at']),'s');
                }
                $this->calculateDuration($startHour, $endHour, $startMinute, $endMinute, $startSecond, $endSecond, $tempRecordSet[$i], 'workhour');
            }

            if($callStartFlag==1 && $timesheet['activity_type'] == "Paid Travel Start")
                $tempRecordSet[$i]['paid_travel_start']=$timesheet['started_at'];
            if($callStartFlag==1 && $timesheet['activity_type'] == "Paid Travel End") {
                if(array_key_exists('paid_travel_start',$tempRecordSet[$i])) {
                    $startHour=date_format(date_create($tempRecordSet[$i]['paid_travel_start']),'H');
                    $endHour=date_format(date_create($timesheet['started_at']),'H');
                    $startMinute=date_format(date_create($tempRecordSet[$i]['paid_travel_start']),'i');
                    $endMinute=date_format(date_create($timesheet['started_at']),'i');
                    $startSecond=date_format(date_create($tempRecordSet[$i]['paid_travel_start']),'s');
                    $endSecond=date_format(date_create($timesheet['started_at']),'s');
                    unset($tempRecordSet[$i]['paid_travel_start']);
                } else {
                    $startHour=0;
                    $endHour=date_format(date_create($timesheet['started_at']),'H');
                    $startMinute=0;
                    $endMinute=date_format(date_create($timesheet['started_at']),'i');
                    $startSecond=0;
                    $endSecond=date_format(date_create($timesheet['started_at']),'s');
                }
                $this->calculateDuration($startHour, $endHour, $startMinute, $endMinute, $startSecond, $endSecond, $tempRecordSet[$i], 'paidtravelhour');
            }

            if($timesheet['is_overriden']==1) {
                $tempRecordSet[$i]['manual_time_override']="Check";
            }

            if($timesheet['created_by']==env('SYSTEM_ID')) {
                $tempRecordSet[$i]['auto_corrected_data']="Check";
            }

            if($callStartFlag==1 && $timesheet['activity_type'] == "On-call End") {
                $callStartFlag=0;
            } else if($timesheet['activity_type'] == "On-call End") {
                //next day call end code
                $callStartFlag=1;
                $j=$tempCallbackRecord-1;
                $tempRecordSet[$i]=array();
                $tempRecordSet[$i]['standbycount']=0;
                continue;
            }

            if( ((array_key_exists($j+1, $timesheetResult)) && ($timesheetResult[$j]['started_at_date'] != $timesheetResult[$j+1]['started_at_date'] || $timesheetResult[$j]['record_for'] != $timesheetResult[$j+1]['record_for'])) || ($j+1)==count($timesheetResult) ) {
                $tempCallbackRecord=$j+1;
                $tempRecordSet[$i]['record_for']=$timesheet['record_for'];
                $tempRecordSet[$i]['email']=$timesheet['email'];
                $tempRecordSet[$i]['name']=$timesheet['first_name'].' '.$timesheet['last_name'];
                $tempRecordSet[$i]['date']=$timesheet['started_at_date'];
                $tempRecordSet[$i]['region']=$timesheet['region'];
                $tempRecordSet[$i]['reference_id']=$timesheet['reference_id'];

                if(array_key_exists('shift_start',$tempRecordSet[$i])) {
                    $startHour=date_format(date_create($tempRecordSet[$i]['shift_start']),'H');
                    $endHour=23;
                    $startMinute=date_format(date_create($tempRecordSet[$i]['shift_start']),'i');
                    $endMinute=60;
                    $startSecond=date_format(date_create($tempRecordSet[$i]['shift_start']),'s');
                    $endSecond=60;
                    unset($tempRecordSet[$i]['shift_start']);
                    $this->calculateDuration($startHour, $endHour, $startMinute, $endMinute, $startSecond, $endSecond, $tempRecordSet[$i], 'shifthour');
                }
                if($callStartFlag==1 && array_key_exists('work_start',$tempRecordSet[$i])) {
                    $startHour=date_format(date_create($tempRecordSet[$i]['work_start']),'H');
                    $endHour=23;
                    $startMinute=date_format(date_create($tempRecordSet[$i]['work_start']),'i');
                    $endMinute=60;
                    $startSecond=date_format(date_create($tempRecordSet[$i]['work_start']),'s');
                    $endSecond=60;
                    unset($tempRecordSet[$i]['work_start']);
                    $this->calculateDuration($startHour, $endHour, $startMinute, $endMinute, $startSecond, $endSecond, $tempRecordSet[$i], 'workhour');
                }
                if(array_key_exists('break_start',$tempRecordSet[$i])) {
                    $startHour=date_format(date_create($tempRecordSet[$i]['break_start']),'H');
                    $endHour=23;
                    $startMinute=date_format(date_create($tempRecordSet[$i]['break_start']),'i');
                    $endMinute=60;
                    $startSecond=date_format(date_create($tempRecordSet[$i]['break_start']),'s');
                    $endSecond=60;
                    unset($tempRecordSet[$i]['break_start']);
                    $this->calculateDuration($startHour, $endHour, $startMinute, $endMinute, $startSecond, $endSecond, $tempRecordSet[$i], 'breakhour');
                }
                if($callStartFlag==1 && array_key_exists('paid_travel_start',$tempRecordSet[$i])) {
                    $startHour=date_format(date_create($tempRecordSet[$i]['paid_travel_start']),'H');
                    $endHour=23;
                    $startMinute=date_format(date_create($tempRecordSet[$i]['paid_travel_start']),'i');
                    $endMinute=60;
                    $startSecond=date_format(date_create($tempRecordSet[$i]['paid_travel_start']),'s');
                    $endSecond=60;
                    unset($tempRecordSet[$i]['paid_travel_start']);
                    $this->calculateDuration($startHour, $endHour, $startMinute, $endMinute, $startSecond, $endSecond, $tempRecordSet[$i], 'paidtravelhour');
                }
                $i++;
                $callStartFlag=0;
                if(($j+1)!=count($timesheetResult)) {
                    $tempRecordSet[$i]=array();
                    $tempRecordSet[$i]['standbycount']=0;
                }
            }
        }        
        return $tempRecordSet;
    }

    public function getDailyHoursDataWithBackground($tempRecordSet) {
        $dataArray = array();
        $cellBackgroundArray=array();
        foreach ($tempRecordSet as $tempData) {
            $totalWorkHours=0;
            $totalBreakHours=0;
            $totalPHours=0;
            $dailyhours=array();
            for($i=0;$i<24;$i++) {
                $hour=($i<10) ? "0".$i : strval($i);
                $dailyhours[$hour]=0;
                if(isset($tempData['workhour'.$hour])) {
                    $dailyhours[$hour]+=$tempData['workhour'.$hour];
                }
                if(isset($tempData['shifthour'.$hour])) {
                    $dailyhours[$hour]+=$tempData['shifthour'.$hour];
                }
                if(isset($tempData['paidtravelhour'.$hour])) {
                    $dailyhours[$hour]+=$tempData['paidtravelhour'.$hour];
                    $totalPHours+=$tempData['paidtravelhour'.$hour];
                }
                $totalWorkHours+=$dailyhours[$hour];
                if(isset($tempData['breakhour'.$hour])) {
                    $totalBreakHours+=$tempData['breakhour'.$hour];
                    $dailyhours[$hour]-=$tempData['breakhour'.$hour];
                }
            }
            $totalWorkHours=round( ($totalWorkHours/60) - ($totalBreakHours/60), 2);
            $totalBreakHours=round($totalBreakHours/60, 2);
            $background=array_fill(0, 35, '');
            $tempDataArr = [
                $tempData['email'],
                $tempData['name'],
                $tempData['date'],
                getenv('MAIL_FROM_NAME'),
                $tempData['region'],
                round($dailyhours['00']/60, 2),
                round($dailyhours['01']/60, 2),
                round($dailyhours['02']/60, 2),
                round($dailyhours['03']/60, 2),
                round($dailyhours['04']/60, 2),
                round($dailyhours['05']/60, 2),
                round($dailyhours['06']/60, 2),
                round($dailyhours['07']/60, 2),
                round($dailyhours['08']/60, 2),
                round($dailyhours['09']/60, 2),
                round($dailyhours['10']/60, 2),
                round($dailyhours['11']/60, 2),
                round($dailyhours['12']/60, 2),
                round($dailyhours['13']/60, 2),
                round($dailyhours['14']/60, 2),
                round($dailyhours['15']/60, 2),
                round($dailyhours['16']/60, 2),
                round($dailyhours['17']/60, 2),
                round($dailyhours['18']/60, 2),
                round($dailyhours['19']/60, 2),
                round($dailyhours['20']/60, 2),
                round($dailyhours['21']/60, 2),
                round($dailyhours['22']/60, 2),
                round($dailyhours['23']/60, 2),
                $totalWorkHours,
                $totalBreakHours,
                $tempData['standbycount'],
                ($totalWorkHours>20) ? 'Check' : '',
                isset($tempData['manual_time_override']) ? $tempData['manual_time_override'] : '',
                isset($tempData['auto_corrected_data']) ? $tempData['auto_corrected_data'] : ''
            ];
            for($i=5;$i<29;$i++) {
                if($tempDataArr[$i]>0)
                    $background[$i]=$this->commonHelper->colorShade("#63BE7B",$tempDataArr[$i]);
                else
                    $background[$i]="#FFFFFF";
            }
            array_push($dataArray, $tempDataArr);
            array_push($cellBackgroundArray, $background);
        }
        return array('dataArray' => $dataArray, 'cellBackgroundArray' => $cellBackgroundArray);
    }

    public function getHoursOnShiftData($tempRecordSet, $worksCompleted) {
        $dataArray = array();
        foreach ($tempRecordSet as $tempData) {
            $totalWorkHours=0;
            $totalBreakHours=0;
            $totalPaidTravelHours=0;
            $dailyhours=array();
            for($i=0;$i<24;$i++) {
                $hour=($i<10) ? "0".$i : strval($i);
                $dailyhours[$hour]=0;
                if(isset($tempData['workhour'.$hour])) {
                    $dailyhours[$hour]+=$tempData['workhour'.$hour];
                }
                if(isset($tempData['shifthour'.$hour])) {
                    $dailyhours[$hour]+=$tempData['shifthour'.$hour];
                }
                if(isset($tempData['paidtravelhour'.$hour])) {
                    $dailyhours[$hour]+=$tempData['paidtravelhour'.$hour];
                    $totalPaidTravelHours+=$tempData['paidtravelhour'.$hour];
                }
                $totalWorkHours+=$dailyhours[$hour];
                if(isset($tempData['breakhour'.$hour])) {
                    $totalBreakHours+=$tempData['breakhour'.$hour];
                }
            }
            $workCompletedDate=Carbon::parse($tempData['date'])->timestamp;
            $totalWorkCompleted=(isset($worksCompleted[$tempData['record_for']]) && isset($worksCompleted[$tempData['record_for']][$workCompletedDate])) ? count($worksCompleted[$tempData['record_for']][$workCompletedDate]) : 0;
            $tempDataArr = [
                $tempData['date'],
                $tempData['email'],
                getenv('MAIL_FROM_NAME'),
                $tempData['region'],
                round( ($totalWorkHours/60), 2 ),
                round( ($totalWorkHours/60) - ($totalBreakHours/60) - ($totalPaidTravelHours/60), 2 ),
                round($totalBreakHours/60,2),
                round($totalPaidTravelHours/60,2),
                $totalWorkCompleted
            ];
            array_push($dataArray, $tempDataArr);
        }
        return array('dataArray' => $dataArray);
    }

    public function getTimesheetHoursData($tempRecordSet, $worksCompleted) {
        $dataArray = array();
        $totalWorkHours=0;
        $totalBreakHours=0;
        $totalPaidTravelHours=0;
        $highHourFlag=0;
        $manualTimeOverrideFlag=0;
        $autoCorrectedDataFlag=0;
        $overTimeOneHour=0;
        $overTimeTwoHour=0;
        $standByWeekDaycount=0;
        $standByWeekEndcount=0;
        $j=0;
        foreach ($tempRecordSet as $tempData) {
            $dailyhours=array();
            $dayOfWeek=Carbon::parse($tempData['date'])->format('N');
            $dailyTotalWorkHour=0;
            $totalWorkHoursForDay=0;
            for($i=0;$i<24;$i++) {
                $hour=($i<10) ? "0".$i : strval($i);
                $dailyhours[$hour]=0;
                if(isset($tempData['workhour'.$hour])) {
                    $dailyhours[$hour]+=$tempData['workhour'.$hour];
                }
                if(isset($tempData['shifthour'.$hour])) {
                    $dailyhours[$hour]+=$tempData['shifthour'.$hour];
                }
                $dailyTotalWorkHour+=$dailyhours[$hour];
                if(isset($tempData['paidtravelhour'.$hour])) {
                    $dailyhours[$hour]+=$tempData['paidtravelhour'.$hour];
                    $totalPaidTravelHours+=$tempData['paidtravelhour'.$hour];
                    $dailyTotalWorkHour-=$tempData['paidtravelhour'.$hour];
                }
                $totalWorkHours+=$dailyhours[$hour];
                $totalWorkHoursForDay+=$dailyhours[$hour];
                if(isset($tempData['breakhour'.$hour])) {
                    $totalBreakHours+=$tempData['breakhour'.$hour];
                    $dailyTotalWorkHour-=$tempData['breakhour'.$hour];
                    $totalWorkHoursForDay-=$tempData['breakhour'.$hour];
                }
            }
            if($dayOfWeek<=5) {
                $overTimeOneHour+=$dailyTotalWorkHour;
                $standByWeekDaycount+=$tempData['standbycount'];
            } else {
                $overTimeTwoHour+=$dailyTotalWorkHour;
                $standByWeekEndcount+=$tempData['standbycount'];
            }
            
            if(round($totalWorkHoursForDay/60,2)>20)
                $highHourFlag=1;

            if(isset($tempData['manual_time_override']))
                $manualTimeOverrideFlag=1;

            if(isset($tempData['auto_corrected_data']))
                $autoCorrectedDataFlag=1;
               
            if( (array_key_exists($j+1, $tempRecordSet) && $tempRecordSet[$j]['record_for']!=$tempRecordSet[$j+1]['record_for']) || ($j+1)==count($tempRecordSet) ) {
                $basicPayHours=round( ($totalWorkHours/60) - ($totalBreakHours/60) - ($totalPaidTravelHours/60), 2 );
                $totalPaidTravelHours=round($totalPaidTravelHours/60,2);
                $overTimeOneHour=round($overTimeOneHour/60,2);
                $overTimeTwoHour=round($overTimeTwoHour/60,2);
                if($overTimeOneHour<40 && $overTimeTwoHour>0) {
                    $overTimeTwoHour=($overTimeOneHour+$overTimeTwoHour)-40;
                    if($overTimeTwoHour<0) {
                        $overTimeTwoHour=0;
                    }
                }
                $completedWorkForWeek=array();
                if(isset($worksCompleted[$tempData['record_for']])) {
                    foreach ($worksCompleted[$tempData['record_for']] as $recordFor) {
                        foreach ($recordFor as $dateWiseCompletedWork) {
                            if(!in_array($dateWiseCompletedWork, $completedWorkForWeek)) {
                                array_push($completedWorkForWeek, $dateWiseCompletedWork);
                            }
                        }
                    }
                }
                $totalWorkCompleted=isset($worksCompleted[$tempData['record_for']][$tempData['date']]) ? count($worksCompleted[$tempData['record_for']][$tempData['date']]) : 0;
                $tempDataArr = [
                    $tempData['email'],
                    getenv('MAIL_FROM_NAME'),
                    $tempData['region'],
                    ($basicPayHours>40) ? 40.00 : $basicPayHours,
                    ($overTimeOneHour>40) ? ($overTimeOneHour-40) : 0,
                    $overTimeTwoHour,
                    0,
                    0,
                    $standByWeekDaycount,
                    $standByWeekEndcount,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    $totalPaidTravelHours,
                    count($completedWorkForWeek) > 0 ? implode("; ",$completedWorkForWeek).";" : '',
                    $highHourFlag==1 ? "Check" : "",
                    $manualTimeOverrideFlag==1 ? "Check" : "",
                    $autoCorrectedDataFlag==1 ? "Check" : "",
                ];
                array_push($dataArray, $tempDataArr);
                $totalWorkHours=0;
                $totalBreakHours=0;
                $totalPaidTravelHours=0;
                $highHourFlag=0;
                $manualTimeOverrideFlag=0;
                $autoCorrectedDataFlag=0;
                $overTimeOneHour=0;
                $overTimeTwoHour=0;
                $standByWeekDaycount=0;
                $standByWeekEndcount=0;
            }
            $j++;
        }
        return array('dataArray' => $dataArray);
    }

    public function downloadReport($name, $period="curr", $data, $reportDownload = null, $sheetTitle = null, $newReportData = null, $oldReportData = null)
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
                    'Vehicle Division',
                    'Vehicle Region',
                    'HGV/Non-HGV',
                    'Type',
                    'Manufacturer',
                    'Model',
                    'Vehicle Location',
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
                // $startDate = (new Carbon('first day of this month'))->format('Y-m-d 00:00:00');
                // $endDate = (new Carbon('now'))->toDateTimeString();
                // if(strtolower($period) == "prev"){
                //     $startDate = (new Carbon('first day of last month'))->format('Y-m-d 00:00:00');
                //     $endDate = (new Carbon('first day of this month'))->format('Y-m-d 00:00:00');
                // }

                $startDate = Carbon::parse($data['date_from']);
                $endDate = Carbon::parse($data['date_to']);

                $dataArray = [];
                $defects = $this->customReportRepository->defectReportData($data);

                if(isset($data['accessible_regions'])) {
                    $defects = $defects->whereHas('vehicle', function($q) use($data) {
                                        $q->whereIn('vehicle_region_id', $data['accessible_regions']);
                                    });
                }

                $defects = $defects->orderBy('report_datetime')->get();

                foreach ($defects as $defect) {
                    if (empty($defect->history->first())) {
                        $last_comment_date = "N/A";
                        $last_comment = "N/A";
                    }
                    else {
                        $last_comment_date = $defect->history->first()->report_datetime->format('d-m-Y');
                        $last_comment = $defect->history->first()->comments;
                    }
                    $defectsData = [
                        $defect->vehicle->registration,
                        $defect->vehicle->division ? $defect->vehicle->division->name : 'NA',
                        $defect->vehicle->region ? $defect->vehicle->region->name : 'NA',
                        ($defect->vehicle->type->vehicle_category == "hgv")?"HGV":"Non-HGV",
                        $defect->vehicle->type->vehicle_type,
                        $defect->vehicle->type->manufacturer,
                        $defect->vehicle->type->model,
                        (!is_null($defect->vehicle->location)) ? $defect->vehicle->location->name : "",
                        //$defect->vehicle->vehicle_region,
                        (!is_null($defect->vehicle->repair_location)) ? $defect->vehicle->repair_location->name: "",
                        $defect->report_datetime->format('d-m-Y'),
                        $defect->id,
                        $defect->vehicle->last_odometer_reading,
                        isset($defect->defectMaster) ? $defect->defectMaster->page_title : "",
                        isset($defect->defectMaster) ? $defect->defectMaster->defect : "",
                        $defect->vehicle->status,
                        $defect->status,
                        $last_comment_date,
                        $last_comment,
                    ];
                    array_push($dataArray, $defectsData);
                }

                $reportTitle = $startDate->format('d M Y')." to ".$endDate->format('d M Y');
                $otherParams = [
                    'fileName' => Carbon::now()->format('Ymd')."_Defect_report_Standard",
                    'sheetTitle_lable' => "Report",
                    'sheetTitle_value' => "Defect Report",
                    'sheetSubTitle_lable_first' => "Description",
                    'sheetSubTitle_value_first' => $data['report_description'],
                    'sheetSubTitle_lable_second' => "Duration",
                    'sheetSubTitle_value_second' => $reportTitle,
                    'sheetName' => $reportTitle,
                    'boldLastRow' => false
                ];
                return $this->toExcel($lableArray,$dataArray,$otherParams);
                break;

            case 'b':
                if($reportDownload) {
                    $labels = $reportDownload->reportDataset->pluck('title')->toArray();
                } else {
                    $labels = $this->getReportLabels('standard_vor_defect_report');
                }
                // $lableArray = ['Defect Category', 'Defect'];
                $allRegions = $this->getUserRegions();
                asort($allRegions);
                $lableArray = array_merge($labels, $allRegions, ['Grand Total']);

                // $startDate = Carbon::now()->startOfWeek()->format('Y-m-d 00:00:00');
                // $endDate = Carbon::now()->endOfWeek()->format('Y-m-d 23:59:59');
                // if(strtolower($period) == "prev"){
                //     $startDate = Carbon::now()->addWeeks(-1)->startOfWeek()->format('Y-m-d 00:00:00');
                //     $endDate = Carbon::now()->addWeeks(-1)->endOfWeek()->format('Y-m-d 23:59:59');
                // }
                $startDate = Carbon::parse($data['date_from']);
                $endDate = Carbon::parse($data['date_to']);

                $dataArray = [];
                // $userRegions = Auth::user()->regions->lists('id')->toArray();
                $userRegions = $this->getUserRegions('id');
                $defects = Defect::select('defect_master.page_title','defect_master.defect','vehicles.vehicle_region_id as vehicle_region', \DB::raw('COUNT(defects.id) cnt'))
                ->join('defect_master','defects.defect_master_id','=','defect_master.id')
                ->join('vehicles','defects.vehicle_id','=','vehicles.id')
                ->leftjoin('vehicle_divisions','vehicles.vehicle_division_id', '=', 'vehicle_divisions.id')
                ->leftjoin('vehicle_regions','vehicles.vehicle_region_id', '=', 'vehicle_regions.id')
                // ->whereBetween('defects.report_datetime',[$startDate,$endDate])
                ->whereDate('defects.report_datetime', '>=', $startDate)
                ->whereDate('defects.report_datetime', '<=', $endDate);
                // ->whereIn('vehicles.vehicle_region_id', $userRegions)


                if(isset($data['accessible_regions'])) {
                    $defects = $defects->whereIn('vehicles.vehicle_region_id', $data['accessible_regions']);
                }

                $defects = $defects->groupBy(['defect_master.page_title','vehicle_region'])
                                    ->get();
                $ddarray = array();
                foreach ($defects as $defect) {
                    $ddarray[$defect->page_title][$defect->defect][$defect->vehicle_region] = $defect->cnt;
                }
                $dataArray = array();
                foreach ($defects as $defect) {
                    $rowData = [];
                    // add initial defect data
                    foreach($lableArray as $label) {
                        if($label == 'Defect Category') {
                            $rowData[] = $defect->page_title;
                        } else if($label == 'Defect') {
                            $rowData[] = $defect->defect;
                        }
                    }
                    // $rowData[] = $defect->page_title;
                    // $rowData[] = $defect->defect;
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
                    $lastRow = 7 + ((count($dataArray) == 0)?1:count($dataArray));
                    if(count($labels) == 1) {
                        $ddata = [
                            "Grand Total"
                        ];
                    } else {
                        $ddata = [
                            "Grand Total",
                            ""
                        ];
                    }
                    // for ($i=2; $i < (count($dataArray[0])); $i++) {
                    for ($i=count($labels); $i < (count($dataArray[0])); $i++) {
                        $excelColumnName = $this->getNameFromNumber($i);
                        $ddata[] = "=SUM(".$excelColumnName."7:".$excelColumnName.$lastRow.")";
                    }

                    array_push($dataArray, $ddata);
                }
                $reportTitle = $startDate->format('d M Y')." to ".$endDate->format('d M Y');

                if($newReportData) {
                    $otherParams = $this->customReportOtherParam($reportTitle, $sheetTitle, $newReportData, $oldReportData);
                } else {
                    $otherParams = [
                        'fileName' => Carbon::now()->format('Ymd')."_DefectSummary_report_Standard",
                        'sheetTitle_lable' => "Report",
                        'sheetTitle_value' => "Defect Summary Report",
                        'sheetSubTitle_lable_first' => "Description",
                        'sheetSubTitle_value_first' => $data['report_description'],
                        'sheetSubTitle_lable_second' => "Duration",
                        'sheetSubTitle_value_second' => $reportTitle,
                        'sheetName' => "Defect Summary Report",
                        'boldLastRow' => true
                    ];
                }

                return $this->toExcel($lableArray,$dataArray,$otherParams);
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
                if($reportDownload) {
                    $lableArray = $reportDownload->reportDataset->pluck('title')->toArray();
                } else {
                    $lableArray = $this->getReportLabels('standard_vor_report');
                }
                $sortBy = $this->setOrderBy('standard_vor_report');

                $startDate = Carbon::parse($data['date_from']);
                $endDate = Carbon::parse($data['date_to']);

                $dataArray = [];
                $defects = $this->customReportRepository->vorReportData($data);
                
                if(isset($data['accessible_regions'])) {
                    $defects = $defects->whereHas('vehicle', function ($query) use($data) {
                                $query->whereIn('vehicles.vehicle_region_id', $data['accessible_regions']);
                            });
                }

                $defects = $defects->orderBy($sortBy)->orderBy('report_datetime')->orderBy('vehicle_id')->get(['defects.*']);
                
                foreach ($defects as $defect) {
                    $duration = '';
                    $vorLogs = $defect->vehicle->vorLogs;
                    if(count($vorLogs)) {
                        $duration = $vorLogs->first()->dt_off_road;
                    }
                    
                    $vorDuration = 'N/A';
                    if(!empty($duration)){
                        $now = Carbon::now();
                        $dateDiff = $duration->diff($now)->days;
                        $vorDuration = ($dateDiff < 1)? 'Today': ($dateDiff == 1) ? "1 Day" : $dateDiff." Days";
                    }
                    if (empty($defect->history->first())) {
                        $last_comment_date = "N/A";
                        $last_comment = "N/A";
                    }
                    else {
                        $last_comment_date = $defect->history->first()->report_datetime->format('d-m-Y');
                        $last_comment = $defect->history->first()->comments;
                    }

                    $dataArr = [];
                    foreach($lableArray as $label) {
                        if($label == 'Registration') {
                            $dataArr[] = $defect->vehicle->registration;
                        } else if($label == 'Vehicle Division') {
                            $dataArr[] = $defect->vehicle->division->name;
                        } else if($label == 'Vehicle Region') {
                            $dataArr[] = $defect->vehicle->region->name;
                        } else if($label == 'HGV/Non-HGV') {
                            $dataArr[] = ($defect->vehicle->type->vehicle_category == "hgv")?"HGV":"Non-HGV";
                        } else if($label == 'Type') {
                            $dataArr[] = $defect->vehicle->type->vehicle_type;
                        } else if($label == 'Manufacturer') {
                            $dataArr[] = $defect->vehicle->type->manufacturer;
                        } else if($label == 'Model') {
                            $dataArr[] = $defect->vehicle->type->model;
                        } else if($label == 'Vehicle Location') {
                            $dataArr[] = (!is_null($defect->vehicle->location)) ? $defect->vehicle->location->name : "";
                        } else if($label == 'Repair/Maintenance Location') {
                            $dataArr[] = (!is_null($defect->vehicle->repair_location)) ? $defect->vehicle->repair_location->name: "";
                        } else if($label == "Dated VOR'd") {
                            $dataArr[] = $duration != '' ? $duration->format('d-m-Y') : $duration;
                        } else if($label == 'VOR Duration(days)') {
                            $dataArr[] = $vorDuration;
                        } else if($label == 'Vehicle Status') {
                            $dataArr[] = $defect->vehicle->status;
                        } else if($label == 'Defect Category') {
                            $dataArr[] = $defect->defectMaster->page_title;
                        } else if($label == 'Defect') {
                            $dataArr[] = $defect->defectMaster->defect;
                        } else if($label == 'Defect Number') {
                            $dataArr[] = $defect->id;
                        } else if($label == "Estimated Completion Date") {
                            $dataArr[] = $defect->est_completion_date;
                        } else if($label == 'Last Comment Date') {
                            $dataArr[] = $last_comment_date;
                        } else if($label == 'Last Comment') {
                            $dataArr[] = $last_comment;
                        }
                    }
                    array_push($dataArray, $dataArr);
                }

                $reportTitle = $startDate->format('d M Y')." to ".$endDate->format('d M Y');

                if($newReportData) {
                    $otherParams = $this->customReportOtherParam($reportTitle, $sheetTitle, $newReportData, $oldReportData);
                } else {
                    $otherParams = [
                        'fileName' => Carbon::now()->format('Ymd')."_VOR_report_Standard",
                        'sheetTitle_lable' => "Report",
                        'sheetTitle_value' => "VOR Report",
                        'sheetSubTitle_lable_first' => "Description",
                        'sheetSubTitle_value_first' => $data['report_description'],
                        'sheetSubTitle_lable_second' => "Duration",
                        'sheetSubTitle_value_second' => $reportTitle,
                        'sheetName' => "VOR",
                        'boldLastRow' => false
                    ];
                }

                // $this->toExcelMulti($lableArray,$dataArray,$otherParams);
                return $this->toExcel($lableArray,$dataArray,$otherParams);
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
                // $startDate = Carbon::now()->startOfWeek()->format('Y-m-d 00:00:00');
                // $endDate = Carbon::now()->endOfWeek()->format('Y-m-d 23:59:59');
                // if(strtolower($period) == "prev"){
                //     $startDate = Carbon::now()->addWeeks(-1)->startOfWeek()->format('Y-m-d 00:00:00');
                //     $endDate = Carbon::now()->addWeeks(-1)->endOfWeek()->format('Y-m-d 23:59:59');
                // }
                $startDate = Carbon::parse($data['date_from']);
                $endDate = Carbon::parse($data['date_to']);
                $dataArray = [];

                $users = $this->customReportRepository->activityReportData($data);

                if(isset($data['accessible_regions'])) {
                    $users = $users->where(function ($query) use ($data) {
                                    $query->whereNull('users.user_region_id');
                                    $query->orWhereIn('users.user_region_id', $data['accessible_regions']);
                                });
                }

                $users = $users->get();

                $dataArray = json_decode(json_encode($users), true);
                $reportTitle = $startDate->format('d M Y')." to ".$endDate->format('d M Y');
                $otherParams = [
                    'fileName' => Carbon::now()->format('Ymd')."_Vehicle_Check_Activity_report_Standard",
                    'sheetTitle_lable' => "Report",
                    'sheetTitle_value' => "Vehicle Check Activity Report",
                    'sheetSubTitle_lable_first' => "Description",
                    'sheetSubTitle_value_first' => $data['report_description'],
                    'sheetSubTitle_lable_second' => "Duration",
                    'sheetSubTitle_value_second' => $reportTitle,
                    'sheetName' => $reportTitle,
                    'boldLastRow' => false
                ];
                // $this->toExcelMulti($lableArray,$dataArray,$otherParams);
                return $this->toExcel($lableArray,$dataArray,$otherParams);
                break;
            case 'p11dreport':
                $p11dReportHelper = new P11dReportHelper();
                $commonHelper = new Common();

                $reportfile = $p11dReportHelper->generateReport($period,'no');
                //$reportFile = $commonHelper->downloadDesktopExcel($excelFileDetail,$sheetArray,'xlsx','yes');
                break;

            default:
                return view('reports.invalid');
                break;
        }
    }

    private function toExcel($lableArray, $dataArray, $otherParams)
    {
        $fileName = isset($otherParams['fileName']) ? $otherParams['fileName'] : $otherParams['sheetTitle_value'];
        $excelCreateObj = \Excel::create($fileName, function($excel) use($lableArray, $dataArray, $otherParams) {
            $excel->setTitle($otherParams['sheetTitle_value']);
            $excel->sheet($otherParams['sheetName'], function($sheet) use($lableArray, $dataArray, $otherParams) {
                $sheet->row(2, array($otherParams['sheetTitle_lable'], $otherParams['sheetTitle_value']));
                $sheet->row(2, function($row){
                    // $row->setFontColor(setting('primary_colour'));
                    // $row->setFontWeight('bold');
                    $row->setFontFamily('Arial');
                    $row->setFontSize(10);
                });
                $sheet->mergeCells('B2:D2');
                $sheet->row(3, array($otherParams['sheetSubTitle_lable_first'], $otherParams['sheetSubTitle_value_first']));
                $sheet->row(3, function($row){
                    // $row->setFontColor(setting('primary_colour'));
                    // $row->setFontWeight('bold');
                    $row->setFontFamily('Arial');
                    $row->setFontSize(10);
                });
                $sheet->mergeCells('B3:D3');
                $sheet->row(4, array($otherParams['sheetSubTitle_lable_second'], $otherParams['sheetSubTitle_value_second']));
                $sheet->row(4, function($row){
                    // $row->setFontColor(setting('primary_colour'));
                    // $row->setFontWeight('bold');
                    $row->setFontFamily('Arial');
                    $row->setFontSize(10);
                });
                $sheet->mergeCells('B4:D4');
                $sheet->cell('A2:A4', function($cells){
                    $cells->setFontWeight('bold');
                });

                if(isset($otherParams['pmiDataCount'])) {
                    $sheet->cell('F2', 'Vehicles to Service');
                    $sheet->cell('G2', $otherParams['pmiDataCount']['totalVehicles'] == 0 ? "\t0" : $otherParams['pmiDataCount']['totalVehicles']);
                    $sheet->cell('G2', function($cells){
                        $cells->setAlignment('right');
                    });

                    $sheet->cell('F3', 'Services Done');
                    $sheet->cell('G3', $otherParams['pmiDataCount']['totalFinishedServiceCount'] == 0 ? "\t0" : $otherParams['pmiDataCount']['totalFinishedServiceCount']);
                    $sheet->cell('G3', function($cells){
                        $cells->setAlignment('right');
                    });

                    $sheet->cell('F4', 'Performance');
                    $sheet->cell('G4', $otherParams['pmiDataCount']['performance'] == 0 ? "\t0" : $otherParams['pmiDataCount']['performance']);
                    $sheet->cell('G4', function($cells){
                        $cells->setAlignment('right');
                    });

                    $sheet->cell('I2', 'Early Service');
                    $sheet->cell('J2', $otherParams['pmiDataCount']['earlyStatusCount'] == 0 ? "\t0" : $otherParams['pmiDataCount']['earlyStatusCount']);
                    $sheet->cell('J2', function($cells){
                        $cells->setAlignment('right');
                    });

                    $sheet->cell('I3', 'Late Service');
                    $sheet->cell('J3', $otherParams['pmiDataCount']['lateStatusCount'] == 0 ? "\t0" : $otherParams['pmiDataCount']['lateStatusCount']);
                    $sheet->cell('J3', function($cells){
                        $cells->setAlignment('right');
                    });

                    $sheet->cell('I4', 'Missed Service');
                    $sheet->cell('J4', $otherParams['pmiDataCount']['missedStatusCount'] == 0 ? "\t0" : $otherParams['pmiDataCount']['missedStatusCount']);
                    $sheet->cell('J4', function($cells){
                        $cells->setAlignment('right');
                    });
                }

                $sheet->row(6, $lableArray);
                $sheet->row(6, function($row){
                    // $row->setBackground(setting('primary_colour'));
                    // $row->setFontColor('#ffffff');
                    $row->setFontWeight('bold');
                    $row->setFontFamily('Arial');
                    $row->setFontSize(10);
                });
                // $sheet->setHeight(5, 30);
                // $sheet->cells('A5', function($cells) {
                //     $cells->setAlignment('center');
                //     $cells->setValignment('middle');
                // });

                $row_no = 7;
                // echo "<pre>"; print_r($dataArray);  echo "</pre>";
                foreach ($dataArray as $data) {
                    if(isset($data[0]) && $data[0] == 'SUBTOTAL' && $data[0] != '0') {
                        $sheet->row(($row_no), function($row){
                            $row->setFontWeight('bold');
                        });
                    }
                    $sheet->row($row_no, $data);
                    $row_no++;
                }

                if($otherParams['boldLastRow']){
                    $sheet->row(($row_no-1), function($row){
                        $row->setFontWeight('bold');
                    });
                }
                // $columnFormat = ['R22'=>'\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT'];
                // $sheet->setColumnFormat($columnFormat);

                if(isset($otherParams['columnFormat']) && count($otherParams['columnFormat']) >0 ){
                    $sheet->setColumnFormat($otherParams['columnFormat']);
                }

                if(isset($otherParams['headingLabelArrayFilterRange'])){
                    $sheet->setAutoFilter($otherParams['headingLabelArrayFilterRange']);
                }

                if(isset($otherParams['url'])){
                    $rowNo = 7;
                    $baseUrl = $otherParams['url']['baseURL'];
                    unset($otherParams['url']['baseURL']);
                    for($i=0;$i<count($dataArray);$i++) {
                        foreach($otherParams['url'] as $columnData) {
                            $column = $columnData['column'];
                            for($j=0; $j < count($dataArray[$i]); $j++) {

                                if($sheet->getCell($columnData['prev_column'].$rowNo)->getValue() != '') {

                                    $sheet->getCell($column.$rowNo) // A1, B2
                                            ->setValueExplicit("Map", \PHPExcel_Cell_DataType::TYPE_STRING);

                                    $sheet->getStyle($column.$rowNo)
                                            ->applyFromArray(array( 'font' => array( 'color' => ['rgb' => '0000FF'], 'underline' => 'single' ) )); // Blue, Underline

                                    $sheet->getCell($column.$rowNo)
                                            ->getHyperlink()
                                            ->setUrl($baseUrl.$sheet->getCell($columnData['prev_column'].$rowNo)->getValue());
                                }
                            }
                        }
                        $rowNo++;
                    }
                }
            });
        });

        $oldFile = 'exports/'.$fileName.'.xlsx';
        if(Storage::disk('local')->has($oldFile)) {
            Storage::delete($oldFile);
        }

        $excelCreateObj->store('xlsx');
        $exportFile=storage_path('exports').'/'.$fileName.'.xlsx';
        return $exportFile;
    }

    private function toExcelMulti($lableArray, $dataArrays, $otherParams)
    {
        $fileName = isset($otherParams['fileName']) ? $otherParams['fileName'] : $otherParams['sheetTitle_value'];
        $excelCreateObj = \Excel::create($fileName, function($excel) use($lableArray, $dataArrays, $otherParams) {
            $excel->setTitle($otherParams['sheetTitle_value']);
            foreach ($dataArrays as $key => $dataArray) {
                $excel->sheet($key, function($sheet) use($lableArray, $dataArray, $otherParams) {
                    $sheet->row(2, array($otherParams['sheetTitle_lable'], $otherParams['sheetTitle_value']));
                    $sheet->row(2, function($row){
                        // $row->setFontColor(setting('primary_colour'));
                        // $row->setFontWeight('bold');
                        $row->setFontFamily('Arial');
                        $row->setFontSize(10);
                    });
                    $sheet->mergeCells('B2:C2');
                    $sheet->row(3, array($otherParams['sheetSubTitle_lable_first'], $otherParams['sheetSubTitle_value_first']));
                    $sheet->row(3, function($row){
                        // $row->setFontColor(setting('primary_colour'));
                        // $row->setFontWeight('bold');
                        $row->setFontFamily('Arial');
                        $row->setFontSize(10);
                    });
                    $sheet->mergeCells('B3:C3');
                    $sheet->row(4, array($otherParams['sheetSubTitle_lable_second'], $otherParams['sheetSubTitle_value_second']));
                    $sheet->row(4, function($row){
                        // $row->setFontColor(setting('primary_colour'));
                        // $row->setFontWeight('bold');
                        $row->setFontFamily('Arial');
                        $row->setFontSize(10);
                    });
                    $sheet->mergeCells('B4:C4');
                    $sheet->cell('A2:A4', function($cells){
                        $cells->setFontWeight('bold');
                    });

                    $sheet->row(6, $lableArray);
                    $sheet->row(6, function($row){
                        // $row->setBackground(setting('primary_colour'));
                        // $row->setFontColor('#ffffff');
                        $row->setFontWeight('bold');
                        $row->setFontFamily('Arial');
                        $row->setFontSize(10);
                    });
                    // $sheet->setHeight(5, 30);
                    // $sheet->cells('A5', function($cells) {
                    //     $cells->setAlignment('center');
                    //     $cells->setValignment('middle');
                    // });

                    $row_no = 7;
                    // echo "<pre>"; print_r($dataArray);  echo "</pre>";
                    foreach ($dataArray as $data) {
                        if($data[0] == 'SUBTOTAL' && $data[0] != '0') {
                            $sheet->row(($row_no), function($row){
                                $row->setFontWeight('bold');
                            });
                        }
                        $sheet->row($row_no, $data);
                        $row_no++;
                    }

                    if($otherParams['boldLastRow']){
                        $sheet->row(($row_no-1), function($row){
                            $row->setFontWeight('bold');
                        });
                    }
                    // $columnFormat = ['R22'=>'\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT'];
                    // $sheet->setColumnFormat($columnFormat);

                    if(isset($otherParams['columnFormat']) && count($otherParams['columnFormat']) >0 ){
                        $sheet->setColumnFormat($otherParams['columnFormat']);
                    }

                    if(isset($otherParams['headingLabelArrayFilterRange'])){
                        $sheet->setAutoFilter($otherParams['headingLabelArrayFilterRange']);
                    }

                });
            }
            $excel->setActiveSheetIndex(0);
        });

        $oldFile = 'exports/'.$fileName.'.xlsx';
        if(Storage::disk('local')->has($oldFile)) {
            Storage::delete($oldFile);
        }

        $excelCreateObj->store('xlsx');
        $exportFile=storage_path('exports').'/'.$fileName.'.xlsx';
        return $exportFile;
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
        // ->whereBetween('defects.report_datetime',[$startDate,$endDate])
        ->whereDate('defects.report_datetime', '>=', $startDate)
        ->whereDate('defects.report_datetime', '<=', $endDate)
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

    public function downloadFleetCostReport($data, $reportDownload = null, $sheetTitle = null, $newReportData = null, $oldReportData = null)
    {
        if($newReportData) {
            $fileName = $sheetTitle;
            $sheetName = $data['report_name'];
        } else {
            $fileName = Carbon::now()->format('Ymd')."_FleetCosts_report_Standard";
            $sheetName = 'Fleet Costs Report';
        }

        $excelCreateObj = Excel::create($fileName, function($excel) use ($data, $reportDownload, $sheetName) {

            // Set the title
            $excel->setTitle($sheetName);

            // Chain the setters
            $excel->setCreator('FleetMaster')
                ->setCompany('iMaster');

            // Call them separately
            $excel->setDescription('This report keeps a track of all the defects recorded within a calendar month as they accumulate.');

            $excel->sheet('Report', function($sheet) use ($data, $reportDownload, $sheetName) {

                // if ($period == 'thisMonth') {
                //     $startDate = Carbon::now()->firstOfMonth();
                //     $endDate = Carbon::now()->endOfMonth();
                // } else {
                //     $startOfPerviousMonth = Carbon::now()->firstOfMonth()->subDays(2);
                //     $startDate = Carbon::parse($startOfPerviousMonth)->firstOfMonth();
                //     $endDate = Carbon::parse($startOfPerviousMonth)->endOfMonth();
                // }

                $startDate = Carbon::parse($data['date_from']);
                $endDate = Carbon::parse($data['date_to']);
                $duration = $startDate->format('d M Y') . ' to ' . $endDate->format('d M Y');

                $sheet->setAutoSize(true);

                $sheet->freezeFirstColumn();
                $sheet->cell('A1', function($cell) {$cell->setValue('Report')->setFontWeight('bold');   });
                $sheet->cell('B1', function($cell) use($sheetName) {$cell->setValue($sheetName);   });
                $sheet->cell('E1', function($cell) {$cell->setValue('New');   });
                $sheet->cell('F1', function($cell) {$cell->setValue('')->setBackground('#92d050');   });
                $sheet->mergeCells('B1:C1');

                $sheet->cell('A2', function($cell) {$cell->setValue('Description')->setFontWeight('bold');   });
                $sheet->cell('B2', function($cell) use($data) {$cell->setValue($data['report_description']);   });
                $sheet->cell('E2', function($cell) {$cell->setValue('Archived/Sold');   });
                $sheet->cell('F2', function($cell) {$cell->setValue('')->setBackground('#ff0000');   });
                $sheet->mergeCells('B2:C2');

                $sheet->cell('A3', function($cell) {$cell->setValue('Duration')->setFontWeight('bold');   });
                $sheet->cell('B3', function($cell) use($duration) {$cell->setValue($duration);   });
                $sheet->cell('E3', function($cell) {$cell->setValue('Transfer');   });
                $sheet->cell('F3', function($cell) {$cell->setValue('')->setBackground('#ffff00');   });
                $sheet->mergeCells('B3:C3');

                // $sheet->cell('F1', function($cell) use ($endDate) {$cell->setValue($endDate->format('d M Y'))->setFontWeight('bold');   });
                // $sheet->cell('H1', function($cell) {$cell->setValue('New')->setFontWeight('bold')->setBackground('#92d050');   });
                // $sheet->cell('I1', function($cell) {$cell->setValue('Archived/Sold')->setFontWeight('bold')->setBackground('#ff0000');   });
                // $sheet->cell('J1', function($cell) {$cell->setValue('Transfer')->setFontWeight('bold')->setBackground('#ffff00');   });
                // $sheet->mergeCells('B1:C1');
                //$sheet->cell('K1', function($cell) {$cell->setValue('No driver')->setFontWeight('bold')->setBackground('#0070c0');   });

                // $sheet->cell('M2', function($cell) {
                //     $cell->setValue('Cost')->setFontWeight('bold')->setBorder('solid');
                // });

                $sheet->cells("A1:F3", function ($cells) {

                    $cells->setFont(array(
                        'family' => 'Calibri',
                        'size' => '11',
                        // 'bold' => true
                    ));
                });

                // $sheet->mergeCells('M2:Z2');

                // $sheet->cells("M2:Z2", function ($cells) {

                //     $cells->setFont(array(
                //         'family'     => 'Calibri',
                //         'size'       => '11',
                //         'bold'       =>  true
                //     ));
                //     $cells->setFontColor("#000000");
                //     $cells->setAlignment('center');
                //     $cells->setValignment('center');
                //     $cells->setBorder('medium', 'medium', 'medium', 'medium');
                // });

                /*if($reportDownload) {
                    $secondHeadings = $reportDownload->reportDataset->pluck('title')->toArray();
                } else {
                    $secondHeadings = [
                        'Registration',
                        'Vehicle Division',
                        'Vehicle Region',
                        'Type',
                        'Operator License',
                        //'Nominated Driver',
                        'Ownership Status',
                        'Vehicle Status',
                        'Location',
                        'Date Added To Fleet',
                        'Location From',
                        'Location To',
                    ];
                }

                $fixedDataArr = ['',
                        'Lease Cost',
                        'Maintenance Cost',
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
                        'Transfer'];

                $secondHeadings = array_merge($secondHeadings, $fixedDataArr);*/

                $secondHeadings = [
                        'Registration',
                        'Vehicle Division',
                        'Vehicle Region',
                        'Location',
                        'Nominated Driver',
                        'Type',
                        'Operator License',
                        //'Nominated Driver',
                        'Ownership Status',
                        'Vehicle Status',
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

                $sheet->row(5,$secondHeadings);
                $sheet->cells("A5:AA5", function ($cells) {
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '11',
                        'bold'       =>  true
                    ));
                });

                $startRow = 6;
                $startColumn = 'A';
                $lastRow='AA';

                $fleetCostDataResult = $this->getFleetCostDataForReport($data, $secondHeadings);
                $sheet->fromArray($fleetCostDataResult['data'], null, $startColumn.$startRow, true, false);

                $TotalRow = count($fleetCostDataResult['data'])+$startRow-1;

                if (count($fleetCostDataResult['blue'])) {
                    foreach ($fleetCostDataResult['blue'] as $key) {
                        $rowNumber = $key+($startRow);
                        $sheet->cells('I'.$rowNumber.":I".$rowNumber, function ($cells) {
                            $cells->setBackground("#0070c0");
                            $cells->setFontColor('#ffffff');
                        });
                    }
                }

                if (count($fleetCostDataResult['green'])) {
                    foreach ($fleetCostDataResult['green'] as $key) {
                        $rowNumber = $key+($startRow);
                        $sheet->cells('J'.$rowNumber.":J".$rowNumber, function ($cells) {
                            $cells->setBackground("#92d050");
                            //$cells->setFontColor('#ffffff');
                        });
                    }
                }

                if (count($fleetCostDataResult['yellow'])) {
                    foreach ($fleetCostDataResult['yellow'] as $key) {
                        $rowNumber = $key+($startRow);
                        $sheet->cells('AB'.$rowNumber.":AB".$rowNumber, function ($cells) {
                            $cells->setBackground("#ffff00");
                        });
                    }
                }
                if (count($fleetCostDataResult['red'])) {
                    foreach ($fleetCostDataResult['red'] as $key) {
                        $rowNumber = $key+($startRow);
                        $sheet->cells('I'.$rowNumber.":I".$rowNumber, function ($cells) {
                            $cells->setBackground("#ff0000");
                        });
                    }
                }

                $rowNumber = $startRow;
                $endNumber = $startRow + count($fleetCostDataResult['data'])-1;
                $sheet->cells('N'.$rowNumber.":AA".$endNumber, function ($cells) {
                    $cells->setAlignment('right');
                });

                $sheet->cells('AA'.$rowNumber.":AA".$endNumber, function ($cells) {
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '11',
                        'bold'       =>  true
                    ));
                });

                $sheet->getStyle('N'.$rowNumber.":AA".$endNumber)->getNumberFormat()->setFormatCode('[$£-809]#,##0.00;[RED]-[$£-809]#,##0.00');

                $sheet->cells('A'.$TotalRow.":AA".$TotalRow, function ($cells) {
                    $cells->setFont(array(
                        'family'     => 'Calibri',
                        'size'       => '11',
                        'bold'       =>  true
                    ));
                });

            });

        });

        $oldFile = 'exports/'.$fileName.'.xlsx';
        if(Storage::disk('local')->has($oldFile)) {
            Storage::delete($oldFile);
        }
        $excelCreateObj->store('xlsx');
        $exportFile=storage_path('exports').'/'.$fileName.'.xlsx';
        return $exportFile;
    }

    private function  getFleetCostDataForReport($data, $secondHeadings) {
        // if ($type == 'thisMonth') {
        //     $startDate = Carbon::now()->firstOfMonth();
        //     $endDate = Carbon::now()->endOfMonth();
        //     $dateObj = Carbon::now();
        // } else {
        //     $startOfPerviousMonth = Carbon::now()->firstOfMonth()->subDays(2);
        //     $startDate = Carbon::parse($startOfPerviousMonth)->firstOfMonth();
        //     $endDate = Carbon::parse($startOfPerviousMonth)->endOfMonth();
        //     $dateObj = Carbon::now()->firstOfMonth()->subDays(2);
        // }

        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);
        $dateObj = Carbon::now();

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

        if(isset($data['accessible_regions'])) {
            $vehicles->whereIn('vehicles.vehicle_region_id', $data['accessible_regions']);
        }


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
            ->where(function ($query) use($dateObj) {
                   $query->whereRaw('MONTH(to_date) = '.$dateObj->subMonth(1)->format('m'))
                        ->orWhereRaw('MONTH(from_date) = '.$dateObj->subMonth(1)->format('m'));
            });
        if(isset($data['accessible_regions'])) {
            $previosMonthEntries = $previosMonthEntries->whereIn('vehicles.vehicle_region_id', $data['accessible_regions']);
        }
        $previosMonthEntries = $previosMonthEntries->orderBy('vehicle_assignment.to_date','DESC')
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
        $totalRow['division'] =  '';
        $totalRow['region'] = '';
        $totalRow['location'] =  '';
        $totalRow['nominated_driver'] =  '';
        $totalRow['type'] = '';
        $totalRow['operator_license'] = '';
        $totalRow['ownership_status'] = '';
        $totalRow['vehicle_status'] = '';
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

        $reportDatesArr = ['startDate' => $startDate, 'endDate' => $endDate];

        foreach ($vehicles as $registration => $group) {
            $group = $group->sortByDesc('vehAssId');

            foreach ($group as $key => $vehicle) {


                $vehicleId = $vehicle->vehId;
                $vehicleArchiveHistory = VehicleArchiveHistory::where('vehicle_id',$vehicleId)->orderBy('id','DESC')->first();
                $single = array();

                $originalFromDate = $vehicle->from_date;
                $originalToDate = $vehicle->to_date;

                if(in_array('Registration', $secondHeadings)) {
                    $single['registration'] = $vehicle->registration;
                }

                if(in_array('Vehicle Division', $secondHeadings)) {
                    $single['division'] =  $vehicle->division_name != null ? $vehicle->division_name : (isset($vehicle->division->name) ? $vehicle->division->name : '');
                }

                if(in_array('Vehicle Region', $secondHeadings)) {
                    $single['region'] = $vehicle->region_name != null ? $vehicle->region_name : (isset($vehicle->region->name) ? $vehicle->region->name : '');
                }

                if(in_array('Location', $secondHeadings)) {
                    $single['location'] =  $vehicle->name != null ? $vehicle->name : (isset($vehicle->location->name) ? $vehicle->location->name : '');
                }

                if(in_array('Nominated Driver', $secondHeadings)) {
                    $single['nominated_driver'] = isset($vehicle->nominatedDriver) ? $vehicle->nominatedDriver->first_name . " " . $vehicle->nominatedDriver->last_name : 'Unassigned';
                }

                if(in_array('Type', $secondHeadings)) {
                    $single['type'] = $vehicle->type->vehicle_type;
                }

                if(in_array('Operator License', $secondHeadings)) {
                    $single['operator_license'] = $vehicle->type->vehicle_type == 'HGV' ? $vehicle->operator_license : 'N/A';
                }
                //$single['nominated_driver'] = isset($vehicle->nominatedDriver->email) ? $vehicle->nominatedDriver->first_name . " " . $vehicle->nominatedDriver->last_name : 'Unassigned';

                /* if ($single['nominated_driver'] == 'Unassigned') {
                     array_push($finalArray['blue'], $i);
                 }*/

                if(in_array('Ownership Status', $secondHeadings)) {
                    $single['ownership_status'] = $vehicle->staus_owned_leased;
                }

                if(in_array('Vehicle Status', $secondHeadings)) {
                    $single['vehicle_status'] = $vehicle->status;
                }
                
                if(in_array('Date Added To Fleet',  $secondHeadings)) {
                    $single['date_added_on_fleet'] = $vehicle->dt_added_to_fleet;
                }

                $dateAddedOnFleet = $vehicle->dt_added_to_fleet != null ? Carbon::parse($vehicle->dt_added_to_fleet) : null;
                if ($vehicle->vlid != null) {

                    if (count($group) == 1 && isset($previosMonthEntries[$vehicle->vehId])) {

                        if(in_array('Location From', $secondHeadings)) {
                            $single['location_from'] = $previosMonthEntries[$vehicle->vehId][0]['name'];
                        }

                        if(in_array('Location To', $secondHeadings)) {
                            $single['location_to'] = $vehicle->name;
                        }

                    } else {
                        if ($key == 0) {

                            if(in_array('Location From', $secondHeadings)) {
                                $single['location_from'] = $vehicle->name;
                            }

                            if ($vehicle->to_date != null) {
                                if(in_array('Location To', $secondHeadings)) {
                                    $single['location_to'] = isset($group[$key + 1]) ? $group[$key + 1]->name : 'N/A';
                                }
                                $vehicle->from_date = isset($group[$key + 1]) ?  $group[$key + 1]->from_date :  $group[$key]->from_date;
                            } else {

                                if(in_array('Location To', $secondHeadings)) {
                                    $single['location_to'] = $vehicle->name;
                                }

                                if(in_array('Location From', $secondHeadings)) {
                                    $single['location_from'] = (isset($group[$key - 1]->name) && $group[$key - 1]->name!="") ? $group[$key - 1]->name : 'N/A';
                                }
                            }

                        } else {
                            if(in_array('Location From', $secondHeadings)) {
                                $single['location_from'] = (isset($group[$key - 1]->name) && $group[$key - 1]->name!="") ? $group[$key - 1]->name : 'N/A';
                            }

                            if(in_array('Location To', $secondHeadings)) {
                                $single['location_to'] = $vehicle->name;
                            }
                        }
                    }
                } else {
                    if(in_array('Location From', $secondHeadings)) {
                        $single['location_from'] = 'N/A';
                    }

                    if(in_array('Location To', $secondHeadings)) {
                        $single['location_to'] = 'N/A';
                    }
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
                    $single['lease_cost'] = $this->fleetCostReportCost($vehicle->lease_cost,'currentCost',$originalFromDate,$originalToDate,$reportDatesArr,$vehicleId,$vehicleArchiveHistory);

                    //$maintenance = $commonHelper->calcMonthlyCurrentData($vehicle->maintenance_cost, $date, $vehicleId, $vehicleArchiveHistory);
                    $single['maintenance_cost'] =  $this->fleetCostReportCost($vehicle->maintenance_cost,'currentCost',$originalFromDate,$originalToDate,$reportDatesArr,$vehicleId,$vehicleArchiveHistory,null,null,1);
                    //$depreciation = $commonHelper->calcMonthlyCurrentData($vehicle->monthly_depreciation_cost, $date, $vehicleId, $vehicleArchiveHistory);
                    $single['depreciation_cost'] = $this->fleetCostReportCost($vehicle->monthly_depreciation_cost,'currentCost',$originalFromDate,$originalToDate,$reportDatesArr,$vehicleId,$vehicleArchiveHistory);

                    //$taxCost = $commonHelper->calcMonthlyCurrentData($vehicle->type->vehicle_tax, $date,$vehicleId,$vehicleArchiveHistory,$vehicle->dt_added_to_fleet,'N/A','N/A',null,'vehicle_tax');

                    $tax = $this->fleetCostReportCost($vehicle->type->vehicle_tax,'currentCost',$originalFromDate,$originalToDate,$reportDatesArr,$vehicleId,$vehicleArchiveHistory,$vehicle->dt_added_to_fleet);

                    $single['vehicle_tax'] = $tax;

                    $insuranceValueJsonValue = '';
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
                    $single['insurance_cost'] = $this->fleetCostReportCost($insuranceValueJsonValue,'currentCost',$originalFromDate,$originalToDate,$reportDatesArr,$vehicleId,$vehicleArchiveHistory,$vehicle->dt_added_to_fleet,$vehicle->is_insurance_cost_override);

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
                        //$single['telematics_cost'] = $this->fleetCostReportCost($telematics, 'currentCost, $originalFromDate, $originalToDate, $reportDatesArr,$vehicleId,'telematics_cost',$vehicle->dt_added_to_fleet,'N/A',$vehicle->is_telematics_cost_override);
                        $single['telematics_cost'] = $this->fleetCostReportCost($telematicsJsonValue,'currentCost',$originalFromDate,$originalToDate,$reportDatesArr,$vehicleId,$vehicleArchiveHistory,$vehicle->dt_added_to_fleet,$vehicle->is_telematics_cost_override);
                    } else {
                        $single['telematics_cost'] = 'N/A';
                    }

                    $manualCostAdjustment = json_decode($vehicle->manual_cost_adjustment, true);
                    if (isset($manualCostAdjustment)) {
                        //$manualCostAdjustment = $commonHelper->calcCurrentMonthBasedOnPeriod($manualCostAdjustment, $date, $vehicleId, $vehicleArchiveHistory,'manual_cost_adjustment');
                        //$single['manual_cost_adjustment'] = $this->calculateCost($manualCostAdjustment,'currentCost',$originalFromDate,$originalToDate,$reportDatesArr,$vehicleId,'manual_cost_adjustment');
                        $single['manual_cost_adjustment'] = $this->fleetCostReportCost($manualCostAdjustment,'manualCost',$originalFromDate,$originalToDate,$reportDatesArr,$vehicleId,$vehicleArchiveHistory);
                    } else {
                        $single['manual_cost_adjustment'] = '0.00';
                    }

                    $fuelCost = json_decode($vehicle->fuel_use, true);
                    if (isset($fuelCost)) {
                        //$fuelCost = $commonHelper->calcCurrentMonthBasedOnPeriod($fuelCost, $date, $vehicleId, $vehicleArchiveHistory);
                        //$single['fuel'] = $this->calculateCost($fuelCost,'manualCost',$originalFromDate,$originalToDate,$reportDatesArr,$vehicleId,'fuel');
                        $single['fuel'] = $this->fleetCostReportCost($fuelCost,'manualCost',$originalFromDate,$originalToDate,$reportDatesArr,$vehicleId,$vehicleArchiveHistory);
                    } else {
                        $single['fuel'] = (string)'0.00';
                    }

                    $oilCost = json_decode($vehicle->oil_use, true);

                    if (isset($oilCost)) {
                        //$oilCost = $commonHelper->calcCurrentMonthBasedOnPeriod($oilCost, $date, $vehicleId, $vehicleArchiveHistory);
                        //$single['oil'] = $this->calculateCost($oilCost,'manualCost',$originalFromDate,$originalToDate,$startDate);
                        $single['oil'] = $this->fleetCostReportCost($oilCost,'manualCost',$originalFromDate,$originalToDate,$reportDatesArr,$vehicleId,$vehicleArchiveHistory);
                    } else {
                        $single['oil'] = (string)'0.00';
                    }

                    $adBlueCost = json_decode($vehicle->adblue_use, true);

                    if (isset($adBlueCost)) {
                        //$adBlue = $commonHelper->calcCurrentMonthBasedOnPeriod($adBlueCost, $date, $vehicleId, $vehicleArchiveHistory);
                        $single['adBlue'] = $this->fleetCostReportCost($adBlueCost,'manualCost',$originalFromDate,$originalToDate,$reportDatesArr,$vehicleId,$vehicleArchiveHistory);
                    } else {
                        $single['adBlue'] = (string)'0.00';
                    }

                    $screenWashCost = json_decode($vehicle->screen_wash_use, true);

                    if (isset($screenWashCost)) {
                        //$screenUse = $commonHelper->calcCurrentMonthBasedOnPeriod(json_decode($vehicle->screen_wash_use, true), $date, $vehicleId, $vehicleArchiveHistory);
                        $single['screen_wash_use'] = $this->fleetCostReportCost($screenWashCost,'manualCost',$originalFromDate,$originalToDate,$reportDatesArr,$vehicleId,$vehicleArchiveHistory);
                    } else {
                        $single['screen_wash_use'] = (string)'0.00';
                    }

                    $liveryUseCost = json_decode($vehicle->fleet_livery_wash, true);

                    if (isset($liveryUseCost)) {
                        //$liveryUse = $commonHelper->calcCurrentMonthBasedOnPeriod($liveryUseCost, $date, $vehicleId, $vehicleArchiveHistory);
                        $single['fleet_livery_wash'] = $this->fleetCostReportCost($liveryUseCost,'manualCost',$originalFromDate,$originalToDate,$reportDatesArr,$vehicleId,$vehicleArchiveHistory);
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

                    // $single['transfer'] = $vehicle->from_date != null && Carbon::parse($vehicle->from_date)->format('m') ==  Carbon::parse($date)->format('m') ? Carbon::parse($vehicle->from_date)->format('d M Y') : '';

                    $single['transfer'] = $vehicle->from_date != null && (Carbon::parse($vehicle->from_date)->format('m') ==  Carbon::parse($startDate)->format('m') || Carbon::parse($vehicle->from_date)->format('m') ==  Carbon::parse($endDate)->format('m')) ? Carbon::parse($vehicle->from_date)->format('d M Y') : '';

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
                    $rowNumber = count($finalArray['data'])+6;
                    $single['total'] = '=SUM(N'.$rowNumber.':Z'.$rowNumber.')';

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

        $endRow = count($finalArray['data'])+4;
        $totalRow['lease_cost'] = '=SUM(N4:N'.$endRow.')';
        $totalRow['maintenance_cost'] = '=SUM(O4:O'.$endRow.')';
        $totalRow['depreciation_cost'] = '=SUM(P4:P'.$endRow.')';
        $totalRow['vehicle_tax'] = '=SUM(Q4:Q'.$endRow.')';
        $totalRow['insurance_cost'] = '=SUM(R4:R'.$endRow.')';
        $totalRow['telematics_cost'] = '=SUM(S4:S'.$endRow.')';
        $totalRow['manual_cost_adjustment'] = '=SUM(T4:T'.$endRow.')';
        $totalRow['fuel'] = '=SUM(U4:U'.$endRow.')';
        $totalRow['oil'] = '=SUM(V4:V'.$endRow.')';
        $totalRow['adBlue'] = '=SUM(W4:W'.$endRow.')';
        $totalRow['screen_wash_use'] = '=SUM(X4:X'.$endRow.')';
        $totalRow['fleet_livery_wash'] = '=SUM(Y4:Y'.$endRow.')';
        $totalRow['defects'] = '=SUM(Z4:Z'.$endRow.')';
        $totalRow['total'] = '=SUM(AA4:AA'.$endRow.')';

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

    // private function fleetCostReportCost($costArray,$type = 'currentCost',$from_date,$to_date,$reportDatesArr,$vehicleId = 0,$vehicleArchiveHistory = null,$vehicleDtAddedToFleet = null, $isOverrideCost = null,$debug = 0, $endDate = null)
    // {
    //         $startDate = $reportDatesArr['startDate'];
    //         $endDate = $reportDatesArr['endDate'];

    //         if ($type == 'currentCost') {
    //             $costArray = json_decode($costArray, true);
    //         }

    //         $totalDaysInMonth = Carbon::parse($startDate)->daysInMonth;
    //         $cost = 0;

    //         /*if($from_date == null) {
    //             $fromDateObj = Carbon::parse($startDate)->firstOfMonth();
    //         } else {
    //             $fromDateObj = Carbon::parse($from_date);
    //         }

    //         if ($fromDateObj->lt(Carbon::parse($startDate)->firstOfMonth())) {
    //             $fromDateObj = Carbon::parse($startDate)->firstOfMonth();
    //         }

    //         $toMonthYear = $currentMonthYear = Carbon::parse($startDate)->format('m-Y');

    //         if ($to_date == null ) {
    //             $toDateObj = Carbon::parse($startDate)->endOfMonth();
    //         } else {
    //             $toDateObj = Carbon::parse($to_date);
    //         }*/

    //         if($from_date == null) {
    //             $fromDateObj = Carbon::parse($startDate);
    //         } else {
    //             $fromDateObj = Carbon::parse($from_date);
    //         }

    //         $toMonthYear = $currentMonthYear = Carbon::parse($startDate)->format('m-Y');

    //         if ($to_date == null ) {
    //             $toDateObj = Carbon::parse($endDate);
    //         } else {
    //             $toDateObj = Carbon::parse($to_date);
    //         }

    //         // if ($toDateObj->gt(Carbon::parse($startDate)->endOfMonth())) {
    //         //     $toDateObj = Carbon::parse($startDate)->endOfMonth();
    //         // }
    //         if($endDate) {
    //             // if ($toDateObj->lt(Carbon::parse($endDate)->endOfMonth())) {
    //             //     $toDateObj = Carbon::parse($endDate)->endOfMonth();
    //             // }
    //             $toMonthYear = Carbon::parse($endDate)->format('m-Y');
    //         }


    //         if ($vehicleArchiveHistory != null && $vehicleArchiveHistory->event == 'Archived') {
    //             $archiveDate = Carbon::parse($vehicleArchiveHistory->event_date_time)->format('Y-m-d');
    //             $archiveDateObj = Carbon::parse($archiveDate);

    //             if ($archiveDateObj->lt($fromDateObj)) {
    //                 return (float) 0;
    //             }

    //             if ($toDateObj->gt($archiveDateObj)) {
    //                 $toDateObj = Carbon::parse($archiveDate);
    //             }

    //             if ($toDateObj->gt(Carbon::parse($startDate)->endOfMonth())) {
    //                 $toDateObj = Carbon::parse($startDate)->endOfMonth();
    //             }
    //         }

    //         $checkDateAddedToFleet = 0;

    //         if (isset($costArray[0]['json_type']) && $costArray[0]['json_type'] == 'monthlyVehicleTax' && $vehicleDtAddedToFleet != null) {
    //             $checkDateAddedToFleet = 1;
    //         }

    //         if ($isOverrideCost === 0 && $vehicleDtAddedToFleet != null) {
    //             $checkDateAddedToFleet = 1;
    //         }

    //         if ($checkDateAddedToFleet == 1) {
    //             $vehicleDtAddedToFleetObject = Carbon::parse($vehicleDtAddedToFleet);

    //             if ($vehicleDtAddedToFleetObject->gt($fromDateObj)) {
    //                 $fromDateObj =  Carbon::parse($vehicleDtAddedToFleet);
    //             }

    //             /*if ($fromDateObj->lt(Carbon::parse($startDate)->firstOfMonth())) {
    //                 $fromDateObj = Carbon::parse($startDate)->firstOfMonth();
    //             }*/

    //         }

    //         if ($fromDateObj->gt($toDateObj)) {
    //             return 0;
    //         }

    //         if($costArray != null && count($costArray) > 0) {

    //             foreach ($costArray as $singleCost) {

    //                 $costFromDateObj = Carbon::parse($singleCost['cost_from_date']);

    //                 if (isset($singleCost['cost_continuous']) && $singleCost['cost_continuous'] == 'true') {
    //                     $costToDateObj = Carbon::parse($startDate)->endOfMonth();
    //                     $costToMonthYear =  Carbon::parse($startDate)->endOfMonth()->format('m-Y');
    //                 } else {
    //                     $costToDateObj = Carbon::parse($singleCost['cost_to_date']);
    //                     $costToMonthYear =  Carbon::parse($singleCost['cost_to_date'])->format('m-Y');
    //                 }

    //                 $costFromMonthYear = Carbon::parse($singleCost['cost_from_date'])->format('m-Y');

    //                 if ($checkDateAddedToFleet ==  1 && $costToDateObj->lt($vehicleDtAddedToFleetObject)) {
    //                     continue;
    //                 }

    //                 if ($costToDateObj->lt($fromDateObj)) {
    //                     continue;
    //                 }

    //                 if (
    //                     $costFromDateObj->lt(Carbon::parse($startDate)->firstOfMonth())
    //                     && $costToDateObj->gt(Carbon::parse($startDate)->endOfMonth())
    //                     && $costFromDateObj->lte($toDateObj)
    //                 ) {
    //                     $days = $fromDateObj->diffInDays($toDateObj) + 1;

    //                 } else {
    //                     if ( ($currentMonthYear == $costFromMonthYear || $currentMonthYear == $costToMonthYear) || ( $toMonthYear == $costFromMonthYear || $toMonthYear == $costToMonthYear) ) {

    //                         if (
    //                             $fromDateObj->gte($costFromDateObj) && $fromDateObj->lte($costToDateObj)
    //                             &&
    //                             $toDateObj->gte($costFromDateObj) && $toDateObj->lte($costToDateObj)
    //                         ) {
    //                             $days = $fromDateObj->diffInDays($toDateObj) + 1;

    //                         } else if (
    //                             $fromDateObj->gte($costFromDateObj) && $toDateObj->gte($costToDateObj)
    //                         ) {
    //                             $days = $fromDateObj->diffInDays($costToDateObj) + 1;

    //                         } else if (
    //                             $fromDateObj->lte($costFromDateObj) && $toDateObj->lte($costToDateObj) && $toDateObj->gte($costFromDateObj)
    //                         ) {
    //                             $days = $costFromDateObj->diffInDays($toDateObj) + 1;

    //                         } else {
    //                             $days = 0;
    //                             if ($toDateObj->lt($costFromDateObj)) {
    //                                 $days = 0;
    //                             } else {
    //                                 $days = $costFromDateObj->diffInDays($costToDateObj) + 1;
    //                             }
    //                         }
    //                     } else {
    //                         $days = 0 ;
    //                     }
    //                 }

    //                 if ($type == 'currentCost') {
    //                     $currentCost = (float)$singleCost['cost_value']/$totalDaysInMonth*$days;
    //                 } else {
    //                     $totalRangeDays = $costFromDateObj->diffInDays($costToDateObj) + 1;
    //                     $currentCost = (float)$singleCost['cost_value']/$totalRangeDays*$days;
    //                 }

    //                 $cost = (float)$cost + (float)$currentCost;
    //             }

    //             return $cost;
    //         } else {
    //             return 0;
    //         }
    // }

    private function fleetCostReportCost($costArray,$type = 'currentCost',$from_date,$to_date,$reportDatesArr,$vehicleId = 0,$vehicleArchiveHistory = null,$vehicleDtAddedToFleet = null, $isOverrideCost = null,$debug = 0) 
    {
        $startDate = $reportDatesArr['startDate'];

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

    public function downloadLastLogin($data, $reportDownload = null, $sheetTitle = null, $newReportData = null, $oldReportData = null)
    {
        if($reportDownload) {
            $lableArray = $reportDownload->reportDataset->pluck('title')->toArray();
        } else {
            $lableArray = $this->getReportLabels('standard_last_login_report');
        }
        $sortBy = $this->setOrderBy('standard_last_login_report');

        $sheetDetail = [];

        $activeUsersData = $this->getUserLastLoginData($data, $lableArray, $sortBy, true);
        $archivedUsersData = $this->getUserLastLoginData($data, $lableArray, $sortBy, false);

        $finalArray = ['Active Users' => $activeUsersData, 'Archived Users' => $archivedUsersData];

        $currentDate = Carbon::now()->format('d M Y');
        if($newReportData) {
            $otherParams = $this->customReportOtherParam($currentDate, $sheetTitle, $newReportData, $oldReportData);
            $cellRange = range('A', 'Z');
            $headingLabelArrayFilterRange = "A6:".$cellRange[count($activeUsersData[0])-1]."6";
            $otherParams['headingLabelArrayFilterRange'] = $headingLabelArrayFilterRange;
        } else {
            $headingLabelArrayFilterRange = "A6:K6";
            $otherParams = [
                'fileName' => Carbon::now()->format('Ymd')."_LastLogin_report_Standard",
                'sheetTitle_lable' => "Report",
                'sheetTitle_value' => "Last Login Report",
                'sheetSubTitle_lable_first' => "Description",
                'sheetSubTitle_value_first' => $data['report_description'],
                'sheetSubTitle_lable_second' => "Duration",
                'sheetSubTitle_value_second' => $currentDate,
                'sheetName' => $currentDate,
                'boldLastRow' => false,
                'headingLabelArrayFilterRange' => $headingLabelArrayFilterRange
            ];
        }

        // return $this->toExcel($lableArray,$dataArray,$otherParams);
        return $this->toExcelMulti($lableArray,$finalArray,$otherParams);

    }

    private function getUserLastLoginData($data, $lableArray, $sortBy, $isActive)
    {
        $dataArray = [];
        $users = $this->customReportRepository->lastLoginReportData($data, $sortBy, $isActive);
        if(isset($data['accessible_regions'])) {
            $users = $users->where(function ($query) use ($data) {
                            $query->whereNull('users.user_region_id');
                            $query->orWhereIn('users.user_region_id', $data['accessible_regions']);
                        });
        }
        $users = $users->get();
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
            if($user->last_login == '0000-00-00 00:00:00' || empty($user->last_login)) {
                $user->last_login = 'No login data recorded';
            }

            $lastLoginData = [];
            foreach($lableArray as $label) {
                if($label == 'Driver First Name' || $label == 'First Name') {
                    $lastLoginData[] = $user->first_name;
                } else if($label == 'Driver Last Name' || $label == 'Last Name') {
                    $lastLoginData[] = $user->last_name;
                } else if($label == 'Company') {
                    $lastLoginData[] = $user->company->name;
                } else if($label == 'User Division' || $label == 'Division') {
                    $lastLoginData[] = $user->userDivision ? $user->userDivision->name : '';
                } else if($label == 'User Region' || $label == 'Region') {
                    $lastLoginData[] = $user->userRegion ? $user->userRegion->name : '';
                } else if($label == 'Username') {
                    $lastLoginData[] = $user->username;
                } else if($label == 'Email') {
                    $lastLoginData[] = $user->email;
                } else if($label == 'Mobile Number' || $label == 'Mobile') {
                    $lastLoginData[] = $user->mobile;
                } else if($label == 'Roles') {
                    $lastLoginData[] = $role;
                } else if($label == 'Last Login') {
                    $lastLoginData[] = $user->last_login;
                } else if($label == 'Is Archived?') {
                    $lastLoginData[] = $user->is_disabled ? "Yes" : "No";
                }
            }

            array_push($dataArray, $lastLoginData);
        }

        return $dataArray;
    }

    public function customReportOtherParam($reportDate, $sheetTitle, $newReportData, $oldReportData)
    {
        return $otherParams = [
                    'fileName' => $sheetTitle,
                    'sheetTitle_lable' => "Report",
                    'sheetTitle_value' => $newReportData->name,
                    'sheetSubTitle_lable_first' => "Description",
                    'sheetSubTitle_value_first' => $newReportData->description,
                    'sheetSubTitle_lable_second' => "Duration",
                    'sheetSubTitle_value_second' => $reportDate,
                    'sheetName' => $reportDate,
                    'boldLastRow' => false
                ];
    }

    public function getUserRegions($field = 'name')
    {
        if(Auth::check()) {
            $allRegions = Auth::user()->regions->lists($field)->toArray();
        } else {
            $resultVehicle = (new UserService())->getAllVehicleLinkedData();
            // $allRegions = $resultVehicle['vehicleRegions'];
            $allRegions = $resultVehicle['vehicleOnlyRegions'];
            if($field == 'id') {
                $allRegions = array_keys($allRegions);
            }
        }

        return $allRegions;
    }

    public function downloadDrivingEvents($data, $reportDownload = null, $sheetTitle = null, $newReportData = null, $oldReportData = null)
    {
        if($reportDownload) {
            $lableArray = $reportDownload->reportDataset->pluck('title')->toArray();
        } else {
            $lableArray = $this->getReportLabels('standard_driving_events_report');
        }

        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);

        $dataArray = [];
        $incidents = config('config-variables.telematics_incidents');
        $ns = array_keys($incidents);

        $drivingEvents = $this->customReportRepository->drivingEventData($data, $ns);

        $drivingEvents = $drivingEvents->get();

        foreach($drivingEvents as $event) {
            $drivingEventsData = [];
            foreach($lableArray as $label) {
                if($label == 'Registration') {
                    $drivingEventsData[] = $event->registration;
                } else if($label == 'Vehicle Division') {
                    $drivingEventsData[] = $event->vehicle_division_id;
                } else if($label == 'Vehicle Region') {
                    $drivingEventsData[] = $event->vehicle_region_id;
                } else if($label == 'Driver First Name' || $label == 'First Name') {
                    $drivingEventsData[] = $event->first_name;
                } else if($label == 'Driver Last Name' || $label == 'Last Name') {
                    $drivingEventsData[] = $event->last_name;
                } else if($label == 'Date') {
                    $drivingEventsData[] = $event->incident_date;
                } else if($label == 'Time') {
                    $drivingEventsData[] = $event->incident_time;
                } else if($label == 'Incident') {
                    $drivingEventsData[] = isset($incidents[$event->ns]) ? $incidents[$event->ns] : $event->ns;
                } else if($label == 'Location') {
                    $drivingEventsData[] = $event->street;
                }
            }
            array_push($dataArray, $drivingEventsData);
        }

        $reportTitle = $startDate->format('d M Y')." - ".$endDate->format('d M Y');

        if($newReportData) {
            $otherParams = $this->customReportOtherParam($reportTitle, $sheetTitle, $newReportData, $oldReportData);
        } else {
            $otherParams = [
                'fileName' => Carbon::now()->format('Ymd')."_DrivingEvents_report_Standard",
                'sheetTitle_lable' => "Report",
                'sheetTitle_value' => "Driving Events Report Standard",
                'sheetSubTitle_lable_first' => "Description",
                'sheetSubTitle_value_first' => $data['report_description'],
                'sheetSubTitle_lable_second' => "Duration",
                'sheetSubTitle_value_second' => $reportTitle,
                'sheetName' => $reportTitle,
                'boldLastRow' => false
            ];
        }

        return $this->toExcel($lableArray,$dataArray,$otherParams);
    }

    public function downloadSpeedingReport($data, $reportDownload = null, $sheetTitle = null, $newReportData = null, $oldReportData = null)
    {
        if($reportDownload) {
            $lableArray = $reportDownload->reportDataset->pluck('title')->toArray();
        } else {
            $lableArray = $this->getReportLabels('standard_speeding_report');
        }

        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);

        $dataArray = [];
        $incidents = config('config-variables.telematics_incidents');
        $ns = 'tm8.dfb2.spdinc';

        $speedingEvent = $this->customReportRepository->speedingData($data, $ns);

        $speedingEvent = $speedingEvent->get();

        foreach($speedingEvent as $event) {
            $speedingEventData = [];
            foreach($lableArray as $label) {
                if($label == 'Registration') {
                    $speedingEventData[] = $event->registration;
                } else if($label == 'Vehicle Division') {
                    $speedingEventData[] = $event->vehicle_division_id;
                } else if($label == 'Vehicle Region') {
                    $speedingEventData[] = $event->vehicle_region_id;
                } else if($label == 'Vehicle Status') {
                    $speedingEventData[] = $event->status;
                } else if($label == 'Driver First Name' || $label == 'First Name') {
                    $speedingEventData[] = $event->first_name;
                } else if($label == 'Driver Last Name' || $label == 'Last Name') {
                    $speedingEventData[] = $event->last_name;
                } else if($label == 'Speed(MPH)') {
                    $speedingEventData[] = $this->mpsToMph($event->speed);
                } else if($label == 'Speed Limit(MPH)') {
                    $speedingEventData[] = number_format(round((float)$this->mpsToMph($event->street_speed),0,PHP_ROUND_HALF_UP),2);
                } else if($label == 'Date') {
                    $speedingEventData[] = $event->incident_date;
                } else if($label == 'Time') {
                    $speedingEventData[] = $event->incident_time;
                } else if($label == 'Incident') {
                    $speedingEventData[] = isset($incidents[$event->ns]) ? $incidents[$event->ns] : $event->ns;
                } else if($label == 'Location') {
                    $speedingEventData[] = $event->street;
                }
            }
            array_push($dataArray, $speedingEventData);
        }

        $reportTitle = $startDate->format('d M Y')." - ".$endDate->format('d M Y');

        if($newReportData) {
            $otherParams = $this->customReportOtherParam($reportTitle, $sheetTitle, $newReportData, $oldReportData);
        } else {
            $otherParams = [
                'fileName' => Carbon::now()->format('Ymd')."_SpeedingEvents_report_Standard",
                'sheetTitle_lable' => "Report",
                'sheetTitle_value' => "Speeding Events Report Standard",
                'sheetSubTitle_lable_first' => "Description",
                'sheetSubTitle_value_first' => $data['report_description'],
                'sheetSubTitle_lable_second' => "Duration",
                'sheetSubTitle_value_second' => $reportTitle,
                'sheetName' => $reportTitle,
                'boldLastRow' => false
            ];
        }

        return $this->toExcel($lableArray,$dataArray,$otherParams);
    }

    public function downloadJourneyReport($data, $reportDownload = null, $sheetTitle = null, $newReportData = null, $oldReportData = null)
    {
        if($reportDownload) {
            $lableArray = $reportDownload->reportDataset->pluck('title')->toArray();
        } else {
            $lableArray = $this->getReportLabels('standard_journey_report');
        }

        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);

        $dataArray = [];

        $journeys = $this->customReportRepository->getJourneyDataForReport($data);

        $journeys = $journeys->selectRaw('telematics_journeys.*, CASE WHEN (j.fuel = 0 and j.gps_distance < 1609.34) THEN "< '.env('MIN_JOURNEY_FUEL').'" ELSE fuel END AS fuel, CASE WHEN (j.co2 = 0 and j.gps_distance < 1609.34) THEN "< '.env('MIN_JOURNEY_CO2').'" ELSE fuel END AS co2, vehicles.registration, CASE WHEN telematics_journeys.user_id = 1 THEN "Driver" ELSE users.first_name END as first_name, CASE WHEN telematics_journeys.user_id = 1 THEN "Unknown" ELSE users.last_name END as last_name, DATE_FORMAT(CONVERT_TZ(start_time, "UTC","'.config('config-variables.format.displayTimezone').'"), "%H:%i:%s %d %b %Y") as start_time,
            DATE_FORMAT(CONVERT_TZ(end_time, "UTC","'.config('config-variables.format.displayTimezone').'"),"%H:%i:%s %d %b %Y") AS end_time, vehiclefuelsum, vehicledistancesum, vehicle_regions.name as region_name, vehicle_divisions.name as division_name')
                            ->get();

        foreach($journeys as $journey) {
            $locationStart = $this->fetchLocation($journey);
            $locationEnd = $journey->end_street." ".$journey->end_town." ".$journey->end_post_code;

            $journeyData = [];
            foreach($lableArray as $label) {
                if($label == 'Registration') {
                    $journeyData[] = $journey->registration;
                } else if($label == 'Vehicle Division') {
                    $journeyData[] = $journey->division_name;
                } else if($label == 'Vehicle Region') {
                    $journeyData[] = $journey->region_name;
                } else if($label == 'Driver First Name' || $label == 'First Name') {
                    $journeyData[] = $journey->first_name;
                } else if($label == 'Driver Last Name' || $label == 'Last Name') {
                    $journeyData[] = $journey->last_name;
                } else if($label == 'Journey Start Time') {
                    $journeyData[] = $journey->start_time;
                } else if($label == 'Journey End Time') {
                    $journeyData[] = $journey->end_time;
                } else if($label == 'Journey Duration(HH:MM:SS)') {
                    $journeyData[] = readableTimeFomatForReports($journey->engine_duration);
                } else if($label == 'Journey Distance(Miles)') {
                    $journeyData[] = number_format($journey->gps_distance * 0.00062137, 2, '.', '');
                } else if($label == 'Start Location') {
                    $journeyData[] = $locationStart;
                } else if($label == 'End Location') {
                    $journeyData[] = $locationEnd;
                } else if($label == 'Number of Incidents') {
                    $journeyData[] = $journey->incident_count;
                } else if($label == 'Fuel') {
                    $journeyData[] = $journey->fuel;
                } else if($label == 'MPG(Actual)') {
                    $journeyData[] = $this->calculationActualMPG($journey);
                } else if($label == 'MPG(Expected)') {
                    $journeyData[] = $this->calculationExpectedMPG($journey);
                } else if($label == 'Journey CO2') {
                    $journeyData[] = $journey->co2;
                }
            }
            array_push($dataArray, $journeyData);
        }

        $reportTitle = $startDate->format('d M Y')." - ".$endDate->format('d M Y');

        if($newReportData) {
            $otherParams = $this->customReportOtherParam($reportTitle, $sheetTitle, $newReportData, $oldReportData);
        } else {
            $otherParams = [
                'fileName' => Carbon::now()->format('Ymd')."_Journey_report_Standard",
                'sheetTitle_lable' => "Report",
                'sheetTitle_value' => "Journey Report",
                'sheetSubTitle_lable_first' => "Description",
                'sheetSubTitle_value_first' => $data['report_description'],
                'sheetSubTitle_lable_second' => "Duration",
                'sheetSubTitle_value_second' => $reportTitle,
                'sheetName' => $reportTitle,
                'boldLastRow' => false
            ];
        }

        return $this->toExcel($lableArray,$dataArray,$otherParams);
    }

    private function calculationActualMPG($journey)
    {
        $gallons = floatval($journey->fuel * 0.264172);
        $miles = floatval($journey->gps_distance * 0.00062137);
        $mpg = 0;
        if ($gallons && $gallons != null && $gallons != 0) {
            $mpg = $gallons == 0 ? 0 : round(floatval($miles / $gallons), 2);
        }

        return $mpg;
    }

    private function calculationExpectedMPG($journey)
    {
        $gallonsExpected = round(floatval($journey->vehiclefuelsum * 0.264172), 2);
        $milesExpected = round(floatval($journey->vehicledistancesum * 0.00062137), 2);
        $mpgExpectedValue = 0;
        if (
            $journey->vehiclefuelsum &&
            $journey->vehiclefuelsum != null &&
            $journey->vehiclefuelsum != 0
        ) {
            $mpgExpectedValue = $gallonsExpected == 0 ? 0 : round(floatval($milesExpected / $gallonsExpected), 2);
        }
        return $mpgExpectedValue;
    }

    public function downloadFuelUsageAndEmissionReport($data, $reportDownload = null, $sheetTitle = null, $newReportData = null, $oldReportData = null)
    {
        // $response = [];
        if($reportDownload) {
            $lableArray = $reportDownload->reportDataset->pluck('title')->toArray();
        } else {
            $lableArray = $this->getReportLabels('standard_fuel_usage_and_emission_report');
        }
        $sortBy = $this->setOrderBy('standard_fuel_usage_and_emission_report');

        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);

        $dataArray = [];

        $journeys = $this->customReportRepository->getJourneyDataForReport($data);

        $journeys = $journeys->selectRaw('telematics_journeys.*, vehicles.registration, SUM(fuel) as fuel, SUM(co2) as co2, SUM(engine_duration) as engine_duration, SUM(gps_distance) as gps_distance, SUM(gps_idle_duration) as gps_idle_duration, SUM(gps_idle_duration) as gps_idle_duration, vehiclefuelsum, vehicledistancesum, vehicle_divisions.name as division_name, vehicle_regions.name as region_name')
                            ->orderBy($sortBy)
                            ->groupBy('telematics_journeys.vehicle_id')->get();

        foreach($journeys as $journey) {
            $i = 0;
            $journeyData = [];
            foreach($lableArray as $label) {
                if($label == 'Registration') {
                    $journeyData[$i] = $journey->registration;
                } else if($label == 'Vehicle Division') {
                    $journeyData[$i] = $journey->division_name;
                } else if($label == 'Vehicle Region') {
                    $journeyData[$i] = $journey->region_name;
                } else if($label == 'Journey Duration(HH:MM:SS)') {
                    $journeyData[$i] = readableTimeFomatForReports($journey->engine_duration);
                } else if($label == 'Journey Distance(Miles)') {
                    $journeyData[$i] = number_format($journey->gps_distance * 0.00062137, 2, '.', '');
                } else if($label == 'Actual Driving Time(HH:MM:SS)') {
                    $journeyData[$i] = readableTimeFomatForReports($journey->engine_duration - $journey->gps_idle_duration);
                } else if($label == 'Idling Time(HH:MM:SS)') {
                    $journeyData[$i] = readableTimeFomatForReports($journey->gps_idle_duration);
                } else if($label == 'Fuel Consumption(in litre)' || $label == 'Fuel') {
                    $journeyData[$i] = $journey->fuel;
                } else if($label == 'MPG(Actual)') {
                    $journeyData[$i] = $this->calculationActualMPG($journey);
                } else if($label == 'MPG(Expected)') {
                    $journeyData[$i] = $this->calculationExpectedMPG($journey);
                } else if($label == 'Journey CO2') {
                    $journeyData[$i] = $journey->co2;
                }
                $i++;
            }
            array_push($dataArray, $journeyData);
        }

        $reportTitle = $startDate->format('d M Y')." - ".$endDate->format('d M Y');

        if($newReportData) {
            $otherParams = $this->customReportOtherParam($reportTitle, $sheetTitle, $newReportData, $oldReportData);
        } else {
            $otherParams = [
                'fileName' => Carbon::now()->format('Ymd')."_Fuel_and_Emission_report_Standard",
                'sheetTitle_lable' => "Report",
                'sheetTitle_value' => "Fuel Usage and Emission Report",
                'sheetSubTitle_lable_first' => "Description",
                'sheetSubTitle_value_first' => $data['report_description'],
                'sheetSubTitle_lable_second' => "Duration",
                'sheetSubTitle_value_second' => $reportTitle,
                'sheetName' => $reportTitle,
                'boldLastRow' => false
            ];
        }

        $otherParams['columnFormat'] = [
            'H'=> \PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00,
            'I'=> \PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00,
            'J'=> \PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00,
            'K'=> \PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00
        ];

        return $this->toExcel($lableArray,$dataArray,$otherParams);
    }

    public function downloadDriverBehaviorReport($data, $reportDownload = null, $sheetTitle = null, $newReportData = null, $oldReportData = null)
    {
        $response = [];
        if($reportDownload) {
            $labelArray = $reportDownload->reportDataset->pluck('title')->toArray();
            $response = $reportDownload->reportDataset->pluck('field_name')->toArray();
        } else {
            $labelArray = $this->getReportLabels('standard_driver_behaviour_report');
        }
        $sortBy = $this->setOrderBy('standard_driver_behaviour_report');

        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);

        $dataArray = [];
        if(!isset($data['accessible_regions'])) {
            $allRegions = $this->getUserRegions('id');
        } else {
            $allRegions = $data['accessible_regions'];
        }

        $userDetails = $this->obj_telematics_journeys->getTelematicsDetailsByStartDateAndUserOrVehicle($data['date_from'], $data['date_to'], 'user_id', $allRegions, $sortBy);

        foreach($userDetails as $user) {

            if(!$user->user) {
                continue;
            }

            $scoreData = $this->getUserVehicleScoreData($user->journey_ids,$user->user_id);
            $i = 0;
            $driverBehaviourData = [];
            $safetyScore = $scoreData['safety'] == null? 100 : number_format($scoreData['safety'], 2, '.', '');
            $efficiencyScore = $scoreData['efficiency'] == null? 100 : number_format($scoreData['efficiency'], 2, '.', '');
            
            if(!empty($response)) {
                foreach($response as $value) {
                    if($value == 'users.email') {
                        $driverBehaviourData[$i] = $user->email;
                    } else if($value == 'users.first_name') {
                        $driverBehaviourData[$i] = $user->first_name;
                    } else if($value == 'users.last_name') {
                        $driverBehaviourData[$i] = $user->last_name;
                    } else if($value == 'users.engineer_id') {
                        $driverBehaviourData[$i] = $user->engineer_id;
                    } else if($value == 'users.mobile') {
                        $driverBehaviourData[$i] = $user->mobile;
                    } else if($value == 'users.company_id') {
                        $driverBehaviourData[$i] = $user->company_name;
                    } else if($value == 'users.user_division_id') {
                        $driverBehaviourData[$i] = $user->user_division_name;
                    } else if($value == 'users.user_region_id') {
                        $driverBehaviourData[$i] = $user->user_region_name;
                    } else if($value == 'driver_safety_score') {
                        $driverBehaviourData[$i] = $safetyScore;
                    } else if($value == 'driver_efficiency_score') {
                        $driverBehaviourData[$i] = $efficiencyScore;
                    } else if($value == 'driver_overall_score') {
                        $driverBehaviourData[$i] = number_format((($safetyScore + $efficiencyScore)/2), 2, '.', '');
                    } /* else if($value == 'vehicles.registration') {
                        $driverBehaviourData[$i] = $user->registration;
                    } else if($value == 'vehicles.vehicle_type_id') {
                        $driverBehaviourData[$i] = $user->vehicle_type;
                    } else if($value == 'vehicles.vehicle_category') {
                        $driverBehaviourData[$i] = $user->vehicle_category;
                    } else if($value == 'vehicles.vehicle_subcategory') {
                        $driverBehaviourData[$i] = $user->vehicle_subcategory;
                    } else if($value == 'vehicles.vehicle_division_id') {
                        $driverBehaviourData[$i] = $user->division_name;
                    } else if($value == 'vehicles.vehicle_region_id') {
                        $driverBehaviourData[$i] = $user->region_name;
                    } */
                    $i++;
                }
            } else {
            
                $driverBehaviourData = [
                    $user->user->email,
                    $user->user->id == 1 ? 'Driver' : $user->user->first_name,
                    $user->user->id == 1 ? 'Unknown' : $user->user->last_name,
                    $user->engineer_id,
                    $user->mobile,
                    $user->company_name,
                    $user->user_division_name,
                    $user->user_region_name,
                    // $scoreData['gps_distance'] == null? 0 : number_format($scoreData['gps_distance'] * 0.00062137, 2, '.', ''),
                    // $scoreData['engine_duration'] == null? 0 : readableTimeFomatForReports($scoreData['engine_duration']),
                    // $scoreData['fuel'] == null? 0 : $scoreData['fuel'],
                    // $user->incident_count,
                    $scoreData['safety'] == null? 100 : number_format($scoreData['safety'], 2, '.', ''),
                    $scoreData['efficiency'] == null? 100 : number_format($scoreData['efficiency'], 2, '.', ''),
                    number_format((($scoreData['safety']+$scoreData['efficiency'])/2), 2, '.', ''),
                    /* $user->registration,
                    $user->vehicle_type,
                    $user->vehicle_category,
                    $user->vehicle_subcategory,
                    $user->region_name,
                    $user->division_name, */
                ];
            }
            array_push($dataArray, $driverBehaviourData);
        }

        $reportTitle = $startDate->format('d M Y')." - ".$endDate->format('d M Y');

        if($newReportData) {
            $otherParams = $this->customReportOtherParam($reportTitle, $sheetTitle, $newReportData, $oldReportData);
        } else {
            $otherParams = $this->otherParams($data, "_DriverBehaviour_report_Standard", "Driver Behaviour Report", $reportTitle);
        }

        $otherParams['columnFormat'] = [
            'I'=> \PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00,
            'J'=> \PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00,
            'K'=> \PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00
        ];

        return $this->toExcel($labelArray,$dataArray,$otherParams);
    }

    public function downloadVehicleBehaviorReport($data, $reportDownload = null, $sheetTitle = null, $newReportData = null, $oldReportData = null) {

        $response = [];
        if($reportDownload) {
            $labelArray = $reportDownload->reportDataset->pluck('title')->toArray();
            $response = $reportDownload->reportDataset->pluck('field_name')->toArray();
        } else {
            $labelArray = $this->getReportLabels('standard_vehicle_behaviour_report');
        }
        $sortBy = $this->setOrderBy('standard_vehicle_behaviour_report');

        $dataArray = [];
        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);

        if(!isset($data['accessible_regions'])) {
            $allRegions = $this->getUserRegions('id');
        } else {
            $allRegions = $data['accessible_regions'];
        }
        $userDetails = $this->obj_telematics_journeys->getTelematicsDetailsByStartDateAndUserOrVehicle($data['date_from'], $data['date_to'], 'vehicle_id', $allRegions, $sortBy);

        foreach($userDetails as $user) {

            $scoreData = $this->getUserVehicleScoreData($user->journey_ids, null, $user->vehicle_id);
            $safetyScore = $scoreData['safety'] == null? 100 : number_format($scoreData['safety'], 2, '.', '');
            $efficiencyScore = $scoreData['efficiency'] == null? 100 : number_format($scoreData['efficiency'], 2, '.', '');

            $i = 0;
            $vehicleBehaviourData = [];
            if(empty($response)) {
                $vehicleBehaviourData = [
                    $user->vehicle->registration,
                    $user->vehicle_type,
                    $user->vehicle_category,
                    $user->vehicle_subcategory,
                    $user->division_name,
                    $user->region_name,
                    // $scoreData['gps_distance'] == null? 0 : number_format($scoreData['gps_distance'] * 0.00062137, 2, '.', ''),
                    // $scoreData['engine_duration'] == null? 0 : readableTimeFomatForReports($scoreData['engine_duration']),
                    // $scoreData['fuel'] == null? 0 : $scoreData['fuel'],
                    // $user->incident_count,
                    $safetyScore,
                    $efficiencyScore,
                    number_format((( $safetyScore+$efficiencyScore )/2), 2, '.', ''),
                    $user->email,
                    $user->first_name,
                    $user->last_name,
                    $user->engineer_id,
                    $user->mobile,
                    $user->company_name,
                    $user->user_division_name,
                    $user->user_region_name,
                ];
            } else {
                foreach($response as $value) {
                    if($value == 'users.email') {
                        $vehicleBehaviourData[$i] = $user->email;
                    } else if($value == 'users.first_name') {
                        $vehicleBehaviourData[$i] = $user->first_name;
                    } else if($value == 'users.last_name') {
                        $vehicleBehaviourData[$i] = $user->last_name;
                    } else if($value == 'users.engineer_id') {
                        $vehicleBehaviourData[$i] = $user->engineer_id;
                    } else if($value == 'users.mobile') {
                        $vehicleBehaviourData[$i] = $user->mobile;
                    } else if($value == 'users.company_id') {
                        $vehicleBehaviourData[$i] = $user->company_name;
                    } else if($value == 'users.user_division_id') {
                        $vehicleBehaviourData[$i] = $user->user_division_name;
                    } else if($value == 'vehicle_safety_score') {
                        $vehicleBehaviourData[$i] = $safetyScore;
                    } else if($value == 'vehicle_efficiency_score') {
                        $vehicleBehaviourData[$i] = $efficiencyScore;
                    } else if($value == 'vehicle_overall_score') {
                        $vehicleBehaviourData[$i] = number_format((($safetyScore + $efficiencyScore)/2), 2, '.', '');
                    } else if($value == 'vehicles.registration') {
                        $vehicleBehaviourData[$i] = $user->vehicle->registration;
                    } else if($value == 'vehicles.vehicle_type_id') {
                        $vehicleBehaviourData[$i] = $user->vehicle_type;
                    } else if($value == 'vehicle_types.vehicle_category') {
                        $vehicleBehaviourData[$i] = $user->vehicle_category;
                    } else if($value == 'vehicle_types.vehicle_subcategory') {
                        $vehicleBehaviourData[$i] = $user->vehicle_subcategory;
                    } else if($value == 'vehicles.vehicle_division_id') {
                        $vehicleBehaviourData[$i] = $user->division_name;
                    } else if($value == 'vehicles.vehicle_region_id') {
                        $vehicleBehaviourData[$i] = $user->region_name;
                    } else if($value == 'users.user_region_id') {
                        $vehicleBehaviourData[$i] = $user->user_region_name;
                    }
                    $i++;
                }
            }
            array_push($dataArray, $vehicleBehaviourData);
        }
        $reportTitle = $startDate->format('d M Y')." - ".$endDate->format('d M Y');

        if($newReportData) {
            $otherParams = $this->customReportOtherParam($reportTitle, $sheetTitle, $newReportData, $oldReportData);
        } else {
            $otherParams = $this->otherParams($data, "_VehicleBehaviour_report_Standard", "Vehicle Behaviour Report", $reportTitle);
        }

        $otherParams['columnFormat'] = [
            'G'=> \PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00,
            'H'=> \PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00,
            'I'=> \PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00
        ];

        return $this->toExcel($labelArray,$dataArray,$otherParams);
    }

    private function fetchLocation($journey)
    {
        $locationStart = "";
        $startLocation = $journey->details()->orderBy('id')->first();
        if(isset($startLocation)) {
            $locationStart = $startLocation->street." ".$startLocation->town." ".$startLocation->post_code;
        }

        return $locationStart;
    }

    private function getUserVehicleScoreData($trendJourneyIds, $user_id = null, $vehicle_id = null)
    {
        $trendJourneyIds = explode(",", $trendJourneyIds);
        $journeySummary = TelematicsJourneys::whereIn('journey_id', $trendJourneyIds)->whereNotNull('end_time')->whereNull('telematics_journeys.deleted_at');
        if($user_id) {
            $journeySummary = $journeySummary->where('user_id',$user_id);
        }
        if($vehicle_id) {
            $journeySummary = $journeySummary->where('vehicle_id',$vehicle_id);
        }

        $journeySummary = $journeySummary->selectRaw('SUM(harsh_acceleration_count) as harsh_acceleration_count,
                                                SUM(harsh_cornering_count) as harsh_cornering_count,
                                                SUM(harsh_acceleration_count) as harsh_acceleration_count,
                                                SUM(speeding_count) as speeding_count,
                                                SUM(harsh_breaking_count) as harsh_breaking_count,
                                                SUM(rpm_count) as rpm_count, 
                                                SUM(idling_count) as idling_count,
                                                SUM(gps_distance) as gps_distance,
                                                SUM(engine_duration) as engine_duration,
                                                SUM(gps_idle_duration) as gps_idle_duration,
                                                SUM(fuel) as fuel,
                                                SUM(co2) as co2')
                                            ->first();

        return $this->telematicsService->calculateJourneyScore($journeySummary);
    }

    public function mpsToMph($metersPerSecond) {
        $metersPerSecond = is_numeric($metersPerSecond) ? $metersPerSecond : 0;
        $milesPerHour = $metersPerSecond * 2.236936;
        return number_format($milesPerHour,2);
    }

    public function downloadVehicleProfileReport($data)
    {
        $labelArray = $this->getReportLabels('standard_vehicle_profile_report');
        $sortBy = $this->setOrderBy('standard_vehicle_profile_report');

        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);

        $dataArray = [];

        $vehicleProfile = $this->customReportRepository->vehicleProfileReport($data);

        $vehicleProfile = $vehicleProfile->selectRaw('vehicles.registration, users.email, users.mobile, companies.name as company_name, user_divisions.name as user_division_name, user_regions.name as user_region_name, users.engineer_id, vehicle_types.vehicle_type, CASE WHEN vehicle_types.vehicle_category = "hgv" THEN "HGV" ELSE "Non-HGV" END as vehicle_category, vehicle_types.vehicle_subcategory, CASE WHEN vehicles.nominated_driver = NULL THEN "" ELSE users.first_name END as first_name, CASE WHEN vehicles.nominated_driver = NULL THEN "" ELSE users.last_name END as last_name, vehicle_regions.name as region_name, vehicle_divisions.name as division_name, vehicle_types.manufacturer, vehicle_types.model, vehicle_types.fuel_type, vehicle_types.engine_type, vehicles.chassis_number, vehicles.contract_id')
        ->orderBy($sortBy)
        ->get();

        foreach ($vehicleProfile as $profile) {

            $profileData = [
                $profile->registration,
                $profile->vehicle_type,
                $profile->vehicle_category,
                $profile->vehicle_subcategory,
                $profile->manufacturer,
                $profile->model,
                $profile->fuel_type,
                $profile->engine_type,
                $profile->division_name,
                $profile->region_name,
                $profile->chassis_number,
                $profile->contract_id,
                $profile->email,
                $profile->first_name,
                $profile->last_name,
                $profile->engineer_id,
                $profile->mobile,
                $profile->company_name,
                $profile->user_division_name,
                $profile->user_region_name
            ];
            array_push($dataArray, $profileData);
        }

        $reportTitle = Carbon::now()->format('d M Y');
        $otherParams = $this->otherParams($data, "_VehicleDetails_report_Standard", "Vehicle Details Report", $reportTitle);

        return $this->toExcel($labelArray, $dataArray, $otherParams);

    }

    private function getReportLabels($reportSlug)
    {
        $labelArray = [];
        $labels = config('config-variables.standard_reports')[$reportSlug];
        foreach($labels as $label) {
            $labelArray[] = str_replace('|Vehicle', '', str_replace('|User', '', $label));
        }

        return $labelArray;
    }

    public function setOrderBy($reportSlug)
    {
        $labelArray = $this->getReportLabels($reportSlug);
        return $this->customReportRepository->getReportDataSetFieldByTitle($labelArray[0]);
    }

    public function downloadVehicleIncidentReport($data)
    {
        $labelArray = $this->getReportLabels('standard_vehicle_incident_report');
        $sortBy = $this->setOrderBy('standard_vehicle_incident_report');

        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);

        $dataArray = [];

        $incidentTypes = array_keys(config('config-variables.telematics_incidents'));

        $incidentEvents = $this->customReportRepository->vehicleAndUserIncidentReportData($data, $incidentTypes, 'vehicle');

        $incidentEvents = $incidentEvents->selectRaw('vehicles.registration, users.email, users.mobile, companies.name as company_name, user_divisions.name as user_division_name, user_regions.name as user_region_name, users.engineer_id, vehicle_types.vehicle_type, CASE WHEN vehicle_types.vehicle_category = "hgv" THEN "HGV" ELSE "Non-HGV" END as vehicle_category, vehicle_types.vehicle_subcategory, CASE WHEN telematics_journeys.user_id = 1 THEN "Driver" ELSE users.first_name END as first_name, CASE WHEN telematics_journeys.user_id = 1 THEN "Unknown" ELSE users.last_name END as last_name, telematics_journey_details.ns, vehicle_regions.name as region_name, vehicle_divisions.name as division_name, DATE_FORMAT(CONVERT_TZ(telematics_journey_details.time, "UTC", "'.config('config-variables.format.displayTimezone').'"),"%Y-%m-%d %H:%i:%s") as incident_date, speed, street_speed,
            CASE WHEN ns = "tm8.dfb2.acc.l" THEN "Harsh Acceleration"
                WHEN ns = "tm8.dfb2.cnrl.l" THEN "Harsh Left Cornering"
                WHEN ns = "tm8.dfb2.cnrr.l" THEN "Harsh Right Cornering"
                WHEN ns = "tm8.dfb2.dec.l" THEN "Harsh Braking"
                WHEN ns = "tm8.dfb2.rpm" THEN "RPM"
                WHEN ns = "tm8.dfb2.spdinc" THEN "Speeding"
                WHEN ns = "tm8.gps.idle.start" THEN "Idle Start"
                WHEN ns = "tm8.gps.idle.end" THEN "Idling"
                END as incident_type')
        ->orderBy($sortBy)
        ->get();

        foreach($incidentEvents as $incident) {

            $incidentData = [
                $incident->registration,
                $incident->vehicle_type,
                $incident->vehicle_category,
                $incident->vehicle_subcategory,
                $incident->division_name,
                $incident->region_name,
                $incident->incident_date,
                $incident->incident_type,
                $incident->incident_type == 'Speeding' ? number_format(round((float)$this->mpsToMph($incident->street_speed),0,PHP_ROUND_HALF_UP),2) : 'NA',
                $incident->incident_type == 'Speeding' ? $this->mpsToMph($incident->speed) : 'NA',
                $incident->email,
                $incident->first_name,
                $incident->last_name,
                $incident->engineer_id,
                $incident->mobile,
                $incident->company_name,
                $incident->user_division_name,
                $incident->user_region_name,
                // $incident->incident_count,
                // gmdate("H:i:s", $incident->gps_idle_duration),
            ];
            array_push($dataArray, $incidentData);
        }

        $reportTitle = $startDate->format('d M Y')." - ".$endDate->format('d M Y');
        $otherParams = $this->otherParams($data, "_VehicleIncident_report_Standard", "Vehicle Incidents Report", $reportTitle);

        return $this->toExcel($labelArray,$dataArray,$otherParams);
    }

    public function downloadVehicleDefectReport($data)
    {
        $labelArray = $this->getReportLabels('standard_vehicle_defects_report');
        $sortBy = $this->setOrderBy('standard_vehicle_defects_report');

        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);

        $dataArray = [];

        $defectsData = $this->customReportRepository->vehicleAndUserDefectReportData($data, 'vehicle');

        $defectsData = $defectsData->selectRaw('vehicles.registration, users.email, users.mobile, companies.name as company_name, user_divisions.name as user_division_name, user_regions.name as user_region_name, users.engineer_id, vehicle_types.vehicle_type, CASE WHEN vehicle_types.vehicle_category = "hgv" THEN "HGV" ELSE "Non-HGV" END as vehicle_category, vehicle_types.vehicle_subcategory, CASE WHEN checks.created_by = 1 THEN "Driver" ELSE users.first_name END as first_name, CASE WHEN checks.created_by = 1 THEN "Unknown" ELSE users.last_name END as last_name, vehicle_regions.name as region_name, vehicle_divisions.name as division_name, defect_master.page_title, defect_master.defect, DATE_FORMAT(CONVERT_TZ(defects.report_datetime, "UTC", "'.config('config-variables.format.displayTimezone').'"),"%Y-%m-%d %H:%i:%s") as report_datetime')
        ->orderBy($sortBy)
        ->get();

        foreach ($defectsData as $defect) {

            $defectDataArr = [
                $defect->registration,
                $defect->vehicle_type,
                $defect->vehicle_category,
                $defect->vehicle_subcategory,
                $defect->division_name,
                $defect->region_name,
                $defect->email,
                $defect->first_name,
                $defect->last_name,
                $defect->engineer_id,
                $defect->mobile,
                $defect->company_name,
                $defect->user_division_name,
                $defect->user_region_name,
                $defect->report_datetime,
                $defect->page_title,
                $defect->defect,
            ];
            array_push($dataArray, $defectDataArr);
        }

        $reportTitle = $startDate->format('d M Y')." to ".$endDate->format('d M Y');
        $otherParams = $this->otherParams($data, "_VehicleDefect_report_Standard", "Vehicle Defect Report", $reportTitle);

        return $this->toExcel($labelArray, $dataArray, $otherParams);

    }

    public function otherParams($data, $fileName, $titleValue, $reportTitle)
    {
        $otherParams = [
            'fileName' => Carbon::now()->format('Ymd').$fileName,
            'sheetTitle_lable' => "Report",
            'sheetTitle_value' => $titleValue,
            'sheetSubTitle_lable_first' => "Description",
            'sheetSubTitle_value_first' => $data['report_description'],
            'sheetSubTitle_lable_second' => "Duration",
            'sheetSubTitle_value_second' => $reportTitle,
            'sheetName' => $reportTitle,
            'boldLastRow' => false
        ];

        return $otherParams;
    }

    public function downloadVehicleCheckReport($data)
    {
        $labelArray = $this->getReportLabels('standard_vehicle_checks_report');
        $sortBy = $this->setOrderBy('standard_vehicle_checks_report');

        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);

        $dataArray = [];

        $checkData = $this->customReportRepository->vehicleAndUserCheckReportData($data, 'vehicle');

        $checkData = $checkData->selectRaw('vehicles.registration, users.email, users.mobile, companies.name as company_name, user_divisions.name as user_division_name, user_regions.name as user_region_name, users.engineer_id, vehicle_types.vehicle_type, CASE WHEN vehicle_types.vehicle_category = "hgv" THEN "HGV" ELSE "Non-HGV" END as vehicle_category, vehicle_types.vehicle_subcategory, CASE WHEN checks.created_by = 1 THEN "Driver" ELSE users.first_name END as first_name, CASE WHEN checks.created_by = 1 THEN "Unknown" ELSE users.last_name END as last_name, vehicle_regions.name as region_name, vehicle_divisions.name as division_name, checks.type as check_type, CASE WHEN checks.status = "RoadWorthy" THEN "Roadworthy" 
            WHEN checks.status = "SafeToOperate" THEN "Safe to operate" 
            ELSE "Unsafe to operate" END as status, DATE_FORMAT(CONVERT_TZ(checks.report_datetime, "UTC", "'.config('config-variables.format.displayTimezone').'"),"%Y-%m-%d %H:%i:%s") as report_datetime,checks.check_duration')
        ->orderBy($sortBy)
        ->get();

        foreach ($checkData as $check) {

            $checksDataArr = [
                $check->registration,
                $check->vehicle_type,
                $check->vehicle_category,
                $check->vehicle_subcategory,
                $check->division_name,
                $check->region_name,
                $check->email,
                $check->first_name,
                $check->last_name,
                $check->engineer_id,
                $check->mobile,
                $check->company_name,
                $check->user_division_name,
                $check->user_region_name,
                $check->check_type,
                $check->status,
                $check->report_datetime,
                $check->check_duration
            ];
            array_push($dataArray, $checksDataArr);
        }

        $reportTitle = $startDate->format('d M Y')." to ".$endDate->format('d M Y');
        $otherParams = $this->otherParams($data, "_VehicleCheck_report_Standard", "Vehicle Check Report", $reportTitle);

        return $this->toExcel($labelArray, $dataArray, $otherParams);

    }

    public function downloadVehiclePlanningReport($data)
    {
        $labelArray = $this->getReportLabels('standard_vehicle_planning_report');
        $sortBy = $this->setOrderBy('standard_vehicle_planning_report');

        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);

        $dataArray = [];

        $visibleColumns = 'vehicles.registration, users.email, users.mobile, companies.name as company_name, user_divisions.name as user_division_name, users.engineer_id, vehicle_types.vehicle_type, vehicle_types.vehicle_category, vehicle_types.vehicle_subcategory, CASE WHEN vehicles.created_by = 1 THEN "Driver" ELSE users.first_name END as first_name, CASE WHEN vehicles.created_by = 1 THEN "Unknown" ELSE users.last_name END as last_name, vehicle_regions.name as region_name, vehicle_divisions.name as division_name';

        $planningData = $this->customReportRepository->vehiclePlanningReportData($data, $visibleColumns, $sortBy);

        foreach ($planningData as $planning) {

            $planningDataArr = [
                $planning['registration'],
                $planning['vehicle_type'],
                $planning['vehicle_category'],
                $planning['vehicle_subcategory'],
                $planning['division_name'],
                $planning['region_name'],
                $planning['service_type'],
                $planning['service_date'],
                $planning['email'],
                $planning['first_name'],
                $planning['last_name'],
                $planning['engineer_id'],
                $planning['mobile'],
                $planning['company_name'],
                $planning['user_division_name'],
            ];
            array_push($dataArray, $planningDataArr);
        }

        $reportTitle = $startDate->format('d M Y')." to ".$endDate->format('d M Y');
        $otherParams = $this->otherParams($data, "_VehiclePlanning_report_Standard", "Vehicle Planning Report", $reportTitle);

        return $this->toExcel($labelArray, $dataArray, $otherParams);

    }

    public function downloadUserDetailsReport($data, $reportDownload = null, $sheetTitle = null, $newReportData = null, $oldReportData = null)
    {
        if($reportDownload) {
            $labelArray = $reportDownload->reportDataset->pluck('title')->toArray();
        } else {
            $labelArray = $this->getReportLabels('standard_user_details_report');
        }
        $sortBy = $this->setOrderBy('standard_user_details_report');

        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);

        $dataArray = [];

        $userData = $this->customReportRepository->userDetailsReport($data);

        $userData = $userData->orderBy($sortBy)->get();

        foreach ($userData as $user) {

            $usersDataArr = [];
            foreach($labelArray as $label) {
                if($label == 'Email') {
                    $usersDataArr[] = $user->email;
                } else if($label == 'Driver First Name' || $label == 'First Name') {
                    $usersDataArr[] = $user->first_name;
                } else if($label == 'Driver Last Name' || $label == 'Last Name') {
                    $usersDataArr[] = $user->last_name;
                } else if($label == 'Created Date') {
                    $usersDataArr[] = $user->created_at;
                } else if($label == 'Engineer ID') {
                    $usersDataArr[] = $user->engineer_id;
                } else if($label == 'Mobile Number' || $label == 'Mobile') {
                    $usersDataArr[] = $user->mobile;
                } else if($label == 'Landline') {
                    $usersDataArr[] = $user->landline;
                } else if($label == 'Company') {
                    $usersDataArr[] = $user->company_id;
                } else if($label == 'Dallas Key') {
                    $usersDataArr[] = $user->dallas_key;
                } else if($label == 'IMEI Number') {
                    $usersDataArr[] = $user->imei;
                } else if($label == 'Base location') {
                    $usersDataArr[] = $user->base_location;
                } else if($label == 'User Division' || $label == 'Division') {
                    $usersDataArr[] = $user->user_division_id;
                } else if($label == 'User Region' || $label == 'Region') {
                    $usersDataArr[] = $user->user_region_id;
                } else if($label == 'Registration') {
                    $usersDataArr[] = $user->registration;
                } else if($label == 'Type') {
                    $usersDataArr[] = $user->vehicle_type_id;
                } else if($label == 'Category') {
                    $usersDataArr[] = $user->vehicle_category;
                } else if($label == 'Sub Category') {
                    $usersDataArr[] = $user->vehicle_subcategory;
                } else if($label == 'Vehicle Division') {
                    $usersDataArr[] = $user->vehicle_division_id;
                } else if($label == 'Vehicle Region') {
                    $usersDataArr[] = $user->vehicle_region_id;
                }
            }
            array_push($dataArray, $usersDataArr);
        }

        $reportTitle = $startDate->format('d M Y')." to ".$endDate->format('d M Y');
        $currentDate = Carbon::now()->format('d M Y');

        if($newReportData) {
            $otherParams = $this->customReportOtherParam($currentDate, $sheetTitle, $newReportData, $oldReportData);
        } else {
            $otherParams = $this->otherParams($data, "_UserDetails_report_Standard", "User Details Report", $currentDate);
        }

        return $this->toExcel($labelArray, $dataArray, $otherParams);

    }

    public function downloadUserIncidentReport($data)
    {
        $labelArray = $this->getReportLabels('standard_user_incident_report');
        $sortBy = $this->setOrderBy('standard_user_incident_report');

        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);

        $dataArray = [];

        $incidentTypes = array_keys(config('config-variables.telematics_incidents'));

        $userIncidents = $this->customReportRepository->vehicleAndUserIncidentReportData($data, $incidentTypes, 'user');

        $userIncidents = $userIncidents->selectRaw('vehicles.registration, users.email, users.mobile, companies.name as company_name, user_divisions.name as user_division_name, user_regions.name as user_region_name, users.engineer_id, vehicle_types.vehicle_type, CASE WHEN vehicle_types.vehicle_category = "hgv" THEN "HGV" ELSE "Non-HGV" END as vehicle_category, vehicle_types.vehicle_subcategory, CASE WHEN telematics_journeys.user_id = 1 THEN "Driver" ELSE users.first_name END as first_name, CASE WHEN telematics_journeys.user_id = 1 THEN "Unknown" ELSE users.last_name END as last_name, telematics_journey_details.ns, vehicle_regions.name as region_name, vehicle_divisions.name as division_name, DATE_FORMAT(CONVERT_TZ(telematics_journey_details.time, "UTC", "'.config('config-variables.format.displayTimezone').'"),"%Y-%m-%d %H:%i:%s") as incident_date, speed, street_speed,
            CASE WHEN ns = "tm8.dfb2.acc.l" THEN "Harsh Acceleration"
                WHEN ns = "tm8.dfb2.cnrl.l" THEN "Harsh Left Cornering"
                WHEN ns = "tm8.dfb2.cnrr.l" THEN "Harsh Right Cornering"
                WHEN ns = "tm8.dfb2.dec.l" THEN "Harsh Braking"
                WHEN ns = "tm8.dfb2.rpm" THEN "RPM"
                WHEN ns = "tm8.dfb2.spdinc" THEN "Speeding"
                WHEN ns = "tm8.gps.idle.start" THEN "Idle Start"
                WHEN ns = "tm8.gps.idle.end" THEN "Idling"
                END as incident_type')
        ->orderBy($sortBy)
        ->get();


        foreach($userIncidents as $incident) {

            $userIncidentData = [
                $incident->email,
                $incident->first_name,
                $incident->last_name,
                $incident->engineer_id,
                $incident->mobile,
                $incident->company_name,
                $incident->user_division_name,
                $incident->user_region_name,
                $incident->registration,
                $incident->vehicle_type,
                $incident->vehicle_category,
                $incident->vehicle_subcategory,
                $incident->division_name,
                $incident->region_name,
                $incident->incident_date,
                $incident->incident_type,
                $incident->incident_type == 'Speeding' ? number_format(round((float)$this->mpsToMph($incident->street_speed),0,PHP_ROUND_HALF_UP),2) : 'NA',
                $incident->incident_type == 'Speeding' ? $this->mpsToMph($incident->speed) : 'NA',
            ];
            array_push($dataArray, $userIncidentData);
        }

        $reportTitle = $startDate->format('d M Y')." - ".$endDate->format('d M Y');
        $otherParams = $this->otherParams($data, "_UserIncident_report_Standard", "User Incidents Report", $reportTitle);

        return $this->toExcel($labelArray,$dataArray,$otherParams);
    }

    public function downloadUserDefectReport($data)
    {
        $labelArray = $this->getReportLabels('standard_user_defects_report');
        $sortBy = $this->setOrderBy('standard_user_defects_report');

        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);

        $dataArray = [];

        $defectsData = $this->customReportRepository->vehicleAndUserDefectReportData($data, 'user');

        $defectsData = $defectsData->selectRaw('vehicles.registration, users.email, users.mobile, companies.name as company_name, user_divisions.name as user_division_name, user_regions.name as user_region_name, users.engineer_id, vehicle_types.vehicle_type, CASE WHEN vehicle_types.vehicle_category = "hgv" THEN "HGV" ELSE "Non-HGV" END as vehicle_category, vehicle_types.vehicle_subcategory, CASE WHEN checks.created_by = 1 THEN "Driver" ELSE users.first_name END as first_name, CASE WHEN checks.created_by = 1 THEN "Unknown" ELSE users.last_name END as last_name, vehicle_regions.name as region_name, vehicle_divisions.name as division_name, defect_master.page_title, defect_master.defect, DATE_FORMAT(CONVERT_TZ(defects.report_datetime, "UTC", "'.config('config-variables.format.displayTimezone').'"),"%Y-%m-%d %H:%i:%s") as report_datetime')
            ->orderBy($sortBy)
            ->get();

        foreach ($defectsData as $defect) {

            $userDefectData = [
                $defect->email,
                $defect->first_name,
                $defect->last_name,
                $defect->engineer_id,
                $defect->mobile,
                $defect->company_name,
                $defect->user_division_name,
                $defect->user_region_name,
                $defect->registration,
                $defect->vehicle_type,
                $defect->vehicle_category,
                $defect->vehicle_subcategory,
                $defect->division_name,
                $defect->region_name,
                $defect->report_datetime,
                $defect->page_title,
                $defect->defect,
            ];
            array_push($dataArray, $userDefectData);
        }

        $reportTitle = $startDate->format('d M Y')." to ".$endDate->format('d M Y');
        $otherParams = $this->otherParams($data, "_UserDefect_report_Standard", "User Defect Report", $reportTitle);

        return $this->toExcel($labelArray, $dataArray, $otherParams);

    }

    public function downloadUserCheckReport($data)
    {
        $labelArray = $this->getReportLabels('standard_user_checks_report');
        $sortBy = $this->setOrderBy('standard_user_checks_report');

        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);

        $dataArray = [];
        $checkData = $this->customReportRepository->vehicleAndUserCheckReportData($data, 'user');

        $checkData = $checkData->selectRaw('vehicles.registration, users.email, users.mobile, companies.name as company_name, user_divisions.name as user_division_name, user_regions.name as user_region_name, users.engineer_id, vehicle_types.vehicle_type, CASE WHEN vehicle_types.vehicle_category = "hgv" THEN "HGV" ELSE "Non-HGV" END as vehicle_category, vehicle_types.vehicle_subcategory, CASE WHEN checks.created_by = 1 THEN "Driver" ELSE users.first_name END as first_name, CASE WHEN checks.created_by = 1 THEN "Unknown" ELSE users.last_name END as last_name, vehicle_regions.name as region_name, vehicle_divisions.name as division_name, checks.type as check_type, CASE WHEN checks.status = "RoadWorthy" THEN "Roadworthy" 
            WHEN checks.status = "SafeToOperate" THEN "Safe to operate" 
            ELSE "Unsafe to operate" END as status, DATE_FORMAT(CONVERT_TZ(checks.report_datetime, "UTC", "'.config('config-variables.format.displayTimezone').'"),"%Y-%m-%d %H:%i:%s") as report_datetime')
        ->orderBy($sortBy)
        ->get();

        foreach ($checkData as $check) {

            $checksDataArr = [
                $check->email,
                $check->first_name,
                $check->last_name,
                $check->engineer_id,
                $check->mobile,
                $check->company_name,
                $check->user_division_name,
                $check->user_region_name,
                $check->registration,
                $check->vehicle_type,
                $check->vehicle_category,
                $check->vehicle_subcategory,
                $check->division_name,
                $check->region_name,
                $check->check_type,
                $check->status,
                $check->report_datetime,
            ];
            array_push($dataArray, $checksDataArr);
        }

        $reportTitle = $startDate->format('d M Y')." to ".$endDate->format('d M Y');
        $otherParams = $this->otherParams($data, "_UserCheck_report_Standard", "User Check Report", $reportTitle);

        return $this->toExcel($labelArray, $dataArray, $otherParams);

    }

    public function downloadUserJourneyReport($data, $reportDownload = null, $sheetTitle = null, $newReportData = null, $oldReportData = null)
    {
        $labelArray = $this->getReportLabels('standard_user_journey_report');
        $sortBy = $this->setOrderBy('standard_user_journey_report');

        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);

        $dataArray = [];

        if($reportDownload) {
            $response = $reportDownload->reportDataset->pluck('field_name')->toArray();
            $response = setDataCoumns($response);
            $visibleColumns = implode(",", $response);
        } else {
            $visibleColumns = 'telematics_journeys.*, vehicles.registration, users.email, users.mobile, companies.name as company_id, user_divisions.name as user_division_id, user_regions.name as user_region_id, users.engineer_id, vehicle_types.vehicle_type, CASE WHEN vehicle_types.vehicle_category = "hgv" THEN "HGV" ELSE "Non-HGV" END as vehicle_category, vehicle_types.vehicle_subcategory, CASE WHEN telematics_journeys.user_id = 1 THEN "Driver" ELSE users.first_name END as first_name, CASE WHEN telematics_journeys.user_id = 1 THEN "Unknown" ELSE users.last_name END as last_name, 
            DATE_FORMAT(CONVERT_TZ(start_time, "UTC","'.config('config-variables.format.displayTimezone').'"), "%d %b %Y") as telematics_journey_start_date, DATE_FORMAT(CONVERT_TZ(start_time, "UTC","'.config('config-variables.format.displayTimezone').'"),"%H:%i:%s") AS telematics_journey_start_time, vehiclefuelsum, vehicledistancesum, vehicle_regions.name as vehicle_region_id, vehicle_divisions.name as vehicle_division_id';
        }

        $userJourneys = $this->customReportRepository->vehicleAndUserJourneyReportData($data, 'user', $visibleColumns);

        $userJourneys = $userJourneys->orderBy('user_id')->orderBy($sortBy)->orderBy('start_time')->get();

        $vrn = '';
        $totalCount = count($userJourneys);
        $engineDuration = $gpsDistance = $fuel = $co2 = 0;
        foreach($userJourneys as $key => $journey) {
            if($vrn != '' && $journey->registration != $vrn) {
                $userJourneysData = [
                    'SUBTOTAL',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    readableTimeFomatForReports($engineDuration),
                    $gpsDistance,
                    // $fuel,
                    // $co2,
                ];
                $engineDuration = $gpsDistance = $fuel = $co2 = 0;
                array_push($dataArray, $userJourneysData);
            }

            $vrn = $journey->registration;
            $engineDuration += $journey->engine_duration;
            $gpsDistance += number_format($journey->gps_distance * 0.00062137, 2, '.', '');

            // $journeyFuel = $journey->fuel == 0 && $journey->gps_distance < 1609.34 ? '< '.env('MIN_JOURNEY_FUEL') : $journey->fuel;
            // $fuel += is_numeric($journeyFuel) ? $journey->fuel : 0;

            // $journeyCo2 = $journey->co2 == 0 && $journey->gps_distance < 1609.34 ? '< '.env('MIN_JOURNEY_CO2') : $journey->co2;
            // $co2 += is_numeric($journeyCo2) ? $journey->co2 : 0;

            $userJourneysData = [
                $journey->email,
                $journey->first_name,
                $journey->last_name,
                $journey->engineer_id,
                $journey->mobile,
                $journey->company_id,
                $journey->user_division_id,
                $journey->user_region_id,
                $journey->registration,
                $journey->vehicle_type,
                $journey->vehicle_category,
                $journey->vehicle_subcategory,
                $journey->vehicle_division_id,
                $journey->vehicle_region_id,
                $journey->telematics_journey_start_date,
                $journey->telematics_journey_start_time,
                readableTimeFomatForReports($journey->engine_duration),
                number_format($journey->gps_distance * 0.00062137, 2, '.', ''),
                // $journeyFuel,
                // $journeyCo2,
            ];
            array_push($dataArray, $userJourneysData);
        }

        if($totalCount > 0) {
            $userJourneysData = [
                'SUBTOTAL',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                readableTimeFomatForReports($engineDuration),
                $gpsDistance,
                // $fuel,
                // $co2,
            ];
            $engineDuration = $gpsDistance = $fuel = $co2 = 0;
            array_push($dataArray, $userJourneysData);
        }

        $reportTitle = $startDate->format('d M Y')." - ".$endDate->format('d M Y');

        if($newReportData) {
            $otherParams = $this->customReportOtherParam($reportTitle, $sheetTitle, $newReportData, $oldReportData);
        } else {
            $otherParams = $this->otherParams($data, "_UserJourney_report_Standard", "User Journey Report", $reportTitle);
        }

        $otherParams['columnFormat'] = [
            'R'=> \PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00
        ];

        return $this->toExcel($labelArray,$dataArray,$otherParams);
    }

    public function downloadVehicleJourneyReport($data, $reportDownload = null, $sheetTitle = null, $newReportData = null, $oldReportData = null)
    {
        $labelArray = $this->getReportLabels('standard_vehicle_journey_report');
        $sortBy = $this->setOrderBy('standard_vehicle_journey_report');

        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);

        $dataArray = [];

        if($reportDownload) {
            $response = $reportDownload->reportDataset->pluck('field_name')->toArray();
            $response = setDataCoumns($response);
            $visibleColumns = implode(",", $response);
        } else {
            $visibleColumns = 'telematics_journeys.*, vehicles.registration, users.email, users.mobile, companies.name as company_id, user_divisions.name as user_division_id, user_regions.name as user_region_id, users.engineer_id, vehicle_types.vehicle_type, CASE WHEN vehicle_types.vehicle_category = "hgv" THEN "HGV" ELSE "Non-HGV" END as vehicle_category, vehicle_types.vehicle_subcategory, CASE WHEN telematics_journeys.user_id = 1 THEN "Driver" ELSE users.first_name END as first_name, CASE WHEN telematics_journeys.user_id = 1 THEN "Unknown" ELSE users.last_name END as last_name, 
            DATE_FORMAT(CONVERT_TZ(start_time, "UTC","'.config('config-variables.format.displayTimezone').'"), "%d %b %Y") as telematics_journey_start_date, DATE_FORMAT(CONVERT_TZ(start_time, "UTC","'.config('config-variables.format.displayTimezone').'"),"%H:%i:%s") AS telematics_journey_start_time, vehiclefuelsum, vehicledistancesum, vehicle_regions.name as vehicle_region_id, vehicle_divisions.name as vehicle_division_id';
        }

        $vehicleJourneys = $this->customReportRepository->vehicleAndUserJourneyReportData($data, 'vehicle', $visibleColumns);
        $vehicleJourneys = $vehicleJourneys->orderBy($sortBy)->orderBy('start_time')->get();

        $vrn = '';
        $engineDuration = $gpsDistance = $fuel = $co2 = 0;
        $totalCount = count($vehicleJourneys);
        foreach($vehicleJourneys as $key => $journey) {
            if($vrn != '' && $journey->registration != $vrn) {
                $vehicleJourneysData = [
                    'SUBTOTAL',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    readableTimeFomatForReports($engineDuration),
                    $gpsDistance,
                    // $fuel,
                    // $co2,
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];
                $engineDuration = $gpsDistance = $fuel = $co2 = 0;
                array_push($dataArray, $vehicleJourneysData);
            }

            $vrn = $journey->registration;
            $engineDuration += $journey->engine_duration;
            $gpsDistance += number_format($journey->gps_distance * 0.00062137, 2, '.', '');

            // $journeyFuel = $journey->fuel == 0 && $journey->gps_distance < 1609.34 ? '< '.env('MIN_JOURNEY_FUEL') : $journey->fuel;
            // $fuel += is_numeric($journeyFuel) ? $journey->fuel : 0;

            // $journeyCo2 = $journey->co2 == 0 && $journey->gps_distance < 1609.34 ? '< '.env('MIN_JOURNEY_CO2') : $journey->co2;
            // $co2 += is_numeric($journeyCo2) ? $journey->co2 : 0;

            $vehicleJourneysData = [
                $journey->registration,
                $journey->vehicle_type,
                $journey->vehicle_category,
                $journey->vehicle_subcategory,
                $journey->vehicle_division_id,
                $journey->vehicle_region_id,
                $journey->telematics_journey_start_date,
                $journey->telematics_journey_start_time,
                readableTimeFomatForReports($journey->engine_duration),
                number_format($journey->gps_distance * 0.00062137, 2, '.', ''),
                // $journeyFuel,
                // $journeyCo2,
                $journey->email,
                $journey->first_name,
                $journey->last_name,
                $journey->engineer_id,
                $journey->mobile,
                $journey->company_id,
                $journey->user_division_id,
                $journey->user_region_id
            ];

            array_push($dataArray, $vehicleJourneysData);
        }

        if($totalCount > 0) {
            $vehicleJourneysData = [
                'SUBTOTAL',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                readableTimeFomatForReports($engineDuration),
                $gpsDistance,
                // $fuel,
                // $co2,
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ];
            $engineDuration = $gpsDistance = $fuel = $co2 = 0;
            array_push($dataArray, $vehicleJourneysData);
        }

        $reportTitle = $startDate->format('d M Y')." - ".$endDate->format('d M Y');

        if($newReportData) {
            $otherParams = $this->customReportOtherParam($reportTitle, $sheetTitle, $newReportData, $oldReportData);
        } else {
            $otherParams = $this->otherParams($data, "_VehicleJourney_report_Standard", "Vehicle Journey Report", $reportTitle);
        }

        $otherParams['columnFormat'] = [
            'J'=> \PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00
        ];

        return $this->toExcel($labelArray,$dataArray,$otherParams);
    }

    public function downloadVehicleWeeklyMaintananceReport($data, $reportDownload = null, $sheetTitle = null, $newReportData = null, $oldReportData = null)
    {
        if($reportDownload) {
            $labelArray = $reportDownload->reportDataset->pluck('title')->toArray();
        } else {
            $labelArray = $this->getReportLabels('standard_weekly_maintanance_report');
        }
        $sortBy = $this->setOrderBy('standard_weekly_maintanance_report');

        $startDt = Carbon::parse($data['date_from']);
        $endDt = Carbon::parse($data['date_to']);

        $vehicleDivisions = VehicleDivisions::orderBy('name','ASC')->lists('name', 'id')->toArray();
        $vehicleRegions = VehicleRegions::orderBy('name','ASC')->lists('name', 'id')->toArray();
        $vehicleLocations = VehicleLocations::orderBy('name','ASC')->lists('name', 'id')->toArray();

        $events = config('config-variables.eventRemindersNotifications');
        $serviceInspectionDistanceKey = array_search('next_service_inspection_distance', array_column($events, 'event'));
        unset($events[$serviceInspectionDistanceKey]);

        $dataArray = [];
        list($vehicles, $vehiclesForServiceInspectionDistance) = $this->customReportRepository->vehicleWeeklyMaintananceReport($data, $events, $sortBy);

        // Collecting events list for the vehicle in the current week
        foreach ($vehicles as $key => $vehicle) {
            $isPmiProcessed = false;
            $pmiArray = [];
            foreach ($events as $event) {
                $eventDt = $vehicle->toArray()[$event['column']];
                if($eventDt) {
                    $eventDt = Carbon::parse($eventDt);
                    if($eventDt->between($startDt, $endDt)) {

                        if($event['maintenanceType'] == 'PMI') {
                            if(!empty($pmiArray) && in_array($eventDt->format('d M Y'), $pmiArray)) {
                                continue;
                            } else {
                                $pmiArray[] = $eventDt->format('d M Y');
                            }
                        }

                        $dueDateKey = 0;
                        $eventNameKey = 0;
                        $dataArr = [];
                        foreach($labelArray as $label) {
                            if($label == 'Registration') {
                                $dataArr[] = $vehicle->registration;
                            } else if($label == 'Type') {
                                $dataArr[] = $vehicle->type->vehicle_type;
                            } else if($label == 'Manufacturer') {
                                $dataArr[] = $vehicle->type->manufacturer;
                            } else if($label == 'Model') {
                                $dataArr[] = $vehicle->type->model;
                            } else if($label == 'Vehicle Division') {
                                $dataArr[] = isset($vehicleDivisions[$vehicle->vehicle_division_id]) ? $vehicleDivisions[$vehicle->vehicle_division_id] : '';
                            } else if($label == 'Vehicle Region') {
                                $dataArr[] = isset($vehicleRegions[$vehicle->vehicle_region_id]) ? $vehicleRegions[$vehicle->vehicle_region_id] : '';
                            } else if($label == 'Vehicle Location') {
                                $dataArr[] = isset($vehicleLocations[$vehicle->vehicle_location_id]) ? $vehicleLocations[$vehicle->vehicle_location_id] : '';
                            } else if($label == 'Maintenance Event') {
                                $dataArr[] = $event['maintenanceType'];
                                $eventNameKey = count($dataArr);
                            } else if($label == 'Due Date') {
                                $dataArr[] = $eventDt->format('d M Y');
                                $dueDateKey = count($dataArr);
                            } else if($label == 'Repair/Maintenance Location') {
                                $dataArr[] = (!is_null($vehicle->repair_location)) ? $vehicle->repair_location->name: '';
                            }
                        }

                        array_push($dataArray, $dataArr);

                        if(isset($vehicle->futurePmiEvents) && $vehicle->futurePmiEvents && !$isPmiProcessed) {
                            foreach($vehicle->futurePmiEvents as $pmiKey => $pmiEvent) {
                                if(Carbon::parse($pmiEvent)->between($startDt, $endDt) && (empty($pmiArray) || !in_array($pmiEvent, $pmiArray))) {
                                    $dataArr[$eventNameKey-1] = 'PMI';
                                    $dataArr[$dueDateKey-1] = $pmiEvent;
                                    $pmiArray[] = $pmiEvent;
                                    array_push($dataArray, $dataArr);
                                }
                            }
                            $isPmiProcessed = true;
                        }
                    }
                }
            }
        }

        foreach ($vehiclesForServiceInspectionDistance as $key => $vehicle) {
            $plannedDate = Carbon::parse($vehicle->event_plan_date)->format('d M Y');
            $dataArr = [];
            foreach($labelArray as $label) {
                if($label == 'Registration') {
                    $dataArr[] = $vehicle->registration;
                } else if($label == 'Type') {
                    $dataArr[] = $vehicle->type->vehicle_type;
                } else if($label == 'Manufacturer') {
                    $dataArr[] = $vehicle->type->manufacturer;
                } else if($label == 'Model') {
                    $dataArr[] = $vehicle->type->model;
                } else if($label == 'Vehicle Division') {
                    $dataArr[] = isset($vehicleDivisions[$vehicle->vehicle_division_id]) ? $vehicleDivisions[$vehicle->vehicle_division_id] : '';
                } else if($label == 'Vehicle Region') {
                    $dataArr[] = isset($vehicleRegions[$vehicle->vehicle_region_id]) ? $vehicleRegions[$vehicle->vehicle_region_id] : '';
                } else if($label == 'Vehicle Location') {
                    $dataArr[] = isset($vehicleLocations[$vehicle->vehicle_location_id]) ? $vehicleLocations[$vehicle->vehicle_location_id] : '';
                } else if($label == 'Maintenance Event') {
                    $dataArr[] = 'Service (distance)';
                } else if($label == 'Due Date') {
                    $dataArr[] = $plannedDate;
                } else if($label == 'Repair/Maintenance Location') {
                    $dataArr[] = (!is_null($vehicle->repair_location)) ? $vehicle->repair_location->name: '';
                }
            }

            array_push($dataArray, $dataArr);
        }

        $reportTitle = $startDt->format('d M Y')." - ".$endDt->format('d M Y');

        if($newReportData) {
            $otherParams = $this->customReportOtherParam($reportTitle, $sheetTitle, $newReportData, $oldReportData);
        } else {
            $otherParams = $this->otherParams($data, "_VehicleMaintenance_Report_Standard", "Vehicle Maintenance Report", $reportTitle);
        }

        return $this->toExcel($labelArray,$dataArray,$otherParams);
    }

    public function downloadVehicleLocationReport($data, $reportDownload = null, $sheetTitle = null, $newReportData = null, $oldReportData = null)
    {
        $labelArray = $this->getReportLabels('standard_vehicle_location_report');

        $startDate = $this->commonHelper->convertBstToUtcWithParse($data['date_from']);
        $endDate = $this->commonHelper->convertBstToUtcWithParse($data['date_to']);

        $dataArray = [];
        if(!isset($data['accessible_regions'])) {
            $allRegions = $this->getUserAssetRegions('id');
        } else {
            $allRegions = $data['accessible_regions'];
        }

        $visibleColumns = 'vehicles.registration,vehicle_divisions.name AS vehicle_division_id,
                            vehicle_regions.name AS vehicle_region_id,
                            DATE_FORMAT(CONVERT_TZ(telematics_journeys.start_time, "UTC","Europe/London"), "%d %b %Y") AS telematics_journey_start_date,
                            DATE_FORMAT(CONVERT_TZ(telematics_journeys.start_time, "UTC","Europe/London"), "%H:%i:%s") AS telematics_journey_start_time,
                            DATE_FORMAT(CONVERT_TZ(telematics_journeys.end_time, "UTC","Europe/London"), "%H:%i:%s") AS telematics_journey_end_time,
                            CONCAT(telematics_journeys.start_lat,",",telematics_journeys.start_lon) AS journey_start_location,"" AS journey_start_map_link,
                            CONCAT(telematics_journeys.end_lat,",",telematics_journeys.end_lon) AS journey_end_location,"" AS journey_end_map_link,
                            companies.name AS company_id,users.email,users.first_name,users.last_name';

        $vehicleLocations = DB::table(DB::raw('telematics_journeys force index (telematics_journeys_start_time_index)'))
                            ->join('vehicles', 'telematics_journeys.vehicle_id', '=', 'vehicles.id')
                            ->leftjoin('vehicle_divisions','vehicles.vehicle_division_id', '=', 'vehicle_divisions.id')
                            ->leftjoin('vehicle_regions','vehicles.vehicle_region_id', '=', 'vehicle_regions.id')
                            ->join('users', 'telematics_journeys.user_id', '=', 'users.id')
                            ->leftJoin('companies', 'users.company_id', '=', 'companies.id')
                            ->whereNull('telematics_journeys.deleted_at')
                            ->where(function ($query) use ($startDate, $endDate) {
                                $query->where(function ($query) use ($startDate, $endDate) {
                                    $query->where('start_time', '>=', $startDate);
                                    $query->where('start_time', '<=', $endDate);
                                });
                                $query->orWhere(function ($query) use ($startDate, $endDate) {
                                    $query->where('end_time', '>=', $startDate);
                                    $query->where('end_time', '<=', $endDate);
                                });
                            })
                            ->where('vehicles.is_telematics_enabled','=','1')
                            ->whereNull('telematics_journeys.deleted_at')
                            ->whereIn('vehicles.vehicle_region_id', $allRegions)
                            ->selectRaw($visibleColumns)
                            ->get();

        $locationData = [];
        foreach($vehicleLocations as $location) {
            $locationData = [
                $location->registration,
                $location->company_id,
                $location->vehicle_division_id,
                $location->vehicle_region_id,
                $location->email,
                $location->first_name,
                $location->last_name,
                $location->telematics_journey_start_date,
                $location->telematics_journey_start_time,
                $location->telematics_journey_end_time,
                $location->journey_start_location,
                '',
                $location->journey_end_location,
                ''
            ];
            array_push($dataArray, $locationData);
        }

        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);
        $reportTitle = $startDate->format('d M Y')." - ".$endDate->format('d M Y');
        $durationColumnTitle = $startDate->format('H:i:s d M Y')." - ".$endDate->format('H:i:s d M Y');

        if($newReportData) {
            $otherParams = $this->customReportOtherParam($reportTitle, $sheetTitle, $newReportData, $oldReportData);
        } else {
            $otherParams = $this->otherParams($data, "_VehicleLocation_report_Standard", "Vehicle Location Report", $reportTitle);
        }
        $otherParams['sheetSubTitle_value_second'] = $durationColumnTitle;

        $otherParams['url'] = [ ['column' => 'L', 'prev_column' => 'K'], ['column' => 'N', 'prev_column' => 'M'], 'baseURL' => 'https://www.google.com/maps?q=' ];

        return $this->toExcel($labelArray,$dataArray,$otherParams);
    }

    public function downloadPMIPerformanceReport($data, $reportDownload = null, $sheetTitle = null, $newReportData = null, $oldReportData = null)
    {
        $pmiEventId = MaintenanceEvents::where('slug', 'preventative_maintenance_inspection')->first()->id;
        $maintenanceHistoryDataCount = VehicleMaintenanceHistory::where('event_plan_date', '>=', $data['date_from'])
                                                ->where('event_plan_date', '<=', $data['date_to'])
                                                ->where('event_type_id', $pmiEventId)
                                                ->selectRaw('distinct vehicle_id')
                                                ->count();
        if($reportDownload) {
            $labelArray = $reportDownload->reportDataset->pluck('title')->toArray();
        } else {
            $labelArray = $this->getReportLabels('standard_pmi_performance_report');
        }
        // $sortBy = $this->setOrderBy('standard_pmi_performance_report');
        $sortBy = 'event_plan_date';

        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);

        $vehicles = $this->customReportRepository->pmiPeformanceReport($data, $pmiEventId);
        $vehicles = $vehicles->orderBy($sortBy)->get();

        $dataArray = [];
        $incompleteStatusCount = $pendingStatusCount = $earlyStatusCount = $lateStatusCount = $onTimeStatusCount = 0;
        $today = Carbon::today();

        foreach ($vehicles as $vehicle) {

            $eventPlannedDate = Carbon::createFromFormat('d M Y', $vehicle->event_plan_date);
            if($vehicle->event_status == 'Incomplete') {
                if($eventPlannedDate->lt($today)) {
                    $status = 'Missed';
                    $incompleteStatusCount++;
                } else {
                    $status = 'Pending';
                    $pendingStatusCount++;
                }
            } else {
                $eventDate = Carbon::createFromFormat('d M Y', $vehicle->event_date);
                $eventDateToPlannedDate = $eventDate->diffInDays($eventPlannedDate);
                // $plannedDateToEventDate = $eventPlannedDate->diffInDays($eventDate);

                if($eventDate->lt($eventPlannedDate) && $eventDateToPlannedDate > 3) {
                    $status = 'Early';
                    $earlyStatusCount++;
                } else if($eventDateToPlannedDate > 6) {
                    $status = 'Late';
                    $lateStatusCount++;
                } else {
                    $status = 'On time';
                    $onTimeStatusCount++;
                }
            }

            $vehicleDataArr = [];
            foreach($labelArray as $label) {
                if($label == 'Registration') {
                    $vehicleDataArr[] = $vehicle->registration;
                } else if($label == 'Type') {
                    $vehicleDataArr[] = $vehicle->vehicle_type;
                } else if($label == 'Category') {
                    $vehicleDataArr[] = $vehicle->vehicle_category;
                } else if($label == 'Sub Category') {
                    $vehicleDataArr[] = $vehicle->vehicle_subcategory;
                } else if($label == 'Vehicle Division') {
                    $vehicleDataArr[] = $vehicle->division_name;
                } else if($label == 'Vehicle Region') {
                    $vehicleDataArr[] = $vehicle->region_name;
                } else if($label == 'Vehicle Location') {
                    $vehicleDataArr[] = $vehicle->location_name;
                } else if($label == 'PMI Planned Date') {
                    $vehicleDataArr[] = $vehicle->event_plan_date;
                } else if($label == 'PMI Actual Date') {
                    $vehicleDataArr[] = $vehicle->event_date;
                } else if($label == 'Repair/Maintenance Location') {
                    $vehicleDataArr[] = $vehicle->repair_location_name;
                } else if($label == 'Event Status') {
                    $vehicleDataArr[] = $status;
                }
            }
            array_push($dataArray, $vehicleDataArr);
        }

        $reportTitle = $startDate->format('d M Y')." to ".$endDate->format('d M Y');

        $totalFinishedServiceCount = $earlyStatusCount + $lateStatusCount + $onTimeStatusCount;
        $performance = $totalFinishedServiceCount == 0 ? 0 : number_format(round(($onTimeStatusCount * 100) / $maintenanceHistoryDataCount, 2), 2).'%';
        $dataCount = ['missedStatusCount' => $incompleteStatusCount, 'pendingStatusCount' => $pendingStatusCount, 'earlyStatusCount' => $earlyStatusCount, 'lateStatusCount' => $lateStatusCount, 'onTimeStatusCount' => $onTimeStatusCount, 'totalVehicles' => $maintenanceHistoryDataCount, 'totalFinishedServiceCount' => $totalFinishedServiceCount, 'performance' => $performance];

        if($newReportData) {
            $otherParams = $this->customReportOtherParam($reportTitle, $sheetTitle, $newReportData, $oldReportData);
        } else {
            $otherParams = $this->otherParams($data, "_VehicleServicePerformance_report_Standard", "Vehicle Service Performance Report", $reportTitle);
        }

        $otherParams['pmiDataCount'] = $dataCount;

        return $this->toExcel($labelArray, $dataArray, $otherParams);

    }

}
?>
