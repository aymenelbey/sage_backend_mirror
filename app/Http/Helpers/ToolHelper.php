<?php

namespace App\Http\Helpers;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
class ToolHelper
{
    public static function fetchAdress($adress){
        $httpClient = new \Http\Adapter\Guzzle6\Client();
        $provider = new \Geocoder\Provider\GoogleMaps\GoogleMaps($httpClient, null, env('GOOGLE_MAPS_API_KEY'));
        $geocoder = new \Geocoder\StatefulGeocoder($provider, 'fr');
        $result = $geocoder->geocodeQuery(GeocodeQuery::create($adress));
        return  $result;
    }
}