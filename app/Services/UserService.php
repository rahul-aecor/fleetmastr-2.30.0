<?php
namespace App\Services;

use App\Models\UserVerification;
use File;
use Mail;
use Storage;
use Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Models\VehicleDivisions;
use App\Models\VehicleRegions;
use App\Models\UserLocation;
use App\Models\UserRegion;
use App\Models\UserDivision;

class UserService
{
    /**
     * Response file.
     *
     * @var string
     */
    protected $importNewUsersResponseFileName;

    /**
     * Response file.
     *
     * @var string
     */
    protected $importAllUsersResponseFileName;

    /**
     * Response file.
     *
     * @var string
     */
    protected $newDivisionsFileName;

    /**
     * Response file.
     *
     * @var string
     */
    protected $newRegionsFileName;

    /**
     * Response file.
     *
     * @var string
     */
    protected $newLocationsFileName;

	/**
     * Create a new user service instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->importNewUsersResponseFileName = 'Fleetmastr - New Drivers List.csv';
        $this->importAllUsersResponseFileName = 'Fleetmastr - All Drivers List.csv';
        $this->newDivisionsFileName = "Fleetmastr - New Divisions.csv";
        $this->newRegionsFileName = "Fleetmastr - New Regions.csv";
        $this->newLocationsFileName = "Fleetmastr - New Locations.csv";
    }

    /**
     * Process users.
     *
     * @return mixed
     */
    public function processUsers($usersFile)
    {
        $brandName = env('BRAND_NAME');
        $brandConfigExist = false;
        $brandConfigFile = config_path().'/'.env('BRAND_NAME').'/config-variables.php';
        if(\File::exists($brandConfigFile)){
            $brandConfigExist = true;
        }

        $allUsersStatus = [];
        $newAddedUsers = [];

        if($brandName === 'rps') {
            $newAddedUsers[] = [
                'First Name', 'Last Name', 'Email', 'Username', 'Profit_Centre',
            ];
        } else {
            $newAddedUsers[] = [
                'First Name', 'Last Name', 'ID', 'Email', 'Username', 'Region',
            ];
        }
        $divisionsNotFound[] = ['Name'];
        $regionsNotFound[] = ['Name'];
        $locationsNotFound[] = ['Name'];
        $companyNotFound = false;

    	$allUsersEngineerIds = User::withTrashed()->orWhere('is_disabled',1)->get()->keyBy('engineer_id');
        $allUsersEmails = User::withTrashed()->orWhere('is_disabled',1)
                                            ->get()
                                            ->keyBy(function ($item) {
                                                return strtolower($item['email']);
                                            });
        $allProcessedUserEngineerIds = [];
        $allProcessedUserEmails = [];
    	$row = 1;
        $createdUpdatedById = 1;
        $isDataAvailableForSync = false;
        $createdUpdatedByUser = User::where('email', env('CREATED_UPDATED_BY_FOR_IMPORT_DRIVER'))->first();
        if($createdUpdatedByUser) {
            $createdUpdatedById = $createdUpdatedByUser->id;
        }

        if (file_exists($usersFile) && ($handle = fopen($usersFile, 'r')) !== FALSE) {
            while (($data = fgetcsv ($handle, 5000, ',')) !== FALSE) {
            	$data = array_map('trim', $data);
            	if($row === 1) {
                    $data[0] = "First Name";
                    if($brandName === 'rps') {
                        $data[7] = "Status";
                        $data[8] = "Comment";
                        array_splice($data, 3, 0, "Username");
                    } else {
                        $data[13] = "Status";
                        $data[14] = "Comment";
                        array_splice($data, 4, 0, "Username");
                    }
                    $allUsersStatus[] = $data;
            		$row++;
                    continue;
                }

                if(env('BRAND_NAME') === 'mgroupservices') {
                    unset($data[13]);
                    unset($data[14]);
                    if($data[8] == '') {
                        $data[13] = "Ignored";
                        $data[14] = "Row ignored due to blank division/region";
                        $allProcessedUserEngineerIds[] = trim($data[6]);
                        array_splice($data, 4, 0, "");
                        $allUsersStatus[] = $data;
                        $row++;
                        continue;
                    }
                }

                $isDataAvailableForSync = true;
            	$isUserExist = false;
            	$isArchievedUser = false;
                $userEngineerId = null;
                $userEmail = null;

                if($brandName === 'rps') {
                    $userEmail = strtolower(trim($data[2]));

                    if(isset($allUsersEmails[$userEmail])) {
                        $isUserExist = true;
                    } else {
                        $userEmail = $this->setUserName($data);
                        if(isset($allUsersEmails[$userEmail])) {
                            $isUserExist = true;
                        }
                    }

                    if($isUserExist) {
                        $status = $allUsersEmails[$userEmail]->is_disabled;
                        if($status == '1') {
                            $isArchievedUser = true;
                        }
                    }
                } else {
                    $userEngineerId = trim($data[6]);

                    if(isset($allUsersEngineerIds[$userEngineerId])) {
                        $isUserExist = true;
                    }

                    if($isUserExist) {
                        $status = $allUsersEngineerIds[$userEngineerId]->is_disabled;
                        if($status == '1') {
                            $isArchievedUser = true;
                        }
                    }
                }

            	$user = null;
		    	if($isUserExist === false) {
                    $user = new User();
		    	} else {
                    if($brandName === 'rps') {
                        $user = $allUsersEmails[$userEmail];
                    } else {
                        $user = $allUsersEngineerIds[$userEngineerId];
                    }
		    	}
                $userObj = $this->addUpdateUserDetails($user, $data, $isUserExist, $isArchievedUser, $createdUpdatedById, $newAddedUsers, $allUsersStatus, $brandConfigExist, $divisionsNotFound, $regionsNotFound, $locationsNotFound, $companyNotFound);

                if($brandName === 'rps') {
                    $allUsersEmails[$userEmail] = $userObj;
                    array_push($allProcessedUserEmails, $userEmail);
                } else {
                    $allUsersEngineerIds[$userEngineerId] = $userObj;
                    array_push($allProcessedUserEngineerIds, $userEngineerId);
                }
                $row++;
            }
            fclose($handle);

            if($isDataAvailableForSync) {
                $allUsersEngineerIds = array_keys($allUsersEngineerIds->toArray());
                $allUsersEmails = array_keys($allUsersEmails->toArray());
                $notUpdatedUsers = [];

                if($brandName === 'rps') {
                    $notUpdatedUsers = array_diff($allUsersEmails, $allProcessedUserEmails);
                } else {
                    $notUpdatedUsers = array_diff($allUsersEngineerIds, $allProcessedUserEngineerIds);
                }

                if(count($notUpdatedUsers) > 0) {
                    $updateUsersQuery = User::where('job_title','Driver');
                    if($brandName === 'rps') {
                        $updateUsersQuery->whereIn('email', $notUpdatedUsers);
                    } else {
                        $updateUsersQuery->whereNotNull('engineer_id')
                                        ->where('engineer_id', '<>', '')
                                        ->whereIn('engineer_id', $notUpdatedUsers);
                    }
                    $updateUsersQuery->update([
                        'is_disabled' => 1
                    ]);
                }

                $newUsersResponseFileName = md5(time() . mt_rand(1,1000000)) . '.csv';
                $newUsersResponseFilePath = storage_path('importresponsefiles') . '/' . $newUsersResponseFileName;
                $allUsersResponseFileName = md5(time() . mt_rand(1,1000000)) . '.csv';
                $allUsersResponseFilePath = storage_path('importresponsefiles') . '/' . $allUsersResponseFileName;

                $this->prepareCSVFile($allUsersResponseFilePath, $allUsersStatus);
                if(count($newAddedUsers) > 1) {
                    $this->prepareCSVFile($newUsersResponseFilePath, $newAddedUsers);
                }

                $importUserResponseEmailToAdmin = explode(",", env('IMPORT_USER_RESPONSE_EMAIL_TO_ADMIN'));
                $importUserResponseEmailToDev = (strpos(env('IMPORT_USER_RESPONSE_EMAIL_TO_DEV'), ',') !== false) ? explode(",", env('IMPORT_USER_RESPONSE_EMAIL_TO_DEV')) : env('IMPORT_USER_RESPONSE_EMAIL_TO_DEV');
                $importUserResponseEmailToInternalAdmin = null;
                if(env('IMPORT_USER_RESPONSE_EMAIL_TO_INTERNAL_ADMIN')) {
                    $importUserResponseEmailToInternalAdmin = explode(",", env('IMPORT_USER_RESPONSE_EMAIL_TO_INTERNAL_ADMIN'));
                }

                //Upload file to s3
                $s3AllUsersResponseFilePath = $this->uploadFileOnS3($allUsersResponseFileName, $allUsersResponseFilePath);

                $s3NewUsersResponseFilePath = "";
                if(count($newAddedUsers) > 1) {
                    $s3NewUsersResponseFilePath = $this->uploadFileOnS3($newUsersResponseFileName, $newUsersResponseFilePath);
                }

                $importNewUsersResponseFileName = $this->importNewUsersResponseFileName;
                $importAllUsersResponseFileName = $this->importAllUsersResponseFileName;

                Mail::send('emails.user_import_response_email', [], function ($message) use($importUserResponseEmailToAdmin, $importUserResponseEmailToInternalAdmin, $importUserResponseEmailToDev, $importNewUsersResponseFileName, $s3NewUsersResponseFilePath, $importAllUsersResponseFileName, $s3AllUsersResponseFilePath, $newAddedUsers) {
                    $message->to($importUserResponseEmailToAdmin);
                    if($importUserResponseEmailToInternalAdmin) {
                        $message->bcc($importUserResponseEmailToInternalAdmin);
                    }
                    $message->subject('fleetmastr - driver import status');
                    $message->attach($s3AllUsersResponseFilePath, ['as' => $importAllUsersResponseFileName]);
                    if(count($newAddedUsers) > 1) {
                        $message->attach($s3NewUsersResponseFilePath, ['as' => $importNewUsersResponseFileName]);
                    }
                });

                if(count($divisionsNotFound) > 1 || count($regionsNotFound) > 1 || count($locationsNotFound) > 1 || $companyNotFound) {
                    $newDivisionsFileName = $this->newDivisionsFileName;
                    $newRegionsFileName = $this->newRegionsFileName;
                    $newLocationsFileName = $this->newLocationsFileName;

                    $newDivisionsResponseFileName = md5(time() . mt_rand(1,1000000)) . '.csv';
                    $newDivisionsResponseFilePath = storage_path('importresponsefiles') . '/' . $newDivisionsResponseFileName;
                    $newRegionsResponseFileName = md5(time() . mt_rand(1,1000000)) . '.csv';
                    $newRegionsResponseFilePath = storage_path('importresponsefiles') . '/' . $newRegionsResponseFileName;
                    $newLocationsResponseFileName = md5(time() . mt_rand(1,1000000)) . '.csv';
                    $newLocationsResponseFilePath = storage_path('importresponsefiles') . '/' . $newLocationsResponseFileName;

                    $s3NewDivisionsResponseFilePath = "";
                    $s3NewRegionsResponseFilePath = "";
                    $s3NewLocationsResponseFilePath = "";

                    if(count($divisionsNotFound) > 1) {
                        $this->prepareCSVFile($newDivisionsResponseFilePath, $divisionsNotFound);
                        $s3NewDivisionsResponseFilePath = $this->uploadFileOnS3($newDivisionsResponseFileName, $newDivisionsResponseFilePath);
                    }
                    if(count($regionsNotFound) > 1) {
                        $this->prepareCSVFile($newRegionsResponseFilePath, $regionsNotFound);
                        $s3NewRegionsResponseFilePath = $this->uploadFileOnS3($newRegionsResponseFileName, $newRegionsResponseFilePath);
                    }
                    if(count($locationsNotFound) > 1) {
                        $this->prepareCSVFile($newLocationsResponseFilePath, $locationsNotFound);
                        $s3NewLocationsResponseFilePath = $this->uploadFileOnS3($newLocationsResponseFileName, $newLocationsResponseFilePath);
                    }

                    Mail::send('emails.user_import_error_email', [], function ($message) use($importUserResponseEmailToDev, $newDivisionsFileName, $s3NewDivisionsResponseFilePath, $newRegionsFileName, $s3NewRegionsResponseFilePath, $newLocationsFileName, $s3NewLocationsResponseFilePath, $divisionsNotFound, $regionsNotFound, $locationsNotFound, $companyNotFound) {
                        $message->to($importUserResponseEmailToDev);
                        $message->subject('fleetmastr - ' . env('BRAND_NAME') . ' driver import UNSUCCESSFUL');
                        if(count($divisionsNotFound) > 1) {
                            $message->attach($s3NewDivisionsResponseFilePath, ['as' => $newDivisionsFileName]);
                        }
                        if(count($regionsNotFound) > 1) {
                            $message->attach($s3NewRegionsResponseFilePath, ['as' => $newRegionsFileName]);
                        }
                        if(count($locationsNotFound) > 1) {
                            $message->attach($s3NewLocationsResponseFilePath, ['as' => $newLocationsFileName]);
                        }
                    });
                }
            }
        }

        if (file_exists($usersFile)) {
            File::delete($usersFile);
        }

        if($isDataAvailableForSync) {
            File::delete($allUsersResponseFilePath);
            if(count($newAddedUsers) > 1) {
                File::delete($newUsersResponseFilePath);
            }
            if(count($divisionsNotFound) > 1) {
                File::delete($newDivisionsResponseFilePath);
            }
            if(count($regionsNotFound) > 1) {
                File::delete($newRegionsResponseFilePath);
            }
            if(count($locationsNotFound) > 1) {
                File::delete($newLocationsResponseFilePath);
            }
        }
    }

