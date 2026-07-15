<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

//CHECK_BUYED_ADDED//
$basketIds = $buyedAddedIds = array();

if(Bitrix\Main\Loader::includeModule('sale')) {
	$fuserId = Bitrix\Sale\Fuser::getId(true);
	$dbItems = CSaleBasket::GetList(
		array("NAME" => "ASC", "ID" => "ASC"),
		array(			
			"LID" => SITE_ID,			
			"DELAY" => "N",
			"CAN_BUY" => "Y",
			"FUSER_ID" => $fuserId,
			"ORDER_ID" => "NULL"
		),
		false,
		false,
		array("ID", "PRODUCT_ID", "DELAY", "TYPE", "SET_PARENT_ID")
	);
	while($arItem = $dbItems->GetNext()) {
		if(CSaleBasketHelper::isSetItem($arItem))
			continue;

		$basketIds[] = $arItem["PRODUCT_ID"];
	}
	unset($arItem, $dbItems);
}

foreach($arResult["ITEMS"] as $item) {
	$buyedAddedIds[$item["ID"]] = in_array($item["ID"], $basketIds) ? true : false;
}
unset($item);

//JS//?>
<script type="text/javascript">
	BX.ready(BX.defer(function() {
		CatalogCompareObj.setBuyedAdded(<?=CUtil::PhpToJSObject($buyedAddedIds, false, true)?>);
	}));
</script>