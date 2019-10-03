<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/timeman/meeting/index.php");
$APPLICATION->SetTitle(GetMessage("SERVICES_TITLE"));?><?$APPLICATION->IncludeComponent(
	"b24tech:meetings", 
	"template1", 
	array(
		"COMPONENT_TEMPLATE" => "template1",
		"MEETINGS_COUNT" => "20",
		"RESERVE_MEETING_IBLOCK_ID" => "14",
		"RESERVE_MEETING_IBLOCK_TYPE" => "events",
		"RESERVE_VMEETING_IBLOCK_ID" => "14",
		"RESERVE_VMEETING_IBLOCK_TYPE" => "events",
		"SEF_FOLDER" => "/timeman/meeting/",
		"SEF_MODE" => "Y",
		"SEF_URL_TEMPLATES" => array(
			"list" => "",
			"meeting" => "#MEETING_ID#/",
			"meeting_edit" => "meeting/#MEETING_ID#/edit/",
			"meeting_copy" => "meeting/#MEETING_ID#/copy/",
			"item" => "item/#ITEM_ID#/",
		)
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>