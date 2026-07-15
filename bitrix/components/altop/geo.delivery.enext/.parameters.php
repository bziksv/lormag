<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = array(	
	"PARAMETERS" => array(		
		"PRODUCT_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("GEOLOCATION_DELIVERY_PARAMETERS_PRODUCT_ID"),
			"TYPE" => "STRING"
		),
		"CACHE_TIME"  => array(
			"DEFAULT" => 36000000
		)
	)
);?>