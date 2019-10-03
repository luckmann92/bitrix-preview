<?php
namespace MA\Meeting;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class InstanceTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ITEM_ID int mandatory
 * <li> MEETING_ID int mandatory
 * <li> INSTANCE_PARENT_ID int optional
 * <li> INSTANCE_TYPE string(1) optional default 'A'
 * <li> ORIGINAL_TYPE string(1) optional default 'A'
 * <li> SORT int optional default 500
 * <li> DURATION int optional
 * <li> DEADLINE datetime optional
 * <li> TASK_ID int optional
 * </ul>
 *
 * @package Bitrix\Meeting
 **/

class InstanceTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_meeting_instance';
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
				'title' => Loc::getMessage('INSTANCE_ENTITY_ID_FIELD'),
			),
			'ITEM_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('INSTANCE_ENTITY_ITEM_ID_FIELD'),
			),
			'MEETING_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('INSTANCE_ENTITY_MEETING_ID_FIELD'),
			),
			'INSTANCE_PARENT_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('INSTANCE_ENTITY_INSTANCE_PARENT_ID_FIELD'),
			),
			'INSTANCE_TYPE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateInstanceType'),
				'title' => Loc::getMessage('INSTANCE_ENTITY_INSTANCE_TYPE_FIELD'),
			),
			'ORIGINAL_TYPE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateOriginalType'),
				'title' => Loc::getMessage('INSTANCE_ENTITY_ORIGINAL_TYPE_FIELD'),
			),
			'SORT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('INSTANCE_ENTITY_SORT_FIELD'),
			),
			'DURATION' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('INSTANCE_ENTITY_DURATION_FIELD'),
			),
			'DEADLINE' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('INSTANCE_ENTITY_DEADLINE_FIELD'),
			),
			'TASK_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('INSTANCE_ENTITY_TASK_ID_FIELD'),
			),
		);
	}
	/**
	 * Returns validators for INSTANCE_TYPE field.
	 *
	 * @return array
	 */
	public static function validateInstanceType()
	{
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}
	/**
	 * Returns validators for ORIGINAL_TYPE field.
	 *
	 * @return array
	 */
	public static function validateOriginalType()
	{
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}
}