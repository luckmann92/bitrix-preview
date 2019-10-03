<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Loader,
    \Bitrix\Main\Localization\Loc;

Loader::includeModule('iblock');
//
//var_dump($arResult['MEETING']['CURRENT_STATE']);

$arResult['CAN_EDIT'] = true;
if ($arResult['MEETING']['ID'] > 0) {
    $res = CIBlockElement::GetList(array(), array('IBLOCK_ID' => 29, 'PROPERTY_MEETING_ID' => $arResult['MEETING']['ID'],), false, false, array('ID', 'IBLOCK_ID', 'PROPERTY_*'));
    $ob = $res->GetNextElement();
    if ($ob) {
        $arMeetingProps = $ob->GetProperties();
        if (is_array($arMeetingProps['AGENDA_RESPONSIBLE']['~VALUE']) && $arMeetingProps['AGENDA_RESPONSIBLE']['~VALUE']['TEXT']) {
            $arMeetingProps['AGENDA_RESPONSIBLE']['VALUE'] = unserialize($arMeetingProps['AGENDA_RESPONSIBLE']['~VALUE']['TEXT']);
            if (is_array($arMeetingProps['AGENDA_RESPONSIBLE']['VALUE'])) {
                foreach ($arMeetingProps['AGENDA_RESPONSIBLE']['VALUE'] as $key => $responsible) {
                    if (is_array($arResult['MEETING']['AGENDA'][$key]))
                        $arResult['MEETING']['AGENDA'][$key]['RESPONSIBLE_CUSTOM'] = $responsible;
                }
            }
        }
    }
}

foreach ($arResult['MEETING']['USERS'] as $key => $role) {
    if ($role == 'O') {
        $ownerID = $arResult['USERS'][$key]['ID'];
        break;
    }
}

if ($_REQUEST["action"] == "generate_file") {
    switch ($_REQUEST["type"]) {
        case 'bpaper':
            include('generate_bpaper.php');
            break;

        case 'wopinion':
            include('generate_wopinion.php');
            break;

        case 'mprotocol':
            include('generate_mprotocol.php');
            break;
    }
    die;
}

$bTask = CBXFeatures::IsFeatureEnabled('tasks') && IsModuleInstalled('tasks');

Loc::loadLanguageFile(__FIтLE__);

$APPLICATION->IncludeComponent('bitrix:main.calendar', '', array('SILENT' => 'Y'));
if ($bTask) {
    $APPLICATION->IncludeComponent(
        "bitrix:tasks.iframe.popup",
        ".default",
        array(
            "ON_TASK_ADDED" => "BX.DoNothing",
            "ON_TASK_CHANGED" => "BX.DoNothing",
            "ON_TASK_DELETED" => "BX.DoNothing",
        ),
        null,
        array("HIDE_ICONS" => "Y")
    );
}
CJSCore::Init(array("jquery"));
/*echo '<pre>';
var_dump($arResult['MEETING']['CURRENT_STATE']);
echo '</pre>';*/
?>

<div class="webform-round-corners webform-additional-block webform-additional-block-topless">
    <div class="webform-content webform-content meeting-detail-agenda-protocol-active" id="agenda_block">
        <?
        $display = (!$arParams['COPY'] && $arResult['MEETING']['CURRENT_STATE'] && $arResult['MEETING']['CURRENT_STATE'] !== CMeeting::STATE_PREPARE) ? 'block' : 'none';
        //$OffAgendaQuestionDisplay = ($arResult['MEETING']['CURRENT_RIGHTS'] == 'M') ? 'voting-block' : '';
        //echo ($arResult['MEETING']['CURRENT_RIGHTS']);
        ?>
        <div class="meeting-detail-tabs-wrap" id="switcher" style="display: <?= $display; ?>">
            <? if ($arResult['MEETING']['CURRENT_STATE'] == 'P') { ?>
                <a class="meeting-detail-tab meeting-tab-active" id="switch_agenda" href="javascript:void(0)"
                   onclick="switchView('agenda', '<?= ($arResult['MEETING']['CURRENT_RIGHTS']) ?>'); return false;">
                    <span class="meeting-detail-tab-text meeting-dash-link"><?= GetMessage('ME_AGENDA') ?></span><span
                            class="meeting-tab-active-right-side"></span>
                </a>
            <? } ?>
            <a class="meeting-detail-tab<?= $arResult['MEETING']['CURRENT_STATE'] != 'P' ? ' meeting-tab-active' : '' ?>"
               href="javascript:void(0)" id="switch_protocol"
               onclick="switchView('protocol', '<?= ($arResult['MEETING']['CURRENT_RIGHTS']) ?>');">
                <span class="meeting-detail-tab-text meeting-dash-link"><?= GetMessage('ME_PROTO') ?></span><span
                        class="meeting-tab-active-right-side"></span>
            </a>
            <a class="meeting-detail-tab" href="javascript:void(0)" id="switch_download"
               onclick="switchView('download', '<?= ($arResult['MEETING']['CURRENT_RIGHTS']) ?>');">
                <span class="meeting-detail-tab-text meeting-dash-link"><?= GetMessage('ME_DOWNLOAD') ?></span><span
                        class="meeting-tab-active-right-side"></span>
            </a>
        </div>

        <div id="agenda_blocks_all" class="meeting-detail-agenda-blocks">
            <div class="meeting-agenda-block meeting-agenda-download-visible">
                <div class="meeting-ag-block-cont-wrap">
                    <div class="meeting-ag-block-cont">
                        <div class="meeting-agenda-download-item">
                            <span class="meeting-agends-download-item-text"><?= GetMessage("ME_DOWNLOAD_BALLOT_PAPER"); ?></span>
                            <span class="meeting-agends-download-item-link">
                                <a href="<?= $APPLICATION->GetCurPageParam("action=generate_file&type=bpaper&format=docx", array(), false) ?>"
                                   target="_blank"><?= GetMessage("TYPE_DOCX"); ?></a>
                            </span>
                            <span class="meeting-agends-download-item-link">
                                <a href="<?= $APPLICATION->GetCurPageParam("action=generate_file&type=bpaper&format=pdf", array(), false) ?>"
                                   target="_blank"><?= GetMessage("TYPE_PDF"); ?></a>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="meeting-ag-block-bottom"></div>
                <div class="meeting-ag-block-cont-wrap">
                    <div class="meeting-ag-block-cont">
                        <div class="meeting-agenda-download-item">
                            <span class="meeting-agends-download-item-text"><?= GetMessage("ME_DOWNLOAD_MEETING_PROTOCOL"); ?></span>
                            <span class="meeting-agends-download-item-link">
                                <a href="<?= $APPLICATION->GetCurPageParam("action=generate_file&type=mprotocol&format=docx", array(), false) ?>"
                                   target="_blank"><?= GetMessage("TYPE_DOCX"); ?></a>
                            </span>
                            <span class="meeting-agends-download-item-link">
                                <a href="<?= $APPLICATION->GetCurPageParam("action=generate_file&type=mprotocol&format=pdf", array(), false) ?>"
                                   target="_blank"><?= GetMessage("TYPE_PDF"); ?></a>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="meeting-ag-block-bottom"></div>
                <div class="meeting-ag-block-cont-wrap">
                    <div class="meeting-ag-block-cont">
                        <div class="meeting-agenda-download-item">
                            <span class="meeting-agends-download-item-text"><?= GetMessage("ME_DOWNLOAD_WRITTEN_OPINION"); ?></span>
                            <span class="meeting-agends-download-item-link">
                                <a href="<?= $APPLICATION->GetCurPageParam("action=generate_file&type=wopinion&format=docx", array(), false) ?>"
                                   target="_blank"><?= GetMessage("TYPE_DOCX"); ?></a>
                            </span>
                            <span class="meeting-agends-download-item-link">
                                <a href="<?= $APPLICATION->GetCurPageParam("action=generate_file&type=wopinion&format=pdf", array(), false) ?>"
                                   target="_blank"><?= GetMessage("TYPE_PDF"); ?></a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div id="agenda_blocks"></div>
            <div class="meeting-agenda-block meeting-agenda-protocol-visible">
                <div class="meeting-ag-block-top">
                    <div class="meeting-ag-block-tl"></div>
                    <div class="meeting-ag-block-tr"></div>
                </div>
                <div class="meeting-ag-block-cont-wrap">
                    <div class="meeting-ag-block-cont meeting-ag-outside-qu">
                        <span class="meeting-ag-block-title-text"><?= GetMessage('ME_PROTO_OUTSIDE') ?></span>
                    </div>
                </div>
                <div class="meeting-ag-block-bottom">
                    <div class="meeting-ag-block-bl"></div>
                    <div class="meeting-ag-block-br"></div>
                </div>
            </div>
            <? /*if ($arResult['MEETING']['CURRENT_RIGHTS'] == 'M')
        {
            ?><div id = "agenda_blocks_outside" class="meeting-agenda-blocks-outside" >1</div><?
        } else {
            ?><div id = "agenda_blocks_outside" class="meeting-agenda-blocks-outside" >2</div><?
        }*/
            ?>
            <div id="agenda_blocks_outside" class="meeting-agenda-blocks-outside"></div>
        </div>
        <?
        if ($arResult['CAN_EDIT']):
            $APPLICATION->IncludeComponent(
                "bitrix:meeting.selector",
                ".default",
                array(
                    'MEETING_ID' => $arResult['MEETING']['ID'],
                    'CALLBACK_NAME' => 'showMeetingSelector',
                    'MEETING_URL_TPL' => $arParams['MEETING_URL_TPL'],
                ),
                null,
                array("HIDE_ICONS" => "Y")
            );

            if ($bTask):
                ?>
                <div id="task_selector" style="display: none;">
                    <?
                    $APPLICATION->IncludeComponent(
                        "bitrix:tasks.task.selector",
                        ".default",
                        array(
                            "MULTIPLE" => "N",
                            "NAME" => "MEETING_AGENDA_TASKS",
                            "VALUE" => "",
                            "POPUP" => "N",
                            "ON_SELECT" => "addTaskRow",
                            "SITE_ID" => SITE_ID,
                            "SELECT" => array('ID', 'TITLE', 'STATUS'),
                        ),
                        null,
                        array("HIDE_ICONS" => "Y")
                    );
                    ?>
                </div>
            <?
            endif;
            /*
        ?>
                <span class="meeting-agenda-add-item-wrap meeting-agenda-protocol-visible">
                    <a class="meeting-agenda-add-item meeting-dash-link" href="javascript:void(0)" onclick="editRow(addRow({EDITABLE: true}, null));"><?=GetMessage('ME_PROTO_ADD')?></a>
                </span>
                <span class="meeting-agenda-add-item-wrap meeting-agenda-protocol-visible">
                    <a class="meeting-agenda-add-item meeting-dash-link" href="javascript:void(0)" onclick="showMeetingSelector(this)"><?=GetMessage('ME_ADD_EX')?></a>
                </span>
        <?
            if ($bTask):
        ?>
                <span class="meeting-agenda-add-item-wrap meeting-agenda-protocol-visible">
                    <a href="javascript:void(0)" onclick="showTaskSelector(this)" class="meeting-agenda-add-item meeting-dash-link"><?=GetMessage('ME_TASK_ADD')?></a>
                </span>
        <?
            endif;*/
            ?>

            <div class="meeting-agenda-add-item-wrap meeting-agenda-agenda-visible meeting-toolbar-layout"
                 style="display:block" id="meeting_toolbar_layout">
                <?if ($arResult['MEETING']['CURRENT_STATE'] != 'C') {?>
                <a class="webform-small-button webform-small-button-accept" href="javascript:void(0)"
                   onclick="editRow(addRow({EDITABLE: true}, null));">
                    <span class="webform-small-button-left"></span><span
                            class="webform-small-button-text"><?= GetMessage('ME_AGENDA_ADD') ?></span><span
                            class="webform-small-button-right"></span>
                </a>
                <a id="approval_button" class="webform-small-button webform-small-button-accept"
                   href="javascript:void(0)"
                   onclick="sendNotice();"<? if ($arMeetingProps['AGENDA_AGREE']['VALUE_ENUM_ID'] == 79 || $USER->GetID() == $ownerID): ?> style="display: none;"<? endif; ?>>
                    <span class="webform-small-button-left"></span><span
                            class="webform-small-button-text"><?= GetMessage('ME_AGENDA_SUBMIT_FOR_APPROVAL') ?></span><span
                            class="webform-small-button-right"></span>
                </a>
                <a class="meeting-agenda-bot-link meeting-dash-link" href="javascript:void(0)"
                   onclick="showMeetingSelector(this)"><?= GetMessage('ME_ADD_EX') ?></a>
                    <?if ($bTask && $arResult['CAN_EDIT']) {?>
                        <a href="javascript:void(0)" onclick="showTaskSelector(this)"
                           class="meeting-agenda-bot-link meeting-dash-link"><?= GetMessage('ME_TASK_ADD') ?></a>
                    <?}?>
                <?}?>
            </div>
        <?
        endif;
        if ($arResult['CAN_EDIT'] || strlen($arResult['MEETING']['PROTOCOL_TEXT']) > 0):
            ?>
            <div class="meeting-agenda-protocol-text meeting-agenda-protocol-visible">
                <span class="meeting-new-agenda-title"><?= GetMessage('ME_PROTO') ?></span>
                <?
                if ($arResult['CAN_EDIT']):
                    $editor_id = "MEProto";

                    ?>
                    <script type="text/javascript">

                        BX.addCustomEvent(window, 'LHE_OnInit', function (ed) {
                            if (ed.id == '<?=$editor_id?>') {
                                var prev_content = '';
                                BX.addCustomEvent(ed, 'OnSaveContent', function (content) {
                                    if (content != prev_content) {
                                        prev_content = content;
                                        saveData();
                                    }
                                });
                            }
                        });
                    </script>
                <?
                $APPLICATION->IncludeComponent('bitrix:fileman.light_editor', '', array(
                    'ID' => $editor_id,
                    'CONTENT' => $arResult['MEETING']['~PROTOCOL_TEXT'],
                    'INPUT_NAME' => 'PROTOCOL_TEXT',
                    'RESIZABLE' => 'Y',
                    'AUTO_RESIZE' => 'Y',
                    'WIDTH' => '100%',
                    'HEIGHT' => '200px',
                ));

                else:
                ?>
                    <div class="meeting-agenda-protocol-text-content"><?= $arResult['MEETING']['~PROTOCOL_TEXT']; ?></div>
                <?
                endif;
                ?>
            </div>
        <?
        endif;
        ?>
    </div>
    <div class="webform-corners-bottom">
        <div class="webform-left-corner"></div>
        <div class="webform-right-corner"></div>
    </div>
