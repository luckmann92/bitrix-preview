<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arRequest = array(
    'MEETING_ID' => $_REQUEST['meet_id'],
);

$arResult = array();
$arResult['MEETING'] = CMeeting::GetByID($arRequest['MEETING_ID'])->Fetch();
//$arResult['MEETING']['ITEMS'] = CMeetingItem::GetList();

