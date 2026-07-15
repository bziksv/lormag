<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

//OBJECTS//
foreach($arResult["ITEMS"] as $item) {
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
		foreach($arResult["ITEMS"] as &$item) {
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

//DISPLAY_PROPERTIES//
foreach($arResult["ITEMS"] as &$item) {
	if(!empty($item["DISPLAY_PROPERTIES"])) {
		foreach($item["DISPLAY_PROPERTIES"] as &$property) {
			if($property["CODE"] == "BRAND") {
				continue;
			} elseif($property["CODE"] == "COLLECTION") {
				$property["DISPLAY_VALUE"] = strip_tags($property["DISPLAY_VALUE"]);
				$rsElements = CIBlockElement::GetList(array(), array("ID" => $property["VALUE"], "IBLOCK_ID" => $property["LINK_IBLOCK_ID"]), false, false, array("ID", "IBLOCK_ID", "CODE", "NAME"));	
				while($obElement = $rsElements->GetNextElement()) {
					$arElement = $obElement->GetFields();
					$arElement["PROPERTIES"] = $obElement->GetProperties();
					foreach($arElement["PROPERTIES"] as $arCollectProp) {
						if($arCollectProp["CODE"] == "BRAND" && !empty($arCollectProp["VALUE"])) {
							$rsBrand = CIBlockElement::GetList(array(), array("ID" => $arCollectProp["VALUE"], "IBLOCK_ID" => $arCollectProp["LINK_IBLOCK_ID"]), false, false, array("ID", "IBLOCK_ID", "DETAIL_PAGE_URL"));
							if($arBrand = $rsBrand->GetNext()) {
								$property["DISPLAY_VALUE"] = "<a href='".$arBrand["~DETAIL_PAGE_URL"].$arElement["CODE"]."/'>".$arElement["NAME"]."</a>";
							}
							unset($arBrand, $rsBrand);
						}
					}
					unset($arCollectProp);
				}
				unset($arElement, $obElement, $rsElements);
			} else {
				$property["DISPLAY_VALUE"] = is_array($property["DISPLAY_VALUE"]) ? implode(" / ", $property["DISPLAY_VALUE"]) : strip_tags($property["DISPLAY_VALUE"]);
			}
		}
		unset($property);
	}
}
unset($item);

//RATING_REVIEWS_COUNT//
if($arParams["USE_REVIEW"] != "N" && $arParams["REVIEWS_IBLOCK_ID"] > 0) {
	$itemParentIds = array();

	foreach($arResult["ITEMS"] as $item) {
		$itemParentIds[] = $item["PARENT_ID"];
		
		$ratingSum[$item["PARENT_ID"]] = 0;
		$reviewsCount[$item["PARENT_ID"]] = 0;
	}
	unset($item);

	if(count($itemParentIds) > 0) {
		$rsElements = CIBlockElement::GetList(array(), array("ACTIVE" => "Y", "IBLOCK_ID" => $arParams["REVIEWS_IBLOCK_ID"], "PROPERTY_PRODUCT_ID" => array_unique($itemParentIds)), false, false, array("ID", "IBLOCK_ID"));
		while($obElement = $rsElements->GetNextElement()) {
			$arElement = $obElement->GetFields();
			$arProps = $obElement->GetProperties();

			$ratingSum[$arProps["PRODUCT_ID"]["VALUE"]] += $arProps["RATING"]["VALUE_XML_ID"];
			
			$reviewsCount[$arProps["PRODUCT_ID"]["VALUE"]]++;
		}
		unset($arProps, $arElement, $obElement, $rsElements);

		$reviewsDeclension = new Bitrix\Main\Grid\Declension(Loc::getMessage("CATALOG_REVIEW"), Loc::getMessage("CATALOG_REVIEWS_1"), Loc::getMessage("CATALOG_REVIEWS_2"));

		foreach($arResult["ITEMS"] as &$item) {
			$item["RATING_VALUE"] = $reviewsCount[$item["PARENT_ID"]] > 0 ? sprintf("%.1f", round($ratingSum[$item["PARENT_ID"]] / $reviewsCount[$item["PARENT_ID"]], 1)) : 0;
			$item["REVIEWS_COUNT"] = $reviewsCount[$item["PARENT_ID"]];
			$item["REVIEWS_DECLENSION"] = $reviewsDeclension->get($reviewsCount[$item["PARENT_ID"]]);
		}
		unset($item, $reviewsDeclension);
	}
	unset($reviewsCount, $ratingSum, $itemParentIds);
}

//MEASURE//
$itemIds = $arMeasureList = array();

foreach($arResult["ITEMS"] as $item) {
	$itemIds[] = $item["ID"];
}
unset($item);

if(count($itemIds) > 0) {
	$arMeasureList = Bitrix\Catalog\ProductTable::getCurrentRatioWithMeasure($itemIds);

	foreach($arResult["ITEMS"] as &$item) {
		if(array_key_exists($item["ID"], $arMeasureList))
			$item["MEASURE"] = $arMeasureList[$item["ID"]]["MEASURE"];
	}
	unset($item);
}
unset($arMeasureList, $itemIds);