    /**
     * Add update user details.
     *
     * @return mixed
     */
    public function addUpdateUserDetails($user, $data, $isUserExist, $isArchievedUser, $createdUpdatedById, &$newAddedUsers, &$allUsersStatus, $brandConfigExist, &$divisionsNotFound, &$regionsNotFound, &$locationsNotFound, &$companyNotFound)
    {
        $brandName = env('BRAND_NAME');
        $sendVerification = $sendVerificationConfig = config('config-variables.send_verification_email_while_user_import');
        $userEmail = null;
        if($brandName == 'rps') {
            $company = Company::where('name', 'RPS')->first();
        } else {
            $company = Company::where('name', $data['2'])->first();
        }

        if(!empty($company)) {
            $user->first_name = $data[0];
            $user->last_name = $data[1];
            $user->company_id = $company->id;
            if ($isArchievedUser) {
                $user->is_disabled = 0;
            }

            if($brandName == 'rps') {
                $userEmail = $data[2];
            } else {
                $userEmail = $data[3];
            }

            if ($userEmail != '' && filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
                if (!$isUserExist) {
                    $user->email = $userEmail;
                    $user->username = $userEmail;
                    $user->is_verified = 0;
                    $user->is_active = 0;
                    $user->is_default_password = 0;
                    $user->is_disabled = 0;
                    $sendVerification = $sendVerificationConfig ? true : false;
                } else {
                    if ($user->username == '' || $user->username == NULL) {
                        $user->username = $userEmail;
                    }
                    if ($user->email != $userEmail) {
                        $user->email = $userEmail;
                        $user->is_verified = 0;
                        $user->is_disabled = 0;
                        $sendVerification = $sendVerificationConfig ? true : false;
                    } else {
                        $user->is_disabled = 0;
                        $sendVerification = false;
                    }
                }
            } else {
                $isNewUser = false;
                $sendVerification = false;
                if (!$isUserExist || $user->username == '' || $user->username == NULL) {
                    $isNewUser = true;
                    //$userRows  = User::whereRaw("username REGEXP '^{$uname}(-[0-9]*)?$'")->get();
                    $uname = $this->setUserName($data);
                    $userRows = \DB::table('users')->whereRaw("username REGEXP '^{$uname}([0-9]*)?$'")->get();
                    $countUser = count($userRows);
                    $newUsername = ($countUser > 0) ? $uname . $countUser : $uname;
                    $user->username = $newUsername;
                    $user->email = $newUsername . '@' . env('BRAND_NAME') . '-imastr.com';
                }
                $user->is_verified = 1;
                $user->is_active = 1;
                $user->is_disabled = 0;
                if($isNewUser) {
                    $user->is_default_password = 1;
                    $user->password = bcrypt(env('DEFAULT_PASSWORD'));
                }
            }

            if($brandName === 'rps') {
                if(!$isUserExist) {
                    $user->job_title = "Driver";
                }
            } else {
                if( (isset($data[12]) && $data[12] == 0) || !$isUserExist) {
                    $user->job_title = $data[5];
                }
            }

            if($brandName !== 'rps') {
                $user->engineer_id = $data[6];
                if ($data[7] === 'Yes') {
                    $user->enable_login = 1;
                } else {
                    $user->enable_login = 0;
                }
            }

            $userDivision = null;
            $userRegion = null;
            $userLocation = null;

            if($brandName === 'rps') {
                $data[3] = ltrim($data[3], '0');
                if($data[3] != '') {
                    $userRegion = UserRegion::where('name', $data[3])->first();
                    if($userRegion) {
                        $userDivision = $userRegion->division;
                    }
                }
            } else {
                if($data[4] != '')
                {
                    $userDivision = UserDivision::where('name',$data[4])->select('id')->first();
                }
                if(env('IS_DIVISION_REGION_LINKED_IN_USER') && env('IS_REGION_LOCATION_LINKED_IN_USER'))
                {
                    if($data[8] != '' && $data[4]  != '' && $userDivision !== null)
                    {
                        $userRegion = UserRegion::where('name',$data[8])->where('user_division_id',$userDivision->id)->select('id')->first();
                    }
                    if($data[9] != '' && $data[8] != '' && $data[4]!='')
                    {
                        $userLocation = UserLocation::where('name',$data[9])->where('user_region_id',$userRegion->id)->select('id')->first();
                    }
                }
                else if(env('IS_DIVISION_REGION_LINKED_IN_USER'))
                {
                    if($data[8] != '' && $data[4]!='' && $userDivision !== null)
                    {
                        $userRegion = UserRegion::where('name',$data[8])->where('user_division_id',$userDivision->id)->select('id')->first();
                    }
                    if($data[9] != '')
                    {
                        $userLocation = UserLocation::where('name',$data[9])->select('id')->first();
                    }
                }
                else if(env('IS_REGION_LOCATION_LINKED_IN_USER'))
                {
                    $userRegion = UserRegion::where('name',$data[8])->select('id')->first();
                    if($data[9] != ''  && $data[8] != '')
                    {
                         $userLocation = UserLocation::where('name',$data[9])->where('user_region_id',$userRegion->id)->select('id')->first();
                    }
                }
                else
                {
                    if($data[8] != '')
                    {
                        $userRegion = UserRegion::where('name',$data[8])->select('id')->first();
                    }
                    if($data[9] != '')
                    {
                        $userLocation = UserLocation::where('name',$data[9])->select('id')->first();
                    }
                }
            }
            $user->user_region_id = $userRegion ? $userRegion->id : null;
            $user->user_division_id = $userDivision ? $userDivision->id : null;
            $user->user_locations_id = $userLocation ? $userLocation->id : null;
            // $user->accessible_regions = explode(',', $data[11]);

            // $allConfigs = config('config-variables');
            // if ($brandConfigExist) {
            //     $allConfigs = config(env('BRAND_NAME') . '.config-variables');
            // }

            if($brandName === 'rps') {
                $data[3] = ltrim($data[3], '0');
                $allUserRegions = UserRegion::all()->pluck('name')->toArray();
                if ($data[3] != '' && !in_array($data[3], $allUserRegions) && !in_array($data[3], array_column($regionsNotFound, 0))) {
                    $regionsNotFound[] = [$data[3]];
                }

            } else {
                $allUserDivisions = UserDivision::all()->pluck('name')->toArray();
                $allUserRegions = UserRegion::all()->groupBy('user_division_id')->toArray();
                $divisionRegions = isset($allUserRegions[$user->user_division_id]) ? $allUserRegions[$user->user_division_id] : [];
                $divisionRegions = count($divisionRegions) > 0 ? array_column($divisionRegions, 'name') : [];

                if ($data[4] != '' && !in_array($data[4], $allUserDivisions) && !in_array($data[4], array_column($divisionsNotFound, 0))) {
                    $divisionsNotFound[] = [$data[4]];
                }
                if ( $data[8] != '' && (!isset($allUserRegions[$user->user_division_id]) || !in_array($data[8], $divisionRegions)) && !in_array($data[8], array_column($regionsNotFound, 0)) ) {
                    $regionsNotFound[] = [$data[8]];
                }
            }

            if($brandName ==='rps') {
                $user->mobile = $data[4];
                $user->fuel_card_number = $data[5];
                if(isset($data[6]) && $data[6]) {
                    $user->driver_tag = 'rfid_card';
                    $user->driver_tag_key = $data[6];
                } else {
                    $user->driver_tag = 'none';
                    $user->driver_tag_key = '';
                }
            }

            $user->save();

            if($brandName ==='rps') {
                if($user->job_title == 'Driver') {
                    $roles = Role::where('name', 'App access')->first();
                    $user->roles()->sync([$roles->id]);
                }
            } else {
                if((isset($data[12]) && $data[12] == 0)) {
                    // $user->accessible_regions = explode(',', $data[11]);
                    $userAccessibleRegion = VehicleRegions::where('name',$data[11])->first();
                    if($userAccessibleRegion) {
                        $user->regions()->sync([$userAccessibleRegion->id]);
                        $user->divisions()->sync([$userAccessibleRegion->division->id]);
                    }
                }

                if (!$isUserExist && (isset($data[12]) && $data[12] == 0)) {
                    $originalDataRole = $data[10];
                    $data[10] = ($data[10] == 'App Access Only') ? 'App access' : $data[10];
                    $roles = Role::where('name', $data[10])->first();
                    $user->roles()->sync([$roles->id]);
                    $data[10] = $originalDataRole;
                }
            }

            \Log::info('checking for user_id: '.$user->id. ', first_name: '.$user->first_name.', sendVerification: '.$sendVerification);
            // TODO Need to reactivate this
            if ($sendVerification) {
                $token = str_random(30);
                $link = url('users/verification', [$token]);
                $userVerification = new UserVerification();
                $userVerification->user_id = $user->id;
                $userVerification->key = $token;
                $userVerification->save();

                $userName = $user->first_name;
                $emailAddress = $user->email;

                \Log::info('sending mail to '.$emailAddress);

                Mail::send('emails.user_set_password', ['userName' => $userName, 'emailAddress' => $emailAddress, 'link' => $link], function ($message) use ($userName, $emailAddress, $token) {
                    $message->to($emailAddress);
                    $message->subject('fleetmastr - set your account password');
                });
            }

            //'First Name', 'Last Name', 'ID', 'Email', 'Username', 'Region',
            if (!$isUserExist) {
                $username = '';
                if ($userEmail != '' && filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
                    $username = $user->username;
                } else {
                    $username = $user->username;
                }

                if($brandName === 'rps') {
                    $newAddedUsers[] = [
                        $data[0],
                        $data[1],
                        $data[2],
                        $username,
                        $data[3],
                    ];

                    $data[7] = "Added";
                    $data[8] = "New driver has been added";
                    array_splice($data, 3, 0, $user->username);
                } else {
                    $newAddedUsers[] = [
                        $data[0],
                        $data[1],
                        $data[6],
                        $data[3],
                        $username,
                        $data[8],
                    ];

                    $data[13] = "Added";
                    $data[14] = "New driver has been added";
                    array_splice($data, 4, 0, $user->username);
                }

                $allUsersStatus[] = $data;
            } else {
                if($brandName === 'rps') {
                    $data[7] = "Updated";
                    $data[8] = "Driver details have been updated";
                    array_splice($data, 3, 0, $user->username);
                } else {
                    $data[13] = "Updated";
                    $data[14] = "Driver details have been updated";
                    array_splice($data, 4, 0, $user->username);
                }
                $allUsersStatus[] = $data;
            }
        } else{
            $companyNotFound = true;
        }

        if($sendVerification)
            sleep(2);

        return $user;
    }

