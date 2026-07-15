<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

global $APPLICATION;

$aMenuLinksExt = $APPLICATION->IncludeComponent("altop:menu.links.enext", "",
	array(		
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => "#ENEXT_CATALOG_IBLOCK_ID#",
		"DEPTH_LEVEL" => "4",
		"COUNT_ELEMENTS" => "Y",		
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_GROUPS" => "Y"
    ),
	false,
	Array("HIDE_ICONS" => "Y")
);
$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);?>