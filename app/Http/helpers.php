<?php

if (! function_exists('get_brand_setting')) {
    /**
     * Get the specified brand configuration value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     *
     */
    function get_brand_setting($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('config');
        }
        $brand_name = config('branding.name');
        $common_setting = config("branding.common.{$key}", $default);

        return config("branding.{$brand_name}.{$key}", $common_setting);
    }
}


if (! function_exists('is_past_date')) {
    /**
     * Get the specified brand configuration value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     *
     */
    function is_past_date($eventDate)
    {
        $today = new \Carbon\Carbon;
        $eventDateValue = new \Carbon\Carbon($eventDate);

        if($eventDate == null){
            return '';
        }
        if($today->diffInDays($eventDateValue, false) < 0 || $today->diffInDays($eventDateValue, false) < 6) {
            return 'label-text-danger font-weight-700';
        }
        if($today->diffInDays($eventDateValue, false) < 13) {
            return 'label-text-warning font-weight-700';
        }
        if($today->diffInDays($eventDateValue, false) < 29) {
            return 'label-text-success font-weight-700';
        }
    }
}

if (! function_exists('getPresignedUrl')) {
    /**
     * Get the Pre-Signed URL from medial collection .
     *
     * @param  collection  $media
     * @return string
     *
     */
    function getPresignedUrl($media)
    {
        if ($media->disk == 'S3_uploads') {
            $path = $media->collection_name . "/" . $media->created_at->format('Y/m') . "/" . $media->id . "/" . $media->file_name;

            $s3 = \Storage::disk('S3_uploads');
            $client = $s3->getDriver()->getAdapter()->getClient();
            $expiry = "+300 seconds";

            $command = $client->getCommand('GetObject', [
                'Bucket' => env('S3_UPLOADS_BUCKET'),
                'Key' => $path
            ]);
            $request = $client->createPresignedRequest($command, $expiry);

            $url = (string)$request->getUri();
        } else {
            $url = $media->getUrl();
        }

        return $url;
    }
}

if (! function_exists('replaceWithPreSignedUrl')) {
    /**
     * Replace normal URL with PreSignedUrl in Vehicle Check's JSON .
     *
     * @param  json  $json
     * @return string
     *
     */
    function replaceWithPreSignedUrl($type,$json)
    {

        $data = json_decode($json,TRUE);

        if (count($data) > 0) {

            $screens = $data['screens']['screen'];

            if (count($screens) > 0) {
                foreach ($screens as $key => $single) {

                    if ($type == 'Vehicle Check') {

                        if (isset($single['defects']['defect']) && count($single['defects']['defect']) > 0) {

                            $defects = $single['defects']['defect'];

                            foreach ($defects as $key_1 => $value) {


                                if ($value['imageString'] != "" && strpos($value['imageString'], 'http') !== false) {

                                    $images = explode("|", $value['imageString']);
                                    $imagesArray = [];
                                    foreach ($images as $image) {
                                        $url = getPreSingedUrlFromNormalUrl($image);
                                        array_push($imagesArray, $url);
                                    }

                                    $screens[$key]['defects']['defect'][$key_1]['imageString'] = implode("|", $imagesArray);

                                }

                            }
                        } else if (isset($single['defects']) && count($single['defects']) == 0) {
                            $screens[$key]['defects'] = new ArrayObject();
                        }
                    }


                    if ($type != 'Vehicle Check') {
                        $options = $single['options']['optionList'];

                        if (isset($single['defects']) && count($single['defects']) == 0) {
                            $screens[$key]['defects'] = new ArrayObject();
                        }

                        if (count($options) > 0) {
                            foreach ($options as $key_2 => $option) {
                                if (isset($option['defects']['defect']) && count($option['defects']['defect']) > 0) {

                                    foreach ($option['defects']['defect'] as $key_3 => $defect) {

                                        //  print_r($defect);
                                        if ($defect['imageString'] != "" && strpos($defect['imageString'], 'http') !== false) {

                                            $images = explode("|", $defect['imageString']);
                                            $imagesArrayDefect = [];
                                            foreach ($images as $image) {
                                                $url = getPreSingedUrlFromNormalUrl($image);
                                                array_push($imagesArrayDefect, $url);
                                            }

                                            $screens[$key]['options']['optionList'][$key_2]['defects']['defect'][$key_3]['imageString'] = implode("|", $imagesArrayDefect);

                                        }

                                    }
                                }
                            }
                        }
                    }
                }
            }

            $data['screens']['screen'] = $screens;
        }

        return json_encode($data);

    }
}

