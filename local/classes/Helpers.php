<?php

// use Bitrix\Main\Loader;
// use Bitrix\Sale;
use Bitrix\Main\Data\Cache;

class Helpers
{	
    public static $source1 = 'https://api.open-meteo.com/v1/';
    public static $source2 = 'https://geocoding-api.open-meteo.com/v1/';
    
    // функция для агента
    public static function initPrecipitation(){

        $currDateDay = date("d.m.Y");

        $cities = self::getCities();

        foreach($cities as $city){
            $data = self::getPrecipitation($city['PROPERTY_VALUE_COORDINATE'], 2, 0);

            $arCompilate = [];
            foreach ($data['hourly']['time'] as $inx => $value) {
                $arCompilate[$inx]['time'] = $value;
            }
            foreach ($data['hourly']['precipitation'] as $inx => $value) {
                $arCompilate[$inx]['precipitation'] = $value;
            }

            $arElem = self::addElements($city['NAME'],$arCompilate);
        }

    }

    public static function getPrecipitation(string $coordinate, int $days = 1, $f_useCache = 1){

        list( $lat, $long) = explode(',', $coordinate);

        $lat = (string)$lat;
        $long = (string)$long;

        if ($f_useCache){

            $cache = Cache::createInstance(); // Служба кеширования
            
            $cachePath = 'rainfall';
            $cacheTtl = 360000;
            $cacheKey = $lat.'_'.$long;
            
            if ($cache->initCache($cacheTtl, $cacheKey, $cachePath))
            {
                $result = $cache->getVars();
            }
            elseif ($cache->startDataCache())
            {
                $result = self::getDataFromApi($coordinate, $days);
                $cache->endDataCache($result);
            }
        } else {
            $result = self::getDataFromApi($coordinate, $days);
        }

        return $result;
    }

    public static function getDataFromApi(string $coordinate, int $days = 1){

        list( $lat, $long) = explode(',', $coordinate);

        $lat = (float)$lat;
        $long = (float)$long;

        $get = array(
            'latitude'  => $lat,
            'longitude' => $long,
            'hourly' => 'precipitation',
            'timezone' => 'Europe/Moscow',
            'forecast_days' => $days
        );

        $url = self::$source1.'forecast?'.http_build_query($get);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $json = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($json, true);
    }

    public static function getCities(){
        
        Loader::includeModule('iblock');

        $arSelect = ["ID", "IBLOCK_ID", 'NAME', "PROPERTY_COORDINATE"];
		$arFilter = ["IBLOCK_ID"=> CIBlockTools::GetIBlockId('cities'), "ACTIVE"=>"Y"];
		$query = CIBlockElement::GetList([], $arFilter, false, ['nTopCount' => 1], $arSelect);
		while ($arItem = $query->Fetch()) {
			$arResult[] = $arItem;
		}

        return $arResult;
    }

	public function addElements($cityName, $arApiData){
		
		$iblockID = CIBlockTools::GetIBlockId('precipitation');

		$arElems = $needElem = [];

		$currDateDay = date("Y_m_d");

		foreach($arApiData as $inx => $arTemp){
			
			$date = strtotime($arTemp['time']);
			$dateDayName = date("Y_m_d", $date);

			if ($inx === 0){
				$endDate = strtotime('+1 day', (int)$arTemp['time']);
			}
			
			if ($date == $endDate)
				$endDate = strtotime('+1 day', (int)$arTemp['time']);
				
			$arElems[$dateDayName][] = $arTemp;
		}

		foreach($arElems as $dateName => $arElem){
				
			$f_createElem = false;

			$arFilter = ["IBLOCK_ID"=> $iblockID, 'NAME' => $cityName];
			$rsSections = CIBlockSection::GetList([], $arFilter);
			if ($arSection = $rsSections->Fetch())
			{
				$sectionId = $arSection['ID'];
				$findElem = $this->isElemOnSection($dateName, $sectionId);
				if (isset($findElem['success']) && !$findElem['success'])
					$f_createElem = true;

				// var_dump('find-',$findElem);
			} else {
				$bs = new CIBlockSection;
				$arFields = [
					"ACTIVE" => 'Y',
					"IBLOCK_ID" => $iblockID,
					"NAME" => $cityName,
				];

				$sectionId = $bs->Add($arFields);
				if(!$sectionId)
					$resSection = ['success' => false, 'error' => $bs->LAST_ERROR ];

				$f_createElem = true;
			}

			if ($resSection !== null && !$resSection['success'])
				continue;

			if ($f_createElem)
				$findElem = $this->addElement($dateName, $arElem, $sectionId);

			if (!isset($findElem['success']) && $currDateDay == $dateName)
				$needElem = $findElem;

		}

		return $needElem;
	}

	public function addElement($dateName, $arElem, $sectionId){

		$el = new CIBlockElement;
		$html = '';
        $iblockID = CIBlockTools::GetIBlockId('precipitation');

		foreach($arElem as $value){

			$date = strtotime($value["time"]);
			$dateTime = date("H:i", $date);			

			$html .= '<tr>
				<td>'.$dateTime.'</td>
				<td>'.$value["precipitation"].' mm</td>
			</tr>';
		}

		$arLoadProductArray = Array(
			// "MODIFIED_BY"    => $USER->GetID(),
			"IBLOCK_SECTION_ID" => $sectionId,
			"IBLOCK_ID"      => $iblockID,
			"NAME"           => $dateName,
			"ACTIVE"         => "Y",
			"PREVIEW_TEXT"   => $html,
			"PREVIEW_TEXT_TYPE" => 'html'
		);

		$ID = $el->Add($arLoadProductArray);

		if ($ID){
			$arLoadProductArray['ID'] = $ID;
		} else {
			$arLoadProductArray = [ 'success' => false, 'error' => $el->LAST_ERROR  ];
		}

		return $arLoadProductArray;
	}

	public function isElemOnSection($dateName, $sectionId){

        $iblockID = CIBlockTools::GetIBlockId('precipitation');

		$arSelect = ["ID", "IBLOCK_ID", 'NAME', 'PREVIEW_TEXT'];
		$arFilter = ["IBLOCK_ID"=> $iblockID, "NAME" => $dateName, "ACTIVE"=>"Y", 'IBLOCK_SECTION_ID' => $sectionId];
		$query = CIBlockElement::GetList([], $arFilter, false, ['nTopCount' => 1], $arSelect);
		if ($arItem = $query->Fetch() && !empty($arItem)) {
			$res = $arItem;
		} else {
			$res = [ 'success' => false];
		}

		return $res;
	}
}