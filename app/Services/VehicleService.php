<?php
namespace App\Services;

use File;
use Auth;
use Mail;
use Storage;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Defect;
use App\Models\Vehicle;
use App\Models\Settings;
use App\Models\VehicleType;
use App\Models\VehicleLocations;
use App\Models\VehicleDivisions;
use App\Models\VehicleRegions;
use App\Models\MaintenanceEvents;
use Illuminate\Support\Facades\DB;
use App\Models\VehicleMaintenanceNotification;
use App\Models\VehicleUsageHistory;
use App\Models\VehicleAssignment;
use App\Models\VehicleRepairLocations;

class VehicleService
{
    /**
     * Response file.
     *
     * @var string
     */
    protected $importNewVehiclesResponseFileName;

    /**
     * Response file.
     *
     * @var string
     */
    protected $importArchivedVehiclesResponseFileName;

    /**
     * Response file.
     *
     * @var string
     */
    protected $importAllVehiclesResponseFileName;

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
     * Response file.
     *
     * @var string
     */
    protected $importVehicleProfileNotFoundResponseFileName;

    /**
     * Create a new vehicle service instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->importNewVehiclesResponseFileName = "Fleetmastr - New Vehicles List.csv";
        $this->importArchivedVehiclesResponseFileName = "Fleetmastr - Archived Vehicles List.csv";
        $this->importAllVehiclesResponseFileName = "Fleetmastr - All Vehicles List.csv";
        $this->newDivisionsFileName = "Fleetmastr - New Divisions.csv";
        $this->newRegionsFileName = "Fleetmastr - New Regions.csv";
        $this->newLocationsFileName = "Fleetmastr - New Locations.csv";
        $this->importVehicleProfileNotFoundResponseFileName = "Fleetmastr - Vehicles with unidentified profiles.csv";
    }

    /**
     * Process vehicles.
     *
     * @return mixed
     */
    public function processVehicles($vehiclesFile)
    {
        $brandName = env('BRAND_NAME');
        $brandConfigExist = false;
        $brandConfigFile = config_path().'/'.env('BRAND_NAME').'/config-variables.php';
        if(\File::exists($brandConfigFile)){
            $brandConfigExist = true;
        }

        $allVehiclesStatus = [];
        $newAddedVehicles = [];
        $archivedVehicles = [];
        $vehicleProfileNotFound = [];
        if($brandName === 'rps') {
            $newAddedVehicles[] = [
                'Vehicle_Registration', 'VehicleType', 'Manufacturer', 'Profit_Centre'
            ];
        } else {
            $newAddedVehicles[] = [
                'Registration Number', 'Type', 'Vehicle Status', 'Division', 'Region', 'Location'
            ];
        }
        $vehicleSubCategoriesNonHGV = config('config-variables.vehicleSubCategoriesNonHGV');
        $archivedVehicles[] = [
                'Registration', 'Region', 'Category', 'Sub Category', 'Type', 'Manufacturer', 'Model', 'Vehicle Status', 'Telematics'
            ];
        $divisionsNotFound = [];
        $divisionsNotFound[] = ['Name'];
        $regionsNotFound = [];
        $regionsNotFound[] = ['Name'];
        $locationsNotFound = [];
        $locationsNotFound[] = ['Name'];

        $allVehicleRegistrationNumbers = Vehicle::withTrashed()->get()->keyBy('registration');
        $allProcessedVehicleRegistrationNumbers = [];
        $processedVehicleRegistrationNumbers = [];
        $row = 1;
        $createdUpdatedById = 1;
        $isDataAvailableForSync = false;
        $createdUpdatedByUser = User::where('email', env('CREATED_UPDATED_BY_FOR_IMPORT_VEHICLE'))->first();
        if($createdUpdatedByUser) {
            $createdUpdatedById = $createdUpdatedByUser->id;
        }

        if (file_exists($vehiclesFile) && ($handle = fopen($vehiclesFile, 'r')) !== FALSE) {
            while (($data = fgetcsv ($handle, 1000, ',')) !== FALSE) {
                $data = array_map('trim', $data);
                if($row === 1) {
                    if($brandName === 'rps') {
                        $data[11] = "Status";
                        $data[12] = "Comment";
                    } else {
                        $data[0] = "Registration Number";
                        $data[16] = "Status";
                        $data[17] = "Comment";
                        if(env('BRAND_NAME') === 'mgroupservices') {
                            unset($data[18]);
                        }
                    }
                    $allVehiclesStatus[] = $data;
                    $vehicleProfileNotFound[] = $data;
                    $row++;
                    continue;
                }
                $isDataAvailableForSync = true;
                $isVehicleExist = false;
                $isArchievedVehicle = false;
                if($brandName === 'rps') {
                    $vehicleRegistrationNumber = $data[3];
                } else {
                    $vehicleRegistrationNumber = $data[0];
                }

                if(env('BRAND_NAME') === 'mgroupservices') {
                    unset($data[17]);
                    unset($data[18]);
                    if($data[7] == '') {
                        $data[16] = "Ignored";
                        $data[17] = "Row ignored due to blank division/region";
                        array_push($allProcessedVehicleRegistrationNumbers, $vehicleRegistrationNumber);
                        $allVehiclesStatus[] = $data;
                        $row++;
                        continue;
                    }
                }

                if(isset($allVehicleRegistrationNumbers[$vehicleRegistrationNumber]) && $allVehicleRegistrationNumbers[$vehicleRegistrationNumber]) {
                    $isVehicleExist = true;
                }

                if($isVehicleExist) {
                    $status = $allVehicleRegistrationNumbers[$vehicleRegistrationNumber]->status;
                    if($status === 'Archived' || $status === 'Archived - De-commissioned' || $status === 'Archived - Written off') {
                        $isArchievedVehicle = true;
                    }
                }

                $vehicle = null;
                if($isVehicleExist === false) {
                    $vehicle = new Vehicle();
                } else {
                    $vehicle = $allVehicleRegistrationNumbers[$vehicleRegistrationNumber];
                }
                $vehicleObj = $this->addUpdateVehicleDetails($vehicle, $data, $isVehicleExist, $isArchievedVehicle, $createdUpdatedById, $newAddedVehicles, $allVehiclesStatus, $brandConfigExist, $divisionsNotFound, $regionsNotFound, $locationsNotFound, $vehicleProfileNotFound);
                if($vehicleObj) {
                    $allVehicleRegistrationNumbers[$vehicleRegistrationNumber] = $vehicleObj;
                }
                array_push($allProcessedVehicleRegistrationNumbers, $vehicleRegistrationNumber);
                $row++;
            }
            fclose($handle);

            if($isDataAvailableForSync) {
                $allVehicleRegistrationNumbers = array_keys($allVehicleRegistrationNumbers->toArray());
                $notUpdatedVehicles = array_diff($allVehicleRegistrationNumbers, $allProcessedVehicleRegistrationNumbers);

                if(count($notUpdatedVehicles) > 0) {
                    $archivedVehiclesData = Vehicle::whereIn('registration', $notUpdatedVehicles)->get();
                    Vehicle::whereIn('registration', $notUpdatedVehicles)->update([
                        'status' => 'Archived'
                    ]);
                    Vehicle::whereIn('registration', $notUpdatedVehicles)->delete();

                    if(in_array($brandName, ['rps', 'skanska', 'mgroupservices'])) {
                        $archivedRow = 1;
                        foreach($archivedVehiclesData as $archivedVehicle) {
                            $archivedVehicleType = $archivedVehicle->type;
                            $archivedVehicles[$archivedRow][] = $archivedVehicle->registration;
                            $archivedVehicles[$archivedRow][] = $archivedVehicle->region ? $archivedVehicle->region->name : '';
                            $archivedVehicles[$archivedRow][] = $archivedVehicleType->vehicle_category = "non-hgv" ? "Non-HGV" : "HGV";
                            $archivedVehicles[$archivedRow][] = $archivedVehicleType->vehicle_category = "non-hgv" ? $vehicleSubCategoriesNonHGV[$archivedVehicleType->vehicle_subcategory] : 'None';
                            $archivedVehicles[$archivedRow][] = $archivedVehicleType->vehicle_type;
                            $archivedVehicles[$archivedRow][] = $archivedVehicleType->manufacturer;
                            $archivedVehicles[$archivedRow][] = $archivedVehicleType->model;
                            $archivedVehicles[$archivedRow][] = $archivedVehicle->status;
                            $archivedVehicles[$archivedRow][] = $archivedVehicle->is_telematics_enabled == 1 ? 'Yes' : 'No';
                            $archivedRow++;
                        }
                    }
                }

                $archivedVehiclesResponseFileName = md5(time() . mt_rand(1,1000000)) . '.csv';
                $archivedVehiclesResponseFilePath = storage_path('importresponsefiles') . '/' . $archivedVehiclesResponseFileName;
                $newVehiclesResponseFileName = md5(time() . mt_rand(1,1000000)) . '.csv';
                $newVehiclesResponseFilePath = storage_path('importresponsefiles') . '/' . $newVehiclesResponseFileName;
                $allVehiclesResponseFileName = md5(time() . mt_rand(1,1000000)) . '.csv';
                $allVehiclesResponseFilePath = storage_path('importresponsefiles') . '/' . $allVehiclesResponseFileName;
                $vehicleProfileNotFoundResponseFileName = md5(time() . mt_rand(1,1000000)) . '.csv';
                $vehicleProfileNotFoundResponseFilePath = storage_path('importresponsefiles') . '/' . $vehicleProfileNotFoundResponseFileName;

                $this->prepareCSVFile($allVehiclesResponseFilePath, $allVehiclesStatus);
                if(count($newAddedVehicles) > 1) {
                    $this->prepareCSVFile($newVehiclesResponseFilePath, $newAddedVehicles);
                }
                if(count($archivedVehicles) > 1) {
                    $this->prepareCSVFile($archivedVehiclesResponseFilePath, $archivedVehicles);
                }
                if(count($vehicleProfileNotFound) > 1 && in_array($brandName, ['rps', 'skanska', 'mgroupservices'])) {
                    $this->prepareCSVFile($vehicleProfileNotFoundResponseFilePath, $vehicleProfileNotFound);
                }

                $importVehicleResponseEmailToAdmin = explode(",", env('IMPORT_VEHICLE_RESPONSE_EMAIL_TO_ADMIN'));
                $importVehicleResponseEmailToDev = (strpos(env('IMPORT_VEHICLE_RESPONSE_EMAIL_TO_DEV'), ',') !== false) ? explode(",", env('IMPORT_VEHICLE_RESPONSE_EMAIL_TO_DEV')) : env('IMPORT_VEHICLE_RESPONSE_EMAIL_TO_DEV');
                $importVehicleResponseEmailToInternalAdmin = null;
                if(env('IMPORT_VEHICLE_RESPONSE_EMAIL_TO_INTERNAL_ADMIN')) {
                    $importVehicleResponseEmailToInternalAdmin = explode(",", env('IMPORT_VEHICLE_RESPONSE_EMAIL_TO_INTERNAL_ADMIN'));
                }

                //Upload file to s3
                $s3AllVehiclesResponseFilePath = $this->uploadFileOnS3($allVehiclesResponseFileName, $allVehiclesResponseFilePath);

                $s3NewVehiclesResponseFilePath = "";
                if(count($newAddedVehicles) > 1) {
                    $s3NewVehiclesResponseFilePath = $this->uploadFileOnS3($newVehiclesResponseFileName, $newVehiclesResponseFilePath);
                }

                $s3ArchivedVehiclesResponseFilePath = "";
                if(count($archivedVehicles) > 1) {
                    $s3ArchivedVehiclesResponseFilePath = $this->uploadFileOnS3($archivedVehiclesResponseFileName, $archivedVehiclesResponseFilePath);
                }

                $s3VehicleProfileNotFoundResponseFilePath = "";
                if(count($vehicleProfileNotFound) > 1 && in_array($brandName, ['rps', 'skanska', 'mgroupservices'])) {
                    $s3VehicleProfileNotFoundResponseFilePath = $this->uploadFileOnS3($vehicleProfileNotFoundResponseFileName, $vehicleProfileNotFoundResponseFilePath);
                }

                $importNewVehiclesResponseFileName = $this->importNewVehiclesResponseFileName;
                $importArchivedVehiclesResponseFileName = $this->importArchivedVehiclesResponseFileName;
                $importAllVehiclesResponseFileName = $this->importAllVehiclesResponseFileName;
                $importVehicleProfileNotFoundResponseFileName = $this->importVehicleProfileNotFoundResponseFileName;

                Mail::send('emails.vehicle_import_response_email', [], function ($message) use($importVehicleResponseEmailToAdmin, $importVehicleResponseEmailToInternalAdmin, $importNewVehiclesResponseFileName, $s3NewVehiclesResponseFilePath, $importArchivedVehiclesResponseFileName, $s3ArchivedVehiclesResponseFilePath, $importAllVehiclesResponseFileName, $s3AllVehiclesResponseFilePath, $importVehicleProfileNotFoundResponseFileName, $s3VehicleProfileNotFoundResponseFilePath, $newAddedVehicles, $archivedVehicles, $vehicleProfileNotFound, $brandName) {
                    $message->to($importVehicleResponseEmailToAdmin);
                    if($importVehicleResponseEmailToInternalAdmin) {
                        $message->bcc($importVehicleResponseEmailToInternalAdmin);
                    }
                    $message->subject('fleetmastr - vehicle import status');
                    $message->attach($s3AllVehiclesResponseFilePath, ['as' => $importAllVehiclesResponseFileName]);
                    if(count($newAddedVehicles) > 1) {
                        $message->attach($s3NewVehiclesResponseFilePath, ['as' => $importNewVehiclesResponseFileName]);
                    }
                    if(count($archivedVehicles) > 1) {
                        $message->attach($s3ArchivedVehiclesResponseFilePath, ['as' => $importArchivedVehiclesResponseFileName]);
                    }
                    if(count($vehicleProfileNotFound) > 1 && in_array($brandName, ['rps', 'skanska', 'mgroupservices'])) {
                        $message->attach($s3VehicleProfileNotFoundResponseFilePath, ['as' => $importVehicleProfileNotFoundResponseFileName]);
                    }
                });

                if(count($divisionsNotFound) > 1 || count($regionsNotFound) > 1 || count($locationsNotFound) > 1) {
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

                    Mail::send('emails.vehicle_import_error_email', [], function ($message) use($importVehicleResponseEmailToDev, $newDivisionsFileName, $s3NewDivisionsResponseFilePath, $newRegionsFileName, $s3NewRegionsResponseFilePath, $newLocationsFileName, $s3NewLocationsResponseFilePath, $divisionsNotFound, $regionsNotFound, $locationsNotFound) {
                        $message->to($importVehicleResponseEmailToDev);
                        $message->subject('fleetmastr - ' . env('BRAND_NAME') . ' vehicle import UNSUCCESSFUL');
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

        if (file_exists($vehiclesFile)) {
            File::delete($vehiclesFile);
        }
        if($isDataAvailableForSync) {
            File::delete($allVehiclesResponseFilePath);
            if(count($newAddedVehicles) > 1) {
                File::delete($newVehiclesResponseFilePath);
            }
            if(count($archivedVehicles) > 1) {
                File::delete($archivedVehiclesResponseFilePath);
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
     * Add update vehicle details.
     *
     * @return mixed
     */
    public function addUpdateVehicleDetails($vehicle, $data, $isVehicleExist, $isArchievedVehicle, $createdUpdatedById, &$newAddedVehicles, &$allVehiclesStatus, $brandConfigExist, &$divisionsNotFound, &$regionsNotFound, &$locationsNotFound, &$vehicleProfileNotFound)
    {
        $brandName = env('BRAND_NAME');
        $nominatedDriverId = null;
        $vehicleType = null;
        $nominatedDriver = null;
        $oldNominatedDriverId = $isVehicleExist ? $vehicle->nominated_driver : null;
        $oldVehicleRegionId = $isVehicleExist ? $vehicle->vehicle_region_id : null;
        $oldVehicleDivisionId = $isVehicleExist ? $vehicle->vehicle_division_id : null;
        $oldVehicleLocationId = $isVehicleExist ? $vehicle->vehicle_location_id : null;

        if($brandName === 'rps') {
            if($data[4] == "FOC") {
                return null;
            }
            $vehicleType = VehicleType::where('vehicle_type', $data[4] . ' ' . $data[5])->first();
            $data[0] = ltrim($data[0], '0');
        } else {
            $vehicleType = VehicleType::where('vehicle_type', $data[1])->first();
        }

        if($brandName === 'rps') {
            /**#3642 comments - ignore "Email"($data[9]) column for time being
             * Enabling again for #6236
             */
            if(trim($data[9])) {
                $nominatedDriver = User::where('email', $data[9])->first();
            }
        } else {
            if($data[4] && $data[4] != 0) {
                $nominatedDriver = User::where('engineer_id', $data[4])->first();
            }
        }
        if($nominatedDriver) {
            $nominatedDriverId = $nominatedDriver->id;
        }

        $vehicleRegion = null;
        $vehicleLocation = null;
        $vehicleDivision = null;

        if($brandName === 'rps') {
            if($data[0] != '') {
                $vehicleRegion = VehicleRegions::with('division')->where('name', $data[0])->first();
                if($vehicleRegion) {
                    $vehicleDivision = $vehicleRegion->division;
                }
            }
        } else {
            if($data[6] != '') {
                $vehicleDivision = VehicleDivisions::where('name',$data[6])->select('id')->first();
            }
            if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE') && env('IS_REGION_LOCATION_LINKED_IN_VEHICLE'))
            {
                if(env('BRAND_NAME') === 'mgroupservices') {
                    $vehicleDivision = null;
                    if($data[7] != '') {
                        $vehicleRegion = VehicleRegions::where('name', $data[7])->first();
                        if($vehicleRegion) {
                            $vehicleDivision = $vehicleRegion->division;
                        }
                    }
                } else {
                    $vehicleRegion = VehicleRegions::where('name',$data[7])->where('vehicle_division_id',$vehicleDivision->id)->select('id')->first();
                }

                if($data[8] != '')
                {
                    $vehicleLocation = VehicleLocations::where('name',$data[8])->where('vehicle_region_id',$vehicleRegion->id)->select('id')->first();
                    if(!$vehicleLocation) 
                    {
                        $vehicleLocation = $this->insertlocationData($data,$vehicleRegion,1);
                        $locationsNotFound[] = [$data[8]];
                    }
                }
            }
            else if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'))
            {
                $vehicleRegion = null;
                if(env('BRAND_NAME') === 'mgroupservices') {
                    $vehicleDivision = null;
                    if($data[7] != '') {
                        $vehicleRegion = VehicleRegions::where('name', $data[7])->first();
                        if($vehicleRegion) {
                            $vehicleDivision = $vehicleRegion->division;
                        }
                    }
                } else {
                    if($vehicleDivision) {
                        $vehicleRegion = VehicleRegions::where('name',$data[7])->where('vehicle_division_id',$vehicleDivision->id)->select('id')->first();
                    }
                }

                if($data[8] != '')
                {
                    $vehicleLocation = VehicleLocations::where('name',$data[8])->select('id')->first();
                    if(!$vehicleLocation) 
                    {
                        $vehicleLocation = $this->insertlocationData($data,$vehicleRegion,0);
                        $locationsNotFound[] = [$data[8]];
                    } 
                }
            }
            else if(env('IS_REGION_LOCATION_LINKED_IN_VEHICLE'))
            {
                $vehicleRegion = VehicleRegions::where('name',$data[7])->select('id')->first();
                if($data[8] != '')
                {
                    $vehicleLocation = VehicleLocations::where('name',$data[8])->where('vehicle_region_id',$vehicleRegion->id)->select('id')->first();
                    if(!$vehicleLocation) 
                    {
                        $vehicleLocation = $this->insertlocationData($data,$vehicleRegion,1);
                        $locationsNotFound[] = [$data[8]];
                    }
                }
            }
            else
            {
                $vehicleRegion = VehicleRegions::where('name',$data[7])->select('id')->first();
                if($data[8] != '')
                {
                    $vehicleLocation = VehicleLocations::where('name',$data[8])->select('id')->first();
                    if(!$vehicleLocation) 
                    {
                        $vehicleLocation = $this->insertlocationData($data,$vehicleRegion,0);
                        $locationsNotFound[] = [$data[8]];
                    } 
                }
            }

            //$vehicleLocation = null;
            // if($data[8] != ''){
            //    $vehicleLocation = VehicleLocations::where('name', $data[8])->first();
            //     if(!$vehicleLocation) {
            //         $vehicleLocation = new VehicleLocations();
            //         $vehicleLocation->name = $data[8];
            //         $vehicleLocation->save();

            //         $locationsNotFound[] = [$data[8]];
            //     } 
            // }
        }

        if(!$vehicleType) {
            if($brandName === 'rps') {
                $data[11] = "Error";
                $data[12] = "Vehicle profile not found.";
            } else {
                $data[16] = "Error";
                $data[17] = "Vehicle profile not found.";
            }
            $allVehiclesStatus[] = $data;
            $vehicleProfileNotFound[] = $data;
            return;
        }

        if($brandName === 'rps') {
            $vehicle->registration = $data[3];
        } else {
            $vehicle->registration = $data[0];
        }
        $vehicle->vehicle_type_id = $vehicleType->id;
        if($brandName === 'rps') {
            // if($isVehicleExist === false) {
            if(!($isVehicleExist === true && $isArchievedVehicle === false)) {
                $vehicle->status = 'Roadworthy';
            }
        } else {
            if(!($isVehicleExist === true && $isArchievedVehicle === false)) {
                $vehicle->status = $data[2];
            }
            $vehicle->usage_type = $data[3];
        }
        $vehicle->nominated_driver = $nominatedDriverId;
        $vehicle->vehicle_division_id = $vehicleDivision ? $vehicleDivision->id : null;
        $vehicle->vehicle_region_id = $vehicleRegion ? $vehicleRegion->id : null;
        if($brandName !== 'rps') {
            $vehicle->dt_registration = $data[5] ? Carbon::createFromFormat('d/m/Y', $data[5])->format('d M Y') : null;
            $vehicle->vehicle_location_id = (isset($data[8]) && $vehicleLocation !== null) ? $vehicleLocation->id : null;
            $vehicle->dt_mot_expiry = $data[9] ? Carbon::createFromFormat('d/m/Y', $data[9])->format('d M Y') : null;
            $vehicle->dt_tax_expiry = $data[10] ? Carbon::createFromFormat('d/m/Y', $data[10])->format('d M Y') : null;
            $vehicle->dt_loler_test_due = $data[13] ? Carbon::createFromFormat('d/m/Y', $data[13])->format('d M Y') : null;
            $vechileCategory =  $vehicleType->vehicle_category;
            if ($vechileCategory == 'non-hgv') {
                $vehicle->dt_tacograch_calibration_due = NULL;
            } else {
                $vehicle->dt_tacograch_calibration_due = $data[14] ? Carbon::createFromFormat('d/m/Y', $data[14])->format('d M Y') : null;
            }
        }
        if(env('BRAND_NAME') === 'skanska') {
            $vehicle->dt_annual_service_inspection = $data[11] ? Carbon::createFromFormat('d/m/Y', $data[11])->format('d M Y') : null;
            $vehicle->dt_next_service_inspection = $data[12] ? Carbon::createFromFormat('d/m/Y', $data[12])->format('d M Y') : null;
        } else if(env('BRAND_NAME') === 'mgroupservices') {
            $vehicle->dt_next_service_inspection = $data[11] ? Carbon::createFromFormat('d/m/Y', $data[11])->format('d M Y') : null;
            $vehicle->next_pmi_date = $data[12] ? Carbon::createFromFormat('d/m/Y', $data[12])->format('d M Y') : null;
            // $vehicle->next_pmi_date = $data[12] ? date ("d M Y", strtotime($vehicleType->pmi_interval, strtotime(Carbon::createFromFormat('d/m/Y', trim($data[12]))->format('d M Y')))) : null;
        }
        
        if($brandName === 'rps') {
            if($isVehicleExist === false) {
                $vehicle->is_telematics_enabled= 0;
                $vehicle->staus_owned_leased = "Leased";
                $vehicle->usage_type = "Commercial";
            }
            $vehicle->dt_added_to_fleet = $data[7] ? Carbon::createFromFormat('Y-m-d', $data[7])->format('d M Y') : null;
        } else {
            if($isVehicleExist === false) {
                $vehicle->is_telematics_enabled = $data[15] ? (strtolower($data[15]) === "yes" ? 1 : 0) : 0;
            }
            $vehicle->dt_added_to_fleet = $data[16] ? Carbon::createFromFormat('d/m/Y', $data[16])->format('d M Y') : null;
        }
        
        if($isVehicleExist === false) {
            $vehicle->created_by = $createdUpdatedById;
        }
        $vehicle->updated_by = $createdUpdatedById;

        if($brandName !== 'rps') {
            if($isArchievedVehicle === true && ($data[2] !== 'Archived' && $data[2] !== 'Archived - De-commissioned' && $data[2] !== 'Archived - Written off')) {
                $vehicle->deleted_at = null;
            }
        } else {
            if($isArchievedVehicle === true) {
                $vehicle->deleted_at = null;
            }
        }

        if($brandName === 'rps') {
            $vehicleRepairLocations = VehicleRepairLocations::where('name', $data[10])->select('id')->first();
            $vehicle->contract_id = trim($data[1]) ? $data[1] : null;
            $vehicle->notes = trim($data[2]) ? $data[2] : null;
            $vehicle->vehicle_repair_location_id = $vehicleRepairLocations ? $vehicleRepairLocations->id : null;
        }

        $vehicle->save();

        if($brandName === 'rps') {
            $vehicleUsageHistory = null;
            $toCreateNewEntry = false;
            $oldVehicleUsageHistory = null;

            /**#3642 comments - ignore "Driver Assign Date"($data[8]) column for time being
            $newVehicleUsageHistoryFromDate = Carbon::createFromFormat('d/m/Y', $data[8])->startOfDay();

            $vehicleUsageHistory = VehicleUsageHistory::where('vehicle_id', $vehicle->id)->first();
            if(!$vehicleUsageHistory || !$isVehicleExist) {
                $toCreateNewEntry = true;
            }

            if(!$toCreateNewEntry && $nominatedDriverId) {
                if($oldNominatedDriverId != $nominatedDriverId) {
                    $oldVehicleUsageHistory = VehicleUsageHistory::where('user_id', $oldNominatedDriverId)->where('vehicle_id', $vehicle->id)->first();
                    $oldVehicleUsageHistoryFromDate = Carbon::parse($oldVehicleUsageHistory->from_date)->startOfDay();
                    if($oldVehicleUsageHistoryFromDate->notEqualTo($newVehicleUsageHistoryFromDate)) {
                        $toCreateNewEntry = true;
                        if($oldVehicleUsageHistory->to_date === null) {
                            $oldVehicleUsageHistory->to_date = $newVehicleUsageHistoryFromDate->format('Y-m-d');
                        }
                    }
                }
            }

            if($toCreateNewEntry) {
                $vehicleHistory = new VehicleUsageHistory();
                $vehicleHistory->user_id = $nominatedDriverId ? $nominatedDriverId : $createdUpdatedById;
                $vehicleHistory->vehicle_id = $vehicle->id;
                $vehicleHistory->from_date = $newVehicleUsageHistoryFromDate->format('Y-m-d');
                $vehicleHistory->save();
            }*/

            if($oldVehicleRegionId != $vehicle->vehicle_region_id && $isVehicleExist) {
                $checkAssignment = VehicleAssignment::where('vehicle_id', $vehicle->id)->orderBy('id', 'DESC')->first();
                if ($checkAssignment) {
                    $vehicleAssignment = $checkAssignment;
                    $toDate = Carbon::now()->subDays('1');
                    $fromDate = Carbon::parse($vehicleAssignment->from_date);

                    if ($toDate->lt($fromDate)) {
                        $toDate = $fromDate;
                    }
                    $vehicleAssignment->to_date = $toDate->format('d M Y');;
                    $vehicleAssignment->save();
                } else {
                    $vehicleAssignment = new VehicleAssignment();
                    $vehicleAssignment->vehicle_id = $vehicle->id;
                    $vehicleAssignment->vehicle_division_id = $oldVehicleDivisionId;
                    $vehicleAssignment->vehicle_location_id = $oldVehicleLocationId;
                    $vehicleAssignment->vehicle_region_id = $oldVehicleRegionId;
                    $vehicleAssignment->from_date = $vehicle->dt_added_to_fleet;
                    $toDate = Carbon::now()->subDays('1');
                    $fromDate = Carbon::parse($vehicleAssignment->from_date);

                    if ($toDate->lt($fromDate)) {
                        $toDate = $fromDate;
                    }
                    $vehicleAssignment->to_date = $toDate->format('d M Y');
                    $vehicleAssignment->save();
                }

                $vehicleAssignmentLast = new VehicleAssignment();
                $vehicleAssignmentLast->vehicle_id = $vehicle->id;
                $vehicleAssignmentLast->vehicle_division_id = $vehicle->vehicle_division_id;
                $vehicleAssignmentLast->vehicle_location_id = $vehicle->vehicle_location_id;
                $vehicleAssignmentLast->vehicle_region_id = $vehicle->vehicle_region_id;
                $vehicleAssignmentLast->from_date = Carbon::now()->format('d M Y');
                $vehicleAssignmentLast->to_date = null;
                $vehicleAssignmentLast->save();
            }
        }

        if($brandName == 'skanska' || ($brandName == 'rps' && trim($data[8]))) {
            $checkVehicleUsageEntry = true;
            if($brandName == 'skanska') {
                $newVehicleUsageHistoryFromDate = Carbon::now()->toDateTimeString();
                $newVehicleUsageHistoryToDate = Carbon::now()->toDateTimeString();
            } else {
                $newVehicleUsageHistoryFromDate = trim($data[8]);
                $newVehicleUsageHistoryToDate = Carbon::parse(trim($data[8]))->addDay(-1)->toDateTimeString();
                $vehicleUsageHistoryData = VehicleUsageHistory::where('vehicle_id', $vehicle->id)->whereNull('to_date')->first();
                if(isset($vehicleUsageHistoryData)) {
                    $vehicleUsageHistoryDate = Carbon::parse($vehicleUsageHistoryData->from_date);
                    if($vehicleUsageHistoryDate->eq(Carbon::parse($newVehicleUsageHistoryFromDate))) {
                        $checkVehicleUsageEntry = false;
                    } else {
                        if($oldNominatedDriverId && $nominatedDriverId == $oldNominatedDriverId) {
                            $checkVehicleUsageEntry = false;
                        }
                    }
                }
            }
            if($checkVehicleUsageEntry) {
                if(!$oldNominatedDriverId && $nominatedDriverId) {
                    $toCreateNewEntry = true;
                } else if($oldNominatedDriverId && !$nominatedDriverId) {
                    $oldVehicleUsageHistory = VehicleUsageHistory::where('user_id', $oldNominatedDriverId)->where('vehicle_id', $vehicle->id)->whereNull('to_date')->first();
                    if(isset($oldVehicleUsageHistory)) {
                        $oldVehicleUsageHistory->to_date = $newVehicleUsageHistoryToDate;
                        $oldVehicleUsageHistory->save();
                        $toCreateNewEntry = false;
                    }
                } else if($oldNominatedDriverId && $nominatedDriverId && $oldNominatedDriverId != $nominatedDriverId) {
                    $oldVehicleUsageHistory = VehicleUsageHistory::where('user_id', $oldNominatedDriverId)->where('vehicle_id', $vehicle->id)->whereNull('to_date')->first();
                    if(isset($oldVehicleUsageHistory)) {
                        $oldVehicleUsageHistory->to_date = $newVehicleUsageHistoryToDate;
                        $oldVehicleUsageHistory->save();
                        $toCreateNewEntry = true;
                    }
                } else {
                    $toCreateNewEntry = false;
                }

                if($toCreateNewEntry) {
                    $vehicleHistory = new VehicleUsageHistory();
                    $vehicleHistory->user_id = $nominatedDriverId;
                    $vehicleHistory->vehicle_id = $vehicle->id;
                    $vehicleHistory->from_date = $newVehicleUsageHistoryFromDate;
                    $vehicleHistory->save();
                }
            }
        }

        // $allConfigs = config('config-variables');
        // if($brandConfigExist) {
        //     $allConfigs = config(env('BRAND_NAME') . '.config-variables');
        // }

        if($brandName === 'rps') {
            $data[0] = ltrim($data[0], '0');
            $allVehicleRegions = VehicleRegions::all()->pluck('name')->toArray();
            if ($data[0] != '' && !in_array($data[0], $allVehicleRegions) && !in_array($data[0], array_column($regionsNotFound, 0))) {
                $regionsNotFound[] = [$data[0]];
            }
        } else {
            $allVehicleDivisions = VehicleDivisions::all()->pluck('name')->toArray();
            $allVehicleRegions = VehicleRegions::all()->groupBy('vehicle_division_id')->toArray();
            $divisionRegions = isset($allVehicleRegions[$vehicle->vehicle_division_id]) ? $allVehicleRegions[$vehicle->vehicle_division_id] : [];
            $divisionRegions = count($divisionRegions) > 0 ? array_column($divisionRegions, 'name') : [];

            if($data[6] != '' && !in_array($data[6], $allVehicleDivisions) && !in_array($data[6], array_column($divisionsNotFound, 0))) {
                $divisionsNotFound[] = [$data[6]];
            }
            
            if( ($data[7] != '' && !isset($allVehicleRegions[$vehicle->vehicle_division_id]) || !in_array($data[7], $divisionRegions)) && !in_array($data[7], array_column($regionsNotFound, 0)) ) {
                $regionsNotFound[] = [$data[7]];
            }
        }

        if($isVehicleExist === false) {
            if($brandName === 'rps') {
                $newAddedVehicles[] = [
                    $data[3],
                    $data[4],
                    $data[5],
                    $data[0]
                ];

                $data[11] = "Added";
                $data[12] = "New vehicle have been added to the platform.";
            } else {
                $newAddedVehicles[] = [
                    $data[0],
                    $data[1],
                    $data[2],
                    $data[6],
                    $data[7],
                    $data[8]
                ];

                $data[16] = "Added";
                $data[17] = "New vehicle have been added to the platform.";
            }
            $allVehiclesStatus[] = $data;
        } else {
            if($brandName === 'rps') {
                $data[11] = "Updated";
                $data[12] = "Vehicle details have been updated.";
            } else {
                $data[16] = "Updated";
                $data[17] = "Vehicle details have been updated.";
            }
            $allVehiclesStatus[] = $data;
        }

        return $vehicle;
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
        $s3path = 'rps_import/vehicles/'.$filename;
        $disk = Storage::disk('s3');
        $contents = File::get($localpath);
        $disk->put($s3path, $contents, 'public');
        // $s3Url = $disk->url($s3path);
        $s3 = Storage::disk('s3')->getAdapter()->getClient();
        $s3Url = $s3->getObjectUrl(env('S3_UPLOADS_BUCKET'), $s3path);
        \Log::info('s3Url: '.$s3Url);
        return $s3Url;
    }

    public function setDivisionRegionArray($allDivisions,$userRegions=[])
    {
        //print_r($userRegions);die;
        $data=[];
        $vehicleRegions=[];
        $vehicleBaseLocations=[];
        $vehicleDivisions = ['' => ''];
        if( is_array($allDivisions) && !empty($allDivisions)) 
        {
            foreach ($allDivisions as $divisions) 
            {
                // create all divisions lists
                if(isset($divisions['name']) && $divisions['id']) 
                {
                    $vehicleDivisions[$divisions['id']] = $divisions['name'];
                }

                if(isset($divisions['vehicle_regions']) && is_array($divisions['vehicle_regions']) && !empty($divisions['vehicle_regions'])) 
                {
                    // create division wise regions lists
                    foreach ($divisions['vehicle_regions'] as $regions) 
                    {
                       
                       if(in_array($regions['id'],$userRegions))
                       {
                            if(isset($regions['name']) && $regions['id']) 
                            {
                                $vehicleRegions[$divisions['id']][$regions['id']] = $regions['name'];
                            }
                            if(isset($regions['vehicle_locations']) && is_array($regions['vehicle_locations']) && !empty($regions['vehicle_locations'])) 
                            {
                                // create region wise locations lists
                                foreach ($regions['vehicle_locations'] as $locations) 
                                {
                                    if(isset($locations['name']) && $locations['id']) 
                                    {
                                        $vehicleBaseLocations[$regions['id']][$locations['id']] = $locations['name'];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $data['vehicleDivisions']=$vehicleDivisions;
        $data['vehicleRegions']=$vehicleRegions;
        $data['vehicleBaseLocations']=$vehicleBaseLocations;
        //print_r($data);die;
        return $data;
        //return $vehicleRegions.$vehicleBaseLocations.$vehicleDivisions;
    }
    public function setRegionLocationArray($allDivisions)
    {
        $data=[];
        $vehicleRegions=[];
        $vehicleBaseLocations=[];
        foreach ($allDivisions as $regions) 
        {
            if(isset($regions['name']) && $regions['id']) 
            {
                $vehicleRegions[$regions['id']] = $regions['name'];
            }
            if(isset($regions['vehicle_locations']) && is_array($regions['vehicle_locations']) && !empty($regions['vehicle_locations'])) 
            {
                // create region wise locations lists
                foreach ($regions['vehicle_locations'] as $locations) 
                {
                    if(isset($locations['name']) && $locations['id']) 
                    {
                        $vehicleBaseLocations[$regions['id']][$locations['id']] = $locations['name'];
                    }
                }
            }
        }

        $data['vehicleRegions']=$vehicleRegions;
        $data['vehicleBaseLocations']=$vehicleBaseLocations;
        return $data;
    }

    public function getData()
    {
        $userDivisions = VehicleDivisions::all()->lists('id')->toArray();
        $userRegions = VehicleRegions::all()->lists('id')->toArray();
        if( env('IS_REGION_LOCATION_LINKED_IN_VEHICLE') && env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'))
        {
            $allDivisions = VehicleDivisions::with(['vehicleRegions', 'vehicleRegions.vehicleLocations'])->whereIn('vehicle_divisions.id',$userDivisions)->orderBy('name', 'asc')->get()->toArray();//->toSql();
            //print_r($allDivisions);die;
            $data=$this->setDivisionRegionArray($allDivisions,$userRegions);
        }
        else if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE') && !env('IS_REGION_LOCATION_LINKED_IN_VEHICLE'))
        {
            $allDivisions = VehicleDivisions::with(['vehicleRegions'])->whereIn('vehicle_divisions.id',$userDivisions)->orderBy('name', 'asc')->get()->toArray();
            $data=$this->setDivisionRegionArray($allDivisions,$userRegions);
            $data['vehicleBaseLocations'] = VehicleLocations::orderBy('name', 'asc')->lists('name', 'id')->toArray();
        }
        else if(!env('IS_DIVISION_REGION_LINKED_IN_VEHICLE') && env('IS_REGION_LOCATION_LINKED_IN_VEHICLE'))
        {
            $allDivisions = VehicleRegions::with(['vehicleLocations'])->whereIn('vehicle_regions.id',$userRegions)->orderBy('name', 'asc')->get()->toArray();
            $data=$this->setRegionLocationArray($allDivisions);
            $data['vehicleDivisions'] = ['' => ''] + VehicleDivisions::orderBy('name', 'asc')->lists('name', 'id')->toArray();
            
        }
        else //(!env('IS_DIVISION_REGION_LINKED_IN_VEHICLE') && !env('IS_REGION_LOCATION_LINKED_IN_VEHICLE'))
        {
            $data['vehicleDivisions'] =['' => ''] + VehicleDivisions::orderBy('name', 'asc')->lists('name', 'id')->toArray();
            $data['vehicleRegions']= VehicleRegions::orderBy('name', 'asc')->lists('name', 'id')->toArray();
            $data['vehicleBaseLocations'] = VehicleLocations::orderBy('name', 'asc')->lists('name', 'id')->toArray();
        }
        return $data;
    }
    public function regionForSelect($data)
    {
        $region_for_select = [];
        foreach ($data['vehicleRegions'] as $keys => $values) {
            foreach ($values as $key => $value) {
                $region_for_select[$key]=$value .' ('. $data['vehicleDivisions'][$keys].')';
            }
        }
        asort($region_for_select);
        return  $region_for_select;
    }
    public function getDataDivRegLoc()
    {
        $authUser = Auth::user();
        $userDivisions = $authUser->divisions->lists('id')->toArray();
        $userRegions = $authUser->regions->lists('id')->toArray();
        $vehicleLocationsArray = VehicleLocations::orderBy('name', 'asc')->lists('name', 'id')->toArray();
        $vehicleDivisionsArray = VehicleDivisions::orderBy('name', 'asc')->lists('name', 'id')->toArray();

        if(env('IS_REGION_LOCATION_LINKED_IN_VEHICLE') && env('IS_DIVISION_REGION_LINKED_IN_VEHICLE')) {
            $allDivisions = VehicleDivisions::with(['vehicleRegions', 'vehicleRegions.vehicleLocations'])->whereIn('vehicle_divisions.id',$userDivisions)->orderBy('name', 'asc')->get()->toArray();
            $data=$this->setDivisionRegionArray($allDivisions,$userRegions);
        } else if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE') && !env('IS_REGION_LOCATION_LINKED_IN_VEHICLE')) {
            $allDivisions = VehicleDivisions::with(['vehicleRegions'])->whereIn('vehicle_divisions.id',$userDivisions)->get()->toArray();
            $data=$this->setDivisionRegionArray($allDivisions,$userRegions);
            $data['vehicleBaseLocations'] = $vehicleLocationsArray;
        } else if(!env('IS_DIVISION_REGION_LINKED_IN_VEHICLE') && env('IS_REGION_LOCATION_LINKED_IN_VEHICLE')) {
            // $allDivisions = VehicleRegions::with(['vehicleLocations'])->whereIn('vehicle_regions.id',$userRegions)->get()->toArray();
            $allDivisions = VehicleRegions::with(['vehicleLocations'])->get()->toArray();
            $data=$this->setRegionLocationArray($allDivisions);
            $data['vehicleDivisions'] = ['' => ''] + $vehicleDivisionsArray;
        } else {
            $data['vehicleDivisions'] =['' => ''] + $vehicleDivisionsArray;
            $data['vehicleRegions']= VehicleRegions::whereIn('vehicle_regions.id',$userRegions)->orderBy('name', 'asc')->lists('name', 'id')->toArray();
            $data['vehicleBaseLocations'] = $vehicleLocationsArray;
        }
        
        return $data;
    }
    public function InsertlocationData($data,$vehicleRegion,$flag)
    {
        $vehicleLocation = new VehicleLocations();
        $vehicleLocation->name = $data[8];
        if($flag==1)
        {
            $vehicleLocation->vehicle_region_id=$vehicleRegion->id;
        }
        $vehicleLocation->save();
        return $vehicleLocation;
    }

    public function getDivisionRegionLinkedData()
    {
        $vehicleRegion = [];
        if (env('IS_DIVISION_REGION_LINKED_IN_VEHICLE')) {
            $allDivisions = VehicleDivisions::with(['vehicleRegions'=> function($query) {
                $query->whereIn('vehicle_regions.id', \Auth::user()->regions->lists('id')->toArray());
            }])->whereIn('vehicle_divisions.id', \Auth::user()->divisions->lists('id')->toArray())->orderBy('name', 'asc')->get()->toArray();
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
                                $vehicleRegion[$regions['id']] = $divisionName . ' - '.$regions['name'];
                            }
                        }
                    }
                }
            }
        } else {
            $vehicleRegion = VehicleRegions::whereIn('vehicle_regions.id', \Auth::user()->regions->lists('id')->toArray())->lists('name', 'id')->toArray();
        }

        return $vehicleRegion;
    }

    public function isDVSAConfigurationTabEnabled()
    {
        $isDVSAConfigurationTabEnabled = false;
        $dvsaSetting = Settings::where('key', 'is_dvsa_enabled')->first();
        if ($dvsaSetting && $dvsaSetting->value == 1) {
            $isDVSAConfigurationTabEnabled = true;
        }

        return $isDVSAConfigurationTabEnabled;
    }

    public function sendVehicleMaintenanceServiceDistanceNotification($vehicle,$isSendNotification = false, $notification = false)
    {
        $lastNotificationOdometer = $vehicle->last_service_distance_notification_odometer;
        $odometerToCheck = $vehicle->next_service_inspection_distance - 1000;

        if ($lastNotificationOdometer && $lastNotificationOdometer >= $odometerToCheck && $lastNotificationOdometer <= $vehicle->next_service_inspection_distance) {
            return false;
        }

        if($vehicle->last_odometer_reading >= strval($odometerToCheck)) {
            $isSendNotification = true;
        }
        if ($isSendNotification) {
            $driver = $vehicle->nominatedDriver;
            $vehicleLink = url("/vehicles/{$vehicle['id']}");
            $eventCaption = 'Service reminder';
            $eventColumn = 'next_service_inspection_distance';
            $eventMessage = 'service';

            if ($driver) {
                if($notification != false) {
                    $notification = VehicleMaintenanceNotification::where('event_type', 'next_service_inspection_distance')
                                ->where('user_id',$driver->id)
                                ->where('is_enabled', true)
                                ->get()
                                ->keyBy('user_id');
                }
                if(isset($notification[$driver->id])) {
                    $registration = $vehicle->registration;
                    $email = $driver->email;
                    $nextServiceInspectionDistance = floor($vehicle->$eventColumn) == $vehicle->$eventColumn ? number_format($vehicle->$eventColumn, 0) : number_format($vehicle->$eventColumn, 2);
                    $odometerSetting = $vehicle->type->odometer_setting;

                    // Sending main notification to the nominated driver for the vehicle
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        Mail::send('emails.vehicle_maintenance_notification', ['userName' => $driver->first_name, 'event' => $eventCaption, 'registration' => $registration, 'nextServiceInspectionDistance' => $nextServiceInspectionDistance, 'eventMessage' => $eventMessage, 'vehicleLink' => $vehicleLink, 'eventName' => 'next_service_inspection_distance', 'odometerSetting' => $odometerSetting], function ($message) use ($email, $driver, &$link, $registration, $vehicleLink) {
                            $message->to($email, $driver->first_name, $link, $registration, $vehicleLink);
                            $message->subject('fleetmastr - vehicle maintenance notification '.$registration);
                        });
                    }

                    $vehicle->last_service_distance_notification_odometer = $vehicle->last_odometer_reading;
                    $vehicle->save();
                }
            }


        }
    }

    public function getYearDatesArray($year) {
        $data = [];
        $months = [];
        for ($m=1; $m<=12; $m++) {
            $month = date('F', mktime(0,0,0,$m, 1, date('Y')));
            $months[$m] = $month;
        }

        $data['year'] = $year;
        $data['months'] = $months;
        $dates = [];
        foreach ($months as $monthNumber => $monthName) {
            $dates[$monthNumber] = array();
            $thisMonth = Carbon::parse($year.'-'.$monthNumber);
            $startOfTheMonth = Carbon::parse($year.'-'.$monthNumber)->firstOfMonth();
            $endOfTheMonth = Carbon::parse($year.'-'.$monthNumber)->endOfMonth();

            $startDay = $startOfTheMonth->format('l');
            $endDay = $endOfTheMonth->format('l');

            if ($startDay == 'Sunday') {
                $startDate = $startOfTheMonth;
            } else {
                $startDate = $startOfTheMonth->startOfWeek()->subDay();
            }

            if ($endDay == 'Saturday') {
                $endDate = $endOfTheMonth;
            } else {
                if ($endDay == 'Sunday') {
                    $endOfTheMonth = $endOfTheMonth->addDay();
                } else {
                    $endOfTheMonth = $endOfTheMonth->endOfWeek();
                }

                $endDate = $endOfTheMonth->endOfWeek()->subDay();

            }


            $weeks = ($endDate->diffInDays($startDate)+1) / 7;

            if ($weeks < 6) {
                $endDate = $endDate->addWeek()->endOfWeek()->subDay();
            }


            $begin = new \DateTime( $startDate->format('Y-m-d') );
            $end = new \DateTime( $endDate->addDay()->format('Y-m-d'));

            $interval = new \DateInterval('P1D');
            $daterange = new \DatePeriod($begin, $interval ,$end);

            foreach ($daterange as $key => $date) {

                if ($date->format('m') == $thisMonth->format('m')) {
                    $class = 'current-month';
                    if ($date->format('Y-m-d') == Carbon::now()->format('Y-m-d')) {
                        $class.=' today';
                    }
                } else {
                    $class = '';
                }

                $attr = "data-date='".$date->format('Y-m-d')."'";

                $dates[$monthNumber][$key]['stringDate'] = $date->format('Y-m-d');
                $dates[$monthNumber][$key]['label'] = (int)$date->format('d');
                $dates[$monthNumber][$key]['class'] = $class;
                $dates[$monthNumber][$key]['attribute'] = $attr;
            }

        }

        $data['dates'] = $dates;
        return $data;
    }
    public function getVehicleById($vehicleId=null) {
        $vehicleData=$this->allVehicles(null,$vehicleId);
        if($vehicleData && isset($vehicleData[0])){
            return $vehicleData[0];
        }
        return null;
    }
    public function allVehicles($lastUpdatedTimestamp,$vehicleId=null) {
        $vehicleList = [];
        $vehicles = Vehicle::with(['maintenanceHistories','type', 'defects' => function($query){$query->where('status','<>','Resolved');}, 'defects.defectMaster','defects.media']);
        
        if($vehicleId!=null){
            $vehicles->whereId($vehicleId);
        }

        if ($lastUpdatedTimestamp === null) {
            $vehicles = $vehicles->get();
        } else {
            $defects = Defect::where('updated_at','>',$lastUpdatedTimestamp)->get();
            $defectVehicleIds = $defects->pluck('vehicle_id');

            $vehicles = $vehicles->where('vehicles.updated_at', '>', $lastUpdatedTimestamp)
                                ->orWhereIn('vehicles.id',$defectVehicleIds)
                                ->get();
        }

        if (!empty($vehicles)){
            $survey_quesMaster = DB::table('survey_master')
                ->select('id','vehicle_type','action','vehicle_category')
                ->whereIn('action',['checkin','checkout','defect'])->get();
            $survey_quesMaster = collect($survey_quesMaster)->groupBy('vehicle_category');

            $countsMastr = DB::table('checks')
                ->select('vehicle_id', \DB::raw('count(*) as count'))
                ->groupBy('vehicle_id')
                ->get();

            $countsMastr = collect($countsMastr)->groupBy('vehicle_id');

            $distanceEvent = MaintenanceEvents::where('slug','next_service_inspection_distance')->first();

            foreach ($vehicles as $key => $vehicle) {
                $survey_ques = isset($survey_quesMaster[$vehicle->type->vehicle_category]) ? $survey_quesMaster[$vehicle->type->vehicle_category]->toArray() : [];
                $checkout_survey_ques_id = 0;
                $checkin_survey_ques_id = 0;
                $defect_survey_ques_id = 0;
                foreach ($survey_ques as $survey_que) {
                    $vtypeArr = explode(',', $survey_que->vehicle_type);
                    if(in_array($vehicle->type->id, $vtypeArr)){
                        if ($survey_que->action == 'checkin') {
                            $checkin_survey_ques_id = $survey_que->id;
                        }
                        if ($survey_que->action == 'checkout') {
                            $checkout_survey_ques_id = $survey_que->id;
                        }
                        if ($survey_que->action == 'defect') {
                            $defect_survey_ques_id = $survey_que->id;
                        }                        
                    }
                }
                ///////
                $vehicleFormatted = $vehicle->format();
                if ($vehicle->type->service_interval_type == 'Distance' && $vehicle->next_service_inspection_distance && $vehicle->maintenanceHistories && count($vehicle->maintenanceHistories) > 0) {
                    $lastInspectionDistance = $vehicle->next_service_inspection_distance - (int)str_replace(",","",$vehicle->type->service_inspection_interval);

                    $past = collect($vehicle->maintenanceHistories->toArray())->where('event_type_id',$distanceEvent->id)
                        ->where('event_planned_distance',(string)$lastInspectionDistance)
                        ->where('event_status','Incomplete')->first();

                    if ($past) {
                        $vehicleFormatted['data']['is_next_service_distance_exceeded'] = true;
                        $vehicleFormatted['data']['previous_next_service_distance'] = $lastInspectionDistance;
                    }
                }
                $vehicleFormatted["meta"]["pre_existing_defect"] = false;
                $defects_list = $this->getDefectList($vehicle->id,$vehicle);

                if(count($defects_list)>0){
                    $vehicleFormatted["meta"]["pre_existing_defect"] = true;
                }



                $totalCheckCount = (isset($countsMastr[$vehicle->id]) && isset($countsMastr[$vehicle->id][0]->count)) ? $countsMastr[$vehicle->id][0]->count: 0;
                $vehicleFormatted["meta"]["defects_list"] = $defects_list;
                $vehicleFormatted["meta"]["checkin_survey_ques_id"] = $checkin_survey_ques_id;
                $vehicleFormatted["meta"]["checkout_survey_ques_id"] = $checkout_survey_ques_id;
                $vehicleFormatted["meta"]["defect_survey_ques_id"] = $defect_survey_ques_id;
                $vehicleFormatted["meta"]["check_count"] = $totalCheckCount;
                array_push($vehicleList, $vehicleFormatted);                
            }
        } else {
            return $this->response->errorNotFound("Vehicle check not found.");
        }
        
        return $vehicleList;
    }

    public function getDefectList($vehicleId,$vehicle = false)
    {
        if ($vehicle == false) {
            $defects = Defect::with('defectMaster', 'media')->where('vehicle_id', $vehicleId)->where('defects.status', '<>', 'Resolved')->get();
        } else {
           $defects = $vehicle->defects;
        }

        $dynamicDefects = json_decode(env('HAVING_DYNAMIC_DEFECTS'), true);
        $defectList = array();

        foreach ($defects as $defect) {
            $media = $defect->media;            
            $mediaUrl = "";
            if($media->count() > 0) {
                foreach ($media as $singleMedia) {
                    $mediaUrl = ($mediaUrl == '') ? getPresignedUrl($singleMedia) : $mediaUrl . '|' . getPresignedUrl($singleMedia);
                }
            }
            $isHavingDynamicDefects = (isset($dynamicDefects[$defect->defectMaster->order]) && in_array($defect->defectMaster->defect_order, $dynamicDefects[$defect->defectMaster->order])) ? 1 : 0 ;
            $data = [
                "id" => $defect->defectMaster->id,
                "_image" => ($defect->defectMaster->has_image)?"yes":"no",
                "_text" => ($defect->defectMaster->has_text)?"yes":"no",
                "imageString" => $mediaUrl,
                "image_exif" => "",
                "selected" => "yes",
                "text" =>  $defect->title != null ? $defect->title : $defect->defectMaster->defect,
                "textString" => "",
                "prohibitional" => ($defect->defectMaster->is_prohibitional)?"yes":"no",
                "safety_notes" => $defect->defectMaster->safety_notes,
                "defect_id" => $defect->id,
                "read_only" => "yes",
                "comments" => $defect->comments,
                "is_dynamically_added" => $isHavingDynamicDefects,
                "report_datetime" => $defect->report_datetime != null ? $defect->report_datetime->setTimezone(config('config-variables.displayTimezone'))->format('H:i j M Y'):"",
            ];
            $defectList[$defect->defectMaster->page_title][] = $data;            
        }

        $newDefectList = array();
        foreach($defectList as $key => $value){
            $data = [ 
              "defects_title" => $key,
              "added_defects" => $value
            ];
            $newDefectList[] = $data;
        }

        return $newDefectList;
    }
}