</div>

<? foreach ($arResult['MEETING']['AGENDA'] as $agenda): ?>
    <div id="js-agenda-<?= $agenda['ID'] ?>">
        <?
        $APPLICATION->IncludeComponent(
            'b24tech:disk.folder.file.list',
            '',
            array(
                'QUESTION_ID' => $agenda['ID'],
                'TYPE' => 'question',
                'TEMPLATE' => 'for_questions'
            ),
            false,
            array("HIDE_ICONS" => "Y")
        );
        ?>
    </div>
<? endforeach; ?>



<?

if (is_array($arResult['MEETING']['AGENDA'])):

    $ids = array();
    $questions = array();
    $sections = array();

    foreach ($arResult['MEETING']['AGENDA'] as $agenda) {
        if (!$agenda['INSTANCE_PARENT_ID']) {
            $ids[] = $agenda["ID"];
        } else {
            $questions[$agenda['INSTANCE_PARENT_ID']][] = $agenda['ID'];
        }
    }
    $db_list = CIBlockSection::GetList(Array("ID" => "ASC"), array(
        "ACTIVE" => "Y",
        "IBLOCK_ID" => 30,
        "UF_ID_INSTANCE" => $ids,
        //"UF_STATUS" => "IN_PROCESS"
    ),
        false,
        array("ID", "IBLOCK_ID", "NAME", "UF_*")
    );

    $votingsIsStarted = array();

    while ($ar_result = $db_list->Fetch()) {
        $arResult['MEETING']['AGENDA'][$ar_result['UF_ID_INSTANCE']]['STATUS'] = $ar_result['UF_STATUS'];
        $votingsIsStarted[$ar_result['UF_ID_INSTANCE']] = $ar_result['UF_STATUS'];
        $sections[$ar_result['UF_ID_INSTANCE']] = $ar_result['ID'];
    }

    foreach ($arResult['MEETING']['AGENDA'] as $key => $agenda) {
        if ($agenda['INSTANCE_PARENT_ID'] && $votingsIsStarted[$agenda['INSTANCE_PARENT_ID']]) {
            $arResult['MEETING']['AGENDA'][$key]['STATUS'] = $votingsIsStarted[$agenda['INSTANCE_PARENT_ID']];
        }
    }

    function getAnswers($userId, $meetingId)
    {
        $answers = array();

        $arHLBlock = \Bitrix\Highloadblock\HighloadBlockTable::getById(2)->fetch();
        $obEntity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arHLBlock);
        $strEntityDataClass = $obEntity->getDataClass();

        $filter = array(
            'UF_USER_ID' => $userId,
            'UF_MEETING_ID' => $meetingId,
        );

        $rsData = $strEntityDataClass::getList(array(
            'order' => array('ID' => 'ASC'),
            'filter' => $filter,
        ));
        // UF_PARENT_INSTANCE
        while ($arItem = $rsData->Fetch()) {
            $answers[$arItem['UF_PARENT_INSTANCE']][$arItem['UF_SECTION_ID']][] = $arItem['UF_ID_INSTANCE'];
        }

        return $answers;
    }

    function detectAnsweredQuestions($questions, $answers, $sections)
    {
        $answeredQestions = array();
        foreach ($questions as $parentInstanceId => $instances) {

            $answeredQestions[$parentInstanceId] = true;

            if (!isset($sections[$parentInstanceId])) {
                $answeredQestions[$parentInstanceId] = false;
                continue;
            }

            $sectionId = $sections[$parentInstanceId];
            foreach ($instances as $id) {
                if (!in_array($id, $answers[$parentInstanceId][$sectionId])) {
                    $answeredQestions[$parentInstanceId] = false;
                    break;
                }
            }
        }

        return $answeredQestions;
    }

    $usersInMeeting = CMeeting::GetUsers($arResult['MEETING']['ID']);


    global $USER;
    $currentUser = $USER->GetId();
    $allowedStart = false;
    $showStatus = false;

    if ($usersInMeeting[$currentUser] == 'K'
        || $usersInMeeting[$currentUser] == 'O'
        && $arResult['MEETING']['CURRENT_STATE'] != 'P') {
        $allowedStart = true;
    }

    if ($usersInMeeting[$currentUser] == 'M' || $usersInMeeting[$currentUser] == 'O') {
        $showStatus = true;
        $answers = getAnswers($currentUser, $arResult['MEETING']['ID']);
        $answeredQestions = detectAnsweredQuestions($questions, $answers, $sections);

        foreach ($arResult['MEETING']['AGENDA'] as $key => $agenda) {
            if (!$agenda['INSTANCE_PARENT_ID']) {
                $arResult['MEETING']['AGENDA'][$key]['NEED_VOTING'] = $answeredQestions[$key];
            }
        }
    }

    $messages = Loc::loadLanguageFile(__FILE__);
    ?>

    <script type="text/javascript">
        BX.message(<?=CUtil::PhpToJsObject($messages, false, true)?>);
        window.listItemParams.mainPrefix = BX.message('MAIN_PREFIX');
        window.listItemParams.childPrefix = BX.message('CHILDREN_PREFIX');
        // window.listItemParams.level = -1;
        BX.ready(function () {
            <?
            foreach ($arResult['MEETING']['AGENDA'] as $item_id => $arItem):
            if (MakeTimeStamp($arItem['DEADLINE']) <= 0)
                $arItem['DEADLINE'] = '';
            ?>
            addRow(<?=CUtil::PhpToJsObject($arItem, false, true)?>, null);
            <?
            endforeach;
            ?>

        });</script>
