<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use Carbon\Carbon;
use App\Models\User;
use App\Models\UserDivision;
use App\Models\UserRegion;
use App\Models\Company;
use App\Models\Report;
use App\Models\ReportDownload;
use App\Models\VehicleType;
use App\Models\Check;
use App\Models\Defect;
use App\Models\TelematicsJourneys;
use App\Models\VehicleDivisions;
use App\Models\VehicleRegions;
use App\Models\VehicleLocations;
use App\Services\UserService;
use App\Services\CustomReportService;
use App\Services\Report as ReportService;
use App\Repositories\CustomReportRepository;
use Maatwebsite\Excel\Facades\Excel;
use Auth;
use Mail;
use Log;
use DB;
use StdClass;

class CreateCustomReport extends Job implements SelfHandling
{
    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $data;
    protected $reportDownloadId;
    protected $newReportData;
    protected $oldReportData;
    protected $obj_telematics_journeys;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $reportDownloadId, $newReportData, $oldReportData)
    {
        $this->data = $data;
        $this->reportDownloadId = $reportDownloadId;
        $this->newReportData = $newReportData;
        $this->oldReportData = $oldReportData;
        $this->obj_telematics_journeys = new TelematicsJourneys();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(UserService $userService, CustomReportRepository $customReportRepository, ReportService $reportService)
    {
        // Log::info('CreateDownloadableReport Job Starts');
        
        $data = $this->data;
        $newReportData = $this->newReportData;
        $oldReportData = $this->oldReportData;
        $slug = $oldReportData['slug'];
        
        $reportDownload = ReportDownload::find($this->reportDownloadId);

        $startDate = Carbon::parse($data['date_from']);
        $endDate = Carbon::parse($data['date_to']);
        $titleName = explode(" ",$oldReportData->name);
        $titleLength = count($titleName)-1;

        $reportName = '';
        foreach($titleName as $key => $title) {
            if($key == $titleLength) {
                $reportName = $reportName.'_'.strtolower($title).'_Custom';
            } else {
                $reportName = $reportName.$title;
            }
        }

        $sheetTitle = Carbon::now()->format('Ymd').'_'.$reportName;
        $reportTitle = $slug == 'standard_vehicle_profile_report' ? Carbon::now()->format('d M Y') : $startDate->format('d M Y')." - ".$endDate->format('d M Y');

        $otherParams = [
            'fileName' => $sheetTitle,
            'sheetTitle_lable' => "Report",
            'sheetTitle_value' => $newReportData->name,
            'sheetSubTitle_lable_first' => "Description",
            'sheetSubTitle_value_first' => $data['report_description'],
            'sheetSubTitle_lable_second' => "Duration",
            'sheetSubTitle_value_second' => $reportTitle,
            'sheetName' => $reportTitle,
            'boldLastRow' => false
        ];


        $allUserDivisions = UserDivision::all()->pluck('name', 'id');
        $allUserRegions = UserRegion::all()->pluck('name', 'id');

        $resultVehicle = $userService->getAllVehicleLinkedData();
        $allVehicleDivisions = $resultVehicle['vehicleDivisions'];
        $allVehicleRegions = $resultVehicle['vehicleRegions'];
        $companies = Company::all()->pluck('name', 'id');
        $vehicleTypes = VehicleType::all()->pluck('vehicle_type', 'id');

        // $reportColumns = $newReportData->reportColumns->pluck('report_dataset_id');
        // $dataSet = $customReportRepository->getReportDataSetColumns($newReportData['id'], $reportColumns);
        // $columns = $columnSet = $dataSet->pluck('field_name')->toArray();
        // $labelArray = $dataSet->pluck('title')->toArray();

        $labelArray = $reportDownload->reportDataset->pluck('title')->toArray();

        // echo "<pre>"; print_r($labelArray);  echo "</pre>";

        if($slug == 'standard_last_login_report') {
            // $reportData = $customReportRepository->lastLoginDetails($data);
            $localpath = $reportService->downloadLastLogin($data, $reportDownload, $sheetTitle, $newReportData, $oldReportData);
            $this->s3UpdateExcel($localpath, $reportDownload); return;

        } elseif($slug == 'standard_fleet_cost_report') {
            $localpath = $reportService->downloadFleetCostReport($data, $reportDownload, $sheetTitle, $newReportData, $oldReportData);
            $this->s3UpdateExcel($localpath, $reportDownload); return;
        } elseif($slug == 'standard_activity_report') {
            $reportData = $customReportRepository->activityReportData($data);
            $response = $reportDownload->reportDataset->pluck('field_name')->toArray();
        } elseif($slug == 'standard_vor_report') {
            // $reportData = $customReportRepository->vorReportDetails($data);

            $localpath = $reportService->downloadReport('d', "curr", $data, $reportDownload, $sheetTitle, $newReportData, $oldReportData);
            $this->s3UpdateExcel($localpath, $reportDownload); return;

        } elseif($slug == 'standard_vor_defect_report') {
            $localpath = $reportService->downloadReport('b', "curr", $data, $reportDownload, $sheetTitle, $newReportData, $oldReportData);
            $this->s3UpdateExcel($localpath, $reportDownload); return;
        } elseif($slug == 'standard_defect_report') {
            $reportData = $customReportRepository->defectReportDetails($data, $reportDownload);
        } elseif($slug == 'standard_driving_events_report') {
            // $incidents = config('config-variables.telematics_incidents');
            // $ns = array_keys($incidents);
            // $reportData = $customReportRepository->drivingEventData($data, $ns);
            $localpath = $reportService->downloadDrivingEvents($data, $reportDownload, $sheetTitle, $newReportData, $oldReportData);
            $this->s3UpdateExcel($localpath, $reportDownload); return;

        } elseif($slug == 'standard_speeding_report') {
            // $incidents = config('config-variables.telematics_incidents');
            // $ns = 'tm8.dfb2.spdinc';
            // $reportData = $customReportRepository->speedingData($data, $ns);

            $localpath = $reportService->downloadSpeedingReport($data, $reportDownload, $sheetTitle, $newReportData, $oldReportData);
            $this->s3UpdateExcel($localpath, $reportDownload); return;

        } elseif($slug == 'standard_journey_report') {
            // $reportData = $customReportRepository->getJourneyDetails($data);

            $localpath = $reportService->downloadJourneyReport($data, $reportDownload, $sheetTitle, $newReportData, $oldReportData);
            $this->s3UpdateExcel($localpath, $reportDownload); return;

        } elseif($slug == 'standard_fuel_usage_and_emission_report') {
            // $reportData = $customReportRepository->getFuelUsageAndEmissionDetails($data);
            $localpath = $reportService->downloadFuelUsageAndEmissionReport($data, $reportDownload, $sheetTitle, $newReportData, $oldReportData);
            $this->s3UpdateExcel($localpath, $reportDownload); return;
        } elseif($slug == 'standard_driver_behaviour_report') {
            $localpath = $reportService->downloadDriverBehaviorReport($data, $reportDownload, $sheetTitle, $newReportData, $oldReportData);
            $this->s3UpdateExcel($localpath, $reportDownload); return;
        } elseif($slug == 'standard_vehicle_behaviour_report') {
            $localpath = $reportService->downloadVehicleBehaviorReport($data, $reportDownload, $sheetTitle, $newReportData, $oldReportData);
            $this->s3UpdateExcel($localpath, $reportDownload); return;
        } elseif($slug == 'standard_vehicle_profile_report') {
            $reportData = $customReportRepository->vehicleProfileData($data, $reportDownload);
            $sortBy = $reportService->setOrderBy('standard_vehicle_profile_report');
            $reportData = $reportData->orderBy($sortBy);
        } elseif($slug == 'standard_vehicle_incident_report') {
            $incidentTypes = array_keys(config('config-variables.telematics_incidents'));
            $reportData = $customReportRepository->vehicleAndUserIncidentData($data, $incidentTypes, 'vehicle', $reportDownload);
            $sortBy = $reportService->setOrderBy('standard_vehicle_incident_report');
            $reportData = $reportData->orderBy($sortBy);
        } elseif($slug == 'standard_vehicle_defects_report') {
            $reportData = $customReportRepository->vehicleAndUserDefectData($data, 'vehicle', $reportDownload);
            $sortBy = $reportService->setOrderBy('standard_vehicle_defects_report');
            $reportData = $reportData->orderBy($sortBy);
        } elseif($slug == 'standard_vehicle_checks_report') {
            $reportData = $customReportRepository->vehicleAndUserCheckData($data, 'vehicle', $reportDownload);
            $sortBy = $reportService->setOrderBy('standard_vehicle_checks_report');
            $reportData = $reportData->orderBy($sortBy);
        } elseif($slug == 'standard_vehicle_planning_report') {
            $sortBy = $reportService->setOrderBy('standard_vehicle_planning_report');
            $reportData = $customReportRepository->vehiclePlanningData($data, $reportDownload, $sortBy);
        } elseif($slug == 'standard_user_details_report') {
            // $reportData = $customReportRepository->userDetailsReport($data);

            $localpath = $reportService->downloadUserDetailsReport($data, $reportDownload, $sheetTitle, $newReportData, $oldReportData);
            $this->s3UpdateExcel($localpath, $reportDownload); return;

        } elseif($slug == 'standard_user_incident_report') {
            $incidentTypes = array_keys(config('config-variables.telematics_incidents'));
            $reportData = $customReportRepository->vehicleAndUserIncidentData($data, $incidentTypes, 'user', $reportDownload);
            $sortBy = $reportService->setOrderBy('standard_user_incident_report');
            $reportData = $reportData->orderBy($sortBy);
        } elseif($slug == 'standard_user_defects_report') {
            $reportData = $customReportRepository->vehicleAndUserDefectData($data, 'user', $reportDownload);
            $sortBy = $reportService->setOrderBy('standard_user_defects_report');
            $reportData = $reportData->orderBy($sortBy);
        } elseif($slug == 'standard_user_checks_report') {
            $reportData = $customReportRepository->vehicleAndUserCheckData($data, 'user', $reportDownload);
            $sortBy = $reportService->setOrderBy('standard_user_checks_report');
            $reportData = $reportData->orderBy($sortBy);
        } elseif($slug == 'standard_user_journey_report') {
            // $reportData = $customReportRepository->vehicleAndUserJourneyData($data, 'user', $reportDownload);
            $localpath = $reportService->downloadUserJourneyReport($data, $reportDownload, $sheetTitle, $newReportData, $oldReportData);
            $this->s3UpdateExcel($localpath, $reportDownload); return;
        } elseif($slug == 'standard_vehicle_journey_report') {
            // $reportData = $customReportRepository->vehicleAndUserJourneyData($data, 'vehicle', $reportDownload);
            $localpath = $reportService->downloadVehicleJourneyReport($data, $reportDownload, $sheetTitle, $newReportData, $oldReportData);
            $this->s3UpdateExcel($localpath, $reportDownload); return;
        } elseif($slug == 'standard_weekly_maintanance_report') {
            $localpath = $reportService->downloadVehicleWeeklyMaintananceReport($data, $reportDownload, $sheetTitle, $newReportData, $oldReportData);
            $this->s3UpdateExcel($localpath, $reportDownload); return;
        } elseif($slug == 'standard_vehicle_location_report') {
            $localpath = $reportService->downloadVehicleLocationReport($data, $reportDownload, $sheetTitle, $newReportData, $oldReportData);
            $this->s3UpdateExcel($localpath, $reportDownload); return;
        } elseif($slug == 'standard_pmi_performance_report') {
            $localpath = $reportService->downloadPMIPerformanceReport($data, $reportDownload, $sheetTitle, $newReportData, $oldReportData);
            $this->s3UpdateExcel($localpath, $reportDownload); return;
        }

        if(isset($data['accessible_regions'])) {
            if($slug == 'standard_activity_report') {
                $reportData = $reportData->where(function ($query) use ($data) {
                            $query->whereNull('users.user_region_id');
                            $query->orWhereIn('users.user_region_id', $data['accessible_regions']);
                        });

            }

        }

        if($slug != 'standard_vehicle_planning_report') {
            $reportData = $reportData->get();
        }

        foreach ($reportData as $key => $report) {
            if($slug == 'standard_activity_report') {
                $activityData = new stdClass;
                foreach($response as $value) {
                    if($value == 'vehicle_take_out') {
                        $activityData->vehicle_take_out = $report->totalVehicleCheck;
                    } else if($value == 'vehicle_return') {
                        $activityData->vehicle_return = $report->totalReturnCheck;
                    } else if($value == 'users.first_name') {
                        $activityData->first_name = $report->first_name;
                    } else if($value == 'users.last_name') {
                        $activityData->last_name = $report->last_name;
                    } else if($value == 'users.email') {
                        $activityData->email = $report->email;
                    } else if($value == 'users.user_region_id') {
                        $activityData->user_region_id = $report->region;
                    }
                }
                $reportData[$key] = $activityData;
            }

            if($slug == 'standard_last_login_report') {
                $roles = $report->roles()->get()->pluck('id')->toArray();
                $report->roles = "";
                if (in_array('1', $roles)) {
                    $report->roles = "Super admin";
                }
                if (in_array('14', $roles)) {
                    $report->roles = "report information only";
                }
                if (in_array('8', $roles)) {
                    $report->roles = "App access only";
                }
                if ($report->isHavingBespokeAccess()) {
                    $report->roles = "Super admin";
                }
                if($report->last_login == '0000-00-00 00:00:00' || empty($report->last_login)) {
                    $report->last_login = 'No login data recorded';
                }
                $report->is_disabled ? "Yes" : "No";
            }

            if($slug == 'standard_driving_events_report') {
                $report->ns = isset($incidents[$report->ns]) ? $incidents[$report->ns] : $report->ns;
            }

            if($slug == 'standard_speeding_report') {
                $report->speed = $reportService->mpsToMph($report->speed);
                $report->street_speed = number_format(round((float)$reportService->mpsToMph($report->street_speed),0,PHP_ROUND_HALF_UP),2);
                $report->ns = isset($incidents[$report->ns]) ? $incidents[$report->ns] : $report->ns;
            }

            if($slug == 'standard_journey_report') {
                $locationStart = $this->fetchLocation($report);

                $report->engine_duration = readableTimeFomatForReports($report->engine_duration);
                $report->gps_distance = number_format($report->gps_distance * 0.00062137, 2, '.', '');
                $report->start_location = $locationStart;
                $report->mpg_actual = $this->calculationActualMPG($report->fuel, $report->gps_distance);
                $report->mpg_expected = $this->calculationExpectedMPG($report->vehiclefuelsum, $report->vehicledistancesum);
                unset($report['vehiclefuelsum']);
                unset($report['vehicledistancesum']);
            }

            if($slug == 'standard_fuel_usage_and_emission_report') {
                $report->engine_duration = readableTimeFomatForReports($report->engine_duration);
                $report->gps_distance = number_format($report->gps_distance * 0.00062137, 2, '.', '');
                $report->actual_driving_time = readableTimeFomatForReports($report->engine_duration - $report->gps_idle_duration);
                $report->gps_idle_duration = readableTimeFomatForReports($report->gps_idle_duration);
                $report->mpg_actual = $this->calculationActualMPG($report->fuel, $report->gps_distance);
                $report->mpg_expected = $this->calculationExpectedMPG($report->vehiclefuelsum, $report->vehicledistancesum);
                unset($report['vehiclefuelsum']);
                unset($report['vehicledistancesum']);
            }

            if($slug == 'standard_vehicle_incident_report' || $slug == 'standard_user_incident_report') {
                $report->street_speed = $report->incident_type == 'Speeding' ? number_format(round((float)$reportService->mpsToMph($report->street_speed),0,PHP_ROUND_HALF_UP),2) : 'NA';
                $report->speed = $report->incident_type == 'Speeding' ? $reportService->mpsToMph($report->speed) : 'NA';
            }

            // if($slug == 'standard_user_journey_report' || $slug == 'standard_vehicle_journey_report') {
            //     $report->engine_duration = readableTimeFomatForReports($report->engine_duration);
            //     $report->gps_distance = number_format($report->gps_distance * 0.00062137, 2, '.', '');
            // }

        }

        $dataArray = json_decode(json_encode($reportData), true);

        $excelCreateObj = \Excel::create($sheetTitle, function($excel) use($labelArray, $dataArray, $otherParams) {
            $excel->setTitle($otherParams['sheetTitle_value']);
            $excel->sheet($otherParams['sheetName'], function($sheet) use($labelArray, $dataArray, $otherParams) {
                $sheet->row(2, array($otherParams['sheetTitle_lable'], $otherParams['sheetTitle_value']));
                $sheet->row(2, function($row){
                    $row->setFontFamily('Arial');
                    $row->setFontSize(10);
                });
                $sheet->mergeCells('B2:D2');
                $sheet->row(3, array($otherParams['sheetSubTitle_lable_first'], $otherParams['sheetSubTitle_value_first']));
                $sheet->row(3, function($row){
                    $row->setFontFamily('Arial');
                    $row->setFontSize(10);
                });
                $sheet->mergeCells('B3:D3');
                $sheet->row(4, array($otherParams['sheetSubTitle_lable_second'], $otherParams['sheetSubTitle_value_second']));
                $sheet->row(4, function($row){
                    $row->setFontFamily('Arial');
                    $row->setFontSize(10);
                });
                $sheet->mergeCells('B4:D4');
                $sheet->cell('A2:A4', function($cells){
                    $cells->setFontWeight('bold');
                });

                $sheet->row(6, $labelArray);
                $sheet->row(6, function($row){
                    $row->setFontWeight('bold');
                    $row->setFontFamily('Arial');
                    $row->setFontSize(10);
                });

                $row_no = 7;
                foreach ($dataArray as $data) {
                    $sheet->row($row_no, $data);
                    $row_no++;
                }

                if($otherParams['boldLastRow']){
                    $sheet->row(($row_no-1), function($row){
                        $row->setFontWeight('bold');
                    });
                }
            });
        });

        $excelCreateObj->store('xlsx');
        $localpath = storage_path('exports').'/'.$sheetTitle.'.xlsx';
        $this->s3UpdateExcel($localpath, $reportDownload);
    }

    public function s3UpdateExcel($localpath, $reportDownload)
    {
        sleep(5);
        $reportDownload->addMedia($localpath)->toCollectionOnDisk('custom_reports', 'S3_uploads');
        $s3Url = $reportDownload->getMedia()->first()->getUrl();
        Log::info('S3 file name: '.$s3Url);
        $reportDownload->filename = $s3Url;
        // $reportDownload->filename = $localpath;
        $reportDownload->save();
    }
    private function fetchLocation($journey)
    {
        $locationStart = "";
        $startLocation = $journey->details()->orderBy('id')->first();
        if(isset($startLocation)) {
            $locationStart = $startLocation->street." ".$startLocation->town." ".$startLocation->post_code;
        }

        return $locationStart;
    }

    private function calculationActualMPG($fuel, $gps_distance)
    {
        $gallons = floatval($fuel * 0.264172);
        $miles = floatval($gps_distance * 0.00062137);
        $mpg = 0;
        if ($gallons && $gallons != null && $gallons != 0) {
            $mpg = round(floatval($miles / $gallons), 2);
        }

        return $mpg;
    }

    private function calculationExpectedMPG($vehiclefuelsum, $vehicledistancesum)
    {
        $gallonsExpected = round(floatval($vehiclefuelsum * 0.264172), 2);
        $milesExpected = round(floatval($vehicledistancesum * 0.00062137), 2);
        $mpgExpectedValue = 0;
        if (
            $vehiclefuelsum &&
            $vehiclefuelsum != null &&
            $vehiclefuelsum != 0
        ) {
            $mpgExpectedValue = round(floatval($milesExpected / $gallonsExpected), 2);
        }
        return $mpgExpectedValue;
    }
}