if (! function_exists('getPreSingedUrlFromNormalUrl')) {
    /**
     * Replace normal URL with PreSignedUrl in Vehicle Check's JSON .
     *
     * @param  url  $url
     * @return string
     *
     */
    function getPreSingedUrlFromNormalUrl($url)
    {

        try {
            $s3 = \Storage::disk('S3_uploads');
            $client = $s3->getDriver()->getAdapter()->getClient();
            $expiry = "+300 seconds";
            $path = explode(env('S3_DOMAIN_NAME') . "/", $url)[1];

            $command = $client->getCommand('GetObject', [
                'Bucket' => env('S3_UPLOADS_BUCKET'),
                'Key' => $path
            ]);
            $request = $client->createPresignedRequest($command, $expiry);

            $preSignedUrl = (string)$request->getUri();

            return $preSignedUrl;
        } catch (\Exception $e) {

            return $url;

        }
    }
}

if (! function_exists('getVehicleCostForDays')) {
    function getVehicleCostForDays($cost,$days,$totalDays) {
        if ($cost == 0) {
            return '0.00';
        }
        if ((float)$totalDays < 1) {
            return '0.00';
        }
        $costForOneDay =  (float) $cost / (float)$totalDays;
        return number_format((float)$costForOneDay * (float)$days, 2, '.', '');
    }
}

if (! function_exists('displayExpiryNextInspectionForDistance')) {
    function displayExpiryNextInspectionForDistance($lastOdometerReading, $nextServiceInspectionDistance) {
        $difference = $nextServiceInspectionDistance - $lastOdometerReading;
        if ($difference <= 1000) {
            return 'label-text-danger font-weight-700';
        } else if ($difference > 1000 && $difference <= 2000) {
            return 'label-text-warning font-weight-700';
        } else if ($difference > 2000 && $difference <= 3000) {
            return 'label-text-success font-weight-700';
        } else {
            return '';
        } 
    }
}

if (! function_exists('replacePreSignedWithNormalUrl')) {
    /**
     * Replace normal URL with PreSignedUrl in Vehicle Check's JSON .
     *
     * @param  json  $json
     * @return string
     *
     */
    function replacePreSignedWithNormalUrl($signedUrl)
    {
        return strpos($signedUrl, "?") ? substr($signedUrl, 0, strpos($signedUrl, "?")) : $signedUrl;        
    }
}

if (! function_exists('toExcel')) {
    function toExcel($excelFileDetail, $sheetArray, $output='xlsx', $download='yes'){
        $fileName=strtolower(str_replace(" ","-",$excelFileDetail['title']))."-".time();
        $excelCreateObj = \Excel::create($fileName, function($excel) use($excelFileDetail, $sheetArray) {
            $excel->setTitle($excelFileDetail['title']);
            foreach ($sheetArray as $sheetDetail) {
                $excel->sheet($sheetDetail['otherParams']['sheetName'], function($sheet) use($sheetDetail) {
                    $sheet->row(1, $sheetDetail['labelArray']);
                    $sheet->row(1, function($row){
                        $row->setBackground("#FFFFFF");
                        $row->setFontColor('#000000');
                        $row->setFontWeight('bold');
                        $row->setFontFamily('Arial');
                        $row->setFontSize(10);
                    });
                    if(isset($sheetDetail['columnFormat']) && count($sheetDetail['columnFormat'])>0){
                        $sheet->setColumnFormat($sheetDetail['columnFormat']);
                    }
                    $rowNo = 2;
                    for($i=0;$i<count($sheetDetail['dataArray']);$i++) {
                        $sheet->row($rowNo, $sheetDetail['dataArray'][$i]);
                        if(isset($sheetDetail['cellBackgroundArray'])) {
                            $column = 'A';
                            for($j=0;$j<count($sheetDetail['dataArray'][$i]);$j++) {
                                $background=$sheetDetail['cellBackgroundArray'][$i][$j];
                                if($background!="") {
                                    $sheet->cell($column.$rowNo, function($cell) use($background) {
                                        $cell->setBackground(strtoupper($background));
                                    });
                                }
                                $sheet->cell($column.$rowNo, function($cell) use($background) {
                                        $cell->setAlignment('center');
                                    });

                                $column++;
                            }
                        }

                        $rowNo++;
                    }
                    $sheet->setAutoFilter();                        
                    if(isset($sheetDetail['otherParams']['freezePane'])) {
                        $sheet->setFreeze($sheetDetail['otherParams']['freezePane']);
                    }
                });
            }
        });
        if($download == 'yes'){
            $excelCreateObj->export($output);
        }else{
            $excelCreateObj->store($output);
        }
        $exportFile=storage_path('exports').'/'.$fileName.'.xlsx';
        return $exportFile;
    }
}

