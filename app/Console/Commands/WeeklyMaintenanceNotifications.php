<?php

namespace App\Console\Commands;

use Mail;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Vehicle;
use App\Custom\Helper\Common;
use Illuminate\Console\Command;
use App\Models\VehicleMaintenanceNotification;
use App\Models\VehicleDivisions;
use App\Models\VehicleRegions;
use App\Models\VehicleLocations;

class WeeklyMaintenanceNotifications extends Command
{
    private $commonHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weekly:maintenanceNotifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will send every week (i.e. every monday) maintenance notification emails';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Common $commonHelper)
    {
        $this->commonHelper = $commonHelper;
        parent::__construct();
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        if(setting('maintenance_reminder_notification') == 0) {
            return false;
        }

        $events = config('config-variables.eventRemindersNotifications');
        $serviceInspectionDistanceKey = array_search('next_service_inspection_distance', array_column($events, 'event'));
        unset($events[$serviceInspectionDistanceKey]);

        $vehicleDivisions = VehicleDivisions::orderBy('name','ASC')->lists('name', 'id')->toArray();
        $vehicleRegions = VehicleRegions::orderBy('name','ASC')->lists('name', 'id')->toArray();
        $vehicleLocations = VehicleLocations::orderBy('name','ASC')->lists('name', 'id')->toArray();

        // $startDt = Carbon::now()->startOfWeek();
        // $endDt = Carbon::now()->endOfWeek();
        $startDt = Carbon::now()->addDays(5)->startOfWeek();
        $endDt = Carbon::now()->addDays(12)->endOfWeek(); // Changed 5 to 12 - FLEE-6724

        $vehicles = Vehicle::where(function($query) use ($events, $startDt, $endDt) {
            foreach ($events as $event => $values) {
                $column = $values['column'];
                $query = $query->orWhere(function($q) use ($column, $startDt, $endDt) {
                    $q->whereNotNull($column)
                        ->where($column, '>=', $startDt)
                        ->where($column, '<=', $endDt);
                });
            }
        })->get();

        $vehiclesForServiceInspectionDistance = Vehicle::with('type')
                    ->join('vehicle_maintenance_history','vehicles.id', '=', 'vehicle_maintenance_history.vehicle_id')
                    ->whereRaw('vehicles.next_service_inspection_distance = vehicle_maintenance_history.event_planned_distance')
                    ->where('vehicle_maintenance_history.event_status', '=', 'Incomplete')
                    ->where('event_plan_date', '>=', $startDt)
                    ->where('event_plan_date', '<=', $endDt)
                    ->select('vehicles.*', 'vehicle_maintenance_history.event_plan_date')
                    ->get();

        $excelFileDetail=array(
            'title' => "Weekly Maintenance Report"
        );

        $notifications = VehicleMaintenanceNotification::where('event_type', 'weekly_maintenance')
                            ->where('is_enabled', true)
                            ->get();

        $userEmail = '';
        $userFirstName = '';
        foreach ($notifications as $key => $notification) {
            $userInfo = User::where('id',$notification->user_id)->first();
            if($userInfo && $vehicles != null && $notification) {
                $sheet = [];
                $eventList = [];
                $sheetArray = [];
                $userEmail = $userInfo->email;
                $userFirstName = $userInfo->first_name;
                $userRegions = $userInfo ? $userInfo->regions->lists('id')->toArray() : [];

                // $vehicles = null;
                if (!empty($userRegions)) {
                    $vehicles = Vehicle::with('type')
                                ->whereIn('vehicle_region_id', $userRegions)->where(function($query) use ($events, $startDt, $endDt) {
                        foreach ($events as $event => $values) {
                            $column = $values['column'];
                            $query = $query->orWhere(function($q) use ($column, $startDt, $endDt) {
                                    $q->whereNotNull($column)
                                        ->where($column, '>=', $startDt)
                                        ->where($column, '<=', $endDt);
                            });
                        }
                    })->get();
                }

                $sheet['labelArray'] = [
                    'Registration', 'Type', 'Manufacturer', 'Model', 'Division', 'Region', 'Location', 'Maintenance Type', 'Due Date', 'Repair/Maintenance location'
                ];
                $sheet['dataArray'] = array();

                // Collecting events list for the vehicle in the current week
                foreach ($vehicles as $key => $vehicle) {
                    foreach ($events as $event) {
                        $eventDt = $vehicle->toArray()[$event['column']];
                        if($eventDt) {
                            $eventDt = Carbon::parse($eventDt);
                            if($eventDt->between($startDt, $endDt)) {
                                $eventList[] = [
                                    'event' => $event['maintenanceType'], 
                                    'dueDt' => $eventDt->format('jS F Y')
                                ];
                                $data = [
                                    $vehicle->registration,
                                    $vehicle->type->vehicle_type,
                                    $vehicle->type->manufacturer,
                                    $vehicle->type->model,
                                    isset($vehicleDivisions[$vehicle->vehicle_division_id]) ? $vehicleDivisions[$vehicle->vehicle_division_id] : '',
                                    isset($vehicleRegions[$vehicle->vehicle_region_id]) ? $vehicleRegions[$vehicle->vehicle_region_id] : '',
                                    isset($vehicleLocations[$vehicle->vehicle_location_id]) ? $vehicleLocations[$vehicle->vehicle_location_id] : '',
                                    $event['maintenanceType'],
                                    $eventDt->format('jS F Y'),
                                    (!is_null($vehicle->repair_location)) ? $vehicle->repair_location->name: '',
                                ];
                                array_push($sheet['dataArray'], $data);
                            }
                        }
                    }
                }

                foreach ($vehiclesForServiceInspectionDistance as $key => $vehicle) {
                    $plannedDate = Carbon::parse($vehicle->event_plan_date)->format('jS F Y');
                    $eventList[] = ['event' => 'next_service_inspection_distance', 'dueDt' => $plannedDate];
                    $data = [
                        $vehicle->registration,
                        $vehicle->type->vehicle_type,
                        $vehicle->type->manufacturer,
                        $vehicle->type->model,
                        isset($vehicleDivisions[$vehicle->vehicle_division_id]) ? $vehicleDivisions[$vehicle->vehicle_division_id] : '',
                        isset($vehicleRegions[$vehicle->vehicle_region_id]) ? $vehicleRegions[$vehicle->vehicle_region_id] : '',
                        isset($vehicleLocations[$vehicle->vehicle_location_id]) ? $vehicleLocations[$vehicle->vehicle_location_id] : '',
                        'Service (distance)',
                        $plannedDate,
                        (!is_null($vehicle->repair_location)) ? $vehicle->repair_location->name: '',
                    ];
                    array_push($sheet['dataArray'], $data);
                }

                if(count($sheet['dataArray']) > 0) {
                    $sheet['otherParams'] = [
                        'sheetName' => "Weekly maintenance report"
                    ];

                    $sheet['columnFormat'] = array();
                    array_push($sheetArray, $sheet);

                    $userEmail = $userInfo->email;
                    $userFirstName = $userInfo->first_name;

                    $exportFile = $this->commonHelper->toExcel($excelFileDetail, $sheetArray, 'xlsx', 'no');

                    $registration = $vehicle->registration;
                    $email = $userEmail;
                    $vehicleLink = url("/vehicles");

                    // Sending main notification to the nominated driver for the vehicle
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        \Log::info('weekly:maintenanceNotifications mail has been sent to '.$email);
                        Mail::queue('emails.weekly_maintenance_notification', ['userName' => $userFirstName, 'eventList' => $eventList, 'vehicleLink' => $vehicleLink], function ($message) use ($email, $userFirstName, $exportFile, $vehicleLink) 
                        {
                            $message->to($email, $userFirstName, $vehicleLink);
                            $message->subject('fleetmastr - weekly vehicle planning and maintenance summary');
                            $message->attach($exportFile, [
                                'as' => "Weekly vehicle maintenance report ".Carbon::now()->format('Y-m-d').".xlsx"
                            ]);
                        });
                    }
                }
            }
        }   
    }
}
