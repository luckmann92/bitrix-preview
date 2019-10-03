<? use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

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
    array('TITLE' => 'За', 'VALUE' => 'Y'),
    array('TITLE' => 'Против', 'VALUE' => 'N'),
    array('TITLE' => 'Воздержался', 'VALUE' => 'A'),
);


?>


<? if (!empty($arResult['QUESTIONS'])): ?>
    <h2>Бюллетень</h2>
    <div id="js-voting" class="form-questions">
        <? foreach ($arResult['QUESTIONS'] as $question): ?>
            <div class="question-row">
                <div class="question-title"><?= $question['NAME']; ?>:</div>
                <? foreach ($answers as $k => $answer):?>
                    <div class="form-check">
                        <input
                            <?=$arResult['VOTING']['UF_STATUS'] == 'FINISHED' ? 'disabled' : ''?>
                                class="form-check-input"
                                type="radio"
                                name="question-<?= $question['ID'] ?>"
                                id="id-question-<?=$k?>-<?=$question['ID']?>"
                                value="<?= $answer['VALUE'] ?>"
                                data-id="<?= $question['ID'] ?>"
                                data-section="<?= $question['IBLOCK_SECTION_ID'] ?>"
                                data-meeting="<?= $question['PROPERTY_MEETING_ID_VALUE'] ?>"
                                data-instance="<?= $question['PROPERTY_ID_INSTANCE_VALUE'] ?>"
                                data-parent-instance="<?= $question['PROPERTY_INSTANCE_PARENT_ID_VALUE'] ?>"
                                data-title="<?= $question['NAME'] ?>"
                                <? if ($answer['VALUE'] == 'A'): ?>checked<? endif; ?>
                        >
                        <label class="form-check-label" for="id-question-<?=$k?>-<?=$question['ID']?>">
                            <?= $answer['TITLE'] ?>
                        </label>
                    </div>
                <? endforeach; ?>
            </div>
        <? endforeach; ?>

        <?if ($arResult['VOTING']['UF_STATUS'] != 'FINISHED') {?>
            <a class="webform-small-button webform-small-button-accept js-submit-button" href="javascript:void(0)" >
                <span class="webform-small-button-left"></span>
                <span class="webform-small-button-text">Проголосовать</span>
                <span class="webform-small-button-right"></span>
            </a>
        <?}else{?>
            <div class="crm-list-stage-bar-title">Голосование завершено</div>
        <?}?>

    </div>

    <script type="text/javascript">
        $(document).ready(function () {
            var bulletin = new VoitingBulletin(<?=$arResult["USER_ID"];?>, <?=$arResult["MEETING_ID"];?>, <?=$arResult["SECTION_ID"];?>, 'online');
        });
    </script>
<? endif; ?>