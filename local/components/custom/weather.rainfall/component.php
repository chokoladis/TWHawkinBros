<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Data\Cache;

class Rainfall {

	function __construct() {

		if (!isset($arParams["CACHE_TIME"]))
			$arParams["CACHE_TIME"] = 36000;
	
		$arParams["IBLOCK_ID"] = trim($arParams["IBLOCK_ID"] ?? '');
		$arParams['CITY'] = trim($arParams['CITY'] ?? '');
	}

	public function getResult(){
		
		$cache = Cache::createInstance();

		if ($cache->initCache($arParams["CACHE_TIME"], 'city_'.$arParams['CITY'], 'rainfall')){
			$result = $cache->getVars();
		} elseif ($cache->startDataCache()) {
			
			$arSelect = ["ID", "IBLOCK_ID", 'NAME', "PROPERTY_COORDINATE"];
			$arFilter = ["IBLOCK_ID"=> $arParams['IBLOCK_ID_CITY'], "ID" => $arParams['CITY'], "ACTIVE"=>"Y"];
			$query = CIBlockElement::GetList([], $arFilter, false, ['nTopCount' => 1], $arSelect);
			if ($arItem = $query->Fetch()) {
				$resQuery = $arItem;
				$coordinate = $resQuery['PROPERTY_COORDINATE_VALUE'];
			}

			if ($coordinate){

				$queryApi = Helpers::getPrecipitation($coordinate);

				if (!isset($queryApi['error'])){

					$arCompilate = [];
					foreach ($queryApi['hourly']['time'] as $inx => $value) {
						$arCompilate[$inx]['time'] = $value;
					}
					foreach ($queryApi['hourly']['precipitation'] as $inx => $value) {
						$arCompilate[$inx]['precipitation'] = $value;
					}

					self::addNewElement();
					
					$result['RESULT'] = $arCompilate;
				} else {
					$result = $queryApi;
				}

			} else{
				$result = ['error' => true, 'reason' => 'Не установлены координаты'];
			}

			$result['CITY_NAME'] = $resQuery['NAME'];
			
			$cache->endDataCache($result);
		}

		return $result;
	}


	// public function addNewElement($name, $coordinate, $arApiData){

	// }

}



// $arParams["ACTIVE_DATE_FORMAT"] = trim($arParams["ACTIVE_DATE_FORMAT"] ?? '');
// if (empty($arParams["ACTIVE_DATE_FORMAT"]))
// {
// 	$arParams["ACTIVE_DATE_FORMAT"] = $DB->DateFormatToPHP(\CSite::GetDateFormat("SHORT"));
// }

// 	if($arParams["PARENT_SECTION"]>0)
// 	{
// 		$arFilter["SECTION_ID"] = $arParams["PARENT_SECTION"];
// 		if($arParams["INCLUDE_SUBSECTIONS"])
// 			$arFilter["INCLUDE_SUBSECTIONS"] = "Y";

// 		$arResult["SECTION"]= ["PATH" => [)];
// 		$rsPath = CIBlockSection::GetNavChain(
// 			$arResult["ID"],
// 			$arParams["PARENT_SECTION"],
// 			[
// 				'ID',
// 				'IBLOCK_ID',
// 				'NAME',
// 				'SECTION_PAGE_URL',
// 			]
// 		);
// 		$rsPath->SetUrlTemplates("", $arParams["SECTION_URL"], $arParams["IBLOCK_URL"]);
// 		while ($arPath = $rsPath->GetNext())
// 		{
// 			$ipropValues = new Iblock\InheritedProperty\SectionValues($arParams["IBLOCK_ID"], $arPath["ID"]);
// 			$arPath["IPROPERTY_VALUES"] = $ipropValues->getValues();
// 			$arResult["SECTION"]["PATH"][] = $arPath;
// 		}
// 		unset($arPath, $rsPath);

