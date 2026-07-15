<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Loader,
	Bitrix\Sale;

class SaleBasketLineComponent extends CBitrixComponent {
	public function onPrepareComponentParams($arParams) {
		//common
		$arSettings = CEnext::GetFrontParametrsValues(SITE_ID);
		
		if($arSettings['DISABLE_DELAY'] != 'Y')
			$arParams['SHOW_DELAY'] = 'Y';

		if($arSettings['DISABLE_BASKET'] != 'Y')
			$arParams['SHOW_BASKET'] = 'Y';

		if($arSettings['TOP_PANEL_CONTACTS'] == 'Y')
			$arParams['SHOW_CONTACTS'] = 'Y';

		$arParams['BASKET_VIEW'] = $arSettings['TOP_PANEL_BASKET_VIEW'];
		
		$arParams['PATH_TO_BASKET'] = trim($arParams['PATH_TO_BASKET']);
		if($arParams['PATH_TO_BASKET'] == '')
			$arParams['PATH_TO_BASKET'] = SITE_DIR.'personal/cart/';
		
		if($arParams['AJAX'] != 'Y')
			$arParams['AJAX'] = 'N';

		return $arParams;
	}

	protected function getUserFilter() {
		$fUserID = (int)$this->currentFuser;
		return ($fUserID > 0)
			? array("FUSER_ID" => $fUserID, "LID" => SITE_ID, "ORDER_ID" => "NULL")
			: null; // no basket for current user
	}
	
	public function executeComponent() {
		if(!Loader::includeModule('sale')) {
			ShowError(GetMessage('SALE_MODULE_NOT_INSTALL'));
			return;
		}

		$this->loadCurrentFuser();
		
		if($this->arParams['SHOW_BASKET'] == 'Y')
			$this->arResult['CART']['NUM_PRODUCTS'] = Sale\BasketComponentHelper::getFUserBasketQuantity($this->currentFuser, SITE_ID);
		
		if($this->arParams['SHOW_DELAY'] == 'Y')
			$this->arResult['DELAY']['NUM_PRODUCTS'] = $this->getBasketDelayQuantity();
		
		//output
		if($this->arParams['AJAX'] == 'Y')
			$this->includeComponentTemplate('ajax_template');
		else
			$this->includeComponentTemplate();
	}

	private function getBasketDelayQuantity() {
		if(!($arFilter = $this->getUserFilter()))
			return 0;

		$arFilter['LID'] = SITE_ID;
		$arFilter['CAN_BUY'] = "Y";
		$arFilter['DELAY'] = "Y";

		$dbItems = CSaleBasket::GetList(
			array("NAME" => "ASC", "ID" => "ASC"),
			$arFilter,
			false,
			false,
			array(
				"ID", "NAME", "CALLBACK_FUNC", "MODULE", "PRODUCT_ID", "QUANTITY", "DELAY", "CAN_BUY",
				"PRICE", "WEIGHT", "DETAIL_PAGE_URL", "CURRENCY", "VAT_RATE", "CATALOG_XML_ID", "MEASURE_NAME",
				"PRODUCT_XML_ID", "SUBSCRIBE", "DISCOUNT_PRICE", "PRODUCT_PROVIDER_CLASS", "TYPE", "SET_PARENT_ID", "BASE_PRICE",
				"PRODUCT_PRICE_ID", 'CUSTOM_PRICE'
			)
		);
		$arBasketItems = array();
		while($arItem = $dbItems->GetNext(true, false)) {
			if(CSaleBasketHelper::isSetItem($arItem))
				continue;
			$arBasketItems[] = $arItem;
		}

		return count($arBasketItems);
	}

	protected function loadCurrentFuser() {
		$this->currentFuser = Sale\Fuser::getId(true);
	}
}