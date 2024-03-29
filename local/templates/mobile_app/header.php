<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var $APPLICATION CAllMain
 **/

use Bitrix\Main\Page\AssetShowTargetType;

$platform = "android";
if (CModule::IncludeModule("mobileapp"))
{
	$platform = CMobile::$platform;
	if(!defined("SKIP_MOBILEAPP_INIT"))
		CMobile::Init();
}
else
{
	die();
}

\Bitrix\Main\Data\AppCacheManifest::getInstance()->setManifestCheckFile(SITE_DIR . "mobile/");
if(CModule::IncludeModule("mobile"))
{
	CJSCore::Init(array("mobile_ui"));
}

define("MOBILE_MODULE_VERSION", "18.5.3.1");
$moduleVersion = (defined("MOBILE_MODULE_VERSION") ? MOBILE_MODULE_VERSION : "default");
if(array_key_exists("IS_WKWEBVIEW", $_COOKIE) && $_COOKIE["IS_WKWEBVIEW"] == "Y")
{
	$moduleVersion .= "_wkwebview";
}

$APPLICATION->IncludeComponent("bitrix:mobile.data", "", Array(
	"START_PAGE" => SITE_DIR . "mobile/index.php?version=" . $moduleVersion,
	"MENU_PAGE" => SITE_DIR . "mobile/left.php?version=" . $moduleVersion,
	"CHAT_PAGE" => SITE_DIR . "mobile/im/right.php?version=" . $moduleVersion
), false, Array("HIDE_ICONS" => "Y"));
?><!DOCTYPE html>
<html<?= $APPLICATION->ShowProperty("manifest"); ?> class="<?= $platform; ?>">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=<?= SITE_CHARSET ?>"/>
	<meta name="format-detection" content="telephone=no">
	<?
	//commented for the Project Quality Control test
	//$APPLICATION->ShowHead();

	if (!defined("BX_DONT_INCLUDE_MOBILE_TEMPLATE_CSS"))
	{
		$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH . (defined('MOBILE_TEMPLATE_CSS') ? MOBILE_TEMPLATE_CSS : "/common_styles.css"));
	}

	if ($USER->IsAuthorized())
	{
		\Bitrix\Main\Page\Asset::getInstance()->addString(
			"<script>(window.BX||top.BX).message({ 'USER_ID': '".$USER->GetID()."'});</script>",
			$unique = false,
		\Bitrix\Main\Page\AssetLocation::AFTER_JS,
		\Bitrix\Main\Page\AssetMode::ALL
		);
	}

	$APPLICATION->AddBufferContent(array(&$APPLICATION, "GetHeadStrings"), 'BEFORE_CSS');
	$APPLICATION->ShowHeadStrings();
	$APPLICATION->ShowHeadScripts();
	$APPLICATION->AddBufferContent(array(&$APPLICATION, "GetCSS"), true, true, AssetShowTargetType::TEMPLATE_PAGE);
	CJSCore::Init(array('ajax', 'mobile_tools'));
	?>
	<title><?$APPLICATION->ShowTitle()?></title>
</head>
<body class="<?= $APPLICATION->ShowProperty("BodyClass"); ?>"><?
?>
<script>
	BX.message({
		MobileSiteDir: '<?=CUtil::JSEscape(htmlspecialcharsbx(SITE_DIR))?>'
	});
</script><?
?>