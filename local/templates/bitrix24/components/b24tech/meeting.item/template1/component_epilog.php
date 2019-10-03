<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use \Bitrix\Main\Localization\Loc;

if (is_array($arResult['ITEM']['INSTANCES']) && count($arResult['ITEM']['INSTANCES']) > 0) {
	$arInstance = current($arResult['ITEM']['INSTANCES']);
	if ($arInstance['INSTANCE_PARENT_ID'] > 0) {
		$APPLICATION->SetTitle(Loc::getMessage('MEETING_ITEM_PAGE_TITLE_CHILD'));
	} else {
		$APPLICATION->SetTitle(Loc::getMessage('MEETING_ITEM_PAGE_TITLE_PARENT'));
	}
}