if (! function_exists('secondsToHourMinute')) {
    function secondsToHourMinute($seconds) {
        /* $s = $seconds%60;
        $m = floor(($seconds%3600)/60);
        $h = floor(($seconds%86400)/3600);
        return $h.':'.$m; */
        
        $secs = $seconds % 60;
        $hrs = $seconds / 60;
        $mins = $hrs % 60;

        $hrs = $hrs / 60;
        $hrs = (int)$hrs;
        $mins=(int)$mins;

        if($hrs==null || $hrs==0){
            return '00:'.($mins<10?'0'.$mins:$mins);
        }
        return ($hrs<10?'0'.$hrs:$hrs).":".($mins<10?'0'.$mins:$mins);
    }
}

if (! function_exists('readableTimeFomat')) {
    /**
     * Get the readable time format.
     *
     * @param  string  $timeInSeconds
     * @return mixed
     *
     */
    function readableTimeFomat($timeInSeconds){
        $readableTimeFomat = $timeInSeconds;
        $hours = floor($readableTimeFomat / 3600);
        $minutes = floor(($readableTimeFomat / 60) % 60);
        $seconds = $readableTimeFomat % 60;

        if($hours >= 1) {
            $readableTimeFomat = "$hours hr $minutes min";
        } else {
            $readableTimeFomat = "$minutes min $seconds sec";
        }
        return $readableTimeFomat;
    }
}

if (! function_exists('readableTimeFomatForReports')) {
    /**
     * Get the readable time format.
     *
     * @param  string  $timeInSeconds
     * @return mixed
     *
     */
    function readableTimeFomatForReports($timeInSeconds){
        $readableTimeFomat = $timeInSeconds;
        $hours = floor($readableTimeFomat / 3600);
        $minutes = floor(($readableTimeFomat / 60) % 60);
        $seconds = $readableTimeFomat % 60;

        $minutes = $minutes < 10 ? '0'.$minutes : $minutes;
        $seconds = $seconds < 10 ? '0'.$seconds : $seconds;
        if($hours >= 1) {
            $hours = $hours < 10 ? '0'.$hours : $hours;
            $readableTimeFomat = "$hours:$minutes:$seconds";
        } else {
            $readableTimeFomat = "00:$minutes:$seconds";
        }
        return $readableTimeFomat;
    }
}

if (! function_exists('checkStreetViewAvailability')) {
    /**
     * Check street view available or not.
     *
     * @param  string  $lat
     * @param  string  $lon
     * @param  string  $size
     * @return mixed
     *
     */
    function checkStreetViewAvailability($lat, $lon, $size)
    {
        $location = $lat.",".$lon;
        $streetViewUrl = 'https://maps.googleapis.com/maps/api/streetview/metadata?location='.$location.'&key='.env('GOOGLE_MAP_KEY');

        \Log::info($streetViewUrl);

        $streetView = json_decode(file_get_contents($streetViewUrl), true);

        \Log::info($streetView);

        if(strtoupper($streetView['status']) == 'NOT_FOUND') {
            return '/img/no_street_view.png';
        } else {
            return 'https://maps.googleapis.com/maps/api/streetview?location='.$location.'&key='.env('GOOGLE_MAP_KEY').'&size='.$size;
        }
    }
}


if (! function_exists('getStreetSpeed')) {
    /**
     * Get the readable time format.
     *
     * @param  string  $timeInSeconds
     * @return mixed
     *
     */
    function getStreetSpeed($maxSpeed){
        if($maxSpeed >= 10) {
            $tmp = $maxSpeed % 10;
            $maxSpeed = (int)($maxSpeed / 10) * 10;
            if($tmp >= 5) {
                $maxSpeed = ((int)($maxSpeed / 10) + 1) * 10;
            }
        } else if($maxSpeed < 10) {
            $maxSpeed = 0;
        }
        return number_format($maxSpeed, 2);
    }
}


if (! function_exists('setMpsToMph')) {
    function setMpsToMph($metersPerSecond){
        $metersPerSecond = is_numeric($metersPerSecond) ? $metersPerSecond : 0;
        // if(env('TELEMATICS_PROVIDER') != 'webfleet') {
            $milesPerHour = $metersPerSecond * 2.236936;
        // } else {
        //     $milesPerHour = $metersPerSecond * 0.621371;
        // }
        return number_format($milesPerHour,2);
        }
}

