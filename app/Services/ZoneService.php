<?php
namespace App\Services;

use File;
use Auth;
use Mail;
use Carbon\Carbon;
use App\Models\User;
use App\Repositories\ZonesRepository;


class ZoneService
{
    
    public function store($data) {
        $zone = new ZonesRepository();
        $zone->store($data);
    }

    public function update($data,$id) {
        $zone = new ZonesRepository();
        $zone->update($data, $id);
    }

    public function getAllZones() {
        $zone = new ZonesRepository();
        return $zone->getAllZones();
    }  

    public function makePolygonJson($zone) {
        $boundString = '';
        if (isset($zone->bounds) && !empty($zone->bounds)) {
            $boundsData = json_decode($zone->bounds, true);
            foreach ($boundsData as $key => $coOrdinate) {
                //print_r($coOrdinate);exit;
                if (is_array($coOrdinate)) {
                    $bounds[$key]['lat'] = $coOrdinate['lat'];
                    $bounds[$key]['lng'] = $coOrdinate['lng'];
                }
                else{            
                    if(!empty($coOrdinate)) {
                        list($lat, $long) = explode(' ', $coOrdinate);
                        $bounds[$key]['lat'] = (float)$lat;
                        $bounds[$key]['lng'] = (float)$long;
                    }
                }
                //$bounds[$key]['lat'] = $coOrdinate['lat'];
                //$bounds[$key]['lng'] = $coOrdinate['lng'];

            }
            $boundString = json_encode($bounds);
        }
        return $boundString;
    }

    public function mapAlertsJson($zoneAlertData) {
        $markerJsonData = [];

        // get the max speed of a journey
        $maxSpeed = $zoneAlertData->max_acceleration != null ? ($zoneAlertData->max_acceleration * 2.236936) : 0;
        if($maxSpeed > 0) {
            $tmp = $maxSpeed % 10;
            $maxSpeed = ($maxSpeed / 10) * 10;
            if($tmp >= 5) {
                $maxSpeed = (int)(($maxSpeed / 10) * 10 + 1);
            }
        }

        $data = [];
        $data['id'] = $zoneAlertData->id;
        $data['vehicleId'] = $zoneAlertData->vehicle_id;
        $zoneBoundsJson = $this->makePolygonJson($zoneAlertData->zone);
        $data['bounds'] = $zoneBoundsJson;

        $data['lat'] = $zoneAlertData->latitude;
        $data['lon'] = $zoneAlertData->longitude;

        $data['speed'] = number_format($zoneAlertData->speed, 2).' MPH';
        $data['direction'] = $this->calcDirection($zoneAlertData->direction);
        $data['status'] = $zoneAlertData->vehicle->status;
        $data['registration'] = $zoneAlertData->vrn;
        $data['driver'] = $zoneAlertData->user->first_name.' '.$zoneAlertData->user->last_name;
        $data['address'] = $zoneAlertData->address;
        $data['date'] = $zoneAlertData->created_at;
        $data['max_acceleration'] = number_format($maxSpeed, 2).' MPH';
        $data['start_time'] = $zoneAlertData->start_time;
        $view = view('_partials.telematics.zoneAlertMarkerDetails')->with(['markerDetails'=>$data]);
        $data['infoWindow'] = $view->render();
        array_push($markerJsonData, $data);

        return $markerJsonData;
    }

    function is_in_polygon2($longitude_x, $latitude_y,$fenceArea)
    {
        $x = $latitude_y; $y = $longitude_x;
        $inside = false;
        for ($i = 0, $j = count($fenceArea) - 1; $i <  count($fenceArea); $j = $i++) {
            $xi = $fenceArea[$i]['lat']; $yi = $fenceArea[$i]['lng'];
            $xj = $fenceArea[$j]['lat']; $yj = $fenceArea[$j]['lng'];

            $intersect = (($yi > $y) != ($yj > $y))
                && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);
            if ($intersect) $inside = !$inside;
        }

        return $inside;
    }

    private function calcDirection($degree){
        if ( ($degree>=338 && $degree<=360) ||($degree>=0 && $degree<=22) ) {
            return 'North';
        }
        if ($degree>=23 && $degree<=75) {
            return 'North East';
        }
        if ($degree>=76 && $degree<=112) {
            return 'East';
        }
        if ($degree>=113 && $degree<=157) {
            return 'South East';
        }
        if ($degree>=158 && $degree<=202) {
            return 'South';
        }
        if ($degree>=203 && $degree<=247) {
            return 'South West';
        }
        if ($degree>=248 && $degree<=292) {
            return 'West';
        }
        if ($degree>=293 && $degree<=337) {
            return 'North West';
        }
    }
}
