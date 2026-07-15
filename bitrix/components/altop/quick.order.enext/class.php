<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Application,
	Bitrix\Main\SystemException;

class QuickOrder extends CBitrixComponent {	
	protected $errors = array();
	
	public function onIncludeComponentLang() {
		Loc::loadMessages(__FILE__);
	}
	
	public function onPrepareComponentParams($params) {
		$params["MODE"] = isset($params["MODE"]) ? (string)$params["MODE"] : "PRODUCT";

		$params["PRODUCT_ID"] = isset($params["PRODUCT_ID"]) ? (int)$params["PRODUCT_ID"] : 0;
		if($params["MODE"] == "PRODUCT" && !$params["PRODUCT_ID"])
			$this->errors[] = Loc::getMessage("QUICK_ORDER_CLASS_REQUIRED_PARAMETER", array("#PARAM#" => "PRODUCT_ID"));
		
		$params["CONTAINER_ID"] = isset($params["CONTAINER_ID"]) ? (string)$params["CONTAINER_ID"] : "";
		if(!$params["CONTAINER_ID"])
			$this->errors[] = Loc::getMessage("QUICK_ORDER_CLASS_REQUIRED_PARAMETER", array("#PARAM#" => "CONTAINER_ID"));

		$params["CONTAINER_CLASS"] = isset($params["CONTAINER_CLASS"]) ? (string)$params["CONTAINER_CLASS"] : "";
		
		$params["DEFAULT_DISPLAY"] = isset($params["DEFAULT_DISPLAY"]) ? (bool)$params["DEFAULT_DISPLAY"] : true;

		$params["QUANTITY_ID"] = isset($params["QUANTITY_ID"]) ? (string)$params["QUANTITY_ID"] : "";
		
		$params["PRODUCT_PROPS_VARIABLE"] = isset($params["PRODUCT_PROPS_VARIABLE"]) ? (string)$params["PRODUCT_PROPS_VARIABLE"] : "";
		$params["PARTIAL_PRODUCT_PROPERTIES"] = isset($params["PARTIAL_PRODUCT_PROPERTIES"]) && $params["PARTIAL_PRODUCT_PROPERTIES"] == "Y" ? "Y" : "N";		
		$params["CART_PROPERTIES"] = isset($params["CART_PROPERTIES"]) ? strtr(base64_encode(serialize($params["CART_PROPERTIES"])), "+/=", "-_,") : "";
		$params["BASKET_PROPS_ID"] = isset($params["BASKET_PROPS_ID"]) ? (string)$params["BASKET_PROPS_ID"] : "";
		
		$params["OFFERS_CART_PROPERTIES"] = isset($params["OFFERS_CART_PROPERTIES"]) ? strtr(base64_encode(serialize($params["OFFERS_CART_PROPERTIES"])), "+/=", "-_,") : "";
		$params["BASKET_SKU_PROPS"] = isset($params["BASKET_SKU_PROPS"]) ? $params["BASKET_SKU_PROPS"] : "";

		$params["OBJECT_ID"] = isset($params["OBJECT_ID"]) ? (int)$params["OBJECT_ID"] : 0;
		
		global $USER;		
		$arSettings = CEnext::GetFrontParametrsValues(SITE_ID);
		$params["USE_CAPTCHA"] = !$USER->IsAuthorized() && $arSettings["FORMS_USE_CAPTCHA"] == "Y" ? true : false;
		
		$params["USER_CONSENT"] = $arSettings["FORMS_USER_CONSENT"] == "Y" ? true : false;
		$params["USER_CONSENT_ID"] = $arSettings["FORMS_USER_CONSENT_ID"];
		$params["USER_CONSENT_IS_CHECKED"] = $arSettings["FORMS_USER_CONSENT_IS_CHECKED"];
		$params["USER_CONSENT_IS_LOADED"] = $arSettings["FORMS_USER_CONSENT_IS_LOADED"];
		
		$params["DEFAULT_COUNTRY"] = strtolower($arSettings["FORMS_DEFAULT_COUNTRY"]);
		$params["PHONE_MASK"] = $arSettings["FORMS_PHONE_MASK"] == "Y" ? true : false;
		
		return $params;
	}
	
