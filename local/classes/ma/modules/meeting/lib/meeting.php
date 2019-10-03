<?php
namespace MA\Meeting;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class MeetingTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TIMESTAMP_X datetime optional default 'CURRENT_TIMESTAMP'
 * <li> EVENT_ID int optional
 * <li> DATE_START datetime optional
 * <li> DATE_FINISH datetime optional
 * <li> DURATION int optional
 * <li> CURRENT_STATE string(1) optional default 'P'
 * <li> TITLE string(255) mandatory
 * <li> GROUP_ID int optional
 * <li> PARENT_ID int optional
 * <li> DESCRIPTION string optional
 * <li> PLACE string(255) optional
 * <li> PROTOCOL_TEXT string optional
 * <li> COLLEGIATE int optional
 * </ul>
 *
 * @package Bitrix\Meeting
 **/

class MeetingTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_meeting';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('MEETING_ENTITY_ID_FIELD'),
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('MEETING_ENTITY_TIMESTAMP_X_FIELD'),
			),
			'EVENT_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('MEETING_ENTITY_EVENT_ID_FIELD'),
			),
			'DATE_START' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('MEETING_ENTITY_DATE_START_FIELD'),
			),
			'DATE_FINISH' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('MEETING_ENTITY_DATE_FINISH_FIELD'),
			),
			'DURATION' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('MEETING_ENTITY_DURATION_FIELD'),
			),
			'CURRENT_STATE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCurrentState'),
				'title' => Loc::getMessage('MEETING_ENTITY_CURRENT_STATE_FIELD'),
			),
			'TITLE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateTitle'),
				'title' => Loc::getMessage('MEETING_ENTITY_TITLE_FIELD'),
			),
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('MEETING_ENTITY_GROUP_ID_FIELD'),
			),
			'PARENT_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('MEETING_ENTITY_PARENT_ID_FIELD'),
			),
			'DESCRIPTION' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('MEETING_ENTITY_DESCRIPTION_FIELD'),
			),
			'PLACE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validatePlace'),
				'title' => Loc::getMessage('MEETING_ENTITY_PLACE_FIELD'),
			),
			'PROTOCOL_TEXT' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('MEETING_ENTITY_PROTOCOL_TEXT_FIELD'),
			),
			'COLLEGIATE' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('MEETING_ENTITY_COLLEGIATE_FIELD'),
			),
		);
	}
	/**
	 * Returns validators for CURRENT_STATE field.
	 *
	 * @return array
	 */
	public static function validateCurrentState()
	{
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}
	/**
	 * Returns validators for TITLE field.
	 *
	 * @return array
	 */
	public static function validateTitle()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for PLACE field.
	 *
	 * @return array
	 */
	public static function validatePlace()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
}