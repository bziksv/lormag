<?define("STOP_STATISTICS", true);
define("NOT_CHECK_PERMISSIONS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Web\HttpClient,
	Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
if($request->isAjaxRequest() && $request->get("action") == "onChange") {	
	$siteCharset = $request->get("siteCharset") ?: SITE_CHARSET;

	$signer = new Bitrix\Main\Security\Sign\Signer;
	$parameters = unserialize(base64_decode($signer->unsign($request->get("parameters"), "search.yandex.enext")));

	$text = $request->get("text");
	$per_page = $request->get("per_page");
	$available = $request->get("available");

	$error = false;
	try {
        $httpClient = new HttpClient();
        $httpClient->setHeader("Content-Type", "application/json", true);
        $result = $httpClient->get($parameters["SEARCH_URL"]."&text=".$text."&per_page=".$per_page."&available=".($available == 'Y' ? 'true' : 'false'));
        $result = Json::decode($result);
    } catch(Exception $e) {
		$error = $e->getMessage();
	}
	
	$arResult = array();
	
	if(!$error && !empty($result["documents"])) {
		if(!!$parameters["SHOW_SECTIONS"]) {
			$arResult["SECTIONS"] = array(	
				0 => array(
					"id" => 0,
					"value" => Loc::getMessage("SEARCH_YANDEX_AJAX_ALL_RESULTS"),
					"found" => count($result["documents"])
				)
			);
			
			$sectionIds = array();
			foreach($result["documents"] as $arItem) {
				$sectionIds[] = $arItem["categoryId"];			
			}
			unset($arItem);
			
			$sectionList = array();
			if(!empty($result["categoryList"])) {
				foreach($result["categoryList"] as $arSection) {
					$sectionList[$arSection["id"]] = $arSection;
				}
				unset($arSection);
			}

			$sectionCountValues = array_count_values($sectionIds);
			foreach(array_unique($sectionIds) as $sectionId) {
				if(array_key_exists($sectionId, $sectionList)) {
					$arResult["SECTIONS"][] = array(
						"id" => $sectionList[$sectionId]["id"],
						"value" => $sectionList[$sectionId]["value"],
						"found" => $sectionCountValues[$sectionId]
					);
				}
			}
			unset($sectionId);
		}
		
		$arResult["ITEMS"] = array();
		foreach($result["documents"] as $arItem) {
			$arResult["ITEMS"][] = $arItem;
		}
		unset($arItem);
	}
	
	echo Json::encode($arResult);
}