    /**
     * Set user name
     *
     * @return mixed
     */
    private function setUserName($data)
    {
        $uname = str_replace(' ', '', strtolower(preg_replace("/[^A-Za-z0-9 ]/", '', $data[0]) . '.' . preg_replace("/[^A-Za-z0-9 ]/", '', $data[1])));
        return $uname;
    }

    /**
     * Prepare CSV File.
     *
     * @return mixed
     */
    public function prepareCSVFile($responseFilePath, $responseData)
    {
        $responseFile = fopen($responseFilePath, "w");
        foreach ($responseData as $data) {
          fputcsv($responseFile, $data);
        }
        fclose($responseFile);
    }

    /**
     * Upload CSV File on S3.
     *
     * @return mixed
     */
    private function uploadFileOnS3($filename, $localpath)
    {
        \Log::info('filename: '.$filename);
        \Log::info('localpath: '.$localpath);
        $s3path = 'rps_import/users/'.$filename;
        $disk = Storage::disk('s3');
        $contents = File::get($localpath);
        $disk->put($s3path, $contents, 'public');
        $s3 = Storage::disk('s3')->getAdapter()->getClient();
        $s3Url = $s3->getObjectUrl(env('S3_UPLOADS_BUCKET'), $s3path);
        \Log::info('s3Url: '.$s3Url);
        return $s3Url;
    }

