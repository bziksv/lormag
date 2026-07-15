<?define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader,
	Bitrix\Main\Application;

$moduleClass = "CEnext";
$moduleID = "altop.enext";

if(!Loader::IncludeModule($moduleID))
	return;

$request = Application::getInstance()->getContext()->getRequest();

if($request->isAjaxRequest() && $request->isPost() && $request->getPost("action") == "change_theme" && check_bitrix_sessid()) {
	$siteId = $request->getPost("SITE_ID") ? $request->getPost("SITE_ID") : SITE_ID;
	$moduleClass::UpdateParametrsValues($siteId);
	$theme = $request->getPost("THEME");
	if($theme != "default") {
		$colorScheme = $request->getPost("COLOR_SCHEME");
		if($colorScheme == "CUSTOM")
			$moduleClass::GenerateColorScheme($siteId);
	}
}