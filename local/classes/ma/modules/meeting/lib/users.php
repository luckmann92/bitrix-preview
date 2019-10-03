<?php
namespace MA\Meeting;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class UsersTable
 * 
 * Fields:
 * <ul>
 * <li> MEETING_ID int mandatory
 * <li> USER_ID int mandatory
 * <li> USER_ROLE string(1) optional default 'M'
 * </ul>
 *
 * @package Bitrix\Meeting
 **/

class UsersTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_meeting_users';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'MEETING_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'title' => Loc::getMessage('USERS_ENTITY_MEETING_ID_FIELD'),
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'title' => Loc::getMessage('USERS_ENTITY_USER_ID_FIELD'),
			),
			'USER_ROLE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateUserRole'),
				'title' => Loc::getMessage('USERS_ENTITY_USER_ROLE_FIELD'),
			),
		);
	}
	/**
	 * Returns validators for USER_ROLE field.
	 *
	 * @return array
	 */
	public static function validateUserRole()
	{
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}
}