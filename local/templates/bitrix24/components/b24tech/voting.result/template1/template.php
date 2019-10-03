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
global $USER;
$userId = $USER->GetID();
$bShowVotingForm = false;
?>

<?if ($arResult["INSTANCES"]) {?>
    <div class="result-grid js-results">
        <table class="table-result" >
            <thead class="result-grid-head">
            <tr>
                <th class="head-item name-question order-item">№ п/п</th>
                <th class="head-item name-question">Пункт решения/Участник</th>
                <?foreach($arResult['USERS'] as $user):?>
                    <th class="head-item"><?=$user?></th>
                <?endforeach;?>
            </tr>
            </thead>
            <tbody class="result-grid-body">
            <?$index = 1;?>
            <?foreach($arResult["INSTANCES"] as $instanceParent):?>
                <tr>
                    <td class="row parent body-item order-item">
                        <strong>Вопрос <?=$index?></strong>
                    </td>
                    <td class="row parent body-item name-question parent-question" colspan="<?=count($arResult['USERS'])+1;?>">
                        <strong><?=$instanceParent['TITLE']?></strong>
                    </td>
                </tr>
                <?$sub_index = 1;?>
                <?foreach($instanceParent['ITEMS'] as $instance):?>
                    <tr class="row">
                        <td class="body-item order-item">Проект решения <?=$sub_index?></td>
                        <td class="body-item name-question"><?=$instance['TITLE']?></td>
                        <?foreach($arResult['USERS'] as $id => $user):?>
                            <td class="body-item">
                                <? if ($arResult['ANSWERS'][$id][$instance['ID']]['UF_VALUE']): ?>
                                    <?if($arResult['ANSWERS'][$id][$instance['ID']]['UF_VALUE'] == "Y"):?>
                                        <img src="/upload/voting-icons/y.png" class="answer-icon">
                                    <?endif;?>
                                    <?if($arResult['ANSWERS'][$id][$instance['ID']]['UF_VALUE'] == "N"):?>
                                        <img src="/upload/voting-icons/n.png" class="answer-icon">
                                    <?endif;?>
                                    <?if($arResult['ANSWERS'][$id][$instance['ID']]['UF_VALUE'] == "A"):?>
                                        <img src="/upload/voting-icons/a.png" class="answer-icon">
                                    <?endif;?>
                                <? else: ?>
                                    <? $bShowVotingForm = true; ?>
                                <? endif; ?>
                            </td>
                        <?endforeach;?>
                    </tr>
                    <?$sub_index++?>
                <?endforeach;?>
                <?unset($sub_index)?>
                <?$index++?>
            <?endforeach;?>
            </tbody>
        </table>
    </div>
<?}

