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

if (!Loader::includeModule('meeting')) {
	echo CUtil::PhpToJSObject(array('CHECK' => 'ERROR', 'MESSAGE' => 'Module "meeting" not install.'));
	exit;
}

if (!class_exists('\MA\Meeting\UsersTable')) {
	echo CUtil::PhpToJSObject(array('CHECK' => 'ERROR', 'MESSAGE' => 'Class "\MA\Meeting\UsersTable" not found.'));
	exit;
}

if (!Loader::includeModule('im')) {
	return CUtil::PhpToJSObject(array('CHECK' => 'ERROR', 'MESSAGE' => 'Module "im" not install.'));
	exit;
}

if (!Loader::includeModule('iblock')) {
	return CUtil::PhpToJSObject(array('CHECK' => 'ERROR', 'MESSAGE' => 'Module "iblock" not install.'));
	exit;
}

$arResult = array();
if ($_POST['MEETING_ID'] > 0) {
	$filter = array(
		'MEETING_ID' => $_POST['MEETING_ID'],
	);
	$select = array(
		'USER_ID'
	);
	$resUser = \MA\Meeting\UsersTable::getList(array(
		'filter' => $filter,
		'select' => $select,
	));
	$arUsers = array();
	while ($arUser = $resUser->Fetch()) {
		$arUsers[] = $arUser['USER_ID'];
	}
	if (is_array($arUsers) && !empty($arUsers)) {
		$resMeeting = \MA\Meeting\MeetingTable::getList(array(
			'filter' => array('ID' => $_POST['MEETING_ID']),
		));
		$arMeeting = $resMeeting->Fetch();
		$meetingTitle = $arMeeting['TITLE'];
		if ($_POST['CODE'] == 'APPROVE_AGENDA') {
			$noticeText = Loc::getMessage('NOTIFICATION_TEXT_APPROVE', array('#TITLE#' => $meetingTitle, '#ID#' => $_POST['MEETING_ID']));
		} else {
			$noticeText = Loc::getMessage('NOTIFICATION_TEXT_REJECT', array('#TITLE#' => $meetingTitle, '#ID#' => $_POST['MEETING_ID']));
		}

		foreach ($arUsers as $key => $usId) {
			$arMessageFields = array(
				'TO_USER_ID' => $usId,
				'FROM_USER_ID' => $userId,
				'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM,
				// 'NOTIFY_TYPE' => IM_NOTIFY_FROM,
				'NOTIFY_MODULE' => "meeting",
				'NOTIFY_MESSAGE' => $noticeText,
			);
			CIMNotify::Add($arMessageFields);
		}
	}

	if ($_POST['CODE'] == 'APPROVE_AGENDA') {
		$res = CIBlockElement::GetList(array(), array('PROPERTY_MEETING_ID' => $_POST['MEETING_ID'], 'IBLOCK_ID' => 29), false, false, array('ID', 'IBLOCK_ID'));
		$arElement = $res->GetNext();

		if ($arElement['ID']) {
			$el = new CIBlockElement;

			$arFields = array(
				'PROPERTY_VALUES' => array(
					'MEETING_ID' => $_POST['MEETING_ID'],
					'AGENDA_AGREE' => 79,
				),
			);

			$res = $el->Update($arElement['ID'], $arFields);
		}
	}
	$arResult['CHECK'] = 'SUCCESS';
	$arResult['MESSAGE'] = 'SUCCESS';
} else {
	$arResult['CHECK'] = 'ERROR';
	$arResult['MESSAGE'] = 'Meeting ID is not defined';
}

echo CUtil::PhpToJSObject($arResult);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');