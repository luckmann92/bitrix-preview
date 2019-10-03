<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var string $templateFolder
 * @global CMain $APPLICATION
 */

if ($arResult['INCLUDE_LANG'])
{
	\Bitrix\Main\Localization\Loc::loadLanguageFile(dirname(__FILE__)."/template.php");
}

?>
				<div class="meeting-detail-title"><?=htmlspecialcharsbx($arResult['ITEM']['TITLE'])?></div>
				<div class="meeting-detail-category"><?=htmlspecialcharsbx($arResult['ITEM']['CATEGORY'])?></div>
<?
if (strlen($arResult['ITEM']['DESCRIPTION']) > 0):
?>
				<div id="meeting-detail-description" class="meeting-detail-description"><?=$arResult['ITEM']['DESCRIPTION']?></div>
<?
endif;

?>

<div class="meeting-detail-files">
	<?
	$APPLICATION->IncludeComponent(
		'b24tech:disk.folder.file.list',
		'',
		array(
			'QUESTION_ID' => $arResult['ITEM']['INSTANCES'][0]['ID'],
			'TYPE' => 'question',
		),
		false,
		array("HIDE_ICONS" => "Y")
	);
	?>
</div>