?>
<?
if ('C' != $arResult['MEETING_CURRENT_STATE'] &&
    ($userId == $arResult['OWNER_USER_ID'] || $userId == $arResult['KEEPER_USER_ID']) &&
    (0 < count($arResult['ADDITIONAL_USERS']) || $bShowVotingForm)): ?>
    <div class="result-grid edit-form js-results-edit">
        <table class="table-result" >
            <thead class="result-grid-head">
            <tr>
                <th class="head-item name-question order-item">№ п/п</th>
                <th class="head-item name-question">Пункт решения/Участник</th>
                <?foreach($arResult['USERS'] as $user):?>
                    <th class="head-item"><?=$user?></th>
                <?endforeach;?>
                <?foreach($arResult['ADDITIONAL_USERS'] as $user):?>
                    <th class="head-item"><?=$user?></th>
                <?endforeach;?>
            </tr>
            </thead>
            <tbody class="result-grid-body">
            <?$index = 1;?>
            <?foreach($arResult["INSTANCES"] as $instanceParent):?>
                <tr>
                    <td class="row parent parent-question order-item">
                        <strong>Вопрос <?=$index?></strong>
                    </td>
                    <td class="row parent body-item name-question parent-question" colspan="<?=count($arResult['USERS'])+count($arResult['ADDITIONAL_USERS'])+1;?>">
                        <strong><?=$instanceParent['TITLE']?></strong>
                    </td>
                </tr>
                <?$sub_index = 1;?>
                <?foreach($instanceParent['ITEMS'] as $instance):?>
                    <tr class="row">
                        <td class="body-item name-question order-item">Проект решения <?=$sub_index?></td>
                        <td class="body-item name-question"><?=$instance['TITLE']?></td>

                        <?foreach($arResult['USERS'] as $id => $user):?>
                            <td class="body-item">
                                <?if($arResult['ANSWERS'][$id][$instance['ID']]['UF_VALUE']): ?>
                                    <?if($arResult['ANSWERS'][$id][$instance['ID']]['UF_VALUE'] == "Y"):?>
                                        <img src="/upload/voting-icons/y.png" class="answer-icon">
                                    <?endif;?>
                                    <?if($arResult['ANSWERS'][$id][$instance['ID']]['UF_VALUE'] == "N"):?>
                                        <img src="/upload/voting-icons/n.png" class="answer-icon">
                                    <?endif;?>
                                    <?if($arResult['ANSWERS'][$id][$instance['ID']]['UF_VALUE'] == "A"):?>
                                        <img src="/upload/voting-icons/a.png" class="answer-icon">
                                    <?endif;?>
                                <?else:?>
                                    <?$APPLICATION->IncludeComponent(
                                        "b24tech:voting.form", "template2", array(
                                            "INSTANCE_ID" => $instance["ID"],
                                            "MEETING_ID" => $instance["MEETING_ID"],
                                            "INSTANCE_PARENT_ID" => $instance["INSTANCE_PARENT_ID"],
                                            "IS_QUESTION" => 'Y',
                                            "USER_ID" => $id,
                                        )
                                    );?>
                                <?endif;?>
                            </td>
                        <?endforeach;?>
                        <?foreach($arResult['ADDITIONAL_USERS'] as $id => $user):?>
                            <td class="body-item">
                                <?if($arResult['ANSWERS'][$id][$instance['ID']]['UF_VALUE']): ?>
                                    <?if($arResult['ANSWERS'][$id][$instance['ID']]['UF_VALUE'] == "Y"):?>
                                        <img src="/upload/voting-icons/y.png" class="answer-icon">
                                    <?endif;?>
                                    <?if($arResult['ANSWERS'][$id][$instance['ID']]['UF_VALUE'] == "N"):?>
                                        <img src="/upload/voting-icons/n.png" class="answer-icon">
                                    <?endif;?>
                                    <?if($arResult['ANSWERS'][$id][$instance['ID']]['UF_VALUE'] == "A"):?>
                                        <img src="/upload/voting-icons/a.png" class="answer-icon">
                                    <?endif;?>
                                <?else:?>
                                    <?$APPLICATION->IncludeComponent(
                                        "b24tech:voting.form", "template2", array(
                                            "INSTANCE_ID" => $instance["ID"],
                                            "MEETING_ID" => $instance["MEETING_ID"],
                                            "INSTANCE_PARENT_ID" => $instance["INSTANCE_PARENT_ID"],
                                            "IS_QUESTION" => 'Y',
                                            "USER_ID" => $id,
                                        )
                                    );?>
                                <?endif;?>
                            </td>
                        <?endforeach;?>
                    </tr>
                    <?$sub_index++?>
                <?endforeach;?>
                <?$index++?>
            <?endforeach;?>
            </tbody>
        </table>
    </div>
    <a class="webform-small-button webform-small-button-accept js-voiting-button" href="javascript:void(0)">
        <span class="webform-small-button-left"></span>
        <span class="webform-small-button-text">Ввести результаты голосования</span>
        <span class="webform-small-button-right"></span>
    </a>
<? endif; ?>