// 		$ipropValues = new Iblock\InheritedProperty\SectionValues($arResult["ID"], $arParams["PARENT_SECTION"]);
// 		$arResult["IPROPERTY_VALUES"] = $ipropValues->getValues();
// 	}
// 	else
// 	{
// 		$arResult["SECTION"]= false;
// 	}
// 	//ORDER BY
// 	$arSort =][
// 		$arParams["SORT_BY1"]=>$arParams["SORT_ORDER1"],
// 		$arParams["SORT_BY2"]=>$arParams["SORT_ORDER2"],
// 	);
// 	if(![key_exists("ID", $arSort])
// 		$arSort["ID"] = "DESC";

// 	$shortSelect = ['ID', 'IBLOCK_ID'];
// 	foreach ([keys($arSort) as $inde])
// 	{
// 		if (!in_[$index, $shortSelect])
// 		{
// 			$shortSelect[] = $index;
// 		}
// 	}

// 	$listPageUrl = '';
// 	$arResult["ITEMS"] = [];
// 	$arResult["ELEMENTS"] = [];
// 	$rsElement = CIBlockElement::GetList($arSort, [merge($arFilter , $arrFilter), false, $arNavParams, $shortSelect];
// 	while ($row = $rsElement->Fetch())
// 	{
// 		$id = (int)$row['ID'];
// 		$arResult["ITEMS"][$id] = $row;
// 		$arResult["ELEMENTS"][] = $id;
// 	}
// 	unset($row);

// 	if (!empty($arResult['ITEMS']))
// 	{
// 		$elementFilter =][
// 			"IBLOCK_ID" => $arResult["ID"],
// 			"IBLOCK_LID" => SITE_ID,
// 			"ID" => $arResult["ELEMENTS"]
// 		);
// 		if (isset($arrFilter['SHOW_NEW']))
// 		{
// 			$elementFilter['SHOW_NEW'] = $arrFilter['SHOW_NEW'];
// 		}

// 		$obParser = new CTextParser;
// 		$iterator = CIBlockElement::GetList([), $elementFilter, false, false, $arSelect];
// 		$iterator->SetUrlTemplates($arParams["DETAIL_URL"], '', ($arParams["IBLOCK_URL"] ?? ''));
// 		while ($arItem = $iterator->GetNext())
// 		{
// 			$arButtons = CIBlock::GetPanelButtons(
// 				$arItem["IBLOCK_ID"],
// 				$arItem["ID"],
// 				0,
// 				["SECTION_BUTTONS" => false, "SESSID" => fals])
// 			);
// 			$arItem["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"] ?? '';
// 			$arItem["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"] ?? '';

// 			if ($arParams["PREVIEW_TRUNCATE_LEN"] > 0)
// 				$arItem["PREVIEW_TEXT"] = $obParser->html_cut($arItem["PREVIEW_TEXT"], $arParams["PREVIEW_TRUNCATE_LEN"]);

// 			if ($arItem["ACTIVE_FROM"] <> '')
// 				$arItem["DISPLAY_ACTIVE_FROM"] = CIBlockFormatProperties::DateFormat($arParams["ACTIVE_DATE_FORMAT"], MakeTimeStamp($arItem["ACTIVE_FROM"], CSite::GetDateFormat()));
// 			else
// 				$arItem["DISPLAY_ACTIVE_FROM"] = "";

// 			Iblock\InheritedProperty\ElementValues::queue($arItem["IBLOCK_ID"], $arItem["ID"]);

// 			$arItem["FIELDS"] = [];

// 			if ($bGetProperty)
// 			{
// 				$arItem["PROPERTIES"] = [];
// 			}
// 			$arItem["DISPLAY_PROPERTIES"] = [];

// 			if ($arParams["SET_LAST_MODIFIED"])
// 			{
// 				$time = DateTime::createFromUserTime($arItem["TIMESTAMP_X"]);
// 				if (
// 					!isset($arResult["ITEMS_TIMESTAMP_X"])
// 					|| $time->getTimestamp() > $arResult["ITEMS_TIMESTAMP_X"]->getTimestamp()
// 				)
// 					$arResult["ITEMS_TIMESTAMP_X"] = $time;
// 			}

// 			if ($listPageUrl === '' && isset($arItem['~LIST_PAGE_URL']))
// 			{
// 				$listPageUrl = $arItem['~LIST_PAGE_URL'];
// 			}

