<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

if(!Loader::IncludeModule("search"))
	return;

if(!isset($arParams["PAGE"]) || strlen($arParams["PAGE"]) <= 0)
	$arParams["PAGE"] = "#SITE_DIR#catalog/";

$arResult["CATEGORIES"] = array();

$query = ltrim($_POST["q"]);

if(!empty($query) && $_REQUEST["ajax_call"] === "y" && (!isset($_REQUEST["INPUT_ID"]) || $_REQUEST["INPUT_ID"] == $arParams["INPUT_ID"])) {
	CUtil::decodeURIComponent($query);	

	$arResult["alt_query"] = "";
	if($arParams["USE_LANGUAGE_GUESS"] !== "N") {
		$arLang = CSearchLanguage::GuessLanguage($query);
		if(is_array($arLang) && $arLang["from"] != $arLang["to"])
			$arResult["alt_query"] = CSearchLanguage::ConvertKeyboardLayout($query, $arLang["from"], $arLang["to"]);
	}

	$arResult["query"] = $query;
	$arResult["phrase"] = stemming_split($query, LANGUAGE_ID);

	$arParams["NUM_CATEGORIES"] = intval($arParams["NUM_CATEGORIES"]);
	if($arParams["NUM_CATEGORIES"] <= 0)
		$arParams["NUM_CATEGORIES"] = 1;

	$arParams["TOP_COUNT"] = intval($arParams["TOP_COUNT"]);
	if($arParams["TOP_COUNT"] <= 0)
		$arParams["TOP_COUNT"] = 5;

	if($arParams["ORDER"] == "date")
		$aSort = array("DATE_CHANGE" => "DESC", "CUSTOM_RANK" => "DESC", "RANK" => "DESC");
	elseif($arParams["ORDER"] == "title")
		$aSort = array("TITLE_RANK" => "DESC", "TITLE" => "DESC", "CUSTOM_RANK" => "DESC", "RANK" => "DESC", "DATE_CHANGE" => "DESC");
	else
		$aSort = array("CUSTOM_RANK" => "DESC", "RANK" => "DESC", "DATE_CHANGE" => "DESC");
	
	for($i = 0; $i < $arParams["NUM_CATEGORIES"]; $i++) {
		$bCustom = true;
		if(is_array($arParams["CATEGORY_".$i])) {
			foreach($arParams["CATEGORY_".$i] as $categoryCode) {
				if((strpos($categoryCode, 'custom_') !== 0)) {
					$bCustom = false;
					break;
				}
			}
		} else {
			$bCustom = (strpos($arParams["CATEGORY_".$i], 'custom_') === 0);
		}

		if($bCustom)
			continue;

		$category_title = trim($arParams["CATEGORY_".$i."_TITLE"]);
		if(empty($category_title)) {
			if(is_array($arParams["CATEGORY_".$i]))
				$category_title = implode(", ", $arParams["CATEGORY_".$i]);
			else
				$category_title = trim($arParams["CATEGORY_".$i]);
		}
		if(empty($category_title))
			continue;

		$arResult["CATEGORIES"][$i] = array(
			"TITLE" => htmlspecialcharsbx($category_title),
			"ITEMS" => array()
		);
		
		$exFILTER = array(
			0 => CSearchParameters::ConvertParamsToFilter($arParams, "CATEGORY_".$i),
		);
		$exFILTER[0]["LOGIC"] = "OR";

		if($arParams["CHECK_DATES"] === "Y")
			$exFILTER["CHECK_DATES"] = "Y";
		
		$j = 0;
		$obSearch = new CSearch;
			
		$obSearch->Search(array("SITE_ID" => SITE_ID, "QUERY" => $arResult["alt_query"] ? $arResult["alt_query"] : $arResult["query"]), $aSort, $exFILTER);

		while($ar = $obSearch->Fetch()) {
			$j++;
			if($j > $arParams["TOP_COUNT"]) {
				break;
			} else {
				$arResult["CATEGORIES"][$i]["ITEMS"][] = array(
					"NAME" => $ar["TITLE"],
					"URL" => htmlspecialcharsbx($ar["URL"]),
					"MODULE_ID" => $ar["MODULE_ID"],
					"PARAM1" => $ar["PARAM1"],
					"PARAM2" => $ar["PARAM2"],
					"ITEM_ID" => $ar["ITEM_ID"],
				);
			}
		}
		
		if(!$j)
			unset($arResult["CATEGORIES"][$i]);
	}
	
	if(!empty($arResult["CATEGORIES"])) {
		$arResult["CATEGORIES"]["all"] = array(
			"TITLE" => "",
			"ITEMS" => array()
		);

		$url = CHTTP::urlAddParams(
			str_replace("#SITE_DIR#", SITE_DIR, $arParams["PAGE"]),
			array(
				"q" => $arResult["alt_query"] ? $arResult["alt_query"] : $arResult["query"]
			),
			array("encode" => true)
		);
		$arResult["CATEGORIES"]["all"]["ITEMS"][] = array(
			"NAME" => GetMessage("CC_BST_ALL_RESULTS"),
			"URL" => $url
		);		
	}
}

$arResult["FORM_ACTION"] = htmlspecialcharsbx(str_replace("#SITE_DIR#", SITE_DIR, $arParams["PAGE"]));

if($_REQUEST["ajax_call"] === "y" && (!isset($_REQUEST["INPUT_ID"]) || $_REQUEST["INPUT_ID"] == $arParams["INPUT_ID"])) {
	$APPLICATION->RestartBuffer();

	if(!empty($query))
		$this->IncludeComponentTemplate('ajax');
	
	CMain::FinalActions();
	die();
} else {
	$APPLICATION->AddHeadScript($this->GetPath().'/script.js');
	CUtil::InitJSCore(array('ajax'));
	$this->IncludeComponentTemplate();
}