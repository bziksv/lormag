<?define("NOT_CHECK_PERMISSIONS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Web\Json;

if(!Bitrix\Main\Loader::includeModule("iblock"))
	return;

Loc::loadMessages(__FILE__);

global $APPLICATION;

$context = Bitrix\Main\Application::getInstance()->getContext();

$response = $context->getResponse();
$response->addHeader("Content-Type", "application/json");

$request = $context->getRequest();

if($request->isAjaxRequest()) {
	$action = $request->get("action");
	//GET_DATA//
	if($action == "getData") {
		$getCaptcha = $request->get("getCaptcha");
		if($getCaptcha)
			$result["captcha"] = $APPLICATION->CaptchaGetCode();

		$phoneMask = $request->get("phoneMask");
		
		$props = $request->get("props");
		if(!empty($props)) {
			foreach($props as $arProp) {
				if(!empty($request->getCookie("ENEXT_FORMS_".$arProp["CODE"].($arProp["CODE"] == "PHONE" && !$phoneMask ? "_FULL" : ""))))
					$result[$arProp["CODE"]] = urldecode($request->getCookie("ENEXT_FORMS_".$arProp["CODE"].($arProp["CODE"] == "PHONE" && !$phoneMask ? "_FULL" : "")));
			}
			unset($arProp);
		}
		
		$siteServerName = $request->get("siteServerName") ?: SITE_SERVER_NAME;
		$languageId = $request->get("languageId") ?: LANGUAGE_ID;

		if(!!$phoneMask) {
			$result["COUNTRY"] = $request->getCookie("ENEXT_FORMS_COUNTRY");
			if(empty($result["COUNTRY"]) && !preg_match("/Google|Yahoo|Rambler|Bot|Yandex|Spider|Snoopy|Crawler|Finder|Mail|curl/i", $request->getUserAgent())) {
				$ipAddress = Bitrix\Main\Service\GeoIp\Manager::getRealIp();
				$countryCode = Bitrix\Main\Service\GeoIp\Manager::getCountryCode($ipAddress, $languageId);
				if(!empty($countryCode)) {
					$result["COUNTRY"] = strtolower($countryCode);
					$cookie = new Bitrix\Main\Web\Cookie("ENEXT_FORMS_COUNTRY", $result["COUNTRY"], time() + 32832000);
					$cookie->setDomain($siteServerName);
					$cookie->setHttpOnly(false);
					$response->addCookie($cookie);
				}
			}
		}

		$response->flush(Json::encode($result));
	//CHECK_CAPTCHA//
	} elseif($action == "checkCaptcha") {
		$resp = CEnext::CheckCaptchaCode($request->get("CAPTCHA_WORD"), $request->get("CAPTCHA_SID"));
		
		$response->flush(Json::encode(array(
			"valid" => $resp
		)));
	//SEND_FORM//	
	} elseif($action == "sendForm") {
		$phoneMask = $request->get("phoneMask");

		$siteId = $request->get("siteId") ?: SITE_ID;
		$siteCharset = $request->get("siteCharset") ?: SITE_CHARSET;
		$siteServerName = $request->get("siteServerName") ?: SITE_SERVER_NAME;
		$languageId = $request->get("languageId") ?: LANGUAGE_ID;

		$iblockString = $request->get("IBLOCK_STRING");
		if(!empty($iblockString))
			$iblock = unserialize(base64_decode(strtr($iblockString, "-_,", "+/=")));

		//USER_CONSENT//
		$userConsent = $request->get("USER_CONSENT");
		if($userConsent == "Y") {
			$userConsentId = (int)$request->get("USER_CONSENT_ID");
			$userConsentUrl = $request->get("USER_CONSENT_URL");
			Bitrix\Main\UserConsent\Consent::addByContext($userConsentId, null, null, array("URL" => $userConsentUrl));
		}

		//CAPTCHA//
		$captchaSid = $request->get("CAPTCHA_SID");
		if(!empty($captchaSid))
			CEnext::DeleteCaptcha($captchaSid);
		
		//PROPERTIES//
		foreach($iblock["PROPERTIES"] as $arProp) {	
			$post = $request->getPost($arProp["CODE"]);
			if(!empty($post)) {			
				if($arProp["CODE"] == "OBJECT_ID" || $arProp["CODE"] == "PRODUCT_ID" || $arProp["CODE"] == "OFFER_ID") {
					$arProps[$arProp["CODE"]] = intval($post);
				} elseif($arProp["CODE"] == "PHONE") {
					if(!!$phoneMask) {
						$arProps[$arProp["CODE"]] = $post["FULL_VALUE"];
						
						if($post["VALUE"] != urldecode($request->getCookie("ENEXT_FORMS_".$arProp["CODE"]))) {
							$cookie = new Bitrix\Main\Web\Cookie("ENEXT_FORMS_".$arProp["CODE"], urlencode($post["VALUE"]), time() + 32832000);
							$cookie->setDomain($siteServerName);
							$cookie->setHttpOnly(false);
							$response->addCookie($cookie);			
							unset($cookie);
						}

						if($post["COUNTRY"]["iso2"] != $request->getCookie("ENEXT_FORMS_COUNTRY")) {
							$cookie = new Bitrix\Main\Web\Cookie("ENEXT_FORMS_COUNTRY", $post["COUNTRY"]["iso2"], time() + 32832000);
							$cookie->setDomain($siteServerName);
							$cookie->setHttpOnly(false);
							$response->addCookie($cookie);			
							unset($cookie);
						}
					} else {
						$arProps[$arProp["CODE"]] = $post;
						if($post != urldecode($request->getCookie("ENEXT_FORMS_".$arProp["CODE"]."_FULL"))) {
							$cookie = new Bitrix\Main\Web\Cookie("ENEXT_FORMS_".$arProp["CODE"]."_FULL", urlencode($post), time() + 32832000);
							$cookie->setDomain($siteServerName);
							$cookie->setHttpOnly(false);
							$response->addCookie($cookie);			
							unset($cookie);
						}
					}
				} elseif($arProp["CODE"] == "SOURCE_URL") {
					$arProps[$arProp["CODE"]] = $post;
				} else {
					$post = iconv("UTF-8", $siteCharset, strip_tags(trim($post)));
					if($arProp["USER_TYPE"] == "HTML") {
						$arProps[$arProp["CODE"]] = array(
							"VALUE" => array(
								"TEXT" => $post,
								"TYPE" => $arProp["DEFAULT_VALUE"]["TYPE"]
							)
						);
					} else {
						$arProps[$arProp["CODE"]] = $post;
						if($post != urldecode($request->getCookie("ENEXT_FORMS_".$arProp["CODE"]))) {
							$cookie = new Bitrix\Main\Web\Cookie("ENEXT_FORMS_".$arProp["CODE"], urlencode($post), time() + 32832000);
							$cookie->setDomain($siteServerName);
							$cookie->setHttpOnly(false);
							$response->addCookie($cookie);			
							unset($cookie);
						}
					}
				}
			}
		}
		unset($arProp);
		
		//PRODUCT_LINK//
		$productLink = $request->getPost("PRODUCT_LINK");

		//NEW_ELEMENT//
		$el = new CIBlockElement;

		$arFields = array(
			"IBLOCK_ID" => $iblock["ID"],
			"ACTIVE" => "Y",
			"NAME" => Loc::getMessage("IBLOCK_ELEMENT_NAME").ConvertTimeStamp(time(), "FULL"),
			"PROPERTY_VALUES" => !empty($arProps) ? $arProps : array(),
		);

		if($el->Add($arFields)) {
			//MAIL_PROPERTIES//
			foreach($iblock["PROPERTIES"] as $arProp) {
				$post = $request->getPost($arProp["CODE"]);
				if(!empty($post)) {
					if($arProp["CODE"] == "OBJECT_ID" || $arProp["CODE"] == "PRODUCT_ID" || $arProp["CODE"] == "OFFER_ID") {
						$arSelect = array("ID", "IBLOCK_ID", "NAME");
						if($arProp["CODE"] == "OBJECT_ID") {
							$arSelect[] = "PROPERTY_PHONE_SMS";
							$arSelect[] = "PROPERTY_EMAIL_EMAIL";
						}
						
						$rsElement = CIBlockElement::GetList(
							array(),
							array("ID" => intval($post)),
							false,
							false,
							$arSelect
						);
						if($arElement = $rsElement->GetNext()) {
							$arMailProps[$arProp["CODE"]] = $arElement["NAME"];
							if($arProp["CODE"] == "OBJECT_ID") {
								$arMailProps["PHONE_SMS"] = $arElement["PROPERTY_PHONE_SMS_VALUE"];
								$arMailProps["EMAIL_EMAIL"] = $arElement["PROPERTY_EMAIL_EMAIL_VALUE"];
							}
						}
						unset($arElement, $rsElement, $arSelect);
					} elseif($arProp["CODE"] == "PHONE") {
						if(!!$phoneMask)
							$arMailProps[$arProp["CODE"]] = $post["FULL_VALUE"];
						else
							$arMailProps[$arProp["CODE"]] = $post;
					} elseif($arProp["CODE"] == "SOURCE_URL") {
						$arMailProps[$arProp["CODE"]] = $post;
					} else {
						$arMailProps[$arProp["CODE"]] = iconv("UTF-8", $siteCharset, strip_tags(trim($post)));
					}
				}
			}
			unset($arProp);
			
			if(!empty($productLink))
				$arMailProps["PRODUCT_LINK"] = $productLink;
			
			$arMailProps["FORM_NAME"] = $iblock["NAME"];
			$arMailProps["EMAIL_TO"] = Bitrix\Main\Config\Option::get("main", "email_from");

			//MAIL_EVENT//	
			$eventName = "ALTOP_FORM_".$iblock["IBLOCK_TYPE_ID"]."_".$iblock["CODE"];

			$eventDesc = $messBody = "";		
			foreach($iblock["PROPERTIES"] as $arProp) {
				$eventDesc .= "#".$arProp["CODE"]."# - ".$arProp["NAME"]."\n";
				if($arProp["CODE"] == "OBJECT_ID") {
					$eventDesc .= Loc::getMessage("MAIL_EVENT_DESCRIPTION_NOTIFICATION");
				}
				$messBody .= $arProp["NAME"].": #".$arProp["CODE"]."#<br />";		
			}
			unset($arProp);
			
			if(!empty($productLink)) {
				$eventDesc .= Loc::getMessage("MAIL_EVENT_DESCRIPTION_PRODUCT_LINK");
				$messBody .= Loc::getMessage("MAIL_EVENT_MESSAGE_PRODUCT_LINK");
			}
			
			$eventDesc .= Loc::getMessage("MAIL_EVENT_DESCRIPTION");
			
			//MAIL_EVENT_TYPE//
			$arEvent = CEventType::GetByID($eventName, LANGUAGE_ID)->Fetch();
			if(empty($arEvent)) {
				$et = new CEventType;
				$arEventFields = array(
					"LID" => LANGUAGE_ID,
					"EVENT_NAME" => $eventName,
					"NAME" => Loc::getMessage("MAIL_EVENT_TYPE_NAME")." \"".$iblock["NAME"]."\"",
					"DESCRIPTION" => $eventDesc
				);
				$et->Add($arEventFields);		
			}

			//MAIL_EVENT_MESSAGE//
			$arMess = CEventMessage::GetList($by = "site_id", $order = "desc", array("TYPE_ID" => $eventName))->Fetch();
			if(empty($arMess)) {
				$em = new CEventMessage;
				$arMess = array();
				$arMess["ID"] = $em->Add(
					array(
						"ACTIVE" => "Y",
						"EVENT_NAME" => $eventName,
						"LID" => $siteId,
						"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
						"EMAIL_TO" => !empty($arMailProps["EMAIL_EMAIL"]) ? "#EMAIL_EMAIL#" : "#EMAIL_TO#",
						"BCC" => "",
						"SUBJECT" => Loc::getMessage("MAIL_EVENT_MESSAGE_SUBJECT"),
						"BODY_TYPE" => "html",
						"MESSAGE" => $messBody.Loc::getMessage("MAIL_EVENT_MESSAGE_FOOTER")
					)
				);		
			}

			//SEND_MAIL//
			Bitrix\Main\Mail\Event::send(array(
				"EVENT_NAME" => $eventName,
				"LID" => $siteId,
				"C_FIELDS" => !empty($arMailProps) ? $arMailProps : array(),
			));
			
			$response->flush(Json::encode(array(
				"status" => true,
				"captcha_code" => false
			)));
		} else {
			$response->flush(Json::encode(array(
				"status" => false,
				"captcha_code" => !empty($captchaSid) ? $APPLICATION->CaptchaGetCode() : false
			)));
		}
	}
}