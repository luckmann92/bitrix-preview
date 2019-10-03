<?php
use Bitrix\Disk\Configuration;
use Bitrix\Disk\Driver;
use Bitrix\Disk\ExternalLink;
use Bitrix\Disk\File;
use Bitrix\Disk\FileLink;
use Bitrix\Disk\Folder;
use Bitrix\Disk\FolderLink;
use Bitrix\Disk\Internals\Error\Error;
use Bitrix\Disk\Internals\ExternalLinkTable;
use Bitrix\Disk\Internals\FolderTable;
use Bitrix\Disk\Internals\ObjectTable;
use Bitrix\Disk\Internals\SharingTable;
use Bitrix\Disk\BaseObject;
use Bitrix\Disk\Sharing;
use Bitrix\Disk\Storage;
use Bitrix\Disk\User;
use Bitrix\Disk\Ui;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Disk\Internals\Error\ErrorCollection;

\Bitrix\Main\Loader::includeModule('highloadblock');
\Bitrix\Main\Loader::includeModule('disk');
\Bitrix\Main\Loader::includeModule('meeting');

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

Loc::loadMessages(__FILE__);

class DiskFolderFileList extends CBitrixComponent
{
	protected $hlblock = 1;

	public function executeComponent()
	{
		if($this->arParams['TYPE'] == 'meeting' && $this->arParams['MEETING_ID'] > 0) {
			$folder = $this->getDiskFolder($this->arParams['MEETING_ID'], 'meeting');
		}

		if($this->arParams['TYPE'] == 'question' && $this->arParams['QUESTION_ID'] > 0) {
			$folder = $this->getDiskFolder($this->arParams['QUESTION_ID'], 'question');
		}

		if($folder) {
         $this->arResult['FOLDER'] = $folder['UF_ID_FOLDER'];
			$this->arResult['STORAGE_ID'] = $folder['UF_ID_STORAGE'];
			$this->includeComponentTemplate();
		}
	}

	public function getDiskFolder($id, $type)
   {
      $arHLBlock = \Bitrix\Highloadblock\HighloadBlockTable::getById($this->hlblock)->fetch();
      $obEntity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arHLBlock);
      $strEntityDataClass = $obEntity->getDataClass();

      if($type == 'meeting') {
         $filter = array('UF_MEETING' => $id, 'UF_TYPE'=>'meeting');
      }
      if($type == 'question') {
         $filter = array('UF_QUESTION' => $id, 'UF_TYPE'=>'question');
      }

      $rsData = $strEntityDataClass::getList(array(
         'order' => array('ID' => 'ASC'),
         'filter' => $filter,
      ));

      if ($arItem = $rsData->Fetch()) {
         return $arItem;
      }

      return null;
   }
}