<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if(!empty($arResult["ERROR_MESSAGE"]))
	ShowError($arResult["ERROR_MESSAGE"]);

$bPriceType = false;
$bPropsColumn = false;
$bDelayColumn = false;
$bDeleteColumn = false;
$bWeightColumn = false;
$bArticleColumn = false;
$bSqMColumn = false;

foreach($arResult["GRID"]["HEADERS"] as $arHeader) {
	$arHeaders[] = $arHeader["id"];
	
	if(in_array($arHeader["id"], array("TYPE"))) {
		$bPriceType = true;
	} elseif($arHeader["id"] == "PROPS") {
		$bPropsColumn = true;
	} elseif($arHeader["id"] == "DELAY") {
		$bDelayColumn = true;
	} elseif($arHeader["id"] == "DELETE") {
		$bDeleteColumn = true;
	} elseif($arHeader["id"] == "WEIGHT") {
		$bWeightColumn = true;
	} elseif($arHeader["id"] == "PROPERTY_ARTNUMBER_VALUE") {
		$bArticleColumn = true;
		$bArticleColumnId = $arHeader["id"];
		$bArticleColumnTitle = $arHeader["name"];
	} elseif($arHeader["id"] == "PROPERTY_M2_COUNT_VALUE") {
		$bSqMColumn = true;
		$bSqMColumnId = $arHeader["id"];
	}
}
unset($arHeader);

$skipHeaders = array('PROPS', 'DELAY', 'DELETE', 'TYPE', 'PROPERTY_ARTNUMBER_VALUE', 'PROPERTY_OBJECT_VALUE', 'PROPERTY_M2_COUNT_VALUE', 'PROPERTY_OLD_PRICE_VALUE');?>

