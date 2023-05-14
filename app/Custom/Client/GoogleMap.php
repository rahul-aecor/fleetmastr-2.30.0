<?php

namespace App\Custom\Client;

use GuzzleHttp\Client;

class GoogleMap
{
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $googleMapConfig = config('config-variables.googleMap');
        $this -> apiKey  = $googleMapConfig['api_key'];
        $this -> apiUrl  = $googleMapConfig['api_url'];
        $this -> apiUrl  = $this -> apiUrl . '?key=' . $this -> apiKey;
    }


    private function callApi($url)
    {
        $client   = new Client();
        $response = $client -> request('GET', $url);
        $response = (string)$response -> getBody();
        $response = json_decode($response, true);

        if (gettype($response) == 'array' && isset($response['error_message'])) {
            return false;
        } else {
            return $response;
        }
    }


    public function getAddressFromll($lat, $lon)
    {
        $url      = $this -> apiUrl . '&latlng=' . $lat . ',' . $lon;
        $jsondata = (array)$this -> callApi($url);



        $address = array(
            "street_no"         => "",
            "street"            => "",
            "town"              => "",
            "country"           => "",
            "postal_code"       => "",
            "formatted_address" => "",
        );

        //dd($jsondata['results'][0]['address_components']);
        if (isset($jsondata['results'][0]['address_components'])) {
            foreach ($jsondata['results'][0]['address_components'] as $value) {
                $value = (array)$value;
                if (in_array('street_number', $value['types'])) {
                    $address['street_no'] = trim($value["long_name"]);
                } else if (in_array('route', $value['types'])) {
                    //dd($value["long_name"]);
                    $address['street'] = trim($address['street_no']) . ' ' . trim($value["long_name"]);
                } else if (in_array('postal_town', $value['types'])) {
                    $address['town'] = trim($value["long_name"]);
                } else if (in_array('administrative_area_level_1', $value['types'])) {
                    $address['town'] = trim($address['town']). ' '.trim($value["long_name"]);
                } else if (in_array('locality', $value['types'])) {
                    $address['town'] = trim($address['town']) .' '. trim($value["long_name"]);
                } else if (in_array('country', $value['types'])) {
                    $address['country'] = trim($value["long_name"]);
                } else if (in_array('postal_code', $value['types'])) {
                    $address['postal_code'] = trim($value["long_name"]);
                } else if (in_array('country', $value['types'])) {
                    $address['country']      = trim($value["long_name"]);
                    $address['country_code'] = trim($value["short_name"]);
                }
            }
            $address['formatted_address'] = trim($jsondata['results'][0]['formatted_address']);
        }
        return $address;
    }

    public function setJourneyAddress($addressStr)
    {
        $address = $this->getAddress($addressStr);
        $address1 = null;
        $address2 = null;
        $postcode = null;

        if(count($address) == 2) {
            $address1 = trim($address[0]);

            $sAddress = trim($address[1]);

            $sAddress = $this->getPostcode($sAddress);
            if(!isset($sAddress[1])) {
                $address2 = trim($address[1]);
                // return null;
            } else {
                $address2 = $sAddress[0];
                $postcode = $sAddress[1];
            }
        } else if(count($address) == 1) {
            $sAddress = trim($address[0]);

            $sAddress = $this->getPostcode($sAddress);
            if(!isset($sAddress[0])) {
                $address1 = $address2 = trim($address[0]);
            } else {
                $address1 = $address2 = $sAddress[0];
            }
            $postcode = isset($sAddress[1]) ? $sAddress[1] : null;
        } else {
            return null;
        }

        $addressArr = ['street' => trim($address1), 'town' => trim($address2), 'postal_code' => trim($postcode)];

        return $addressArr;
    }

    public function getAddress($rawData)
    {
        $address = str_replace(", GB", "", trim($rawData));
        $address = explode(",", $address);
        return $address;
    }

    public function getPostcode($address)
    {
        $address = preg_split('/(\\s+\\w*\\s+\\w*$)/', $address, -1, PREG_SPLIT_DELIM_CAPTURE);
        return array_slice($address, 0, -1);
    }
}