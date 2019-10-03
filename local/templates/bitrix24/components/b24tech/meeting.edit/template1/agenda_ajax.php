<?
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
$_SESSION['SESS_SHOW_INCLUDE_TIME_EXEC'] = 'N';
$APPLICATION->ShowIncludeStat = false;

use \Bitrix\Main\Loader,
	\Bitrix\Main\Localization\Loc;

global $USER;
$userId = $USER->GetID();

if (!Loader::includeModule('iblock')) {
	echo CUtil::PhpToJSObject(array('CHECK' => 'ERROR', 'MESSAGE' => 'Module "iblock" not install.'));
	exit;
}

$arResult = array();
if ($_POST['MEETING_ID'] > 0 && $_POST['NEED_AGREE'] == 'Y') {

	$res = CIBlockElement::GetList(array(), array('PROPERTY_MEETING_ID' => $_POST['MEETING_ID'], 'IBLOCK_ID' => 29), false, false, array('ID', 'IBLOCK_ID'));
	$arElement = $res->GetNext();
	if ($arElement['ID']) {
		// $el = new CIBlockElement;

		// $arFields = array(
		// 	'PROPERTY_VALUES' => array(
		// 		'MEETING_ID' => $_POST['MEETING_ID'],
		// 		'AGENDA_AGREE' => '',
		// 	),
		// );

		// if ($res = $el->Update($arElement['ID'], $arFields)) {
		// 	$arResult['CHECK'] = 'SUCCESS';
		// } else {
		// 	$arResult['CHECK'] = 'ERROR';
		// 	$arResult['MESSAGE'] = $el->LAST_ERROR;
		// }

		CIBlockElement::SetPropertyValues($arElement['ID'], $arElement['IBLOCK_ID'], '', 'AGENDA_AGREE');

	} else {
		$arResult['CHECK'] = 'ERROR';
		$arResult['MESSAGE'] = 'IBlock element not found for this meeting';
	}
} elseif ($_POST['MEETING_ID'] > 0 && $_POST['ACTION'] == 'SAVE_RESPONSOBLE') {
	$res = CIBlockElement::GetList(array(), array('PROPERTY_MEETING_ID' => $_POST['MEETING_ID'], 'IBLOCK_ID' => 29), false, false, array('ID', 'IBLOCK_ID'));
	$arElement = $res->GetNext();
	if ($arElement['ID']) {
		CIBlockElement::SetPropertyValues($arElement['ID'], $arElement['IBLOCK_ID'], serialize($_POST['DATA']), 'AGENDA_RESPONSIBLE');
	}

} else {
	$arResult['CHECK'] = 'ERROR';
	$arResult['MESSAGE'] = 'Meeting ID is not defined';
}

echo CUtil::PhpToJSObject($arResult);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');