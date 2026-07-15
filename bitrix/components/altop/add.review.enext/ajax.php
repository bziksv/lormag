<?define("NOT_CHECK_PERMISSIONS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Web\Json;

if(!Bitrix\Main\Loader::includeModule("iblock"))
	return;

Loc::loadMessages(__FILE__);

global $APPLICATION, $USER;

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
		
		$props = $request->get("props");
		if(!empty($props)) {
			foreach($props as $arProp) {
				if(!empty($request->getCookie("ENEXT_FORMS_".$arProp)))
					$result[$arProp] = urldecode($request->getCookie("ENEXT_FORMS_".$arProp));
			}
			unset($arProp);
		}
		
		$response->flush(Json::encode($result));
	//CHECK_CAPTCHA//
	} elseif($action == "checkCaptcha") {
		$resp = CEnext::CheckCaptchaCode($request->get("CAPTCHA_WORD"), $request->get("CAPTCHA_SID"));
		
		$response->flush(Json::encode(array(
			"valid" => $resp
		)));
	//ADD_REVIEW//	
	} elseif($action == "addReview") {
		$premoderation = $request->get("premoderation") == "Y" ? true : false;

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
			$post = $request->get($arProp["CODE"]);
			if(!empty($post)) {
				if($arProp["PROPERTY_TYPE"] == "E" || $arProp["PROPERTY_TYPE"] == "L") {
					$arProps[$arProp["CODE"]] = intval($post);					
					if($arProp["PROPERTY_TYPE"] == "E") {
						$rsElement = CIBlockElement::GetList(array(), array("ID" => intval($post)), false, false, array("ID", "IBLOCK_ID", "NAME"));
						if($arElement = $rsElement->GetNext()) {
							$arMailProps[$arProp["CODE"]] = $arElement["NAME"];
						}
						unset($arElement, $rsElement);
					} else {
						$arMailProps[$arProp["CODE"]] = $arProp["VALUES"][intval($post)]["VALUE"];
					}
				} elseif($arProp["PROPERTY_TYPE"] == "S") {
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
						if(empty($arProp["VALUE"]) && $post != urldecode($request->getCookie("ENEXT_FORMS_".$arProp["CODE"]))) {
							$cookie = new Bitrix\Main\Web\Cookie("ENEXT_FORMS_".$arProp["CODE"], urlencode($post), time() + 32832000);
							$cookie->setDomain($siteServerName);
							$cookie->setHttpOnly(false);
							$response->addCookie($cookie);			
							unset($cookie);
						}
					}
					$arMailProps[$arProp["CODE"]] = $post;
				}
			} else {
				if($arProp["PROPERTY_TYPE"] == "S") {
					if($arProp["USER_TYPE"] == "UserID" && $USER->IsAuthorized()) {
						$arProps[$arProp["CODE"]] = $USER->GetID();
						$arMailProps[$arProp["CODE"]] = $USER->GetLogin();
					} elseif($arProp["CODE"] == "NAME") {
						$arProps[$arProp["CODE"]] = Loc::getMessage("ADD_REVIEW_AJAX_PROP_NAME");
						$arMailProps[$arProp["CODE"]] = Loc::getMessage("ADD_REVIEW_AJAX_PROP_NAME");
					} elseif($arProp["CODE"] == "CITY") {
						$ipAddress = Bitrix\Main\Service\GeoIp\Manager::getRealIp();
						$cityName = Bitrix\Main\Service\GeoIp\Manager::getCityName($ipAddress, $languageId);
						if(!empty($cityName)) {
							$arProps[$arProp["CODE"]] = $cityName;
							$arMailProps[$arProp["CODE"]] = $cityName;
						}						
					}
				} elseif($arProp["PROPERTY_TYPE"] == "N") {
					$arProps[$arProp["CODE"]] = $arProp["DEFAULT_VALUE"];
				}
			}

			$files = $request->getFile($arProp["CODE"]);
			if(!empty($files)) {
				if($arProp["PROPERTY_TYPE"] == "F") {
					foreach($files["size"] as $key => $arSize) {
						$arFile = array(
							"name" => iconv("UTF-8", $siteCharset, $files["name"][$key]),
							"size" => $arSize,
							"tmp_name" => $files["tmp_name"][$key],
							"type" => $files["type"][$key]
						);
						CFile::ResizeImage(
							$arFile,
							array("width" => "1000", "height" => "1000"),
							BX_RESIZE_IMAGE_PROPORTIONAL
						);
						$arProps[$arProp["CODE"]][] = $arMailFiles[] = CFile::SaveFile($arFile, "iblock");
					}
					unset($key, $arSize);
				}
			}
		}
		unset($arProp);		
		
		//NEW_ELEMENT//
		$el = new CIBlockElement;
		
		$arFields = array(
			"NAME" => Loc::getMessage("ADD_REVIEW_AJAX_IBLOCK_ELEMENT_NAME").ConvertTimeStamp(time(), "FULL"),
			"IBLOCK_ID" => $iblock["ID"],
			"ACTIVE" => !$premoderation ? "Y" : "N",
			"ACTIVE_FROM" => ConvertTimeStamp(time(), "FULL"),
			"PROPERTY_VALUES" => !empty($arProps) ? $arProps : array(),
		);

		if($elementId = $el->Add($arFields)) {
			//MAIL_PROPERTIES//
			$arMailProps["MODERATION_LINK"] = (CMain::IsHTTPS() ? "https" : "http")."://".SITE_SERVER_NAME."/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=".$iblock["ID"]."&type=".$iblock["IBLOCK_TYPE_ID"]."&ID=".$elementId."&lang=".$languageId;
			
			$arMailProps["IBLOCK_NAME"] = $iblock["NAME"];
			$arMailProps["EMAIL_TO"] = Bitrix\Main\Config\Option::get("main", "email_from");

			//MAIL_EVENT//	
			$eventName = "ALTOP_".ToUpper($iblock["IBLOCK_TYPE_ID"])."_".ToUpper($iblock["CODE"]);

			$eventDesc = $messBody = "";		
			foreach($iblock["PROPERTIES"] as $arProp) {
				if($arProp["PROPERTY_TYPE"] != "N" && $arProp["PROPERTY_TYPE"] != "F") {
					$eventDesc .= "#".$arProp["CODE"]."# - ".$arProp["NAME"]."\n";				
					$messBody .= $arProp["NAME"].": #".$arProp["CODE"]."#<br />";
				}
			}
			unset($arProp);

			$eventDesc .= Loc::getMessage("ADD_REVIEW_AJAX_MAIL_EVENT_DESCRIPTION_MODERATION_LINK");
			$messBody .= Loc::getMessage("ADD_REVIEW_AJAX_MAIL_EVENT_MESSAGE_MODERATION_LINK");
			
			$eventDesc .= Loc::getMessage("ADD_REVIEW_AJAX_MAIL_EVENT_DESCRIPTION");

			//MAIL_EVENT_TYPE//
			$arEvent = CEventType::GetByID($eventName, $languageId)->Fetch();
			if(empty($arEvent)) {
				$et = new CEventType;
				$arEventFields = array(
					"LID" => $languageId,
					"EVENT_NAME" => $eventName,
					"NAME" => Loc::getMessage("ADD_REVIEW_AJAX_MAIL_EVENT_TYPE_NAME", array("#IBLOCK_NAME#" => $iblock["NAME"])),
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
						"EMAIL_TO" => "#EMAIL_TO#",
						"BCC" => "",
						"SUBJECT" => Loc::getMessage("ADD_REVIEW_AJAX_MAIL_EVENT_MESSAGE_SUBJECT"),
						"BODY_TYPE" => "html",
						"MESSAGE" => $messBody.Loc::getMessage("ADD_REVIEW_AJAX_MAIL_EVENT_MESSAGE_FOOTER")
					)
				);		
			}

			//SEND_MAIL//
			Bitrix\Main\Mail\Event::send(array(
				"EVENT_NAME" => $eventName,
				"LID" => $siteId,
				"C_FIELDS" => !empty($arMailProps) ? $arMailProps : array(),
				"FILE" => !empty($arMailFiles) ? $arMailFiles : array()
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