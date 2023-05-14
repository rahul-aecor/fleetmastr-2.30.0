<?php
namespace App\Services;

use App\Models\UserVerification;
use File;
use Mail;
use Storage;
use Auth;
use Carbon\Carbon;
use App\Models\Vehicle;

class FuelCostService
{
    /**
     * Response file.
     *
     * @var string
     */
    protected $notImportFuelCostsResponseFileName;

    /**
     * Response file.
     *
     * @var string
     */
    protected $importAllFuelCostsResponseFileName;

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
        $this->notImportFuelCostsResponseFileName = Carbon::now()->format('Ymd').'_fleetmastr_fuel_import_failedimports.csv';
        $this->importAllFuelCostsResponseFileName = Carbon::now()->format('Ymd').'_fleetmastr_fuel_import_summary.csv';
    }

    /**
     * Process users.
     *
     * @return mixed
     */
    public function processData($fuelCostsFile)
    {
        $brandName = env('BRAND_NAME');
        $brandConfigExist = false;
        $brandConfigFile = config_path().'/'.env('BRAND_NAME').'/config-variables.php';
        if(\File::exists($brandConfigFile)){
            $brandConfigExist = true;
        }

        $allFuelCostsStatus = [];
        $notImportedFuelCosts = [];
        $isDataAvailableForSync = false;

        $row = 1;

        if (file_exists($fuelCostsFile) && ($handle = fopen($fuelCostsFile, 'r')) !== FALSE) {
            while (($data = fgetcsv ($handle, 5000, ',')) !== FALSE) {
            	$data = array_map('trim', $data);
            	if($row === 1) {
                    $data[3] = "Import Status";
                    $allFuelCostsStatus[] = $data;
                    $notImportedFuelCosts[] = $data;
            		$row++;
                    continue;
                }

                $isDataAvailableForSync = true;
                $costDate = trim($data[0]);
                $registration = trim($data[1]);
                $cost = trim($data[2]);

                $vehicle = Vehicle::where('registration', $registration)->first();
                if(isset($vehicle)) {
                    $date = Carbon::createFromFormat('d/m/Y H:i', $costDate)->format('d M Y');
                    $fuelUse = json_decode($vehicle->fuel_use, true);

                    $costFromDateKey = $costKey = '';
                    if($fuelUse && !empty($fuelUse)) {
                        $costFromDateKey = array_search($date, array_column($fuelUse, 'cost_from_date'));
                        $costKey = array_search($cost, array_column($fuelUse, 'cost_value'));
                    }

                    if ( $costFromDateKey && $costFromDateKey != '' && $costKey && $costKey != '' ) {
                        //Do Nothing
                    } else {
                        $newFuelUseEntry = [];
                        $newFuelUseEntry['cost_from_date'] = $date;
                        $newFuelUseEntry['cost_to_date'] = $date;
                        $newFuelUseEntry['cost_value'] = $cost;
                        $fuelUse[] = $newFuelUseEntry;
                        $vehicle->fuel_use = json_encode($fuelUse);
                        $vehicle->save();
                    }

                    $data[3] = 'Imported successfully';

                } else {
                    $data[3] = 'Not imported vehicle not recognised';
                    $notImportedFuelCosts[] = $data;
                }

                $allFuelCostsStatus[] = $data;

                $row++;
            }
            fclose($handle);

            if($isDataAvailableForSync) {

                $notImportedResponseFileName = md5(time() . mt_rand(1,1000000)) . '.csv';
                $notImportedResponseFilePath = storage_path('importresponsefiles') . '/' . $notImportedResponseFileName;
                $allImportedResponseFileName = md5(time() . mt_rand(1,1000000)) . '.csv';
                $allImportedResponseFilePath = storage_path('importresponsefiles') . '/' . $allImportedResponseFileName;

                $this->prepareCSVFile($allImportedResponseFilePath, $allFuelCostsStatus);
                if(count($notImportedFuelCosts) > 1) {
                    $this->prepareCSVFile($notImportedResponseFilePath, $notImportedFuelCosts);
                }

                $importFuelCostsResponseEmailToAdmin = explode(",", env('IMPORT_FUEL_COSTS_RESPONSE_EMAIL_TO_ADMIN'));
                
                $importFuelCostsResponseEmailToDev = null;
                // if(env('IMPORT_FUEL_COSTS_RESPONSE_EMAIL_TO_DEV')) {
                //     $importFuelCostsResponseEmailToDev = explode(",", env('IMPORT_FUEL_COSTS_RESPONSE_EMAIL_TO_DEV'));
                // }
                $importFuelCostsResponseEmailToInternalAdmin = null;
                if(env('IMPORT_FUEL_COSTS_RESPONSE_EMAIL_TO_INTERNAL_ADMIN')) {
                    $importFuelCostsResponseEmailToInternalAdmin = explode(",", env('IMPORT_FUEL_COSTS_RESPONSE_EMAIL_TO_INTERNAL_ADMIN'));
                }

                //Upload file to s3
                $s3AllFuelCostsResponseFilePath = $this->uploadFileOnS3($allImportedResponseFileName, $allImportedResponseFilePath);

                $s3NotImportedFuelCostsResponseFilePath = "";
                if(count($notImportedFuelCosts) > 1) {
                    $s3NotImportedFuelCostsResponseFilePath = $this->uploadFileOnS3($notImportedResponseFileName, $notImportedResponseFilePath);
                }

                $notImportFuelCostsResponseFileName = $this->notImportFuelCostsResponseFileName;
                $importAllFuelCostsResponseFileName = $this->importAllFuelCostsResponseFileName;

                Mail::send('emails.fuel_costs_import_response_email', [], function ($message) use($importFuelCostsResponseEmailToAdmin, $importFuelCostsResponseEmailToInternalAdmin, $importFuelCostsResponseEmailToDev, $notImportFuelCostsResponseFileName, $s3NotImportedFuelCostsResponseFilePath, $importAllFuelCostsResponseFileName, $s3AllFuelCostsResponseFilePath, $notImportedFuelCosts) {
                    $message->to($importFuelCostsResponseEmailToAdmin);
                    if($importFuelCostsResponseEmailToInternalAdmin) {
                        $message->bcc($importFuelCostsResponseEmailToInternalAdmin);
                    }
                    $message->subject('fleetmastr - fuel import status');
                    $message->attach($s3AllFuelCostsResponseFilePath, ['as' => $importAllFuelCostsResponseFileName]);
                    if(count($notImportedFuelCosts) > 1) {
                        $message->attach($s3NotImportedFuelCostsResponseFilePath, ['as' => $notImportFuelCostsResponseFileName]);
                    }
                });
            }
        }

        if (file_exists($fuelCostsFile)) {
            File::delete($fuelCostsFile);
        }

        if($isDataAvailableForSync) {
            File::delete($allImportedResponseFilePath);
            if(count($notImportedFuelCosts) > 1) {
                File::delete($notImportedResponseFilePath);
            }
        }
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
}
