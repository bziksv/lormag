<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$this->setFrameMode(true);

use Bitrix\Main\Localization\Loc;

$obName = "ob".preg_replace("/[^a-zA-Z0-9_]/", "x", $this->GetEditAreaId($this->randString()));
$containerName = "slide-panel-geo-delivery-".$obName;?>

<div class="slide-panel-geo-delivery" id="<?=$containerName?>">
	<?if($arParams["PERSON_TYPE_INPUT"] == "Y" && !empty($arResult["PERSON_TYPES"])) {?>
		<div class="form-group">
			<?foreach($arResult["PERSON_TYPES"] as $arPersonType) {?>
				<div class="radio">
					<label>
						<input type="radio" name="PERSON_TYPE_ID" value="<?=$arPersonType['ID']?>"<?=($arPersonType["ID"] == $arParams["PERSON_TYPE_ID"] ? " checked='true'" : "")?> />
						<span class="check-cont"><span class="check"><i class="icon-ok-b"></i></span></span><span class="check-title"><?=$arPersonType["NAME"]?></span>
					</label>
				</div>
			<?}
			unset($arPersonType);?>
		</div>
	<?}
	if($arParams["LOCATION_INPUT"] == "Y") {?>
		<div class="form-group">
			<?$APPLICATION->IncludeComponent("bitrix:sale.location.selector.search", "slide_panel",
				array(
					"ID" => $arParams["LOCATION_ID"],
					"CODE" => "",
					"INPUT_NAME" => "LOCATION",
					"PROVIDE_LINK_BY" => "id",
					"FILTER_BY_SITE" => "Y",
					"FILTER_SITE_ID" => $arParams["SITE_ID"],
					"SHOW_DEFAULT_LOCATIONS" => "Y",
					"JSCONTROL_GLOBAL_ID" => "",
					"JS_CALLBACK" => "",
					"CACHE_TYPE" => $arParams["CACHE_TYPE"],
					"CACHE_TIME" => $arParams["CACHE_TIME"],
					"SUPPRESS_ERRORS" => "N",
					"INITIALIZE_BY_GLOBAL_EVENT" => ""
				),
				$component,
				array("HIDE_ICONS" => "Y")
			);?>
		</div>
	<?}
	if($arParams["QUANTITY_INPUT"] == "Y") {?>
		<div class="form-group">
			<div class="slide-panel-geo-delivery-amount">								
				<a class="slide-panel-geo-delivery-amount-btn-minus" href="javascript:void(0)" rel="nofollow" data-entity="quantity-down"><span>-</span></a>
				<input type="tel" name="PRODUCT_QUANTITY" value="<?=$arParams['PRODUCT_QUANTITY']?>" data-entity="quantity" />
				<a class="slide-panel-geo-delivery-amount-btn-plus" href="javascript:void(0)" rel="nofollow" data-entity="quantity-up"><span>+</span></a>
				<div class="slide-panel-geo-delivery-amount-measure"><?=$arResult["RATIO_MEASURE"]["MEASURE"]["SYMBOL"]?></div>
			</div>
		</div>
	<?}
	if($arParams["CALC_ALL_PRODUCTS_INPUT"] == "Y") {?>
		<div class="form-group">
			<div class="checkbox">
				<label>
					<input type="checkbox" name="CALC_ALL_PRODUCTS" value="Y"<?=($arParams["CALC_ALL_PRODUCTS"] == "Y" ? " checked='true'" : "")?> />
					<span class="check-cont"><span class="check"><i class="icon-ok-b"></i></span></span><span class="check-title"><?=Loc::getMessage("GEO_DELIVERY_SLIDE_PANEL_CART_PRODUCTS")?></span>
				</label>
			</div>
		</div>
	<?}?>
	<div class="slide-panel-geo-delivery-items" data-entity="items">
		<!-- items-container -->
		<?if(!empty($arResult["DELIVERY_ITEMS"])) {
			foreach($arResult["DELIVERY_ITEMS"] as $arDeliveryItem) {?>
				<div class="slide-panel-geo-delivery-item" data-entity="item">
					<div class="slide-panel-geo-delivery-item-col slide-panel-geo-delivery-item-col-image">
						<div class="slide-panel-geo-delivery-item-image">
							<?if(!empty($arDeliveryItem["LOGOTIP"])) {?>
								<img src="<?=$arDeliveryItem['LOGOTIP']['SRC']?>" width="<?=$arDeliveryItem['LOGOTIP']['WIDTH']?>" height="<?=$arDeliveryItem['LOGOTIP']['HEIGHT']?>" alt="<?=$arDeliveryItem['NAME']?>" />
							<?} else {?>
								<img src="<?=SITE_TEMPLATE_PATH?>/images/no_photo.png" width="120" height="120" alt="<?=$arDeliveryItem['NAME']?>" />
							<?}?>
						</div>
					</div>
					<div class="slide-panel-geo-delivery-item-col slide-panel-geo-delivery-item-col-info">
						<div class="slide-panel-geo-delivery-item-title"><?=$arDeliveryItem["NAME"]?></div>
						<?if(!empty($arDeliveryItem["PERIOD_TEXT"])) {?>
							<div class="slide-panel-geo-delivery-item-period"><?=$arDeliveryItem["PERIOD_TEXT"]?></div>
						<?}?>
					</div>
					<div class="slide-panel-geo-delivery-item-col">
						<div class="slide-panel-geo-delivery-item-price"><?=$arDeliveryItem["PRICE_FORMATED"]?></div>
					</div>
					<?if(!empty($arDeliveryItem["DESCRIPTION"])) {?>
						<div class="slide-panel-geo-delivery-item-col slide-panel-geo-delivery-item-col-descr"><?=$arDeliveryItem["DESCRIPTION"]?></div>
					<?}?>
				</div>
			<?}
			unset($arDeliveryItem);
		} else {?>
			<div class="alert alert-error"><?=Loc::getMessage("GEO_DELIVERY_SLIDE_PANEL_ITEMS_EMPTY")?></div>
		<?}?>
		<!-- items-container -->
	</div>
</div>

<?$signer = new \Bitrix\Main\Security\Sign\Signer;
$signedParams = $signer->sign(base64_encode(serialize($arParams)), "geo.delivery.enext");?>

<script type="text/javascript">
	BX.message({
		GEO_DELIVERY_UNDEFINED: '<?=GetMessageJS("GEO_DELIVERY_SLIDE_PANEL_UNDEFINED")?>',
		GEO_DELIVERY_FROM: '<?=GetMessageJS("GEO_DELIVERY_SLIDE_PANEL_FROM")?>',
		GEO_DELIVERY_LOCATION: '<?=GetMessageJS("GEO_DELIVERY_SLIDE_PANEL_LOCATION")?>',
	});
	var <?=$obName?> = new JCGeoDeliverySlidePanelComponent({
		componentPath: '<?=CUtil::JSEscape($componentPath)?>',
		stepQuantity: '<?=CUtil::JSEscape($arResult["RATIO_MEASURE"]["RATIO"])?>',
		parameters: '<?=CUtil::JSEscape($signedParams)?>',
		siteId: '<?=CUtil::JSEscape($arParams["SITE_ID"])?>',
		customSiteId: '<?=CUtil::JSEscape($arParams["CUSTOM_SITE_ID"])?>',
		geoDeliveryContainerId: '<?=CUtil::JSEscape($arParams["GEO_DELIVERY_CONTAINER_ID"])?>',
		container: '<?=$containerName?>'
	});
</script>