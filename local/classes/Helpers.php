<?php

// use Bitrix\Main\Loader;
// use Bitrix\Sale;

class Helpers
{	
    public static $source1 = 'https://api.open-meteo.com/v1/';
    public static $source2 = 'https://geocoding-api.open-meteo.com/v1/';
    
    public static function getPrecipitation(string $coordinate){

        list( $lat, $long) = explode(',', $coordinate);

        $lat = (float)$lat;
        $long = (float)$long;

        $get = array(
            'latitude'  => $lat,
            'longitude' => $long,
            'hourly' => 'precipitation',
            'timezone' => 'Europe/Moscow',
            'forecast_days' => 1
        );

        $url = self::$source1.'forecast?'.http_build_query($get);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $json = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($json, true);

        return $result;
    }

    public static function getParamsTimeZones(){

    }

    public static function addCity(string $cityName){
        
    }
}