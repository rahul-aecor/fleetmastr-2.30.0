<?php
namespace App\Repositories;

use Auth;
use App\Models\User;
use App\Models\Check;
use App\Models\Defect;
use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;
use \Carbon\Carbon;

class VehicleAssignmentRepository extends EloquentRepositoryAbstract {

    public function __construct($data)
    {
        $vehicleId = $data['vehicle_id'];
        $this->Database = DB::table('vehicle_assignment')
            ->leftjoin('vehicles','vehicle_assignment.vehicle_id', '=', 'vehicles.id')
            ->leftjoin('vehicle_divisions','vehicle_assignment.vehicle_division_id', '=', 'vehicle_divisions.id')
            ->leftjoin('vehicle_regions','vehicle_assignment.vehicle_region_id', '=', 'vehicle_regions.id')
            ->leftjoin('vehicle_locations','vehicle_assignment.vehicle_location_id', '=', 'vehicle_locations.id')
            ->where('vehicle_assignment.vehicle_id',$vehicleId)
            ->select('vehicle_assignment.id',
                'vehicles.registration as registration','vehicle_divisions.name as vehicle_divisions','vehicle_regions.name as vehicle_regions','vehicle_locations.name as vehicle_locations',
                DB::raw("DATE_FORMAT(vehicle_assignment.from_date, '%d %b %Y') as 'from_date'"),
                DB::raw("DATE_FORMAT(vehicle_assignment.to_date, '%d %b %Y') as 'to_date'"));


        if(isset($data['startRange']) && $data['startRange'] !='' && isset($data['endRange']) && $data['endRange'] != '') {
            $this->Database = $this->Database->where(function($query) use($data) {
                $query->where(function($query1) use($data){
                    $query1->where('from_date', '>=', $data['startRange'])
                    ->where('from_date', '<=', $data['endRange']);
                })->orWhere(function($query2) use($data){
                    $query2->where('to_date', '>=', $data['startRange'])
                    ->where('to_date', '<=', $data['endRange']);
                })->orWhere(function($query3) use($data){
                    $query3->whereRaw('? between from_date and to_date', [$data['startRange']])
                    ->whereRaw('? between from_date and to_date', [$data['endRange']]);
                });
            });
        }

        $this->visibleColumns = [
                'vehicle_assignment.id',
                'vehicles.registration as registration','vehicle_divisions.name as vehicle_divisions','vehicle_regions.name as vehicle_regions','vehicle_locations.name as vehicle_locations',
                DB::raw("DATE_FORMAT(vehicle_assignment.from_date, '%d %b %Y') as 'from_date'"),
                DB::raw("DATE_FORMAT(vehicle_assignment.to_date, '%d %b %Y') as 'to_date'"),

        ];
        $this->orderBy = [['registration', 'ASC']];
    }
}
