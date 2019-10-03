<?php
define("STOP_STATISTICS", true);
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->IncludeComponent(
	"b24tech:voting.form", "", array( 
		"ACTION" => $_REQUEST['action'],
		"ANSWERS" => $_REQUEST['answers'],		
		"USER_ID" => $_REQUEST['user_id'],		
		"MEETING_ID" => $_REQUEST['meeting_id'],		
		"SECTION_ID" => $_REQUEST['section_id'],		
	)
);