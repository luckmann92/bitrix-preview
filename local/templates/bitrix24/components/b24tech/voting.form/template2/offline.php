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

$answers = array(
	array('TITLE'=> 'За', 'VALUE' => 'Y'),
	array('TITLE'=> 'Против', 'VALUE' => 'N'),
	array('TITLE'=> 'Воздержался', 'VALUE' => 'A'),
);

?>
<?if(!empty($arResult['QUESTIONS'])):?>
	<? $rand = rand(); ?>
	<div id="js-voting-<?=$rand?>" class="form-questions">
		<input type="hidden" name="member" id="js-member-<?=$rand?>" value="<?=$arParams['USER_ID']?>" />
		<div class="js-messages"></div>
		<?foreach($arResult['QUESTIONS'] as $question):?>
			<div class="question-row js-question-row">
				<?foreach($answers as $answer):?>
					<div class="form-check">
					  <label class="form-check-label">
						  <input 
						  	class="form-check-input" 
						  	type="radio" 
						  	name="[<?=$arParams['USER_ID']?>]question-<?=$question['ID']?>" 
						  	id="id-question-<?=$question['ID']?>-<?=$arParams['USER_ID']?>" 
						  	value="<?=$answer['VALUE']?>"
						  	data-id="<?=$question['ID']?>" 
						  	data-section="<?=$question['IBLOCK_SECTION_ID']?>" 
						  	data-meeting="<?=$question['PROPERTY_MEETING_ID_VALUE']?>" 
						  	data-instance="<?=$question['PROPERTY_ID_INSTANCE_VALUE']?>" 
						  	data-parent-instance="<?=$question['PROPERTY_INSTANCE_PARENT_ID_VALUE']?>" 
						  	data-title="<?=$question['NAME']?>"
						  	data-user="<?=$arParams['USER_ID']?>"
						  	<?if($answer['VALUE'] == 'A'):?>checked<?endif;?>
						  	>
					  	<?=$answer['TITLE']?>
					  </label>
					</div>
				<?endforeach;?>
			</div>	
		<?endforeach;?>
		<a class="webform-small-button webform-small-button-accept js-submit-button" href="javascript:void(0)" >
			<span class="webform-small-button-left"></span>
			<span class="webform-small-button-text">Проголосовать</span>
			<span class="webform-small-button-right"></span>
		</a>
	</div>

	<script type="text/javascript">
		$(document).ready(function() {
			var bulletin = new VoitingBulletin(<?=$arResult["USER_ID"];?>, <?=$arResult["MEETING_ID"];?>, <?=$arResult["SECTION_ID"];?>, 'offline', '<?=$rand?>');
		});
	</script>
<?endif;?>