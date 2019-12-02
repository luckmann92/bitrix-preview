<?php
namespace MA\Tasks;

use MA\Meeting\MeetingTable,
	Bitrix\Main\Loader;

class TasksHandlers {

	/**
	 * @param $taskFields array - new task fields array
	 * @return array - modify new task fields array
	 */
	public static function onBeforeTaskAddHandler (&$taskFields) {
		if ($_POST['MEETING_ID'] > 0) {
			$arMeeting = MeetingTable::getList(array(
				'filter' => array('ID' => $_POST['MEETING_ID']),
				'select' => array('ID', 'TITLE'),
			))->FetchAll();
			$arMeeting = current($arMeeting);
			if ($arMeeting['ID'] > 0) {
				$taskFields['UF_MEETING_ID'] = $arMeeting['ID'];
			}
			if ($arMeeting['TITLE']) {
				$taskFields['UF_MEETING_SUBJECT'] = $arMeeting['TITLE'];
			}
		}
	}

	/**
	 * @param $arItemFields array - agenda item fields array
	 */
	public static function onAfterMeetingItemAddHandler ($arItemFields) {

		if ($arItemFields['MEETING_ID'] > 0 && $arItemFields['TASK_ID'] > 0) {
			$arMeeting = MeetingTable::getList(array(
				'filter' => array('ID' => $arItemFields['MEETING_ID']),
				'select' => array('ID', 'TITLE'),
			))->FetchAll();
			$arMeeting = current($arMeeting);
			
			Loader::includeModule('tasks');
			$arFields = array(
				'UF_MEETING_ID' => $arMeeting['ID'],
				'UF_MEETING_SUBJECT' => $arMeeting['TITLE'],
			);
			global $USER;
			$userId = $USER->GetID();
			$oTaskItem = new \CTaskItem($arItemFields['TASK_ID'], $userId);
			$rs = $oTaskItem->Update($arFields);
		}

	}

	/**
	 * @param $arFields array - meeting instance fields
	 */
	public static function onAfterMeetingInstanceAddHandler ($arFields) {
		if (
		    $arFields['MEETING_ID'] > 0 && $arFields['ID'] > 0 && $arFields['TITLE']
            &&
            !empty($_REQUEST['AGENDA_RESPONSIBLE_CUSTOM'])
        )
		{
			$itemTmpKey = array_search($arFields['TITLE'], $_REQUEST['AGENDA_TITLE']);
			if (strlen($_REQUEST['AGENDA_RESPONSIBLE_CUSTOM'][$itemTmpKey]) > 0) {
				Loader::includeModule('iblock');
				$res = \CIBlockElement::GetList(
				    array(),
                    array(
                        'PROPERTY_MEETING_ID' => $arFields['MEETING_ID'],
                        'IBLOCK_ID' => 29
                    ),
                    false, false,
                    array(
                        'ID', 'IBLOCK_ID', 'PROPERTY_AGENDA_RESPONSIBLE'
                    )
                );
				$arElement = $res->fetch();
				if ($arElement['PROPERTY_AGENDA_RESPONSIBLE_VALUE']['TEXT']) {
					$arResponsibleCustom = unserialize($arElement['PROPERTY_AGENDA_RESPONSIBLE_VALUE']['TEXT']);
					$arResponsibleCustom[$arFields['ID']] = $_REQUEST['AGENDA_RESPONSIBLE_CUSTOM'][$itemTmpKey];
				} else {
					$arResponsibleCustom = array();
					$arResponsibleCustom['ID'] = $_REQUEST['AGENDA_RESPONSIBLE_CUSTOM'][$itemTmpKey];
				}
				if (!empty($arResponsibleCustom)) {
					\CIBlockElement::SetPropertyValues($arElement['ID'], $arElement['IBLOCK_ID'], serialize($arResponsibleCustom), 'AGENDA_RESPONSIBLE');
				}
			}
		}
	}

	/**
	 * @param $arFields array - meeting instance fields
	 */
	public static function onAfterMeetingInstanceUpdateHandler ($itemId, $arFields) {
		if ($arFields['MEETING_ID'] > 0 && $itemId > 0 && $arFields['TITLE'] && !empty($_REQUEST['AGENDA_RESPONSIBLE_CUSTOM'])) {
			$itemTmpKey = array_search($arFields['TITLE'], $_REQUEST['AGENDA_TITLE']);
			if (strlen($_REQUEST['AGENDA_RESPONSIBLE_CUSTOM'][$itemTmpKey]) > 0) {
				Loader::includeModule('iblock');
				$res = \CIBlockElement::GetList(array(), array('PROPERTY_MEETING_ID' => $arFields['MEETING_ID'], 'IBLOCK_ID' => 29), false, false, array('ID', 'IBLOCK_ID', 'PROPERTY_AGENDA_RESPONSIBLE'));
				$arElement = $res->fetch();
				if ($arElement['PROPERTY_AGENDA_RESPONSIBLE_VALUE']['TEXT']) {
					$arResponsibleCustom = unserialize($arElement['PROPERTY_AGENDA_RESPONSIBLE_VALUE']['TEXT']);
					$arResponsibleCustom[$itemTmpKey] = $_REQUEST['AGENDA_RESPONSIBLE_CUSTOM'][$itemTmpKey];
				} else {
					$arResponsibleCustom = array();
					$arResponsibleCustom[$itemTmpKey] = $_REQUEST['AGENDA_RESPONSIBLE_CUSTOM'][$itemTmpKey];
				}
				if (!empty($arResponsibleCustom)) {
					\CIBlockElement::SetPropertyValues($arElement['ID'], $arElement['IBLOCK_ID'], serialize($arResponsibleCustom), 'AGENDA_RESPONSIBLE');
				}
			}
		}
	}
}