	protected function checkModules() {
		if(!Loader::includeModule("catalog"))
			throw new SystemException(Loc::getMessage("QUICK_ORDER_CLASS_MODULE_NOT_INSTALLED", array("#NAME#" => "catalog")));
	}
	
	protected function formatResult() {
		if($this->errors)
			throw new SystemException(current($this->errors));

		$this->arResult["MODE"] = $this->arParams["MODE"];
		$this->arResult["PRODUCT_ID"] = $this->arParams["PRODUCT_ID"];		
		$this->arResult["CONTAINER_ID"] = $this->arParams["CONTAINER_ID"];
		$this->arResult["CONTAINER_CLASS"] = $this->arParams["CONTAINER_CLASS"];
		$this->arResult["DEFAULT_DISPLAY"] = $this->arParams["DEFAULT_DISPLAY"];
		$this->arResult["QUANTITY_ID"] = $this->arParams["QUANTITY_ID"];
		$this->arResult["PRODUCT_PROPS_VARIABLE"] = $this->arParams["PRODUCT_PROPS_VARIABLE"];
		$this->arResult["PARTIAL_PRODUCT_PROPERTIES"] = $this->arParams["PARTIAL_PRODUCT_PROPERTIES"];
		$this->arResult["CART_PROPERTIES"] = $this->arParams["CART_PROPERTIES"];
		$this->arResult["BASKET_PROPS_ID"] = $this->arParams["BASKET_PROPS_ID"];		
		$this->arResult["OFFERS_CART_PROPERTIES"] = $this->arParams["OFFERS_CART_PROPERTIES"];
		$this->arResult["BASKET_SKU_PROPS"] = $this->arParams["BASKET_SKU_PROPS"];
		$this->arResult["OBJECT_ID"] = $this->arParams["OBJECT_ID"];
		$this->arResult["USE_CAPTCHA"] = $this->arParams["USE_CAPTCHA"];
		$this->arResult["USER_CONSENT"] = $this->arParams["USER_CONSENT"];
		$this->arResult["USER_CONSENT_ID"] = $this->arParams["USER_CONSENT_ID"];
		$this->arResult["USER_CONSENT_IS_CHECKED"] = $this->arParams["USER_CONSENT_IS_CHECKED"];
		$this->arResult["USER_CONSENT_IS_LOADED"] = $this->arParams["USER_CONSENT_IS_LOADED"];
		$this->arResult["DEFAULT_COUNTRY"] = $this->arParams["DEFAULT_COUNTRY"];
		$this->arResult["PHONE_MASK"] = $this->arParams["PHONE_MASK"];
		
		if($this->arParams["MODE"] == "PRODUCT") {
			$this->arResult["FIELDS"] = array(				
				"PHONE" => array(
					"CODE" => "PHONE",
					"TYPE" => "TEXT",
					"REQUIRED" => "Y",
					"ICON" => "icon-phone"
				)
			);
		} else {
			$this->arResult["FIELDS"] = array(
				"NAME" => array(
					"CODE" => "NAME",
					"TYPE" => "TEXT",
					"REQUIRED" => "N",
					"ICON" => "icon-user"
				),
				"PHONE" => array(
					"CODE" => "PHONE",
					"TYPE" => "TEXT",
					"REQUIRED" => "Y",
					"ICON" => "icon-phone"
				),
				"EMAIL" => array(
					"CODE" => "EMAIL",
					"TYPE" => "TEXT",
					"REQUIRED" => "N",
					"ICON" => "icon-mail"
				),
				"COMMENTS" => array(
					"CODE" => "COMMENTS",
					"TYPE" => "TEXTAREA",
					"REQUIRED" => "N",
					"ICON" => "icon-comment"
				)
			);
		}
	}
	
	public function executeComponent() {
		try {
			$this->checkModules();
			$this->formatResult();
			$this->includeComponentTemplate();
		} catch(SystemException $e) {
			ShowError($e->getMessage());
		}
	}
}