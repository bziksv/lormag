<?global $arSettings;
$template = "";
if($arSettings["CATALOG_MENU"]["VALUE"] == "INTERFACE-2-0-1")
	$template = "catalog_menu_interface_2_0_1";
elseif($arSettings["CATALOG_MENU"]["VALUE"] == "INTERFACE-2-0-2" || $arSettings["CATALOG_MENU"]["VALUE"] == "INTERFACE-2-0-3")
	$template = "catalog_menu_interface_2_0_2_3";
elseif($arSettings["CATALOG_MENU"]["VALUE"] == "OPTION-1")
	$template = "slide_menu_option_1";
elseif($arSettings["CATALOG_MENU"]["VALUE"] == "OPTION-2")
	$template = "slide_menu_option_2";
elseif($arSettings["CATALOG_MENU"]["VALUE"] == "OPTION-3" || $arSettings["CATALOG_MENU"]["VALUE"] == "OPTION-4" || $arSettings["CATALOG_MENU"]["VALUE"] == "OPTION-5")
	$template = "catalog_menu_option_3_4_5";
elseif($arSettings["CATALOG_MENU"]["VALUE"] == "OPTION-6")
	$template = "catalog_menu_option_6";?>
<?$APPLICATION->IncludeComponent("bitrix:menu", $template, 
	array(
		"ROOT_MENU_TYPE" => "left",
		"MENU_CACHE_TYPE" => "A",
		"MENU_CACHE_TIME" => "36000000",
		"MENU_CACHE_USE_GROUPS" => "Y",
		"MENU_CACHE_GET_VARS" => array(),
		"MAX_LEVEL" => "4",
		"CHILD_MENU_TYPE" => "left",
		"USE_EXT" => "Y",
		"ALLOW_MULTI_SELECT" => "N",
		"CACHE_SELECTED_ITEMS" => "N"
	),
	false
);?>