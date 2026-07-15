<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$this->addExternalCss(SITE_TEMPLATE_PATH."/js/owlCarousel/owl.carousel.min.css");
$this->addExternalJS(SITE_TEMPLATE_PATH."/js/owlCarousel/owl.carousel.min.js");

if(!empty($arResult["OBJECTS"]))
	CJSCore::Init(array("ls"));

$curPage = $APPLICATION->GetCurPage()."?".$arParams["ACTION_VARIABLE"]."=";
$arUrls = array(
	"delete" => $curPage."delete&id=#ID#",
	"delay" => $curPage."delay&id=#ID#",
	"add" => $curPage."add&id=#ID#",
	"clear" => $curPage."clear",
	"clearDelay" => $curPage."clearDelay"
);
unset($curPage);

$arParams["USE_ENHANCED_ECOMMERCE"] = isset($arParams["USE_ENHANCED_ECOMMERCE"]) && $arParams["USE_ENHANCED_ECOMMERCE"] === "Y" ? "Y" : "N";
$arParams["DATA_LAYER_NAME"] = isset($arParams["DATA_LAYER_NAME"]) ? trim($arParams["DATA_LAYER_NAME"]) : "dataLayer";
$arParams["BRAND_PROPERTY"] = isset($arParams["BRAND_PROPERTY"]) ? trim($arParams["BRAND_PROPERTY"]) : "";

$signer = new Bitrix\Main\Security\Sign\Signer;
$signedTemplate = $signer->sign($templateName, "sale.basket.basket");
$signedParams = $signer->sign(base64_encode(serialize($arParams)), "sale.basket.basket");

$arBasketJSParams = array(
	"DISABLE_DELAY" => $arParams["DISABLE_DELAY"],
	"ORDER_SUM" => $arResult["allSum"],
	"MIN_ORDER_SUM" => $arParams["MIN_ORDER_SUM"],
	"OBJECTS" => !empty($arResult["OBJECTS"]),
	"DELETE_URL" => $arUrls["delete"],
	"DELAY_URL" => $arUrls["delay"],
	"ADD_URL" => $arUrls["add"],
	"PATH_TO_ORDER" => $arParams["PATH_TO_ORDER"],
	"EVENT_ONCHANGE_ON_START" => (!empty($arResult["EVENT_ONCHANGE_ON_START"]) && $arResult["EVENT_ONCHANGE_ON_START"] === "Y") ? "Y" : "N",
	"USE_ENHANCED_ECOMMERCE" => $arParams["USE_ENHANCED_ECOMMERCE"],
	"DATA_LAYER_NAME" => $arParams["DATA_LAYER_NAME"],
	"BRAND_PROPERTY" => $arParams["BRAND_PROPERTY"],
	"SIGNED_TEMPLATE" => CUtil::JSEscape($signedTemplate),
	"SIGNED_PARAMS" => CUtil::JSEscape($signedParams)
);?>

<script type="text/javascript">
	BX.message({
		SBB_ORDER: '<?=GetMessageJS("SALE_ORDER")?>',
		SBB_ORDER_SHORT: '<?=GetMessageJS("SALE_ORDER_SHORT")?>',
		SBB_TOTAL_DISCOUNT: '<?=GetMessageJS("SALE_TOTAL_DISCOUNT")?>',
		SBB_DELETE: '<?=GetMessageJS("SALE_DELETE")?>',
		SBB_DELAY: '<?=GetMessageJS("SALE_DELAY")?>',
		SBB_TYPE: '<?=GetMessageJS("SALE_TYPE")?>',
		SBB_MEASURE_PC: '<?=GetMessageJS("SALE_MEASURE_PC")?>',
		SBB_MEASURE_SQ_M: '<?=GetMessageJS("SALE_MEASURE_SQ_M")?>',
		SBB_NO_ITEMS: '<?=GetMessageJS("SALE_NO_ITEMS")?>'
	});
	var basketJSParams = <?=CUtil::PhpToJSObject($arBasketJSParams);?>;
