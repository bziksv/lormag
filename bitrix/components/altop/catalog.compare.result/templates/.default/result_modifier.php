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

//DETAIL_PAGE_URL: /product/{code}/ like catalog cards (not /catalog/product/{CODE}/)
$compareUrlMeta = array();
$compareParentIds = array();
foreach($arResult["ITEMS"] as $item) {
	$compareParentIds[] = !empty($item["PARENT_ID"]) ? $item["PARENT_ID"] : $item["ID"];
}
unset($item);
if(!empty($compareParentIds)) {
	$rsUrlElements = CIBlockElement::GetList(
		array(),
		array("ID" => array_unique($compareParentIds)),
		false,
		false,
		array("ID", "IBLOCK_ID", "CODE")
	);
	while($obUrlElement = $rsUrlElements->GetNextElement()) {
		$arUrlElement = $obUrlElement->GetFields();
		$arUrlProps = $obUrlElement->GetProperties();
		$compareUrlMeta[$arUrlElement["ID"]] = array(
			"CODE" => (string)$arUrlElement["CODE"],
			"PROP_CODE" => !empty($arUrlProps["code"]["VALUE"]) ? (string)$arUrlProps["code"]["VALUE"] : ""
		);
	}
	unset($arUrlProps, $arUrlElement, $obUrlElement, $rsUrlElements);
}
unset($compareParentIds);

foreach($arResult["ITEMS"] as &$item) {
	$parentId = !empty($item["PARENT_ID"]) ? $item["PARENT_ID"] : $item["ID"];
	$detailUrl = (string)$item["DETAIL_PAGE_URL"];
	$detailUrl = str_replace("catalog/product", "product", $detailUrl);

	$elementCode = isset($compareUrlMeta[$parentId]["CODE"]) ? $compareUrlMeta[$parentId]["CODE"] : (string)$item["CODE"];
	$propCode = isset($compareUrlMeta[$parentId]["PROP_CODE"]) ? $compareUrlMeta[$parentId]["PROP_CODE"] : "";
	if($propCode === "" && !empty($item["PROPERTIES"]["code"]["VALUE"]))
		$propCode = (string)$item["PROPERTIES"]["code"]["VALUE"];

	if($propCode !== "" && $elementCode !== "" && $propCode !== $elementCode)
		$detailUrl = str_replace($elementCode, $propCode, $detailUrl);

	$item["DETAIL_PAGE_URL"] = $detailUrl;
	$item["~DETAIL_PAGE_URL"] = $detailUrl;
}
unset($item, $compareUrlMeta);

//Sanitize long HTML text fields so compare table cells don't break layout
$compareTextFields = array("PREVIEW_TEXT", "DETAIL_TEXT");
$compareTextLimit = 280;
foreach($arResult["ITEMS"] as &$item) {
	foreach($compareTextFields as $textCode) {
		if(!empty($item["FIELDS"][$textCode]) && is_string($item["FIELDS"][$textCode])) {
			$text = trim(preg_replace("/\\s+/u", " ", html_entity_decode(strip_tags($item["FIELDS"][$textCode]), ENT_QUOTES | ENT_HTML5, "UTF-8")));
			if(function_exists("mb_strlen") && mb_strlen($text) > $compareTextLimit)
				$text = rtrim(mb_substr($text, 0, $compareTextLimit), " .,;:") . "…";
			elseif(strlen($text) > $compareTextLimit)
				$text = rtrim(substr($text, 0, $compareTextLimit), " .,;:") . "…";
			$item["FIELDS"][$textCode] = htmlspecialcharsbx($text);
		}
		if(!empty($item["OFFER_FIELDS"][$textCode]) && is_string($item["OFFER_FIELDS"][$textCode])) {
			$text = trim(preg_replace("/\\s+/u", " ", html_entity_decode(strip_tags($item["OFFER_FIELDS"][$textCode]), ENT_QUOTES | ENT_HTML5, "UTF-8")));
			if(function_exists("mb_strlen") && mb_strlen($text) > $compareTextLimit)
				$text = rtrim(mb_substr($text, 0, $compareTextLimit), " .,;:") . "…";
			elseif(strlen($text) > $compareTextLimit)
				$text = rtrim(substr($text, 0, $compareTextLimit), " .,;:") . "…";
			$item["OFFER_FIELDS"][$textCode] = htmlspecialcharsbx($text);
		}
	}
	unset($textCode);
}
unset($item, $compareTextFields, $compareTextLimit);

// Hide duplicate article prop: CML2_ARTICLE ("Артикул") vs ARTICLS ("Артикулы")
unset(
	$arResult["SHOW_PROPERTIES"]["ARTICLS"],
	$arResult["DELETED_PROPERTIES"]["ARTICLS"],
	$arResult["SHOW_OFFER_PROPERTIES"]["ARTICLS"],
	$arResult["DELETED_OFFER_PROPERTIES"]["ARTICLS"]
);

