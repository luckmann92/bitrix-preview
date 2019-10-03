<?php
define("STOP_STATISTICS", true);
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$voting_element_id = $_GET['id'];
$block_id = 30;
//Если ИБ может быть другим, раскомментировать, проверит id ИБ по id элемента
//$block_id = CIBlockElement::GetIBlockByID($voting_element_id); // отдает id инфоблока '30'

$IsProp = CIBlockElement::GetProperty($block_id, $voting_element_id, "sort", "asc", array("NAME" => "AGENDA_OFF_VOTING"));
$propValue = $IsProp->Fetch();

echo $propValue['VALUE'];