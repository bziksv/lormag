<?$APPLICATION->IncludeComponent("bitrix:catalog.compare.list", "",
	array(
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => "#ENEXT_CATALOG_IBLOCK_ID#",
		"NAME" => "CATALOG_COMPARE_LIST",
		"COMPARE_URL" => "#SITE_DIR#catalog/compare/"
	),
	false
);?>