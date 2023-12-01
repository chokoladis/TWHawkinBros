<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php"); 
$APPLICATION->SetTitle("1С-Битрикс: Управление сайтом");

$APPLICATION->IncludeComponent(
	"custom:weather.rainfall", 
	".default", 
	array(
		"IBLOCK_ID" => CIBlockTools::GetIBlockId('precipitation'),
		"ACTIVE_DATE_FORMAT" => "d.m.Y",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => 360,
		"COMPONENT_TEMPLATE" => ".default",
		"IBLOCK_ID_CITY" => CIBlockTools::GetIBlockId('cities'),
		"CITY" => "1",
	),
	false
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>