<?define("STOP_STATISTICS", true);
define("NOT_CHECK_PERMISSIONS", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader,
	Bitrix\Main\Config\Option,	
	Bitrix\Main\Localization\Loc,	
	Bitrix\Main\Web\Cookie,
	Bitrix\Main\Web\Json,
	Bitrix\Sale;

if(!Loader::includeModule("iblock") || !Loader::includeModule("catalog") || !Loader::includeModule("sale"))
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
	//CREATE_ORDER//	
	} elseif($action == "createOrder") {
		$phoneMask = $request->get("phoneMask");

		$siteId = $request->get("siteId") ?: SITE_ID;
		$siteCharset = $request->get("siteCharset") ?: SITE_CHARSET;
		$siteServerName = $request->get("siteServerName") ?: SITE_SERVER_NAME;
		$languageId = $request->get("languageId") ?: LANGUAGE_ID;
		
		$mode = $request->get("MODE");

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
		
		//PROPS//
		$getName = $request->get("NAME");
		if(!empty($getName)) {
			$name = iconv("UTF-8", $siteCharset, strip_tags(trim($getName)));
			if($name != urldecode($request->getCookie("ENEXT_FORMS_NAME"))) {
				$cookie = new Cookie("ENEXT_FORMS_NAME", urlencode($name), time() + 32832000);
				$cookie->setDomain($siteServerName);
				$cookie->setHttpOnly(false);
				$response->addCookie($cookie);			
				unset($cookie);
			}
		}
		
		if(!!$phoneMask) {
			$getPhone = $request->get("PHONE");
			$phone = $getPhone["FULL_VALUE"];
			
			if($getPhone["VALUE"] != urldecode($request->getCookie("ENEXT_FORMS_PHONE"))) {
				$cookie = new Cookie("ENEXT_FORMS_PHONE", urlencode($getPhone["VALUE"]), time() + 32832000);
				$cookie->setDomain($siteServerName);
				$cookie->setHttpOnly(false);
				$response->addCookie($cookie);			
				unset($cookie);
			}

			if($getPhone["COUNTRY"]["iso2"] != $request->getCookie("ENEXT_FORMS_COUNTRY")) {
				$cookie = new Cookie("ENEXT_FORMS_COUNTRY", $getPhone["COUNTRY"]["iso2"], time() + 32832000);
				$cookie->setDomain($siteServerName);
				$cookie->setHttpOnly(false);
				$response->addCookie($cookie);			
				unset($cookie);
			}
		} else {
			$phone = $request->get("PHONE");
			if($phone != urldecode($request->getCookie("ENEXT_FORMS_PHONE_FULL"))) {
				$cookie = new Bitrix\Main\Web\Cookie("ENEXT_FORMS_PHONE_FULL", urlencode($phone), time() + 32832000);
				$cookie->setDomain($siteServerName);
				$cookie->setHttpOnly(false);
				$response->addCookie($cookie);			
				unset($cookie);
			}
		}
		
		$getEmail = $request->get("EMAIL");
		if(!empty($getEmail))
			$email = iconv("UTF-8", $siteCharset, strip_tags(trim($getEmail)));

		$getComments = $request->get("COMMENTS");
		if(!empty($getComments))
			$comments = iconv("UTF-8", $siteCharset, strip_tags(trim($getComments)));
		
		//LOCATION//
		$ipAddress = Bitrix\Main\Service\GeoIp\Manager::getRealIp();
		$locCode = Sale\Location\GeoIp::getLocationCode($ipAddress, $languageId);

		//OBJECT//		
		$object = false;
		$objectId = (int)$request->get("OBJECT_ID");
		if($objectId > 0) {
			$rsElement = CIBlockElement::GetList(array(), array("ID" => $objectId), false, false, array("ID", "IBLOCK_ID", "NAME", "PROPERTY_PHONE_SMS", "PROPERTY_EMAIL_EMAIL"));
			if($arElement = $rsElement->GetNext()) {
				$object = array(
					"ID" => $arElement["ID"],
					"NAME" => $arElement["NAME"],
					"PHONE_SMS" => $arElement["PROPERTY_PHONE_SMS_VALUE"],
					"EMAIL_EMAIL" => $arElement["PROPERTY_EMAIL_EMAIL_VALUE"]
				);
			}
		}
	
		//USER//
		global $USER;
		if(!$USER->IsAuthorized()) {
			$rsUser = $USER->GetByLogin($phone);
			if($arUser = $rsUser->Fetch()) {
				$registeredUserID = $arUser["ID"];
			} else {
				$newPass = randString(10, array("abcdefghijklnmopqrstuvwxyz", "ABCDEFGHIJKLNMOPQRSTUVWXYZ", "0123456789", "!@#\$%^&*()"));
				
				$groupIds = array();
				$defaultGroups = Option::get("main", "new_user_registration_def_group");
				if(!empty($defaultGroups))
					$groupIds = explode(",", $defaultGroups);
				
				$arFields = Array(
					"LOGIN" => $phone,
					"NAME" => !empty($name) ? $name : $phone,
					"EMAIL" => !empty($email) ? $email : (Option::get("main", "new_user_email_auth") == "Y" && Option::get("main", "new_user_email_required") == "Y" ? preg_replace("/[^0-9]/", "", $phone)."@".$siteServerName : ""),
					"PHONE_NUMBER" => preg_replace("/[^0-9+]/", "", $phone),
					"PASSWORD" => $newPass,
					"CONFIRM_PASSWORD" => $newPass,
					"GROUP_ID" => $groupIds,
					"ACTIVE" => "Y",
					"LID" => $siteId
				);

				$registeredUserID = $USER->Add($arFields);
			}
			unset($arUser, $rsUser);
		} else {
			$registeredUserID = $USER->GetID();
		}

		//BASKET//
		$basketUserID = Sale\Fuser::getId();

		Sale\DiscountCouponsManager::init();

		//PERSON_TYPE_ID//
		$arPersonTypes = Sale\PersonType::load($siteId);
		reset($arPersonTypes);
		$arPersonType = current($arPersonTypes);
		$arPersonTypeId = !empty($arPersonType) ? $arPersonType["ID"] : 1;

		//DELIVERY_SERVICE//
		$arDeliveryService = Sale\Delivery\Services\Manager::getById(Sale\Delivery\Services\EmptyDeliveryService::getEmptyDeliveryServiceId());

		function checkCreateOrderProps($arPersonTypeId) {
			$arPropsGroupId = 1;
			$rsPropsGroup = CSaleOrderPropsGroup::GetList(array("ID" => "ASC"), array("PERSON_TYPE_ID" => $arPersonTypeId), false, array("nTopCount" => 1), array("ID"));
			if($arPropGroup = $rsPropsGroup->Fetch()) {
				$arPropsGroupId = $arPropGroup["ID"];
			}
			unset($arPropGroup, $rsPropsGroup);

			//PROP_OBJECT_ID//
			$arOrderProp = CSaleOrderProps::GetList(array(), array("PERSON_TYPE_ID" => $arPersonTypeId, "CODE" => "OBJECT_ID"), false, false, array("ID"))->Fetch();
			if(empty($arOrderProp)) {
				$arFields = array(
					"PERSON_TYPE_ID" => $arPersonTypeId,
					"NAME" => Loc::getMessage("QUICK_ORDER_AJAX_PROP_OBJECT_ID_NAME"),
					"TYPE" => "TEXT",
					"REQUIED" => "N",
					"SORT" => "500",
					"USER_PROPS" => "N",
					"IS_LOCATION" => "N",
					"PROPS_GROUP_ID" => $arPropsGroupId,
					"IS_EMAIL" => "N",
					"IS_PROFILE_NAME" => "N",
					"IS_PAYER" => "N",
					"IS_LOCATION4TAX" => "N",
					"IS_ZIP" => "N",
					"CODE" => "OBJECT_ID",
					"IS_FILTERED" => "Y",
					"ACTIVE" => "Y",
					"UTIL" => "Y",
					"MULTIPLE" => "N"
				);
				CSaleOrderProps::Add($arFields);
				unset($arFields);
			}
			unset($arOrderProp);

			//PROP_OBJECT_NAME//
			$arOrderProp = CSaleOrderProps::GetList(array(), array("PERSON_TYPE_ID" => $arPersonTypeId, "CODE" => "OBJECT_NAME"), false, false, array("ID"))->Fetch();
			if(empty($arOrderProp)) {
				$arFields = array(
					"PERSON_TYPE_ID" => $arPersonTypeId,
					"NAME" => Loc::getMessage("QUICK_ORDER_AJAX_PROP_OBJECT_NAME_NAME"),
					"TYPE" => "TEXT",
					"REQUIED" => "N",
					"SORT" => "500",
					"USER_PROPS" => "N",
					"IS_LOCATION" => "N",
					"PROPS_GROUP_ID" => $arPropsGroupId,
					"IS_EMAIL" => "N",
					"IS_PROFILE_NAME" => "N",
					"IS_PAYER" => "N",
					"IS_LOCATION4TAX" => "N",
					"IS_ZIP" => "N",
					"CODE" => "OBJECT_NAME",
					"IS_FILTERED" => "Y",
					"ACTIVE" => "Y",
					"UTIL" => "Y",
					"MULTIPLE" => "N"
				);
				CSaleOrderProps::Add($arFields);
				unset($arFields);
			}
			unset($arOrderProp, $arPropsGroupId);
		}

		function setOrderPropsFields($newOrder, $name, $phone, $email, $comments, $locCode, $object) {
			global $USER;

			//ORDER_SET_PROPERTIES//
			$propertyCollection = $newOrder->getPropertyCollection();
			
			$nameProp = $propertyCollection->getPayerName();
			if(!empty($name))
				$nameProp->setValue($name);
			elseif($USER->IsAuthorized())
				$nameProp->setValue($USER->GetFullName());
			else
				$nameProp->setValue(Loc::getMessage("QUICK_ORDER_AJAX_ORDER_PAYER_NAME"));
			unset($nameProp);
			
			$phoneProp = $propertyCollection->getPhone();
			$phoneProp->setValue(preg_replace("/[^0-9]/", "", $phone));
			unset($phoneProp);

			if(!empty($email)) {
				$emailProp = $propertyCollection->getUserEmail();
				$emailProp->setValue($email);
				unset($emailProp);
			}
		
			if(!empty($locCode)) {
				$locProp = $propertyCollection->getDeliveryLocation();
				$locProp->setValue($locCode);
				unset($locProp);
			}

			if($object) {
				foreach($propertyCollection as $prop) {
					if($prop->getField("CODE") == "OBJECT_ID")
						$prop->setValue($object["ID"]);
					elseif($prop->getField("CODE") == "OBJECT_NAME")
						$prop->setValue($object["NAME"]);
				}
				unset($prop);
			}

			unset($propertyCollection);

			//ORDER_SET_FIELDS//
			$newOrder->setField("CURRENCY", Option::get("sale", "default_currency"));
			if(!empty($comments))
				$newOrder->setField("USER_DESCRIPTION", $comments);
			$newOrder->setField("COMMENTS", Loc::getMessage("QUICK_ORDER_AJAX_ORDER_COMMENT"));
		}

		function checkCreateEvent($siteId, $languageId) {
			$eventName = "SALE_NEW_ORDER_OBJECT";

			//EVENT_TYPE//
			$arEvent = CEventType::GetByID($eventName, $languageId)->Fetch();
			if(empty($arEvent)) {
				$et = new CEventType;
				$arEventFields = array(
					"LID" => $languageId,
					"EVENT_NAME" => $eventName,
					"NAME" => Loc::getMessage("QUICK_ORDER_AJAX_EVENT_NAME"),
					"DESCRIPTION" => Loc::getMessage("QUICK_ORDER_AJAX_EVENT_DESCRIPTION")
				);
				$et->Add($arEventFields);
				unset($arEventFields, $et);
			}
			unset($arEvent);

			//EVENT_MESSAGE//
			$arMess = CEventMessage::GetList($by = "site_id", $order = "desc", array("TYPE_ID" => $eventName))->Fetch();
			if(empty($arMess)) {
				$em = new CEventMessage;
				$arMessFields = array(
					"ACTIVE" => "Y",
					"EVENT_NAME" => $eventName,
					"LID" => $siteId,
					"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
					"EMAIL_TO" => "#OBJECT_EMAIL_EMAIL#",
					"BCC" => "#BCC#",
					"SUBJECT" => Loc::getMessage("QUICK_ORDER_AJAX_EVENT_MESSAGE_SUBJECT"),
					"BODY_TYPE" => "html",
					"MESSAGE" => Loc::getMessage("QUICK_ORDER_AJAX_EVENT_MESSAGE_MESSAGE")
				);
				$em->Add($arMessFields);
				unset($arMessFields, $em);
			}
			unset($arMess);
		}

		function sendEvent($newOrder, $name, $phone, $email, $comments, $object) {
			global $USER;

			$basketList = "";
			$basket = $newOrder->getBasket();
			if($basket) {
				$basketTextList = $basket->getListOfFormatText();
				if(!empty($basketTextList)) {
					foreach($basketTextList as $basketItemCode => $basketItemData) {
						$basketList .= $basketItemData."<br/>";
					}
				}
			}
			
			$fields = array(
				"ORDER_ID" => $newOrder->getField("ACCOUNT_NUMBER"),
				"ORDER_REAL_ID" => $newOrder->getField("ID"),
				"ORDER_DATE" => $newOrder->getDateInsert()->toString(),				
				"PRICE" => SaleFormatCurrency($newOrder->getPrice(), $newOrder->getCurrency()),
				"ORDER_LIST" => $basketList,
				"NAME" => !empty($name) ? $name : ($USER->IsAuthorized() ? $USER->GetFullName() : Loc::getMessage("QUICK_ORDER_AJAX_ORDER_PAYER_NAME")),
				"PHONE" => $phone,
				"EMAIL" => !empty($email) ? $email : "",
				"COMMENTS" => !empty($comments) ? $comments : "",
				"OBJECT_PHONE_SMS" => !empty($object["PHONE_SMS"]) ? $object["PHONE_SMS"] : "",
				"OBJECT_EMAIL_EMAIL" => !empty($object["EMAIL_EMAIL"]) ? $object["EMAIL_EMAIL"] : "",
				"BCC" => Option::get("sale", "order_email"),
				"SALE_EMAIL" => Option::get("sale", "order_email")
			);
			unset($basketList);
			
			//EVENT_SEND//
			$eventName = "SALE_NEW_ORDER_OBJECT";

			Bitrix\Main\Mail\Event::send(array(
				"EVENT_NAME" => $eventName,
				"LID" => $newOrder->getField("LID"),
				"C_FIELDS" => $fields,
				"LANGUAGE_ID" => Sale\Notify::getOrderLanguageId($newOrder)
			));
		}

        /**
         * Получение товаров заказа
         * @param $order
         * @return array
         */
        function getOrderProducts($order): array
        {
            $result = [];

            $basket = $order->getBasket();
            foreach ($basket as $item) {
                $propsVals = array_column($item->getPropertyCollection()->getPropertyValues(), 'VALUE');
                $result[] = [
                    "id" => $item->getProductId(),
                    "name" => $item->getField("NAME"),
                    "price" => $item->getPrice(),
                    "brand" => "",
                    "variant" => implode('/', $propsVals),
                    "quantity" => $item->getQuantity()
                ];
            }

            if (empty($result)) {
                return [];
            }

            $brandNames = getProductsBrand(array_column($result, 'id'));
            foreach ($result as $key => $item) {
                if (!$brandNames[$item['id']]) {
                    continue;
                }

                $result[$key]['brand'] = $brandNames[$item['id']];
            }

            return $result;
        }

        /**
         * Поиск бренда по ID товаров
         * @param array $ids
         * @return array
         */
        function getProductsBrand(array $ids): array
        {
            if (empty($ids)) {
                return [];
            }

            $result = [];

            $offersList = [];

            $rsElements = CIBlockElement::GetList(
                [],
                ['ID' => $ids],
                false,
                false,
                ['ID', 'IBLOCK_ID', 'PROPERTY_BRAND.ID', 'PROPERTY_BRAND.NAME', 'CATALOG_TYPE']
            );
            while ($arElement = $rsElements->GetNext()) {
                if ($arElement['CATALOG_TYPE'] == Bitrix\Catalog\ProductTable::TYPE_OFFER) {
                    $offersList[$arElement['ID']] = $arElement['ID'];
                    continue;
                }

                if (!($arElement['PROPERTY_BRAND_ID'] > 0)) {
                    continue;
                }

                $result[$arElement['ID']] = $arElement['PROPERTY_BRAND_NAME'];
            }

            if (empty($offersList)) {
                return $result;
            }

            $itemsParents = CCatalogSku::getProductList($offersList);

            if (empty($itemsParents)) {
                return $result;
            }

            $offersMap = [];
            foreach ($itemsParents as $offerId => $parentData) {
                if (!isset($offersMap[$parentData["ID"]])) {
                    $offersMap[$parentData["ID"]] = [];
                }
                $offersMap[$parentData["ID"]][$offerId] = $offerId;
            }

            $rsElements = CIBlockElement::GetList(
                [],
                ['ID' => array_keys($offersMap)],
                false,
                false,
                ['ID', 'IBLOCK_ID', 'PROPERTY_BRAND.ID', 'PROPERTY_BRAND.NAME']
            );
            while ($arElement = $rsElements->GetNext()) {
                if (!($arElement['PROPERTY_BRAND_ID'] > 0)) {
                    continue;
                }

                foreach($offersMap[$arElement['ID']] as $itemId) {
                    $result[$itemId] = $arElement['PROPERTY_BRAND_NAME'];
                }
            }

            return $result;
        }

		if($mode == "PRODUCT") {
			$basket = Sale\Basket::loadItemsForFUser($basketUserID, $siteId)->getOrderableItems();
			foreach($basket as $basketItem) {
				CSaleBasket::Delete($basketItem->getId());
			}
			unset($basketItem);

			//PRODUCT_ID//
			$productId = (int)$request->get("PRODUCT_ID");

			//PRODUCT_QUANTITY//
			$productQnt = (float)$request->get("quantity");	
			if($productQnt <= 0) {
				$ratioIterator = CCatalogMeasureRatio::getList(
					array(),
					array("PRODUCT_ID" => $productId),
					false,
					false,
					array("PRODUCT_ID", "RATIO")
				);
				if($ratio = $ratioIterator->Fetch()) {
					$intRatio = (int)$ratio["RATIO"];
					$floatRatio = (float)$ratio["RATIO"];
					$productQnt = $floatRatio > $intRatio ? $floatRatio : $intRatio;
				}
				unset($ratio, $ratioIterator);
			}
			if($productQnt <= 0) {
				$productQnt = 1;
			}
			
			$item = $basket->createItem("catalog", $productId);
			$item->setFields(array(
				"QUANTITY" => $productQnt,
				"CURRENCY" => Bitrix\Currency\CurrencyManager::getBaseCurrency(),
				"LID" => $siteId,
				"PRODUCT_PROVIDER_CLASS" => "CCatalogProductProvider"
			));
			$basket->save();

			//PRODUCT_PROPERTIES//	
			$productProperties = array();	
			$iblockId = (int)CIBlockElement::GetIBlockByID($productId);
			if($iblockId > 0) {
				$productCatalogInfo = CCatalogSku::GetInfoByIBlock($iblockId);
				if(!empty($productCatalogInfo) && $productCatalogInfo["CATALOG_TYPE"] == CCatalogSku::TYPE_PRODUCT) {
					$productCatalogInfo = false;
				}
				if(!empty($productCatalogInfo)) {
					$productIblockId = $productCatalogInfo["CATALOG_TYPE"] == CCatalogSku::TYPE_CATALOG ? $productCatalogInfo["IBLOCK_ID"] : $productCatalogInfo["PRODUCT_IBLOCK_ID"];			
					if($productCatalogInfo["CATALOG_TYPE"] != CCatalogSku::TYPE_OFFERS) {				
						$cartProps = unserialize(base64_decode(strtr($request->get("CART_PROPERTIES"), "-_,", "+/=")));				
						if(!empty($cartProps)) {					
							$productPropsVar = $request->get("PRODUCT_PROPS_VARIABLE");
							$productProps = !empty($productPropsVar) ? $request->get($productPropsVar) : "";
							if(is_array($productProps)) {
								$partialProductProps = $request->get("PARTIAL_PRODUCT_PROPERTIES");
								$productProperties = CIBlockPriceTools::CheckProductProperties(
									$productIblockId,
									$productId,
									$cartProps,
									$productProps,
									$partialProductProps === "Y"
								);
							}
						}
					} else {																
						$skuCartProps = $request->get("OFFERS_CART_PROPERTIES") ? unserialize(base64_decode(strtr($request->get("OFFERS_CART_PROPERTIES"), "-_,", "+/="))) : "";				
						$skuAddProps = $request->get("basket_props") ? $request->get("basket_props") : "";
						if(!empty($skuCartProps) || !empty($skuAddProps)) {
							$productProperties = CIBlockPriceTools::GetOfferProperties(
								$productId,
								$productIblockId,
								$skuCartProps,
								$skuAddProps
							);
						}
					}			
				}
			}
			if(!empty($productProperties)) {
				$basketPropertyCollection = $item->getPropertyCollection();
				$basketPropertyCollection->setProperty($productProperties);
				$basketPropertyCollection->save();
			}
			unset($productProperties, $item, $basket);
		}
		
		if($mode != "OBJECTS") {
			if($object) {
				checkCreateOrderProps($arPersonTypeId);
				checkCreateEvent($siteId, $languageId);
			}
			
			//CREATE_ORDER//
			$newOrder = Sale\Order::create($siteId, $registeredUserID);

			//ORDER_PERSON_TYPE//
			$newOrder->setPersonTypeId($arPersonTypeId);
			
			//ORDER_SET_BASKET//
			$basket = Sale\Basket::loadItemsForFUser($basketUserID, $siteId)->getOrderableItems();
			$newOrder->setBasket($basket);
			unset($basket);

			//ORDER_SET_DELIVERY//
			$shipmentCollection = $newOrder->getShipmentCollection();
			$shipment = $shipmentCollection->createItem();
			$shipment->setFields(array(
				"DELIVERY_ID" => $arDeliveryService["ID"],
				"DELIVERY_NAME" => $arDeliveryService["NAME"],
			));
			
			$shipmentItemCollection = $shipment->getShipmentItemCollection();
			foreach($newOrder->getBasket() as $item) {
				$shipmentItem = $shipmentItemCollection->createItem($item);
				$shipmentItem->setQuantity($item->getQuantity());
			}
			unset($shipmentItem, $item, $shipmentItemCollection);
			
			unset($shipment, $shipmentCollection);
			
			//ORDER_SET_PROPERTIES_FIELDS//
			setOrderPropsFields($newOrder, $name, $phone, $email, $comments, $locCode, $object);
			
			//SAVE_ORDER//
			$newOrder->doFinalAction(true);

			$newOrder->save();
	
			$orderId = $newOrder->GetId();
			if($orderId > 0) {
				//OBJECT//
				if ($object && (!empty($object["PHONE_SMS"]) || !empty($object["EMAIL_EMAIL"]))) {
					sendEvent($newOrder, $name, $phone, $email, $comments, $object);
                }

                $products = getOrderProducts($newOrder);

                //MESSAGE//
				$results["RESULTS"][] = [
					"status" => true,
					"text" => Loc::getMessage("QUICK_ORDER_AJAX_ORDER_CREATE_SUCCESS", [
                        "#ORDER_DATE#" => $newOrder->getDateInsert()->toString(),
                        "#ORDER_ID#" => $newOrder->getField("ACCOUNT_NUMBER")
                    ]),
                    "order" => [
                        "id" => $newOrder->getField("ACCOUNT_NUMBER"),
                        "total_price" => $newOrder->getPrice(),
                        "tax_price" => $newOrder->getTaxPrice(),
                        "delivery_price" => $newOrder->getDeliveryPrice()
                    ],
                    "products" => $products,
					"captcha_code" => false
				];
			} else {
				//MESSAGE//
				$results["RESULTS"][] = [
					"status" => false,
					"text" => Loc::getMessage("QUICK_ORDER_AJAX_ORDER_CREATE_ERROR"),
					"captcha_code" => !empty($captchaSid) ? $APPLICATION->CaptchaGetCode() : false
				];
			}
			unset($orderId, $newOrder);
			
			$response->flush(Json::encode($results));
		} else {
			$basket = Sale\Basket::loadItemsForFUser($basketUserID, $siteId)->getOrderableItems();
			$itemsList = $offersList = array();
			foreach($basket as $basketItem) {
				CSaleBasket::Delete($basketItem->getId());
				
				$itemsList[$basketItem->getProductId()] = array(
					"ID" => $basketItem->getProductId(),
					"QUANTITY" => $basketItem->getQuantity(),
					"CURRENCY" => $basketItem->getField("CURRENCY"),
					"LID" => $basketItem->getField("LID"),
					"PRODUCT_PROVIDER_CLASS" => $basketItem->getField("PRODUCT_PROVIDER_CLASS"),
					"XML_ID" => $basketItem->getField("XML_ID"),
					"PROPERTIES" => $basketItem->getPropertyCollection()->getPropertyValues()
				);
			}
			unset($basketItem);

			if(!empty($itemsList)) {
				$rsElements = CIBlockElement::GetList(array(), array("ID" => array_keys($itemsList)), false, false, array("ID", "IBLOCK_ID", "CATALOG_TYPE"));
				while($obElement = $rsElements->GetNextElement()) {
					$arElement = $obElement->GetFields();
					$arProps = $obElement->GetProperties();

					foreach($arProps as $arProp) {
						if($arProp["CODE"] == "OBJECT" && !empty($arProp["VALUE"]))
							$itemsList[$arElement["ID"]]["OBJECT"] = $arProp["VALUE"];
					}
					unset($arProp);

					if($arElement["CATALOG_TYPE"] == Bitrix\Catalog\ProductTable::TYPE_OFFER)
						$offersList[$arElement["ID"]] = $arElement["ID"];
				}
				unset($arProps, $arElement, $obElement, $rsElements);

				if(!empty($offersList)) {
					$itemsParents = CCatalogSku::getProductList($offersList);
					if(!empty($itemsParents)) {
						$offersMap = array();
						foreach($itemsParents as $offerId => $parentData) {
							if(!isset($offersMap[$parentData["ID"]]))
								$offersMap[$parentData["ID"]] = array();
							$offersMap[$parentData["ID"]][$offerId] = $offerId;
						}
						unset($offerId, $parentData);

						if(!empty($offersMap)) {
							$rsElements = CIBlockElement::GetList(array(), array("ID" => array_keys($offersMap)), false, false, array("ID", "IBLOCK_ID"));
							while($obElement = $rsElements->GetNextElement()) {
								$arElement = $obElement->GetFields();
								$arProps = $obElement->GetProperties();

								foreach($offersMap[$arElement["ID"]] as $itemId) {
									if(array_key_exists($itemId, $itemsList) && !isset($itemsList[$itemId]["OBJECT"])) {
										foreach($arProps as $arProp) {
											if($arProp["CODE"] == "OBJECT" && !empty($arProp["VALUE"]))
												$itemsList[$itemId]["OBJECT"] = $arProp["VALUE"];
										}
										unset($arProp);
									}
								}
								unset($itemId);
							}
							unset($arProps, $arElement, $obElement, $rsElements);
						}
						unset($offersMap);
					}
					unset($itemsParents);
				}
				unset($offersList);

				foreach($itemsList as $key => $item) {
					if(!empty($item["OBJECT"])) {
						$objectsItemsList[$item["OBJECT"]]["ITEMS"][] = $item;
						unset($itemsList[$key]);
					}
				}
				unset($key, $item);
				
				if(!empty($objectsItemsList)) {
					$rsElements = CIBlockElement::GetList(array(), array("ID" => array_keys($objectsItemsList)), false, false, array("ID", "IBLOCK_ID", "NAME", "PROPERTY_PHONE_SMS", "PROPERTY_EMAIL_EMAIL"));
					while($arElement = $rsElements->GetNext()) {
						$arObjects[$arElement["ID"]] = array(
							"ID" => $arElement["ID"],
							"NAME" => $arElement["NAME"],
							"PHONE_SMS" => $arElement["PROPERTY_PHONE_SMS_VALUE"],
							"EMAIL_EMAIL" => $arElement["PROPERTY_EMAIL_EMAIL_VALUE"]
						);
					}
					unset($arElement, $rsElements);

					if(!empty($arObjects)) {
						foreach($objectsItemsList as $objectId => &$data) {
							if(array_key_exists($objectId, $arObjects))
								$data["OBJECT"] = $arObjects[$objectId];
						}
						unset($objectId, $data);
					}
					unset($arObjects);

					checkCreateOrderProps($arPersonTypeId);
					checkCreateEvent($siteId, $languageId);
					
					foreach($objectsItemsList as $data) {
						//CREATE_ORDER//
						$newOrder = Sale\Order::create($siteId, $registeredUserID);

						//ORDER_PERSON_TYPE//
						$newOrder->setPersonTypeId($arPersonTypeId);
						
						//ORDER_SET_BASKET//
						$newBasket = Sale\Basket::create($siteId);
						foreach($data["ITEMS"] as $basketItem) {
							$item = $newBasket->createItem("catalog", $basketItem["ID"]);
							$item->setFields(array(
								"QUANTITY" => $basketItem["QUANTITY"],
								"CURRENCY" => $basketItem["CURRENCY"],
								"LID" => $basketItem["LID"],
								"PRODUCT_PROVIDER_CLASS" => $basketItem["PRODUCT_PROVIDER_CLASS"],
								"XML_ID" => $basketItem["XML_ID"]
							));
							$newBasket->save();

							$basketPropertyCollection = $item->getPropertyCollection();
							$basketPropertyCollection->setProperty($basketItem["PROPERTIES"]);
							$basketPropertyCollection->save();
						}
						unset($basketItem);
						
						$newOrder->setBasket($newBasket);
						unset($newBasket);

						//ORDER_SET_DELIVERY//
						$shipmentCollection = $newOrder->getShipmentCollection();
						$shipment = $shipmentCollection->createItem();
						$shipment->setFields(array(
							"DELIVERY_ID" => $arDeliveryService["ID"],
							"DELIVERY_NAME" => $arDeliveryService["NAME"],
						));
						
						$shipmentItemCollection = $shipment->getShipmentItemCollection();
						foreach($newOrder->getBasket() as $item) {
							$shipmentItem = $shipmentItemCollection->createItem($item);
							$shipmentItem->setQuantity($item->getQuantity());
						}
						unset($shipmentItem, $item, $shipmentItemCollection);
						
						unset($shipment, $shipmentCollection);
						
						//ORDER_SET_PROPERTIES_FIELDS//
						setOrderPropsFields($newOrder, $name, $phone, $email, $comments, $locCode, $data["OBJECT"]);
						
						//SAVE_ORDER//
						$newOrder->doFinalAction(true);

						$newOrder->save();

						$orderId = $newOrder->GetId();
						if($orderId > 0) {
							//OBJECT//
							if($data["OBJECT"] && (!empty($data["OBJECT"]["PHONE_SMS"]) || !empty($data["OBJECT"]["EMAIL_EMAIL"])))
								sendEvent($newOrder, $name, $phone, $email, $comments, $data["OBJECT"]);

                            $products = getOrderProducts($newOrder);

							//MESSAGE//
							$results["RESULTS"][] = [
								"status" => true,
								"text" => Loc::getMessage("QUICK_ORDER_AJAX_ORDER_CREATE_SUCCESS", [
                                    "#ORDER_DATE#" => $newOrder->getDateInsert()->toString(),
                                    "#ORDER_ID#" => $newOrder->getField("ACCOUNT_NUMBER")
                                ]),
                                "order" => [
                                    "id" => $newOrder->getField("ACCOUNT_NUMBER"),
                                    "total_price" => $newOrder->getPrice(),
                                    "tax_price" => $newOrder->getTaxPrice(),
                                    "delivery_price" => $newOrder->getDeliveryPrice()
                                ],
                                "products" => $products
							];
						} else {
							//MESSAGE//
							$results["RESULTS"][] = [
								"status" => false,
								"text" => Loc::getMessage("QUICK_ORDER_AJAX_ORDER_CREATE_ERROR")
							];
						}
						unset($orderId, $newOrder);
					}
					unset($data);
				}
				unset($objectsItemsList);

				if(!empty($itemsList)) {
					//CREATE_ORDER//
					$newOrder = Sale\Order::create($siteId, $registeredUserID);

					//ORDER_PERSON_TYPE//
					$newOrder->setPersonTypeId($arPersonTypeId);
					
					//ORDER_SET_BASKET//
					$newBasket = Sale\Basket::create($siteId);
					foreach($itemsList as $basketItem) {
						$item = $newBasket->createItem("catalog", $basketItem["ID"]);
						$item->setFields(array(
							"QUANTITY" => $basketItem["QUANTITY"],
							"CURRENCY" => $basketItem["CURRENCY"],
							"LID" => $basketItem["LID"],
							"PRODUCT_PROVIDER_CLASS" => $basketItem["PRODUCT_PROVIDER_CLASS"],
							"XML_ID" => $basketItem["XML_ID"]
						));
						$newBasket->save();

						$basketPropertyCollection = $item->getPropertyCollection();
						$basketPropertyCollection->setProperty($basketItem["PROPERTIES"]);
						$basketPropertyCollection->save();
					}
					unset($basketItem);
					
					$newOrder->setBasket($newBasket);
					unset($newBasket);

					//ORDER_SET_DELIVERY//
					$shipmentCollection = $newOrder->getShipmentCollection();
					$shipment = $shipmentCollection->createItem();
					$shipment->setFields(array(
						"DELIVERY_ID" => $arDeliveryService["ID"],
						"DELIVERY_NAME" => $arDeliveryService["NAME"],
					));
					
					$shipmentItemCollection = $shipment->getShipmentItemCollection();
					foreach($newOrder->getBasket() as $item) {
						$shipmentItem = $shipmentItemCollection->createItem($item);
						$shipmentItem->setQuantity($item->getQuantity());
					}
					unset($shipmentItem, $item, $shipmentItemCollection);
					
					unset($shipment, $shipmentCollection);
					
					//ORDER_SET_PROPERTIES_FIELDS//
					setOrderPropsFields($newOrder, $name, $phone, $email, $comments, $locCode, false);
					
					//SAVE_ORDER//
					$newOrder->doFinalAction(true);

					$newOrder->save();

					$orderId = $newOrder->GetId();
					if($orderId > 0) {
                        $products = getOrderProducts($newOrder);

						//MESSAGE//
						$results["RESULTS"][] = [
							"status" => true,
							"text" => Loc::getMessage("QUICK_ORDER_AJAX_ORDER_CREATE_SUCCESS", [
                                "#ORDER_DATE#" => $newOrder->getDateInsert()->toString(),
                                "#ORDER_ID#" => $newOrder->getField("ACCOUNT_NUMBER")
                            ]),
                            "order" => [
                                "id" => $newOrder->getField("ACCOUNT_NUMBER"),
                                "total_price" => $newOrder->getPrice(),
                                "tax_price" => $newOrder->getTaxPrice(),
                                "delivery_price" => $newOrder->getDeliveryPrice()
                            ],
                            "products" => $products
                        ];
					} else {
						//MESSAGE//
						$results["RESULTS"][] = [
							"status" => false,
							"text" => Loc::getMessage("QUICK_ORDER_AJAX_ORDER_CREATE_ERROR")
						];
					}
					unset($orderId, $newOrder);
				}
				
				$response->flush(Json::encode($results));
			}
			unset($itemsList);
		}
	}
}