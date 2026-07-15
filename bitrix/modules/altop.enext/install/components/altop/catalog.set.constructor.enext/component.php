<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Application,
	Bitrix\Catalog,
	Bitrix\Catalog\Product\Price;

if(!Catalog\Config\Feature::isProductSetsEnabled())
	return;

$arParams["IBLOCK_ID"] = isset($arParams["IBLOCK_ID"]) ? (int)$arParams["IBLOCK_ID"] : 0;
if($arParams["IBLOCK_ID"] <= 0)
	return;

if(!isset($arParams["BASKET_URL"]))
	$arParams["BASKET_URL"] = "/personal/cart/";
if("" == trim($arParams["BASKET_URL"]))
	$arParams["BASKET_URL"] = "/personal/cart/";

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

$arParams["CACHE_GROUPS"] = trim($arParams["CACHE_GROUPS"]);
if("N" != $arParams["CACHE_GROUPS"])
	$arParams["CACHE_GROUPS"] = "Y";

$elementID = intval($arParams["ELEMENT_ID"]);
if(!$elementID) {
	ShowError(GetMessage("EMPTY_ELEMENT_ERROR"));
	return;
}

if(!is_array($arParams["OFFERS_CART_PROPERTIES"]))
	$arParams["OFFERS_CART_PROPERTIES"] = array();
foreach($arParams["OFFERS_CART_PROPERTIES"] as $i => $pid)
	if($pid === "")
		unset($arParams["OFFERS_CART_PROPERTIES"][$i]);

$arParams["BUNDLE_ITEMS_COUNT"] = isset($arParams["BUNDLE_ITEMS_COUNT"]) ? (int)$arParams["BUNDLE_ITEMS_COUNT"] : 3;
if($arParams["BUNDLE_ITEMS_COUNT"] < 1)
	$arParams["BUNDLE_ITEMS_COUNT"] = 3;

$arSettings = CEnext::GetFrontParametrsValues(SITE_ID);

$arParams["DISABLE_BASKET"] = false;
if($arSettings["DISABLE_BASKET"] == "Y")
	$arParams["DISABLE_BASKET"] = true;