<div id="basket_items_list">
	<div class="basket-objects" id="basket_items">	
		<?foreach($arResult["OBJECTS"] as $arObject) {?>
			<div class="basket-object" data-object-id="<?=intval($arObject['ID'])?>">
				<?if(intval($arObject["ID"]) > 0) {?>
					<div class="basket-object-info">
						<div class="hidden-xs hidden-sm basket-object-info-col">
							<div class="basket-object-info-image">
								<?if(is_array($arObject["PREVIEW_PICTURE"])) {?>									
									<img src="<?=$arObject['PREVIEW_PICTURE']['SRC']?>" width="<?=$arObject['PREVIEW_PICTURE']['WIDTH']?>" height="<?=$arObject['PREVIEW_PICTURE']['HEIGHT']?>" alt="<?=$arObject['NAME']?>" />
								<?} else {?>
									<img src="<?=SITE_TEMPLATE_PATH?>/images/no_photo.png" width="222" height="222" alt="<?=$arObject['NAME']?>" />
								<?}?>
							</div>
						</div>
						<div class="basket-object-info-col">
							<div class="basket-object-info-name"><?=$arObject["NAME"]?></div>
							<div class="basket-object-info-address"><i class="icon-map-marker"></i><span><?=$arObject["ADDRESS"]?></span></div>
						</div>
					</div>
				<?}?>
				<div class="basket-object-items">			
					<?foreach($arObject["ITEMS"] as $arItem) {?>
						<div class="basket-object-item-tr" id="<?=$arItem['ID']?>" data-item-name="<?=$arItem['NAME']?>" data-item-brand="<?=$arItem[$arParams['BRAND_PROPERTY'].'_VALUE']?>" data-item-price="<?=$arItem['PRICE']?>" data-item-currency="<?=$arItem['CURRENCY']?>" data-item-measure="<?=$arItem['MEASURE_SYMBOL_INTL']?>" data-entity="row">
							<?foreach($arResult["GRID"]["HEADERS"] as $arHeader) {
								if(in_array($arHeader["id"], $skipHeaders))
									continue;
								
								//ITEM//
								if($arHeader["id"] == "NAME") {?>									
									<div class="basket-object-item-td basket-item-item">
										<?//IMAGE//?>
										<div class="basket-item-image-container">
											<div class="basket-item-image">
												<img src="<?=(strlen($arItem['PREVIEW_PICTURE_SRC']) > 0 ? $arItem['PREVIEW_PICTURE_SRC'] : (strlen($arItem['DETAIL_PICTURE_SRC']) > 0 ? $arItem['DETAIL_PICTURE_SRC'] : $templateFolder.'/images/no_photo.png'))?>" alt="<?=$arItem['NAME']?>" />
											</div>
										</div>
										<div class="basket-item-info">
											<?//ARTICLE//
											if($bArticleColumn) {?>
												<span class="basket-item-article">
													<span id="col_<?=$bArticleColumnId?>"><?=$bArticleColumnTitle?></span>: <?=($arItem[$bArticleColumnId] ? $arItem[$bArticleColumnId] : '-');?>
												</span>
											<?}
											//TITLE//?>
											<div class="basket-item-title">
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
														<span class="basket-item-prop">
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
													<div class="basket-item-sku-prop">
														<div class="basket-item-sku-title">
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
														<ul class="basket-item-sku-list" id="prop_<?=$arProp['CODE']?>_<?=$arItem['ID']?>">
															<?$counter = 0;
															foreach($arProp["VALUES"] as $arSkuValue) {
																$counter++;
																$selected = ($selectedIndex == $counter ? ' selected' : '');
																if(!empty($arSkuValue['CODE']) || !empty($arSkuValue['PICT'])) {?>
																	<li class="basket-item-sku-item-color<?=$selected?>" data-sku-selector="Y" data-value-id="<?=$arSkuValue['XML_ID']?>" data-sku-name="<?=htmlspecialcharsbx($arSkuValue['NAME'])?>" data-element="<?=$arItem['ID']?>" data-property="<?=$arProp['CODE']?>" style="<?=(!empty($arSkuValue['CODE']) ? 'background-color: #'.$arSkuValue['CODE'].';' : (!empty($arSkuValue['PICT']) ? 'background-image: url('.$arSkuValue['PICT']['SRC'].');' : ''));?>"></li>
																<?} else {?>
																	<li class="basket-item-sku-item-text<?=$selected?>" data-sku-selector="Y" data-value-id="<?=($arProp['TYPE'] == 'S' && $arProp['USER_TYPE'] == 'directory' ? $arSkuValue['XML_ID'] : htmlspecialcharsbx($arSkuValue['NAME']));?>" data-sku-name="<?=htmlspecialcharsbx($arSkuValue['NAME'])?>" data-element="<?=$arItem['ID']?>" data-property="<?=$arProp['CODE']?>">
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
									<div class="basket-object-item-td basket-item-quantity">										
										<?if($bSqMColumn && $arItem[$bSqMColumnId] && ($arItem["MEASURE_SYMBOL_INTL"] == "pc. 1" || $arItem["MEASURE_SYMBOL_INTL"] == "m2")) {?>
											<div class="basket-item-amount">
												<a class="hidden-print basket-item-amount-btn-minus" href="javascript:void(0)" onclick="setQuantity('PC_QUANTITY_INPUT_<?=$arItem["ID"]?>', <?=$arItem['ID']?>, <?=$arItem['PC_MEASURE_RATIO']?>, 'down', false);">-</a>
												<input type="text" class="basket-item-amount-input" id="PC_QUANTITY_INPUT_<?=$arItem['ID']?>" name="PC_QUANTITY_INPUT_<?=$arItem['ID']?>" maxlength="18" value="<?=$arItem['PC_QUANTITY']?>" onchange="updatePcQuantity('PC_QUANTITY_INPUT_<?=$arItem["ID"]?>', <?=$arItem['ID']?>, <?=$arItem['PC_MEASURE_RATIO']?>, false);" data-ratio="<?=$arItem['PC_MEASURE_RATIO']?>" />
												<a class="hidden-print basket-item-amount-btn-plus" href="javascript:void(0)" onclick="setQuantity('PC_QUANTITY_INPUT_<?=$arItem["ID"]?>', <?=$arItem['ID']?>, <?=$arItem['PC_MEASURE_RATIO']?>, 'up', false);">+</a>
												<div class="basket-item-amount-measure"><?=GetMessage("SALE_MEASURE_PC")?></div>
											</div>
											<div class="basket-item-amount">
												<a class="hidden-print basket-item-amount-btn-minus" href="javascript:void(0)" onclick="setQuantity('SQ_M_QUANTITY_INPUT_<?=$arItem["ID"]?>', <?=$arItem['ID']?>, <?=$arItem['SQ_M_MEASURE_RATIO']?>, 'down', true);">-</a>
												<input type="text" class="basket-item-amount-input" id="SQ_M_QUANTITY_INPUT_<?=$arItem['ID']?>" name="SQ_M_QUANTITY_INPUT_<?=$arItem['ID']?>" maxlength="18" value="<?=$arItem['SQ_M_QUANTITY']?>" onchange="updateSqMQuantity('SQ_M_QUANTITY_INPUT_<?=$arItem["ID"]?>', <?=$arItem['ID']?>, <?=$arItem['SQ_M_MEASURE_RATIO']?>, true);" data-ratio="<?=$arItem['SQ_M_MEASURE_RATIO']?>" />
												<a class="hidden-print basket-item-amount-btn-plus" href="javascript:void(0)" onclick="setQuantity('SQ_M_QUANTITY_INPUT_<?=$arItem["ID"]?>', <?=$arItem['ID']?>, <?=$arItem['SQ_M_MEASURE_RATIO']?>, 'up', true);">+</a>
												<div class="basket-item-amount-measure"><?=GetMessage("SALE_MEASURE_SQ_M")?></div>
											</div>
										<?} else {?>
											<div class="basket-item-amount">
												<?$ratio = isset($arItem["MEASURE_RATIO"]) ? $arItem["MEASURE_RATIO"] : 0;
												$useFloatQuantity = $arParams["QUANTITY_FLOAT"] == "Y" ? true : false;
												$useFloatQuantityJS = $useFloatQuantity ? "true" : "false";
												if(!isset($arItem["MEASURE_RATIO"]))
													$arItem["MEASURE_RATIO"] = 1;
												if(floatval($arItem["MEASURE_RATIO"]) != 0) {?>
													<a class="hidden-print basket-item-amount-btn-minus" href="javascript:void(0)" onclick="setQuantity('QUANTITY_INPUT_<?=$arItem["ID"]?>', <?=$arItem['ID']?>, <?=$arItem['MEASURE_RATIO']?>, 'down', <?=$useFloatQuantityJS?>);">-</a>
												<?}?>
												<input type="text" class="basket-item-amount-input" id="QUANTITY_INPUT_<?=$arItem['ID']?>" name="QUANTITY_INPUT_<?=$arItem['ID']?>" maxlength="18" value="<?=$arItem['QUANTITY']?>" onchange="updateQuantity('QUANTITY_INPUT_<?=$arItem["ID"]?>', <?=$arItem['ID']?>, <?=$ratio?>, <?=$useFloatQuantityJS?>);" />
												<?if(floatval($arItem["MEASURE_RATIO"]) != 0) {?>
													<a class="hidden-print basket-item-amount-btn-plus" href="javascript:void(0)" onclick="setQuantity('QUANTITY_INPUT_<?=$arItem["ID"]?>', <?=$arItem['ID']?>, <?=$arItem['MEASURE_RATIO']?>, 'up', <?=$useFloatQuantityJS?>);">+</a>
												<?}
												if(isset($arItem["MEASURE_TEXT"])) {?>
													<div class="basket-item-amount-measure">
														<?=htmlspecialcharsbx($arItem["MEASURE_TEXT"])?>
													</div>
												<?}?>
											</div>
										<?}?>
										<input type="hidden" id="QUANTITY_<?=$arItem['ID']?>" name="QUANTITY_<?=$arItem['ID']?>" value="<?=$arItem['QUANTITY']?>" />
									</div>
								<?//PRICE//
								} elseif($arHeader["id"] == "PRICE") {?>
									<div class="hidden-xs hidden-sm basket-object-item-td">
										<div id="current_price_<?=$arItem["ID"]?>">
											<span data-entity="price-current"><?=($arItem["SQ_M_PRICE"] ? $arItem["SQ_M_PRICE_FORMATED"] : $arItem["PRICE_FORMATED"])?></span>
											<?if($bSqMColumn && $arItem[$bSqMColumnId] && ($arItem["MEASURE_SYMBOL_INTL"] == "pc. 1" || $arItem["MEASURE_SYMBOL_INTL"] == "m2")) {?>
												<span>/<?=GetMessage("SALE_MEASURE_SQ_M")?></span>
											<?}?>
										</div>
										<div class="basket-item-old-price" id="old_price_<?=$arItem["ID"]?>">
											<?if(floatval($arItem["DISCOUNT_PRICE_PERCENT"]) > 0) {
												echo $arItem["SQ_M_FULL_PRICE"] ? $arItem["SQ_M_FULL_PRICE_FORMATED"] : $arItem["FULL_PRICE_FORMATED"];
											}?>
										</div>
										<?if($bPriceType && strlen($arItem["NOTES"]) > 0) {?>
											<div class="basket-item-type-price"><?=GetMessage("SALE_TYPE")?></div>
											<div class="basket-item-type-price-value"><?=$arItem["NOTES"]?></div>
										<?}?>
									</div>
								<?//DISCOUNT_PERCENT//
								} elseif($arHeader["id"] == "DISCOUNT") {?>
									<div class="hidden-xs hidden-sm basket-object-item-td basket-item-discount-percent">
										<div id="discount_value_<?=$arItem["ID"]?>"><?=$arItem["DISCOUNT_PRICE_PERCENT_FORMATED"]?></div>
									</div>
								<?//WEIGHT//
								} elseif($arHeader["id"] == "WEIGHT") {?>
									<div class="hidden-xs hidden-sm basket-object-item-td">										
										<?=$arItem["WEIGHT_FORMATED"]?>
									</div>
								<?//SUM//
								} elseif($arHeader["id"] == "SUM") {?>
									<div class="basket-object-item-td basket-item-sum">
										<div id="sum_<?=$arItem["ID"]?>"><?=$arItem[$arHeader["id"]]?></div>
									</div>
								<?//OTHER//
								} else {?>
									<div class="hidden-xs hidden-sm basket-object-item-td">
										<?=$arItem[$arHeader["id"]]?>
									</div>
								<?}
							}
							unset($arHeader);
							//CONTROLS//
							if((!$arParams["DISABLE_DELAY"] && $bDelayColumn) || $bDeleteColumn) {?>
								<div class="basket-object-item-td basket-object-item-sep">
									<div class="hidden-print basket-item-controls">									
										<?if(!$arParams["DISABLE_DELAY"] && $bDelayColumn) {?>
											<a class="basket-item-control" href="<?=str_replace('#ID#', $arItem['ID'], $arUrls['delay'])?>" title="<?=GetMessage('SALE_DELAY')?>"><i class="icon-heart"></i></a>
										<?}
										if($bDeleteColumn) {?>
											<a class="basket-item-control" href="<?=str_replace('#ID#', $arItem['ID'], $arUrls['delete'])?>" onclick="return deleteProductRow(this)" title="<?=GetMessage('SALE_DELETE')?>"><i class="icon-close"></i></a>
										<?}?>
									</div>
								</div>
							<?}?>
						</div>
					<?}
					unset($arItem);
					//TOTAL//?>
					<div class="basket-object-item-tr">
						<?foreach($arResult["GRID"]["HEADERS"] as $arHeader) {
							if(in_array($arHeader["id"], $skipHeaders))
								continue;
							
							if($arHeader["id"] == "PRICE") {?>
								<div class="hidden-xs hidden-sm basket-object-item-td basket-item-total-title"><?=GetMessage("SALE_TOTAL")?></div>
							<?} elseif($arHeader["id"] == "SUM") {?>
								<div class="basket-object-item-td basket-object-item-cols">
									<div class="basket-object-item-col">
										<span class="basket-item-total-val" id="allSum_FORMATED_<?=intval($arObject['ID'])?>"><?=$arObject["allSum_FORMATED"]?></span>
										<?$showTotalPrice = (float)$arObject["DISCOUNT_PRICE_ALL"] > 0;?>
										<span class="basket-item-old-price" id="PRICE_WITHOUT_DISCOUNT_<?=intval($arObject['ID'])?>" style="display: <?=($showTotalPrice ? 'block' : 'none');?>;"><?=($showTotalPrice ? $arObject["PRICE_WITHOUT_DISCOUNT"] : '');?></span>
										<span class="basket-item-discount" id="DISCOUNT_PRICE_ALL_FORMATED_<?=intval($arObject['ID'])?>" style="display: <?=($showTotalPrice ? 'block' : 'none');?>;"><?=($showTotalPrice ? GetMessage("SALE_TOTAL_DISCOUNT").' '.$arObject["DISCOUNT_PRICE_ALL_FORMATED"] : '');?></span>
									</div>
									<?if(!$arParams["DISABLE_ORDER"] && !empty($arObject["SITE_ID"]) && $arObject["SITE_ID"] != SITE_ID) {?>
										<div class="hidden-md hidden-lg basket-object-item-col">
											<button type="button" class="btn btn-buy" data-entity="checkOutObject"><span><?=GetMessage("SALE_ORDER_SHORT")?></span></button>
										</div>
									<?}?>
								</div>
							<?} else {?>
								<div class="hidden-xs hidden-sm basket-object-item-td"></div>
							<?}
						}
						unset($arHeader);
						if((!$arParams["DISABLE_DELAY"] && $bDelayColumn) || $bDeleteColumn) {?>
							<div class="hidden-xs hidden-sm basket-object-item-td basket-object-item-sep"></div>
						<?}?>
					</div>
				</div>
				<?if(!$arParams["DISABLE_ORDER"] && !empty($arObject["SITE_ID"]) && $arObject["SITE_ID"] != SITE_ID) {?>
					<div class="hidden-xs hidden-sm basket-object-btn">
						<button type="button" class="btn btn-buy" data-entity="checkOutObject"><span><?=GetMessage("SALE_ORDER")?></span></button>
					</div>
				<?}?>
			</div>
		<?}
		unset($signedObject, $arObject);?>
		<div class="<?=(!$arParams['QUICK_ORDER'] ? 'hidden-md hidden-lg ' : '')?>basket-object-btn basket-object-button<?=($arParams['QUICK_ORDER'] ? 's' : '')?>">
			<a class="hidden-md hidden-lg btn btn-default" href="<?=$arUrls['clear']?>" role="button"><i class="icon-trash"></i><span><?=GetMessage("SALE_BASKET_CLEAR")?></span></a>
			<?if($arParams["QUICK_ORDER"]) {?>
				<button type="button" class="btn btn-primary" data-entity="quickOrder" data-has-object="true"><span class="hidden-xs hidden-sm"><?=GetMessage("SALE_QUICK_ORDER_BASKET")?></span><span class="hidden-md hidden-lg"><?=GetMessage("SALE_QUICK_ORDER_SHORT")?></span></button>
			<?}?>
		</div>
	</div>
	<input type="hidden" id="column_headers" value="<?=htmlspecialcharsbx(implode(',', $arHeaders))?>" />
	<input type="hidden" id="offers_props" value="<?=htmlspecialcharsbx(implode(',', $arParams['OFFERS_PROPS']))?>" />
	<input type="hidden" id="action_var" value="<?=htmlspecialcharsbx($arParams['ACTION_VARIABLE'])?>" />
	<input type="hidden" id="quantity_float" value="<?=($arParams['QUANTITY_FLOAT'] == 'Y' ? 'Y' : 'N')?>" />
	<input type="hidden" id="price_vat_show_value" value="<?=($arParams['PRICE_VAT_SHOW_VALUE'] == 'Y' ? 'Y' : 'N')?>" />
	<input type="hidden" id="hide_coupon" value="<?=($arParams['HIDE_COUPON'] == 'Y' ? 'Y' : 'N')?>" />
	<input type="hidden" id="use_prepayment" value="<?=($arParams['USE_PREPAYMENT'] == 'Y' ? 'Y' : 'N')?>" />
	<input type="hidden" id="auto_calculation" value="<?=($arParams['AUTO_CALCULATION'] == 'N' ? 'N' : 'Y')?>" />
</div>