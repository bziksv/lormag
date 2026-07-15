<?
	$APPLICATION->IncludeComponent(
	"bitrix:search.title", 
	"visual", 
	array(
		"SHOW_PREVIEW" => "Y",
		"PREVIEW_WIDTH" => "75",
		"PREVIEW_HEIGHT" => "75",
		"PAGE" => "/catalog/",
		"NUM_CATEGORIES" => "1",
		"TOP_COUNT" => "7",
		"ORDER" => "date",
		"USE_LANGUAGE_GUESS" => "N",
		"CHECK_DATES" => "Y",
		"CATEGORY_0_TITLE" => "Каталог",
		"CATEGORY_0" => array(
			0 => "iblock_catalog",
		),
		"CATEGORY_0_iblock_catalog" => array(
			0 => "26",
		),
		"SHOW_INPUT" => "Y",
		"INPUT_ID" => "title-search-input-m",
		"CONTAINER_ID" => "title-search-m",
		"PRICE_CODE" => array(
			0 => "BASE",
		),
		"PRICE_VAT_INCLUDE" => "N",
		"CONVERT_CURRENCY" => "Y",
		"CURRENCY_ID" => "RUB",
		"COMPONENT_TEMPLATE" => "visual",
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"CATEGORY_1_TITLE" => "Каталог",
		"CATEGORY_1" => array(
			0 => "iblock_catalog",
		),
		"CATEGORY_1_iblock_catalog" => array(
			0 => "all",
		),
		"CATEGORY_2_TITLE" => "Каталог",
		"CATEGORY_2" => array(
			0 => "iblock_catalog",
		),
		"CATEGORY_2_iblock_catalog" => array(
			0 => "all",
		),
		"CATEGORY_3_TITLE" => "Каталог",
		"CATEGORY_3" => array(
			0 => "iblock_catalog",
		),
		"CATEGORY_3_iblock_catalog" => array(
			0 => "all",
		),
		"CATEGORY_4_TITLE" => "Каталог",
		"CATEGORY_4" => array(
			0 => "iblock_catalog",
		),
		"CATEGORY_4_iblock_catalog" => array(
			0 => "all",
		)
	),
	false
);?>