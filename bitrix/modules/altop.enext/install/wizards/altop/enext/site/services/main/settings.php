<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!defined("WIZARD_SITE_ID"))
	return;

//MAIN//
COption::SetOptionString("main", "new_user_registration", "Y");
COption::SetOptionString("main", "captcha_registration", "Y");
COption::SetOptionString("main", "auth_components_template", "flat");

//IBLOCK//
COption::SetOptionString("iblock", "property_features_enabled", "N");
COption::SetOptionString("iblock", "use_htmledit", "Y");
COption::SetOptionString("iblock", "combined_list_mode", "Y");

//SEARCH//
COption::SetOptionInt("search", "suggest_save_days", 250);
COption::SetOptionString("search", "use_tf_cache", "Y");
COption::SetOptionString("search", "use_word_distance", "Y");
COption::SetOptionString("search", "use_social_rating", "Y");

//SOCIALSERVICES//
if(COption::GetOptionString("socialservices", "auth_services") == "") {
	$bRu = (LANGUAGE_ID == 'ru');
	$arServices = array(
		"VKontakte" => "N",  
		"MyMailRu" => "N",
		"Twitter" => "N",
		"Facebook" => "N",
		"Livejournal" => "Y",
		"YandexOpenID" => ($bRu? "Y":"N"),
		"Rambler" => ($bRu? "Y":"N"),
		"MailRuOpenID" => ($bRu? "Y":"N"),
		"Liveinternet" => ($bRu? "Y":"N"),
		"Blogger" => "Y",
		"OpenID" => "Y",
		"LiveID" => "N",
	);
	COption::SetOptionString("socialservices", "auth_services", serialize($arServices));
}
COption::SetOptionString("socialnetwork", "allow_tooltip", "N", false, WIZARD_SITE_ID);

//SALE//
COption::SetOptionString("sale", "SHOP_SITE_".WIZARD_SITE_ID, WIZARD_SITE_ID);
COption::SetOptionString("sale", "viewed_capability", "Y");
COption::SetOptionInt("sale", "viewed_count", 12);
COption::SetOptionString("sale", "use_sale_discount_only", "Y");

//FILEMAN//
COption::SetOptionString("fileman", "propstypes", serialize(array("description" => GetMessage("MAIN_OPT_DESCRIPTION"), "keywords" => GetMessage("MAIN_OPT_KEYWORDS"), "title" => GetMessage("MAIN_OPT_TITLE"), "keywords_inner" => GetMessage("MAIN_OPT_KEYWORDS_INNER"))), false, WIZARD_SITE_ID);

//CAPTCHA//
COption::SetOptionString("main", "CAPTCHA_presets", "0");
COption::SetOptionString("main", "CAPTCHA_transparentTextPercent", "0");
COption::SetOptionString("main", "CAPTCHA_arBGColor_1", "d0e0e3");
COption::SetOptionString("main", "CAPTCHA_arBGColor_2", "d0e0e3");
COption::SetOptionString("main", "CAPTCHA_numEllipses", "0");
COption::SetOptionString("main", "CAPTCHA_arEllipseColor_1", "ffffff");
COption::SetOptionString("main", "CAPTCHA_arEllipseColor_2", "ffffff");
COption::SetOptionString("main", "CAPTCHA_bLinesOverText", "N");
COption::SetOptionString("main", "CAPTCHA_numLines", "0");
COption::SetOptionString("main", "CAPTCHA_arLineColor_1", "ffffff");
COption::SetOptionString("main", "CAPTCHA_arLineColor_2", "ffffff");
COption::SetOptionString("main", "CAPTCHA_textStartX", "30");
COption::SetOptionString("main", "CAPTCHA_textFontSize", "24");
COption::SetOptionString("main", "CAPTCHA_arTextColor_1", "000000");
COption::SetOptionString("main", "CAPTCHA_arTextColor_2", "000000");
COption::SetOptionString("main", "CAPTCHA_textAngel_1", "-15");
COption::SetOptionString("main", "CAPTCHA_textAngel_2", "-15");
COption::SetOptionString("main", "CAPTCHA_textDistance_1", "15");
COption::SetOptionString("main", "CAPTCHA_textDistance_2", "15");
COption::SetOptionString("main", "CAPTCHA_bWaveTransformation", "N");
COption::SetOptionString("main", "CAPTCHA_bEmptyText", "N");
COption::SetOptionString("main", "CAPTCHA_arBorderColor", "d0e0e3");
COption::SetOptionString("main", "CAPTCHA_arTTFFiles", "bitrix_captcha.ttf");
COption::SetOptionString("main", "CAPTCHA_letters", "123456789");

//SHARE_DEFAULT_PICTURE//
$arFile = CFile::MakeFileArray(WIZARD_TEMPLATE_ABSOLUTE_PATH."/images/share-default-picture.png");
$arFile["MODULE_ID"] = "enext";
$shareDefPict = CFile::SaveFile($arFile, "enext");
if($shareDefPict > 0)
	COption::SetOptionString("enext", "SHARE_DEFAULT_PICTURE", $shareDefPict);
unset($shareDefPict, $arFile);

//APPLE_TOUCH_ICON//
$arFile = CFile::MakeFileArray(WIZARD_TEMPLATE_ABSOLUTE_PATH."/images/apple-touch-icon-180.png");
$arFile["MODULE_ID"] = "enext";
$appleTouchIcon = CFile::SaveFile($arFile, "enext");
if($appleTouchIcon > 0)
	COption::SetOptionString("enext", "APPLE_TOUCH_ICON_180_180", $appleTouchIcon);
unset($appleTouchIcon, $arFile);