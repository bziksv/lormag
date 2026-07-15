<?define("NOT_CHECK_PERMISSIONS", true);

$siteId = isset($_REQUEST["customSiteId"]) && is_string($_REQUEST["customSiteId"]) ? $_REQUEST["customSiteId"] : (isset($_REQUEST["siteId"]) && is_string($_REQUEST["siteId"]) ? $_REQUEST["siteId"] : "");
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
	if($action == "geoDelivery") {
		$siteId = $request->get("siteId");
		$siteServerName = $request->get("siteServerName") ?: SITE_SERVER_NAME;

		$signer = new Bitrix\Main\Security\Sign\Signer;
		$parameters = unserialize(base64_decode($signer->unsign($request->get("parameters"), "catalog.element")));

		$result = array();

		$offers = $request->get("offers");
		if(!empty($offers)) {
			foreach($offers as $key => $arOffer) {
				$result[$key] = $APPLICATION->IncludeComponent("altop:geo.delivery.enext", "",
					array(			
						"PRODUCT_ID" => $arOffer["ID"],
						"SITE_ID" => $siteId,
						"CUSTOM_SITE_ID" => $arOffer["CUSTOM_SITE_ID"],
						"SITE_SERVER_NAME" => $siteServerName,
						"IGNORE_TEMPLATE" => "Y",
						"CACHE_TYPE" => $parameters["CACHE_TYPE"],
						"CACHE_TIME" => $parameters["CACHE_TIME"]
					),
					false
				);
			}
			unset($key, $arOffer);
		}

		$response->flush(Bitrix\Main\Web\Json::encode($result));
	} elseif($action == "sPanelGeoDeliveryRequest") {
		$siteServerName = $request->get("siteServerName") ?: SITE_SERVER_NAME;

		$signer = new Bitrix\Main\Security\Sign\Signer;
		$parameters = unserialize(base64_decode($signer->unsign($request->get("parameters"), "catalog.element")));
		
		$productId = intval($request->get("productId"));

		$onlyGeoDeliveryItems = $request->get("onlyGeoDeliveryItems");

		$geoDeliveryContainerId = $request->get("geoDeliveryContainerId");

		$siteId = $request->get("siteId");
		$customSiteId = $request->get("customSiteId");
		
		$APPLICATION->IncludeComponent("altop:geo.delivery.enext", "slide_panel",
			array(			
				"PRODUCT_ID" => $productId,
				"GEO_DELIVERY_CONTAINER_ID" => $geoDeliveryContainerId,
				"SITE_ID" => $siteId,
				"CUSTOM_SITE_ID" => $customSiteId,
				"SITE_SERVER_NAME" => $siteServerName,
				"PERSON_TYPE_INPUT" => $onlyGeoDeliveryItems == "Y" ? "N" : "Y",
				"LOCATION_INPUT" => $onlyGeoDeliveryItems == "Y" ? "N" : "Y",
				"QUANTITY_INPUT" => $onlyGeoDeliveryItems == "Y" ? "N" : "Y",
				"CACHE_TYPE" => $parameters["CACHE_TYPE"],
				"CACHE_TIME" => $parameters["CACHE_TIME"]
			),
			false
		);
		
		$content = ob_get_contents();
		ob_end_clean();
		
		if(Bitrix\Main\Loader::includeModule("iblock")) {
			$response->flush(Bitrix\Iblock\Component\Base::sendJsonAnswer(array(
				"content" => $content
			)));
		}
	} elseif($action == "recalculateDeliveryItems") {
		$siteServerName = $request->get("siteServerName") ?: SITE_SERVER_NAME;

		$signer = new Bitrix\Main\Security\Sign\Signer;
		$parameters = unserialize(base64_decode($signer->unsign($request->get("parameters"), "geo.delivery.enext")));
		
		$personTypeId = (int)$request->get("PERSON_TYPE_ID");
		if($personTypeId > 0) {
			$parameters["PERSON_TYPE_ID"] = $personTypeId;
			if($parameters["PERSON_TYPE_ID"] != $request->getCookie("ENEXT_GEO_PERSON_TYPE_ID")) {
				$cookie = new Bitrix\Main\Web\Cookie("ENEXT_GEO_PERSON_TYPE_ID", $parameters["PERSON_TYPE_ID"], time() + 32832000);
				$cookie->setDomain($siteServerName);
				$cookie->setHttpOnly(false);
				$response->addCookie($cookie);
			}
		}

		$locationId = (int)$request->get("LOCATION");
		if($locationId > 0) {
			$parameters["LOCATION_ID"] = $locationId;
			if($parameters["LOCATION_ID"] != $request->getCookie("ENEXT_GEO_LOCATION_ID")) {
				$cookie = new Bitrix\Main\Web\Cookie("ENEXT_GEO_LOCATION_ID", $parameters["LOCATION_ID"], time() + 32832000);
				$cookie->setDomain($siteServerName);
				$cookie->setHttpOnly(false);
				$response->addCookie($cookie);
			}
		}
		
		$productQuantity = (float)$request->get("PRODUCT_QUANTITY");
		if(!empty($productQuantity))
			$parameters["PRODUCT_QUANTITY"] = $productQuantity;
		
		$calcAllProducts = $request->get("CALC_ALL_PRODUCTS");
		if(!empty($calcAllProducts))
			$parameters["CALC_ALL_PRODUCTS"] = $calcAllProducts;
		
		$arData = $APPLICATION->IncludeComponent("altop:geo.delivery.enext", "slide_panel",
			$parameters
		);
		
		$content = ob_get_contents();
		ob_end_clean();

		list(, $itemsContainer) = explode("<!-- items-container -->", $content);
		
		if(Bitrix\Main\Loader::includeModule("iblock")) {
			$response->flush(Bitrix\Iblock\Component\Base::sendJsonAnswer(array(
				"items" => $itemsContainer,
				"data" => $arData
			)));
		}
	}
}