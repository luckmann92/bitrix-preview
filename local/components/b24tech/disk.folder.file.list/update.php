<?php
define("STOP_STATISTICS", true);
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");


if($_REQUEST['folder'] > 0) {
	$APPLICATION->IncludeComponent(
		'b24tech:disk.file.list',
		'list',
		array(
			'FOLDER_ID' => $_REQUEST['folder'],
			'NO_IMAGES' => true,	
		),
		false,
		array("HIDE_ICONS" => "Y")
	);
}
