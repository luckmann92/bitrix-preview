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

if (!Loader::includeModule('tasks')) {
	return CUtil::PhpToJSObject(array('CHECK' => 'ERROR', 'MESSAGE' => 'Module "tasks" not install.'));
	exit;
}

$arResult = array();
if ($_POST['data']['MEETING_ID'] > 0) {
	$filter = array(
		'MEETING_ID' => $_POST['data']['MEETING_ID'],
		'USER_ROLE' => 'O'
	);
	$select = array(
		'USER_ID'
	);
	$resUser = \MA\Meeting\UsersTable::getList(array(
		'filter' => $filter,
		'select' => $select,
	));
	$resultUser = $resUser->Fetch();

	$meetingTitle = '';
	if ($_POST['data']['TITLE']) {
		$meetingTitle = $_POST['data']['TITLE'];
	} else {
		$resMeeting = \MA\Meeting\MeetingTable::getList(array(
			'filter' => array('ID' => $_POST['data']['MEETING_ID']),
		));
		$arMeeting = $resMeeting->Fetch();
		$meetingTitle = $arMeeting['TITLE'];
	}


	$task = new \Bitrix\Tasks\Item\Task();
	$task['TITLE'] = Loc::getMessage('TASK_TITLE', array('#TITLE#' => $meetingTitle));
	$task['DESCRIPTION'] = Loc::getMessage('TASK_DESCRIPTION', array('#TITLE#' => $meetingTitle, '#ID#' => $_POST['data']['MEETING_ID']));
	$task['RESPONSIBLE_ID'] = $resultUser['USER_ID'];
	$task['SITE_ID'] = SITE_ID;
	$task['CREATED_BY'] = $userId;
	$task['UF_TYPE'] = 30;
	$task['UF_MEETING_ID'] = $_POST['data']['MEETING_ID'];

	$result = $task->save();
	if($result->isSuccess()) {
		$taskId = $task->getId();
		$arMessageFields = array(
			'TO_USER_ID' => $resultUser['USER_ID'],
			'FROM_USER_ID' => $userId,
			'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM,
			// 'NOTIFY_TYPE' => IM_NOTIFY_FROM,
			'NOTIFY_MODULE' => "meeting",
			'NOTIFY_MESSAGE' => Loc::getMessage('NOTIFICATION_TEXT', array('#TITLE#' => $meetingTitle, '#ID#' => $_POST['data']['MEETING_ID'], '#TASK_ID#' => $taskId)),
		);
		CIMNotify::Add($arMessageFields);
		$arResult['CHECK'] = 'SUCCESS';
		$arResult['MESSAGE'] = 'SUCCESS';
	} else {
		$arResult['CHECK'] = 'ERROR';
		$arResult['MESSAGE'] = $result->dump();
	}


} else {
	$arResult['CHECK'] = 'ERROR';
	$arResult['MESSAGE'] = 'Meeting ID is not defined';
}

echo CUtil::PhpToJSObject($arResult);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');