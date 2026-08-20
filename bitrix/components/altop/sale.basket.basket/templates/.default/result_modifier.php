<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * Theme expects PROPERTY_ARTNUMBER, catalog stores article in CML2_ARTICLE.
 * Fill empty ARTNUMBER column from CML2_ARTICLE (and basket props fallback).
 */
if(empty($arResult["GRID"]["ROWS"]) || !is_array($arResult["GRID"]["ROWS"]))
	return;

$productIds = array();
foreach($arResult["GRID"]["ROWS"] as $row) {
	if(!empty($row["PROPERTY_ARTNUMBER_VALUE"]) || !empty($row["PROPERTY_CML2_ARTICLE_VALUE"]))
		continue;
	if(intval($row["PRODUCT_ID"]) > 0)
		$productIds[] = intval($row["PRODUCT_ID"]);
}
unset($row);

$articlesByProductId = array();
if(!empty($productIds) && \Bitrix\Main\Loader::includeModule("iblock")) {
	$rsElements = CIBlockElement::GetList(
		array(),
		array("ID" => array_unique($productIds)),
		false,
		false,
		array("ID", "IBLOCK_ID", "PROPERTY_ARTNUMBER", "PROPERTY_CML2_ARTICLE")
	);
	while($arElement = $rsElements->Fetch()) {
		$article = "";
		if(!empty($arElement["PROPERTY_ARTNUMBER_VALUE"]))
			$article = $arElement["PROPERTY_ARTNUMBER_VALUE"];
		elseif(!empty($arElement["PROPERTY_CML2_ARTICLE_VALUE"]))
			$article = $arElement["PROPERTY_CML2_ARTICLE_VALUE"];
		if($article !== "")
			$articlesByProductId[intval($arElement["ID"])] = $article;
	}
	unset($arElement, $rsElements);
}

foreach($arResult["GRID"]["ROWS"] as &$row) {
	if(!empty($row["PROPERTY_ARTNUMBER_VALUE"]))
		continue;

	if(!empty($row["PROPERTY_CML2_ARTICLE_VALUE"])) {
		$row["PROPERTY_ARTNUMBER_VALUE"] = $row["PROPERTY_CML2_ARTICLE_VALUE"];
		continue;
	}

	$productId = intval($row["PRODUCT_ID"]);
	if($productId > 0 && !empty($articlesByProductId[$productId])) {
		$row["PROPERTY_ARTNUMBER_VALUE"] = $articlesByProductId[$productId];
		continue;
	}

	if(!empty($row["PROPS"]) && is_array($row["PROPS"])) {
		foreach($row["PROPS"] as $prop) {
			$code = isset($prop["CODE"]) ? $prop["CODE"] : "";
			if(($code === "ARTNUMBER" || $code === "CML2_ARTICLE") && !empty($prop["VALUE"])) {
				$row["PROPERTY_ARTNUMBER_VALUE"] = $prop["VALUE"];
				break;
			}
		}
		unset($prop);
	}
}
unset($row, $articlesByProductId, $productIds);
