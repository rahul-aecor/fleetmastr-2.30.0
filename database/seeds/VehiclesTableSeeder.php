<?php

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\VehicleType;
use App\Models\VehicleLocations;
use App\Models\VehicleRepairLocations;
use App\Models\Vehicle;
use App\Models\VehicleUsageHistory;
use App\Models\User;
use Carbon\Carbon;
use App\Models\VehicleRegions;
use App\Models\VehicleDivisions;

class VehiclesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        $vehicleTypeArray = VehicleType::all()->keyBy('vehicle_type')->toArray();
        $vehicleRepairLocationArray = VehicleRepairLocations::lists('id','name')->toArray();
        $vehicleDivisionsArray = VehicleDivisions::lists('id','name')->toArray();
        $vehicleRegionsArray = VehicleRegions::lists('id','name')->toArray();
        $users = User::get(['id', 'email'])->keyBy('email');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('vehicles')->truncate();
        $filename = strtolower(env('BRAND_NAME')."/"."vehicles.txt");
        $file = fopen(base_path("database" . DIRECTORY_SEPARATOR . "seeds" . DIRECTORY_SEPARATOR . $filename), "r");
        $cntr = 0;
        while(!feof($file)){
            $line = fgets($file);
            // $line = rtrim($line);
            $dataArray = explode("\t", $line);
            if($cntr > 0) {
                $vehicle = new Vehicle;
                $vehicle['registration'] = $dataArray[0];
                $vehicle['dt_added_to_fleet'] = $dataArray[1] ? Carbon::createFromFormat('d/m/Y',trim($dataArray[1]))->format('d M Y') : null;
                $vehicle['vehicle_type_id'] = $vehicleTypeArray[trim($dataArray[2])]['id'];
                $vehicle['last_odometer_reading'] = $dataArray[3];
                $vehicle['status'] = $dataArray[4];
                $vehicle['usage_type'] = $dataArray[5];
                $vehicle['is_telematics_enabled'] = $dataArray[6] == 'Yes' ? 1 : 0;
                $vehicle['staus_owned_leased'] = $dataArray[7];
                $vehicle['nominated_driver'] = isset($users[$dataArray[8]]) ? $users[$dataArray[8]]->id : null;
                $vehicle['dt_registration'] = trim($dataArray[9]) && $dataArray[9] != 'N/A' ? Carbon::createFromFormat('d/m/Y',trim($dataArray[9]))->format('d M Y') : null;
                $vehicle['dt_first_use_inspection'] = trim($dataArray[10]) ? Carbon::createFromFormat('d/m/Y',trim($dataArray[10]))->format('d M Y') : null;
                $vehicle['lease_expiry_date'] = trim($dataArray[11]) ? Carbon::createFromFormat('d/m/Y',trim($dataArray[11]))->format('d M Y') : null;
                $vehicle['P11D_list_price'] = $dataArray[12] != '' ? $dataArray[12] : null;
                $vehicle['chassis_number'] = $dataArray[13] != '' ? $dataArray[13] : null;
                $vehicle['contract_id'] = $dataArray[14] != '' ? $dataArray[14] : null;
                $vehicle['vehicle_division_id'] = $vehicleDivisionsArray[trim($dataArray[16])];
                $vehicle['vehicle_region_id'] = $vehicleRegionsArray[trim($dataArray[17])];

                // $vehicle['vehicle_location_id'] = $dataArray[18] != '' ? $dataArray[18] : null;
                if($dataArray[18] != '') {
                    $vehicleLocation = VehicleLocations::where('name', $dataArray[18])->where('vehicle_region_id', $vehicle['vehicle_region_id'])->first();
                    $vehicle['vehicle_location_id'] = isset($vehicleLocation) ? $vehicleLocation->id : null;
                }

                $vehicle['vehicle_repair_location_id'] = $dataArray[20] != '' ? $vehicleRepairLocationArray[trim($dataArray[20])] : null;
                $vehicle['dt_annual_service_inspection'] = trim($dataArray[21]) ? Carbon::createFromFormat('d/m/Y',trim($dataArray[21]))->format('d M Y') : null;
                $vehicle['next_compressor_service'] = trim($dataArray[22]) ? Carbon::createFromFormat('d/m/Y',trim($dataArray[22]))->format('d M Y') : null;
                $vehicle['next_invertor_service_date'] = trim($dataArray[23]) ? Carbon::createFromFormat('d/m/Y',trim($dataArray[23]))->format('d M Y') : null;
                $vehicle['dt_loler_test_due'] = trim($dataArray[24]) ? Carbon::createFromFormat('d/m/Y',trim($dataArray[24]))->format('d M Y') : null;
                $vehicle['dt_repair_expiry'] = trim($dataArray[25]) ? Carbon::createFromFormat('d/m/Y',trim($dataArray[25]))->format('d M Y') : null;
                $vehicle['dt_mot_expiry'] = trim($dataArray[26]) ? Carbon::createFromFormat('d/m/Y',trim($dataArray[26]))->format('d M Y') : null;
                $vehicle['dt_next_service_inspection'] = trim($dataArray[27]) ? Carbon::createFromFormat('d/m/Y',trim($dataArray[27]))->format('d M Y') : null;

                // $vehicle['next_pmi_date'] = trim($dataArray[31]) ? date ("d M Y", strtotime($vehicleTypeArray[trim($dataArray[31])]['pmi_interval'], strtotime(Carbon::createFromFormat('d/m/Y',trim($dataArray[31]))->format('d M Y')))) : null;

                $vehicle['first_pmi_date'] = trim($dataArray[29]) ? Carbon::createFromFormat('d/m/Y',trim($dataArray[29]))->format('d M Y') : null;

                if(trim($dataArray[31])) {
                    $vehicle['next_pmi_date'] = Carbon::createFromFormat('d/m/Y',trim($dataArray[31]))->format('d M Y');
                } else {
                    if(trim($dataArray[29])) {
                        if($vehicleTypeArray[trim($dataArray[2])]['pmi_interval']) {
                            $vehicle['next_pmi_date'] = date ("d M Y", strtotime($vehicleTypeArray[trim($dataArray[2])]['pmi_interval'], strtotime(Carbon::createFromFormat('d/m/Y',trim($dataArray[29]))->format('d M Y'))));
                        } else {
                            $vehicle['next_pmi_date'] = trim($dataArray[29]) ? Carbon::createFromFormat('d/m/Y',trim($dataArray[29]))->format('d M Y') : null;
                        }
                    }
                }

                $vehicle['next_pto_service_date'] = trim($dataArray[32]) ? Carbon::createFromFormat('d/m/Y',trim($dataArray[32]))->format('d M Y') : null;
                $vehicle['dt_tacograch_calibration_due'] = trim($dataArray[33]) ? Carbon::createFromFormat('d/m/Y',trim($dataArray[33]))->format('d M Y') : null;
                $vehicle['dt_tax_expiry'] = trim($dataArray[34]) ? Carbon::createFromFormat('d/m/Y',trim($dataArray[34]))->format('d M Y') : null;
                $vehicle['adr_test_date'] = trim(isset($dataArray[35])) ? Carbon::createFromFormat('d/m/Y',trim($dataArray[35]))->format('d M Y') : null;
                $vehicle['tank_test_date'] = trim(isset($dataArray[36])) ? Carbon::createFromFormat('d/m/Y',trim($dataArray[36]))->format('d M Y') : null;


                // echo $vehicleTypeArray[trim($dataArray[2])]['pmi_interval'];
                // echo date ("d M Y", strtotime($vehicleTypeArray[trim($dataArray[2])]['pmi_interval'], strtotime(Carbon::createFromFormat('d/m/Y',trim($dataArray[30]))->format('d M Y'))));
                // $vehicle['next_pmi_date'] = trim($dataArray[30]) ? date ("d M Y", strtotime($vehicleTypeArray[trim($dataArray[2])]['pmi_interval'], strtotime(Carbon::createFromFormat('d/m/Y',trim($dataArray[30]))->format('d M Y')))) : null;
                // $vehicle['next_pmi_date'] = trim($dataArray[30]) ? Carbon::createFromFormat('d/m/Y',trim($dataArray[30]))->format('d M Y') : null;
                // $vehicle['dt_tacograch_calibration_due'] = trim($dataArray[32]) ? Carbon::createFromFormat('d/m/Y',trim($dataArray[32]))->format('d M Y') : null;
                // $vehicle['dt_tax_expiry'] = trim($dataArray[33]) ? Carbon::createFromFormat('d/m/Y',trim($dataArray[33]))->format('d M Y') : null;

                $vehicle['created_by'] = 1;
                $vehicle['updated_by'] = 1;
                $vehicle->save();

                if(isset($users[$dataArray[8]])) {
                    $vehicleHistory = new VehicleUsageHistory();
                    $vehicleHistory->user_id = $users[$dataArray[8]]->id;
                    $vehicleHistory->vehicle_id = $vehicle->id;
                    $vehicleHistory->from_date = Carbon::now();
                    $vehicleHistory->save();
                }
            }
            $cntr++;
        }
        fclose($file);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}