    public function getAllVehicleLinkedData($authUserRegion = false) {
        $vehicleDivisions = [];
        $vehicleRegion = [];
        $vehicleBaseLocation = [];
        $vehicleOnlyRegions = [];
        if (env('IS_DIVISION_REGION_LINKED_IN_VEHICLE')) {
            if($authUserRegion) {

                $userRegions = Auth::user()->regions->lists('id')->toArray();
                $userDivisions = Auth::user()->divisions->lists('id')->toArray();

                $allDivisions = VehicleDivisions::with(['vehicleRegions' => function($query) use($userRegions) {
                    $query->whereIn('id', $userRegions);
                    $query->orderBy('name', 'ASC');
                }])->whereIn('id', $userDivisions)->orderBy('name','ASC')->get()->toArray();

            } else {
                $allDivisions = VehicleDivisions::with(['vehicleRegions' => function($query) {
                    $query->orderBy('name', 'ASC');
                }])->orderBy('name','ASC')->get()->toArray();
            }

            if(is_array($allDivisions) && !empty($allDivisions)) {
                foreach ($allDivisions as $divisions) {
                    // create all divisions lists
                    if(isset($divisions['name']) && $divisions['id']) {
                        $vehicleDivisions[$divisions['id']] = $divisions['name'];
                    }

                    if(isset($divisions['vehicle_regions']) && is_array($divisions['vehicle_regions']) && !empty($divisions['vehicle_regions'])) {
                        // create division wise regions lists
                        sort($divisions['vehicle_regions']);
                        foreach ($divisions['vehicle_regions'] as $regions) {
                            if(isset($regions['name']) && $regions['id']) {
                                $vehicleRegion[$divisions['id']][$regions['id']] = $regions['name'];
                                $vehicleOnlyRegions[$regions['id']] = $regions['name'];
                            }
                        }
                    }
                }
            }
        } else {
            if($authUserRegion) {
                $userRegions = Auth::user()->regions->lists('id')->toArray();
                $userDivisions = Auth::user()->divisions->lists('id')->toArray();
                $vehicleDivisions = VehicleDivisions::whereIn('id', $userDivisions)->orderBy('name','ASC')->lists('name', 'id')->toArray();
                $vehicleRegion = VehicleRegions::whereIn('id', $userRegions)->orderBy('name','ASC')->lists('name', 'id')->toArray();
            } else {
                $vehicleDivisions = VehicleDivisions::orderBy('name','ASC')->lists('name', 'id')->toArray();
                $vehicleRegion = VehicleRegions::orderBy('name','ASC')->lists('name', 'id')->toArray();
            }
            $vehicleOnlyRegions = $vehicleRegion;
        }

        asort($vehicleOnlyRegions);

        return [
            'vehicleDivisions' => $vehicleDivisions,
            'vehicleRegions' => $vehicleRegion,
            'vehicleOnlyRegions' => $vehicleOnlyRegions
        ];
    }

