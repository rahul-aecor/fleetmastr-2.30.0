<?php
namespace App\Repositories;

use Auth;
use App\Models\User;
use App\Models\Check;
use App\Models\Defect;
use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;
use \Carbon\Carbon;

class VehicleMaintenanceHistoryRepository extends EloquentRepositoryAbstract {

    public function __construct($data)
    {
        $this->Database = DB::table('vehicle_maintenance_history')
            ->leftjoin('maintenance_events','maintenance_events.id','=','vehicle_maintenance_history.event_type_id')
            ->leftjoin('users as createdUser', 'vehicle_maintenance_history.created_by', '=', 'createdUser.id')
            ->leftjoin('users as updatedUser', 'vehicle_maintenance_history.updated_by', '=', 'updatedUser.id')
            ->leftjoin('vehicles','vehicle_maintenance_history.vehicle_id', '=', 'vehicles.id')
            ->leftjoin('vehicle_types','vehicles.vehicle_type_id', '=', 'vehicle_types.id')
            ->select('vehicle_maintenance_history.id',
                'vehicle_maintenance_history.event_status',
                'vehicle_maintenance_history.odomerter_reading',
                'vehicle_maintenance_history.event_planned_distance',
                DB::raw("DATE_FORMAT(vehicle_maintenance_history.event_plan_date, '%d %b %Y') as 'event_plan_date'"),'event_date',
                // DB::raw("DATE_FORMAT(vehicle_maintenance_history.event_date, '%d %b %Y') as 'event_date'"),
                'vehicle_maintenance_history.created_by', 
                'vehicle_maintenance_history.updated_by', 
                DB::raw("CONCAT(createdUser.first_name, ' ', createdUser.last_name) as createdBy"),'vehicle_maintenance_history.vehicle_id',
                DB::raw("CONCAT(updatedUser.first_name, ' ', updatedUser.last_name) as updatedBy"),
                'vehicles.registration as registration',
                \DB::raw("(SELECT count(*) FROM media
                          WHERE media.model_id = vehicle_maintenance_history.id 
                          AND media.collection_name = 'vehicle_maintenance_docs'
                        ) as documentCount"),
            'maintenance_events.name',
            'maintenance_events.slug',
            'vehicle_maintenance_history.comment',
            'vehicle_types.odometer_setting'
        );

        if (isset($data['searchByDate']) && $data['searchByDate'] != null && count($data['searchByDate']) > 1) {
            $searchStr = $data['searchByDate'];
            $this->Database = $this->Database->where(function($query) use ($searchStr) {
                $startRange = Carbon::createFromFormat('d/m/Y', $searchStr[0])->format('Y-m-d');
                $endRange = Carbon::createFromFormat('d/m/Y', $searchStr[1])->addDay(1)->format('Y-m-d');

                $query->where(function($query1) use($startRange, $endRange) {
                    $query1->where('vehicle_maintenance_history.event_date', '>=', $startRange)
                    ->where('vehicle_maintenance_history.event_date', '<', $endRange);
                })->orWhere(function($query2) use($startRange, $endRange) {
                    $query2->where('vehicle_maintenance_history.event_plan_date', '>=', $startRange)
                    ->where('vehicle_maintenance_history.event_plan_date', '<', $endRange);
                });
            });
        }

        if (isset($data['filters']) && $data['filters'] != "") {
            foreach (json_decode($data['filters'])->rules as $filter) {
                if ($filter->field == 'event_type_id') {
                    $this->Database->where('event_type_id', $filter->data);
                }
                if ($filter->field == 'event_date') {
                    $this->Database->whereDate('event_date', '=', $filter->data);
                }
                if ($filter->field == 'vehicle_id') {
                    $this->Database->where('vehicle_id', $filter->data);
                }
            }
        } else {
            $this->Database->where('vehicle_id', $data['vehicle_id']);
        }

        $this->visibleColumns = [
                'vehicle_maintenance_history.id',
                'vehicle_maintenance_history.event_status',
                DB::raw("DATE_FORMAT(vehicle_maintenance_history.event_plan_date, '%d %b %Y') as 'event_plan_date'"),'event_date',
                // DB::raw("DATE_FORMAT(vehicle_maintenance_history.event_date, '%d %b %Y') as 'event_date'"),
                'vehicle_maintenance_history.created_by', 
                'vehicle_maintenance_history.updated_by', 
                DB::raw("CONCAT(createdUser.first_name, ' ', createdUser.last_name) as createdBy"),'vehicle_maintenance_history.vehicle_id',
                DB::raw("CONCAT(updatedUser.first_name, ' ', updatedUser.last_name) as updatedBy"),
                'vehicles.registration as registration',
                \DB::raw("(SELECT count(*) FROM media
                          WHERE media.model_id = vehicle_maintenance_history.id 
                          AND media.collection_name = 'vehicle_maintenance_docs'
                        ) as documentCount"),
            'maintenance_events.name',
            'maintenance_events.slug'
        ];
        $this->orderBy = [['event_date', 'DESC']];
    }
}
