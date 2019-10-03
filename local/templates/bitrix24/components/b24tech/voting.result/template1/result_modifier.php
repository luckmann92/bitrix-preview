<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

foreach ($arResult['INSTANCES'] as $instanceParent) {
	if (0 < $instanceParent['MEETING_ID']) {
		$arMeeting = MA\Meeting\MeetingTable::getList(array(
			'filter' => array('ID' => $instanceParent['MEETING_ID']),
		))->Fetch();
		$arResult['MEETING_CURRENT_STATE'] = $arMeeting['CURRENT_STATE'];
		$arUsers = MA\Meeting\UsersTable::getList(array(
			'filter' => array('MEETING_ID' => $instanceParent['MEETING_ID']),
		))->FetchAll();
		foreach ($arUsers as $arUser) {
			if (!isset($arResult['USERS'][$arUser['USER_ID']]) && 'O' != $arUser['USER_ROLE'] && 'K' != $arUser['USER_ROLE']) {
				$arUserInfo = CUser::GetById($arUser['USER_ID'])->Fetch();
				$fullName = '';
				if ($arUserInfo['NAME'])
					$fullName .= $arUserInfo['NAME'];
				if ($arUserInfo['LAST_NAME'])
					$fullName .= ' '.$arUserInfo['LAST_NAME'];

				if (0 >= strlen($fullName))
					$fullName = $arUserInfo['LOGIN'];

				$arResult['ADDITIONAL_USERS'][$arUserInfo['ID']] = $fullName;
			} elseif ('O' == $arUser['USER_ROLE']) {
				$arResult['OWNER_USER_ID'] = $arUser['USER_ID'];
			} elseif ('K' == $arUser['USER_ROLE']) {
				$arResult['KEEPER_USER_ID'] = $arUser['USER_ID'];
			}
		}
		break;
	}
}