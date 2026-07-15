<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

if(!Loader::includeModule("iblock"))
	return;

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
if($arParams["IBLOCK_ID"] <= 0)
	return;

$arParams["ELEMENT_ID"] = intval($arParams["ELEMENT_ID"]);
$arParams["RATING_ID"] = intval($arParams["RATING_ID"]);

global $USER;
$arParams["IS_AUTHORIZED"] = $USER->IsAuthorized() ? true : false;
$arParams["IS_ADMIN"] = $USER->IsAdmin() ? true : false;

$arSettings = CEnext::GetFrontParametrsValues(SITE_ID);
$arParams["USE_CAPTCHA"] = !$arParams["IS_AUTHORIZED"] && $arSettings["FORMS_USE_CAPTCHA"] == "Y" ? true : false;

$arParams["PREMODERATION"] = !$arParams["IS_ADMIN"] && $arSettings["REVIEWS_PREMODERATION"] == "Y" ? true : false;
$arParams["CAN_ADD"] = !$arParams["IS_AUTHORIZED"] && $arSettings["REVIEWS_ADD_AUTHORIZED"] == "Y" ? false : true;

$arParams["USER_CONSENT"] = $arSettings["FORMS_USER_CONSENT"] == "Y" ? true : false;
$arParams["USER_CONSENT_ID"] = $arSettings["FORMS_USER_CONSENT_ID"];
$arParams["USER_CONSENT_IS_CHECKED"] = $arSettings["FORMS_USER_CONSENT_IS_CHECKED"];
$arParams["USER_CONSENT_IS_LOADED"] = $arSettings["FORMS_USER_CONSENT_IS_LOADED"];

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
	
	//IBLOCK_PROPS//
	$rsProps = CIBlock::GetProperties($arIblock["ID"], array("SORT" => "ASC", "NAME" => "ASC"), array("ACTIVE" => "Y"));
	while($arProp = $rsProps->fetch()) {
		if($arProp["PROPERTY_TYPE"] == "L") {
			$rsPropsEnum = CIBlockProperty::GetPropertyEnum($arProp["ID"]);
			while($arPropEnum = $rsPropsEnum->GetNext()) {
				$arProp["VALUES"][$arPropEnum["ID"]] = $arPropEnum;
			}
			unset($arPropEnum, $rsPropsEnum);
		} elseif($arProp["PROPERTY_TYPE"] == "S" && empty($arProp["USER_TYPE"]) && $arProp["CODE"] != "CITY") {
			$arProp["VALUE"] = $arProp["CODE"] == "NAME" && $arParams["IS_AUTHORIZED"] ? $USER->GetFirstName() : "";
		}

		$arResult["IBLOCK"]["PROPERTIES"][] = $arProp;
	}
	unset($arProp, $rsProps);
	
	if(!isset($arResult["IBLOCK"]["PROPERTIES"]) || empty($arResult["IBLOCK"]["PROPERTIES"])) {
		$this->abortResultCache();
		return;
	}
	
	$arResult["IBLOCK"]["STRING"] = strtr(base64_encode(serialize($arResult["IBLOCK"])), "+/=", "-_,");

	//ELEMENT//
	if($arParams["ELEMENT_ID"] > 0) {
		$arElement = CIBlockElement::GetList(array(), array("ID" => $arParams["ELEMENT_ID"]), false, false, array("ID", "IBLOCK_ID", "NAME", "PREVIEW_PICTURE"))->Fetch();

		if(empty($arElement)) {
			$this->abortResultCache();
			return;
		}
		
		$arResult["ELEMENT"]["ID"] = $arElement["ID"];
		$arResult["ELEMENT"]["NAME"] = $arElement["NAME"];
		
		if($arElement["PREVIEW_PICTURE"] > 0)
			$arResult["ELEMENT"]["PREVIEW_PICTURE"] = CFile::GetFileArray($arElement["PREVIEW_PICTURE"]);
	}
	
	$this->IncludeComponentTemplate();
}