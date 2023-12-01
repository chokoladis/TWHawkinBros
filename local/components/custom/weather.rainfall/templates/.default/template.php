<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

$arElem = $arResult['RESULT'];

if ($arResult['error'] !== true){
	?>
	<table border=1 width='30%' align="center">
		<tr rowspan="2" >
			<td colspan='2'><?=$arResult['CITY_NAME']?><br><?=$arResult['TITLE_DATE']?></td>
		</tr>
		<?php
			if (!empty($arElem) && $arElem['PREVIEW_TEXT']){
				echo $arElem['PREVIEW_TEXT'];
			}
		?>
	</table>
	<?
} else {

}

?>
<style>
	table *{
		text-align: center;
	}
</style>