    public function getAllVehicleDashboardData() {
        $vehicleRegion = [];
        if (env('IS_DIVISION_REGION_LINKED_IN_VEHICLE')) {
            $allDivisions = VehicleDivisions::with(['vehicleRegions'=> function($query) {
                $query->whereIn('vehicle_regions.id', \Auth::user()->regions->lists('id')->toArray());
                $query->orderBy('name','ASC');
            }])->whereIn('vehicle_divisions.id', \Auth::user()->divisions->lists('id')->toArray())->orderBy('name','ASC')->get()->toArray();
            if(is_array($allDivisions) && !empty($allDivisions)) {
                foreach ($allDivisions as $divisions) {
                    // create all divisions lists
                    $divisionName = '';
                    if(isset($divisions['name']) && $divisions['id']) {
                        $divisionName = $divisions['name'];
                    }

                    if(isset($divisions['vehicle_regions']) && is_array($divisions['vehicle_regions']) && !empty($divisions['vehicle_regions'])) {
                        // create division wise regions lists
                        foreach ($divisions['vehicle_regions'] as $regions) {
                            if(isset($regions['name']) && $regions['id']) {
                                $vehicleRegion[$regions['id']] = $regions['name'] . ' ('.$divisionName.')';
                            }
                        }
                    }
                }
            }
            asort($vehicleRegion);
        } else {
            $vehicleRegion = VehicleRegions::whereIn('vehicle_regions.id', \Auth::user()->regions->lists('id')->toArray())->orderBy('name','ASC')->lists('name', 'id')->toArray();
        }
        if(!empty($vehicleRegion)) {
            $vehicleRegion = ['' => ''] + $vehicleRegion;
        }

        return $vehicleRegion;
    }

