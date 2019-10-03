<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
/** @var $this \CBitrixComponentTemplate */
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
/** @var \Bitrix\Disk\Internals\BaseComponent $component */
$template = '';

if(isset($arParams['TEMPLATE'])) {
	$template = $arParams['TEMPLATE'];
}
?>
<?
$APPLICATION->IncludeComponent(
	'b24tech:disk.file.list',
	$template,
	array(
		'FOLDER_ID' => $arResult['FOLDER'],
		'TYPE' => $arParams['TYPE'],	
	),
	false,
	array("HIDE_ICONS" => "Y")
);
?>

