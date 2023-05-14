<?php
namespace App\Repositories;

use Carbon\Carbon as Carbon;
use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;
use App\Models\TelematicsJourneys;
use App\Models\TelematicsJourneyDetails;

class TelematicsJourneyDetailsRepository extends EloquentRepositoryAbstract {

    public function __construct($data = null)
    {
        
    }

    public function create($telematicsJourneysDetailsData) {

        $nsToProcessForSpeed = ['tm8.gps', 'tm8.dfb2.acc.l', 'tm8.dfb2.dec.l', 'tm8.dfb2.spd', 'tm8.dfb2.rpm','tm8.dfb2.cnrl.l', 'tm8.dfb2.cnrr.l'];

        $json_journey_id = isset($telematicsJourneysDetailsData['journey_id'])?$telematicsJourneysDetailsData['journey_id']:null;
        $json_vrn = isset($telematicsJourneysDetailsData['vrn'])?$telematicsJourneysDetailsData['vrn']:null;

        $telematicsJourneyDetails = new TelematicsJourneyDetails();
        $telematicsJourneyDetails->odometer = isset($telematicsJourneysDetailsData['odometer'])?$telematicsJourneysDetailsData['odometer']:null;
        $telematicsJourneyDetails->ns = isset($telematicsJourneysDetailsData['ns'])?$telematicsJourneysDetailsData['ns']:null;
        $telematicsJourneyDetails->vrn = isset($telematicsJourneysDetailsData['vrn'])?$telematicsJourneysDetailsData['vrn']:null;
        $telematicsJourneyDetails->odometer = isset($telematicsJourneysDetailsData['odometer'])?$telematicsJourneysDetailsData['odometer']:null;
        //$telematicsJourneyDetails->odo_source = isset($telematicsJourneysDetailsData['odo_source'])?$telematicsJourneysDetailsData['odo_source']:null;
        $telematicsJourneyDetails->lat = isset($telematicsJourneysDetailsData['lat'])?$telematicsJourneysDetailsData['lat']:null;
        $telematicsJourneyDetails->lon = isset($telematicsJourneysDetailsData['lon'])?$telematicsJourneysDetailsData['lon']:null;
        $telematicsJourneyDetails->time = isset($telematicsJourneysDetailsData['time'])?Carbon::parse($telematicsJourneysDetailsData['time'])->setTimezone('UTC'):null;
        $telematicsJourneyDetails->heading = isset($telematicsJourneysDetailsData['heading'])?$telematicsJourneysDetailsData['heading']:0.00;
        if (in_array($telematicsJourneyDetails->ns, $nsToProcessForSpeed)) {
            $telematicsJourneyDetails->speed = isset($telematicsJourneysDetailsData['speed'])?$telematicsJourneysDetailsData['speed']:null;
        }
        $telematicsJourneyDetails->gps_odo = isset($telematicsJourneysDetailsData['gps_odo'])?$telematicsJourneysDetailsData['gps_odo']:0;
        $telematicsJourneyDetails->street = isset($telematicsJourneysDetailsData['street'])?$telematicsJourneysDetailsData['street']:null;
        $telematicsJourneyDetails->town = isset($telematicsJourneysDetailsData['town'])?$telematicsJourneysDetailsData['town']:null;
        $telematicsJourneyDetails->post_code = isset($telematicsJourneysDetailsData['postcode'])?$telematicsJourneysDetailsData['postcode']:null;
        $telematicsJourneyDetails->idle_duration = isset($telematicsJourneysDetailsData['idle_duration'])?$telematicsJourneysDetailsData['idle_duration']:0;
        $telematicsJourneyDetails->vin = isset($telematicsJourneysDetailsData['vin'])?$telematicsJourneysDetailsData['vin']:null;
        $telematicsJourneyDetails->ex_idle_threshold = isset($telematicsJourneysDetailsData['ex_idle_threshold'])?$telematicsJourneysDetailsData['ex_idle_threshold']:null;
        $telematicsJourneyDetails->street_speed = isset($telematicsJourneysDetailsData['street_speed'])?$telematicsJourneysDetailsData['street_speed']:null;
        $telematicsJourneyDetails->idle_threshold = isset($telematicsJourneysDetailsData['idle_threshold'])?$telematicsJourneysDetailsData['idle_threshold']:null;
        $telematicsJourneyDetails->num_stats = isset($telematicsJourneysDetailsData['num_stats'])?$telematicsJourneysDetailsData['num_stats']:null;
        $uid = isset($telematicsJourneysDetailsData['uid'])?$telematicsJourneysDetailsData['uid']:null;
        $telematics_journey_id = $this->fetchJourneyId($json_vrn, $json_journey_id,$uid);
        
        $telematicsJourneyDetails->telematics_journey_id = $telematics_journey_id != null ? $telematics_journey_id : 0;
        $telematicsJourneyDetails->raw_json = json_encode($telematicsJourneysDetailsData);
        //$telematicsJourneyDetails->fix = isset($telematicsJourneysDetailsData['fix'])?$telematicsJourneysDetailsData['fix']:'0';
        //$telematicsJourneyDetails->uid = isset($telematicsJourneysDetailsData['uid'])?$telematicsJourneysDetailsData['uid']:'null';
        //$telematicsJourneyDetails->make = isset($telematicsJourneysDetailsData['make'])?$telematicsJourneysDetailsData['make']:'null';
        //$telematicsJourneyDetails->model = isset($telematicsJourneysDetailsData['model'])?$telematicsJourneysDetailsData['model']:'null';
        //$telematicsJourneyDetails->mil = isset($telematicsJourneysDetailsData['mil'])?$telematicsJourneysDetailsData['mil']:'null';
        if(isset($telematicsJourneysDetailsData['lat']) && isset($telematicsJourneysDetailsData['lon']) && $telematicsJourneysDetailsData['lat'] != '' && $telematicsJourneysDetailsData['lon'] != '') {
            $telematicsJourneyDetails->save();
        }
        return $telematicsJourneyDetails;
    }

    private function fetchJourneyId($registration, $journey_id, $uid){
        //$telematicsJourney = TelematicsJourneys::where(['vrn'=>$registration,'journey_id'=>$journey_id])->whereNull('end_time')->first();
        $telematicsJourney = TelematicsJourneys::withTrashed()->where(['vrn'=>$registration,'journey_id'=>$journey_id,'uid'=>$uid])->first();
        if ($telematicsJourney) {
            return $telematicsJourney->id;
        }
        return null;

    }
    public function updateJourneyIdForIgnon($registration, $journey_id){
        $telematicsJourneyDetails = TelematicsJourneyDetails::where(['vrn'=>$registration,'ns'=>'tm8.gps.ign.on'])->latest('time')->first();
        if($telematicsJourneyDetails) {
            $telematicsJourneyDetails->telematics_journey_id = $journey_id;
            $telematicsJourneyDetails->save();
        }
        return $telematicsJourneyDetails;
    }

}
