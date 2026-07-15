<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arDefaultSetIds = array($arResult["ELEMENT"]["ID"]);

$itemBrandId = $arResult["ELEMENT"]["PROPERTIES"]["BRAND"]["VALUE"];
if(!empty($itemBrandId))
	$arSetBrandsIds = array($itemBrandId);
else
	$arSetBrandsIds = array();

$arSetSectionsIds = array();

foreach(array("DEFAULT", "OTHER") as $type) {
	foreach($arResult["SET_ITEMS"][$type] as $arItem) {
		if($type == "DEFAULT")
			$arDefaultSetIds[] = $arItem["ID"];

		foreach($arItem["PROPERTIES"] as $arProp) {
			if($arProp["CODE"] == "BRAND" && !empty($arProp["VALUE"]))
				$arSetBrandsIds[] = $arProp["VALUE"];
		}
		unset($arProp);
		
		$arSetSectionsIds[] = $arItem["IBLOCK_SECTION_ID"];
	}
	unset($arItem);
}
unset($type);

//DEFAULT_SET_IDS//
$arResult["DEFAULT_SET_IDS"] = $arDefaultSetIds;

//BRANDS//
if(!empty($arSetBrandsIds)) {
	$arSetBrands = array();
	$rsElements = CIBlockElement::GetList(array(), array("ID" => array_unique($arSetBrandsIds)), false, false, array("ID", "IBLOCK_ID", "NAME", "PREVIEW_PICTURE"));
	while($arElement = $rsElements->GetNext()) {
		$arSetBrands[$arElement["ID"]] = array(
			"NAME" => $arElement["NAME"],
			"PREVIEW_PICTURE" => $arElement["PREVIEW_PICTURE"] > 0 ? CFile::GetFileArray($arElement["PREVIEW_PICTURE"]) : array()
		);
	}
	unset($arElement, $rsElements);
	
	if(!empty($arSetBrands)) {
		if(!empty($itemBrandId)) {
			if(array_key_exists($itemBrandId, $arSetBrands))
				$arResult["ELEMENT"]["PROPERTIES"]["BRAND"]["FULL_VALUE"] = $arSetBrands[$itemBrandId];
		}
		unset($itemBrandId);
		
		foreach(array("DEFAULT", "OTHER") as &$type) {
			foreach($arResult["SET_ITEMS"][$type] as &$arItem) {	
				foreach($arItem["PROPERTIES"] as &$arProp) {
					if($arProp["CODE"] == "BRAND" && !empty($arProp["VALUE"])) {
						if(array_key_exists($arProp["VALUE"], $arSetBrands))
							$arProp["FULL_VALUE"] = $arSetBrands[$arProp["VALUE"]];
					}
				}
				unset($arProp);
			}
			unset($arItem);
		}
		unset($type);
	}
	unset($arSetBrands);
}
unset($arSetBrandsIds);

//SET_ITEMS_SECTIONS//
if(!empty($arSetSectionsIds)) {
	$rsSections = CIBlockSection::GetList(array("NAME" => "ASC"), array("ID" => array_unique($arSetSectionsIds)), false, array("ID", "IBLOCK_ID", "NAME"));
	while($arSection = $rsSections->GetNext()) {
		$arResult["SET_ITEMS"]["SECTIONS"][$arSection["ID"]] = array(
			"ID" => $arSection["ID"],
			"NAME" => $arSection["NAME"],
			"ITEMS" => array()
		);
	}
	unset($arSection, $rsSections);
}
unset($arSetSectionsIds);

foreach($arResult["SET_ITEMS"]["OTHER"] as $arItem) {
	if(array_key_exists($arItem["IBLOCK_SECTION_ID"], $arResult["SET_ITEMS"]["SECTIONS"]))
		$arResult["SET_ITEMS"]["SECTIONS"][$arItem["IBLOCK_SECTION_ID"]]["ITEMS"][$arItem["ID"]] = $arItem;
}
unset($arItem);