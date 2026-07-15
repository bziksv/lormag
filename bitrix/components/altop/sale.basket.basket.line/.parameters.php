<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = array(	
	"PARAMETERS" => array(
		"PATH_TO_BASKET" => array(
			"NAME" => GetMessage("SBBL_PATH_TO_BASKET"),
			"TYPE" => "STRING",
			"DEFAULT" => '={SITE_DIR."personal/cart/"}',
			"PARENT" => "BASE",
		)
	)
);