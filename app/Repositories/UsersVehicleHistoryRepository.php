<?php
namespace App\Repositories;

use App\Models\User;
use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;

class UsersVehicleHistoryRepository extends EloquentRepositoryAbstract {
	public function __construct($id) {
		$this->Database = DB::table('vehicle_usage_history')
			->select('vehicle_usage_history.from_date', 'vehicle_usage_history.to_date', 'vehicles.P11D_list_price', 'vehicles.registration', 'vehicles.id as vehicleId', 'vehicle_usage_history.id as id')
			->leftJoin('vehicles', 'vehicles.id', '=', 'vehicle_usage_history.vehicle_id')
			->where('vehicle_usage_history.user_id', '=', $id);

		$this->visibleColumns = [
			'vehicle_usage_history.id', 'vehicle_usage_history.from_date', 'vehicle_usage_history.to_date',
			'vehicles.P11D_list_price', 'vehicles.registration', 'vehicleId'
		];

		$this->orderBy = [['vehicle_usage_history.created_at', 'DESC']];
	}
}