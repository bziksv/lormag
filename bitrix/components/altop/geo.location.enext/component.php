<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if(!Bitrix\Main\Loader::includeModule("sale"))
	return;

if(!isset($arParams["SITE_ID"]))
	$arParams["SITE_ID"] = SITE_ID;

if(!isset($arParams["SITE_SERVER_NAME"]))
	$arParams["SITE_SERVER_NAME"] = SITE_SERVER_NAME;

if(!isset($arParams["LANGUAGE_ID"]))
	$arParams["LANGUAGE_ID"] = LANGUAGE_ID;

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

$context = Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

$arSettings = CEnext::GetFrontParametrsValues($arParams["SITE_ID"]);

$arParams["LOCATION_ID"] = (int)$request->getCookie("ENEXT_GEO_LOCATION_ID");
if($arParams["LOCATION_ID"] <= 0) {
	$arParams["LOCATION_ID"] = $arSettings["DEFAULT_LOCATION_ID"];
	if(!preg_match("/Google|Yahoo|Rambler|Bot|Yandex|Spider|Snoopy|Crawler|Finder|Mail|curl/i", $request->getUserAgent())) {
		$ipAddress = Bitrix\Main\Service\GeoIp\Manager::getRealIp();
		$locId = Bitrix\Sale\Location\GeoIp::getLocationId($ipAddress, $arParams["LANGUAGE_ID"]);
		if(!empty($locId)) {
			$arParams["LOCATION_ID"] = $locId;
			$cookie = new Bitrix\Main\Web\Cookie("ENEXT_GEO_LOCATION_ID", $arParams["LOCATION_ID"], time() + 32832000);
			$cookie->setDomain($arParams["SITE_SERVER_NAME"]);
			$cookie->setHttpOnly(false);
			$context->getResponse()->addCookie($cookie);
		}
	}
}

if($this->StartResultCache()) {
	if($arParams["LOCATION_ID"] <= 0)
		$this->abortResultCache();	

	$rsLocation = Bitrix\Sale\Location\LocationTable::getList(array(
		"filter" => array(
			"=ID" => $arParams["LOCATION_ID"],
			"NAME.LANGUAGE_ID" => $arParams["LANGUAGE_ID"]
		),
		"select" => array(
			"ID",
			"NAME_RU" => "NAME.NAME"
		)
	));
	if($arLocation = $rsLocation->fetch()) {
		$arResult["CITY"] = $arLocation["NAME_RU"];
	}
	unset($arLocation, $rsLocation);

	$this->IncludeComponentTemplate();
}