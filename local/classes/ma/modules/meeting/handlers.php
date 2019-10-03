<?php
namespace MA\Meeting;

use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class MeetingHandlers
{
	private static $IBLOCK_ID = 29;

	/**
	 * @param $arMeetingFields array - new metting fields
	 */
	public static function addIblockElementAfterMeetingAdd ($arMeetingFields) {
		Loader::includeModule('iblock');

		$el = new \CiblockElement();
		$arFields = array(
			'IBLOCK_ID' => self::$IBLOCK_ID,
			'NAME' => Loc::getMessage('MA_MEETING_HANDLERS_DEFAULT_NAME', array('#MEETING_ID#' => $arMeetingFields['ID'])),
			'ACTIVE' => 'Y',
			'PROPERTY_VALUES' => array(
				'MEETING_ID' => $arMeetingFields['ID'],
				'AGENDA_AGREE' => 79,
			),
		);

		$el->Add($arFields);
	}
}