<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Sale\DiscountCouponsManager;

if(!empty($arResult["ERROR_MESSAGE"]))
	ShowError($arResult["ERROR_MESSAGE"]);

$bPropsColumn = false;
$bDeleteColumn = false;
$bArticleColumn = false;
$bObjectColumn = false;
$bSqMColumn = false;

foreach($arResult["GRID"]["HEADERS"] as $arHeader) {
	$arHeaders[] = $arHeader["id"];
	
	if($arHeader["id"] == "PROPS") {
		$bPropsColumn = true;
	} elseif($arHeader["id"] == "DELETE") {
		$bDeleteColumn = true;
	} elseif($arHeader["id"] == "PROPERTY_ARTNUMBER_VALUE") {
		$bArticleColumn = true;
		$bArticleColumnId = $arHeader["id"];
		$bArticleColumnTitle = $arHeader["name"];
	} elseif($arHeader["id"] == "PROPERTY_OBJECT_VALUE") {
		$bObjectColumn = true;
		$bObjectColumnId = $arHeader["id"];
		$bObjectColumnTitle = $arHeader["name"];
	} elseif($arHeader["id"] == "PROPERTY_M2_COUNT_VALUE") {
		$bSqMColumn = true;
		$bSqMColumnId = $arHeader["id"];
	}
}
unset($arHeader);

