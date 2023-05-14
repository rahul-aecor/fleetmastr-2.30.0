<?php

use Illuminate\Database\Seeder;

class UserSyncSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $curr_user_list = DB::connection('mysql_new')->table('users')->lists('email');
        foreach ($curr_user_list as $key => $value) {
            $curr_user_list[$key] = trim(strtolower($value));
        }
        // echo (in_array('ndeopura@aecordigital.com',$curr_user_list))?"yes":"no";
        
        // print_r($curr_user_list);
        // exit;

        $file = fopen(base_path("database" . DIRECTORY_SEPARATOR . "seeds" . DIRECTORY_SEPARATOR . "user_sync.txt"), "r");
        $cntr = 0;
        while(!feof($file)){
            $line = fgets($file);
            $line = rtrim($line);
	    if (trim($line) == ""){
		continue;
	    }
            $dataArray = explode("\t", $line);

            $imeiStr = "";

            if( strpos($dataArray[2], '(') !== false ){ }
            else
            {
            	if (!in_array(trim(strtolower($dataArray[0])), $curr_user_list)){
	            	// print_r($dataArray);
			        $user = factory(App\Models\User::class)->make()->toArray();
			        $user['email'] = trim($dataArray[0]);
			        $user['password'] = bcrypt('l@nesGr0up');
			        $user['first_name'] = trim($dataArray[1]);
			        $user['last_name'] = trim($dataArray[2]);
			        //$user['region'] = null;
			        $user['imei'] = $imeiStr;
                    $user['enable_login'] = 0;
                    $user['is_lanes_account'] = 1;
                    // echo "$cntr\n";
                    // print_r($user);
			        $userId = DB::connection('mysql_new')->table('users')->insertGetId($user);
			        echo "Inserted user with ID : $userId : " . $user['email'] ." \n";
					DB::connection('mysql_new')->table('role_user')->insert( ['user_id' => $userId, 'role_id' => 8] );
	            	$cntr++;
            	}
            }
        }
        fclose($file);
    }
}
