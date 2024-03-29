 <?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
\Bitrix\Main\UI\Extension::load("ui.buttons");
\Bitrix\Main\UI\Extension::load("ui.buttons.icons");

Loc::loadMessages(__FILE__);

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var CBitrixComponent $component */

$helper = $arResult['HELPER'];

$taskId = $arParams["TASK_ID"];
$can = $arParams["TASK"]["ACTION"];
$taskData = $arParams["TASK"];

 if (\Bitrix\Main\ModuleManager::isModuleInstalled('rest'))
 {
	 $APPLICATION->IncludeComponent(
		 'bitrix:app.placement',
		 'menu',
		 array(
			 'PLACEMENT'         => "TASK_LIST_CONTEXT_MENU",
			 "PLACEMENT_OPTIONS" => array(),
			 //			'INTERFACE_EVENT' => 'onCrmLeadListInterfaceInit',
			 'MENU_EVENT_MODULE' => 'tasks',
			 'MENU_EVENT'        => 'onTasksBuildContextMenu',
		 ),
		 null,
		 array('HIDE_ICONS' => 'Y')
	 );
 }

?>

<div id="<?=$helper->getScopeId()?>" class="task-view-buttonset <?=implode(' ', $arResult['CLASSES'])?>">

	<span data-bx-id="task-view-b-timer" class="task-timeman-link">
		<span class="task-timeman-icon"></span>
		<span id="task_details_buttons_timer_<?=$taskId?>_text" class="task-timeman-text">

		<span data-bx-id="task-view-b-time-elapsed"><?=\Bitrix\Tasks\UI::formatTimeAmount($taskData['TIME_ELAPSED']);?></span>

		<?if ($taskData["TIME_ESTIMATE"] > 0):?>
			/ <?=\Bitrix\Tasks\UI::formatTimeAmount($taskData["TIME_ESTIMATE"]);?>
		<?endif?>
		</span>
		<span class="task-timeman-arrow"></span>
	</span>

	<span data-bx-id="task-view-b-buttonset">

		<?
		$styleShow = '';
		$styleHide = '';
		//$arExtStatuses = array(5,7);

        $isTypeTask = false;
		if ($arParams['TASK']['UF_TYPE'] == 30 && $arParams['USER_ID'] == $arParams['TASK']['CREATED_BY'] && $arParams['TASK']['STATUS'] == 5) {
			$styleShow = ' style="display:inline-block!important;"';
			$styleHide = ' style="display:none!important;"';
			$isTypeTask = true;
		}
		?>
		<span data-bx-id="task-view-b-button" data-action="START_TIMER" class="task-view-button timer-start ui-btn ui-btn-success"<? if ($styleHide) {echo $styleHide;} ?>>
			<?=Loc::getMessage("TASKS_START_TASK_TIMER")?>
		</span><?

		?><span data-bx-id="task-view-b-button" data-action="PAUSE_TIMER" class="task-view-button timer-pause ui-btn ui-btn-light-border"<? if ($styleHide) {echo $styleHide;} ?>>
			<?=Loc::getMessage("TASKS_PAUSE_TASK_TIMER")?>
		</span><?

		?>
        <span data-bx-id="task-view-b-button"
              <?/*data-action="START" */?>
              data-action="COMPLETE"
              <?=!$isTypeTask ? ' style="display:inline-block!important" ' : ''?>
              class="task-view-button start ui-btn ui-btn-success"<? if ($styleHide) {echo $styleHide;} ?>>
            <?if ($arParams['USER_ID'] == $arParams['TASK']['CREATED_BY']) {?>
                <?if ($arParams['TASK']['UF_TYPE'] == 30 && $arParams['TASK']['STATUS'] == 5) {?>
                    <?=Loc::getMessage("TASKS_CLOSE_TASK")?>
                <?} else {?>
                    Завершить
                <?}?>
            <?} else {?>
                Завершить
            <?}?>

		</span><?

		?><span data-bx-id="task-view-b-button" data-action="PAUSE" class="task-view-button pause ui-btn ui-btn-success"<? if ($styleHide) {echo $styleHide;} ?>>
			<?=Loc::getMessage("TASKS_PAUSE_TASK")?>
		</span><?
		?>
        <?
        if ($arParams['TASK']['UF_TYPE'] != 30 && $arParams['TASK']['STATUS'] == 5 && $arParams['USER_ID'] == $arParams['TASK']['CREATED_BY']) {
            $isShow = true;
        }?>
        <span data-bx-id="task-view-b-button" data-action="APPROVE"  class="task-view-button approve ui-btn ui-btn-success"<?=$isShow ? ' style="display:inline-block!important"' : $styleHide?>>
			<?=Loc::getMessage("TASKS_APPROVE_TASK")?>
		</span><?
        if ($arParams['TASK']['UF_TYPE'] != 30 && $arParams['TASK']['STATUS'] == 5 && $arParams['USER_ID'] == $arParams['TASK']['CREATED_BY']) {
            $isShow = true;
        }?>
		<span data-bx-id="task-view-b-button" data-action="DISAPPROVE" class="task-view-button disapprove ui-btn ui-btn-danger"<?=$isShow ? ' style="display:inline-block!important"' : $styleHide?>>
			<?=Loc::getMessage("TASKS_REDO_TASK")?>
		</span><?
		// for approve agenda
            ?><span data-bx-id="task-view-b-button" data-action="APPROVE_AGENDA"
                    class="task-view-button approve-agenda ui-btn ui-btn-success"<? if ($styleShow) {
                echo $styleShow;
            } ?>>
            <?= Loc::getMessage("TASKS_APPROVE_AGENDA") ?>
            </span><?
		// for reject agenda
		?><span data-bx-id="task-view-b-button" data-action="REJECT_AGENDA" class="task-view-button reject-agenda ui-btn ui-btn-danger"<? if ($styleShow) {echo $styleShow;} ?>>
			<?=Loc::getMessage("TASKS_REJECT_AGENDA")?>
		</span><?

		?><span data-bx-id="task-view-b-open-menu" class="task-more-button ui-btn ui-btn-light-border ui-btn-dropdown">
			<?=Loc::getMessage("TASKS_MORE")?>
		</span><?

		?><a href="<?=$arResult['EDIT_URL']?>" class="task-view-button edit ui-btn ui-btn-link" data-slider-ignore-autobinding="true">
			<?=GetMessage("TASKS_EDIT_TASK")?>
		</a>

		<script type="text/html" data-bx-id="task-view-b-timeman-confirm-title">
			<span><?=Loc::getMessage('TASKS_TASK_CONFIRM_START_TIMER_TITLE');?></span>
		</script>
		<script type="text/html" data-bx-id="task-view-b-timeman-confirm-body">
			<div style="width: 400px; padding: 25px;"><?=Loc::getMessage('TASKS_TASK_CONFIRM_START_TIMER');?></div>
		</script>

	</span>
</div>

	<script>
		BX.message({
			TASKS_REST_BUTTON_TITLE: '<?=Loc::getMessage('TASKS_REST_BUTTON_TITLE')?>',
			TASKS_DELETE_SUCCESS: '<?=GetMessage('TASKS_DELETE_SUCCESS')?>',
			AGENDA_EMPTY_COMMENT_BLOCK: '<?=GetMessage('AGENDA_EMPTY_COMMENT_BLOCK')?>',
		});
	</script>
<?$helper->initializeExtension();?>