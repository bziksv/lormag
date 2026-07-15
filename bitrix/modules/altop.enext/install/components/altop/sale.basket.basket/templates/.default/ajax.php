<?define("STOP_STATISTICS", true);
define("NOT_CHECK_PERMISSIONS", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader,
	Bitrix\Main\Config\Option,	
	Bitrix\Main\Localization\Loc,		
	Bitrix\Main\Web\Json,
	Bitrix\Sale;

Loc::loadMessages(__FILE__);

$context = Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

if($request->isAjaxRequest()) {
	$action = $request->getPost("action");
	if($action == "checkOutObject") {
		$objectId = (int)$request->get("objectId");
		if($objectId > 0) {
			$ids = $request->get("ids");

			$siteId = $request->get("siteId") ?: SITE_ID;			
			
			$newSiteId = false;
			if(Loader::includeModule("iblock")) {
				$rsElement = CIBlockElement::GetList(array(), array("ID" => $objectId), false, false, array("ID", "IBLOCK_ID", "PROPERTY_SITE_ID"));
				if($arElement = $rsElement->GetNext()) {
					if(!empty($arElement["PROPERTY_SITE_ID_VALUE"]))
						$newSiteId = $arElement["PROPERTY_SITE_ID_VALUE"];
				}
				unset($arElement, $rsElement);
			}
			
			if($newSiteId) {
				$basketUserId = Sale\Fuser::getId();
		
				//NEW_BASKET//
				$newBasket = Sale\Basket::loadItemsForFUser($basketUserId, $newSiteId)->getOrderableItems();
				if(!$newBasket->isEmpty()) {
					foreach($newBasket as $basketItem) {
						CSaleBasket::Delete($basketItem->getId());
					}
					unset($basketItem);

					$newBasket->save();
				}
				
				//BASKET//
				$basket = Sale\Basket::loadItemsForFUser($basketUserId, $siteId)->getOrderableItems();
				if(!$basket->isEmpty()) {
					$fuser = new Sale\Discount\Context\Fuser($basket->getFUserId());
					$discounts = Sale\Discount::buildFromBasket($basket, $fuser);
					$r = $discounts->calculate();		
					$result = $r->getData();
					if(isset($result["BASKET_ITEMS"])) {
						$r = $basket->applyDiscount($result["BASKET_ITEMS"]);
					}
				
					foreach($basket as $basketItem) {
						if(in_array($basketItem->getId(), $ids)) {
							$newBasketNewItem = $newBasket->createItem("catalog", $basketItem->getProductId());
							
							$newBasketNewItem->setFields(array(
								"QUANTITY" => $basketItem->getQuantity(),
								"CURRENCY" => $basketItem->getField("CURRENCY"),
								"LID" => $newSiteId,
								"PRODUCT_PROVIDER_CLASS" => $basketItem->getField("PRODUCT_PROVIDER_CLASS")
							));

							if($basketItem->getBasePrice() != $basketItem->getPrice()) {
								$newBasketNewItem->setFields(array(
									"PRICE" => $basketItem->getPrice(),
									"CUSTOM_PRICE" => "Y"
								));
							}
							
							$newBasketNewItemPropertyCollection = $newBasketNewItem->getPropertyCollection();
							$newBasketNewItemPropertyCollection->setProperty($basketItem->getPropertyCollection()->getPropertyValues());
						}
					}
					unset($basketItem);
					
					$saveResult = $newBasket->save();
				}
		
				//JSON_RESULT//
				echo Json::encode(array(
					"status" => $saveResult && $saveResult->isSuccess() ? true : false,
					"siteId" => $newSiteId
				));
			}
		}
	} elseif($action == "orderSaved") {
		$orderNumber = $request->get("orderNumber");
		$objectId = (int)$request->get("objectId");
		if(!empty($orderNumber) && $objectId > 0) {
			$siteId = $request->get("siteId") ?: SITE_ID;
			$languageId = $request->get("languageId") ?: LANGUAGE_ID;
			
			//LOAD_ORDER//
			$arOrder = Sale\Order::loadByAccountNumber($orderNumber);
			$arOrderBasket = $arOrder->getBasket();
			$arOrderPropertyCollection = $arOrder->getPropertyCollection();

			$arOrderBasketList = "";
			if(!$arOrderBasket->isEmpty()) {
				$arOrderBasketTextList = $arOrderBasket->getListOfFormatText();
				if(!empty($arOrderBasketTextList)) {
					foreach($arOrderBasketTextList as $basketItem) {
						$arOrderBasketList .= $basketItem."<br/>";
					}
					unset($basketItem);
				}
				unset($arOrderBasketTextList);
			}			
			
			//OBJECT//
			if(Loader::includeModule("iblock")) {
				$rsElement = CIBlockElement::GetList(array(), array("ID" => $objectId), false, false, array("ID", "IBLOCK_ID", "NAME", "PROPERTY_PHONE_SMS", "PROPERTY_EMAIL_EMAIL", "PROPERTY_SITE_ID"));
				if($arElement = $rsElement->GetNext()) {
					$arObject = array(
						"NAME" => $arElement["NAME"],
						"PHONE_SMS" => $arElement["PROPERTY_PHONE_SMS_VALUE"],
						"EMAIL_EMAIL" => $arElement["PROPERTY_EMAIL_EMAIL_VALUE"],
						"SITE_ID" => $arElement["PROPERTY_SITE_ID_VALUE"]
					);
				}
				unset($arElement, $rsElement);
			}

			//ORDER_PERSON_TYPE//
			$arPersonTypeId = $arOrder->getPersonTypeId();

			//ORDER_PROPS_GROUP//
			$arPropsGroupId = 1;
			$rsPropsGroup = CSaleOrderPropsGroup::GetList(array("ID" => "ASC"), array("PERSON_TYPE_ID" => $arPersonTypeId), false, array("nTopCount" => 1), array("ID"));
			if($arPropGroup = $rsPropsGroup->Fetch()) {
				$arPropsGroupId = $arPropGroup["ID"];
			}
			unset($arPropGroup, $rsPropsGroup);

			//ORDER_PROP_OBJECT_ID//
			$arOrderProp = CSaleOrderProps::GetList(array(), array("PERSON_TYPE_ID" => $arPersonTypeId, "CODE" => "OBJECT_ID"), false, false, array("ID"))->Fetch();
			if(empty($arOrderProp)) {
				$arFields = array(
					"PERSON_TYPE_ID" => $arPersonTypeId,
					"NAME" => Loc::getMessage("SBB_ORDER_PROP_OBJECT_ID"),
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

			//ORDER_PROP_OBJECT_NAME//
			$arOrderProp = CSaleOrderProps::GetList(array(), array("PERSON_TYPE_ID" => $arPersonTypeId, "CODE" => "OBJECT_NAME"), false, false, array("ID"))->Fetch();
			if(empty($arOrderProp)) {
				$arFields = array(
					"PERSON_TYPE_ID" => $arPersonTypeId,
					"NAME" => Loc::getMessage("SBB_ORDER_PROP_OBJECT_NAME"),
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
			unset($arOrderProp);
				
			//ORDER_PROPERTY_COLLECTION//
			foreach($arOrderPropertyCollection as $prop) {
				if($prop->getField("CODE") == "OBJECT_ID")
					$prop->setValue($objectId);
				elseif($prop->getField("CODE") == "OBJECT_NAME" && !empty($arObject["NAME"]))
					$prop->setValue($arObject["NAME"]);
			}
			unset($prop);

			$arOrder->save();

			//ORDER_ADD_EVENT//
			$eventName = "SALE_NEW_ORDER_OBJECT";

			//ORDER_ADD_EVENT_TYPE//
			$arEvent = CEventType::GetByID($eventName, $languageId)->Fetch();
			if(empty($arEvent)) {
				$et = new CEventType;
				$arEventFields = array(
					"LID" => $languageId,
					"EVENT_NAME" => $eventName,
					"NAME" => Loc::getMessage("SBB_ORDER_EVENT_NAME"),
					"DESCRIPTION" => Loc::getMessage("SBB_ORDER_EVENT_DESCRIPTION")
				);
				$et->Add($arEventFields);
				unset($arEventFields, $et);
			}
			unset($arEvent);

			//ORDER_ADD_EVENT_MESSAGE//
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
					"SUBJECT" => Loc::getMessage("SBB_ORDER_EVENT_MESSAGE_SUBJECT"),
					"BODY_TYPE" => "html",
					"MESSAGE" => Loc::getMessage("SBB_ORDER_EVENT_MESSAGE_MESSAGE")
				);
				$em->Add($arMessFields);
				unset($arMessFields, $em);
			}
			unset($arMess);

			//ORDER_SEND_EVENT//
			$fields = array(
				"ORDER_ID" => $arOrder->getField("ACCOUNT_NUMBER"),
				"ORDER_REAL_ID" => $arOrder->getField("ID"),
				"ORDER_DATE" => $arOrder->getDateInsert()->toString(),				
				"PRICE" => SaleFormatCurrency($arOrder->getPrice(), $arOrder->getCurrency()),
				"ORDER_LIST" => $arOrderBasketList,
				"NAME" => $arOrderPropertyCollection->getPayerName()->getValue(),
				"PHONE" => $arOrderPropertyCollection->getPhone()->getValue(),
				"EMAIL" => $arOrderPropertyCollection->getUserEmail()->getValue(),
				"COMMENTS" => $arOrder->getField("USER_DESCRIPTION"),
				"OBJECT_PHONE_SMS" => !empty($arObject["PHONE_SMS"]) ? $arObject["PHONE_SMS"] : "",
				"OBJECT_EMAIL_EMAIL" => !empty($arObject["EMAIL_EMAIL"]) ? $arObject["EMAIL_EMAIL"] : "",
				"BCC" => Option::get("sale", "order_email"),
				"SALE_EMAIL" => Option::get("sale", "order_email")
			);
				
			Bitrix\Main\Mail\Event::send(array(
				"EVENT_NAME" => $eventName,
				"LID" => $siteId,
				"C_FIELDS" => $fields,
				"LANGUAGE_ID" => Sale\Notify::getOrderLanguageId($arOrder)
			));
			unset($fields);			

			//ORDER_UPDATE_LID//
			if(!empty($arObject["SITE_ID"]) && $arOrder->getSiteId() == $arObject["SITE_ID"]) {
				$arOrder->setField("LID", $siteId);
				$arOrder->save();
			}

			//ORDER_UPDATE_USER//
			global $USER;
			$rsUser = $USER->GetByID($arOrder->getUserId());
			if($arUser = $rsUser->Fetch()) {
				if(!empty($arObject["SITE_ID"]) && $arUser["LID"] == $arObject["SITE_ID"])
					$USER->Update($arUser["ID"], array("LID" => $siteId));
			}
			unset($arUser, $rsUser);
			
			//ORDER_BASKET_PRODUCT_IDS//
			if(!$arOrderBasket->isEmpty()) {
				foreach($arOrderBasket as $basketItem) {
					$productIds[] = $basketItem->getProductId();
				}
				unset($basketItem);
			}
			
			//BASKET//
			$basketUserId = Sale\Fuser::getId();

			$quantity = $_SESSION["SALE_USER_BASKET_QUANTITY"][$siteId][$basketUserId];
			$delayedItemsCount = 0;
			
			$basket = Sale\Basket::loadItemsForFUser($basketUserId, $siteId)->getBasket();
			if(!$basket->isEmpty()) {
				foreach($basket as $basketItem) {
					if($basketItem->canBuy()) {
						if(!$basketItem->isDelay() && is_array($productIds) && in_array($basketItem->getProductId(), $productIds)) {
							$ids[] = $basketItem->getId();

							CSaleBasket::Delete($basketItem->getId());
							$quantity--;
						} elseif($basketItem->isDelay()) {
							$delayedItemsCount++;
						}
					}
				}
				unset($basketItem);

				$basket->save();

				$_SESSION["SALE_USER_BASKET_QUANTITY"][$siteId][$basketUserId] = $quantity;
			}
			
			//JSON_RESULT//
			echo Json::encode(array(
				"DELETED_BASKET_ITEMS" => $ids,
				"BASKET_DATA" => array(
					"ORDERABLE_BASKET_ITEMS_COUNT" => $quantity,
					"DELAYED_BASKET_ITEMS_COUNT" => $delayedItemsCount,
					"ERROR_MESSAGE" => $quantity <= 0 && $delayedItemsCount <= 0 ? Loc::getMessage("SBB_EMPTY_BASKET") : ""
				)
			));
		}
	}
}