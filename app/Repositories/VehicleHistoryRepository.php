<?php
namespace App\Repositories;

use App\Models\User;
use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;

class VehicleHistoryRepository extends EloquentRepositoryAbstract {
	public function __construct($data) {
		$this->Database = DB::table('vehicle_usage_history')
			->select('vehicle_usage_history.from_date', 'vehicle_usage_history.to_date', 'users.first_name', 'users.last_name', 'vehicles.id as vehicleId', 'vehicle_usage_history.id as id')
			->leftJoin('vehicles', 'vehicles.id', '=', 'vehicle_usage_history.vehicle_id')
      ->leftJoin('users', 'users.id', '=', 'vehicle_usage_history.user_id')
			->where('vehicle_usage_history.vehicle_id', '=', $data['vehicle_id']);

		if(isset($data['startRange']) && $data['startRange'] !='' && isset($data['endRange']) && $data['endRange'] != '') {
				$this->Database = $this->Database->where(function($query) use($data) {
						$query->where(function($query1) use($data){
								$query1->whereDate('from_date', '>=', $data['startRange'])
								->whereDate('from_date', '<=', $data['endRange']);
						})->orWhere(function($query2) use($data){
								$query2->whereDate('to_date', '>=', $data['startRange'])
								->whereDate('to_date', '<=', $data['endRange']);
						})->orWhere(function($query3) use($data){
								$query3->whereRaw('? between from_date and to_date', [$data['startRange']])
									->whereRaw('? between from_date and to_date', [$data['endRange']]);
						});
				});
		}

		$this->visibleColumns = [
      'vehicle_usage_history.id',
      'users.first_name', 'users.last_name', 'vehicles.registration', 'vehicleId',
      DB::raw("DATE_FORMAT(vehicle_usage_history.from_date, '%d %b %Y') as 'from_date'"),
      DB::raw("DATE_FORMAT(vehicle_usage_history.to_date, '%d %b %Y') as 'to_date'"),
		];

		$this->orderBy = [['vehicles.registration', 'ASC']];
	}
}
