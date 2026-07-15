<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class CGeoDelivery extends CBitrixComponent{
	public function getPersonTypes($siteId) {
		return Bitrix\Sale\PersonType::load($siteId);
	}

	public function getRatioMeasure($productId) {
		$measureList = Bitrix\Catalog\ProductTable::getCurrentRatioWithMeasure($productId);
		
		return array(
			"RATIO" => $measureList[$productId]["RATIO"],
			"MEASURE" => $measureList[$productId]["MEASURE"]
		);
	}

	public function needCalcAllProductsInput($siteId) {
		$basket = Bitrix\Sale\Basket::loadItemsForFUser(Bitrix\Sale\Fuser::getId(), $siteId)->getOrderableItems();
		if(!$basket->isEmpty()) {
			foreach($basket as $basketItem) {
				$productIds[] = $basketItem->getProductId();
			}
			unset($basketItem);
		}

		$objectIds = array();
		if(!empty($productIds))
			$objectIds = CEnext::getObjectIdsFromProductIds($productIds);
		
		return empty($objectIds) ? "Y" : "N";
	}
	
	public function getDeliveryItems($arParams) {
		//DELIVERY_ITEMS//
		$obCache = Bitrix\Main\Data\Cache::createInstance();
		$cache_id = $arParams["SITE_ID"].$arParams["CUSTOM_SITE_ID"].$arParams["USER_ID"].$arParams["PRODUCT_ID"].$arParams["PRODUCT_QUANTITY"].$arParams["LOCATION_ID"].$arParams["PERSON_TYPE_ID"].$arParams["CALC_ALL_PRODUCTS"];
		if($obCache->initCache($arParams["CACHE_TIME"], $cache_id, $arParams["SITE_ID"]."/altop/geo.delivery.enext/")) {
			$deliveryItems = $obCache->GetVars();
		} elseif($obCache->startDataCache()) {
			$deliveryItems = array();
			
			//COUPONS//
			Bitrix\Sale\DiscountCouponsManager::init();
			
			//ORDER//
			$order = Bitrix\Sale\Order::create($arParams["SITE_ID"], $arParams["USER_ID"] > 0 ? $arParams["USER_ID"] : CSaleUser::GetAnonymousUserID());

			//ORDER_PERSON_TYPE//
			$order->setPersonTypeId($arParams["PERSON_TYPE_ID"]);
			
			//ORDER_BASKET//
			if($arParams["CALC_ALL_PRODUCTS"] == "Y") {
				$basket = Bitrix\Sale\Basket::loadItemsForFUser(Bitrix\Sale\Fuser::getId(), $arParams["SITE_ID"])->getOrderableItems();
			} else {
				$basket = Bitrix\Sale\Basket::create($arParams["SITE_ID"]);
			}

			if($item = $basket->getExistsItem("catalog", $arParams["PRODUCT_ID"])) {
				$item->setField("QUANTITY", $item->getQuantity() + $arParams["PRODUCT_QUANTITY"]);
			} else {
				$item = $basket->createItem("catalog", $arParams["PRODUCT_ID"]);
				$item->setFields(array(
					"QUANTITY" => $arParams["PRODUCT_QUANTITY"],
					"CURRENCY" => Bitrix\Currency\CurrencyManager::getBaseCurrency(),
					"LID" => $arParams["SITE_ID"],
					"PRODUCT_PROVIDER_CLASS" => "CCatalogProductProvider"
				));
			}
			unset($item);
			
			$order->setBasket($basket);

			if(!empty($arParams["CUSTOM_SITE_ID"]))
				$order->setField("LID", $arParams["CUSTOM_SITE_ID"]);
			
			//ORDER_PROPERTIES//
			if($arParams["LOCATION_ID"] > 0) {
				$propertyCollection = $order->getPropertyCollection();
					
				$locCode = CSaleLocation::getLocationCODEbyID($arParams["LOCATION_ID"]);
				
				$rsLocZip = CSaleLocation::GetLocationZIP($arParams["LOCATION_ID"]);
				if($arLocZip = $rsLocZip->Fetch()) {
					if(!empty($arLocZip["ZIP"]))
						$locZip = $arLocZip["ZIP"];
				}
				unset($arLocZip, $rsLocZip);
				
				$order->setFields(array(
					"DELIVERY_LOCATION" => !empty($locCode) ? $locCode : $arParams["LOCATION_ID"],
					"DELIVERY_LOCATION_ZIP" => !empty($locZip) ? $locZip : ""
				));
				
				$propLocation = $propertyCollection->getDeliveryLocation();
				if(!empty($propLocation))
					$propLocation->setValue(!empty($locCode) ? $locCode : $arParams["LOCATION_ID"]);
				
				if(!empty($locZip)) {
					$propLocationZip = $propertyCollection->getDeliveryLocationZip();
					if(!empty($propLocationZip))
						$propLocationZip->setValue($locZip);
				}
			}
			
			//ORDER_SHIPMENT//
			$shipmentCollection = $order->getShipmentCollection();
			$shipment = $shipmentCollection->createItem();
			$shipmentItemCollection = $shipment->getShipmentItemCollection();
			$shipment->setField("CURRENCY", $order->getCurrency());

			foreach($order->getBasket() as $item) {
				$shipmentItem = $shipmentItemCollection->createItem($item);
				$shipmentItem->setQuantity($item->getQuantity());
			}
			unset($item);
			
			//ORDER_DELIVERY//
			$arDeliveryServiceAll = Bitrix\Sale\Delivery\Services\Manager::getRestrictedObjectsList($shipment);
			if(!empty($arDeliveryServiceAll)) {
				foreach($arDeliveryServiceAll as $deliveryId => $deliveryObj) {
					$deliveryName = $deliveryObj->isProfile() ? $deliveryObj->getNameWithParent() : $deliveryObj->getName();
					 
					$shipment->setFields(array(
						"DELIVERY_ID" => $deliveryId
					));
					$shipment->calculateDelivery();

					$order->doFinalAction(true);
					
					$calcResult = $deliveryObj->calculate($shipment);
					if($calcResult->isSuccess()) {
						$deliveryItems[$deliveryId]["ID"] = $deliveryId;
						$deliveryItems[$deliveryId]["NAME"] = $deliveryName;
						$deliveryItems[$deliveryId]["DESCRIPTION"] = $deliveryObj->getDescription();
						$deliveryItems[$deliveryId]["LOGOTIP"] = !empty($deliveryObj->getLogotip()) ? CFile::GetFileArray($deliveryObj->getLogotip()) : "";
						$deliveryPrice = Bitrix\Sale\PriceMaths::roundPrecision($calcResult->getPrice());
						$deliveryItems[$deliveryId]["PRICE"] = $deliveryPrice;
						$deliveryItems[$deliveryId]["PRICE_FORMATED"] = SaleFormatCurrency($deliveryPrice, $order->getCurrency());
						$deliveryItems[$deliveryId]["PERIOD_TEXT"] = $calcResult->getPeriodDescription();
					}
				}
				unset($deliveryObj);
			}
			
			if(!empty($deliveryItems)) {
				Bitrix\Main\Type\Collection::sortByColumn($deliveryItems, array("PRICE" => SORT_ASC));	
				$obCache->endDataCache($deliveryItems);
			} else {
				$obCache->abortDataCache();
			}
		}
		
		return $deliveryItems;
	}
}?>