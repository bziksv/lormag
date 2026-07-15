<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

if(!Loader::includeModule("iblock"))
	return;

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
if($arParams["IBLOCK_ID"] <= 0)
	return;

global $USER;
$arSettings = CEnext::GetFrontParametrsValues(SITE_ID);
$arParams["USE_CAPTCHA"] = !$USER->IsAuthorized() && $arSettings["FORMS_USE_CAPTCHA"] == "Y" ? true : false;

$arParams["USER_CONSENT"] = $arSettings["FORMS_USER_CONSENT"] == "Y" ? true : false;
$arParams["USER_CONSENT_ID"] = $arSettings["FORMS_USER_CONSENT_ID"];
$arParams["USER_CONSENT_IS_CHECKED"] = $arSettings["FORMS_USER_CONSENT_IS_CHECKED"];
$arParams["USER_CONSENT_IS_LOADED"] = $arSettings["FORMS_USER_CONSENT_IS_LOADED"];

$arParams["DEFAULT_COUNTRY"] = strtolower($arSettings["FORMS_DEFAULT_COUNTRY"]);
$arParams["PHONE_MASK"] = $arSettings["FORMS_PHONE_MASK"] == "Y" ? true : false;

if($this->StartResultCache()) {
	//IBLOCK//
	$rsIblock = CIBlock::GetList(array("SORT" => "ASC"), array("ID" => $arParams["IBLOCK_ID"], "ACTIVE" => "Y"));
	if(!$arIblock = $rsIblock->Fetch()) {
		$this->abortResultCache();
		return;
	}
	
	$arResult["IBLOCK"]["ID"] = $arIblock["ID"];
	$arResult["IBLOCK"]["CODE"] = $arIblock["CODE"];
	$arResult["IBLOCK"]["IBLOCK_TYPE_ID"] = $arIblock["IBLOCK_TYPE_ID"];
	$arResult["IBLOCK"]["NAME"] = $arIblock["NAME"];
	$arResult["IBLOCK"]["DESCRIPTION"] = $arIblock["DESCRIPTION"];

	//IBLOCK_PROPS//
	$rsProps = CIBlock::GetProperties($arIblock["ID"], array("SORT" => "ASC", "NAME" => "ASC"), array("ACTIVE" => "Y", "PROPERTY_TYPE" => "S"));
	while($arProp = $rsProps->fetch()) {
		$arResult["IBLOCK"]["PROPERTIES"][] = $arProp;
	}
	unset($arProp, $rsProps);

	$rsProps = CIBlock::GetProperties($arIblock["ID"], array("SORT" => "ASC", "NAME" => "ASC"), array("ACTIVE" => "Y", "USER_TYPE" => "EList"));
	while($arProp = $rsProps->fetch()) {
		$arResult["IBLOCK"]["PROPERTIES"][] = $arProp;
	}
	unset($arProp, $rsProps, $arIblock);
	
	if(!isset($arResult["IBLOCK"]["PROPERTIES"]) || empty($arResult["IBLOCK"]["PROPERTIES"])) {
		$this->abortResultCache();
		return;
	}

	$arResult["IBLOCK"]["STRING"] = strtr(base64_encode(serialize($arResult["IBLOCK"])), "+/=", "-_,");
	
	$this->IncludeComponentTemplate();
}