//SECTIONS: group by top-level catalog section (not leaf subsections)
$arResult["COMPARE_SECTIONS"] = array();
$arResult["COMPARE_SECTION_ID"] = 0;

if(!empty($arResult["ITEMS"])) {
	$iblockId = intval($arParams["IBLOCK_ID"]);
	$leafIds = array();
	foreach($arResult["ITEMS"] as $item) {
		$leafId = intval($item["IBLOCK_SECTION_ID"]);
		if($leafId > 0)
			$leafIds[$leafId] = $leafId;
	}
	unset($item);

	// leaf section ID -> top-level (DEPTH_LEVEL=1) section
	$leafToRoot = array();
	$rootMeta = array();
	if(!empty($leafIds) && $iblockId > 0) {
		foreach($leafIds as $leafId) {
			$rootId = $leafId;
			$rootName = "";
			$rsChain = CIBlockSection::GetNavChain($iblockId, $leafId, array("ID", "NAME", "DEPTH_LEVEL"));
			while($arChain = $rsChain->Fetch()) {
				if(intval($arChain["DEPTH_LEVEL"]) === 1) {
					$rootId = intval($arChain["ID"]);
					$rootName = (string)$arChain["NAME"];
					break;
				}
			}
			unset($arChain, $rsChain);
			$leafToRoot[$leafId] = $rootId;
			if(!isset($rootMeta[$rootId])) {
				$rootMeta[$rootId] = array(
					"ID" => $rootId,
					"NAME" => ($rootName !== "" ? $rootName : Loc::getMessage("CATALOG_COMPARE_SECTION_OTHER")),
					"COUNT" => 0
				);
			}
		}
		unset($leafId, $rootId, $rootName);
	}

	$sectionCounts = array();
	foreach($arResult["ITEMS"] as &$item) {
		$leafId = intval($item["IBLOCK_SECTION_ID"]);
		$rootId = ($leafId > 0 && isset($leafToRoot[$leafId])) ? $leafToRoot[$leafId] : 0;
		$item["COMPARE_ROOT_SECTION_ID"] = $rootId;
		if(!isset($sectionCounts[$rootId]))
			$sectionCounts[$rootId] = 0;
		$sectionCounts[$rootId]++;
		if($rootId > 0 && isset($rootMeta[$rootId]))
			$rootMeta[$rootId]["COUNT"] = $sectionCounts[$rootId];
	}
	unset($item, $leafId, $rootId, $leafToRoot, $leafIds);

	if(count($sectionCounts) > 1) {
		foreach($sectionCounts as $rootId => $count) {
			if($rootId > 0 && isset($rootMeta[$rootId])) {
				$arResult["COMPARE_SECTIONS"][] = $rootMeta[$rootId];
			} else {
				$arResult["COMPARE_SECTIONS"][] = array(
					"ID" => 0,
					"NAME" => Loc::getMessage("CATALOG_COMPARE_SECTION_OTHER"),
					"COUNT" => intval($count)
				);
			}
		}
		unset($rootMeta, $sectionCounts, $rootId, $count);

		$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
		$sectionVar = $arParams["SECTION_ID_VARIABLE"];
		$activeSectionId = intval($request->get($sectionVar));
		$validSectionIds = array();
		foreach($arResult["COMPARE_SECTIONS"] as $section)
			$validSectionIds[] = intval($section["ID"]);
		unset($section);

		// Old leaf SECTION_ID in URL → map to its root via nav chain
		if($activeSectionId > 0 && !in_array($activeSectionId, $validSectionIds, true) && $iblockId > 0) {
			$rsChain = CIBlockSection::GetNavChain($iblockId, $activeSectionId, array("ID", "DEPTH_LEVEL"));
			while($arChain = $rsChain->Fetch()) {
				if(intval($arChain["DEPTH_LEVEL"]) === 1) {
					$mappedRoot = intval($arChain["ID"]);
					if(in_array($mappedRoot, $validSectionIds, true))
						$activeSectionId = $mappedRoot;
					break;
				}
			}
			unset($arChain, $rsChain, $mappedRoot);
		}

		if(!in_array($activeSectionId, $validSectionIds, true))
			$activeSectionId = $validSectionIds[0];

		$arResult["COMPARE_SECTION_ID"] = $activeSectionId;
		$filteredItems = array();
		foreach($arResult["ITEMS"] as $item) {
			if(intval($item["COMPARE_ROOT_SECTION_ID"]) === $activeSectionId)
				$filteredItems[] = $item;
		}
		unset($item);
		$arResult["ITEMS"] = $filteredItems;
		unset($filteredItems, $validSectionIds);
	}
}