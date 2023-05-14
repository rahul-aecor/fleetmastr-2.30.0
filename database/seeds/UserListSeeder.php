<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\UserRegion;
use App\Models\UserDivision;
use App\Models\UserLocation;
use App\Models\VehicleDivisions;
use App\Models\VehicleRegions;

class UserListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userfile = strtolower(env('BRAND_NAME')."/"."users.txt");
        $file = fopen(base_path("database" . DIRECTORY_SEPARATOR . "seeds" . DIRECTORY_SEPARATOR . $userfile), "r");
        $userDivision = UserDivision::get();
        $usetRegion = UserRegion::get();
        $userData = User::select('id', \DB::raw("CONCAT(first_name, ' ', last_name) as full_name"))->get()->pluck('full_name', 'id')->toArray();

        if(fgets($file)) {
            while(!feof($file)){
                $line = fgets($file);
                $line = rtrim($line);
                $dataArray = explode("\t", $line);
                $companyId = Company::where('name', '=', $dataArray[2])->first();
                $user = new User();
                $user->first_name = trim($dataArray[0]);
                $user->last_name = trim($dataArray[1]);
                $user->company_id = isset($companyId->id) ? $companyId->id : '';
                if(trim($dataArray[3]) != '') {
                    $user->email = strtolower(trim($dataArray[3]));
                    $user->username = strtolower(trim($dataArray[3]));
                } else {
                    $user->email = strtolower($user->first_name.'.'.$user->last_name.'@'.env('BRAND_NAME').'-imastr.com');
                    $user->username = strtolower($user->first_name.'.'.$user->last_name);
                }
                $UserRegionId = $UserDivisionId = null;
                if(trim($dataArray[4]) != '')
                {
                    $UserDivisionId = UserDivision::where('name',$dataArray[4])->select('id')->first();
                }
                if(trim($dataArray[5]) != '')
                {
                    $UserRegionId = UserRegion::where('name',$dataArray[5])->select('id')->first();
                }
                $user->user_division_id = $UserDivisionId ? $UserDivisionId->id : null;
                $user->user_region_id = $UserRegionId ? $UserRegionId->id : null;
                $user->job_title = isset($dataArray[6]) ? $dataArray[6] : '';
                $user->mobile = isset($dataArray[7]) ? $dataArray[7] : '';
                $user->landline = isset($dataArray[8]) ? $dataArray[8] : '';

                if(trim($dataArray[3]) == '') {
                    $user->is_verified = 1;
                    $user->is_active = 1;
                    $user->is_default_password = 1;
                    $user->password = bcrypt(env('DEFAULT_PASSWORD'));
                }
                
                $user->enable_login = isset($dataArray[10]) == 'Yes' ? 1 : 0;
                $user->imei = isset($dataArray[11]) ? $dataArray[11] : '';
                $user->line_manager = isset($userData[$dataArray[12]]) ? $userData[$dataArray[12]] : '';
                $user->base_location = isset($dataArray[13]) ? $dataArray[13] : '';
                if($user->base_location != '' && $user->user_region_id) {
                    $userLocation = UserLocation::where('name', $dataArray[13])->where('user_region_id', $user->user_region_id)->first();
                    $user->user_locations_id = isset($userLocation) ? $userLocation->id : null;
                }
                $user->fuel_card_issued = isset($dataArray[14]) ? $dataArray[14] : '';

                // if($dataArray[12] != '')
                // {
                //     $UserRegionsId = UserRegion::where('name',$dataArray[8])->select('id')->first();    
                // }


                // if(env('IS_DIVISION_REGION_LINKED_IN_USER'))
                // {
                //     if($dataArray[8] != '')
                //     {
                //         $UserRegionsId = UserRegion::where('name',$dataArray[8])->where('user_division_id',$UserDivisionId->id)->select('id')->first();
                //     }
                // }
                // $user->job_title = $dataArray[5];
                // $user->engineer_id = $dataArray[6];
                // $user->enable_login = TRUE;
                // $user->user_region_id = $UserRegionsId ? $UserRegionsId->id : null ;//$dataArray[8];

                // dump($user);
                if($user->save()){
                
                    $userId = $user->id;
                    $name = $user->first_name.' '.$user->last_name;
                    $userData[$name] = $userId;
                    echo "Inserted user with ID : $userId \n";

                    if(isset($dataArray[32]) && $dataArray[32] == 'Yes') {
                        $user->divisions()->sync($userDivision);
                        $user->regions()->sync($usetRegion);
                    }
                   
                    $appAccessRole = '';
                    if($dataArray[15] == 'App access only') {
                        $appAccessRole = trim($dataArray[15]," only");
                    }

                    if($appAccessRole){
                        DB::connection('mysql_new')->table('role_user')->insert(['user_id' => $userId, 'role_id' => 8] );
                    }

                    // if ($dataArray[15] == 'User information only' ) {
                    //     DB::connection('mysql_new')->table('role_user')->insert(['user_id' => $userId, 'role_id' => 14] );
                    // }

                    if ($dataArray[15] == 'Super admin' ) {
                        DB::connection('mysql_new')->table('role_user')->insert(['user_id' => $userId, 'role_id' => 1] );
                        continue;
                    }

                    //16 -> Dashboard (statistics)
                    if(isset($dataArray[16]) && $dataArray[16] == 'Yes'){
                        DB::connection('mysql_new')->table('role_user')->insert(['user_id' => $userId, 'role_id' => 15] );
                    }

                    //17 -> Dashboard (costs)
                    if(isset($dataArray[17]) && $dataArray[17] == 'Yes'){
                        DB::connection('mysql_new')->table('role_user')->insert(['user_id' => $userId, 'role_id' => 19] );
                    }

                    //18 -> Fleet planning
                    if(isset($dataArray[18]) && $dataArray[18] == 'Yes'){
                        DB::connection('mysql_new')->table('role_user')->insert(['user_id' => $userId, 'role_id' => 16] );
                    }

                    //19 -> Vehicle Checks
                    if(isset($dataArray[19]) && $dataArray[19] == 'Yes'){
                        DB::connection('mysql_new')->table('role_user')->insert(['user_id' => $userId, 'role_id' => 2] );
                    }

                    //20 -> Vehicle Defects
                    if(isset($dataArray[20]) && $dataArray[20] == 'Yes'){
                        DB::connection('mysql_new')->table('role_user')->insert(['user_id' => $userId, 'role_id' => 3] );
                    }

                    //21 -> Incident reports
                    if(isset($dataArray[21]) && $dataArray[21] == 'Yes'){
                        DB::connection('mysql_new')->table('role_user')->insert(['user_id' => $userId, 'role_id' => 18] );
                    }

                    //22 -> Vehicle search
                    if(isset($dataArray[22]) && $dataArray[22] == 'Yes'){
                        DB::connection('mysql_new')->table('role_user')->insert(['user_id' => $userId, 'role_id' => 4] );
                    }

                    //23 -> Vehicle profiles
                    if(isset($dataArray[23]) && $dataArray[23] == 'Yes'){
                        DB::connection('mysql_new')->table('role_user')->insert(['user_id' => $userId, 'role_id' => 11] );
                    }

                    //24 -> Reports
                    if(isset($dataArray[24]) && $dataArray[24] == 'Yes'){
                        DB::connection('mysql_new')->table('role_user')->insert(['user_id' => $userId, 'role_id' => 5] );
                    }

                    //25 -> Messaging
                    if(isset($dataArray[25]) && $dataArray[25] == 'Yes'){
                        DB::connection('mysql_new')->table('role_user')->insert(['user_id' => $userId, 'role_id' => 10] );
                    }

                    //26 -> Workshops
                    if(isset($dataArray[26]) && $dataArray[26] == 'Yes'){
                        DB::connection('mysql_new')->table('role_user')->insert(['user_id' => $userId, 'role_id' => 9] );
                    }

                    //27 -> User management
                    if(isset($dataArray[27]) && $dataArray[27] == 'Yes'){
                        DB::connection('mysql_new')->table('role_user')->insert(['user_id' => $userId, 'role_id' => 6] );
                    }

                    //28 -> Alert Centre
                    if(isset($dataArray[28]) && $dataArray[28] == 'Yes'){
                        DB::connection('mysql_new')->table('role_user')->insert(['user_id' => $userId, 'role_id' => 21] );
                    }

                    //29 -> Settings
                    if(isset($dataArray[29]) && $dataArray[29] == 'Yes'){
                        DB::connection('mysql_new')->table('role_user')->insert(['user_id' => $userId, 'role_id' => 17] );
                    }

                    //30 -> Telematics
                    if(isset($dataArray[30]) && $dataArray[30] == 'Yes'){
                        DB::connection('mysql_new')->table('role_user')->insert(['user_id' => $userId, 'role_id' => 23] );
                    }

                    /*** FOR FUTURE USER ***/
                    //Earned recognition -> ROLE ID 22
                    //Manage DVSA configurations -> ROLE ID 24

                }
            }
            fclose($file);
        } else {
            fclose($file);
        }

    }
}