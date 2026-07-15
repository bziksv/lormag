<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Iblock;

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);

$arParams["DEPTH_LEVEL"] = intval($arParams["DEPTH_LEVEL"]);
if($arParams["DEPTH_LEVEL"] <= 0)
	$arParams["DEPTH_LEVEL"] = 4;

$arParams["COUNT_ELEMENTS"] = trim($arParams["COUNT_ELEMENTS"]);
if($arParams["COUNT_ELEMENTS"] != "Y")
	$arParams["COUNT_ELEMENTS"] = false;

$arResult["SECTIONS"] = array();

if($this->StartResultCache(false, $arParams["CACHE_GROUPS"] === "N" ? false: $USER->GetGroups())) {
	if(!CModule::IncludeModule("iblock")) {
		$this->AbortResultCache();
	} else {
		//SECTIONS//		
		$arOrder = array(
			"left_margin" => "asc"
		);
		$arFilter = array(	
			"ACTIVE" => "Y",
			"GLOBAL_ACTIVE" => "Y",	
			"<="."DEPTH_LEVEL" => $arParams["DEPTH_LEVEL"],
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"IBLOCK_ACTIVE" => "Y",
			"CNT_ACTIVE" => $arParams["COUNT_ELEMENTS"],
			"UF_HIDDEN_MENU" => false
		);
		$arSelect = array("ID", "IBLOCK_ID", "NAME", "PICTURE", "DEPTH_LEVEL", "SECTION_PAGE_URL", "UF_ICON");
		$rsSections = CIBlockSection::GetList($arOrder, $arFilter, $arParams["COUNT_ELEMENTS"], $arSelect);
		while($arSection = $rsSections->GetNext()) {	
			Iblock\Component\Tools::getFieldImageData(
				$arSection,
				array("PICTURE"),
				Iblock\Component\Tools::IPROPERTY_ENTITY_SECTION,
				"IPROPERTY_VALUES"
			);
			$arResult["SECTIONS"][] = array(		
				"DEPTH_LEVEL" => $arSection["DEPTH_LEVEL"],
				"~NAME" => $arSection["~NAME"],
				"~SECTION_PAGE_URL" => $arSection["~SECTION_PAGE_URL"],
				"PICTURE" => $arSection["PICTURE"],
				"ICON" => $arSection["UF_ICON"],
				"ELEMENT_CNT" => $arSection["ELEMENT_CNT"]
			);			
		}
		$this->EndResultCache();
	}
}

//MENU_LINKS//
$aMenuLinksNew = array();
$menuIndex = 0;
$previousDepthLevel = 1;
foreach($arResult["SECTIONS"] as $arSection) {
	if($menuIndex > 0)
		$aMenuLinksNew[$menuIndex - 1][3]["IS_PARENT"] = $arSection["DEPTH_LEVEL"] > $previousDepthLevel;
	$previousDepthLevel = $arSection["DEPTH_LEVEL"];
	
	$aMenuLinksNew[$menuIndex++] = array(
		htmlspecialcharsbx($arSection["~NAME"]),
		$arSection["~SECTION_PAGE_URL"],
		array(),
		array(
			"FROM_IBLOCK" => true,
			"IS_PARENT" => false,
			"DEPTH_LEVEL" => $arSection["DEPTH_LEVEL"],
			"PICTURE" => $arSection["PICTURE"],
			"ICON" => $arSection["ICON"],
			"ELEMENT_CNT" => $arSection["ELEMENT_CNT"]
		)
	);
}
return $aMenuLinksNew;?>