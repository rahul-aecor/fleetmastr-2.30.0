<?php
namespace App\Repositories;

use Auth;
use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;

class WorkshopDefectsRepository extends EloquentRepositoryAbstract {

    public function __construct($input)
    {
        $workshopid = $input['id'];

        $this->Database = DB::table('defects')
            ->select('defects.id', 'defects.status', 'companies.name as workshop_name',
                'vehicles.registration', 'defects.vehicle_id', 'defects.workshop',
                'defect_master.defect','defect_master.page_title',
                'vehicles.dt_added_to_fleet','vehicles.status as vehicleStatus',
                'vehicle_types.vehicle_category', 'vehicle_types.vehicle_type',
                'vehicle_types.manufacturer', 'vehicle_types.oil_grade', 'vehicle_types.model', 
                'defects.description', 'checks.odometer_reading', 'checks.type', 
                'vehicle_regions.name as vehicle_region', 'defects.duplicate_flag',
                'defects.est_completion_date', 'defects.cost','defects.invoice_date', 
                'createdUser.first_name as driver_first_name', 'createdUser.last_name as driver_last_name',
                'defects.invoice_number','createdUser.first_name', 'createdUser.last_name',
                DB::raw("CONCAT(createdUser.first_name, ' ', createdUser.last_name) as createdBy "),
                DB::raw("CONCAT(updatedUser.first_name, ' ', updatedUser.last_name) as updatedBy "),
                DB::raw("DATE_FORMAT(CONVERT_TZ(defects.created_at, 'UTC', '".config('config-variables.format.displayTimezone')."'),
                    '%H:%i:%s %d %b %Y') as 'date_created_reported'"),
                DB::raw("DATE_FORMAT(CONVERT_TZ(defects.report_datetime, 'UTC', '".config('config-variables.format.displayTimezone')."'),
                    '%H:%i:%s %d %b %Y') as 'report_datetime'"),
                DB::raw("CONVERT_TZ(defects.updated_at, 'UTC', '".config('config-variables.format.displayTimezone')."') as 'modified_date'"))
            ->leftJoin('users as workshop', 'defects.workshop' , '=' ,'workshop.id')
            ->leftJoin('companies', 'companies.id', '=', 'workshop.company_id')
            ->join('vehicles', 'defects.vehicle_id', '=', 'vehicles.id')
            ->leftjoin('vehicle_divisions','vehicles.vehicle_division_id', '=', 'vehicle_divisions.id')
            ->leftjoin('vehicle_regions','vehicles.vehicle_region_id', '=', 'vehicle_regions.id')
            ->join('defect_master', 'defects.defect_master_id', '=', 'defect_master.id')
            ->join('checks', 'defects.check_id', '=', 'checks.id')
            ->join('users as createdUser', 'defects.created_by', '=', 'createdUser.id')
            ->leftjoin('users as updatedUser', 'defects.updated_by', '=', 'updatedUser.id')
            ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
            ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
            ->where('defects.workshop',$workshopid)
            // ->where('defects.status','!=','Resolved')
            ->whereNull('defects.deleted_at')
            ->whereNull('vehicles.deleted_at');
        $this->visibleColumns = [
            'defects.id', 'defects.status', 'companies.name as workshop_name','defects.description',
            'vehicles.registration','defects.vehicle_id','defects.duplicate_flag',
            'defect_master.defect','defect_master.page_title','vehicles.dt_added_to_fleet', 'vehicle_types.vehicle_category','vehicle_types.vehicle_type','vehicle_regions.name as vehicle_region',
            'vehicle_types.manufacturer','vehicle_types.model','checks.odometer_reading','vehicles.status as vehicleStatus', 'defects.created_at', 'defects.report_datetime',
            'checks.type', 'defects.est_completion_date','defects.workshop', 'date_created_reported',
            'defects.cost','defects.invoice_date','defects.invoice_number', 'createdBy', 'updatedBy'
        ];
        $this->orderBy = [['defects.report_datetime', 'DESC']];

    }
}

