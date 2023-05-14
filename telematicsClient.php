<?php 
$lat = '51.533474';
$lng = '-0.119582';
while (true) {
    $curl = curl_init();
    // Set some options - we are passing in a useragent too here
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => 'http://sunny-fleetmastr-api.dev.aecortech.com/api/v1/telematics/addData',
        CURLOPT_USERAGENT => 'cURL Request',
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => array(
            'vehicle_id' => '3',
            'hardware_id' => 'hw10',
            'latitude' => $lat,
            'longitude' => $lng,
            'driver_id' => 'd02',
            'fuel_used' => '100',
            'distance_covered' => '1',
            'journey_id' => 'j02'
        )
    ));
    $lat = $lat+0.001100;
    $lng = $lng+0.001100;
    //$resp = curl_exec($curl);
    // Close request to clear up some resources
    //curl_close($curl);
    if(!curl_exec($curl)){
        die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
    }
    echo "Data added successfully.";
    sleep(10);
}
?>