</script>

<?if(strlen($arResult["ERROR_MESSAGE"]) <= 0) {
	$normalCount = count($arResult["ITEMS"]["AnDelCanBuy"]);
	$delayCount = count($arResult["ITEMS"]["DelDelCanBuy"]);
	
	foreach(array_keys($arResult["GRID"]["HEADERS"]) as $id) {
		$data = $arResult["GRID"]["HEADERS"][$id];
		$headerName = (isset($data["name"]) ? (string)$data["name"] : "");
		if($headerName == "")
			$arResult["GRID"]["HEADERS"][$id]["name"] = GetMessage("SALE_".$data["id"]);
		unset($headerName, $data);
	}
	unset($id);?>

	<form method="post" action="<?=POST_FORM_ACTION_URI?>" name="basket_form" id="basket_form">
		<div id="basket_form_container">
			<div class="bx-ordercart">
				<div class="hidden-print bx-ordercart-tabs-block" data-entity="tabs">					
					<div class="container">
						<div class="row">
							<div class="col-xs-12 col-md-8">
								<div class="bx-ordercart-tabs-scroll">
									<ul class="bx-ordercart-tabs-list">
										<?if(!$arParams["DISABLE_BASKET"]) {?>
											<li class="bx-ordercart-tab active" data-entity="tab" data-value="basket-items" onclick="showBasketItemsList()">
												<?=GetMessage("SALE_BASKET_ITEMS")?>
												<span class="bx-ordercart-tab-count"><?=$normalCount?></span>
											</li>
										<?}
										if(!$arParams["DISABLE_DELAY"]) {?>
											<li class="bx-ordercart-tab<?=($arParams['DISABLE_BASKET'] ? ' active' : '')?>" data-entity="tab" data-value="basket-items-delayed" onclick="showBasketItemsList(2)">
												<?=GetMessage("SALE_BASKET_ITEMS_DELAYED")?>
												<span class="bx-ordercart-tab-count"><?=$delayCount?></span>
											</li>
										<?}?>
									</ul>
								</div>
							</div>
							<div class="hidden-xs hidden-sm col-md-4">
								<div class="bx-ordercart-tab-buttons">
									<?if(!$arParams["DISABLE_BASKET"] || !$arParams["DISABLE_DELAY"]) {?>
										<a class="bx-ordercart-tab-button" href="javascript:window.print(); void(0);" data-entity="print" style="<?=(!$arParams['DISABLE_BASKET'] ? ($normalCount <= 0 ? 'display: none;' : '') : ($delayCount <= 0 ? 'display: none;' : ''));?>"><i class="icon-print"></i><span><?=GetMessage("SALE_BASKET_PRINT")?></span></a>
									<?}
									if(!$arParams["DISABLE_BASKET"]) {?>
										<a class="bx-ordercart-tab-button" href="<?=$arUrls['clear']?>" data-entity="clear" style="<?=($normalCount <= 0 ? 'display: none;' : '');?>"><i class="icon-trash"></i><span><?=GetMessage("SALE_BASKET_CLEAR")?></span></a>
									<?}
									if(!$arParams["DISABLE_DELAY"]) {?>
										<a class="bx-ordercart-tab-button" href="<?=$arUrls['clearDelay']?>" data-entity="clearDelay" style="<?=(!$arParams['DISABLE_BASKET'] ? 'display: none;' : ($delayCount <= 0 ? 'display: none;' : ''))?>"><i class="icon-trash"></i><span><?=GetMessage("SALE_BASKET_CLEAR_DELAYED")?></span></a>
									<?}?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div id="warning_message" class="bx-ordercart-message" style="<?=(!empty($arResult["WARNING_MESSAGE"]) ? '' : 'display: none;')?>">
					<?if(!empty($arResult["WARNING_MESSAGE"]))
						ShowNote(implode("<br />", $arResult["WARNING_MESSAGE"]), "warning");?>
				</div>
				<?if(!$arParams["DISABLE_BASKET"]) {
					if(!empty($arResult["OBJECTS"]))
						include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_items_objects.php");
					else
						include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_items.php");
				}
				if(!$arParams["DISABLE_DELAY"])
					include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_items_delayed.php");?>
			</div>
		</div>
		<input type="hidden" name="BasketOrder" value="BasketOrder" />
	</form>
	<?if($arParams["USE_GIFTS"] == "Y") {?>
		<div class="basket-gifts" data-entity="parent-container" style="display: none;">
			<?if($arParams["GIFTS_HIDE_BLOCK_TITLE"] !== "Y") {?>
				<div class="h2" data-entity="header" data-showed="false" style="display: none; opacity: 0;"><?=($arParams["GIFTS_BLOCK_TITLE"] ?: Loc::getMessage("SALE_GIFTS_BLOCK_TITLE"))?></div>
			<?}
			CBitrixComponent::includeComponentClass("bitrix:sale.products.gift.basket");?>
			<?$APPLICATION->IncludeComponent("bitrix:sale.products.gift.basket", ".default",
				array(
					"IBLOCK_TYPE" => $arParams["CATALOG_IBLOCK_TYPE"],
					"IBLOCK_ID" => $arParams["CATALOG_IBLOCK_ID"],
					"ELEMENT_SORT_FIELD" => $arParams["CATALOG_ELEMENT_SORT_FIELD"],
					"ELEMENT_SORT_ORDER" => $arParams["CATALOG_ELEMENT_SORT_ORDER"],
					"ELEMENT_SORT_FIELD2" => $arParams["CATALOG_ELEMENT_SORT_FIELD2"],
					"ELEMENT_SORT_ORDER2" => $arParams["CATALOG_ELEMENT_SORT_ORDER2"],					
					"PROPERTY_CODE" => $arParams["CATALOG_PROPERTY_CODE"],			
					"INCLUDE_SUBSECTIONS" => $arParams["CATALOG_INCLUDE_SUBSECTIONS"],
					"BASKET_URL" => $arParams["CATALOG_BASKET_URL"],
					"ACTION_VARIABLE" => $arParams["CATALOG_ACTION_VARIABLE"],
					"PRODUCT_ID_VARIABLE" => $arParams["CATALOG_PRODUCT_ID_VARIABLE"],
					"SECTION_ID_VARIABLE" => $arParams["CATALOG_SECTION_ID_VARIABLE"],
					"PRODUCT_QUANTITY_VARIABLE" => $arParams["CATALOG_PRODUCT_QUANTITY_VARIABLE"],
					"PRODUCT_PROPS_VARIABLE" => $arParams["CATALOG_PRODUCT_PROPS_VARIABLE"],				
					"CACHE_TYPE" => $arParams["CACHE_TYPE"],
					"CACHE_TIME" => $arParams["CACHE_TIME"],
					"CACHE_FILTER" => $arParams["CACHE_FILTER"],
					"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],									
					"DISPLAY_COMPARE" => $arParams["CATALOG_DISPLAY_COMPARE"] ? "Y" : "N",
					"PAGE_ELEMENT_COUNT" => 0,
					"DEFERRED_PAGE_ELEMENT_COUNT" => $arParams["GIFTS_PAGE_ELEMENT_COUNT"],				
					"PRICE_CODE" => $arParams["CATALOG_PRICE_CODE"],
					"USE_PRICE_COUNT" => $arParams["CATALOG_USE_PRICE_COUNT"] ? "Y" : "N",
					"SHOW_PRICE_COUNT" => $arParams["CATALOG_SHOW_PRICE_COUNT"] ? "Y" : "N",
					"PRICE_VAT_INCLUDE" => $arParams["CATALOG_PRICE_VAT_INCLUDE"] ? "Y" : "N",
					"USE_PRODUCT_QUANTITY" => $arParams["CATALOG_USE_PRODUCT_QUANTITY"] ? "Y" : "N",
					"ADD_PROPERTIES_TO_BASKET" => $arParams["CATALOG_ADD_PROPERTIES_TO_BASKET"],
					"PARTIAL_PRODUCT_PROPERTIES" => $arParams["CATALOG_PARTIAL_PRODUCT_PROPERTIES"],
					"PRODUCT_PROPERTIES" => $arParams["CATALOG_PRODUCT_PROPERTIES"],										
					"OFFERS_CART_PROPERTIES" => $arParams["CATALOG_OFFERS_CART_PROPERTIES"],					
					"OFFERS_FIELD_CODE" => $arParams["CATALOG_OFFERS_FIELD_CODE"],
					"OFFERS_PROPERTY_CODE" => $arParams["CATALOG_OFFERS_PROPERTY_CODE"],
					"OFFERS_SORT_FIELD" => $arParams["CATALOG_OFFERS_SORT_FIELD"],
					"OFFERS_SORT_ORDER" => $arParams["CATALOG_OFFERS_SORT_ORDER"],
					"OFFERS_SORT_FIELD2" => $arParams["CATALOG_OFFERS_SORT_FIELD2"],
					"OFFERS_SORT_ORDER2" => $arParams["CATALOG_OFFERS_SORT_ORDER2"],
					"OFFERS_LIMIT" => $arParams["CATALOG_OFFERS_LIMIT"],
					"SECTION_ID" => "",
					"SECTION_CODE" => "",
					"SECTION_URL" => "",
					"DETAIL_URL" => "",					
					"USE_MAIN_ELEMENT_SECTION" => $arParams["CATALOG_USE_MAIN_ELEMENT_SECTION"] ? "Y" : "N",
					"CONVERT_CURRENCY" => $arParams["CATALOG_CONVERT_CURRENCY"],
					"CURRENCY_ID" => $arParams["CATALOG_CURRENCY_ID"],
					"HIDE_NOT_AVAILABLE" => $arParams["CATALOG_HIDE_NOT_AVAILABLE"],
					"HIDE_NOT_AVAILABLE_OFFERS" => $arParams["CATALOG_HIDE_NOT_AVAILABLE_OFFERS"],
					"TEXT_LABEL_GIFT" => $arParams["GIFTS_TEXT_LABEL_GIFT"],							
					"PRODUCT_DISPLAY_MODE" => $arParams["CATALOG_PRODUCT_DISPLAY_MODE"],						
					"PRODUCT_ROW_VARIANTS" => "",
					"DEFERRED_PRODUCT_ROW_VARIANTS" => Bitrix\Main\Web\Json::encode(SaleProductsGiftBasketComponent::predictRowVariants(4, $arParams["GIFTS_PAGE_ELEMENT_COUNT"])),					
					"OFFER_TREE_PROPS" => $arParams["CATALOG_OFFER_TREE_PROPS"],					
					"PRODUCT_SUBSCRIPTION" => $arParams["CATALOG_PRODUCT_SUBSCRIPTION"],
					"SHOW_DISCOUNT_PERCENT" => $arParams["CATALOG_SHOW_DISCOUNT_PERCENT"],						
					"SHOW_OLD_PRICE" => $arParams["CATALOG_SHOW_OLD_PRICE"],
					"SHOW_MAX_QUANTITY" => $arParams["CATALOG_SHOW_MAX_QUANTITY"],
					"MESS_SHOW_MAX_QUANTITY" => $arParams["~CATALOG_MESS_SHOW_MAX_QUANTITY"],
					"RELATIVE_QUANTITY_FACTOR" => $arParams["CATALOG_RELATIVE_QUANTITY_FACTOR"],
					"MESS_RELATIVE_QUANTITY_MANY" => $arParams["~CATALOG_MESS_RELATIVE_QUANTITY_MANY"],
					"MESS_RELATIVE_QUANTITY_FEW" => $arParams["~CATALOG_MESS_RELATIVE_QUANTITY_FEW"],				
					"MESS_BTN_BUY" => $arParams["~CATALOG_MESS_BTN_BUY"],
					"MESS_BTN_ADD_TO_BASKET" => $arParams["~CATALOG_MESS_BTN_ADD_TO_BASKET"],
					"GIFTS_MESS_BTN_BUY" => $arParams["~GIFTS_MESS_BTN_BUY"],
					"GIFTS_MESS_BTN_ADD_TO_BASKET" => $arParams["~GIFTS_MESS_BTN_BUY"],
					"MESS_BTN_SUBSCRIBE" => $arParams["~CATALOG_MESS_BTN_SUBSCRIBE"],
					"MESS_BTN_DETAIL" => $arParams["~CATALOG_MESS_BTN_DETAIL"],
					"MESS_NOT_AVAILABLE" => $arParams["~CATALOG_MESS_NOT_AVAILABLE"],
					"MESS_BTN_COMPARE" => $arParams["~CATALOG_MESS_BTN_COMPARE"],
					"APPLIED_DISCOUNT_LIST" => $arResult["APPLIED_DISCOUNT_LIST"],
					"FULL_DISCOUNT_LIST" => $arResult["FULL_DISCOUNT_LIST"],						
					"USE_ENHANCED_ECOMMERCE" => $arParams["USE_ENHANCED_ECOMMERCE"],
					"DATA_LAYER_NAME" => $arParams["DATA_LAYER_NAME"],
					"BRAND_PROPERTY" => $arParams["BRAND_PROPERTY"],
					"ADD_TO_BASKET_ACTION" => $arParams["CATALOG_ADD_TO_BASKET_ACTION"],
					"COMPARE_PATH" => $arParams["CATALOG_COMPARE_PATH"],
					"COMPARE_NAME" => $arParams["CATALOG_COMPARE_NAME"],
					"DETAIL_ADD_PICT_PROP" => $arParams["CATALOG_DETAIL_ADD_PICT_PROP"],
					"DETAIL_OFFER_ADD_PICT_PROP" => $arParams["CATALOG_DETAIL_OFFER_ADD_PICT_PROP"],
					"DETAIL_PROPERTY_CODE" => $arParams["CATALOG_DETAIL_PROPERTY_CODE"],				
					"DETAIL_OFFERS_FIELD_CODE" => $arParams["CATALOG_DETAIL_OFFERS_FIELD_CODE"],
					"DETAIL_OFFERS_PROPERTY_CODE" => $arParams["CATALOG_DETAIL_OFFERS_PROPERTY_CODE"],
					"DETAIL_MAIN_BLOCK_PROPERTY_CODE" => $arParams["CATALOG_DETAIL_MAIN_BLOCK_PROPERTY_CODE"],
					"DETAIL_MAIN_BLOCK_OFFERS_PROPERTY_CODE" => $arParams["CATALOG_DETAIL_MAIN_BLOCK_OFFERS_PROPERTY_CODE"],	
					"DETAIL_IMAGE_RESOLUTION" => $arParams["CATALOG_DETAIL_IMAGE_RESOLUTION"],				
					"DETAIL_ADD_DETAIL_TO_SLIDER" => $arParams["CATALOG_DETAIL_ADD_DETAIL_TO_SLIDER"],
					"DETAIL_DETAIL_PICTURE_MODE" => $arParams["CATALOG_DETAIL_DETAIL_PICTURE_MODE"],
					"DETAIL_SHOW_SLIDER" => $arParams["CATALOG_DETAIL_SHOW_SLIDER"],
					"DETAIL_SLIDER_INTERVAL" => $arParams["CATALOG_DETAIL_SLIDER_INTERVAL"],
					"DETAIL_SLIDER_PROGRESS" => $arParams["CATALOG_DETAIL_SLIDER_PROGRESS"],
					
					"USE_REVIEW" => $arParams["CATALOG_USE_REVIEW"],
					"REVIEWS_IBLOCK_TYPE" => $arParams["CATALOG_REVIEWS_IBLOCK_TYPE"],
					"REVIEWS_IBLOCK_ID" => $arParams["CATALOG_REVIEWS_IBLOCK_ID"],
					"REVIEWS_NEWS_COUNT" => $arParams["CATALOG_REVIEWS_NEWS_COUNT"],
					"REVIEWS_SORT_BY1" => $arParams["CATALOG_REVIEWS_SORT_BY1"],
					"REVIEWS_SORT_ORDER1" => $arParams["CATALOG_REVIEWS_SORT_ORDER1"],
					"REVIEWS_SORT_BY2" => $arParams["CATALOG_REVIEWS_SORT_BY2"],
					"REVIEWS_SORT_ORDER2" => $arParams["CATALOG_REVIEWS_SORT_ORDER2"],
					"REVIEWS_ACTIVE_DATE_FORMAT" => $arParams["CATALOG_REVIEWS_ACTIVE_DATE_FORMAT"],
					"REVIEWS_PROPERTY_CODE" => $arParams["CATALOG_REVIEWS_PROPERTY_CODE"],
					"MESS_REVIEWS_TAB" => $arParams["CATALOG_MESS_REVIEWS_TAB"],
					
					"USE_GIFTS_DETAIL" => $arParams["CATALOG_USE_GIFTS_DETAIL"],
					"GIFTS_DETAIL_PAGE_ELEMENT_COUNT" => $arParams["CATALOG_GIFTS_DETAIL_PAGE_ELEMENT_COUNT"],
					"GIFTS_DETAIL_HIDE_BLOCK_TITLE" => $arParams["CATALOG_GIFTS_DETAIL_HIDE_BLOCK_TITLE"],
					"GIFTS_DETAIL_BLOCK_TITLE" => $arParams["CATALOG_GIFTS_DETAIL_BLOCK_TITLE"],
					"GIFTS_DETAIL_TEXT_LABEL_GIFT" => $arParams["CATALOG_GIFTS_DETAIL_TEXT_LABEL_GIFT"],
					"GIFTS_MESS_BTN_BUY" => $arParams["CATALOG_GIFTS_MESS_BTN_BUY"],
					
					"USE_STORE" => $arParams["CATALOG_USE_STORE"],
					"STORE_PATH" => $arParams["CATALOG_STORE_PATH"],
					"STORES" => $arParams["CATALOG_STORES"],
					"USE_MIN_AMOUNT" => $arParams["CATALOG_USE_MIN_AMOUNT"],
					"USER_FIELDS" => $arParams["CATALOG_USER_FIELDS"],
					"FIELDS" => $arParams["CATALOG_FIELDS"],
					"MIN_AMOUNT" => $arParams["CATALOG_MIN_AMOUNT"],
					"SHOW_EMPTY_STORE" => $arParams["CATALOG_SHOW_EMPTY_STORE"],
					"SHOW_GENERAL_STORE_INFORMATION" => $arParams["CATALOG_SHOW_GENERAL_STORE_INFORMATION"],
					"MAIN_TITLE" => $arParams["CATALOG_MAIN_TITLE"],
					
					"SET_ITEMS_COUNT" => $arParams["CATALOG_SET_ITEMS_COUNT"],
					
					"OBJECTS_USE_REVIEW" => $arParams["OBJECTS_USE_REVIEW"],
					"OBJECTS_REVIEWS_IBLOCK_ID" => $arParams["OBJECTS_REVIEWS_IBLOCK_ID"],
					"CONTACTS_IBLOCK_ID" => $arParams["CONTACTS_IBLOCK_ID"],
					"CONTACTS_USE_REVIEW" => $arParams["CONTACTS_USE_REVIEW"],
					"CONTACTS_REVIEWS_IBLOCK_ID" => $arParams["CONTACTS_REVIEWS_IBLOCK_ID"],
					"CONTACTS_REVIEWS_PAGE_LINK" => $arParams["CONTACTS_REVIEWS_PAGE_LINK"]
				),
				$component,
				array("HIDE_ICONS" => "Y")
			);?>
		</div>
	<?}
} else {
	ShowNote($arResult["ERROR_MESSAGE"], "warning");
}