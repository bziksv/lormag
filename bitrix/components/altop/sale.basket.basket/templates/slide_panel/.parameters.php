<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
	Bitrix\Iblock,
	Bitrix\Catalog;

if(!Loader::includeModule("iblock"))
	return;

$catalogIncluded = Loader::includeModule("catalog");

if($catalogIncluded) {
	$arSKU = false;
	$boolSKU = false;
	$arOfferProps = array();
	$arSkuData = array();

	//get iblock props from all catalog iblocks including sku iblocks
	$arSkuIblockIDs = array();
	$iterator = Catalog\CatalogIblockTable::getList(array(
		"select" => array("IBLOCK_ID", "PRODUCT_IBLOCK_ID", "SKU_PROPERTY_ID"),
		"filter" => array("!=PRODUCT_IBLOCK_ID" => 0)
	));
	while($row = $iterator->fetch()) {
		$boolSKU = true;
		$arSkuIblockIDs[] = $row["IBLOCK_ID"];
		$arSkuData[$row["IBLOCK_ID"]] = $row;
	}
	unset($row, $iterator);

	//iblock props
	$arProps = array();
	foreach($arSkuIblockIDs as $iblockID) {
		$dbProps = CIBlockProperty::GetList(
			array(
				"SORT"=>"ASC",
				"NAME"=>"ASC"
			),
			array(
				"IBLOCK_ID" => $iblockID,
				"ACTIVE" => "Y",
				"CHECK_PERMISSIONS" => "N",
			)
		);
		while($arProp = $dbProps->GetNext()) {
			if($arProp["ID"] == $arSkuData[$iblockID]["SKU_PROPERTY_ID"])
				continue;

			if($arProp["XML_ID"] == "CML2_LINK")
				continue;

			$strPropName = "[".$arProp["ID"]."] ".("" != $arProp["CODE"] ? "[".$arProp["CODE"]."]" : "")." ".$arProp["~NAME"];

			if($arProp["PROPERTY_TYPE"] != "F") {
				$arOfferProps[$arProp["CODE"]] = $strPropName;
			}
		}

		if(!empty($arOfferProps) && is_array($arOfferProps)) {
			$arTemplateParameters["OFFERS_PROPS"] = array(
				"PARENT" => "OFFERS_PROPS",
				"NAME" => GetMessage("CP_SBB_TPL_PROPERTIES_RECALCULATE_BASKET"),
				"TYPE" => "LIST",
				"MULTIPLE" => "Y",
				"SIZE" => "7",
				"ADDITIONAL_VALUES" => "N",
				"REFRESH" => "N",
				"DEFAULT" => "-",
				"VALUES" => $arOfferProps
			);
		}
	}
}