<?
endif;
?>
<script type="text/javascript">

    //Функция проверяет установлено ли в свойстве элемента значение Y
    //Если установлено, показывает кнопку "Перейти к голосованию"
    function showVotingOffBtn(id_prop) {
        var prop_value = '';
        $.ajax({
            url: '/local/templates/bitrix24/components/b24tech/meeting.edit/template1/activate_voting.php',
            data: {
                id: id_prop,
            },
            async: false,
            success: function (data) {
                prop_value = data;
            }
        });
        if (prop_value === 'Y') {
            $('#voting-btn-' + id_prop).css('display', 'inline');
        } else {
            $('#voting-btn-' + id_prop).css('display', 'none');
        }
    }

    //Функция собирает id кнопок "вопросов вне повестки" (ссылки из блока #agenda_blocks_outside)
    //Передает функции showVotingOffBtn() для показа/скрытия кнопок
    function getVotingOffBtnId() {
        var linkIds = [];
        var i = 0;
        var arrlinks = $("#agenda_blocks_outside a");
        for (i; i < arrlinks.length; i++) {
            linkIds[i] = arrlinks[i].id.replace("voting-btn-", "");
            showVotingOffBtn(linkIds[i]);
        }
        ;
        return linkIds;
    };

    //Функция записывает в свойство элемета "AGENDA_OFF_VOTING" (вопрос для голосования вне повестки) значение "Y"
    function setVotingOffBtn(id) {
        $.ajax({
            url: '/local/templates/bitrix24/components/b24tech/meeting.edit/template1/agenda_off_voting_ajax.php',
            data: {
                id: id,
            }
        });
    }

    function startVoting(id, type) {
        $.ajax({
            url: '/local/components/b24tech/voting.form/start.php',
            data: {
                id: id,
                type: type,
                action: 'create'
            },
            error: function (jqXHR, exception) {
                var msg = '';
                if (jqXHR.status === 0) {
                    msg = 'Not connect.\n Verify Network.';
                } else if (jqXHR.status == 404) {
                    msg = 'Requested page not found. [404]';
                } else if (jqXHR.status == 500) {
                    msg = 'Internal Server Error [500].';
                } else if (exception === 'parsererror') {
                    msg = 'Requested JSON parse failed.';
                } else if (exception === 'timeout') {
                    msg = 'Time out error.';
                } else if (exception === 'abort') {
                    msg = 'Ajax request aborted.';
                } else {
                    msg = 'Uncaught Error.\n' + jqXHR.responseText;
                }
                console.log(msg);
            },
            success: function (res) {
                if (res.success) {
                    $('.voting-' + id).html('Голосование успешно запущено');
                    setVotingOffBtn(id);
                } else {
                    $('.voting-' + id).html('Ошибка запуска голосования: "Нет проектов решения"');
                }

                console.log(res);
            }.bind(this)
        });
    }

    function _onUpdateIndexes(all_cnt, cnt, ix) {
        if (this.BXINSTANCEKEY && document.forms.meeting_edit['AGENDA_SORT[' + this.BXINSTANCEKEY + ']'])
            document.forms.meeting_edit['AGENDA_SORT[' + this.BXINSTANCEKEY + ']'].value = all_cnt * 100;
    }

    BX.addCustomEvent('onUpdateIndexes', _onUpdateIndexes);

    var _index = <?=$arResult['START_INDEX']?>;
    var current_view = 'agenda';
    var currently_edited_row = null;

    function updateSelect(copy) {
        if (copy && copy.options) {
            var a = [], i;
            if (window.arMembersList) {
                a = BX.util.array_values(window.arMembersList);
            }

            var copy_value = {};

            for (i = 0; i < copy.options.length; i++) {
                if (copy.options[i].selected)
                    copy_value[copy.options[i].value] = true;
            }

            while (copy.options.length > 0)
                copy.remove(0);

            copy.options.add(new Option('<?=CUtil::JSEScape(GetMessage('ME_AGENDA_NO_RESPONSIBLE'))?>', 0))
            for (var i = 0; i < a.length; i++) {
                var o = new Option(a[i].name, a[i].id);
                o.selected = !!copy_value[a[i].id];
                copy.options.add(o)
            }
        }
    }

    <?

    if ($arResult['CAN_EDIT']):
    ?>
    function editRow(row, bSkipFocus) {
        if (BX.hasClass(row, 'meeting-agenda-edit-block'))
            return viewRow(row, true);

        if (null != window.currently_edited_row)
            viewRow(window.currently_edited_row, true);

        window.currently_edited_row = row;

        hideComments(row);
        jsDD.Disable();

        if (BX.setSelectable)
            BX.setSelectable(row);

        BX.addClass(row, 'meeting-agenda-edit-block meeting-add-item-form');

        updateSelect(document.forms.meeting_edit['AGENDA_RESPONSIBLE[' + row.BXINSTANCEKEY + '][]']);

        var inp = document.forms.meeting_edit['AGENDA_TITLE[' + row.BXINSTANCEKEY + ']'];
        BX.bind(inp, 'keydown', BX.delegate(checkEnter, row));

        if (!bSkipFocus)
            BX.focus(inp);
    }

    function viewRow(row, bSave) {
        jsDD.Enable();
        BX.setUnselectable(row);

        var data = row.BXINSTANCE, key = data.ID;

        if (bSave) {
            if (data.EDITABLE) {
                data.TITLE = BX.util.htmlspecialchars(document.forms.meeting_edit['AGENDA_TITLE[' + key + ']'].value);
                if (data.TITLE == '<?=CUtil::JSEscape(GetMessage('ME_MEETING_TITLE_DEFAULT'))?>' || data.TITLE == '<?=CUtil::JSEscape(GetMessage('ME_MEETING_TITLE_DEFAULT_1'))?>')
                    data.TITLE = '';

            }

            data.RESPONSIBLE = [document.forms.meeting_edit['AGENDA_RESPONSIBLE[' + key + '][]'].value];
            data.RESPONSIBLE_CUSTOM = document.forms.meeting_edit['AGENDA_RESPONSIBLE_CUSTOM[' + key + ']'].value;
            if (document.forms.meeting_edit['AGENDA_DEADLINE[' + key + ']']) {
                data.DEADLINE = document.forms.meeting_edit['AGENDA_DEADLINE[' + key + ']'].value;
            }

            if (document.forms.meeting_edit['AGENDA_TASK[' + key + ']'])
                data.AGENDA_TASK_CHECKED = document.forms.meeting_edit['AGENDA_TASK[' + key + ']'].checked;
        }

        deleteComments(row);

        if (bSave || data.ID[0] != 'n' || !!data.TITLE) {
            var new_row = addRow(data, null, true);
            jsDD.disableDest(row);
            row.parentNode.replaceChild(new_row, row);
        } else {
            row.parentNode.removeChild(row);
        }

        updateIndexes();
        jsDD.refreshDestArea();

        if (bSave) {
            saveData();
            <? if ($USER->GetID() != $ownerID): ?>
            toogleAgendaStatus();
            <? endif; ?>
        }

        window.currently_edited_row = null;

        return new_row;
        console.log(new_row);
    }
    <?
    endif;
    ?>
    function addRow(data, previousSibling, bReturn) {
        var currentState = '<?=$arResult['MEETING']['CURRENT_STATE']?>';

        if (data.INSTANCE_PARENT_ID === '0')
            data.INSTANCE_PARENT_ID = null;

        if (!data.TASKS_COUNT)
            data.TASKS_COUNT = [0, 0];
        else if (data.TASKS_COUNT[1] == '0')
            data.TASKS_COUNT[1] = 0;

        if (!data.COMMENTS_COUNT)
            data.COMMENTS_COUNT = 0;
        else if (data.COMMENTS_COUNT == '0')
            data.COMMENTS_COUNT = 0;

        var parent = data.INSTANCE_PARENT_ID;
        var row, children;
        var files;


        if (bReturn) {
            files = BX.findChild(BX('js-file-list-wrap-' + data.ID), {className: 'file-list-wrap'}, false);
        }

        if (!data.ORIGINAL_TYPE) {
            if (currentState == 'A') {
                data.ORIGINAL_TYPE = 'T';
            } else {
                data.ORIGINAL_TYPE = current_view == 'agenda' ? '<?=CMeetingInstance::TYPE_AGENDA?>' : '<?=CMeetingInstance::TYPE_TASK?>';
            }
            data.INSTANCE_TYPE = '<?=CMeetingInstance::TYPE_TASK?>';
        }

        if (data.ORIGINAL_TYPE == '<?=CMeetingInstance::TYPE_TASK?>'  && !parent) {
            parent = 'outside';
        }

        var key = data.ID || 'n' + (_index++);

        var p = null;
        if (!!parent) {
            p = BX('agenda_blocks_' + parent);
            if (!p)
                setTimeout('checkParent(\'' + key + '\')', 20);
        }

        p = p || BX('agenda_blocks');

        var link_href = data.ITEM_ID ? '<?=CUtil::JSEscape($arParams['ITEM_URL'])?>'.replace('#ITEM_ID#', data.ITEM_ID) : 'javascript:void(0)';

        var h = '\
<input type="hidden" name="AGENDA[' + key + ']" value="' + key + '" /><input type="hidden" name="AGENDA_PARENT[' + key + ']" value="' + (!!parent ? parent : 0) + '" /><input type="hidden" name="AGENDA_ORIGINAL[' + key + ']" value="' + data.ORIGINAL_TYPE + '" /><input type="hidden" name="AGENDA_TYPE[' + key + ']" value="' + (data.INSTANCE_TYPE || '<?=CMeetingInstance::TYPE_AGENDA;?>') + '" /><input type="hidden" name="AGENDA_SORT[' + key + ']" value="" /><input type="hidden" name="AGENDA_ITEM[' + key + ']" value="' + (data.ITEM_ID || '') + '" />' + (data.TASK_ID ? '<input type="hidden" name="AGENDA_TASK[' + key + ']" value="' + data.TASK_ID + '" />' : '') + '\
<div class="meeting-ag-block-top"><div class="meeting-ag-block-tl"></div><div class="meeting-ag-block-tr"></div></div>\
<div class="meeting-ag-block-cont-wrap"><div class="meeting-ag-block-cont">\
<div class="meeting-ag-info-icons"' + (data.ITEM_ID > 0 ? '' : ' style="display:none;"') + '>\
' + (data.TASKS_COUNT[0] > 0 ? '<a href="' + link_href + '#tasks" class="meeting-ag-tasks-ic" title="<?=CUtil::JSEscape(htmlspecialcharsbx(GetMessage('ME_AGENDA_TT_TASKS_C')))?>" onmousedown="BX.PreventDefault(arguments[0])">' + data.TASKS_COUNT[0] + '</a>' : '') + '\
<span onmousedown="BX.PreventDefault(arguments[0])" onclick="toggleComments(BX.findParent(this, window.listItemParams.isItem))" class="meeting-ag-comments-ic" title="<?=CUtil::JSEscape(htmlspecialcharsbx(GetMessage('ME_AGENDA_TT_COMMENTS_C')))?>">' + (data.COMMENTS_COUNT > 0 ? data.COMMENTS_COUNT : '+') + '</span></div>';

        var bChecked = data.INSTANCE_TYPE == '<?=CMeetingInstance::TYPE_AGENDA?>';

        h += '<div class="meeting-ag-block-title-wrap' + (bChecked ? ' meeting-ag-cont-block-checked' : '') + '">';
        <?
        if($arResult['CAN_EDIT']):
        ?>
        h += '<div class="meeting-sub-bl-checkbox-wrap' + (bChecked ? ' meeting-checkbox-checked' : '') + '" onmousedown="BX.PreventDefault(arguments[0])" onclick="checkboxClick(this)" title="<?=CUtil::JSEscape(htmlspecialcharsbx(GetMessage('ME_AGENDA_TT_CHECK')))?>"></div>';
        <?
        endif;
        ?>
        h += '<span class="meet-ag-block-title-num bx-list-number"></span>\
<div class="meeting-ag-add-form">\
<div class="meeting-ag-add-buttons-form"><div class="meeting-ag-buttons-save" onclick="viewRow(BX.findParent(this, window.listItemParams.isItem), true)"><span class="meeting-ag-buttons-left"></span><span class="meeting-ag-buttons-cont"><span class="meeting-ag-buttons-icon"></span><?=CUtil::JSEscape(htmlspecialcharsbx(GetMessage('ME_AGENDA_TT_SAVE')))?></span><span class="meeting-ag-buttons-right"></span></div><div class="meeting-ag-buttons-cancel" onclick="viewRow(BX.findParent(this, window.listItemParams.isItem), false)"><span class="meeting-ag-buttons-left"></span><span class="meeting-ag-buttons-cont"><span class="meeting-ag-buttons-icon"></span><?=CUtil::JSEscape(htmlspecialcharsbx(GetMessage('ME_AGENDA_TT_CANCEL')))?></span><span class="meeting-ag-buttons-right"></span></div></div>\
<div class="meeting-ag-add-form-blocks">\
<span class="meeting-ag-add-qu-form"><span class="meeting-ag-add-input-wrap">';

        if (data.EDITABLE) {
            h += '\
<input type="text" name="AGENDA_TITLE[' + key + ']" maxlength="255" value="' + (data.TITLE || '<?=CUtil::JSEscape(GetMessage('ME_MEETING_TITLE_DEFAULT_1'))?>') + '" class="meeting-ag-add-input' + (!!data.TITLE ? ' meeting-ag-add-inp-active' : '') + '" onblur="if(this.value==\'\'||this.value==\'<?=addslashes(CUtil::JSEscape(GetMessage('ME_MEETING_TITLE_DEFAULT_1')))?>\') {this.value=\'<?=addslashes(CUtil::JSEscape(GetMessage('ME_MEETING_TITLE_DEFAULT_1')))?>\'; BX.removeClass(this,\'meeting-ag-add-inp-active\')}" onfocus="if(this.value==\'<?=addslashes(CUtil::JSEscape(GetMessage('ME_MEETING_TITLE_DEFAULT_1')))?>\') this.value=\'\'; BX.addClass(this, \'meeting-ag-add-inp-active\')" />';
//h += '<div class="meeting-ag-add-wrap"><a href="" class="meeting-ag-add-description"><?=CUtil::JSEscape(GetMessage('ME_AGENDA_ADD_DESCRIPTION'))?></a></div>';
        } else {
            if (!parent) {
                h += '<a class="meeting-ag-block-title-text" href="' + link_href + '" onfocus="this.blur();">' + (data.TITLE || '<?=CUtil::JSEscape(GetMessage('ME_MEETING_TITLE_DEFAULT'))?>') + '</a>';
            } else {
                h += '<span class="meeting-ag-block-title-text">' + (data.TITLE || '<?=CUtil::JSEscape(GetMessage('ME_MEETING_TITLE_DEFAULT'))?>') + '</span>';
            }
        }

        h += '</span></span>';

        var bMultiple = (!!data.RESPONSIBLE) && (data.RESPONSIBLE.length > 1);

        h += 'Докладчик:<span class="meeting-ag-add-responsible-form"><span class="meeting-ag-add-input-wrap"><select onfocus="BX.addClass(this, \'meeting-ag-add-inp-active\')" class="meeting-ag-add-select meeting-ag-add-inp-active" name="AGENDA_RESPONSIBLE[' + key + '][]"' + (bMultiple ? ' multiple="multiple"' : '') + '>';
        if (!!data.RESPONSIBLE) {
            for (var i = 0; i < data.RESPONSIBLE.length; i++)
                h += '<option value="' + data.RESPONSIBLE[i] + '" selected="selected"></option>';
        }
        h += '</select>';

        h += '<input type="text" name="AGENDA_RESPONSIBLE_CUSTOM[' + key + ']" value="' + (data.RESPONSIBLE_CUSTOM ? data.RESPONSIBLE_CUSTOM : '') + '" placeholder="Докладчик" />';

        h += '<div class="meeting-ag-add-do-task-wrap' + (!!data.TASK_ID ? ' meeting-has-task' : '') + '" id="meeting_make_task_' + key + '"><span class="meeting-task-add"><input type="checkbox" name="AGENDA_TASK[' + key + ']" id="meeting-ag-add-do-task' + key + '" class="meeting-ag-add-do-task-ch"' + (!!data.AGENDA_TASK_CHECKED ? ' checked="checked"' : '') + ' value="Y" /><label for="meeting-ag-add-do-task' + key + '" class="meeting-ag-add-do-task-l"><?=CUtil::JSEscape(GetMessage('ME_AGENDA_ADD_TASK'))?></label></span>' + (data.TASK_ACCESS ? '<a id="meeting_task_' + data.TASK_ID + '" href="javascript:void(0)" class="meeting-dash-link meeting-task-link"' + (!!data.TASK_ID ? ' onclick="taskIFramePopup.tasksList=[]; taskIFramePopup.view(' + data.TASK_ID + ');"' : '') + '><?=CUtil::JSEscape(GetMessage('ME_AGENDA_TASK_ADDED'))?></a>' : '') + '</div>';

        h += '\
</span></span>\
<!--span class="meeting-ag-add-time-form"><span class="meeting-ag-add-input-wrap"><input type="text" onclick="if(this.value==\'<?=addslashes(CUtil::JSEscape(GetMessage('ME_AGENDA_DEADLINE_DEFAULT')))?>\') { this.value=\'\'; };BX.addClass(this, \'meeting-ag-add-inp-active\'); BX.calendar({node: this, field: \'AGENDA_DEADLINE[' + key + ']\', bHideTime: true, bTime: true, currentTime: \'<?=(time() + date("Z") + CTimeZone::GetOffset())?>\', form: \'meeting_edit\'});" value="' + (data.DEADLINE || '<?=addslashes(CUtil::JSEscape(GetMessage('ME_AGENDA_DEADLINE_DEFAULT')))?>') + '" class="meeting-ag-add-input' + (!!data.DEADLINE ? ' meeting-ag-add-inp-active' : '') + '" name="AGENDA_DEADLINE[' + key + ']" /></span></span--></div></div>\
<div class="meeting-ag-block-title">';

        if (!parent || parent == 'outside') {
            <?
            //Проверка доступа пользователя и этапа для перехода к вопросу
            if ($arResult['ACCESS'] != 'M' || $arResult['MEETING']['CURRENT_STATE'] != 'P') {?>
            h += '<a class="meeting-ag-block-title-text" href="' + link_href + '" onfocus="this.blur();">' + (data.TITLE || '<?=CUtil::JSEscape(GetMessage('ME_MEETING_TITLE_DEFAULT'))?>') + '</a>';
            <?} else {?>
            h += '<span class="meeting-ag-block-title-text">' + (data.TITLE || '<?=CUtil::JSEscape(GetMessage('ME_MEETING_TITLE_DEFAULT'))?>') + '</span>';
            <?}?>

        } else {
            h += '<span class="meeting-ag-block-title-text">' + (data.TITLE || '<?=CUtil::JSEscape(GetMessage('ME_MEETING_TITLE_DEFAULT'))?>') + '</span>';
        }
        <?
        //Проверка  этапа для перехода к голосованию
        if ($arResult['MEETING']['CURRENT_STATE'] != 'P' && $arResult['MEETING']['CURRENT_STATE'] != 'C') {?>
        <?if($allowedStart):?>
        // if(!data.INSTANCE_PARENT_ID && (!data.STATUS || data.STATUS == 'CLOSE')) {
        //<!--a onclick="startVoting('+data.ID+',\'online\');">Запустить голосование online</a-->
        if (!data.INSTANCE_PARENT_ID && !data.STATUS) {
            h += '<div class="voting-block voting-' + data.ID + '"><a onclick="startVoting(' + data.ID + ',\'offline\');">Запустить голосование</a></div>';
        }
        <?endif;?>
        <?}?>

        <?if($showStatus):?>
        /*if(!data.INSTANCE_PARENT_ID && data.STATUS == 'IN_PROCESS') {
            //h += '<div class="voting-block voting-'+data.ID+'">Идет голосование</div>';
            h += '<div class="voting-block voting-'+data.ID+'"><a href="'+link_href+'" onfocus="this.blur();">Идет голосование</a></div>';
        } else
        if(!data.INSTANCE_PARENT_ID && data.STATUS == 'FINISHED') {
            h += '<div class="voting-block voting-'+data.ID+'">На проверке у секретаря</div>';
        } else
        if(!data.INSTANCE_PARENT_ID && data.STATUS == 'AGREEMENT') {
            h += '<div class="voting-block voting-'+data.ID+'">На проверке у председателя</div>';
        }*/


        if (!data.INSTANCE_PARENT_ID && data.ORIGINAL_TYPE === 'A') {

            if (data.NEED_VOTING) {
                h += '<div class="voting-block voting-' + data.ID + '">Вы уже проголосовали</div>';
            } else {
                <?
                //Проверка  этапа для перехода к голосованию
                if ($arResult['MEETING']['CURRENT_STATE'] == "A") {?>
                    if (data.STATUS) {
                        h += '<div class="voting-block voting-' + data.ID + '"><a id="voting-btn-' + data.ID + '" href="' + link_href + '" onfocus="this.blur()">Перейти к голосованию</a></div>';
                    }
                <?}?>
            }
        } else if (!data.INSTANCE_PARENT_ID && data.ORIGINAL_TYPE === 'T') {
            if (data.NEED_VOTING) {
                h += '<div class="voting-block voting-' + data.ID + '">Вы уже проголосовали</div>';
            } else {
                <?
                //Проверка  этапа для перехода к голосованию
                if ($arResult['MEETING']['CURRENT_STATE'] == "A") {?>
                h += '<div class="voting-block voting-' + data.ID + '"><a id="voting-btn-' + data.ID + '" href="' + link_href + '" onfocus="this.blur()" style="display:none;">Перейти к голосованию</a></div>';
                <?}?>
            }
        }
        <?endif;?>

        /*if ($arResult['MEETING']['CURRENT_RIGHTS'] == 'M') {

        }*/

        var s = "updateSelect(document.forms.meeting_edit['AGENDA_RESPONSIBLE[" + key + "][]']);";
        setTimeout("BX.addCustomEvent('onMeetingChangeUsersList', function(){" + s + "});" + s, 200);

        var bHasFiles = BX.type.isArray(data.FILES) && data.FILES.length > 0,
            bHasReports = BX.type.isArray(data.REPORTS) && data.REPORTS.length > 0,
            bHasResponsible = BX.type.isArray(data.RESPONSIBLE) && data.RESPONSIBLE.length > 0 && data.RESPONSIBLE[0] > 0,
            bHasCustomResponsible = data.RESPONSIBLE_CUSTOM != undefined && data.RESPONSIBLE_CUSTOM.length > 0,
            bHasTask = !!data.TASK_ID && !!data.TASK_ACCESS;

        if (bHasReports) {
            bHasReports = false;
            for (var rep = 0; rep < data.REPORTS.length; rep++) {
                if (data.REPORTS[rep].REPORT.length > 0 || data.REPORTS[rep].FILES && data.REPORTS[rep].FILES.length > 0) {
                    bHasReports = true;
                    break;
                }
            }
        }

        if (bHasFiles || bHasReports || bHasResponsible || bHasTask || bHasCustomResponsible) {
            h += '<span class="meeting-ag-report">';

            if (bHasReports) {
                for (var rep = 0; rep < data.REPORTS.length; rep++) {
                    var r = parseInt(Math.random() * 100000);
                    window['report_popup_data_' + r] = data.REPORTS[rep];
                    h += '<span onclick="showReport(' + r + ',this)" class="meeting-ag-report-link  meeting-dash-link">' + '<?=CUtil::JSEscape(GetMessage('ME_AGENDA_REPORT'))?>' + '</span>';
                }
            }

            if (bHasTask) {
                h += '<span class="meeting-ag-report-link meeting-dash-link" onclick="taskIFramePopup.tasksList=[]; taskIFramePopup.view(' + data.TASK_ID + ');"><?=CUtil::JSEscape(GetMessage('ME_AGENDA_TASK'))?></span>';
            }

            if (bHasResponsible || bHasCustomResponsible) {
                if (bHasResponsible) {
                    for (var resp = 0; resp < data.RESPONSIBLE.length; resp++) {
                        var rr = parseInt(Math.random() * 100000);
                        if (window.arMembersList.length <= 0) {
                            var rrf = function () {
                                var u = BX('user_name_' + rr);
                                if (u && u.getAttribute("bxuserid") && window.arMembersList[u.getAttribute("bxuserid")]) {
                                    u.innerHTML = BX.util.htmlspecialchars(window.arMembersList[u.getAttribute("bxuserid")].name);
                                }
                                BX.removeCustomEvent('onMeetingChangeUsersList', rrf);
                            };
                            BX.addCustomEvent('onMeetingChangeUsersList', rrf);

                            h += '<a href="' + getUserUrl(data.RESPONSIBLE[resp]) + '" class="meeting-ag-report-name" id="user_name_' + rr + '" bxuserid="' + data.RESPONSIBLE[resp] + '"></a>';
                        } else if (window.arMembersList[data.RESPONSIBLE[resp]]) {
                            h += '<a href="' + getUserUrl(data.RESPONSIBLE[resp]) + '" class="meeting-ag-report-name">Докладчик: ' + BX.util.trim(BX.util.htmlspecialchars(window.arMembersList[data.RESPONSIBLE[resp]].name)) + '</a>';
                        }
                    }
                }
                if (bHasCustomResponsible) {
                    if (bHasResponsible) {
                        h += '<span class="meeting-ag-report-name">, ' + data.RESPONSIBLE_CUSTOM + '</span>';
                    } else {
                        h += '<span class="meeting-ag-report-name">Докладчик: ' + data.RESPONSIBLE_CUSTOM + '</span>';
                    }
                }
            }

            if (bHasFiles) {
                h += '<button type="button" onclick="BX.toggle(BX.nextSibling(this));">Материалы</button>';
                h += '<div class="report-files-wrapper" style="display: none;">';
                for (var j = 0; j < data.FILES.length; j++) {
                    h += '<span class="meeting-ag-report-file-wrap"><span class="meeting-ag-report-ic"></span><a class="meeting-ag-report-file" href="' + data.FILES[j].DOWNLOAD_URL + '">' + data.FILES[j].ORIGINAL_NAME + '</a>' +/*<span class="meeting-del-icon"></span>*/'</span>';
                }
                h += '</div>';
            }
            // h += '<button type="button">Голосование</button>';
            h += '<span class="meeting-ag-report-lt"></span><span class="meeting-ag-report-rt"></span><span class="meeting-ag-report-lb"></span><span class="meeting-ag-report-rb"></span></span>';
        }

        h += '\
</div>\
<div id="js-file-list-wrap-' + data.ID + '"></div>\
<div class="meeting-comments-wrap" id="agenda_item_comments_' + key + '" style="display: none;"><span onclick="toggleComments(null, this.parentNode);" class="meeting-hide-com meeting-dash-link"><?=CUtil::JSEscape(GetMessage('ME_AGENDA_HIDE_COMMENTS'))?></span><div></div></div></div></div></div>\
<div class="meeting-ag-block-bottom"><div class="meeting-ag-block-bl"></div><div class="meeting-ag-block-br"></div></div>';
        <?
        if($arResult['CAN_EDIT']):
        ?>
//edit buttons data.INSTANCE_PARENT_ID 
        if (!data.STATUS || data.STATUS == 'CLOSE') {
            h += '\
<div class="meeting-ag-edit-block"><div class="meeting-ag-edit-bl-cont"><div class="meeting-ag-edit-close" onmousedown="BX.PreventDefault(arguments[0])" onclick="deleteRow(\'' + key + '\', BX.findParent(this, window.listItemParams.isItem));" title="<?=CUtil::JSEscape(htmlspecialcharsbx(GetMessage('ME_AGENDA_TT_DELETE')))?>"></div><div class="meeting-ag-edit-edit" onmousedown="BX.PreventDefault(arguments[0])" onclick="editRow(BX.findParent(this, window.listItemParams.isItem));" title="<?=CUtil::JSEscape(htmlspecialcharsbx(GetMessage('ME_AGENDA_TT_EDIT')))?>"></div></div>\
<div class="meeting-ag-edit-bl-top"></div><div class="meeting-ag-edit-bl-bot"></div></div>\
<div class="meeting-ag-add-sub-item" title="<?=CUtil::JSEscape(htmlspecialcharsbx(GetMessage('ME_AGENDA_TT_ADDSUB')))?>"><span  onmousedown="BX.PreventDefault(arguments[0])" onclick="plusClick(this)"></span></div><div class="meeting-ag-draggable" title="<?=CUtil::JSEscape(htmlspecialcharsbx(GetMessage('ME_AGENDA_TT_DRAG')))?>"></div><div  onmousedown="BX.PreventDefault(arguments[0])" onclick="shiftRow(this.parentNode)" class="meeting-ag-shift" title="<?=CUtil::JSEscape(htmlspecialcharsbx(GetMessage('ME_AGENDA_TT_SHIFT')))?>"></div>';
        }
        <?
        endif;
        ?>

        data.ID = key;

        var row = BX.create('DIV', {
            props: {
                BXINSTANCEKEY: key,
                BXINSTANCE: data,
                BXLISTITEM: true,
                id: 'agenda_item_' + key,
                className: 'meeting-agenda-block' + (data.TASKS_COUNT[0] > 0 ? ' meeting-block-has-tasks' : '') + (data.COMMENTS_COUNT[0] > 0 ? ' meeting-block-has-comments' : '') + (parent && parent != 'outside' ? ' meeting-agenda-sub-block' : '') + (data.ORIGINAL_TYPE == '<?=CMeetingInstance::TYPE_TASK?>' ? ' meeting-agenda-new-sub-block' : '')
            },
            events: {
                mouseover: function () {
                    BX.addClass(this, 'meeting-agenda-block-hover')
                },
                mouseout: function () {
                    BX.removeClass(this, 'meeting-agenda-block-hover')
                }
            },
            html: h
        });

        if (!bReturn) {
            var q = BX.create('DIV', {props: {id: 'agenda_blocks_' + key}}),
                bAppend = true;

            if (previousSibling) {
                if (previousSibling.parentNode == p) {
                    if (previousSibling.nextSibling) {
                        bAppend = false;
                        p.insertBefore(q, previousSibling.nextSibling.nextSibling);
                    }
                } else if (p.firstChild) {
                    bAppend = false;
                    p.insertBefore(q, p.firstChild);
                }
            }

            if (bAppend) {
                p.appendChild(q);
            }


            p.insertBefore(row, q);
            updateIndexes();
            copyFilesBlock(data.ID);
        } else {
            if (!!files) {
                setTimeout(function () {
                    BX.append(files, BX('js-file-list-wrap-' + data.ID));
                }, 50);
            }
        }


        <?
        if ($arResult['CAN_EDIT']):
        ?>
        row.onbxdragstart = rowDragStart;
        row.onbxdrag = rowDragMove;
        row.onbxdragstop = rowDragStop;
        row.onbxdraghover = rowDragHover;

        jsDD.registerDest(row);
        if (row.nextSibling) {
            jsDD.registerDest(row.nextSibling);
            if (data.INSTANCE_PARENT_ID)
                jsDD.disableDest(row.nextSibling);
        }

        jsDD.registerObject(row);
        <?
        endif;
        ?>
        return row;
    }

    function copyFilesBlock(id) {

        var wrap = BX('js-file-list-wrap-' + id);
        var files = BX.findChild(BX('js-agenda-' + id));

        if (files) {
            wrap.append(files);
            initViewer('js-file-list-wrap-' + id);
        }
    }

    function initViewer(id) {
        var area = BX(id);
        if (area) {
            top.BX.viewElementBind(
                area,
                {},
                function (node) {
                    return BX.type.isElementNode(node) &&
                        (node.getAttribute("data-bx-viewer") || node.getAttribute("data-bx-image"));
                }
            );
        }
    };

    <?
    if ($arResult['CAN_EDIT']):
    ?>
    function deleteRow(item_id, row, bShiftChildren, bSkipConfirm) {
        row = row || BX('agenda_item_' + item_id);
        item_id = item_id || row.BXINSTANCEKEY;

        var bNew = isNaN(parseInt(row.BXINSTANCE.ID));

        if (!!bSkipConfirm || bNew || confirm(row.BXINSTANCE.ORIGINAL_TYPE == '<?=CMeetingInstance::TYPE_AGENDA?>' ? '<?=CUtil::JSEscape(GetMessage('ME_AGENDA_CONFIRM_AGENDA'))?>' : '<?=CUtil::JSEscape(GetMessage('ME_AGENDA_CONFIRM_PROTO'))?>')) {
            if (row && item_id) ;
            {
                hideComments(row);

                row.style.display = 'none';
                row.BXDELETED = true;
                row.appendChild(BX.create('INPUT', {
                    props: {
                        type: 'hidden', name: 'AGENDA_DELETED[' + item_id + ']', value: 'Y'
                    }
                }));

                var row_children = BX('agenda_blocks_' + item_id);
                if (row_children) {
                    var rows = BX.findChildren(row_children, window.listItemParams.isItem);
                    if (rows && rows.length > 0) {
                        for (var i = 0; i < rows.length; i++) {
                            if (!!bShiftChildren)
                                shiftRow(rows[i]);
                            else
                                deleteRow(rows[i].BXINSTANCEKEY, rows[i], bShiftChildren, true);
                        }
                    }
                    row_children.style.display = 'none';
                }

                updateIndexes();

                jsDD.disableDest(row);
                jsDD.disableDest(row.nextSibling);

                jsDD.refreshDestArea();

                if (!bNew)
                    saveData();

                if (row == window.currently_edited_row) {
                    window.currently_edited_row = null;
                    jsDD.Enable();
                }
            }
        }
    }

    function unDeleteRow(item_id, row) {
        row = row || BX('agenda_item_' + item_id);
        item_id = item_id || row.BXINSTANCEKEY;

        if (row && item_id) ;
        {
            row.style.display = '';
            row.BXDELETED = false;
            row.removeChild(document.forms.meeting_edit['AGENDA_DELETED[' + item_id + ']']);

            var row_children = BX('agenda_blocks_' + item_id);
            if (row_children)
                row_children.style.display = '';

            updateIndexes();

            jsDD.enableDest(row);
            jsDD.enableDest(row.nextSibling);

            jsDD.refreshDestArea();

            if (!bNew)
                saveData();
        }
    }

    function shiftRow(row) {
        if (BX.hasClass(row, 'meeting-agenda-sub-block')) {
            var prev_parent = row.parentNode, prev_sibling = row.nextSibling;

            row.BXINSTANCE.INSTANCE_PARENT_ID = document.forms.meeting_edit['AGENDA_PARENT[' + row.BXINSTANCEKEY + ']'].value = 0;

            row.parentNode.removeChild(row);
            prev_sibling.parentNode.removeChild(prev_sibling);

            if (prev_parent.nextSibling)
                prev_parent.parentNode.insertBefore(prev_sibling, prev_parent.nextSibling);
            else
                prev_parent.parentNode.appendChild(prev_sibling);

            prev_parent.parentNode.insertBefore(row, prev_sibling);

            jsDD.enableDest(prev_sibling);
        } else {
            var new_parent = row.previousSibling, prev_sibling = row.nextSibling;

            while (!!new_parent && new_parent.style.display == 'none') {
                new_parent = new_parent.previousSibling;
            }

            if (!new_parent)
                return;

            var arChildren = [];
            while (prev_sibling.lastChild)
                arChildren.push(shiftRow(prev_sibling.lastChild.previousSibling));

            row.BXINSTANCE.INSTANCE_PARENT_ID = document.forms.meeting_edit['AGENDA_PARENT[' + row.BXINSTANCEKEY + ']'].value = new_parent.previousSibling.BXINSTANCEKEY;

            new_parent.appendChild(row);
            new_parent.appendChild(prev_sibling);

            while (arChildren[0])
                shiftRow(arChildren.pop());

            jsDD.disableDest(prev_sibling);
        }

        BX.toggleClass(row, 'meeting-agenda-sub-block');

        jsDD.refreshDestArea();
        updateIndexes();

        BX.onCustomEvent('onMeetingCommentsChange');

        saveData();

        return row;
    }

    function checkParent(item_id) {
        var p = document.forms.meeting_edit['AGENDA_PARENT[' + item_id + ']'], r = BX('agenda_item_' + item_id);

        if (r && p) {
            var new_parent = BX(p.value == 0 ? 'agenda' : ('agenda_blocks_' + p.value)), old_parent = r.parentNode,
                n_sibling = r.nextSibling;

            if (new_parent && new_parent != old_parent) {
                old_parent.removeChild(r);
                old_parent.removeChild(n_sibling);

                if (old_parent.parentNode.parentNode == new_parent && old_parent.parentNode.nextSibling)
                    new_parent.insertBefore(n_sibling, old_parent.parentNode.nextSibling);
                else
                    new_parent.appendChild(n_sibling);

                new_parent.insertBefore(r, n_sibling)

                BX.addClass(r, 'meeting-agenda-sub-block');

                return new_parent.parentNode.BXINSTANCEKEY;
            }
        }
    }

    function addNext(r) {
        return editRow(addRow({
            EDITABLE: true,
            INSTANCE_PARENT_ID: (r.BXINSTANCE.INSTANCE_PARENT_ID ? r.BXINSTANCE.INSTANCE_PARENT_ID : r.BXINSTANCE.ID)
        }, r));
    }

    function plusClick(el) {
        var r = BX.findParent(el, window.listItemParams.isItem);
        if (r)
            addNext(r);
    }

    function checkboxClick(el) {
        var r = BX.findParent(el, window.listItemParams.isItem);
        if (r) {
            var inp = document.forms.meeting_edit['AGENDA_TYPE[' + r.BXINSTANCEKEY + ']'];
            if (inp.value == '<?=CMeetingInstance::TYPE_AGENDA?>') {
                inp.value = '<?=CMeetingInstance::TYPE_TASK?>';
                BX.removeClass(el, 'meeting-checkbox-checked');
                BX.removeClass(el.parentNode, 'meeting-ag-cont-block-checked');
            } else {
                inp.value = '<?=CMeetingInstance::TYPE_AGENDA?>';
                BX.addClass(el, 'meeting-checkbox-checked');
                BX.addClass(el.parentNode, 'meeting-ag-cont-block-checked');
            }
        }

        saveData();
    }

    function checkEnter(e) {
        switch ((e || window.event).keyCode) {
            case 13:
                addNext(viewRow(this, true));
                return BX.PreventDefault(e);
            case 27:
                viewRow(this, false);
                return BX.PreventDefault(e);
            case 9: // tab
                shiftRow(this);
                BX.focus(BX.proxy_context);
                return BX.PreventDefault(e);
        }
    }

    function showTaskSelector(el) {
        if (!window.task_selector_wnd) {
            var q = BX('task_selector');
            q.parentNode.removeChild(q);
            q.style.display = 'block';
            window.task_selector_wnd = new BX.PopupWindow('task_selector', el, {
                autoHide: true,
                lightShadow: true,
                content: q,
                bindOptions: {forceBindPosition: true}
            });
        } else {
            window.task_selector_wnd.setBindElement(el);
        }

        window.task_selector_wnd.show();
    }

    function addTaskRow(task) {
        addRow({
            TITLE: BX.util.htmlspecialchars(task.name),
            TASK_ID: task.id,
            EDITABLE: true,
            RESPONSIBLE: [<?=$USER->GetID()?>]
        });
        window.task_selector_wnd.close();
        saveData();
    }
    <?
    endif;
    ?>

    function showComments(row, com_row) {
        toggleComments(row, com_row, 'block');
    }

    function hideComments(row, com_row) {
        toggleComments(row, com_row, 'none');
    }

    function deleteComments(row, com_row) {
        toggleComments(row, com_row, 'delete');
    }

    function toggleComments(row, com_row, display) {
        if (!!window.bCommentsLoadingInProgress) {
            return;
        }

        com_row = com_row || BX('agenda_item_comments_' + row.BXINSTANCEKEY);
        if (com_row) {
            if (display == 'delete') {
                if (com_row.bx_comments)
                    com_row.bx_comments.parentNode.removeChild(com_row.bx_comments);
                com_row.parentNode.removeChild(com_row);
                return;
            }

            if (display) {
                com_row.style.display = display;
                if (com_row.bx_comments)
                    com_row.bx_comments.style.display = display;
            } else {
                BX.toggle(com_row);
                if (com_row.bx_comments)
                    BX.toggle(com_row.bx_comments);
            }
            <?
            if ($arResult['CAN_EDIT']):
            ?>
            if (com_row.style.display == 'block')
                jsDD.Disable();
            else {
                jsDD.Enable();
            }
            <?
            endif;
            ?>
            if (com_row && row && !row.BXINSTANCECOMMENTSLOADED && display != 'none') {
                row.BXINSTANCECOMMENTSLOADED = true;

                var c = com_row.lastChild.appendChild(BX.create('DIV', {
                    props: {
                        className: 'agenda-comments-pos'
                    }
                }));
                var cont = BX.create('DIV', {
                        props: {
                            className: 'agenda-comments-frame',
                            bx_pos_cont: c
                        }
                    }
                );
                com_row.bx_comments = cont;

                window.bCommentsLoadingInProgress = true;
                BX.ajax.get('<?=CUtil::JSEscape($arParams['ITEM_URL'])?>'.replace('#ITEM_ID#', row.BXINSTANCE.ITEM_ID), {MEETING_ITEM_COMMENTS: row.BXINSTANCE.ITEM_ID}, function (r) {
                    window.bCommentsLoadingInProgress = false;
                    var pos = BX.pos(c);

                    document.body.appendChild(BX.adjust(cont, {
                        props: {
                            className: 'agenda-comments-frame',
                            bx_pos_cont: c
                        },
                        style:
                            {
                                top: pos.top + 'px',
                                left: pos.left + 'px',
                                height: pos.height + 'px',
                                width: pos.width + 'px'
                            },
                        html: r
                    }));

                    BX.addCustomEvent('onMeetingCommentsChange', BX.proxy(_onMeetingCommentsChange, cont));

                    BX.addCustomEvent('onAjaxSuccess', BX.proxy(_onAjaxSuccess, cont));
                    BX.addCustomEvent('OnUCListWasBuilt', BX.defer(_onAjaxSuccess, cont));
                    BX.addCustomEvent('OnUCRecordHasDrawn', BX.defer(_onAjaxSuccess, cont));
                    BX.addCustomEvent('OnUCommentWasDeleted', BX.defer(_onAjaxSuccess, cont));
                    BX.addCustomEvent('OnUCommentWasHidden', BX.defer(_onAjaxSuccess, cont));
                    BX.addCustomEvent('OnUCAfterRecordEdit', BX.defer(_onAjaxSuccess, cont));
                    BX.addCustomEvent('OnUCFeedChanged', BX.defer(_onAjaxSuccess, cont));
                    BX.addCustomEvent('OnUCListWasHidden', BX.defer(_onAjaxSuccess, cont));
                    BX.addCustomEvent('OnUCListWasShown', BX.defer(_onAjaxSuccess, cont));
                    BX.addCustomEvent('OnUCFormAfterShow', BX.defer(_onAjaxSuccess, cont));
                    BX.addCustomEvent('OnAfterHideLHE', BX.defer(_onAjaxSuccess, cont));
                    BX.addCustomEvent('OnUCRecordWasExpanded', BX.defer(_onAjaxSuccess, cont));
                    BX.addCustomEvent('OnEditorResizedAfter', BX.defer(_onAjaxSuccess, cont));

                    setTimeout(BX.proxy(_onAjaxSuccess, cont), 50);
                });
            }

            BX.onCustomEvent('onMeetingCommentsChange');
        }
    }

    function _onAjaxSuccess() {
        (BX.proxy(_onAjaxSuccessRepeat, this))();
        setTimeout(BX.proxy(_onAjaxSuccessRepeat, this), 300);
    }

    function _onAjaxSuccessRepeat() {
        var cc = BX.findChild(this, {tag: 'DIV'});
        if (cc)
            this.style.height = this.bx_pos_cont.style.height = (cc.offsetHeight + 30) + 'px';

        BX.onCustomEvent('onMeetingCommentsChange');
    }

    function _onMeetingCommentsChange() {
        var pos = BX.pos(this.bx_pos_cont);
        if (pos.top > 0) {
            BX.adjust(this, {
                style:
                    {
                        top: pos.top + 'px',
                        left: pos.left + 'px'
                    }
            });
        }
    }

    function showReport(r, el) {
        if (!window['BXREPORTPOPUP_' + r]) {
            var data = window['report_popup_data_' + r];

            var h = '<div class="meeting-report-popup"><div class="meeting-report-popup-text-wrap"><div class="meeting-report-popup-text">' + data.REPORT + '</div></div>';
            if (data.FILES && data.FILES.length > 0) {
                h += '<div class="popup-window-hr popup-window-buttons-hr"><i></i></div><div class="meeting-detail-files"><label class="meeting-detail-files-title">' + '<?=CUtil::JSEscape(GetMessage('ME_FILES'))?>' + ':</label><span class="meeting-detail-files-list">';

                for (var i = 0; i < data.FILES.length; i++) {
                    h += '<span class="meeting-detail-file"><span class="meeting-detail-file-number">' + (i + 1) + '.</span><span class="meeting-detail-file-info">' + (data.FILE_SRC > 0 ? '<span class="meeting-detail-file-comment"></span>' : '') + '<a href="' + data.FILES[i].DOWNLOAD_URL + '" class="meeting-detail-file-link">' + data.FILES[i].ORIGINAL_NAME + '</a><span class="meeting-detail-file-size">(' + data.FILES[i].FILE_SIZE_FORMATTED + ')</span></span></span>';
                }

                h += '</span>';
            }

            h += '</div></div>';

            window['BXREPORTPOPUP_' + r] = BX.PopupWindowManager.create('report_popup_' + r, el,
                {
                    content: h,
                    autoHide: true,
                    closeByEsc: true,
                    offsetTop: 10,
                    offsetLeft: -20,
                    bindOptions: {forceBindPosition: true},
                    angle: {offset: 27},
                    buttons: [
                        new BX.PopupWindowButton({
                            text: BX.message('JS_CORE_WINDOW_CLOSE'),
                            className: "popup-window-button-decline meeting-report-popup-but",
                            events: {
                                click: function () {
                                    this.popupWindow.close();
                                }
                            }
                        })
                    ]
                }
            );
        }

        window['BXREPORTPOPUP_' + r].show();
    }

    <?
    if ($arResult['CAN_EDIT']):
    ?>
    /* drag'n'drop */
    window.bxcp = null;
    window.bxpos = null;
    window.bxparent = null;
    window.bxblank = null;
    window.bxblank1 = null;

    function rowDragStart() {
        window.bxparent = this.parentNode;
        window.bxblank = window.bxparent.insertBefore(BX.create('DIV', {style: {height: '0px'}}), this);
        window.bxblank1 = BX.create('DIV', {style: {height: (this.offsetHeight + this.nextSibling.offsetHeight + 5) + 'px'}});
        jsDD.disableDest(window.bxparent);

        window.bxcp = BX.create('DIV', {
            style: {
                position: 'absolute',
                zIndex: '100',
                width: (this.offsetWidth - 5) + 'px'
            },
            children: [this, this.nextSibling]
        })


        window.bxpos = BX.pos(window.bxparent);

        window.bxparent.style.position = 'relative';
        window.bxparent.appendChild(window.bxcp);
    }

    function rowDragMove(x, y) {
        y -= window.bxpos.top;

        if (y < 0)
            y = 0;
        if (y > window.bxpos.height + this.parentNode.offsetHeight)
            y = window.bxpos.height;

        window.bxcp.style.top = y + 'px';
    }

    function rowDragStop() {
        if (window.bxblank1) {
            var q = this.nextSibling;
            if (window.bxblank1.parentNode)
                window.bxparent.replaceChild(q, window.bxblank1);
            else
                window.bxparent.appendChild(q);

            window.bxparent.insertBefore(this, q);

            window.bxcp.parentNode.removeChild(window.bxcp);
            window.bxblank.parentNode.removeChild(window.bxblank);

            jsDD.enableDest(window.bxparent);
            window.bxparent.style.position = 'static';

            window.bxcp = null;
            window.bxblank = null;
            window.bxblank1 = null;
            window.bxparent = null;
            jsDD.refreshDestArea();
            updateIndexes();
            saveData();
        }
    }

    function rowDragHover(dest, x, y) {
        if (dest == this) {
            window.bxblank.parentNode.insertBefore(window.bxblank1, window.bxblank);
        } else if (dest.parentNode == window.bxparent) {
            if (dest.BXINSTANCEKEY) {
                if (document.forms.meeting_edit['AGENDA_SORT[' + this.BXINSTANCEKEY + ']'].value >= document.forms.meeting_edit['AGENDA_SORT[' + dest.BXINSTANCEKEY + ']'].value) {
                    dest.parentNode.insertBefore(window.bxblank1, dest);
                } else if (dest.nextSibling.nextSibling) {
                    dest.parentNode.insertBefore(window.bxblank1, dest.nextSibling.nextSibling);
                } else {
                    dest.parentNode.appendChild(window.bxblank1);
                }
            } else {
                rowDragHover.apply(this, [dest.previousSibling, x, y]);
            }
        }
    }
    <?
    endif;
    ?>

    BX.ready(function () {
        window.listItemParams.startDiv = [BX('agenda_blocks'), BX('agenda_blocks_outside')];
        <?
        if ($arResult['CAN_EDIT']):
        if (!$arResult['MEETING']['AGENDA']):
        ?>
        editRow(addRow({EDITABLE: true}, null), true);
        <?
        endif;
        ?>
        jsDD.refreshDestArea();
        <?
        endif;
        //=$templateFolder
        ?>
    });

    function sendNotice() {
        var noticeUrl, data;
        noticeUrl = '<?=$templateFolder?>/notice_ajax.php';
        data = BX.ajax.prepareForm(document.forms.meeting_edit);
        BX.ajax({
            url: noticeUrl,
            data: data,
            method: 'POST',
            dataType: 'json',
            timeout: 30,
            async: true,
            processData: true,
            scriptsRunFirst: true,
            emulateOnload: true,
            start: true,
            cache: false,
            onsuccess: function (data) {
                if (data.CHECK == 'ERROR') {
                    console.error(data.MESSAGE);
                }
            },
            onfailure: function () {

            }
        });
    }

    function toogleAgendaStatus() {
        var noticeUrl, data;
        noticeUrl = '<?=$templateFolder?>/agenda_ajax.php';
        data = {
            MEETING_ID: '<?=$arResult['MEETING']['ID']?>',
            NEED_AGREE: 'Y',
        };
        BX.ajax({
            url: noticeUrl,
            data: data,
            method: 'POST',
            dataType: 'json',
            timeout: 30,
            async: true,
            processData: true,
            scriptsRunFirst: true,
            emulateOnload: true,
            start: true,
            cache: false,
            onsuccess: function (data) {
                if (data.CHECK == 'SUCCESS') {
                    BX('approval_button').removeAttribute('style');
                } else if (data.CHECK == 'ERROR') {
                    console.error(data.MESSAGE);
                }
            },
            onfailure: function () {

            }
        });
    }

    function saveResponsible() {
        var customResposible, data = {}, id, meetingId, url;
        customResposible = document.querySelectorAll('input[name^=AGENDA_RESPONSIBLE_CUSTOM]');
        customResposible.forEach(function (node) {
            id = node.name.replace('AGENDA_RESPONSIBLE_CUSTOM[', '');
            id = id.replace(']', '');
            data[id] = node.value;
        });
        meetingId = document.forms.meeting_edit['MEETING_ID'].value;
        url = '<?=$templateFolder?>/agenda_ajax.php';
        data = {
            DATA: data,
            MEETING_ID: meetingId,
            ACTION: 'SAVE_RESPONSOBLE',
        };

        BX.ajax({
            url: url,
            data: data,
            method: 'POST',
            dataType: 'json',
            //timeout: 30,
            async: true,
            processData: true,
            scriptsRunFirst: true,
            emulateOnload: true,
            start: true,
            cache: false,
            onsuccess: function (data) {
            },
            onfailure: function () {
            }
        });
    }

    //Проверяем права доступа, участникам подключаем проверку кнопок, секретарю и председателю нет
    var checkAccess = '<?=$arResult['ACCESS'];?>';
    console.log(checkAccess);
    if (checkAccess === 'M') {
        //запускаем проверку кнопок сразу после загрузки
        window.onload = function () {
            getVotingOffBtnId();
        };
        //запускаем проверку кнопок каждые 5 сек
        window.setInterval(function () {
                getVotingOffBtnId();
            },
            5000);
    }
</script>


