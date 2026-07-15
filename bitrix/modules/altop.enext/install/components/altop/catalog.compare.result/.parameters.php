<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
	Bitrix\Currency,
	Bitrix\Iblock;

if(!Loader::includeModule("iblock"))
	return;

$boolCatalog = Loader::includeModule("catalog");

$iblockExists = !empty($arCurrentValues["IBLOCK_ID"]) && (int)$arCurrentValues["IBLOCK_ID"] > 0;

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock = array();
$iblockFilter = !empty($arCurrentValues["IBLOCK_TYPE"]) ? array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE" => "Y")	: array("ACTIVE" => "Y");
$rsIBlock = CIBlock::GetList(array("SORT" => "ASC"), $iblockFilter);
while($arr = $rsIBlock->Fetch()) {
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}
unset($arr, $rsIBlock, $iblockFilter);

$arProperty = array();
$arProperty_N = array();
if($iblockExists) {
	$propertyIterator = Iblock\PropertyTable::getList(array(
		"select" => array("ID", "IBLOCK_ID", "NAME", "CODE", "PROPERTY_TYPE"),
		"filter" => array("=IBLOCK_ID" => $arCurrentValues["IBLOCK_ID"], "=ACTIVE" => "Y", "!=PROPERTY_TYPE" => Iblock\PropertyTable::TYPE_FILE),
		"order" => array("SORT" => "ASC", "NAME" => "ASC")
	));
	while($property = $propertyIterator->fetch()) {
		$propertyCode = (string)$property["CODE"];
		if($propertyCode == "")
			$propertyCode = $property["ID"];
		$propertyName = "[".$propertyCode."] ".$property["NAME"];

		$arProperty[$propertyCode] = $propertyName;
		if($property["PROPERTY_TYPE"] == Iblock\PropertyTable::TYPE_NUMBER)
			$arProperty_N[$propertyCode] = $propertyName;
	}
	unset($propertyCode, $propertyName, $property, $propertyIterator);
}

$arOffers = CIBlockPriceTools::GetOffersIBlock($arCurrentValues["IBLOCK_ID"]);
$OFFERS_IBLOCK_ID = is_array($arOffers) ? $arOffers["OFFERS_IBLOCK_ID"] : 0;
$arProperty_Offers = array();
if($OFFERS_IBLOCK_ID) {
	$propertyIterator = Iblock\PropertyTable::getList(array(
		"select" => array("ID", "IBLOCK_ID", "NAME", "CODE", "PROPERTY_TYPE"),
		"filter" => array("=IBLOCK_ID" => $OFFERS_IBLOCK_ID, "=ACTIVE" => "Y", "!=PROPERTY_TYPE" => Iblock\PropertyTable::TYPE_FILE),
		"order" => array("SORT" => "ASC", "NAME" => "ASC")
	));
	while($property = $propertyIterator->fetch()) {
		$propertyCode = (string)$property["CODE"];
		if($propertyCode == "")
			$propertyCode = $property["ID"];
		$propertyName = "[".$propertyCode."] ".$property["NAME"];

		$arProperty_Offers[$propertyCode] = $propertyName;
	}
	unset($propertyCode, $propertyName, $property, $propertyIterator);
}

$arReviewsIBlock = array();
$rsIBlock = CIBlock::GetList(array("sort" => "asc"), array("TYPE" => $arCurrentValues["REVIEWS_IBLOCK_TYPE"], "ACTIVE" => "Y"));
while($arr = $rsIBlock->Fetch()) {
	$arReviewsIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}
unset($arr, $rsIBlock);

$arSort = CIBlockParameters::GetElementSortFields(
	array("SHOWS", "SORT", "TIMESTAMP_X", "NAME", "ID", "ACTIVE_FROM", "ACTIVE_TO"),
	array("KEY_LOWERCASE" => "Y")
);

$arPrice = array();
if($boolCatalog) {
	$arSort = array_merge($arSort, CCatalogIBlockParameters::GetCatalogSortFields());
	$arPrice = CCatalogIBlockParameters::getPriceTypesList();
} else {
	$arPrice = $arProperty_N;
}

$arAscDesc = array(
	"asc" => GetMessage("CCR_SORT_ASC"),
	"desc" => GetMessage("CCR_SORT_DESC"),
);