if (! function_exists('valueFormatter')) {
    function valueFormatter($value=0,$currency=true,$decimal=2){
        if(is_numeric($value)) {
            $v = number_format($value,$decimal,'.',',');
            if($currency == true) {
                $newValue='&pound;'.$v;
            } else {
                $newValue=$v;
            }
            return $newValue;
        }

        return '&pound;'.$value;
    }
}

if (! function_exists('setDataCoumns')) {
    /**
     * Set data columns for custom reports.
     *
     * @param  array  $response
     * @return mixed
     *
     */
    function setDataCoumns($response)
    {
        foreach($response as $key => $value) {
            if($value == 'vehicles.vehicle_division_id') {
                $response[$key] = 'vehicle_divisions.name as vehicle_division_id';
            } else if($value == 'vehicles.vehicle_region_id') {
                $response[$key] = 'vehicle_regions.name as vehicle_region_id';
            } else if($value == 'users.user_region_id') {
                $response[$key] = 'user_regions.name as user_region_id';
            } else if($value == 'hgv_non_hgv') {
                $response[$key] = 'CASE WHEN vehicle_types.manufacturer = "hgv" THEN "HGV" ELSE "Non-HGV" END as hgv_non_hgv';
            } else if($value == 'vehicles.vehicle_type_id') {
                $response[$key] = 'vehicle_types.vehicle_type';
            } else if($value == 'vehicle_types.vehicle_category') {
                $response[$key] = 'CASE WHEN vehicle_types.vehicle_category = "hgv" THEN "HGV" ELSE "Non-HGV" END as vehicle_category';
            } else if($value == 'vehicles.vehicle_location_id') {
                $response[$key] = 'vehicle_locations.name as vehicle_location_id';
            } else if($value == 'vehicles.repair_location_name') {
                $response[$key] = 'vehicle_repair_locations.name as repair_location_name';
            } else if($value == 'defects.report_datetime') {
                $response[$key] = 'DATE_FORMAT(CONVERT_TZ(defects.report_datetime, "UTC","'.config('config-variables.format.displayTimezone').'"), "%Y-%m-%d %H:%i:%s") as report_datetime';
            } else if($value == 'defect_status') {
                $response[$key] = 'defects.status as defect_status';
            } else if($value == 'last_comment_date') {
                $response[$key] = 'DATE_FORMAT(CONVERT_TZ(defect_history.created_at, "UTC","'.config('config-variables.format.displayTimezone').'"), "%H:%i:%s %d %M %Y") as last_comment_date';
            } else if($value == 'last_comment') {
                $response[$key] = 'defect_history.comments';
            } else if($value == 'users.company_id') {
                $response[$key] = 'companies.name as company_id';
            } else if($value == 'users.user_division_id') {
                $response[$key] = 'user_divisions.name as user_division_id';
            } else if($value == 'telematics_journey_start_date') {
                $response[$key] = 'DATE_FORMAT(CONVERT_TZ(telematics_journeys.start_time, "UTC","'.config('config-variables.format.displayTimezone').'"), "%d %b %Y") as telematics_journey_start_date';
            } else if($value == 'telematics_journey_start_time') {
                $response[$key] = 'DATE_FORMAT(CONVERT_TZ(telematics_journeys.start_time, "UTC","'.config('config-variables.format.displayTimezone').'"), "%H:%i:%s") as telematics_journey_start_time';
            } else if($value == 'telematics_journey_end_time') {
                $response[$key] = 'DATE_FORMAT(CONVERT_TZ(telematics_journeys.end_time, "UTC","'.config('config-variables.format.displayTimezone').'"), "%H:%i:%s") as telematics_journey_end_time';
            } else if($value == 'incident_time') {
                $response[$key] = 'DATE_FORMAT(CONVERT_TZ(telematics_journey_details.time, "UTC","'.config('config-variables.format.displayTimezone').'"), "%Y-%m-%d %H:%i:%s") as incident_time';
            } else if($value == 'telematics_journey_details.ns') {
                $response[$key] = 'CASE WHEN ns = "tm8.dfb2.acc.l" THEN "Harsh Acceleration" WHEN ns = "tm8.dfb2.cnrl.l" THEN "Harsh Left Cornering" WHEN ns = "tm8.dfb2.cnrr.l" THEN "Harsh Right Cornering" WHEN ns = "tm8.dfb2.dec.l" THEN "Harsh Braking"
                    WHEN ns = "tm8.dfb2.rpm" THEN "RPM" WHEN ns = "tm8.dfb2.spdinc" THEN "Speeding" WHEN ns = "tm8.gps.idle.start" THEN "Idle Start" WHEN ns = "tm8.gps.idle.end" THEN "Idling" END as incident_type';
            } else if($value == 'defects.report_datetime') {
                $response[$key] = 'DATE_FORMAT(CONVERT_TZ(defects.report_datetime, "UTC","'.config('config-variables.format.displayTimezone').'"), "%Y-%m-%d %H:%i:%s") as report_datetime';
            // } else if($value == 'checks.report_datetime') {
            //     $response[$key] = 'DATE_FORMAT(CONVERT_TZ(checks.report_datetime, "UTC","'.config('config-variables.format.displayTimezone').'"), "%Y-%m-%d %H:%i:%s") as report_datetime';
            } else if($value == 'vehicles.dt_annual_service_inspection') {
                $response[$key] = 'DATE_FORMAT(CONVERT_TZ(vehicles.dt_annual_service_inspection, "UTC","'.config('config-variables.format.displayTimezone').'"), "%Y-%m-%d %H:%i:%s") as dt_annual_service_inspection';
            } else if($value == 'vehicles.next_compressor_service') {
                $response[$key] = 'DATE_FORMAT(CONVERT_TZ(vehicles.next_compressor_service, "UTC","'.config('config-variables.format.displayTimezone').'"), "%Y-%m-%d %H:%i:%s") as next_compressor_service';
            } else if($value == 'vehicles.next_compressor_service') {
                $response[$key] = 'DATE_FORMAT(CONVERT_TZ(vehicles.next_compressor_service, "UTC","'.config('config-variables.format.displayTimezone').'"), "%Y-%m-%d %H:%i:%s") as next_compressor_service';
            } else if($value == 'vehicles.next_invertor_service_date') {
                $response[$key] = 'DATE_FORMAT(CONVERT_TZ(vehicles.next_invertor_service_date, "UTC","'.config('config-variables.format.displayTimezone').'"), "%Y-%m-%d %H:%i:%s") as next_invertor_service_date';
            } else if($value == 'vehicles.dt_loler_test_due') {
                $response[$key] = 'DATE_FORMAT(CONVERT_TZ(vehicles.dt_loler_test_due, "UTC","'.config('config-variables.format.displayTimezone').'"), "%Y-%m-%d %H:%i:%s") as dt_loler_test_due';
            } else if($value == 'vehicles.dt_mot_expiry') {
                $response[$key] = 'DATE_FORMAT(CONVERT_TZ(vehicles.dt_mot_expiry, "UTC","'.config('config-variables.format.displayTimezone').'"), "%Y-%m-%d %H:%i:%s") as dt_mot_expiry';
            } else if($value == 'vehicles.first_pmi_date') {
                $response[$key] = 'DATE_FORMAT(CONVERT_TZ(vehicles.first_pmi_date, "UTC","'.config('config-variables.format.displayTimezone').'"), "%Y-%m-%d %H:%i:%s") as first_pmi_date';
            } else if($value == 'vehicles.next_pmi_date') {
                $response[$key] = 'DATE_FORMAT(CONVERT_TZ(vehicles.next_pmi_date, "UTC","'.config('config-variables.format.displayTimezone').'"), "%Y-%m-%d %H:%i:%s") as next_pmi_date';
            } else if($value == 'vehicles.next_pto_service_date') {
                $response[$key] = 'DATE_FORMAT(CONVERT_TZ(vehicles.next_pto_service_date, "UTC","'.config('config-variables.format.displayTimezone').'"), "%Y-%m-%d %H:%i:%s") as next_pto_service_date';
            } else if($value == 'vehicles.dt_next_service_inspection') {
                $response[$key] = 'DATE_FORMAT(CONVERT_TZ(vehicles.dt_next_service_inspection, "UTC","'.config('config-variables.format.displayTimezone').'"), "%Y-%m-%d %H:%i:%s") as dt_next_service_inspection';
            } else if($value == 'vehicles.dt_tacograch_calibration_due') {
                $response[$key] = 'DATE_FORMAT(CONVERT_TZ(vehicles.dt_tacograch_calibration_due, "UTC","'.config('config-variables.format.displayTimezone').'"), "%Y-%m-%d %H:%i:%s") as dt_tacograch_calibration_due';
            } else if($value == 'vehicles.dt_tax_expiry') {
                $response[$key] = 'DATE_FORMAT(CONVERT_TZ(vehicles.dt_tax_expiry, "UTC","'.config('config-variables.format.displayTimezone').'"), "%Y-%m-%d %H:%i:%s") as dt_tax_expiry';
            } else if($value == 'telematics_journeys.engine_duration') {
                // $response[$key] = 'SUM(telematics_journeys.engine_duration) as engine_duration';
                $response[$key] = 'telematics_journeys.engine_duration';
            } else if($value == 'actual_driving_time') {
                $response[$key] = 'SUM(telematics_journeys.gps_distance) as actual_driving_time';
            } else if($value == 'idling_time') {
                $response[$key] = 'SUM(telematics_journeys.gps_idle_duration) as gps_idle_duration';
            } else if($value == 'mpg_actual') {
                $response[$key] = 'telematics_journeys.fuel as mpg_actual';
            } else if($value == 'mpg_expected') {
                $response[$key] = 'vehicles.vehiclefuelsum as mpg_expected';
            } else if($value == 'service_type') {
                $response[$key] = '"" as service_type';
            } else if($value == 'service_date') {
                $response[$key] = '"" as service_date';
            } else if($value == 'journey_start_location') {
                $response[$key] = 'CONCAT(telematics_journeys.start_lat,",",telematics_journeys.start_lon) as journey_start_location';
            } else if($value == 'journey_end_location') {
                $response[$key] = 'CONCAT(telematics_journeys.end_lat,",",telematics_journeys.end_lon) as journey_end_location';
            } else if($value == 'journey_start_map_link') {
                $response[$key] = '"" as journey_start_map_link';
            } else if($value == 'journey_end_map_link') {
                $response[$key] = '"" journey_end_map_link';
            } else {
                $response[$key] = $value;
            }
        }

        return $response;

    }

    if (! function_exists('callTeletracJourneysApi')) {
    /**
     * Call Teletrac API
     *
     * @param  array  $vehicleId
     * @return mixed
     *
     */
        function callTeletracJourneysApi($queryString)
        {
            $ch = curl_init();
            $from1 = \Carbon\Carbon::now()->subMinutes(2)->format('y-m-d');
            $from2 = \Carbon\Carbon::now()->subMinutes(2)->format('H:i:s');
            $from = $from1.'T'.$from2;
            // $url = 'https://api-uk.nextgen.teletracnavman.net/v1/trips?vehicleId=16503&from=2022-10-13T00:00:09&event_types=IOR,VPM_IT,SPEED,GEOFENCE,VPM_HC,VPM_IM,VPM_HB,VPM_OR,VPM_EA,VPM_EOP,VPM_ECT,VPM_EOT,ALARM,PRETRIP,FORM,ALERT,PTO,CAMERA,DRIVER,MASS,FATIGUE,GPIO&embed=meters,events';
            $baseURL = 'https://api-uk.nextgen.teletracnavman.net/v1/';
            // $url = $baseURL.'trips?vehicleId='.$vehicleId;
            // $url = $url.'&event_types=IOR,VPM_IT,SPEED,GEOFENCE,VPM_HC,VPM_IM,VPM_HB,VPM_OR,VPM_EA,VPM_EOP,VPM_ECT,VPM_EOT,ALARM,PRETRIP,FORM,ALERT,PTO,CAMERA,DRIVER,MASS,FATIGUE,GPIO&embed=meters,events';

            $url = $baseURL.$queryString;

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Authorization: Token token="'.env('TELETRAC_KEY').'"' ,
                        'Content-Type: application/json'
                        ));
            $output = curl_exec($ch);
            $resp = json_decode($output,true);
            curl_close($ch);
            return $resp;
        }
    }

    if (! function_exists('updateKPIDashboardData')) {
        /**
         * Get the readable time format.
         *
         * @param  string  $timeInSeconds
         * @return mixed
         *
         */
        function updateKPIDashboardData($registration)
        {
            $url = env('KPI_DASHBOARD_URL');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $post_data = 'registration='.$registration;
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            // If there is a requirement of HTTP authentication
            // $username = 'username';
            // $password = 'password';
            // // error_reporting(~0);
            // curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            $response = curl_exec($ch);
            curl_close($ch);
            if (!$response) {
                \Log::info('Unable to get API Response');
                return false;
            }
            $response = json_decode($response);
        }
    }
}