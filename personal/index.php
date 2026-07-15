<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Личный кабинет");?>

<?$APPLICATION->IncludeComponent("bitrix:sale.personal.section", ".default", 
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"PATH_TO_PAYMENT" => "/personal/order/payment/",
		"PATH_TO_BASKET" => "/personal/cart/",
		"PATH_TO_CATALOG" => "/catalog/",
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "/personal/",
		"SAVE_IN_SESSION" => "Y",
		"ACTIVE_DATE_FORMAT" => "d.m.Y",
		"CUSTOM_SELECT_PROPS" => array(),
		"ORDER_HIDE_USER_INFO" => array(
			0 => "0"
		),
		"PROP_1" => array(),
		"PROP_2" => array(),
		"ORDER_HISTORIC_STATUSES" => array(
			0 => "F"
		),
		"ORDER_RESTRICT_CHANGE_PAYSYSTEM" => array(
			0 => "0"
		),
		"ORDER_DEFAULT_SORT" => "STATUS",
		"ORDER_REFRESH_PRICES" => "N",
		"ORDER_DISALLOW_CANCEL" => "N",
		"ALLOW_INNER" => "N",
		"ONLY_INNER_FULL" => "N",
		"NAV_TEMPLATE" => "arrows",
		"ORDERS_PER_PAGE" => "20",
		"USE_AJAX_LOCATIONS_PROFILE" => "N",
		"COMPATIBLE_LOCATION_MODE_PROFILE" => "N",
		"PROFILES_PER_PAGE" => "20",
		"SEND_INFO_PRIVATE" => "N",
		"CHECK_RIGHTS_PRIVATE" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_GROUPS" => "Y",
		"SHOW_ACCOUNT_PAGE" => "",
		"SHOW_ORDER_PAGE" => "",
		"SHOW_PRIVATE_PAGE" => "",
		"SHOW_PROFILE_PAGE" => "",
		"SHOW_SUBSCRIBE_PAGE" => "",
		"SHOW_CONTACT_PAGE" => "",
		"SHOW_BASKET_PAGE" => "",
		"PATH_TO_CONTACT" => "",
		"MAIN_CHAIN_NAME" => "Личный кабинет",
		"SET_TITLE" => "Y",
		"CUSTOM_PAGES" => "",
		"SHOW_ACCOUNT_COMPONENT" => "",
		"SHOW_ACCOUNT_PAY_COMPONENT" => "",
		"ACCOUNT_PAYMENT_SELL_CURRENCY" => "",
		"ACCOUNT_PAYMENT_PERSON_TYPE" => "",
		"ACCOUNT_PAYMENT_ELIMINATED_PAY_SYSTEMS" => "",
		"ACCOUNT_PAYMENT_SELL_SHOW_FIXED_VALUES" => "",
		"ACCOUNT_PAYMENT_SELL_TOTAL" => "",
		"ACCOUNT_PAYMENT_SELL_USER_INPUT" => "",
		"ICON_PROPS_GROUP_1" => "icon-user",
		"ICON_PROPS_GROUP_2" => "icon-delivery",
		"ICON_PROPS_GROUP_3" => "icon-price",
		"ICON_PROPS_GROUP_4" => "icon-globe",
		"SEF_URL_TEMPLATES" => array(
			"index" => "index.php",
			"orders" => "orders/",
			"account" => "",
			"subscribe" => "subscribe/",
			"profile" => "private/profiles/",
			"profile_detail" => "private/profiles/#ID#/",
			"private" => "private/",
			"order_detail" => "orders/#ID#/",
			"order_cancel" => "cancel/#ID#/",
		)
	),
	false
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>