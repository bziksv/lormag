<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

global $APPLICATION;

$aMenuLinksExt = $APPLICATION->IncludeComponent("altop:menu.links.enext", "",
	array(		
		"IBLOCK_TYPE" => "content",
		"IBLOCK_ID" => "#ENEXT_PROMOTIONS_IBLOCK_ID#",
		"DEPTH_LEVEL" => "2",
		"COUNT_ELEMENTS" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_GROUPS" => "Y"
    ),
	false,
	array("HIDE_ICONS" => "Y")
);
$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);?>