<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if($arParams["API_KEY"] == "")
	$arParams["API_KEY"] = Bitrix\Main\Config\Option::get("fileman", "yandex_map_api_key", "");

if(!$arParams["LOCALE"]) {
	switch(LANGUAGE_ID) {
		case "ru":
			$arParams["LOCALE"] = "ru-RU";
		break;
		case "ua":
			$arParams["LOCALE"] = "ru-UA";
		break;
		case "tk":
			$arParams["LOCALE"] = "tr-TR";
		break;
		default:
			$arParams["LOCALE"] = "en-US";
		break;
	}
}

if(!defined("BX_YMAP_SCRIPT_LOADED")) {
	$arResult["MAPS_SCRIPT_URL"] = (CMain::IsHTTPS() ? "https" : "http")."://api-maps.yandex.ru/2.1/?lang=".$arParams["LOCALE"];

	if($arParams["API_KEY"] != "")
		$arResult["MAPS_SCRIPT_URL"] .= "&apikey=".$arParams["API_KEY"];?>
	
	<script type="text/javascript">
		var script = document.createElement('script');
		script.src = '<?=$arResult["MAPS_SCRIPT_URL"]?>';
		(document.head || document.documentElement).appendChild(script);
		script.onload = function () {
			this.parentNode.removeChild(script);
		};
	</script>
	
	<?define("BX_YMAP_SCRIPT_LOADED", 1);
}

$arParams["MAP_ID"] = $arParams["MAP_ID"] == "" || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["MAP_ID"]) ? "MAP_".$this->randString() : $arParams["MAP_ID"];

$arParams["INIT_MAP_LON"] = floatval($arParams["INIT_MAP_LON"]);
$arParams["INIT_MAP_LON"] = $arParams["INIT_MAP_LON"] ? $arParams["INIT_MAP_LON"] : 37.64;

$arParams["INIT_MAP_LAT"] = floatval($arParams["INIT_MAP_LAT"]);
$arParams["INIT_MAP_LAT"] = $arParams["INIT_MAP_LAT"] ? $arParams["INIT_MAP_LAT"] : 55.76;

$arParams["INIT_MAP_SCALE"] = intval($arParams["INIT_MAP_SCALE"]);
$arParams["INIT_MAP_SCALE"] = $arParams["INIT_MAP_SCALE"] ? $arParams["INIT_MAP_SCALE"] : 10;

$arResult["ALL_MAP_OPTIONS"] = array("drag", "scrollZoom", "dblClickZoom", "multiTouch", "rightMouseButtonMagnifier", "leftMouseButtonMagnifier", "ruler", "routeEditor");

$arResult["ALL_MAP_CONTROLS"] = array("fullscreenControl", "geolocationControl", "routeEditor", "rulerControl", "searchControl", "trafficControl", "typeSelector", "zoomControl");

if(!$arParams["INIT_MAP_TYPE"])
	$arParams["INIT_MAP_TYPE"] = "map";

if(!is_array($arParams["OPTIONS"]))
	$arParams["OPTIONS"] = array("drag", "dblClickZoom", "multiTouch", "rightMouseButtonMagnifier");

if(!is_array($arParams["CONTROLS"]))
	$arParams["CONTROLS"] = array("fullscreenControl", "zoomControl");

$arParams["MAP_WIDTH"] = trim($arParams["MAP_WIDTH"]);
if(ToUpper($arParams["MAP_WIDTH"]) != "AUTO" && mb_substr($arParams["MAP_WIDTH"], -1, 1) != "%") {
	$arParams["MAP_WIDTH"] = intval($arParams["MAP_WIDTH"]);
	if($arParams["MAP_WIDTH"] <= 0)
		$arParams["MAP_WIDTH"] = 600;
	$arParams["MAP_WIDTH"] .= "px";
}

$arParams["MAP_HEIGHT"] = trim($arParams["MAP_HEIGHT"]);
if(mb_substr($arParams["MAP_HEIGHT"], -1, 1) != "%") {
	$arParams["MAP_HEIGHT"] = intval($arParams["MAP_HEIGHT"]);
	if($arParams["MAP_HEIGHT"] <= 0)
		$arParams["MAP_HEIGHT"] = 500;
	$arParams["MAP_HEIGHT"] .= "px";
}

CJSCore::Init();

$this->IncludeComponentTemplate();