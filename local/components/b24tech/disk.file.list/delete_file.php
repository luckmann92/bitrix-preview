<?php
use Bitrix\Disk\File;

define("STOP_STATISTICS", true);
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
\Bitrix\Main\Loader::includeModule('disk');
$result = array(
	'success' => false,
);
if($_REQUEST['file'] > 0) {
	$file = File::loadById((int)$_REQUEST['file'], array('STORAGE'));
	
	global $USER;
	if(!$file->delete($USER->GetId()))
	{
		$result['error_message'] = $file->getErrors();
	} else {
		$result['success'] = true;
	}
}

header('Content-Type: application/json');
echo json_encode($result);
