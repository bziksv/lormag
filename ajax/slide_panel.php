<?define("STOP_STATISTICS", true);
define("NOT_CHECK_PERMISSIONS", true);

if(!empty($_REQUEST["REQUEST_URI"]))
	$_SERVER["REQUEST_URI"] = $_REQUEST["REQUEST_URI"];

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
if($request->isAjaxRequest()) {
	$action = $request->getPost("action");
	switch($action) {		
		//CALLBACK//
		case "callback":
			$APPLICATION->IncludeComponent("bitrix:main.include", "",
				array(
					"AREA_FILE_SHOW" => "file",
					"PATH" => SITE_DIR."include/slide_panel_callback.php"
				)
			);
			break;
		//CALLBACK_OBJECTS//
		case "callback_objects":
			$APPLICATION->IncludeComponent("bitrix:main.include", "",
				array(
					"AREA_FILE_SHOW" => "file",
					"PATH" => SITE_DIR."include/slide_panel_callback_objects.php"
				)
			);
			break;
		//LOGIN//
		case "login":
			$APPLICATION->IncludeComponent("bitrix:main.include", "",
				array(
					"AREA_FILE_SHOW" => "file",
					"PATH" => SITE_DIR."include/slide_panel_login.php"
				)
			);
			break;
		//ASK_PRICE//
		case "ask_price":
			$APPLICATION->IncludeComponent("bitrix:main.include", "",
				array(
					"AREA_FILE_SHOW" => "file",
					"PATH" => SITE_DIR."include/slide_panel_ask_price.php"
				)
			);
			break;
		//ASK_PRICE_OBJECTS//
		case "ask_price_objects":
			$APPLICATION->IncludeComponent("bitrix:main.include", "",
				array(
					"AREA_FILE_SHOW" => "file",
					"PATH" => SITE_DIR."include/slide_panel_ask_price_objects.php"
				)
			);
			break;
		//NOT_AVAILABLE//
		case "not_available":
			$APPLICATION->IncludeComponent("bitrix:main.include", "",
				array(
					"AREA_FILE_SHOW" => "file",
					"PATH" => SITE_DIR."include/slide_panel_not_available.php"
				)
			);
			break;
		//NOT_AVAILABLE_OBJECTS//
		case "not_available_objects":
			$APPLICATION->IncludeComponent("bitrix:main.include", "",
				array(
					"AREA_FILE_SHOW" => "file",
					"PATH" => SITE_DIR."include/slide_panel_not_available_objects.php"
				)
			);
			break;
		//QUICK_ORDER//
		case "quick_order":
			$APPLICATION->IncludeComponent("altop:quick.order.enext", "",
				array(
					"MODE" => "CART",					
					"CONTAINER_ID" => "bx-ordercart-quick-order",
					"CONTAINER_CLASS" => "slide-panel__form"
				),
				false,
				array("HIDE_ICONS" => "Y")
			);
			break;
		//QUICK_ORDER_OBJECTS//
		case "quick_order_objects":
			$APPLICATION->IncludeComponent("altop:quick.order.enext", "",
				array(
					"MODE" => "OBJECTS",					
					"CONTAINER_ID" => "bx-ordercart-quick-order",
					"CONTAINER_CLASS" => "slide-panel__form"
				),
				false,
				array("HIDE_ICONS" => "Y")
			);
			break;
		//CART//
		case "cart":
			$APPLICATION->IncludeComponent("bitrix:main.include", "",
				array(
					"AREA_FILE_SHOW" => "file",
					"PATH" => SITE_DIR."include/slide_panel_cart.php"
				)
			);
			break;
	}
	
	if(Bitrix\Main\Loader::includeModule("iblock")) {
		$content = ob_get_contents();
		ob_end_clean();
		
		Bitrix\Iblock\Component\Base::sendJsonAnswer(array(
			"content" => $content
		));
	}
}