<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Loader,
	Bitrix\Main\Application;

$moduleClass = "CEnext";
$moduleID = "altop.enext";

if(!Loader::IncludeModule($moduleID))
	return;

$docRoot = Application::getDocumentRoot();

global $USER;

$arResult = array();
$arFrontParametrs = $moduleClass::GetFrontParametrsValues(SITE_ID);
foreach($moduleClass::$arParametrsList as $blockCode => $arBlock) {
	foreach($arBlock["OPTIONS"] as $optionCode => $arOption) {
		$arResult[$optionCode] = $arOption;
		$arResult[$optionCode]["VALUE"] = $arFrontParametrs[$optionCode];		
		if($arResult[$optionCode]["LIST"]) {
			foreach($arResult[$optionCode]["LIST"] as $variantCode => $variantTitle) {
				if(!is_array($variantTitle)) {
					$arResult[$optionCode]["LIST"][$variantCode] = array("TITLE" => $variantTitle);
				}				
				if($arResult[$optionCode]["TYPE"] == "multiselectbox") {
					if(is_array($arResult[$optionCode]["VALUE"]) && in_array($variantCode, $arResult[$optionCode]["VALUE"])) {
						$arResult[$optionCode]["LIST"][$variantCode]["CURRENT"] = "Y";
					}
				} else {
					if($arResult[$optionCode]["VALUE"] == $variantCode) {
						$arResult[$optionCode]["LIST"][$variantCode]["CURRENT"] = "Y";
					}
				}
			}
		}
	}
}

//ICON_FONTS//
if($arResult["ICON_FONTS_UI_NEXT"]["VALUE"] == "MIN") {
	$APPLICATION->AddHeadString("<link rel='preload' href='".SITE_TEMPLATE_PATH."/fonts/uinext2020min.woff' as='font' type='font/woff' crossorigin />", true);
	$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/uinext2020min.min.css");
} else {
	$APPLICATION->AddHeadString("<link rel='preload' href='".SITE_TEMPLATE_PATH."/fonts/uinext2020.woff' as='font' type='font/woff' crossorigin />", true);
	$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/uinext2020.min.css");
}

if($arResult["ICON_FONTS_FONT_AWESOME"]["VALUE"] != "DISABLE") {
	if($arResult["ICON_FONTS_FONT_AWESOME"]["VALUE"] == "MIN") {
		$APPLICATION->AddHeadString("<link rel='preload' href='".SITE_TEMPLATE_PATH."/fonts/famin.woff' as='font' type='font/woff' crossorigin />", true);
		$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/famin.min.css");
	} else {
		$APPLICATION->AddHeadString("<link rel='preload' href='".SITE_TEMPLATE_PATH."/fonts/fontawesome-webfont.woff' as='font' type='font/woff' crossorigin />", true);
		$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/font-awesome.min.css");
	}
}

if(!empty($arResult["ICON_FONTS_ELASTO_FONT_NEXT"]["VALUE"])) {
	foreach($arResult["ICON_FONTS_ELASTO_FONT_NEXT"]["VALUE"] as $arFont) {
		$APPLICATION->AddHeadString("<link rel='preload' href='".SITE_TEMPLATE_PATH."/fonts/efn/".strtolower($arFont).".woff' as='font' type='font/woff' crossorigin />", true);
		$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/efn/".strtolower($arFont).".min.css");
	}
	unset($arFont);
}

if($arResult["ICON_FONTS_ELASTO_FONT"]["VALUE"] == "Y") {
	$APPLICATION->AddHeadString("<link rel='preload' href='".SITE_TEMPLATE_PATH."/fonts/ELASTO-FONT.woff' as='font' type='font/woff' crossorigin />", true);
	$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/elasto-font.min.css");
}

if(!empty($arResult["ICON_FONTS_CUSTOM"]["VALUE"])) {
	$APPLICATION->SetAdditionalCSS($arResult["ICON_FONTS_CUSTOM"]["VALUE"]);
}

//APPLE_TOUCH_ICON_180_180//
$appleTouchIcon180 = CFile::GetFileArray($arResult["APPLE_TOUCH_ICON_180_180"]["VALUE"]);
if(!empty($appleTouchIcon180)) {
	$APPLICATION->AddHeadString("<link rel='icon' type='image/png' href='".$appleTouchIcon180["SRC"]."' />", true);
	$APPLICATION->AddHeadString("<link rel='apple-touch-icon' href='".$appleTouchIcon180["SRC"]."' />", true);
	$APPLICATION->AddHeadString("<link rel='apple-touch-icon' sizes='180x180' href='".$appleTouchIcon180["SRC"]."' />", true);
}
unset($appleTouchIcon180);

//COLOR_SCHEME//
$colorScheme = $arResult["COLOR_SCHEME"]["VALUE"];
if($colorScheme != "VIOLET") {
	$file = SITE_TEMPLATE_PATH."/schemes/".$colorScheme.($colorScheme == "CUSTOM" ? "_".SITE_ID : "")."/colors.min.css";
	if(!file_exists($docRoot.$file))
		$moduleClass::GenerateColorScheme(SITE_ID);
	$APPLICATION->SetAdditionalCSS($file, true);
	unset($file);
}

//THEME_COLOR//
if($colorScheme != "CUSTOM")
	$themeColor = $arResult["COLOR_SCHEME"]["LIST"][$colorScheme]["COLOR"];
else
	$themeColor = $arResult["COLOR_SCHEME_CUSTOM"]["VALUE"];
$APPLICATION->AddHeadString("<meta name='theme-color' content='".$themeColor."' />", true);
$APPLICATION->AddHeadString("<meta name='msapplication-navbutton-color' content='".$themeColor."' />", true);
$APPLICATION->AddHeadString("<meta name='apple-mobile-web-app-status-bar-style' content='".$themeColor."' />", true);
unset($themeColor, $colorScheme);

//CUSTOM_CSS//
$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/custom.css", true);

//CUSTOM_JS//
$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/custom.js");

//CATALOG_MENU//
$APPLICATION->SetPageProperty(
	"catalogMenu",
	"slide-menu-".strtolower($arResult["CATALOG_MENU"]["VALUE"]).($arResult["CATALOG_MENU"]["VALUE"] == "INTERFACE-2-0-1" && !CSite::inDir(SITE_DIR."index.php") ? "-inner" : "")
);

//COUNTERS_SCRIPTS//
if(!empty($arResult["COUNTERS_SCRIPTS_HEAD"]["VALUE"])) {
	$APPLICATION->SetPageProperty(
		"countersScriptsHead",
		$arResult["COUNTERS_SCRIPTS_HEAD"]["VALUE"]
	);
}
if(!empty($arResult["COUNTERS_SCRIPTS_BODY_START"]["VALUE"])) {
	$APPLICATION->SetPageProperty(
		"countersScriptsBodyStart",
		$arResult["COUNTERS_SCRIPTS_BODY_START"]["VALUE"]
	);
}
if(!empty($arResult["COUNTERS_SCRIPTS_BODY_END"]["VALUE"])) {
	$APPLICATION->SetPageProperty(
		"countersScriptsBodyEnd",
		$arResult["COUNTERS_SCRIPTS_BODY_END"]["VALUE"]
	);
}

if($USER->IsAdmin() && $arResult["SHOW_SETTINGS_PANEL"]["VALUE"] == "Y") {
	$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/js/spectrum/spectrum.min.css");
	$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/jquery.cookie.min.js");
	$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/spectrum/spectrum.min.js");
	$this->IncludeComponentTemplate();
}

return $arResult;?>