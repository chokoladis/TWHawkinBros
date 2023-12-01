<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Main\Data\Cache;

class Rainfall extends CBitrixComponent{

	public function executeComponent()
    {
        try {
			$this->checkModules();
            $this->getResult();
        } catch (SystemException $e) {
            ShowError($e->getMessage());
        }
    }

	protected function checkModules()
    {
        if (!Loader::includeModule('iblock'))
            throw new SystemException(Loc::getMessage('IBLOCK_MODULE_NOT_INSTALLED'));
    }

	public function onPrepareComponentParams($arParams)
    {
        $arParams["CACHE_TIME"] = !isset($arParams["CACHE_TIME"]) ?? 3600;
		$arParams["IBLOCK_ID"] = trim($arParams["IBLOCK_ID"] ?? '');
		$arParams["IBLOCK_ID_CITY"] = trim($arParams["IBLOCK_ID_CITY"] ?? '');
		$arParams['CITY'] = trim($arParams['CITY'] ?? '');

		$this->arParams = $arParams;

        return $arParams;
    }

	public function getResult(){
			
		Loader::includeModule("iblock");

		$arSelect = ["ID", "IBLOCK_ID", 'NAME', "PROPERTY_COORDINATE"];
		$arFilter = ["IBLOCK_ID"=> $this->arParams['IBLOCK_ID_CITY'], "ID" => $this->arParams['CITY'], "ACTIVE"=>"Y"];
		$query = CIBlockElement::GetList([], $arFilter, false, ['nTopCount' => 1], $arSelect);
		if ($arItem = $query->Fetch()) {
			$resQuery = $arItem;
			$coordinate = $resQuery['PROPERTY_COORDINATE_VALUE'];
		}

		$result['CITY_NAME'] = $resQuery['NAME'];

		if ($coordinate){

			$queryApi = Helpers::getPrecipitation($coordinate, 2, 1);

			if (!isset($queryApi['error'])){

				$arCompilate = [];
				foreach ($queryApi['hourly']['time'] as $inx => $value) {
					$arCompilate[$inx]['time'] = $value;
				}
				foreach ($queryApi['hourly']['precipitation'] as $inx => $value) {
					$arCompilate[$inx]['precipitation'] = $value;
				}

				$arElem = self::addElements($result['CITY_NAME'],$arCompilate);
				$currDateDay = date("d.m.Y");

				$result['RESULT'] = $arElem;
				$result['TITLE_DATE'] = $currDateDay;
			} else {
				$result = $queryApi;
			}

		} else{
			$result = ['error' => true, 'reason' => 'Не установлены координаты'];
		}

		$this->arResult = $result;
		
		return $result;
	}


	public function addElements($cityName, $arApiData){
		
		$iblockID = $this->arParams['IBLOCK_ID'];

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
			"IBLOCK_ID"      => $this->arParams['IBLOCK_ID'],
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
		$arSelect = ["ID", "IBLOCK_ID", 'NAME', 'PREVIEW_TEXT'];
		$arFilter = ["IBLOCK_ID"=> $this->arParams['IBLOCK_ID'], "NAME" => $dateName, "ACTIVE"=>"Y", 'IBLOCK_SECTION_ID' => $sectionId];
		$query = CIBlockElement::GetList([], $arFilter, false, ['nTopCount' => 1], $arSelect);
		if ($arItem = $query->Fetch() && !empty($arItem)) {
			$res = $arItem;
		} else {
			$res = [ 'success' => false];
		}

		return $res;
	}

}

$model = new Rainfall();

$model->onPrepareComponentParams($arParams);
$arResult = $model->getResult();

$this->includeComponentTemplate();
?>