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

if ($arResult['error'] !== true){
	?>
	<table>
		<tr>
			<td><?=$arResult['CITY_NAME']?></td>
		</tr>
		<?php
			foreach ($arResult['RESULT'] as $value) {
				?>
				<tr>
					<td><?=$value['time']?></td>
					<td><?=$value['precipitation']?></td>
				</tr>
				<?
			}
		?>
	</table>
	<?
} else {

}



?>