    public function getAllMessageLinkedData($authUserRegion = false) {
        $messageDivisions = [];
        $messageRegion = [];
        $vehicleBaseLocation = [];
        if (env('IS_DIVISION_REGION_LINKED_IN_VEHICLE')) {
            if($authUserRegion) {

                $userRegions = Auth::user()->messageRegions->lists('id')->toArray();
                $userDivisions = Auth::user()->messageDivisions->lists('id')->toArray();

                $allDivisions = VehicleDivisions::with(['vehicleRegions' => function($query) use($userRegions) {
                    $query->whereIn('id', $userRegions);
                    $query->orderBy('name', 'ASC');
                }])->whereIn('id', $userDivisions)->orderBy('name','ASC')->get()->toArray();

            } else {
                $allDivisions = VehicleDivisions::with(['vehicleRegions' => function($query) {
                    $query->orderBy('name', 'ASC');
                }])->orderBy('name','ASC')->get()->toArray();
            }

            if(is_array($allDivisions) && !empty($allDivisions)) {
                foreach ($allDivisions as $divisions) {
                    // create all divisions lists
                    if(isset($divisions['name']) && $divisions['id']) {
                        $messageDivisions[$divisions['id']] = $divisions['name'];
                    }

                    if(isset($divisions['vehicle_regions']) && is_array($divisions['vehicle_regions']) && !empty($divisions['vehicle_regions'])) {
                        // create division wise regions lists
                        sort($divisions['vehicle_regions']);
                        foreach ($divisions['vehicle_regions'] as $regions) {
                            if(isset($regions['name']) && $regions['id']) {
                                $messageRegion[$divisions['id']][$regions['id']] = $regions['name'];
                            }
                        }
                    }
                }
            }
        } else {
            if($authUserRegion) {
                $userRegions = Auth::user()->messageRegions->lists('id')->toArray();
                $userDivisions = Auth::user()->messageDivisions->lists('id')->toArray();
                $messageDivisions = UserDivision::whereIn('id', $userDivisions)->orderBy('name','ASC')->lists('name', 'id')->toArray();
                $messageRegion = UserRegion::whereIn('id', $userRegions)->orderBy('name','ASC')->lists('name', 'id')->toArray();
            } else {
                $messageDivisions = UserDivision::orderBy('name','ASC')->lists('name', 'id')->toArray();
                $messageRegion = UserRegion::orderBy('name','ASC')->lists('name', 'id')->toArray();
            }
        }

        return [
            'messageDivisions' => $messageDivisions,
            'messageRegions' => $messageRegion
        ];
    }
}