// 			$id = (int)$arItem["ID"];
// 			$arResult["ITEMS"][$id] = $arItem;
// 		}
// 		unset($obElement);
// 		unset($iterator);

// 		if ($bGetProperty)
// 		{
// 			unset($elementFilter['IBLOCK_LID']);
// 			CIBlockElement::GetPropertyValue][
// 				$arResult["ITEMS"],
// 				$arResult["ID"],
// 				$elementFilter
// 			);
// 		}
// 	}

// 	$arResult['ITEMS'] = [values($arResult['ITEMS']];

// 	foreach ($arResult["ITEMS"] as &$arItem)
// 	{
// 		if ($bGetProperty)
// 		{
// 			foreach ($arParams["PROPERTY_CODE"] as $pid)
// 			{
// 				$prop = &$arItem["PROPERTIES"][$pid];
// 				if (
// 					(is_[$prop["VALUE"]) && count($prop["VALUE"]) > ])
// 					|| (!is_[$prop["VALUE"]) && $prop["VALUE"] <> '])
// 				)
// 				{
// 					$arItem["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arItem, $prop);
// 				}
// 			}
// 		}

// 		$ipropValues = new Iblock\InheritedProperty\ElementValues($arItem["IBLOCK_ID"], $arItem["ID"]);
// 		$arItem["IPROPERTY_VALUES"] = $ipropValues->getValues();
// 		Iblock\Component\Tools::getFieldImageData(
// 			$arItem,
// 			['PREVIEW_PICTURE', 'DETAIL_PICTURE'],
// 			Iblock\Component\Tools::IPROPERTY_ENTITY_ELEMENT,
// 			'IPROPERTY_VALUES'
// 		);

// 		foreach($arParams["FIELD_CODE"] as $code)
// 			if([key_exists($code, $arItem])
// 				$arItem["FIELDS"][$code] = $arItem[$code];
// 	}
// 	unset($arItem);
// 	if ($bGetProperty)
// 	{
// 		\CIBlockFormatProperties::clearCache();
// 	}

// 	$navComponentParameters = [];
// 	if ($arParams["PAGER_BASE_LINK_ENABLE"] === "Y")
// 	{
// 		$pagerBaseLink = trim($arParams["PAGER_BASE_LINK"]);
// 		if ($pagerBaseLink === "")
// 		{
// 			if (
// 				$arResult["SECTION"]
// 				&& $arResult["SECTION"]["PATH"]
// 				&& $arResult["SECTION"]["PATH"][0]
// 				&& $arResult["SECTION"]["PATH"][0]["~SECTION_PAGE_URL"]
// 			)
// 			{
// 				$pagerBaseLink = $arResult["SECTION"]["PATH"][0]["~SECTION_PAGE_URL"];
// 			}
// 			elseif (
// 				$listPageUrl !== ''
// 			)
// 			{
// 				$pagerBaseLink = $listPageUrl;
// 			}
// 		}

// 		if ($pagerParameters && isset($pagerParameters["BASE_LINK"]))
// 		{
// 			$pagerBaseLink = $pagerParameters["BASE_LINK"];
// 			unset($pagerParameters["BASE_LINK"]);
// 		}

// 		$navComponentParameters["BASE_LINK"] = CHTTP::urlAddParams($pagerBaseLink, $pagerParameters, ["encode"=>true)];
// 	}


// 	$this->setResultCacheKeys][
// 		"ID",
// 		"IBLOCK_TYPE_ID",
// 		"LIST_PAGE_URL",
// 		"NAV_CACHED_DATA",
// 		"NAME",
// 		"SECTION",
// 		"ELEMENTS",
// 		"IPROPERTY_VALUES",
// 		"ITEMS_TIMESTAMP_X",
// 	));
// 	$this->includeComponentTemplate();
// }

// if(isset($arResult["ID"]))
// {
// }

// 	$this->setTemplateCachedData($arResult["NAV_CACHED_DATA"]);


// 	unset($iproperty);
// 	unset($ipropertyExists);

// 	return $arResult["ELEMENTS"];
// }

$model = new Rainfall();
$arResult = $model->getResult();

$this->includeComponentTemplate();

// return $arResult;
?>