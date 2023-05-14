<?php
namespace App\Repositories;

use App\Models\User;
use \Illuminate\Support\Facades\DB;
use App\Custom\Helper\P11dReportHelper;
use App\Custom\Repositories\EloquentRepositoryAbstract;

class UsersVehiclePrivateUseRepository extends EloquentRepositoryAbstract {
	public function __construct($id) {
		$p11dReportHelper = new P11dReportHelper();
		$this->Database = DB::table('private_use_logs')
			->select('private_use_logs.start_date', 'private_use_logs.end_date', 'vehicles.P11D_list_price', 'vehicles.registration', 'vehicles.id as vehicleId', 'private_use_logs.id as id')
			->leftJoin('vehicles', 'vehicles.id', '=', 'private_use_logs.vehicle_id')
			->where(['private_use_logs.user_id'=>$id, 'private_use_logs.tax_year' => $p11dReportHelper->calcTaxYear(), 'private_use_logs.deleted_at'=>NULL]);

		$this->visibleColumns = [
			'private_use_logs.id', 'private_use_logs.start_date', 'private_use_logs.end_date',
			'vehicles.P11D_list_price', 'vehicles.registration', 'vehicleId'
		];

		$this->orderBy = [['private_use_logs.created_at', 'DESC']];
	}
}