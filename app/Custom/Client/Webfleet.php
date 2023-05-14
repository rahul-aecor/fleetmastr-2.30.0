<?php

namespace App\Custom\Client;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class Webfleet
{

    private $apiKey;
    private $account;
    private $username;
    private $password;
    private $apiUrl;
    private $datetimeFormat;

    public function __construct()
    {

        $webfleetConfig         = config('config-variables.webfleet');
        $this -> apiKey         = $webfleetConfig['api_key'];
        $this -> account        = $webfleetConfig['account'];
        $this -> username       = $webfleetConfig['username'];
        $this -> password       = $webfleetConfig['password'];
        $this -> datetimeFormat = $webfleetConfig['datetime_format'];
        $this -> apiUrl         = $webfleetConfig['api_url'];
        $this -> apiUrl         = $this -> apiUrl . '?lang=en&useUTF8=true&account=' . $this -> account . '&username=' . $this -> username . '&password=' . $this -> password . '&apikey=' . $this -> apiKey . '&outputformat=json';
    }

    private function callApi($url)
    {
        \Log::info('callApi' . $url);
        $client   = new Client();
        $response = $client -> request('GET', $url);
        $response = (string)$response -> getBody();
        $response = json_decode($response, true);
        if (isset($response->errorCode) && $response->errorCode == 8011) {
            return exit('API LIMIT REACHED');
        } else {
            return $response;
        }
    }

    public function getJourney($startRange, $endRange)
    {
        $url = $this -> apiUrl . '&action=showTripReportExtern';

        $startRange = Carbon ::parse($startRange) -> setTimezone(config('config-variables.format.displayTimezone')) -> format($this -> datetimeFormat);
        $endRange   = Carbon ::parse($endRange) -> setTimezone(config('config-variables.format.displayTimezone')) -> format($this -> datetimeFormat);

        $url = $url . '&rangefrom_string=' . $startRange . '&rangeto_string=' . $endRange;
        //dd($url);
        return $this -> callApi($url);
    }

    public function getJourneyExtraDetails($startRange, $endRange, $obejctno, $objectuid)
    {
        $url = $this -> apiUrl . '&action=showTripSummaryReportExtern';
        $url .= '&objectuid=' . $objectuid;
        $url = $url . '&rangefrom_string=' . $startRange . '&rangeto_string=' . $endRange;
        return $this -> callApi($url);
    }

    public function getJourneySummary($startTime, $endTime, $objectno, $objectuid = '')
    {
        $url = $this -> apiUrl . '&action=showTracks';
        $url .= '&objectuid=' . $objectuid;
        $url = $url . '&rangefrom_string=' . $this->UtcToTime($startTime) . '&rangeto_string=' . $this->UtcToTime($endTime);
        return $this -> callApi($url);
    }

    public function getIdleIncident($startTime,$endTime,$objectno,$objectuid = '') {
        $url = $this -> apiUrl . '&action=showIdleExceptions';
        $url .= '&objectuid=' . $objectuid;
        $url = $url . '&rangefrom_string=' . $this->UtcToTime($startTime)  . '&rangeto_string=' . $this->UtcToTime($endTime) ;
        return $this -> callApi($url);
    }

    public function getAccelerationIncident($startTime,$endTime,$objectno,$objectuid = '') {
        $url = $this -> apiUrl . '&action=showAccelerationEvents';
        $url .= '&objectuid=' . $objectuid;
        $url = $url . '&rangefrom_string=' . $this->UtcToTime($startTime)  . '&rangeto_string=' . $this->UtcToTime($endTime) ;
        return $this -> callApi($url);
    }

    public function getSpeedingIncident($startTime,$endTime,$objectno,$objectuid = '') {
        $url = $this -> apiUrl . '&action=showSpeedingEvents';
        $url .= '&objectuid=' . $objectuid;
        $url = $url . '&rangefrom_string=' . $this->UtcToTime($startTime)  . '&rangeto_string=' . $this->UtcToTime($endTime) ;
        return $this -> callApi($url);
    }

    public function getVehicles()
    {
        $url = $this -> apiUrl . '&action=showObjectReportExtern';
        return $this -> callApi($url);
    }

    public function wgs84Toll($value)
    {
        return $value * 0.000001;
    }

    public function timeToUtc($dateTime)
    {
        return Carbon ::createFromFormat($this -> datetimeFormat, $dateTime, config('config-variables.format.displayTimezone')) -> setTimezone('UTC') -> format('Y-m-d H:i:s');
    }

    public function UtcToTime($dateTime)
    {
        return Carbon ::parse($dateTime,'UTC') -> setTimezone(config('config-variables.format.displayTimezone')) -> format($this -> datetimeFormat);
    }

    /**
     * Convert km per hour to meter per second
     */
    public function kmphToMps($value)
    {
        return number_format($value * 0.277778, 2);
    }

}