<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Iblock\InheritedProperty\ElementValues,
	Bitrix\Iblock,
	Bitrix\Catalog,
	Bitrix\Currency;

$this->setFrameMode(false);

if(!Loader::includeModule("iblock")) {
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return;
}

$arParams["NAME"] = trim($arParams["NAME"]);
if($arParams["NAME"] == "")
	$arParams["NAME"] = "CATALOG_COMPARE_LIST";

unset($arParams["IBLOCK_TYPE"]);

$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);

if(!is_array($arParams["FIELD_CODE"]))
	$arParams["FIELD_CODE"] = array();
foreach($arParams["FIELD_CODE"] as $k => $v) {
	if($v === "")
		unset($arParams["FIELD_CODE"][$k]);
}
unset($k, $v);

if(!in_array("NAME", $arParams["FIELD_CODE"]))
	$arParams["FIELD_CODE"][] = "NAME";

if(!in_array("PREVIEW_PICTURE", $arParams["FIELD_CODE"]))
	$arParams["FIELD_CODE"][] = "PREVIEW_PICTURE";

if(!is_array($arParams["PROPERTY_CODE"]))
	$arParams["PROPERTY_CODE"] = array();
foreach($arParams["PROPERTY_CODE"] as $k => $v) {
	if($v === "")
		unset($arParams["PROPERTY_CODE"][$k]);
}
unset($k, $v);

if(!is_array($arParams["OFFERS_FIELD_CODE"]))
	$arParams["OFFERS_FIELD_CODE"] = array();
foreach($arParams["OFFERS_FIELD_CODE"] as $k => $v) {
	if($v === "")
		unset($arParams["OFFERS_FIELD_CODE"][$k]);
}

if(!in_array("NAME", $arParams["OFFERS_FIELD_CODE"]))
	$arParams["OFFERS_FIELD_CODE"][] = "NAME";

if(!in_array("PREVIEW_PICTURE", $arParams["OFFERS_FIELD_CODE"]))
	$arParams["OFFERS_FIELD_CODE"][] = "PREVIEW_PICTURE";

if(!is_array($arParams["OFFERS_PROPERTY_CODE"]))
	$arParams["OFFERS_PROPERTY_CODE"] = array();
foreach($arParams["OFFERS_PROPERTY_CODE"] as $k => $v) {
	if($v === "")
		unset($arParams["OFFERS_PROPERTY_CODE"][$k]);
}

if(strlen($arParams["ELEMENT_SORT_FIELD"]) <= 0)
	$arParams["ELEMENT_SORT_FIELD"] = "sort";

if(!preg_match("/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i", $arParams["ELEMENT_SORT_ORDER"]))
	$arParams["ELEMENT_SORT_ORDER"] = "asc";

$arParams["DETAIL_URL"] = trim($arParams["DETAIL_URL"]);

$arParams["BASKET_URL"] = trim($arParams["BASKET_URL"]);
if($arParams["BASKET_URL"] == "")
	$arParams["BASKET_URL"] = "/personal/cart/";

$arParams["ACTION_VARIABLE"] = trim($arParams["ACTION_VARIABLE"]);
if($arParams["ACTION_VARIABLE"] == "" || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["ACTION_VARIABLE"]))
	$arParams["ACTION_VARIABLE"] = "action";

$arParams["PRODUCT_ID_VARIABLE"] = trim($arParams["PRODUCT_ID_VARIABLE"]);
if($arParams["PRODUCT_ID_VARIABLE"] == "" || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["PRODUCT_ID_VARIABLE"]))
	$arParams["PRODUCT_ID_VARIABLE"] = "id";

$arParams["SECTION_ID_VARIABLE"] = trim($arParams["SECTION_ID_VARIABLE"]);
if($arParams["SECTION_ID_VARIABLE"] == "" || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["SECTION_ID_VARIABLE"]))
	$arParams["SECTION_ID_VARIABLE"] = "SECTION_ID";

if(!is_array($arParams["PRICE_CODE"]))
	$arParams["PRICE_CODE"] = array();

