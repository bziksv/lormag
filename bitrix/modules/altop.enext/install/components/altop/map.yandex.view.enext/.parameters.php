<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = array(
	"GROUPS" => array(),
	"PARAMETERS" => array(
		"INIT_MAP_TYPE" => array(
			"NAME" => GetMessage("MYMS_PARAM_INIT_MAP_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"map" => GetMessage("MYMS_PARAM_INIT_MAP_TYPE_MAP"),
				"satellite" => GetMessage("MYMS_PARAM_INIT_MAP_TYPE_SATELLITE"),
				"hybrid" => GetMessage("MYMS_PARAM_INIT_MAP_TYPE_HYBRID")
			),
			"DEFAULT" => "map",
			"ADDITIONAL_VALUES" => "N",
			"PARENT" => "BASE",
		),
		"MAP_DATA" => array(
			"NAME" => GetMessage("MYMS_PARAM_DATA"),
			"TYPE" => "STRING",
			"DEFAULT" => serialize(array(
				"yandex_lat" => GetMessage("MYMS_PARAM_DATA_DEFAULT_LAT"),
				"yandex_lon" => GetMessage("MYMS_PARAM_DATA_DEFAULT_LON"),
				"yandex_scale" => 14
			)),
			"PARENT" => "BASE",
		),
		"MAP_WIDTH" => array(
			"NAME" => GetMessage("MYMS_PARAM_MAP_WIDTH"),
			"TYPE" => "STRING",
			"DEFAULT" => "600",
			"PARENT" => "BASE",
		),
		"MAP_HEIGHT" => array(
			"NAME" => GetMessage("MYMS_PARAM_MAP_HEIGHT"),
			"TYPE" => "STRING",
			"DEFAULT" => "500",
			"PARENT" => "BASE",
		),
		"CONTROLS" => array(
			"NAME" => GetMessage("MYMS_PARAM_CONTROLS"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => array(
				"fullscreenControl" => GetMessage("MYMS_PARAM_CONTROLS_FULLSCREEN"), 
				"geolocationControl" => GetMessage("MYMS_PARAM_CONTROLS_GEOLOCATION"), 
				"routeEditor" => GetMessage("MYMS_PARAM_CONTROLS_ROUTE"), 
				"rulerControl" => GetMessage("MYMS_PARAM_CONTROLS_RULER"), 
				"searchControl" => GetMessage("MYMS_PARAM_CONTROLS_SEARCH"),
				"trafficControl" => GetMessage("MYMS_PARAM_CONTROLS_TRAFFIC"),
				"typeSelector" => GetMessage("MYMS_PARAM_CONTROLS_TYPE_SELECTOR"),
				"zoomControl" => GetMessage("MYMS_PARAM_CONTROLS_ZOOM")
			),
			"DEFAULT" => array("fullscreenControl", "zoomControl"),
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"OPTIONS" => array(
			"NAME" => GetMessage("MYMS_PARAM_OPTIONS"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => array(
				"drag" => GetMessage("MYMS_PARAM_OPTIONS_DRAG"),
				"scrollZoom" => GetMessage("MYMS_PARAM_OPTIONS_SCROLL_ZOOM"),
				"dblClickZoom" => GetMessage("MYMS_PARAM_OPTIONS_DBL_CLICK_ZOOM"),
				"multiTouch" => GetMessage("MYMS_PARAM_OPTIONS_MULTI_TOUCH"),
				"rightMouseButtonMagnifier" => GetMessage("MYMS_PARAM_OPTIONS_RIGHT_MOUSE_BUTTON_MAGNIFIER"),
				"leftMouseButtonMagnifier" => GetMessage("MYMS_PARAM_OPTIONS_LEFT_MOUSE_BUTTON_MAGNIFIER"),
				"ruler" => GetMessage("MYMS_PARAM_OPTIONS_RULER"),
				"routeEditor" => GetMessage("MYMS_PARAM_OPTIONS_ROUTE_EDITOR")
			),
			"DEFAULT" => array("drag", "dblClickZoom", "multiTouch", "rightMouseButtonMagnifier"),
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"MAP_ID" => array(
			"NAME" => GetMessage("MYMS_PARAM_MAP_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"API_KEY" => array(
			"NAME" => GetMessage("MYMS_PARAM_API_KEY"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		)
	)
);