<? use Bitrix\Main\Localization\Loc;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

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

CJSCore::Init(array("jquery"));


?>

<?
$APPLICATION->IncludeComponent(
	"b24tech:voting.result", "", array(
		"SECTION_ID" => $arResult["SECTION_ID"],		
		"MEETING_ID" => $arResult["MEETING_ID"],		
	)
);
?>
<br/>
<div id="js-voting">
	<a class="webform-small-button webform-small-button-accept js-finish-voting" href="javascript:void(0)" >
		<span class="webform-small-button-left"></span>
		<span class="webform-small-button-text">Завершить голосование</span>
		<span class="webform-small-button-right"></span>
	</a>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		var bulletin = new VoitingBulletin(<?=$arResult["USER_ID"];?>, <?=$arResult["MEETING_ID"];?>, <?=$arResult["SECTION_ID"];?>, 'online');
	});
</script>