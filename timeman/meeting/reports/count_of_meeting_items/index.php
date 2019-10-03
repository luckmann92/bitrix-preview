<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
//$APPLICATION->SetTitle("Новая страница");
?><br>
 <?$APPLICATION->IncludeComponent(
	"b24tech:meet_report.count_of_meeting_items",
	'',
    Array()
);
 ?><br>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>