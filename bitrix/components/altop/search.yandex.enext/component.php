<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

$arSettings = CEnext::GetFrontParametrsValues(SITE_ID);

$arParams["API_KEY"] = trim($arSettings["MAIN_SEARCH_YANDEX_API_KEY"]);
if(empty($arParams["API_KEY"])) {
	ShowError(Loc::getMessage("SEARCH_YANDEX_COMPONENT_EMPTY_API_KEY"));
	return;
}

$arParams["SEARCH_ID"] = trim($arSettings["MAIN_SEARCH_YANDEX_SEARCH_ID"]);
if(empty($arParams["SEARCH_ID"])) {
	ShowError(Loc::getMessage("SEARCH_YANDEX_COMPONENT_EMPTY_SEARCH_ID"));
	return;
}

$arParams["SEARCH_URL"] = "https://catalogapi.site.yandex.net/v1.0?apikey=".$arParams["API_KEY"]."&searchid=".$arParams["SEARCH_ID"];

$arParams["SHOW_SECTIONS"] = $arSettings["MAIN_SEARCH_YANDEX_SHOW_SECTIONS"] == "Y" ? true : false;
$arParams["SHOW_CHECKBOX_AVAILABLE"] = $arSettings["MAIN_SEARCH_YANDEX_SHOW_CHECKBOX_AVAILABLE"] == "Y" ? true : false;

$arResult["CURRENCIES"] = array();

if(Bitrix\Main\Loader::includeModule("currency")) {
	$rsCurrency = Bitrix\Currency\CurrencyTable::getList(array(
		"select" => array("CURRENCY")
	));
	while($arCurrency = $rsCurrency->fetch()) {
		$currencyFormat = CCurrencyLang::GetFormatDescription($arCurrency["CURRENCY"]);
		$arResult["CURRENCIES"][] = array(
			"CURRENCY" => $arCurrency["CURRENCY"],
			"FORMAT" => array(
				"FORMAT_STRING" => $currencyFormat["FORMAT_STRING"],
				"DEC_POINT" => $currencyFormat["DEC_POINT"],
				"THOUSANDS_SEP" => $currencyFormat["THOUSANDS_SEP"],
				"DECIMALS" => $currencyFormat["DECIMALS"],
				"THOUSANDS_VARIANT" => $currencyFormat["THOUSANDS_VARIANT"],
				"HIDE_ZERO" => $currencyFormat["HIDE_ZERO"]
			)
		);
	}
	unset($currencyFormat, $arCurrency, $rsCurrencies);
}

$this->IncludeComponentTemplate();