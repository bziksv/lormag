<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = array(
	"PARAMETERS" => array(
		"MODE" => array(
			"NAME" => GetMessage("QUICK_ORDER_PARAMETERS_MODE"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"PRODUCT" => GetMessage("QUICK_ORDER_PARAMETERS_MODE_PRODUCT"),
				"CART" => GetMessage("QUICK_ORDER_PARAMETERS_MODE_CART"),
			),
			"DEFAULT" => "PRODUCT",		
		),		
		"CONTAINER_ID" => array(
			"NAME" => GetMessage("QUICK_ORDER_PARAMETERS_CONTAINER_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"CONTAINER_CLASS" => array(
			"NAME" => GetMessage("QUICK_ORDER_PARAMETERS_CONTAINER_CLASS"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		)
	)
);