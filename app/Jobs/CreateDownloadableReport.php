<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

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
use App\Services\UserService;
use App\Services\CustomReportService;
use App\Services\Report as ReportService;
use App\Repositories\CustomReportRepository;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Auth;
use Mail;
use Log;
use DB;

class CreateDownloadableReport extends Job implements SelfHandling, ShouldQueue
{
    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $data;
    protected $reportDownloadId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $reportDownloadId)
    {
        $this->data = $data;
        $this->reportDownloadId = $reportDownloadId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(UserService $userService, CustomReportRepository $customReportRepository, ReportService $reportService)
    {
        Log::info('CreateDownloadableReport Job Starts');
        
        $data = $this->data;
        
        $report = Report::find($data['report_id']);
        $category = $report->category;

        $reportDownload = ReportDownload::find($this->reportDownloadId);
        
        $sheetTitle = str_slug($report->name."-".time());
        
        Log::info('Is custom report?: '.$report->is_custom_report);
        
        if(!$report->is_custom_report) {

            Log::info('Report slug: '.$report->slug);
            if($report->slug == 'standard_last_login_report') {
                $localpath = $reportService->downloadLastLogin($data);
            } else if($report->slug == 'standard_fleet_cost_report') {
                $localpath = $reportService->downloadFleetCostReport($data);
            } else if($report->slug == 'standard_p11d_benefits_report') {
                $localpath = $reportService->downloadReport('p11dreport', "2021-2022", $data);
            } else if($report->slug == 'standard_activity_report') {
                $localpath = $reportService->downloadReport('j', "curr", $data);
            } else if($report->slug == 'standard_vor_report') {
                $localpath = $reportService->downloadReport('d', "curr", $data);
            } else if($report->slug == 'standard_vor_defect_report') {
                $localpath = $reportService->downloadReport('b', "curr", $data);
            } else if($report->slug == 'standard_defect_report') {
                $localpath = $reportService->downloadReport('a', "curr", $data);
            } else if($report->slug == 'standard_driving_events_report') {
                $localpath = $reportService->downloadDrivingEvents($data);
            } else if($report->slug == 'standard_speeding_report') {
                $localpath = $reportService->downloadSpeedingReport($data);
            } else if($report->slug == 'standard_journey_report') {
                $localpath = $reportService->downloadJourneyReport($data);
            } else if($report->slug == 'standard_fuel_usage_and_emission_report') {
                $localpath = $reportService->downloadFuelUsageAndEmissionReport($data);
            } else if($report->slug == 'standard_driver_behaviour_report') {
                $localpath = $reportService->downloadDriverBehaviorReport($data);
            } else if($report->slug == 'standard_vehicle_behaviour_report') {
                $localpath = $reportService->downloadVehicleBehaviorReport($data);
            } else if($report->slug == 'standard_vehicle_profile_report') {
                $localpath = $reportService->downloadVehicleProfileReport($data);
            } else if($report->slug == 'standard_vehicle_incident_report') {
                $localpath = $reportService->downloadVehicleIncidentReport($data);
            } else if($report->slug == 'standard_vehicle_defects_report') {
                $localpath = $reportService->downloadVehicleDefectReport($data);
            } else if($report->slug == 'standard_vehicle_checks_report') {
                $localpath = $reportService->downloadVehicleCheckReport($data);
            } else if($report->slug == 'standard_vehicle_planning_report') {
                $localpath = $reportService->downloadVehiclePlanningReport($data);
            } else if($report->slug == 'standard_user_details_report') {
                $localpath = $reportService->downloadUserDetailsReport($data);
            } else if($report->slug == 'standard_user_incident_report') {
                $localpath = $reportService->downloadUserIncidentReport($data);
            } else if($report->slug == 'standard_user_defects_report') {
                $localpath = $reportService->downloadUserDefectReport($data);
            } else if($report->slug == 'standard_user_checks_report') {
                $localpath = $reportService->downloadUserCheckReport($data);
            } else if($report->slug == 'standard_user_journey_report') {
                $localpath = $reportService->downloadUserJourneyReport($data);
            } else if($report->slug == 'standard_vehicle_journey_report') {
                $localpath = $reportService->downloadVehicleJourneyReport($data);
            } else if($report->slug == 'standard_weekly_maintanance_report') {
                $localpath = $reportService->downloadVehicleWeeklyMaintananceReport($data);
            } else if($report->slug == 'standard_vehicle_location_report') {
                $localpath = $reportService->downloadVehicleLocationReport($data);
            } else if($report->slug == 'standard_pmi_performance_report') {
                $localpath = $reportService->downloadPMIPerformanceReport($data);
            }

        } else {

                $allUserDivisions = UserDivision::all()->pluck('name', 'id');
                $allUserRegions = UserRegion::all()->pluck('name', 'id');

                $resultVehicle = $userService->getAllVehicleLinkedData();
                $allVehicleDivisions = $resultVehicle['vehicleDivisions'];
                $allVehicleRegions = $resultVehicle['vehicleRegions'];
                $companies = Company::all()->pluck('name', 'id');
                $vehicleTypes = VehicleType::all()->pluck('vehicle_type', 'id');

                $reportColumns = $report->reportColumns->pluck('report_dataset_id');
                $dataSet = $customReportRepository->getReportDataSetColumns($data['report_id'], $reportColumns);
                $columns = $columnSet = $dataSet->pluck('field_name')->toArray();
                $labelArray = $dataSet->pluck('title')->toArray();

                $users = User::join('vehicles', 'vehicles.nominated_driver', '=', 'users.id')
                                ->join('vehicle_types', 'vehicle_types.id', '=', 'vehicles.vehicle_type_id');

                if($category->slug == 'driver_behaviour') {

                    $users = $users->join(DB::raw('(select user_id, vehicle_id, AVG(safety_score) as driver_safety_score, AVG(efficiency_score) as driver_efficiency_score, ((AVG(safety_score)+AVG(efficiency_score))/2) AS driver_overall_score from telematics_journeys where (CONVERT(start_time, DATE) >= '.$data['date_from'].' AND CONVERT(start_time, DATE) <= '.$data['date_to'].') OR (CONVERT(end_time, DATE) >= '.$data['date_from'].' AND CONVERT(end_time, DATE) <= '.$data['date_to'].') GROUP BY user_id) as driver_avg_scores'), function($query) {

                        $query->on('users.id', '=', 'driver_avg_scores.user_id');
                        $query->on('vehicles.id', '=', 'driver_avg_scores.vehicle_id');

                    });

                } else if($category->slug == 'user_checks' || $category->slug == 'vehicle_checks') {

                    $users = Check::join('users', 'checks.created_by', '=', 'users.id')
                                    ->join('vehicles', 'checks.vehicle_id', '=', 'vehicles.id')
                                    ->join('vehicle_types', 'vehicle_types.id', '=', 'vehicles.vehicle_type_id')
                                    ->whereDate('checks.report_datetime', '>=', $data['date_from'])
                                    ->whereDate('checks.report_datetime', '<=', $data['date_to']);

                } else if($category->slug == 'user_defects' || $category->slug == 'vehicle_defects') {

                    $users = Defect::join('users', 'defects.created_by', '=', 'users.id')
                                    ->join('defect_master', 'defects.defect_master_id', '=', 'defect_master.id')
                                    ->join('vehicles', 'defects.vehicle_id', '=', 'vehicles.id')
                                    ->join('vehicle_types', 'vehicle_types.id', '=', 'vehicles.vehicle_type_id')
                                    ->whereDate('defects.report_datetime', '>=', $data['date_from'])
                                    ->whereDate('defects.report_datetime', '<=', $data['date_to']);

                } else if($category->slug == 'user_details') {

                    $users = $users->whereDate('users.created_at', '>=', $data['date_from'])
                                    ->whereDate('users.created_at', '<=', $data['date_to']);

                } else if($category->slug == 'user_journey' || $category->slug == 'vehicle_journey') {

                    $users = TelematicsJourneys::join('users', 'telematics_journeys.user_id', '=', 'users.id')
                                                ->join('vehicles', 'telematics_journeys.vehicle_id', '=', 'vehicles.id')
                                                ->join('vehicle_types', 'vehicle_types.id', '=', 'vehicles.vehicle_type_id')
                                                ->where(function($query) use($data) {
                                                    $query->where(function($query1) use($data){
                                                            $query1->whereDate('start_time', '>=', $data['date_from'])
                                                                ->whereDate('start_time', '<=', $data['date_to']);
                                                        })->orWhere(function($query2) use($data){
                                                            $query2->whereDate('end_time', '>=', $data['date_from'])
                                                                ->whereDate('end_time', '<=', $data['date_to']);
                                                        });
                                                    });

                    unset($columnSet[array_search('total_journeys', $columnSet)]);

                } else if($category->slug == 'vehicle_behaviour') {

                    $users = $users->join(DB::raw('(select user_id, vehicle_id, AVG(safety_score) as vehicle_safety_score, AVG(efficiency_score) as vehicle_efficiency_score, ((AVG(safety_score)+AVG(efficiency_score))/2) AS overall_score from telematics_journeys where (CONVERT(start_time, DATE) >= '.$data['date_from'].' AND CONVERT(start_time, DATE) <= '.$data['date_to'].') OR (CONVERT(end_time, DATE) >= '.$data['date_from'].' AND CONVERT(end_time, DATE) <= '.$data['date_to'].') GROUP BY user_id) as vehicle_avg_scores'), function($query) {

                            $query->on('users.id', '=', 'vehicle_avg_scores.user_id');
                            $query->on('vehicles.id', '=', 'vehicle_avg_scores.vehicle_id');

                        });


                } else if($category->slug == 'user_incident' || $category->slug == 'vehicle_incident') {

                    $users = $users->join('telematics_journeys', function($query) {
                                        $query->on('users.id', '=', 'telematics_journeys.user_id');
                                        $query->on('vehicles.id', '=', 'telematics_journeys.vehicle_id');
                                    })
                                    ->join(DB::raw("(SELECT telematics_journey_id, CONVERT(time, DATE) as time,
                                            SUM(CASE WHEN ns = 'tm8.dfb2.acc.l' THEN 1 ELSE 0 END) AS acceleration_score,
                                            SUM(CASE WHEN ns = 'tm8.dfb2.dec.l' THEN 1 ELSE 0 END) AS braking_score,
                                            SUM(CASE WHEN ns = 'tm8.dfb2.cnrl.l' OR ns = 'tm8.dfb2.cnrr.l' THEN 1 ELSE 0 END) AS cornering_score,
                                            SUM(CASE WHEN ns = 'tm8.gps.idle.start' THEN 1 ELSE 0 END) AS idle_score,
                                            SUM(CASE WHEN ns = 'tm8.dfb2.rpm' THEN 1 ELSE 0 END) AS rpm_score,
                                            SUM(CASE WHEN ns = 'tm8.dfb2.spdinc' THEN 1 ELSE 0 END) AS speeding_score,
                                            1 as total_incidents
                                            FROM telematics_journey_details
                                            where CONVERT(time, date) >= '".$data['date_from']."' AND 
                                            CONVERT(time, date) <= '".$data['date_to']."'
                                            GROUP BY telematics_journey_id, CONVERT(time, DATE)) as telematics_journey_details"), 'telematics_journeys.id', '=', 'telematics_journey_details.telematics_journey_id');

                } else if($category->slug == 'vehicle_profile') {

                    $users = $users->whereDate('vehicles.created_at', '>=', $data['date_from'])
                                    ->whereDate('vehicles.created_at', '<=', $data['date_to']);

                } else if($category->slug == 'vehicle_planning') {

                    $users = $users->where(function($query) use($data) {

                                $query->where(function($query1) use($data){

                                    $query1->where('vehicles.dt_annual_service_inspection', '>=', $data['date_from'])
                                            ->where('vehicles.dt_annual_service_inspection', '<=', $data['date_to']);

                                })->orWhere(function($query2) use($data){

                                    $query2->where('vehicles.next_compressor_service', '>=', $data['date_from'])
                                            ->where('vehicles.next_compressor_service', '<=', $data['date_to']);

                                })->orWhere(function($query3) use($data){

                                    $query3->where('vehicles.next_invertor_service_date', '>=', $data['date_from'])
                                            ->where('vehicles.next_invertor_service_date', '<=', $data['date_to']);

                                })->orWhere(function($query4) use($data){

                                    $query4->where('vehicles.dt_loler_test_due', '>=', $data['date_from'])
                                            ->where('vehicles.dt_loler_test_due', '<=', $data['date_to']);

                                })->orWhere(function($query5) use($data){

                                    $query5->where('vehicles.dt_mot_expiry', '>=', $data['date_from'])
                                            ->where('vehicles.dt_mot_expiry', '<=', $data['date_to']);

                                })->orWhere(function($query6) use($data){

                                    $query6->where(function($query) use($data) {
                                        $query->where(function($pmiquery1) use($data){
                                                $pmiquery1->whereDate('vehicles.first_pmi_date', '>=', $data['date_from'])
                                                    ->whereDate('vehicles.first_pmi_date', '<=', $data['date_to']);
                                            })->orWhere(function($pmiquery2) use($data){
                                                $pmiquery2->whereDate('vehicles.next_pmi_date', '>=', $data['date_from'])
                                                    ->whereDate('vehicles.next_pmi_date', '<=', $data['date_to']);
                                            });
                                    });

                                })->orWhere(function($query7) use($data){

                                    $query7->where('vehicles.next_pto_service_date', '>=', $data['date_from'])
                                            ->where('vehicles.next_pto_service_date', '<=', $data['date_to']);

                                })->orWhere(function($query8) use($data){

                                    $query8->where('vehicles.dt_next_service_inspection', '>=', $data['date_from'])
                                            ->where('vehicles.dt_next_service_inspection', '<=', $data['date_to']);

                                })->orWhere(function($query9) use($data){

                                    $query9->where('vehicles.dt_tacograch_calibration_due', '>=', $data['date_from'])
                                            ->where('vehicles.dt_tacograch_calibration_due', '<=', $data['date_to']);

                                })->orWhere(function($query10) use($data){

                                    $query10->where('vehicles.dt_tax_expiry', '>=', $data['date_from'])
                                            ->where('dt_tax_expiry', '<=', $data['date_to']);

                                })->orWhere(function($query11) use($data){
                                    //Maintanance
                                    $query11->where('vehicles.dt_repair_expiry', '>=', $data['date_from'])
                                            ->where('dt_repair_expiry', '<=', $data['date_to']);

                                });
                            });

                }

                $users = $users->whereIn('vehicles.vehicle_region_id', $data['accessible_regions']);
                $users = $users->get($columnSet);

                foreach($users as $key => $user) {
                    if(in_array($category->slug, ['user_incident', 'vehicle_incident']) && in_array('total_incidents', $columns)) {
                        $user->total_incidents = $user->acceleration_score + $user->braking_score + $user->cornering_score + $user->idle_score + $user->rpm_score + $user->speeding_score;
                    }

                    if(in_array('total_journeys', $columns)) {
                        $user->total_journeys = 1;
                    }

                    if(isset($user->user_division_id)) {
                        $user->user_division_id = $allUserDivisions[$user->user_division_id];
                    }

                    if(isset($user->user_region_id)) {
                        $user->user_region_id = $allUserRegions[$user->user_region_id];
                    }

                    if(isset($user->vehicle_division_id)) {
                        $user->vehicle_division_id = $allVehicleDivisions[$user->vehicle_division_id];
                    }
                    
                    if(isset($user->vehicle_region_id)) {
                        $user->vehicle_region_id = $allVehicleRegions[$user->vehicle_region_id];
                    }
                    
                    if(isset($user->company_id)) {
                        $user->company_id = $companies[$user->company_id];
                    }

                    if(isset($user->vehicle_type_id)) {
                        $user->vehicle_type_id = $vehicleTypes[$user->vehicle_type_id];
                    }
                }

                $dataArray = json_decode(json_encode($users), true);

                $excelCreateObj = \Excel::create($sheetTitle, function($excel) use($labelArray, $dataArray, $sheetTitle) {
                    $excel->setTitle($sheetTitle);
                    $excel->sheet($sheetTitle, function($sheet) use($labelArray, $dataArray, $sheetTitle) {
                        $sheet->row(1, $labelArray);
                        $row_no = 2;
                        foreach ($dataArray as $data) {
                            $sheet->row($row_no, $data);
                            $row_no++;
                        }
                    });
                });

                $excelCreateObj->store('xlsx');
                $localpath = storage_path('exports').'/'.$sheetTitle.'.xlsx';
        }

        sleep(5);
        $reportDownload->addMedia($localpath)->toCollectionOnDisk('custom_reports', 'S3_uploads');
        $s3Url = $reportDownload->getMedia()->first()->getUrl();
        Log::info('S3 file name: '.$s3Url);
        $reportDownload->filename = $s3Url;
        $reportDownload->save();

        // Update last generated date for the actual report
        $report->last_downloaded_at = Carbon::now('UTC')->toDateTimeString();
        $report->save();

        $liveReportSlugArray = ['standard_last_login_report', 'standard_user_details_report', 'standard_vehicle_profile_report'];
        if($data['user']->email) {
            Log::info('sending download report notification email');
            $mailData['user_name'] = $data['user']->first_name;
            $mailData['report_name'] = $report->name;
            $mailData['report_date'] = in_array($report->slug, $liveReportSlugArray) ? 'Live report' : $data['date_range'];
            $mailData['link'] = $link = url('reports').'?tab=download';
            Mail::send('emails.report_download', ['data' => $mailData], function ($message) use($data, $report) {
                $message->to($data['user']->email);
                $message->subject('Your report is ready to download - '.$report->name);
            });
        }

        Log::info('CreateDownloadableReport Job Ends');

    }
}