$arParams["SHOW_PRICE_COUNT"] = intval($arParams["SHOW_PRICE_COUNT"]);
if($arParams["SHOW_PRICE_COUNT"] <= 0)
	$arParams["SHOW_PRICE_COUNT"] = 1;

$arParams["PRICE_VAT_INCLUDE"] = $arParams["PRICE_VAT_INCLUDE"] !== "N";

$arParams["CONVERT_CURRENCY"] = isset($arParams["CONVERT_CURRENCY"]) && "Y" == $arParams["CONVERT_CURRENCY"] ? "Y" : "N";
$arParams["CURRENCY_ID"] = trim(strval($arParams["CURRENCY_ID"]));
if("" == $arParams["CURRENCY_ID"])
	$arParams["CONVERT_CURRENCY"] = "N";
elseif("N" == $arParams["CONVERT_CURRENCY"])
	$arParams["CURRENCY_ID"] = "";

$arParams["USE_REVIEW"] = isset($arParams["USE_REVIEW"]) && $arParams["USE_REVIEW"] == "Y" ? "Y" : "N";
unset($arParams["REVIEWS_IBLOCK_TYPE"]);
$arParams["REVIEWS_IBLOCK_ID"] = intval($arParams["REVIEWS_IBLOCK_ID"]);

$arSettings = CEnext::GetFrontParametrsValues(SITE_ID);
$arParams["DISABLE_BASKET"] = false;
if($arSettings["DISABLE_BASKET"] == "Y")
	$arParams["DISABLE_BASKET"] = true;

$arResult = array();

if(!isset($_SESSION[$arParams["NAME"]]))
	$_SESSION[$arParams["NAME"]] = array();
if(!isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]))
	$_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]] = array();

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();

