<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/company/vis_structure.php");
$APPLICATION->SetTitle(GetMessage("COMPANY_TITLE"));
$APPLICATION->AddChainItem(GetMessage("COMPANY_TITLE"), "vis_structure.php");
?>
<?
$APPLICATION->IncludeComponent("b24tech:intranet.structure.visual", ".default", Array(
	"DETAIL_URL" => "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#",	// Страница структуры компании
		"PROFILE_URL" => "/company/personal/user/#ID#/",	// Страница профиля пользователя
		"PM_URL" => "/company/personal/messages/chat/#ID#/",	// Страница отправки личного сообщения
		"NAME_TEMPLATE" => "",	// Отображение имени
		"USE_USER_LINK" => "Y",	// Выводить всплывающие информационные карточки пользователей
	),
	false
);
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>