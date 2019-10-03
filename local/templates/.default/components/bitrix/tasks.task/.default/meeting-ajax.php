<?
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
$_SESSION['SESS_SHOW_INCLUDE_TIME_EXEC'] = 'N';
$APPLICATION->ShowIncludeStat = false;

use \Bitrix\Main\Loader,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Type\DateTime;

$arResult = array();

if (!Loader::includeModule('meeting')) {
	echo CUtil::PhpToJSObject(array('CHECK' => 'ERROR', 'MESSAGE' => 'Module "meeting" not install.'));
	exit;
}

$arFields = array(
	'TITLE' => $_POST['SUBJECT'],
	'DATE_START' => new DateTime($_POST['DATE']),
	'DESCRIPTION' => $_POST['COMMENT'],
	'USERS' => array(
		$_POST['USER'] => 'O',
		$_POST['USER_KEEPER'] => 'K',
	),
);
$meetingId = CMeeting::Add($arFields);

if ($meetingId > 0) {
	$arResult['CHECK'] = 'SUCCESS';
	$arResult['MEETING_ID'] = $meetingId;
} else {
	$arResult['CHECK'] = 'ERROR';
	$arResult['MESSAGE'] = 'Error meeting add.';
}

echo CUtil::PhpToJSObject($arResult);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');