<?php
use Bitrix\Disk\Uf\LocalDocumentController;
use Bitrix\Disk\Internals\ObjectTable;
use Bitrix\Disk\ZipNginx;

define("STOP_STATISTICS", true);
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if(!\Bitrix\Main\Loader::includeModule('disk'))
{
	die;
}

if(!($_REQUEST['folder'] > 0)) {
	die();
}

if(!ZipNginx\Configuration::isEnabled())
{
	die('Work with mod_zip is disabled in module settings.');
}

$folder = \Bitrix\Disk\Folder::loadById($_REQUEST['folder']);
$folderSecurityContext = $folder->getStorage()->getCurrentUserSecurityContext();

//$zipArchive = new ZipNginx\Archive('archive' . date('y-m-d') . '.zip');
$zipArchive = new ZipNginx\Archive($folder->getName().'.zip');

foreach($folder->getChildren(
	$folderSecurityContext, 
	array(
		'filter' => array(
			'TYPE' => ObjectTable::TYPE_FILE, 
		)
	)) as $fileModel)
{
	$zipArchive->addEntry(
		ZipNginx\ArchiveEntry::createFromFile($fileModel)
	);
}

if ($zipArchive->isEmpty())
{
	die('Archive is empty');
}

$zipArchive->send();

/** @noinspection PhpUndefinedClassInspection */
/*\CMain::finalActions();
die;*/