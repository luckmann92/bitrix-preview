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


if ($arResult) {?>
<div class="result-grid">
	<table class="table-result" >
		<thead class="result-grid-head">
			<tr>
				<th class="head-item name-question">Пункт решения/Участник</th>
				<?foreach($arResult['USERS'] as $user):?>
					<th class="head-item"><?=$user?></th>
				<?endforeach;?>
			</tr>
		</thead>
		<tbody class="result-grid-body">
			<?foreach($arResult["INSTANCES"] as $instance):?>
				<tr class="row">
					<td class="body-item name-question"><?=$instance['TITLE']?></td>
					<?foreach($arResult['USERS'] as $id => $user):?>
						<td class="body-item">
							<?if($arResult['ANSWERS'][$id][$instance['ID']]['UF_VALUE'] == "Y"):?>
								<img src="/upload/voting-icons/y.png" class="answer-icon">
							<?endif;?>	
							<?if($arResult['ANSWERS'][$id][$instance['ID']]['UF_VALUE'] == "N"):?>
								<img src="/upload/voting-icons/n.png" class="answer-icon">
							<?endif;?>	
							<?if($arResult['ANSWERS'][$id][$instance['ID']]['UF_VALUE'] == "A"):?>
								<img src="/upload/voting-icons/a.png" class="answer-icon">
							<?endif;?>	
						</td>
					<?endforeach;?>
				</tr>
			<?endforeach;?>
		</tbody>
	</table>
</div>
<?}?>