//AJAX//
if($request->isAjaxRequest()) {
	$action = $request->get($arParams["ACTION_VARIABLE"]);
	$productId = $request->get($arParams["PRODUCT_ID_VARIABLE"]);
	
	if($action == "DELETE_FROM_COMPARE_RESULT" && intval($productId) > 0) {
		if(isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"][$productId]))
			unset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"][$productId]);
	} elseif($action == "CLEAR_COMPARE" && intval($productId) == 0) {
		if(isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"]))
			unset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"]);
	} elseif($action == "COMPARE_ADD2BASKET" && intval($productId) > 0) {
		if(Loader::includeModule("sale") && Loader::includeModule("catalog")) {
			$quantity = 1;
			$product_properties = array();
			if(is_array($arParams["OFFERS_CART_PROPERTIES"])) {
				foreach($arParams["OFFERS_CART_PROPERTIES"] as $i => $pid) {
					if($pid === "")
						unset($arParams["OFFERS_CART_PROPERTIES"][$i]);
				}
				unset($i, $pid);

				if(!empty($arParams["OFFERS_CART_PROPERTIES"])) {
					$product_properties = CIBlockPriceTools::GetOfferProperties(
						$productID,
						$arParams["IBLOCK_ID"],
						$arParams["OFFERS_CART_PROPERTIES"]
					);
				}
			}

			if(Add2BasketByProductID($productId, $quantity, $product_properties)) {
				Iblock\Component\Base::sendJsonAnswer(array(
					"STATUS" => "OK"
				));
			} else {
				Iblock\Component\Base::sendJsonAnswer(array(
					"STATUS" => "ERROR"
				));
			}
		}
	}
}

//DIFFERENT//
$arResult["DIFFERENT"] = false;
$diff = $request->get("DIFFERENT");
if(!empty($diff) && $diff == "Y")
	$arResult["DIFFERENT"] = true;

//COMPARE//
$arCompareIds = array();

$arSessionItemIds = array();
if(!empty($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"]))
	$arSessionItemIds = array_keys($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"]);

$arResult["IS_EXTERNAL"] = false;

$ids = $request->get("ids");
if(!empty($ids)) {
	$arCompareIds = explode("+", $ids);
	
	if($arCompareIds != $arSessionItemIds)
		$arResult["IS_EXTERNAL"] = true;
} elseif(!empty($arSessionItemIds)) {
	$arCompareIds = $arSessionItemIds;
}

if(!empty($arCompareIds)) {
	$fieldsRequired = array(
		"NAME" => true,
		"PREVIEW_PICTURE" => true
	);
	
	$fieldsHidden = array(
		"IBLOCK_TYPE_ID" => true,
		"IBLOCK_CODE" => true,
		"IBLOCK_NAME" => true,
		"IBLOCK_EXTERNAL_ID" => true,
		"SECTION_ID" => true,
		"IBLOCK_SECTION_ID" => true
	);
	
	$sessionFields = array(
		"DELETE_FIELD",
		"DELETE_PROP",
		"DELETE_OFFER_FIELD",
		"DELETE_OFFER_PROP"
	);
	foreach($sessionFields as &$fieldName) {
		if(!isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]][$fieldName]) || !is_array($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]][$fieldName]))
			$_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]][$fieldName] = array();
	}
	unset($fieldName, $sessionFields);

	$catalogIncluded = Loader::includeModule("catalog");
	$arResult["PRICES"] = CIBlockPriceTools::GetCatalogPrices($arParams["IBLOCK_ID"], $arParams["PRICE_CODE"]);
	$arResult["PRICES_ALLOW"] = CIBlockPriceTools::GetAllowCatalogPrices($arResult["PRICES"]);

	$arConvertParams = array();
	$basePrice = "";
	if($arParams["CONVERT_CURRENCY"] == "Y") {
		$correct = false;
		if(Loader::includeModule("currency")) {
			$correct = Currency\CurrencyManager::isCurrencyExist($arParams["CURRENCY_ID"]);
			$basePrice = Currency\CurrencyManager::getBaseCurrency();
		}

		if($correct) {
			$arConvertParams["CURRENCY_ID"] = $arParams["CURRENCY_ID"];
		} else {
			$arParams["CONVERT_CURRENCY"] = "N";
			$arParams["CURRENCY_ID"] = "";
		}
		unset($correct);
	}

	$arResult["CONVERT_CURRENCY"] = $arConvertParams;

	$arResult["OFFERS_IBLOCK_ID"] = 0;
	$arResult["OFFERS_PROPERTY_ID"] = 0;
	$arOffers = CIBlockPriceTools::GetOffersIBlock($arParams["IBLOCK_ID"]);
	if(!empty($arOffers)) {
		$arResult["OFFERS_IBLOCK_ID"] = $arOffers["OFFERS_IBLOCK_ID"];
		$arResult["OFFERS_PROPERTY_ID"] = $arOffers["OFFERS_PROPERTY_ID"];
	}
	unset($arOffers);

	$usePropertyFeatures = Iblock\Model\PropertyFeature::isEnabledFeatures();
	if($usePropertyFeatures) {
		$properties = array();
		$list = Iblock\Model\PropertyFeature::getListPageShowPropertyCodes(
			$arParams["IBLOCK_ID"],
			array("CODE" => "Y")
		);
		if(!empty($list)) {
			$properties = $list;
		}
		$list = Iblock\Model\PropertyFeature::getDetailPageShowPropertyCodes(
			$arParams["IBLOCK_ID"],
			array("CODE" => "Y")
		);
		if(!empty($list)) {
			$properties = array_unique(array_merge($properties, $list));
		}
		$arParams["PROPERTY_CODE"] = $properties;
		if($catalogIncluded && $arResult["OFFERS_IBLOCK_ID"] > 0) {
			$properties = array();
			$list = Iblock\Model\PropertyFeature::getListPageShowPropertyCodes(
				$arResult["OFFERS_IBLOCK_ID"],
				array("CODE" => "Y")
			);
			if(!empty($list)) {
				$properties = $list;
			}
			$list = Iblock\Model\PropertyFeature::getDetailPageShowPropertyCodes(
				$arResult["OFFERS_IBLOCK_ID"],
				array("CODE" => "Y")
			);
			if(!empty($list)) {
				$properties = array_merge($properties, $list);
			}
			$list = Catalog\Product\PropertyCatalogFeature::getOfferTreePropertyCodes(
				$arResult["OFFERS_IBLOCK_ID"],
				array("CODE" => "Y")
			);
			if(!empty($list)) {
				$properties = array_merge($properties, $list);
			}
			$arParams["OFFERS_PROPERTY_CODE"] = array_unique($properties);
		}
		unset($list, $properties);
	}

	$arSelect = array(
		"ID",
		"IBLOCK_ID",
		"IBLOCK_SECTION_ID",
		"DETAIL_PAGE_URL",
		"PROPERTY_*",
	);
	$arFilter = array(
		"ID" => $arCompareIds,
		"IBLOCK_LID" => SITE_ID,
		"IBLOCK_ACTIVE" => "Y",
		"ACTIVE_DATE" => "Y",
		"ACTIVE" => "Y",
		"CHECK_PERMISSIONS" => "Y",
	);
	$arFilter["IBLOCK_ID"] = $arResult["OFFERS_IBLOCK_ID"] > 0 ? array($arParams["IBLOCK_ID"], $arResult["OFFERS_IBLOCK_ID"]) : $arParams["IBLOCK_ID"];

	$arPriceTypeID = array();
	foreach($arResult["PRICES"] as &$value) {
		if(!$value["CAN_VIEW"] && !$value["CAN_BUY"])
			continue;
		$arSelect[] = $value["SELECT"];
		$arFilter["CATALOG_SHOP_QUANTITY_".$value["ID"]] = $arParams["SHOW_PRICE_COUNT"];
	}
	unset($value);
	
	if(!empty($arParams["FIELD_CODE"]))
		$arSelect = array_merge($arSelect, $arParams["FIELD_CODE"]);
	if(!empty($arParams["OFFERS_FIELD_CODE"]))
		$arSelect = array_merge($arSelect, $arParams["OFFERS_FIELD_CODE"]);
	$arSelect = array_unique($arSelect);

	$arSort = array(
		$arParams["ELEMENT_SORT_FIELD"] => $arParams["ELEMENT_SORT_ORDER"],
		"ID" => "DESC",
	);

	$currentPath = CHTTP::urlDeleteParams(
		$APPLICATION->GetCurPageParam(),
		array(
			$arParams["ACTION_VARIABLE"], $arParams["PRODUCT_ID_VARIABLE"],
			"ids", "DIFFERENT", "ajax_action"
		),
		array("delete_system_params" => true)
	);

	$arResult["COMPARE_URL_TEMPLATE"] = $currentPath.(stripos($currentPath, "?") === false ? "?" : "&");
	unset($currentPath);

	$arResult["DELETED_FIELDS"] = array();
	$arResult["SHOW_FIELDS"] = array();
	$arResult["DELETED_PROPERTIES"] = array();
	$arResult["SHOW_PROPERTIES"] = array();
	$arResult["DELETED_OFFER_FIELDS"] = array();
	$arResult["SHOW_OFFER_FIELDS"] = array();
	$arResult["DELETED_OFFER_PROPERTIES"] = array();
	$arResult["SHOW_OFFER_PROPERTIES"] = array();
	$arResult["EMPTY_FIELDS"] = array();
	$arResult["EMPTY_PROPERTIES"] = array();
	$arResult["EMPTY_OFFER_FIELDS"] = array();
	$arResult["EMPTY_OFFER_PROPERTIES"] = array();
	
	$arResult["ITEMS"] = array();
	$rsElements = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
	$rsElements->SetUrlTemplates($arParams["DETAIL_URL"]);
	while($obElement = $rsElements->GetNextElement()) {
		$arItem = $obElement->GetFields();
		
		$arOffer = false;
		if($arItem["IBLOCK_ID"] == $arResult["OFFERS_IBLOCK_ID"]) {
			if(!empty($arParams["OFFERS_PROPERTY_CODE"]))
				$arItem["PROPERTIES"] = $obElement->GetProperties();

			$rsMasterProperty = CIBlockElement::GetProperty($arItem["IBLOCK_ID"], $arItem["ID"], array(), array("ID" => $arResult["OFFERS_PROPERTY_ID"], "EMPTY" => "N"));
			if($arMasterProperty = $rsMasterProperty->Fetch()) {
				$rsMaster = CIBlockElement::GetList(
					array(),
					array(
						"ID" => $arMasterProperty["VALUE"],
						"IBLOCK_ID" => $arMasterProperty["LINK_IBLOCK_ID"],
						"ACTIVE" => "Y",
					),
					false,
					false,
					$arSelect
				);
				$rsMaster->SetUrlTemplates($arParams["DETAIL_URL"]);
				$obElement = $rsMaster->GetNextElement();
				if(!is_object($obElement))
					continue;
			} else {
				continue;
			}

			Iblock\Component\Tools::getFieldImageData(
				$arItem,
				array("PREVIEW_PICTURE", "DETAIL_PICTURE"),
				Iblock\Component\Tools::IPROPERTY_ENTITY_ELEMENT,
				"IPROPERTY_VALUES"
			);
			$arOffer = $arItem;
			$arItem = $obElement->GetFields();
		}
		
		$ipropValues = new ElementValues($arItem["IBLOCK_ID"], $arItem["ID"]);
		$arItem["IPROPERTY_VALUES"] = $ipropValues->getValues();

		Iblock\Component\Tools::getFieldImageData(
			$arItem,
			array("PREVIEW_PICTURE", "DETAIL_PICTURE"),
			Iblock\Component\Tools::IPROPERTY_ENTITY_ELEMENT,
			"IPROPERTY_VALUES"
		);
		
		$arItem["FIELDS"] = array();
		if(!empty($arParams["FIELD_CODE"])) {
			foreach($arParams["FIELD_CODE"] as &$code) {
				if(isset($fieldsHidden[$code]))
					continue;
				if(!isset($arResult["EMPTY_FIELDS"][$code]))
					$arResult["EMPTY_FIELDS"][$code] = true;

				if(array_key_exists($code, $arItem)) {
					if(isset($fieldsRequired[$code]) || !isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_FIELD"][$code])) {
						$arItem["FIELDS"][$code] = $arItem[$code];
						if($arItem["FIELDS"][$code] === null)
							$arItem["FIELDS"][$code] = "";
						if($arItem["FIELDS"][$code] != "")
							$arResult["EMPTY_FIELDS"][$code] = false;
					}
					if(isset($fieldsRequired[$code]) || !isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_FIELD"][$code])) {
						$arResult["SHOW_FIELDS"][$code] = $code;
					} else {
						$arResult["DELETED_FIELDS"][$code] = $code;
						$arResult["EMPTY_FIELDS"][$code] = false;
					}
				}
			}
			unset($code);
		}
		
		if($arOffer) {
			$arItem["OFFER_FIELDS"] = array();
			$arItem["OFFER_PROPERTIES"] = array();
			$arItem["OFFER_DISPLAY_PROPERTIES"] = array();
			if(!empty($arParams["OFFERS_FIELD_CODE"])) {
				foreach($arParams["OFFERS_FIELD_CODE"] as &$code) {
					if(isset($fieldsHidden[$code]))
						continue;
					if(!isset($arResult["EMPTY_OFFER_FIELDS"][$code]))
						$arResult["EMPTY_OFFER_FIELDS"][$code] = true;

					if(array_key_exists($code, $arOffer)) {
						if(!isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_OFFER_FIELD"][$code])) {
							$arItem["OFFER_FIELDS"][$code] = $arOffer[$code];
							if($arItem["OFFER_FIELDS"][$code] === null)
								$arItem["OFFER_FIELDS"][$code] = "";
							if($arItem["OFFER_FIELDS"][$code] != "")
								$arResult["EMPTY_OFFER_FIELDS"][$code] = false;
						}

						if(isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_OFFER_FIELD"][$code])) {
							$arResult["DELETED_OFFER_FIELDS"][$code] = $code;
							$arResult["EMPTY_OFFER_FIELDS"][$code] = false;
						} else {
							$arResult["SHOW_OFFER_FIELDS"][$code] = $code;
						}
					}
				}
				unset($code);
			}

			$arItem["OFFER_PROPERTIES"] = $arOffer["PROPERTIES"];
			if(!empty($arParams["OFFERS_PROPERTY_CODE"])) {
				foreach($arParams["OFFERS_PROPERTY_CODE"] as &$pid) {
					if(!isset($arOffer["PROPERTIES"][$pid]))
						continue;

					if(!isset($arResult["EMPTY_OFFER_PROPERTIES"][$pid]))
						$arResult["EMPTY_OFFER_PROPERTIES"][$pid] = true;

					if(!isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_OFFER_PROP"][$pid])) {
						$prop = &$arOffer["PROPERTIES"][$pid];
						$boolArr = is_array($prop["VALUE"]);
						if(($boolArr && !empty($prop["VALUE"])) || (!$boolArr && strlen($prop["VALUE"]) > 0)) {
							$arItem["OFFER_DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arOffer, $prop, "catalog_out");
							if($arItem["OFFER_DISPLAY_PROPERTIES"][$pid]["DISPLAY_VALUE"] !== false)
								$arResult["EMPTY_OFFER_PROPERTIES"][$pid] = false;
						}
						unset($prop);
					}

					if(isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_OFFER_PROP"][$pid])) {
						$arResult["DELETED_OFFER_PROPERTIES"][$pid] = $arOffer["PROPERTIES"][$pid];
						$arResult["EMPTY_OFFER_PROPERTIES"][$pid] = false;
					} else {
						$arResult["SHOW_OFFER_PROPERTIES"][$pid] = $arOffer["PROPERTIES"][$pid];
					}
				}
				unset($pid);
			}
		}

		$arItem["PROPERTIES"] = array();
		$arItem["DISPLAY_PROPERTIES"] = array();

		if(!empty($arParams["PROPERTY_CODE"])) {
			$arItem["PROPERTIES"] = $obElement->GetProperties();
			foreach($arParams["PROPERTY_CODE"] as &$pid) {
				if(!isset($arItem["PROPERTIES"][$pid]))
					continue;

				if(!isset($arResult["EMPTY_PROPERTIES"][$pid]))
					$arResult["EMPTY_PROPERTIES"][$pid] = true;

				if(!isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_PROP"][$pid])) {
					$prop = &$arItem["PROPERTIES"][$pid];
					$boolArr = is_array($prop["VALUE"]);
					if(($boolArr && !empty($prop["VALUE"])) || (!$boolArr && strlen($prop["VALUE"]) > 0)) {
						$arItem["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arItem, $prop, "catalog_out");
						if($arItem["DISPLAY_PROPERTIES"][$pid]["DISPLAY_VALUE"] !== false)
							$arResult["EMPTY_PROPERTIES"][$pid] = false;
					}
				}

				if(isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_PROP"][$pid])) {
					$arResult["DELETED_PROPERTIES"][$pid] = $arItem["PROPERTIES"][$pid];
					$arResult["EMPTY_PROPERTIES"][$pid] = false;
				} else {
					$arResult["SHOW_PROPERTIES"][$pid] = $arItem["PROPERTIES"][$pid];
				}
			}
			unset($pid);
		}

		$arItem["PARENT_ID"] = $arItem["ID"];
		
		$arItem["PRICES"] = array();		
		$arItem["MIN_PRICE"] = false;
		if($arOffer) {
			$arItem["PRICES"] = CIBlockPriceTools::GetItemPrices(
				$arOffer["IBLOCK_ID"],
				$arResult["PRICES"],
				$arOffer,
				$arParams["PRICE_VAT_INCLUDE"],
				$arConvertParams
			);
			$arItem["CAN_BUY"] = CIBlockPriceTools::CanBuy($arParams["IBLOCK_ID"], $arResult["PRICES"], $arOffer);
			$arItem["ID"] = $arOffer["ID"];
		} else {
			$arItem["PRICES"] = CIBlockPriceTools::GetItemPrices(
				$arItem["IBLOCK_ID"],
				$arResult["PRICES"],
				$arItem,
				$arParams["PRICE_VAT_INCLUDE"],
				$arConvertParams
			);
			$arItem["CAN_BUY"] = CIBlockPriceTools::CanBuy($arParams["IBLOCK_ID"], $arResult["PRICES"], $arItem);
		}
		if(!empty($arItem["PRICES"])) {
			foreach($arItem["PRICES"] as &$arOnePrice) {
				if($arOnePrice["MIN_PRICE"] == "Y") {
					$arItem["MIN_PRICE"] = $arOnePrice;
					break;
				}
			}
			unset($arOnePrice);
		}
		
		$arResult["ITEMS"][] = $arItem;
	}
	unset($arItem, $obElement, $rsElements);
	
	if(!empty($arResult["EMPTY_FIELDS"])) {
		$arResult["EMPTY_FIELDS"] = array_filter($arResult["EMPTY_FIELDS"]);
		if(!empty($arResult["EMPTY_FIELDS"])) {
			foreach($arResult["EMPTY_FIELDS"] as $code => $isEmpty) {
				if(isset($arResult["SHOW_FIELDS"][$code]))
					unset($arResult["SHOW_FIELDS"][$code]);
				if(isset($arResult["DELETED_FIELDS"][$code]))
					unset($arResult["DELETED_FIELDS"][$code]);
			}
			unset($code, $isEmpty);
		}
	}
	if(!empty($arResult["EMPTY_OFFER_FIELDS"])) {
		$arResult["EMPTY_OFFER_FIELDS"] = array_filter($arResult["EMPTY_OFFER_FIELDS"]);
		if(!empty($arResult["EMPTY_OFFER_FIELDS"])) {
			foreach($arResult["EMPTY_OFFER_FIELDS"] as $code => $isEmpty) {
				if(isset($arResult["SHOW_OFFER_FIELDS"][$code]))
					unset($arResult["SHOW_OFFER_FIELDS"][$code]);
				if(isset($arResult["DELETED_OFFER_FIELDS"][$code]))
					unset($arResult["DELETED_OFFER_FIELDS"][$code]);
			}
			unset($code, $isEmpty);
		}
	}

	if(!empty($arResult["EMPTY_OFFER_PROPERTIES"])) {
		$arResult["EMPTY_OFFER_PROPERTIES"] = array_filter($arResult["EMPTY_OFFER_PROPERTIES"]);
		if(!empty($arResult["EMPTY_OFFER_PROPERTIES"])) {
			foreach($arResult["EMPTY_OFFER_PROPERTIES"] as $code => $isEmpty) {
				if(isset($arResult["SHOW_OFFER_PROPERTIES"][$code]))
					unset($arResult["SHOW_OFFER_PROPERTIES"][$code]);
				if(isset($arResult["DELETED_OFFER_PROPERTIES"][$code]))
					unset($arResult["DELETED_OFFER_PROPERTIES"][$code]);
			}
			unset($code, $isEmpty);
		}
	}

	if(!empty($arResult["EMPTY_PROPERTIES"])) {
		$arResult["EMPTY_PROPERTIES"] = array_filter($arResult["EMPTY_PROPERTIES"]);
		if(!empty($arResult["EMPTY_PROPERTIES"])) {
			foreach($arResult["EMPTY_PROPERTIES"] as $code => $isEmpty) {
				if(isset($arResult["SHOW_PROPERTIES"][$code]))
					unset($arResult["SHOW_PROPERTIES"][$code]);
				if(isset($arResult["DELETED_PROPERTIES"][$code]))
					unset($arResult["DELETED_PROPERTIES"][$code]);
			}
			unset($code, $isEmpty);
		}
	}
	
	$this->includeComponentTemplate();	
} else {
	if($request->isAjaxRequest()) {
		$actionByAjax = $request->get("ajax_action");
		if($actionByAjax == "Y") {
			$APPLICATION->RestartBuffer();
			ShowNote(GetMessage("CATALOG_COMPARE_LIST_EMPTY"), "warning");
			die();
		}
	} else {
		$APPLICATION->SetAdditionalCSS($componentPath."/templates/.default/style.css");
		ShowNote(GetMessage("CATALOG_COMPARE_LIST_EMPTY"), "warning");
	}
}