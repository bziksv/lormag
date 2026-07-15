<?
$moduleClass = "CEnext";
$moduleID = "altop.enext";

CModule::AddAutoloadClasses(
	$moduleID,
	array(
		"enext" => "install/index.php",
		$moduleClass => "classes/general/".$moduleClass.".php"
	)
);

CJSCore::RegisterExt("enextIntlTelInput", array(
	"js" => "/bitrix/js/".$moduleID."/intlTelInput/intlTelInput.min.js",
	"css" => "/bitrix/js/".$moduleID."/intlTelInput/css/intlTelInput.min.css",
	"lang" => "/bitrix/modules/".$moduleID."/lang/".LANGUAGE_ID."/install/js/intlTelInput.php"
));

AddEventHandler("iblock", "OnAfterIBlockPropertyUpdate", array($moduleClass, "UpdateMailEvent"));
AddEventHandler("iblock", "OnAfterIBlockPropertyAdd", array($moduleClass, "UpdateMailEvent"));

AddEventHandler("iblock", "OnAfterIBlockElementAdd", array($moduleClass, "DoIBlockAfterSave"));
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", array($moduleClass, "DoIBlockAfterSave"));
AddEventHandler("catalog", "OnPriceAdd", array($moduleClass, "DoIBlockAfterSave"));
AddEventHandler("catalog", "OnPriceUpdate", array($moduleClass, "DoIBlockAfterSave"));

AddEventHandler("main", "OnEpilog", array($moduleClass, "GetShareDefPict"));
AddEventHandler("main", "OnEpilog", array($moduleClass, "SetCanonicalUrl"));

AddEventHandler("main", "OnEndBufferContent", array($moduleClass, "ChangeContent"));

AddEventHandler("sale", "OnBasketAdd", array($moduleClass, "AddPresentToBasket"));

AddEventHandler("sale", "OnOrderNewSendEmail", array($moduleClass, "CheckOrderNewSendEmail"));
AddEventHandler("sale", "OnOrderStatusSendEmail", array($moduleClass, "CheckOrderStatusSendEmail"));