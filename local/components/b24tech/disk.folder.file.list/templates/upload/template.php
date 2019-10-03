<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
/** @var $this \CBitrixComponentTemplate */
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var \Bitrix\Disk\Internals\BaseComponent $component */
$driver = \Bitrix\Disk\Driver::getInstance(); 
$storage = $driver->getStorageByUserId($arResult['STORAGE_ID']);
$folder = \Bitrix\Disk\Folder::loadById($arResult['FOLDER']);

?>

<div id='js-file-list'>
<?
$APPLICATION->IncludeComponent(
	'b24tech:disk.file.list',
	'list',
	array(
		'FOLDER_ID' => $arResult['FOLDER'],
		'NO_IMAGES' => true,
		'TYPE' => $arParams['TYPE'],
	),
	false,
	array("HIDE_ICONS" => "Y")
);
?>
</div>

<div class="webform-field-upload" id="mfi-MEETING_DESCRIPTION-button" onclick="openFileUpload();">
	<span class="webform-small-button webform-button-upload">Добавить файл</span>
	<input type="file" id="file_input_MEETING_DESCRIPTION" multiple="multiple" name="bxu_files[]">
</div>


<?$APPLICATION->IncludeComponent(
	'bitrix:disk.file.upload',
	'',
	array(
		'STORAGE' => $storage,
		'FOLDER' => $folder,
		'CID' => 'FolderList',
		'INPUT_CONTAINER' => 'BX("file_input_MEETING_DESCRIPTION")',
		'DROPZONE' => 'BX("mfi-MEETING_DESCRIPTION-button")'
	),
	false,
	array("HIDE_ICONS" => "Y")
);?>

<script type="text/javascript">
	function openFileUpload() {
		BX('file_input_MEETING_DESCRIPTION').click();
	}


	function updateFileList(folderId) {;
		BX.showWait();
		BX.ajax.get(
		 '/local/components/b24tech/disk.folder.file.list/update.php',
		 { folder: folderId },
		 function (res) {
		 	BX.closeWait();
		 	console.log(res);
		 	BX('js-file-list').innerHTML = res;
		 }
		);
	}
	
	BX.addCustomEvent("onPopupFileUploadClose", BX.delegate(function(command,params){
	   console.log('Events of moduleName', command, params);
	   updateFileList(<?=$arResult['FOLDER'];?>);
	}, this));
</script>	