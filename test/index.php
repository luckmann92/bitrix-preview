<?
use Bitrix\Disk\Internals\ObjectTable;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Доставка");

\Bitrix\Main\Loader::includeModule('disk');
?>
<?
$folder = \Bitrix\Disk\Folder::loadById(563);
$folderSecurityContext = $folder->getStorage()->getCurrentUserSecurityContext();

foreach($folder->getChildren(
	$folderSecurityContext, 
	array(
		'filter' => array(
			'TYPE' => ObjectTable::TYPE_FILE, 
		)
	)) as $fileModel)
{
	print_r($fileModel->getName());
	echo '<br>';
}
?>
http://31.177.78.153/bitrix/services/main/ajax.php?action=disk.api.folder.downloadArchive&folderId=563
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
