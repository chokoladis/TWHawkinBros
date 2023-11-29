<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
/** @var array $arCurrentValues */

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock'))
{
	return;
}

$arCities = [];

$arSelect = ["ID", "IBLOCK_ID", "NAME", "CODE"];
$arFilter = ["IBLOCK_ID" => $arParams['IBLOCK_ID_CITY'] ,"ACTIVE"=>"Y"];
$query = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
while($ob = $query->GetNextElement()){ 
	$arFields = $ob->GetFields();
	$arCities[$arFields['ID']] = '['.$arFields["ID"].'] '.$arFields["NAME"];
}

$arComponentParameters = [
	"GROUPS" => [],
	"PARAMETERS" => [
		"IBLOCK_ID" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_IBLOCK_DESC_LIST_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => $citiesIblockId,
		],
		"CITY" => [
			"NAME" => "Город",
			"TYPE" => "LIST",
			"VALUES" => $arCities,
			"DEFAULT" => '={$_REQUEST["ID"]}',
			"ADDITIONAL_VALUES" => "Y",
			"REFRESH" => "Y",
		],
		"ACTIVE_DATE_FORMAT" => CIBlockParameters::GetDateFormat(GetMessage("T_IBLOCK_DESC_ACTIVE_DATE_FORMAT"), "ADDITIONAL_SETTINGS"),
		
		"CACHE_TIME"  =>  ["DEFAULT"=>36000000],
		"CACHE_FILTER" => [
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("IBLOCK_CACHE_FILTER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		],
		"CACHE_GROUPS" => [
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("CP_BNL_CACHE_GROUPS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		],
	],
];

// CIBlockParameters::AddPagerSettings(
// 	$arComponentParameters,
// 	GetMessage("T_IBLOCK_DESC_PAGER_NEWS"), //$pager_title
// 	true, //$bDescNumbering
// 	true, //$bShowAllParam
// 	true, //$bBaseLink
// 	($arCurrentValues["PAGER_BASE_LINK_ENABLE"] ?? '') ==="Y" //$bBaseLinkEnabled
// );

// CIBlockParameters::Add404Settings($arComponentParameters, $arCurrentValues);
