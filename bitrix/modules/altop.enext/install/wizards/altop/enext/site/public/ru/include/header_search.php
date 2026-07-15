<?$APPLICATION->IncludeComponent("altop:search.title", "visual", 
	array(
		"PAGE" => "#SITE_DIR#catalog/",
		"NUM_CATEGORIES" => "1",
		"TOP_COUNT" => "5",
		"ORDER" => "date",
		"USE_LANGUAGE_GUESS" => "N",
		"CHECK_DATES" => "Y",
		"CATEGORY_0_TITLE" => "Каталог",
		"CATEGORY_0" => array(
			0 => "iblock_catalog",
		),
		"CATEGORY_0_iblock_catalog" => array(
			0 => "all",
		),
		"SHOW_INPUT" => "Y",
		"INPUT_ID" => "title-search-input",
		"CONTAINER_ID" => "title-search",
		"PRICE_CODE" => array(
			0 => "BASE",
		),
		"PRICE_VAT_INCLUDE" => "Y",
		"CONVERT_CURRENCY" => "N"
	),
	false
);?>