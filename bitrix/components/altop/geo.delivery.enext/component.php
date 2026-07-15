<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

if(!Loader::includeModule("catalog") || !Loader::includeModule("sale"))
	return;

$arParams["PRODUCT_ID"] = (int)$arParams["PRODUCT_ID"];
if($arParams["PRODUCT_ID"] <= 0)
	return;

$arParams["GEO_DELIVERY_CONTAINER_ID"] = isset($arParams["GEO_DELIVERY_CONTAINER_ID"]) ? (string)$arParams["GEO_DELIVERY_CONTAINER_ID"] : "";

if(!isset($arParams["SITE_ID"]))
	$arParams["SITE_ID"] = SITE_ID;

$arParams["CUSTOM_SITE_ID"] = isset($arParams["CUSTOM_SITE_ID"]) ? (string)$arParams["CUSTOM_SITE_ID"] : "";

if(!isset($arParams["SITE_SERVER_NAME"]))
	$arParams["SITE_SERVER_NAME"] = SITE_SERVER_NAME;

if(!isset($arParams["IGNORE_TEMPLATE"]))
	$arParams["IGNORE_TEMPLATE"] = "N";

if(!isset($arParams["PERSON_TYPE_INPUT"]) || $arParams["PERSON_TYPE_INPUT"] != "N")
	$arParams["PERSON_TYPE_INPUT"] = "Y";

if(!isset($arParams["LOCATION_INPUT"]) || $arParams["LOCATION_INPUT"] != "N")
	$arParams["LOCATION_INPUT"] = "Y";

if(!isset($arParams["QUANTITY_INPUT"]) || $arParams["QUANTITY_INPUT"] != "N")
	$arParams["QUANTITY_INPUT"] = "Y";

if(!empty($arParams["CUSTOM_SITE_ID"]))
	$arParams["CALC_ALL_PRODUCTS_INPUT"] = "N";
else
	$arParams["CALC_ALL_PRODUCTS_INPUT"] = $this->needCalcAllProductsInput($arParams["SITE_ID"]);

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

global $USER;
$arParams["USER_ID"] = (int)$USER->GetID();

$arSettings = CEnext::GetFrontParametrsValues($arParams["SITE_ID"]);

$arParams["IGNORE_DELIVERY"] = $arSettings["IGNORE_DELIVERY"];

$context = Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

if(!isset($arParams["LOCATION_ID"])) {
	$arParams["LOCATION_ID"] = (int)$request->getCookie("ENEXT_GEO_LOCATION_ID");
	if($arParams["LOCATION_ID"] <= 0) {
		$arParams["LOCATION_ID"] = $arSettings["DEFAULT_LOCATION_ID"];
		if(!preg_match("/Google|Yahoo|Rambler|Bot|Yandex|Spider|Snoopy|Crawler|Finder|Mail|curl/i", $request->getUserAgent())) {
			$ipAddress = Bitrix\Main\Service\GeoIp\Manager::getRealIp();
			$locId = Bitrix\Sale\Location\GeoIp::getLocationId($ipAddress, LANGUAGE_ID);
			if(!empty($locId)) {
				$arParams["LOCATION_ID"] = $locId;
				$cookie = new Bitrix\Main\Web\Cookie("ENEXT_GEO_LOCATION_ID", $arParams["LOCATION_ID"], time() + 32832000);
				$cookie->setDomain($arParams["SITE_SERVER_NAME"]);
				$cookie->setHttpOnly(false);
				$context->getResponse()->addCookie($cookie);
			}
		}
	}
}

if($arParams["LOCATION_ID"] > 0) {
	$rsLocation = Bitrix\Sale\Location\LocationTable::getList(array(
		"filter" => array(
			"=ID" => $arParams["LOCATION_ID"],
			"NAME.LANGUAGE_ID" => LANGUAGE_ID
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
}

$arResult["PERSON_TYPES"] = $this->getPersonTypes(!empty($arParams["CUSTOM_SITE_ID"]) ? $arParams["CUSTOM_SITE_ID"] : $arParams["SITE_ID"]);

if(!isset($arParams["PERSON_TYPE_ID"])) {
	$arParams["PERSON_TYPE_ID"] = (int)$request->getCookie("ENEXT_GEO_PERSON_TYPE_ID");
	if($arParams["PERSON_TYPE_ID"] <= 0 && !empty($arResult["PERSON_TYPES"])) {
		$personTypesIds = array_keys($arResult["PERSON_TYPES"]);
		$arParams["PERSON_TYPE_ID"] = $personTypesIds[0];
		$cookie = new Bitrix\Main\Web\Cookie("ENEXT_GEO_PERSON_TYPE_ID", $arParams["PERSON_TYPE_ID"], time() + 32832000);
		$cookie->setDomain($arParams["SITE_SERVER_NAME"]);
		$cookie->setHttpOnly(false);
		$context->getResponse()->addCookie($cookie);
	}
}

$arResult["RATIO_MEASURE"] = $this->getRatioMeasure($arParams["PRODUCT_ID"]);
	
if(!isset($arParams["PRODUCT_QUANTITY"]))
	$arParams["PRODUCT_QUANTITY"] = $arResult["RATIO_MEASURE"]["RATIO"];

$arResult["DELIVERY_ITEMS"] = $this->getDeliveryItems($arParams);

if(!empty($arResult["DELIVERY_ITEMS"])) {
	foreach($arResult["DELIVERY_ITEMS"] as $arDeliveryItem) {
		if(!in_array($arDeliveryItem["ID"], $arParams["IGNORE_DELIVERY"]))
			$deliveryItems[$arDeliveryItem["ID"]] = $arDeliveryItem;
	}
	unset($arDeliveryItem);

	if(!empty($deliveryItems)) {
		foreach($deliveryItems as $arDeliveryItem) {
			$arResult["MIN_NAME"] = $arDeliveryItem["NAME"];
			$arResult["MIN_PRICE"] = $arDeliveryItem["PRICE_FORMATED"];
			$arResult["MIN_PERIOD"] = $arDeliveryItem["PERIOD_TEXT"];
			break;
		}
		unset($arDeliveryItem);
	}
	unset($deliveryItems);
}

if($arParams["IGNORE_TEMPLATE"] != "Y")
	$this->IncludeComponentTemplate();

return array(
	"CITY" => !empty($arResult["CITY"]) ? $arResult["CITY"] : false,
	"MIN_NAME" => !empty($arResult["MIN_NAME"]) ? $arResult["MIN_NAME"] : false,
	"MIN_PRICE" => !empty($arResult["MIN_PRICE"]) ? $arResult["MIN_PRICE"] : false,
	"MIN_PERIOD" => !empty($arResult["MIN_PERIOD"]) ? $arResult["MIN_PERIOD"] : false
);