<?php
use Bitrix\Disk\Configuration;
use Bitrix\Disk\Document\GoogleHandler;
use Bitrix\Disk\Document\LocalDocumentController;
use Bitrix\Disk\File;
use Bitrix\Disk\Internals\BaseComponent;
use Bitrix\Disk\Document\DocumentHandler;
use Bitrix\Disk\Uf\FileUserType;
use Bitrix\Disk\Ui;
use Bitrix\Disk\Internals\ObjectTable;
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

\Bitrix\Main\Loader::includeModule('highloadblock');
\Bitrix\Main\Loader::includeModule('iblock');

class VotingResultComponent extends CBitrixComponent
{
	protected $iblock_id = 30;
	protected $answersHL = 2;

	public function executeComponent()
	{
        if ($this->arParams['MEETING_ID']) {
            $arUsers = array();
            $rsUsers = CMeeting::GetUsers($this->arParams['MEETING_ID']);
            foreach ($rsUsers as $id => $role) {
                if ($role != 'K') {
                    $rsUser = CUser::GetByID($id)->Fetch();
                    $arUsers[$id] = $rsUser['NAME'] . ' ' . $rsUser['LAST_NAME'];
                }
            }
        }

		if($this->arParams['SECTION_ID']) {
			$answers = $this->getAnswers($this->arParams['SECTION_ID']);

			$users = array();
			$userAnswers = array();
			$parentInstance = 0;
			foreach($answers as $answer) {
				$users[$answer['UF_USER_ID']] = $answer['UF_USER_TITLE'];
				$userAnswers[$answer['UF_USER_ID']][$answer['UF_ID_INSTANCE']] = $answer;
				$parentInstance = $answer['UF_PARENT_INSTANCE'];
			}
			unset($answers);
			if(!empty($userAnswers)) {
				$this->arResult["INSTANCES"] = $this->getInstances($this->arParams['MEETING_ID'], $parentInstance);
				$this->arResult["ANSWERS"] = $userAnswers;
				//$this->arResult["USERS"] = $users;
                $this->arResult["USERS"] = !empty($arUsers) ? $arUsers : $users;
				//$this->includeComponentTemplate();
			}

		} else if($this->arParams['MEETING_ID'] > 0 && $this->arParams['COMMON_RESULT'] == 'Y') {
			$answers = $this->getAnswersByMeetingId($this->arParams['MEETING_ID']);

			$users = array();
			$userAnswers = array();
			$parentInstance = 0;
			foreach($answers as $answer) {
				$users[$answer['UF_USER_ID']] = $answer['UF_USER_TITLE'];
				$userAnswers[$answer['UF_USER_ID']][$answer['UF_ID_INSTANCE']] = $answer;
				$parentInstance = $answer['UF_PARENT_INSTANCE'];
			}
			unset($answers);
			//if(!empty($userAnswers)) {
				$this->arResult["INSTANCES"] = $this->getAllInstances($this->arParams['MEETING_ID']);
				$this->arResult["ANSWERS"] = $userAnswers;
				//$this->arResult["USERS"] = $users;
				$this->arResult["USERS"] = !empty($arUsers) ? $arUsers : $users;

			//}
		}

        $this->includeComponentTemplate();
	}

	protected function getAnswers($sectionId) 
	{
		$answers = array();

		$arHLBlock = \Bitrix\Highloadblock\HighloadBlockTable::getById($this->answersHL)->fetch();
	    $obEntity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arHLBlock);
	    $strEntityDataClass = $obEntity->getDataClass();

	    $filter = array(
	    	'UF_SECTION_ID'=> $sectionId,
	    );
	    
	    $rsData = $strEntityDataClass::getList(array(
	       'order' => array('ID' => 'ASC'),
	       'filter' => $filter,
	    ));

	    while($arItem = $rsData->Fetch()) {
	       $answers[] = $arItem;
	    }

	    return $answers;
	}

	protected function getAnswersByMeetingId($meetingId) 
	{
		$answers = array();

		$arHLBlock = \Bitrix\Highloadblock\HighloadBlockTable::getById($this->answersHL)->fetch();
	    $obEntity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arHLBlock);
	    $strEntityDataClass = $obEntity->getDataClass();

	    $filter = array(
	    	'UF_MEETING_ID'=> $meetingId,
	    );
	    
	    $rsData = $strEntityDataClass::getList(array(
	       'order' => array('ID' => 'ASC'),
	       'filter' => $filter,
	    ));

	    while($arItem = $rsData->Fetch()) {
	       $answers[] = $arItem;
	    }

	    return $answers;
	}

	protected function getInstances($meetingId, $parentInstance) 
	{
		$instances = array();
		
		$res = CMeeting::GetItems($meetingId);
		while ($item = $res->Fetch()) {
			if($item['INSTANCE_PARENT_ID'] == $parentInstance) {
				$instances[$item["ID"]] = $item;
			}
		}
		
		return $instances;
	}

	protected function getAllInstances($meetingId) 
	{
		$instances = array();
		
		$res = CMeeting::GetItems($meetingId);
		while ($item = $res->Fetch()) {
			if(empty($item['INSTANCE_PARENT_ID'])) {
				$instances[$item["ID"]] = $item;
			} else {
				$instances[$item['INSTANCE_PARENT_ID']]['ITEMS'][$item["ID"]] = $item;
			}
		}
		
		return $instances;
	}
}