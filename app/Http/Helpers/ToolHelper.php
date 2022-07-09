<?php

namespace App\Http\Helpers;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use App\models\Departement;

class ToolHelper
{
    public static function fetchAdress($adress){
        $httpClient = new \Http\Adapter\Guzzle6\Client();
        $provider = new \Geocoder\Provider\GoogleMaps\GoogleMaps($httpClient, null, env('GOOGLE_MAPS_API_KEY'));
        $geocoder = new \Geocoder\StatefulGeocoder($provider, 'fr');
        $result = $geocoder->geocodeQuery(GeocodeQuery::create($adress));
        return  $result;
    }

    public static function fetchDataFromInseeAPI($q, $mapping_function = false){
        // $q ex ['siren:1234', 'siren:1234', 'siren:1234']
        // $mapping function that maps API data to DB object
        $q = implode(' OR ', $q);
        $q = urlencode("({$q}) AND etablissementSiege:true");
        $champs = '';

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.insee.fr/entreprises/sirene/V3/siret?champs='.$champs.'&q='.$q.'&masquerValeursNulles=false',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Authorization: Bearer eacb5e64-dec0-32b6-961d-c3dd43ffd0ad',
                'Cookie: INSEE=155763466.20480.0000; pdapimgateway=2890449674.22560.0000'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $api_data = json_decode($response, 1)['etablissements'];

        $entities = [];

        foreach($api_data as $entity){
            $mapping = [];
            $address = $entity['adresseEtablissement'];
            $address_text = "";

            if(isset($address['numeroVoieEtablissement'])){
                $address_text .= $address['numeroVoieEtablissement']." ";
            }

            if(isset($address['typeVoieEtablissement'])){
                $address_text .= $address['typeVoieEtablissement']." ";
            }

            if(isset($address['libelleVoieEtablissement'])){
                $address_text .= $address['libelleVoieEtablissement'];
            }

            $mapping['siret'] = $entity['siret'];
            $mapping['serin'] = $entity['siren'];
            $mapping['adresse'] = $address_text;
            $mapping['postcode'] = $address['codePostalEtablissement'];

            $mapping['city'] = $address['libelleCommuneEtablissement'];
            $mapping['country'] = 'France';
            
            if($mapping_function){
                $mapping = array_merge($mapping, $mapping_function($entity));
            }
            $entities[] = $mapping;
        }
        return $entities;
    }
}