$arComponentParameters = array(
	"GROUPS" => array(
		"ACTION_SETTINGS" => array(
			"NAME" => GetMessage("CCR_ACTIONS")
		),
		"PRICES" => array(
			"NAME" => GetMessage("CCR_PRICES"),
		),
		"BASKET" => array(
			"NAME" => GetMessage("CCR_BASKET"),
		),
		"REVIEWS" => array(
			"NAME" => GetMessage("CCR_REVIEWS"),
		)
	),
	"PARAMETERS" => array(
		"NAME" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CCR_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => "CATALOG_COMPARE_LIST"
		),
		"IBLOCK_TYPE" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CCR_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y"
		),
		"IBLOCK_ID" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CCR_IBLOCK_ID"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y"
		),
		"FIELD_CODE" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CCR_FIELD_CODE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => array(
				"ID" => GetMessage("CCR_FIELD_CODE_ID"),
				"NAME" => GetMessage("CCR_FIELD_CODE_NAME"),
				"PREVIEW_TEXT" => GetMessage("CCR_FIELD_CODE_PREVIEW_TEXT"),
				"PREVIEW_PICTURE" => GetMessage("CCR_FIELD_CODE_PREVIEW_PICTURE"),
				"DETAIL_TEXT" => GetMessage("CCR_FIELD_CODE_DETAIL_TEXT"),
				"DETAIL_PICTURE" => GetMessage("CCR_FIELD_CODE_DETAIL_PICTURE"),
				"DATE_ACTIVE_FROM" => GetMessage("CCR_FIELD_CODE_DATE_ACTIVE_FROM"),
				"DATE_ACTIVE_TO" => GetMessage("CCR_FIELD_CODE_DATE_ACTIVE_TO"),
			)
		),
		"PROPERTY_CODE" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CCR_PROPERTY_CODE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arProperty,
			"ADDITIONAL_VALUES" => "Y"
		),
		"OFFERS_FIELD_CODE" => CIBlockParameters::GetFieldCode(GetMessage("CCR_OFFERS_FIELD_CODE"), "DATA_SOURCE"),
		"OFFERS_PROPERTY_CODE" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CCR_OFFERS_PROPERTY_CODE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arProperty_Offers,
			"ADDITIONAL_VALUES" => "Y"
		),
		"ELEMENT_SORT_FIELD" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CCR_ELEMENT_SORT_FIELD"),
			"TYPE" => "LIST",
			"VALUES" => $arSort,
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => "sort"
		),
		"ELEMENT_SORT_ORDER" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CCR_ELEMENT_SORT_ORDER"),
			"TYPE" => "LIST",
			"VALUES" => $arAscDesc,
			"DEFAULT" => "asc",
			"ADDITIONAL_VALUES" => "Y"
		),
		"DETAIL_URL" => CIBlockParameters::GetPathTemplateParam(
			"DETAIL",
			"DETAIL_URL",
			GetMessage("CCR_DETAIL_URL"),
			"",
			"ADDITIONAL_SETTINGS"
		),
		"BASKET_URL" => array(
			"PARENT" => "BASKET",
			"NAME" => GetMessage("CCR_BASKET_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "/personal/cart/"
		),
		"ACTION_VARIABLE" => array(
			"PARENT" => "ACTION_SETTINGS",
			"NAME" => GetMessage("CCR_ACTION_VARIABLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "action"
		),
		"PRODUCT_ID_VARIABLE" => array(
			"PARENT" => "ACTION_SETTINGS",
			"NAME" => GetMessage("CCR_PRODUCT_ID_VARIABLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "id"
		),
		"SECTION_ID_VARIABLE" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("CCR_SECTION_ID_VARIABLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "SECTION_ID"
		),
		"PRICE_CODE" => array(
			"PARENT" => "PRICES",
			"NAME" => GetMessage("CCR_PRICE_CODE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arPrice
		),
		"SHOW_PRICE_COUNT" => array(
			"PARENT" => "PRICES",
			"NAME" => GetMessage("CCR_SHOW_PRICE_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "1"
		),
		"PRICE_VAT_INCLUDE" => array(
			"PARENT" => "PRICES",
			"NAME" => GetMessage("CCR_PRICE_VAT_INCLUDE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"
		),
		"USE_REVIEW" => array(
			"PARENT" => "REVIEWS",
			"NAME" => GetMessage("CCR_USE_REVIEW"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"REFRESH" => "Y"
		)
	)
);

if($boolCatalog) {
	$arComponentParameters["PARAMETERS"]["CONVERT_CURRENCY"] = array(
		"PARENT" => "PRICES",
		"NAME" => GetMessage("CCR_CONVERT_CURRENCY"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => "Y"
	);

	if(isset($arCurrentValues["CONVERT_CURRENCY"]) && $arCurrentValues["CONVERT_CURRENCY"] == "Y") {
		$arComponentParameters["PARAMETERS"]["CURRENCY_ID"] = array(
			"PARENT" => "PRICES",
			"NAME" => GetMessage("CCR_CURRENCY_ID"),
			"TYPE" => "LIST",
			"VALUES" => Currency\CurrencyManager::getCurrencyList(),
			"DEFAULT" => Currency\CurrencyManager::getBaseCurrency(),
			"ADDITIONAL_VALUES" => "Y"
		);
	}
}

if(isset($arCurrentValues["USE_REVIEW"]) && $arCurrentValues["USE_REVIEW"] == "Y") {	
	$arComponentParameters["PARAMETERS"]["REVIEWS_IBLOCK_TYPE"] = array(
		"PARENT" => "REVIEWS",
		"NAME" => GetMessage("CCR_REVIEWS_IBLOCK_TYPE"),
		"TYPE" => "LIST",		
		"REFRESH" => "Y",
		"VALUES" => $arIBlockType
	);
	$arComponentParameters["PARAMETERS"]["REVIEWS_IBLOCK_ID"] = array(
		"PARENT" => "REVIEWS",
		"NAME" => GetMessage("CCR_REVIEWS_IBLOCK_ID"),
		"TYPE" => "LIST",
		"REFRESH" => "Y",		
		"VALUES" => $arReviewsIBlock,
		"ADDITIONAL_VALUES" => "Y"
	);
}

if(!$OFFERS_IBLOCK_ID) {
	unset($arComponentParameters["PARAMETERS"]["OFFERS_FIELD_CODE"]);
	unset($arComponentParameters["PARAMETERS"]["OFFERS_PROPERTY_CODE"]);
}