if($this->startResultCache(false, array($elementID, $arParams["CACHE_GROUPS"] === "N" ? false : $USER->GetGroups()))) {
	if(!Loader::includeModule("catalog")) {
		ShowError(GetMessage("CATALOG_MODULE_NOT_INSTALLED"));
		$this->abortResultCache();
		return;
	}
	
	$isProductHaveSet = CCatalogProductSet::isProductHaveSet($elementID, CCatalogProductSet::TYPE_GROUP);
	$product = false;
	if(!$isProductHaveSet) {
		$product = CCatalogSku::GetProductInfo($elementID, $arParams["IBLOCK_ID"]);
		if(!empty($product)) {
			$isProductHaveSet = CCatalogProductSet::isProductHaveSet($product["ID"], CCatalogProductSet::TYPE_GROUP);
			if(!$isProductHaveSet)
				$product = false;
		}
	}
	if(!$isProductHaveSet) {
		$this->abortResultCache();
		return;
	}

	if(!empty($product)) {
		$arResult["PRODUCT_ID"] = $product["ID"];
		$arResult["PRODUCT_IBLOCK_ID"] = $product["IBLOCK_ID"];
		$arResult["ELEMENT_ID"] = $elementID;
		$arResult["ELEMENT_IBLOCK_ID"] = $arParams["IBLOCK_ID"];
	} else {
		$arResult["PRODUCT_ID"] = $elementID;
		$arResult["PRODUCT_IBLOCK_ID"] = $arParams["IBLOCK_ID"];
		$arResult["ELEMENT_ID"] = $elementID;
		$arResult["ELEMENT_IBLOCK_ID"] = $arParams["IBLOCK_ID"];
	}

	$arParams["CONVERT_CURRENCY"] = isset($arParams["CONVERT_CURRENCY"]) && "Y" == $arParams["CONVERT_CURRENCY"] ? "Y" : "N";
	$arParams["CURRENCY_ID"] = trim(strval($arParams["CURRENCY_ID"]));
	if($arParams["CURRENCY_ID"] == "")
		$arParams["CONVERT_CURRENCY"] = "N";
	elseif($arParams["CONVERT_CURRENCY"] == "N")
		$arParams["CURRENCY_ID"] = "";

	$arParams["PRICE_VAT_INCLUDE"] = $arParams["PRICE_VAT_INCLUDE"] !== "N";

	$arConvertParams = array();
	if($arParams["CONVERT_CURRENCY"] == "Y") {
		if(!Loader::includeModule("currency")) {
			$arParams["CONVERT_CURRENCY"] = "N";
			$arParams["CURRENCY_ID"] = "";
		} else {
			$arCurrencyInfo = CCurrency::GetByID($arParams["CURRENCY_ID"]);
			if(!(is_array($arCurrencyInfo) && !empty($arCurrencyInfo))) {
				$arParams["CONVERT_CURRENCY"] = "N";
				$arParams["CURRENCY_ID"] = "";
			} else {
				$arParams["CURRENCY_ID"] = $arCurrencyInfo["CURRENCY"];
				$arConvertParams["CURRENCY_ID"] = $arCurrencyInfo["CURRENCY"];
			}
		}
	}

	$currentSet = false;
	$productLink = array();
	$allSets = CCatalogProductSet::getAllSetsByProduct($arResult["PRODUCT_ID"], CCatalogProductSet::TYPE_GROUP);
	foreach($allSets as &$oneSet) {
		if($oneSet["ACTIVE"] == "Y") {
			$currentSet = $oneSet;
			break;
		}
	}
	unset($oneSet, $allSets);
	
	if(empty($currentSet)) {
		$this->abortResultCache();
		return;
	}

	Main\Type\Collection::sortByColumn($currentSet["ITEMS"], array("SORT" => SORT_ASC), "", null, true);

	$arSetItemsID = array($arResult["ELEMENT_ID"]);
	$productQuantity = array(
		$arResult["ELEMENT_ID"] => 1
	);
	foreach($currentSet["ITEMS"] as $index => $item) {
		$id = $item["ITEM_ID"];
		$arSetItemsID[] = $id;
		$productLink[$id] = $index;
		$productQuantity[$id] = $item["QUANTITY"];
		unset($id);
	}
	unset($index, $item);

	$countSetDefaultItems = 0;
	
	$arResult["PRICES"] = CIBlockPriceTools::GetCatalogPrices($arResult["PRODUCT_IBLOCK_ID"], $arParams["PRICE_CODE"]);
	$allowPriceTypes = CIBlockPriceTools::GetAllowCatalogPrices($arResult["PRICES"]);

	$arResult["SET_ITEMS"]["DEFAULT"] = array();
	$arResult["SET_ITEMS"]["OTHER"] = array();
	$arResult["SET_ITEMS"]["PRICE"]["DISCOUNT_VALUE"] = 0;
	$arResult["SET_ITEMS"]["PRICE"]["VALUE"] = 0;
	$arResult["SET_ITEMS"]["PRICE"]["DISCOUNT_DIFF"] = 0;

	$arResult["ITEMS_RATIO"] = array_fill_keys($arSetItemsID, 1);
	$ratioResult = Catalog\ProductTable::getCurrentRatioWithMeasure($arSetItemsID);
	foreach($ratioResult as $ratioProduct => $ratioData) {
		$arResult["ITEMS_RATIO"][$ratioProduct] = $ratioData["RATIO"];
		$productQuantity[$ratioProduct] *= $ratioData["RATIO"];
	}
	unset($ratioProduct, $ratioData);

	$tagIblockList = array();
	$tagIblockList[$arResult["PRODUCT_IBLOCK_ID"]] = $arResult["PRODUCT_IBLOCK_ID"];
	$tagIblockList[$arResult["ELEMENT_IBLOCK_ID"]] = $arResult["ELEMENT_IBLOCK_ID"];
	$tagCurrencyList = array();

	$foundMain = false;
	$itemsList = array();
	$offerList = array();
	
	$rsElements = CIBlockElement::GetList(array(), array("ID" => $arSetItemsID, "IBLOCK_LID" => SITE_ID, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y", "CHECK_PERMISSIONS" => "Y", "MIN_PERMISSION" => "R"), false, false, array("ID", "IBLOCK_ID", "CODE", "NAME", "IBLOCK_SECTION_ID", "PREVIEW_PICTURE", "DETAIL_PAGE_URL", "CATALOG_AVAILABLE", "CATALOG_MEASURE"));
	while($obElement = $rsElements->GetNextElement()) {
		$arElement = $obElement->GetFields();
		
		$correct = $arElement["CATALOG_TYPE"] == Catalog\ProductTable::TYPE_PRODUCT || $arElement["CATALOG_TYPE"] == Catalog\ProductTable::TYPE_SET || $arElement["CATALOG_TYPE"] == Catalog\ProductTable::TYPE_OFFER || ($arElement["CATALOG_TYPE"] == Catalog\ProductTable::TYPE_SKU && $arElement["ID"] == $arResult["ELEMENT_ID"]);
		if(!$correct)
			continue;
		
		if($arElement["PREVIEW_PICTURE"] > 0)
			$arElement["PREVIEW_PICTURE"] = CFile::GetFileArray($arElement["PREVIEW_PICTURE"]);

		$arElement["PROPERTIES"] = $obElement->GetProperties();
		
		if($arParams["ADD_PROPERTIES_TO_BASKET"] == "Y" && !empty($arParams["PRODUCT_PROPERTIES"])) {
			$arElement["PRODUCT_PROPERTIES"] = CIBlockPriceTools::GetProductProperties($arElement["IBLOCK_ID"], $arElement["ID"], $arParams["PRODUCT_PROPERTIES"], $arElement["PROPERTIES"]);

			if(!empty($arElement["PRODUCT_PROPERTIES"]))
				$arElement["PRODUCT_PROPERTIES_FILL"] = CIBlockPriceTools::getFillProductProperties($arElement["PRODUCT_PROPERTIES"]);
		}
		
		$itemsList[$arElement["ID"]] = $arElement;
		
		if($arElement["CATALOG_TYPE"] == Catalog\ProductTable::TYPE_OFFER)
			$offerList[$arElement["ID"]] = $arElement["ID"];
		
		if($arElement["ID"] == $arResult["ELEMENT_ID"])
			$foundMain = true;
	}
	unset($correct, $arElement, $obElement, $rsElements);
	
	if(!$foundMain || count($itemsList) < 2) {
		$this->abortResultCache();
		return;
	}
	
	if(!empty($offerList)) {
		$parents = CCatalogSku::getProductList($offerList);
		if(!empty($parents) && is_array($parents)) {
			$offersMap = array();
			foreach($parents as $offerId => $parentData) {
				$offersMap[$parentData["ID"]][$offerId] = $offerId;
			}
			unset($offerId, $parentData);
			
			$rsElements = CIBlockElement::GetList(array(), array("ID" => array_keys($offersMap), "IBLOCK_LID" => SITE_ID, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y", "CHECK_PERMISSIONS" => "Y", "MIN_PERMISSION" => "R"), false, false, array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "PREVIEW_PICTURE"));
			while($obElement = $rsElements->GetNextElement()) {
				$arElement = $obElement->GetFields();
				$arElement["PROPERTIES"] = $obElement->GetProperties();
				
				foreach($offersMap[$arElement["ID"]] as $offerId) {
					unset($offerList[$offerId]);

					if($itemsList[$offerId]["IBLOCK_SECTION_ID"] === null)
						$itemsList[$offerId]["IBLOCK_SECTION_ID"] = $arElement["IBLOCK_SECTION_ID"];
					
					if($itemsList[$offerId]["PREVIEW_PICTURE"] === null) {
						if($arElement["PREVIEW_PICTURE"] > 0)
							$itemsList[$offerId]["PREVIEW_PICTURE"] = CFile::GetFileArray($arElement["PREVIEW_PICTURE"]);
					}
					
					foreach($itemsList[$offerId]["PROPERTIES"] as $arProp) {
						if(($arProp["CODE"] == "OBJECT" || $arProp["CODE"] == "PARTNERS_URL" || $arProp["CODE"] == "OLD_PRICE") && empty($arProp["VALUE"]))
							$itemsList[$offerId]["PROPERTIES"][$arProp["CODE"]]["VALUE"] = $arElement["PROPERTIES"][$arProp["CODE"]]["VALUE"];
					}
					unset($arProp);
				}
				unset($offerId);
			}
			unset($arElement, $obElement, $rsElements);
			unset($offersMap);
		}
		unset($parents);

		if(!empty($offerList)) {
			foreach($offerList as $clearId)
				unset($itemsList[$clearId]);
			unset($clearId);
		}
	}
	
	if(empty($itemsList)) {
		$this->abortResultCache();
		return;
	}
	
	foreach($itemsList as $item) {
		foreach($item["PROPERTIES"] as $prop) {
			if($prop["CODE"] == "OBJECT" && !empty($prop["VALUE"]))
				$objectsIds[] = $prop["VALUE"];
		}
		unset($prop);
	}
	unset($item);

	if(!empty($objectsIds)) {
		$rsElements = CIBlockElement::GetList(array(), array("ID" => array_unique($objectsIds)), false, false, array("ID", "IBLOCK_ID", "NAME", "PROPERTY_PHONE_SMS", "PROPERTY_EMAIL_EMAIL"));
		while($arElement = $rsElements->GetNext()) {
			$arObjects[$arElement["ID"]] = array(
				"NAME" => $arElement["NAME"],
				"PHONE_SMS" => !empty($arElement["PROPERTY_PHONE_SMS_VALUE"]),
				"EMAIL_EMAIL" => !empty($arElement["PROPERTY_EMAIL_EMAIL_VALUE"])
			);
		}
		unset($arElement, $rsElements);
		
		if(!empty($arObjects)) {
			foreach($itemsList as &$item) {		
				foreach($item["PROPERTIES"] as &$prop) {
					if($prop["CODE"] == "OBJECT" && !empty($prop["VALUE"])) {
						if(array_key_exists($prop["VALUE"], $arObjects))
							$prop["FULL_VALUE"] = $arObjects[$prop["VALUE"]];
					}
				}
				unset($prop);
			}
			unset($item);
		}
		unset($arObjects);
	}
	unset($objectsIds);

	foreach($itemsList as $item)
		$tagIblockList[$item["IBLOCK_ID"]] = $item["IBLOCK_ID"];
	unset($item);

	if(!empty($allowPriceTypes)) {
		$prices = array();
		$iterator = Catalog\PriceTable::getList(array(
			"select" => array("ID", "PRODUCT_ID", "CATALOG_GROUP_ID", "PRICE", "CURRENCY", "QUANTITY_FROM", "QUANTITY_TO", "EXTRA_ID"),
			"filter" => array("@PRODUCT_ID" => array_keys($itemsList), "@CATALOG_GROUP_ID" => $allowPriceTypes),
			"order" => array("PRODUCT_ID" => "ASC", "CATALOG_GROUP_ID" => "ASC")
		));
		while($row = $iterator->fetch()) {
			$id = (int)$row["PRODUCT_ID"];
			$rawPrice = array();
			
			if($row["QUANTITY_FROM"] !== null || $row["QUANTITY_TO"] !== null) {
				if(($row["QUANTITY_FROM"] === null || (int)$row["QUANTITY_FROM"] <= $productQuantity[$id]) && ($row["QUANTITY_TO"] === null || (int)$row["QUANTITY_TO"] >= $productQuantity[$id]))
					$rawPrice = $row;
			} else {
				$rawPrice = $row;
			}
			
			if(!empty($rawPrice)) {
				$priceType = $rawPrice["CATALOG_GROUP_ID"];
				$itemsList[$id]["CATALOG_PRICE_ID_".$priceType] = $rawPrice["ID"];
				$itemsList[$id]["~CATALOG_PRICE_ID_".$priceType] = $rawPrice["ID"];
				$itemsList[$id]["CATALOG_PRICE_".$priceType] = $rawPrice["PRICE"];
				$itemsList[$id]["~CATALOG_PRICE_".$priceType] = $rawPrice["PRICE"];
				$itemsList[$id]["CATALOG_CURRENCY_".$priceType] = $rawPrice["CURRENCY"];
				$itemsList[$id]["~CATALOG_CURRENCY_".$priceType] = $rawPrice["CURRENCY"];
				$itemsList[$id]["CATALOG_QUANTITY_FROM_".$priceType] = $rawPrice["QUANTITY_FROM"];
				$itemsList[$id]["~CATALOG_QUANTITY_FROM_".$priceType] = $rawPrice["QUANTITY_FROM"];
				$itemsList[$id]["CATALOG_QUANTITY_TO_".$priceType] = $rawPrice["QUANTITY_TO"];
				$itemsList[$id]["~CATALOG_QUANTITY_TO_".$priceType] = $rawPrice["QUANTITY_TO"];
				$itemsList[$id]["CATALOG_EXTRA_ID_".$priceType] = $rawPrice["EXTRA_ID"];
				$itemsList[$id]["~CATALOG_EXTRA_ID_".$priceType] = $rawPrice["EXTRA_ID"];

				$tagCurrencyList[$rawPrice["CURRENCY"]] = $rawPrice["CURRENCY"];
				unset($priceType);
			}
			unset($rawPrice, $id);
		}
		unset($row, $iterator);
	}

	$item = $itemsList[$arResult["ELEMENT_ID"]];
	$priceList = CIBlockPriceTools::GetItemPrices($item["IBLOCK_ID"], $arResult["PRICES"], $item, $arParams["PRICE_VAT_INCLUDE"], $arConvertParams);
	
	if(empty($priceList)) {
		$this->abortResultCache();
		return;
	}
	
	$minimalPrice = CIBlockPriceTools::getMinPriceFromList($priceList);
	
	if(empty($minimalPrice)) {
		$this->abortResultCache();
		return;
	} else {
		$itemsList[$arResult["ELEMENT_ID"]]["PRICE"] = $minimalPrice;
		
		if($arParams["CONVERT_CURRENCY"] == "N") {
			$arConvertParams["CONVERT_CURRENCY"] = "Y";
			$arConvertParams["CURRENCY_ID"] = $minimalPrice["CURRENCY"];
		}
	}
	unset($minimalPrice, $priceList, $item);

	if($arConvertParams["CURRENCY_ID"] !== "")
		$tagCurrencyList[$arConvertParams["CURRENCY_ID"]] = $arConvertParams["CURRENCY_ID"];

	foreach($itemsList as $item) {
		if($item["ID"] != $arResult["ELEMENT_ID"]) {
			$priceList = CIBlockPriceTools::GetItemPrices($item["IBLOCK_ID"], $arResult["PRICES"], $item, $arParams["PRICE_VAT_INCLUDE"], $arConvertParams);
			
			if(empty($priceList))
				continue;

			$minimalPrice = CIBlockPriceTools::getMinPriceFromList($priceList);
			if(!empty($minimalPrice)) {
				$item["PRICE"] = $minimalPrice;
			}
			unset($minimalPrice);
		}

		$item["CAN_BUY"] = CIBlockPriceTools::CanBuy($item["IBLOCK_ID"], $arResult["PRICES"], $item);

		if(isset($productLink[$item["ID"]])) {
			$index = $productLink[$item["ID"]];
			$currentSet["ITEMS"][$index]["ITEM_DATA"] = $item;
			unset($index);
		} elseif($item["ID"] == $arResult["ELEMENT_ID"]) {
			$currentSet["ITEM_DATA"] = $item;
		}
	}
	unset($item, $itemsList);
	
	if(empty($currentSet["ITEM_DATA"])) {
		$this->abortResultCache();
		return;
	}

	$defaultMeasure = CCatalogMeasure::getDefaultMeasure(true, true);
	
	$arResult["ELEMENT"] = $currentSet["ITEM_DATA"];
	$arResult["ELEMENT"]["SET_QUANTITY"] = 1;
	$arResult["ELEMENT"]["MEASURE_RATIO"] = $arResult["ITEMS_RATIO"][$arResult["ELEMENT"]["ID"]];
	$arResult["ELEMENT"]["MEASURE"] = !empty($ratioResult[$arResult["ELEMENT"]["ID"]]["MEASURE"]) ? $ratioResult[$arResult["ELEMENT"]["ID"]]["MEASURE"] : $defaultMeasure;
	$arResult["ELEMENT"]["BASKET_QUANTITY"] = $arResult["ELEMENT"]["MEASURE_RATIO"];
	
	if(!empty($arResult["ELEMENT"]["PROPERTIES"]["OLD_PRICE"]["VALUE"]) && $arResult["ELEMENT"]["PRICE"]["DISCOUNT_VALUE"] == $arResult["ELEMENT"]["PRICE"]["VALUE"]) {
		$oldPrice = str_replace(",", ".", $arResult["ELEMENT"]["PROPERTIES"]["OLD_PRICE"]["VALUE"]);		
		$arResult["ELEMENT"]["PRICE"]["VALUE"] = Price::roundPrice($arResult["ELEMENT"]["PRICE"]["PRICE_ID"], $oldPrice, $arResult["ELEMENT"]["PRICE"]["CURRENCY"]);
		$arResult["ELEMENT"]["PRICE"]["PRINT_VALUE"] = CCurrencyLang::CurrencyFormat($arResult["ELEMENT"]["PRICE"]["VALUE"], $arResult["ELEMENT"]["PRICE"]["CURRENCY"], true);		
		$arResult["ELEMENT"]["PRICE"]["DISCOUNT_DIFF"] = Price::roundPrice($arResult["ELEMENT"]["PRICE"]["PRICE_ID"], $arResult["ELEMENT"]["PRICE"]["VALUE"] - $arResult["ELEMENT"]["PRICE"]["DISCOUNT_VALUE"], $arResult["ELEMENT"]["PRICE"]["CURRENCY"]);
		$arResult["ELEMENT"]["PRICE"]["PRINT_DISCOUNT_DIFF"] = CCurrencyLang::CurrencyFormat($arResult["ELEMENT"]["PRICE"]["DISCOUNT_DIFF"], $arResult["ELEMENT"]["PRICE"]["CURRENCY"], true);
		$arResult["ELEMENT"]["PRICE"]["DISCOUNT_DIFF_PERCENT"] = roundEx(100 * $arResult["ELEMENT"]["PRICE"]["DISCOUNT_DIFF"] / $arResult["ELEMENT"]["PRICE"]["VALUE"], 0);
		unset($oldPrice);
	}

	$arResult["ELEMENT"]["PRICE"]["RATIO_DISCOUNT_VALUE"] = Price::roundPrice($arResult["ELEMENT"]["PRICE"]["PRICE_ID"], $arResult["ELEMENT"]["PRICE"]["DISCOUNT_VALUE"] * $arResult["ELEMENT"]["BASKET_QUANTITY"], $arResult["ELEMENT"]["PRICE"]["CURRENCY"]);
	$arResult["ELEMENT"]["PRICE"]["PRINT_RATIO_DISCOUNT_VALUE"] = CCurrencyLang::CurrencyFormat($arResult["ELEMENT"]["PRICE"]["RATIO_DISCOUNT_VALUE"], $arResult["ELEMENT"]["PRICE"]["CURRENCY"], true);
	$arResult["ELEMENT"]["PRICE"]["RATIO_VALUE"] = Price::roundPrice($arResult["ELEMENT"]["PRICE"]["PRICE_ID"], $arResult["ELEMENT"]["PRICE"]["VALUE"] * $arResult["ELEMENT"]["BASKET_QUANTITY"], $arResult["ELEMENT"]["PRICE"]["CURRENCY"]);
	$arResult["ELEMENT"]["PRICE"]["PRINT_RATIO_VALUE"] = CCurrencyLang::CurrencyFormat($arResult["ELEMENT"]["PRICE"]["RATIO_VALUE"], $arResult["ELEMENT"]["PRICE"]["CURRENCY"], true);		
	$arResult["ELEMENT"]["PRICE"]["RATIO_DISCOUNT_DIFF"] = Price::roundPrice($arResult["ELEMENT"]["PRICE"]["PRICE_ID"], $arResult["ELEMENT"]["PRICE"]["DISCOUNT_DIFF"] * $arResult["ELEMENT"]["BASKET_QUANTITY"], $arResult["ELEMENT"]["PRICE"]["CURRENCY"]);
	$arResult["ELEMENT"]["PRICE"]["PRINT_RATIO_DISCOUNT_DIFF"] = CCurrencyLang::CurrencyFormat($arResult["ELEMENT"]["PRICE"]["RATIO_DISCOUNT_DIFF"], $arResult["ELEMENT"]["PRICE"]["CURRENCY"], true);
	
	$arResult["ELEMENT"]["OBJECT"] = !empty($arResult["ELEMENT"]["PROPERTIES"]["OBJECT"]["FULL_VALUE"]) ? $arResult["ELEMENT"]["PROPERTIES"]["OBJECT"]["FULL_VALUE"] : false;
	$arResult["ELEMENT"]["OBJECT_CONTACTS"] = $arResult["ELEMENT"]["OBJECT"]["PHONE_SMS"] || $arResult["ELEMENT"]["OBJECT"]["EMAIL_EMAIL"] ? true : false;
	$arResult["ELEMENT"]["PARTNERS_URL"] = !empty($arResult["ELEMENT"]["PROPERTIES"]["PARTNERS_URL"]["VALUE"]) ? true : false;
	
	$arResult["SET_ITEMS"]["PRICE"]["DISCOUNT_VALUE"] = $arResult["ELEMENT"]["PRICE"]["RATIO_DISCOUNT_VALUE"];
	$arResult["SET_ITEMS"]["PRICE"]["VALUE"] = $arResult["ELEMENT"]["PRICE"]["RATIO_VALUE"];
	$arResult["SET_ITEMS"]["PRICE"]["DISCOUNT_DIFF"] = $arResult["ELEMENT"]["PRICE"]["RATIO_DISCOUNT_DIFF"];
	
	$arResult["BASKET_QUANTITY"] = array(
		$arResult["ELEMENT"]["ID"] => $arResult["ELEMENT"]["BASKET_QUANTITY"]
	);

	$defaultCurrency = $arResult["ELEMENT"]["PRICE"]["CURRENCY"];
	$compareCurrency = empty($arConvertParams) || $arConvertParams["CONVERT_CURRENCY"] == "N";
	$found = false;
	$resort = false;
	foreach($currentSet["ITEMS"] as &$setItem) {
		if(!isset($setItem["ITEM_DATA"]))
			continue;

		$setItem["ITEM_DATA"]["SET_QUANTITY"] = empty($setItem["QUANTITY"]) ? 1 : $setItem["QUANTITY"];
		$setItem["ITEM_DATA"]["MEASURE_RATIO"] = $arResult["ITEMS_RATIO"][$setItem["ITEM_DATA"]["ID"]];
		$setItem["ITEM_DATA"]["MEASURE"] = !empty($ratioResult[$setItem["ITEM_DATA"]["ID"]]["MEASURE"]) ? $ratioResult[$setItem["ITEM_DATA"]["ID"]]["MEASURE"] : $defaultMeasure;
		$setItem["ITEM_DATA"]["BASKET_QUANTITY"] = $setItem["ITEM_DATA"]["SET_QUANTITY"] * $setItem["ITEM_DATA"]["MEASURE_RATIO"];		
		$arResult["BASKET_QUANTITY"][$setItem["ITEM_DATA"]["ID"]] = $setItem["ITEM_DATA"]["BASKET_QUANTITY"];
		$setItem["ITEM_DATA"]["SET_SORT"] = $setItem["SORT"];

		if(!empty($setItem["ITEM_DATA"]["PROPERTIES"]["OLD_PRICE"]["VALUE"]) && $setItem["ITEM_DATA"]["PRICE"]["DISCOUNT_VALUE"] == $setItem["ITEM_DATA"]["PRICE"]["VALUE"]) {
			$oldPrice = str_replace(",", ".", $setItem["ITEM_DATA"]["PROPERTIES"]["OLD_PRICE"]["VALUE"]);
			$setItem["ITEM_DATA"]["PRICE"]["VALUE"] = Price::roundPrice($setItem["ITEM_DATA"]["PRICE"]["PRICE_ID"], $oldPrice, $setItem["ITEM_DATA"]["PRICE"]["CURRENCY"]);
			$setItem["ITEM_DATA"]["PRICE"]["PRINT_VALUE"] = CCurrencyLang::CurrencyFormat($setItem["ITEM_DATA"]["PRICE"]["VALUE"], $setItem["ITEM_DATA"]["PRICE"]["CURRENCY"], true);
			$setItem["ITEM_DATA"]["PRICE"]["DISCOUNT_DIFF"] = Price::roundPrice($setItem["ITEM_DATA"]["PRICE"]["PRICE_ID"], $setItem["ITEM_DATA"]["PRICE"]["VALUE"] - $setItem["ITEM_DATA"]["PRICE"]["DISCOUNT_VALUE"], $setItem["ITEM_DATA"]["PRICE"]["CURRENCY"]);
			$setItem["ITEM_DATA"]["PRICE"]["PRINT_DISCOUNT_DIFF"] = CCurrencyLang::CurrencyFormat($setItem["ITEM_DATA"]["PRICE"]["DISCOUNT_DIFF"], $setItem["ITEM_DATA"]["PRICE"]["CURRENCY"], true);
			$setItem["ITEM_DATA"]["PRICE"]["DISCOUNT_DIFF_PERCENT"] = roundEx(100 * $setItem["ITEM_DATA"]["PRICE"]["DISCOUNT_DIFF"] / $setItem["ITEM_DATA"]["PRICE"]["VALUE"], 0);
			unset($oldPrice);
		}
		
		if($compareCurrency && $setItem["ITEM_DATA"]["PRICE"]["CURRENCY"] != $defaultCurrency) {
			$setItem["ITEM_DATA"]["PRICE"]["DISCOUNT_VALUE"] = CCurrencyRates::ConvertCurrency($setItem["ITEM_DATA"]["PRICE"]["DISCOUNT_VALUE"], $setItem["ITEM_DATA"]["PRICE"]["CURRENCY"], $defaultCurrency);
			$setItem["ITEM_DATA"]["PRICE"]["VALUE"] = CCurrencyRates::ConvertCurrency($setItem["ITEM_DATA"]["PRICE"]["VALUE"], $setItem["ITEM_DATA"]["PRICE"]["CURRENCY"], $defaultCurrency);
			$setItem["ITEM_DATA"]["PRICE"]["DISCOUNT_DIFF"] = CCurrencyRates::ConvertCurrency($setItem["ITEM_DATA"]["PRICE"]["DISCOUNT_DIFF"], $setItem["ITEM_DATA"]["PRICE"]["CURRENCY"], $defaultCurrency);
			$setItem["ITEM_DATA"]["PRICE"]["CURRENCY"] = $defaultCurrency;
		}

		$setItem["ITEM_DATA"]["PRICE"]["RATIO_DISCOUNT_VALUE"] = $setItem["ITEM_DATA"]["PRICE"]["DISCOUNT_VALUE"] * $setItem["ITEM_DATA"]["BASKET_QUANTITY"];		
		$setItem["ITEM_DATA"]["PRICE"]["PRINT_RATIO_DISCOUNT_VALUE"] = CCurrencyLang::CurrencyFormat($setItem["ITEM_DATA"]["PRICE"]["RATIO_DISCOUNT_VALUE"], $setItem["ITEM_DATA"]["PRICE"]["CURRENCY"], true);		
		$setItem["ITEM_DATA"]["PRICE"]["RATIO_VALUE"] = $setItem["ITEM_DATA"]["PRICE"]["VALUE"] * $setItem["ITEM_DATA"]["BASKET_QUANTITY"];
		$setItem["ITEM_DATA"]["PRICE"]["PRINT_RATIO_VALUE"] = CCurrencyLang::CurrencyFormat($setItem["ITEM_DATA"]["PRICE"]["RATIO_VALUE"], $setItem["ITEM_DATA"]["PRICE"]["CURRENCY"], true);		
		$setItem["ITEM_DATA"]["PRICE"]["RATIO_DISCOUNT_DIFF"] = $setItem["ITEM_DATA"]["PRICE"]["DISCOUNT_DIFF"] * $setItem["ITEM_DATA"]["BASKET_QUANTITY"];
		$setItem["ITEM_DATA"]["PRICE"]["PRINT_RATIO_DISCOUNT_DIFF"] = CCurrencyLang::CurrencyFormat($setItem["ITEM_DATA"]["PRICE"]["RATIO_DISCOUNT_DIFF"], $setItem["ITEM_DATA"]["PRICE"]["CURRENCY"], true);
		
		$setItem["ITEM_DATA"]["OBJECT"] = !empty($setItem["ITEM_DATA"]["PROPERTIES"]["OBJECT"]["FULL_VALUE"]) ? $setItem["ITEM_DATA"]["PROPERTIES"]["OBJECT"]["FULL_VALUE"] : false;
		$setItem["ITEM_DATA"]["OBJECT_CONTACTS"] = $setItem["ITEM_DATA"]["OBJECT"]["PHONE_SMS"] || $setItem["ITEM_DATA"]["OBJECT"]["EMAIL_EMAIL"] ? true : false;
		$setItem["ITEM_DATA"]["PARTNERS_URL"] = !empty($setItem["ITEM_DATA"]["PROPERTIES"]["PARTNERS_URL"]["VALUE"]) ? true : false;
		
		if($setItem["ITEM_DATA"]["CAN_BUY"] && $setItem["ITEM_DATA"]["PRICE"]["RATIO_DISCOUNT_VALUE"] > 0 && (!$setItem["ITEM_DATA"]["OBJECT"] || ($setItem["ITEM_DATA"]["OBJECT"] && $setItem["ITEM_DATA"]["OBJECT_CONTACTS"])) && !$setItem["ITEM_DATA"]["PARTNERS_URL"] && $countSetDefaultItems < $arParams["BUNDLE_ITEMS_COUNT"]) {
			$arResult["SET_ITEMS"]["DEFAULT"][] = $setItem["ITEM_DATA"];
			
			$arResult["SET_ITEMS"]["PRICE"]["DISCOUNT_VALUE"] += $setItem["ITEM_DATA"]["PRICE"]["RATIO_DISCOUNT_VALUE"];			
			$arResult["SET_ITEMS"]["PRICE"]["VALUE"] += $setItem["ITEM_DATA"]["PRICE"]["RATIO_VALUE"];	
			$arResult["SET_ITEMS"]["PRICE"]["DISCOUNT_DIFF"] += $setItem["ITEM_DATA"]["PRICE"]["RATIO_DISCOUNT_DIFF"];		
			
			$countSetDefaultItems++;
		} else {
			if(!$setItem["ITEM_DATA"]["CAN_BUY"])
				$resort = true;
			$arResult["SET_ITEMS"]["OTHER"][] = $setItem["ITEM_DATA"];
		}
		$found = true;
	}
	unset($setItem, $currentSet);
	
	if(!$found || empty($arResult["SET_ITEMS"]["DEFAULT"])) {
		$this->abortResultCache();
		return;
	}
	unset($found);
	
	if($resort)
		Main\Type\Collection::sortByColumn($arResult["SET_ITEMS"]["OTHER"], array("CAN_BUY" => SORT_DESC, "SET_SORT" => SORT_ASC));
	unset($resort);

	if(defined("BX_COMP_MANAGED_CACHE") && (!empty($tagIblockList) || !empty($tagCurrencyList))) {
		$taggedCache = Application::getInstance()->getTaggedCache();
		if(!empty($tagIblockList)) {
			foreach($tagIblockList as $iblock)
				$taggedCache->registerTag("iblock_id_".$iblock);
			unset($iblock);
		}
		if(!empty($tagCurrencyList)) {
			foreach($tagCurrencyList as $currency)
				$taggedCache->registerTag("currency_id_".$currency);
			unset($currency);
		}
	}

	$arResult["SHOW_DEFAULT_SET_DISCOUNT"] = true;
	if($arResult["SET_ITEMS"]["PRICE"]["VALUE"] && $arResult["SET_ITEMS"]["PRICE"]["VALUE"] != $arResult["SET_ITEMS"]["PRICE"]["DISCOUNT_VALUE"]) {
		$arResult["SET_ITEMS"]["PRICE"]["VALUE"] = CCurrencyLang::CurrencyFormat($arResult["SET_ITEMS"]["PRICE"]["VALUE"], $defaultCurrency, true);
	} else {
		$arResult["SET_ITEMS"]["PRICE"]["VALUE"] = 0;
		$arResult["SHOW_DEFAULT_SET_DISCOUNT"] = false;
	}	
	if($arResult["SET_ITEMS"]["PRICE"]["DISCOUNT_VALUE"])
		$arResult["SET_ITEMS"]["PRICE"]["DISCOUNT_VALUE"] = CCurrencyLang::CurrencyFormat($arResult["SET_ITEMS"]["PRICE"]["DISCOUNT_VALUE"], $defaultCurrency, true);
	if($arResult["SET_ITEMS"]["PRICE"]["DISCOUNT_DIFF"])
		$arResult["SET_ITEMS"]["PRICE"]["DISCOUNT_DIFF"] = CCurrencyLang::CurrencyFormat($arResult["SET_ITEMS"]["PRICE"]["DISCOUNT_DIFF"], $defaultCurrency, true);
	
	$currencyFormat = CCurrencyLang::GetFormatDescription($defaultCurrency);
	$arResult["CURRENCIES"] = array(
		array(
			"CURRENCY" => $defaultCurrency,
			"FORMAT" => array(
				"FORMAT_STRING" => $currencyFormat["FORMAT_STRING"],
				"DEC_POINT" => $currencyFormat["DEC_POINT"],
				"THOUSANDS_SEP" => $currencyFormat["THOUSANDS_SEP"],
				"DECIMALS" => $currencyFormat["DECIMALS"],
				"THOUSANDS_VARIANT" => $currencyFormat["THOUSANDS_VARIANT"],
				"HIDE_ZERO" => $currencyFormat["HIDE_ZERO"]
			)
		)
	);
	unset($currencyFormat);
	$arResult["CONVERT_CURRENCY"] = $arConvertParams;

	$this->setResultCacheKeys(array());
	$this->includeComponentTemplate();
}