if(count($arResult["ITEMS"]["AnDelCanBuy"]) > 0) {?>
	<div class="slide-panel-basket-items">
		<?foreach($arResult["GRID"]["ROWS"] as $arItem) {
			if($arItem["DELAY"] == "N" && $arItem["CAN_BUY"] == "Y") {?>
				<div class="slide-panel-basket-item-tr" data-id="<?=$arItem['ID']?>" data-item-measure="<?=$arItem['MEASURE_SYMBOL_INTL']?>" data-entity="row">
					<?foreach($arResult["GRID"]["HEADERS"] as $arHeader) {
						//ITEM//
						if($arHeader["id"] == "NAME") {?>									
							<div class="slide-panel-basket-item-td slide-panel-basket-item-item">
								<?//IMAGE//?>
								<div class="slide-panel-basket-item-image-container">
									<div class="slide-panel-basket-item-image">
										<img src="<?=(strlen($arItem['PREVIEW_PICTURE_SRC']) > 0 ? $arItem['PREVIEW_PICTURE_SRC'] : (strlen($arItem['DETAIL_PICTURE_SRC']) > 0 ? $arItem['DETAIL_PICTURE_SRC'] : $templateFolder.'/images/no_photo.png'))?>" alt="<?=$arItem['NAME']?>" />
										<?//MARKER//?>
										<div id="discount_value_<?=$arItem['ID']?>" class="slide-panel-basket-item-marker" style="display: <?=($arItem['DISCOUNT_PRICE_PERCENT'] > 0 ? '' : 'none')?>;"><?=($arItem["DISCOUNT_PRICE_PERCENT"] > 0 ? "-".$arItem["DISCOUNT_PRICE_PERCENT"]."%" : "")?></div>
									</div>
								</div>
								<div class="slide-panel-basket-item-info">
									<?//ARTICLE//
									if($bArticleColumn) {?>
										<span class="slide-panel-basket-item-article">
											<span id="col_<?=$bArticleColumnId?>"><?=$bArticleColumnTitle?></span>: <?=($arItem[$bArticleColumnId] ? $arItem[$bArticleColumnId] : '-');?>
										</span>
									<?}
									//OBJECT//
									if($bObjectColumn && $arItem["PROPERTY_OBJECT_FULL_VALUE"]) {?>
										<span class="slide-panel-basket-item-object">
											<span id="col_<?=$bObjectColumnId?>"><?=$bObjectColumnTitle?></span>: <?=$arItem["PROPERTY_OBJECT_FULL_VALUE"]["NAME"]?>
										</span>
									<?}
									//TITLE//?>
									<div class="slide-panel-basket-item-title">
										<?if(strlen($arItem["DETAIL_PAGE_URL"]) > 0) {?>
											<a href="<?=$arItem['DETAIL_PAGE_URL']?>">
										<?}
										echo $arItem["NAME"];
										if(strlen($arItem["DETAIL_PAGE_URL"]) > 0) {?>
											</a>
										<?}?>
									</div>										
									<?//PROPS//
									if($bPropsColumn) {
										foreach($arItem["PROPS"] as $val) {
											if(is_array($arItem["SKU_DATA"])) {
												$bSkip = false;
												foreach($arItem["SKU_DATA"] as $arProp) {
													if($arProp["CODE"] == $val["CODE"]) {
														$bSkip = true;
														break;
													}
												}
												unset($arProp);
												if($bSkip)
													continue;
											}
											if($val["CODE"] != "ARTNUMBER") {?>
												<span class="slide-panel-basket-item-prop">
													<?=htmlspecialcharsbx($val["NAME"])?>: <?=$val["VALUE"]?>
												</span>
											<?}
										}
										unset($val);
									}
									//SKU_PROPS//
									if(is_array($arItem["SKU_DATA"]) && !empty($arItem["SKU_DATA"])) {
										$propsMap = array();
										foreach($arItem["PROPS"] as $propValue) {
											if(empty($propValue) || !is_array($propValue))
												continue;
											$propsMap[$propValue['CODE']] = (isset($propValue['~VALUE']) ? $propValue['~VALUE'] : $propValue['VALUE']);
										}
										unset($propValue);

										foreach($arItem["SKU_DATA"] as $arProp) {
											$selectedIndex = 0;
											if(!empty($arProp["VALUES"]) && is_array($arProp["VALUES"])) {
												$counter = 0;
												foreach($arProp["VALUES"] as $arVal) {
													$counter++;
													if(isset($propsMap[$arProp["CODE"]])) {
														if($propsMap[$arProp["CODE"]] == $arVal["NAME"] || $propsMap[$arProp["CODE"]] == $arVal["XML_ID"])
															$selectedIndex = $counter;
													}
												}
												unset($arVal, $counter);
											}?>
											<div class="slide-panel-basket-item-sku-prop">
												<div class="slide-panel-basket-item-sku-title">
													<?=htmlspecialcharsbx($arProp["NAME"]);
													$counter = 0;
													foreach($arProp["VALUES"] as $arSkuValue) {
														$counter++;
														if($selectedIndex == $counter) {
															if(!empty($arSkuValue["CODE"]) || !empty($arSkuValue["PICT"])) {
																echo "<span>".htmlspecialcharsbx($arSkuValue["NAME"])."</span>";
																break;
															}
														}
													}
													unset($arSkuValue, $counter);?>
												</div>
												<ul class="slide-panel-basket-item-sku-list" id="prop_<?=$arProp['CODE']?>_<?=$arItem['ID']?>">
													<?$counter = 0;
													foreach($arProp["VALUES"] as $arSkuValue) {
														$counter++;
														$selected = ($selectedIndex == $counter ? ' selected' : '');
														if(!empty($arSkuValue['CODE']) || !empty($arSkuValue['PICT'])) {?>
															<li class="slide-panel-basket-item-sku-item-color<?=$selected?>" data-sku-selector="Y" data-value-id="<?=$arSkuValue['XML_ID']?>" data-sku-name="<?=htmlspecialcharsbx($arSkuValue['NAME'])?>" data-element="<?=$arItem['ID']?>" data-property="<?=$arProp['CODE']?>" style="<?=(!empty($arSkuValue['CODE']) ? 'background-color: #'.$arSkuValue['CODE'].';' : (!empty($arSkuValue['PICT']) ? 'background-image: url('.$arSkuValue['PICT']['SRC'].');' : ''));?>"></li>
														<?} else {?>
															<li class="slide-panel-basket-item-sku-item-text<?=$selected?>" data-sku-selector="Y" data-value-id="<?=($arProp['TYPE'] == 'S' && $arProp['USER_TYPE'] == 'directory' ? $arSkuValue['XML_ID'] : htmlspecialcharsbx($arSkuValue['NAME']));?>" data-sku-name="<?=htmlspecialcharsbx($arSkuValue['NAME'])?>" data-element="<?=$arItem['ID']?>" data-property="<?=$arProp['CODE']?>">
																<?=htmlspecialcharsbx($arSkuValue['NAME'])?>
															</li>
														<?}
													}
													unset($arSkuValue, $counter);?>
												</ul>
											</div>
										<?}
										unset($arProp);
									}?>
								</div>
							</div>
						<?//QUANTITY//
						} elseif($arHeader["id"] == "QUANTITY") {?>
							<div class="slide-panel-basket-item-td slide-panel-basket-item-quantity">
								<?if($bSqMColumn && $arItem[$bSqMColumnId] && ($arItem["MEASURE_SYMBOL_INTL"] == "pc. 1" || $arItem["MEASURE_SYMBOL_INTL"] == "m2")) {?>
									<div class="slide-panel-basket-item-amount">
										<a class="slide-panel-basket-item-amount-btn-minus" href="javascript:void(0)" onclick="BX.Sale.BasketComponent.setQuantity('PC_QUANTITY_INPUT_<?=$arItem["ID"]?>', <?=$arItem['ID']?>, <?=$arItem['PC_MEASURE_RATIO']?>, 'down', false);">-</a>
										<input type="text" class="slide-panel-basket-item-amount-input" id="PC_QUANTITY_INPUT_<?=$arItem['ID']?>" name="PC_QUANTITY_INPUT_<?=$arItem['ID']?>" maxlength="18" value="<?=$arItem['PC_QUANTITY']?>" onchange="BX.Sale.BasketComponent.updatePcQuantity('PC_QUANTITY_INPUT_<?=$arItem["ID"]?>', <?=$arItem['ID']?>, <?=$arItem['PC_MEASURE_RATIO']?>, false);" data-ratio="<?=$arItem['PC_MEASURE_RATIO']?>" />
										<a class="slide-panel-basket-item-amount-btn-plus" href="javascript:void(0)" onclick="BX.Sale.BasketComponent.setQuantity('PC_QUANTITY_INPUT_<?=$arItem["ID"]?>', <?=$arItem['ID']?>, <?=$arItem['PC_MEASURE_RATIO']?>, 'up', false);">+</a>
										<div class="slide-panel-basket-item-amount-measure"><?=GetMessage("SBB_SLIDE_PANEL_MEASURE_PC")?></div>
									</div>
									<div class="slide-panel-basket-item-amount">
										<a class="slide-panel-basket-item-amount-btn-minus" href="javascript:void(0)" onclick="BX.Sale.BasketComponent.setQuantity('SQ_M_QUANTITY_INPUT_<?=$arItem["ID"]?>', <?=$arItem['ID']?>, <?=$arItem['SQ_M_MEASURE_RATIO']?>, 'down', true);">-</a>
										<input type="text" class="slide-panel-basket-item-amount-input" id="SQ_M_QUANTITY_INPUT_<?=$arItem['ID']?>" name="SQ_M_QUANTITY_INPUT_<?=$arItem['ID']?>" maxlength="18" value="<?=$arItem['SQ_M_QUANTITY']?>" onchange="BX.Sale.BasketComponent.updateSqMQuantity('SQ_M_QUANTITY_INPUT_<?=$arItem["ID"]?>', <?=$arItem['ID']?>, <?=$arItem['SQ_M_MEASURE_RATIO']?>, true);" data-ratio="<?=$arItem['SQ_M_MEASURE_RATIO']?>" />
										<a class="slide-panel-basket-item-amount-btn-plus" href="javascript:void(0)" onclick="BX.Sale.BasketComponent.setQuantity('SQ_M_QUANTITY_INPUT_<?=$arItem["ID"]?>', <?=$arItem['ID']?>, <?=$arItem['SQ_M_MEASURE_RATIO']?>, 'up', true);">+</a>
										<div class="slide-panel-basket-item-amount-measure"><?=GetMessage("SBB_SLIDE_PANEL_MEASURE_SQ_M")?></div>
									</div>
								<?} else {?>
									<div class="slide-panel-basket-item-amount">
										<?$ratio = isset($arItem["MEASURE_RATIO"]) ? $arItem["MEASURE_RATIO"] : 0;
										$useFloatQuantity = $arParams["QUANTITY_FLOAT"] == "Y" ? true : false;
										$useFloatQuantityJS = $useFloatQuantity ? "true" : "false";
										if(!isset($arItem["MEASURE_RATIO"]))
											$arItem["MEASURE_RATIO"] = 1;
										if(floatval($arItem["MEASURE_RATIO"]) != 0) {?>
											<a class="slide-panel-basket-item-amount-btn-minus" href="javascript:void(0)" onclick="BX.Sale.BasketComponent.setQuantity('QUANTITY_INPUT_<?=$arItem["ID"]?>', <?=$arItem['ID']?>, <?=$arItem['MEASURE_RATIO']?>, 'down', <?=$useFloatQuantityJS?>);">-</a>
										<?}?>
										<input type="text" class="slide-panel-basket-item-amount-input" id="QUANTITY_INPUT_<?=$arItem['ID']?>" name="QUANTITY_INPUT_<?=$arItem['ID']?>" maxlength="18" value="<?=$arItem['QUANTITY']?>" onchange="BX.Sale.BasketComponent.updateQuantity('QUANTITY_INPUT_<?=$arItem["ID"]?>', <?=$arItem['ID']?>, <?=$ratio?>, <?=$useFloatQuantityJS?>);" />
										<?if(floatval($arItem["MEASURE_RATIO"]) != 0) {?>
											<a class="slide-panel-basket-item-amount-btn-plus" href="javascript:void(0)" onclick="BX.Sale.BasketComponent.setQuantity('QUANTITY_INPUT_<?=$arItem["ID"]?>', <?=$arItem['ID']?>, <?=$arItem['MEASURE_RATIO']?>, 'up', <?=$useFloatQuantityJS?>);">+</a>
										<?}
										if(isset($arItem["MEASURE_TEXT"])) {?>
											<div class="slide-panel-basket-item-amount-measure">
												<?=htmlspecialcharsbx($arItem["MEASURE_TEXT"])?>
											</div>
										<?}?>
									</div>
								<?}?>
								<input type="hidden" id="QUANTITY_<?=$arItem['ID']?>" name="QUANTITY_<?=$arItem['ID']?>" value="<?=$arItem['QUANTITY']?>" />
							</div>
						<?//SUM//
						} elseif($arHeader["id"] == "SUM") {?>
							<div class="slide-panel-basket-item-td slide-panel-basket-item-sum">
								<div id="sum_<?=$arItem['ID']?>"><?=$arItem[$arHeader["id"]]?></div>
								<div id="old_sum_<?=$arItem['ID']?>" class="slide-panel-basket-item-old-sum" style="display: <?=($arItem['SUM_DISCOUNT_PRICE'] > 0 ? '' : 'none')?>;"><?=($arItem["SUM_DISCOUNT_PRICE"] > 0 ? $arItem["SUM_FULL_PRICE_FORMATED"] : "")?></div>
							</div>
						<?}
					}
					unset($arHeader);
					//DELETE//
					if($bDeleteColumn) {?>
						<div class="slide-panel-basket-item-td slide-panel-basket-item-controls">
							<a class="slide-panel-basket-item-control" href="javascript:void(0);" onclick="BX.Sale.BasketComponent.deleteItem(<?=$arItem['ID']?>);" title="<?=GetMessage('SBB_SLIDE_PANEL_DELETE')?>"><i class="icon-close"></i></a>
						</div>
					<?}?>
				</div>
			<?}
		}
		unset($arItem);?>
	</div>
	<input type="hidden" id="column_headers" value="<?=htmlspecialcharsbx(implode(',', $arHeaders))?>" />
	<input type="hidden" id="offers_props" value="<?=htmlspecialcharsbx(implode(',', $arParams['OFFERS_PROPS']))?>" />
	<input type="hidden" id="action_var" value="<?=htmlspecialcharsbx($arParams['ACTION_VARIABLE'])?>" />
	<input type="hidden" id="quantity_float" value="<?=($arParams['QUANTITY_FLOAT'] == 'Y' ? 'Y' : 'N')?>" />
	<input type="hidden" id="price_vat_show_value" value="<?=($arParams['PRICE_VAT_SHOW_VALUE'] == 'Y' ? 'Y' : 'N')?>" />
	<input type="hidden" id="hide_coupon" value="Y" />
	<input type="hidden" id="use_prepayment" value="N" />
	<input type="hidden" id="auto_calculation" value="Y" />	
	<div class="slide-panel-basket-footer slidePanelCartFooterRightIn" data-entity="basketFooter">
		<div class="slide-panel-basket-footer-row">
			<?//CLEAR//?>
			<div class="slide-panel-basket-footer-col">
				<a class="btn btn-default" href="javascript:void(0);" onclick="BX.Sale.BasketComponent.clearBasket();" role="button"><i class="icon-trash"></i><span><?=GetMessage("SBB_SLIDE_PANEL_BASKET_CLEAR")?></span></a>
			</div>
			<?//TOTAL//?>
			<div class="slide-panel-basket-footer-col slide-panel-basket-footer-total">
				<?foreach($arResult["GRID"]["HEADERS"] as $arHeader) {
					if($arHeader["id"] == "SUM") {?>
						<span class="slide-panel-basket-footer-total-all-sum" data-entity="allSum"><?=$arResult["allSum_FORMATED"]?></span>
						<?$showTotalPrice = (float)$arResult["DISCOUNT_PRICE_ALL"] > 0;?>
						<span class="slide-panel-basket-footer-total-old-sum" data-entity="oldSum" style="display: <?=($showTotalPrice ? 'block' : 'none');?>;"><?=($showTotalPrice ? $arResult["PRICE_WITHOUT_DISCOUNT"] : '');?></span>
						<span class="slide-panel-basket-footer-total-discount-sum" data-entity="discountSum" style="display: <?=($showTotalPrice ? 'block' : 'none');?>;"><?=($showTotalPrice ? GetMessage("SBB_SLIDE_PANEL_TOTAL_DISCOUNT").' '.$arResult["DISCOUNT_PRICE_ALL_FORMATED"] : '');?></span>
					<?}
				}
				unset($arHeader);?>
			</div>
		</div>
		<?//BUTTONS//
		$hasObject = false;
		foreach($arResult["GRID"]["ROWS"] as $arItem) {
			if($arItem["PROPERTY_OBJECT_FULL_VALUE"]) {
				$hasObject = true;
				break;
			}
		}
		unset($arItem);
		if($arParams["QUICK_ORDER"] || (!$arParams["DISABLE_ORDER"] && !$hasObject)) {?>
			<div class="slide-panel-basket-footer-row slide-panel-basket-footer-button<?=($arParams["QUICK_ORDER"] && !$arParams["DISABLE_ORDER"] && !$hasObject ? 's' : '')?>">
				<?if($arParams["QUICK_ORDER"]) {?>
					<button type="button" class="btn btn-primary"<?=($arParams["MIN_ORDER_SUM"] > 0 && $arResult["allSum"] < $arParams["MIN_ORDER_SUM"] ? " disabled" : "")?> data-entity="quickOrder" data-has-object="<?=($hasObject ? 'true' : 'false')?>"><span><?=GetMessage("SBB_SLIDE_PANEL_QUICK_ORDER")?></span></button>
				<?}
				if(!$arParams["DISABLE_ORDER"] && !$hasObject) {?>
					<button type="button" class="btn btn-buy"<?=($arParams["MIN_ORDER_SUM"] > 0 && $arResult["allSum"] < $arParams["MIN_ORDER_SUM"] ? " disabled" : "")?> data-entity="checkOut"><span><?=GetMessage("SBB_SLIDE_PANEL_ORDER")?></span></button>
				<?}?>
			</div>
		<?}?>
	</div>
<?} else {
	ShowNote(GetMessage("SBB_SLIDE_PANEL_NO_ITEMS"), "warning");
}?>