<?define("NOT_CHECK_PERMISSIONS", true);

$siteId = isset($_REQUEST["siteId"]) && is_string($_REQUEST["siteId"]) ? $_REQUEST["siteId"] : "";
$siteId = substr(preg_replace("/[^a-z0-9_]/i", "", $siteId), 0, 2);
if(!empty($siteId) && is_string($siteId)) {
	define("SITE_ID", $siteId);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$context = Bitrix\Main\Application::getInstance()->getContext();

$response = $context->getResponse();
$response->addHeader("Content-Type", "application/json");

$request = $context->getRequest();

if($request->isAjaxRequest()) {
	$action = $request->get("action");
	if($action == "sPanelGeoLocationRequest") {
		$signer = new Bitrix\Main\Security\Sign\Signer;
		$parameters = unserialize(base64_decode($signer->unsign($request->get("parameters"), "geo.location.enext")));

		$parameters["SITE_ID"] = SITE_ID;

		$siteServerName = $request->get("siteServerName") ?: SITE_SERVER_NAME;
		$parameters["SITE_SERVER_NAME"] = $siteServerName;

		$languageId = $request->get("languageId") ?: LANGUAGE_ID;
		$parameters["LANGUAGE_ID"] = $languageId;
		
		$APPLICATION->IncludeComponent("altop:geo.location.enext", "slide_panel",
			$parameters,
			false
		);
		
		$content = ob_get_contents();
		ob_end_clean();
		
		if(Bitrix\Main\Loader::includeModule("iblock")) {
			$response->flush(Bitrix\Iblock\Component\Base::sendJsonAnswer(array(
				"content" => $content
			)));
		}
	} elseif($action == "changeGeoLocation") {
		$siteServerName = $request->get("siteServerName") ?: SITE_SERVER_NAME;

		$locationId = (int)$request->get("locationId");
		if($locationId > 0) {
			$cookie = new Bitrix\Main\Web\Cookie("ENEXT_GEO_LOCATION_ID", $locationId, time() + 32832000);
			$cookie->setDomain($siteServerName);
			$cookie->setHttpOnly(false);
			
			$response->addCookie($cookie);
			
			$response->flush(Bitrix\Main\Web\Json::encode(array(
				"status" => true
			)));
		}
	}
}