<?define("NO_AGENT_CHECK", true);
define("NOT_CHECK_PERMISSIONS", true);

$siteId = isset($_REQUEST["siteId"]) && is_string($_REQUEST["siteId"]) ? $_REQUEST["siteId"] : "";
$siteId = substr(preg_replace("/[^a-z0-9_]/i", "", $siteId), 0, 2);
if(!empty($siteId) && is_string($siteId)) {
	define("SITE_ID", $siteId);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(!Bitrix\Main\Loader::includeModule("catalog"))
	return;

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();

if($request->isAjaxRequest() && $request->get("action") == "catalogSetAdd2Basket") {
	$setIds = $request->get("set_ids");
	$iblockId = intval($request->get("iblockId"));
	$productPropsVar = $request->get("productPropsVar");
	$productProps = !empty($productPropsVar) ? $request->get($productPropsVar) : array();
	$partialProductProps = $request->get("partialProductProps");
	$setCartProps = $request->get("setCartProps");
	$setOffersCartProps = $request->get("setOffersCartProps");
	$itemsRatio = $request->get("itemsRatio");

	if(!empty($setIds)) {
		foreach($setIds as $itemId) {
			$productProperties = array();
			
			$mxResult = CCatalogSku::GetProductInfo($itemId);
			if(is_array($mxResult)) {
				if(!empty($setOffersCartProps))
					$productProperties = CIBlockPriceTools::GetOfferProperties($itemId, $iblockId, $setOffersCartProps);
			} else {
				if(array_key_exists($itemId, $productProps)) {
					$productProperties = CIBlockPriceTools::CheckProductProperties(
						$iblockId,
						$itemId,
						$setCartProps,
						$productProps[$itemId],
						$partialProductProps === "Y"
					);
				}
			}
			
			$ratio = 1;
			if(array_key_exists($itemId, $itemsRatio))
				$ratio = $itemsRatio[$itemId];

			Add2BasketByProductID($itemId, $ratio, array("LID" => SITE_ID), $productProperties);
		}
	}
}