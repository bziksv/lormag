<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Sale\DiscountCouponsManager;

if(!empty($arResult["ERROR_MESSAGE"]))
	ShowError($arResult["ERROR_MESSAGE"]);

$bPriceType = false;
$bPropsColumn = false;
$bDelayColumn = false;
$bDeleteColumn = false;
$bWeightColumn = false;
$bArticleColumn = false;
$bObjectColumn = false;?>

<div id="basket_items_list">
	<?if($normalCount > 0) {?>
		<div class="basket-items" id="basket_items">				
			<div class="hidden-xs hidden-sm basket-item-tr basket-item-thead">	
				<div class="basket-item-td basket-item-sep"></div>
				<?foreach($arResult["GRID"]["HEADERS"] as $arHeader) {
					$arHeaders[] = $arHeader["id"];
					
					if(in_array($arHeader["id"], array("TYPE"))) {
						$bPriceType = true;
						continue;					
					} elseif($arHeader["id"] == "PROPS") {
						$bPropsColumn = true;
						continue;
					} elseif($arHeader["id"] == "DELAY") {
						$bDelayColumn = true;
						continue;
					} elseif($arHeader["id"] == "DELETE") {
						$bDeleteColumn = true;
						continue;
					} elseif($arHeader["id"] == "WEIGHT") {
						$bWeightColumn = true;
					} elseif($arHeader["id"] == "PROPERTY_ARTNUMBER_VALUE") {
						$bArticleColumn = true;
						$bArticleColumnId = $arHeader["id"];
						$bArticleColumnTitle = $arHeader["name"];
						continue;
					} elseif($arHeader["id"] == "PROPERTY_OBJECT_VALUE") {
						$bObjectColumn = true;
						$bObjectColumnId = $arHeader["id"];
						$bObjectColumnTitle = $arHeader["name"];
						continue;
					} elseif($arHeader["id"] == "PROPERTY_M2_COUNT_VALUE") {
						$bSqMColumn = true;
						$bSqMColumnId = $arHeader["id"];
						continue;
					} elseif($arHeader["id"] == "PROPERTY_OLD_PRICE_VALUE") {
						continue;
					}

					if($arHeader["id"] == "NAME") {?>
						<div class="basket-item-td basket-item-item" id="col_<?=$arHeader["id"];?>">
					<?} elseif($arHeader["id"] == "QUANTITY") {?>
						<div class="basket-item-td basket-item-thead-amount" id="col_<?=$arHeader["id"];?>">
					<?} else {?>
						<div class="basket-item-td" id="col_<?=$arHeader["id"];?>">
					<?}
					echo $arHeader["name"]."</div>";
				}
				unset($arHeader);?>
				<div class="basket-item-td basket-item-sep"></div>
			</div>
			<?$skipHeaders = array('PROPS', 'DELAY', 'DELETE', 'TYPE', 'PROPERTY_ARTNUMBER_VALUE', 'PROPERTY_OBJECT_VALUE', 'PROPERTY_M2_COUNT_VALUE', 'PROPERTY_OLD_PRICE_VALUE');
			foreach($arResult["GRID"]["ROWS"] as $arItem) {
				if($arItem["DELAY"] == "N" && $arItem["CAN_BUY"] == "Y") {?>
					<div class="basket-item-tr" id="<?=$arItem['ID']?>" data-item-name="<?=$arItem['NAME']?>" data-item-brand="<?=$arItem[$arParams['BRAND_PROPERTY'].'_VALUE']?>" data-item-price="<?=$arItem['PRICE']?>" data-item-currency="<?=$arItem['CURRENCY']?>" data-item-measure="<?=$arItem['MEASURE_SYMBOL_INTL']?>" data-entity="row">
						<div class="hidden-xs hidden-sm basket-item-td basket-item-sep"></div>
						<?foreach($arResult["GRID"]["HEADERS"] as $arHeader) {
							if(in_array($arHeader["id"], $skipHeaders)) //some values are not shown in the columns in this template
								continue;
							
							//ITEM//
							if($arHeader["id"] == "NAME") {?>									
								<div class="basket-item-td basket-item-item">
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
										//OBJECT//
										if($bObjectColumn && $arItem["PROPERTY_OBJECT_FULL_VALUE"]) {?>
											<span class="basket-item-object">
												<span id="col_<?=$bObjectColumnId?>"><?=$bObjectColumnTitle?></span>: <?=$arItem["PROPERTY_OBJECT_FULL_VALUE"]["NAME"]?>
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
								<div class="basket-item-td basket-item-quantity">										
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
								<div class="hidden-xs hidden-sm basket-item-td">
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
								<div class="hidden-xs hidden-sm basket-item-td basket-item-discount-percent">
									<div id="discount_value_<?=$arItem["ID"]?>"><?=$arItem["DISCOUNT_PRICE_PERCENT_FORMATED"]?></div>
								</div>
							<?//WEIGHT//
							} elseif($arHeader["id"] == "WEIGHT") {?>
								<div class="hidden-xs hidden-sm basket-item-td">										
									<?=$arItem["WEIGHT_FORMATED"]?>
								</div>
							<?//SUM//
							} elseif($arHeader["id"] == "SUM") {?>
								<div class="basket-item-td basket-item-sum">
									<div id="sum_<?=$arItem["ID"]?>"><?=$arItem[$arHeader["id"]]?></div>
								</div>
							<?//OTHER//
							} else {?>
								<div class="hidden-xs hidden-sm basket-item-td">
									<?=$arItem[$arHeader["id"]]?>
								</div>
							<?}
						}
						unset($arHeader);
						//CONTROLS//
						if((!$arParams["DISABLE_DELAY"] && $bDelayColumn) || $bDeleteColumn) {?>
							<div class="basket-item-td basket-item-sep" style="position: relative;">
								<div class="hidden-print basket-item-controls">									
									<?if(!$arParams["DISABLE_DELAY"] && $bDelayColumn) {?>
										<a class="basket-item-control" href="<?=str_replace('#ID#', $arItem['ID'], $arUrls['delay'])?>" title="<?=GetMessage('SALE_DELAY')?>"><i class="icon-heart"></i></a>
									<?}
									if($bDeleteColumn) {?>
										<a class="basket-item-control" href="<?=str_replace('#ID#', $arItem['ID'], $arUrls['delete'])?>" onclick="return deleteProductRow(this)" title="<?=GetMessage('SALE_DELETE')?>"><i class="icon-close"></i></a>
									<?}?>
								</div>
							</div>
						<?} else {?>
							<div class="hidden-xs hidden-sm basket-item-td basket-item-sep"></div>
						<?}?>
					</div>
				<?}
			}
			unset($arItem);
			//TOTAL_WEIGHT//
			if($bWeightColumn && floatval($arResult['allWeight']) > 0) {?>
				<div class="hidden-xs hidden-sm basket-item-tr">
					<div class="basket-item-td basket-item-sep"></div>
					<?foreach($arResult["GRID"]["HEADERS"] as $arHeader) {
						if(in_array($arHeader["id"], $skipHeaders)) //some values are not shown in the columns in this template
							continue;
						if($arHeader["id"] == "PRICE") {?>
							<div class="basket-item-td basket-item-total-title"><?=GetMessage("SALE_TOTAL_WEIGHT")?></div>
						<?} elseif($arHeader["id"] == "SUM") {?>
							<div class="basket-item-td basket-item-total-val">
								<span id="allWeight_FORMATED"><?=$arResult["allWeight_FORMATED"]?></span>
							</div>
						<?} else {?>
							<div class="basket-item-td"></div>
						<?}
					}
					unset($arHeader);?>
					<div class="basket-item-td basket-item-sep"></div>
				</div>
			<?}
			//VAT//
			if($arParams["PRICE_VAT_SHOW_VALUE"] == "Y") {?>
				<div class="hidden-xs hidden-sm basket-item-tr">
					<div class="basket-item-td basket-item-sep"></div>
					<?foreach($arResult["GRID"]["HEADERS"] as $arHeader) {
						if(in_array($arHeader["id"], $skipHeaders)) //some values are not shown in the columns in this template
							continue;
						if($arHeader["id"] == "PRICE") {?>
							<div class="basket-item-td basket-item-total-title"><?=GetMessage("SALE_VAT_EXCLUDED")?></div>
						<?} elseif($arHeader["id"] == "SUM") {?>
							<div class="basket-item-td">
								<span class="basket-item-total-val" id="allSum_wVAT_FORMATED"><?=$arResult["allSum_wVAT_FORMATED"]?></span>
							</div>
						<?} else {?>
							<div class="basket-item-td"></div>
						<?}
					}
					unset($arHeader);?>
					<div class="basket-item-td basket-item-sep"></div>
				</div>
				<?if(floatval($arResult['allVATSum']) > 0) {?>
					<div class="hidden-xs hidden-sm basket-item-tr">
						<div class="basket-item-td basket-item-sep"></div>
						<?foreach($arResult["GRID"]["HEADERS"] as $arHeader) {
							if(in_array($arHeader["id"], $skipHeaders)) //some values are not shown in the columns in this template
								continue;
							if($arHeader["id"] == "PRICE") {?>
								<div class="basket-item-td basket-item-total-title"><?=GetMessage("SALE_VAT")?></div>
							<?} elseif($arHeader["id"] == "SUM") {?>
								<div class="basket-item-td">
									<span class="basket-item-total-val" id="allVATSum_FORMATED"><?=$arResult["allVATSum_FORMATED"]?></span>
								</div>
							<?} else {?>
								<div class="basket-item-td"></div>
							<?}
						}
						unset($arHeader);?>
						<div class="basket-item-td basket-item-sep"></div>
					</div>
				<?}
			}
			//TOTAL//?>
			<div class="basket-item-tr">
				<div class="hidden-print basket-item-td basket-item-sep">
					<a class="hidden-md hidden-lg btn btn-default" href="<?=$arUrls['clear']?>" role="button"><i class="icon-trash"></i><span><?=GetMessage("SALE_BASKET_CLEAR")?></span></a>
				</div>				
				<?foreach($arResult["GRID"]["HEADERS"] as $arHeader) {
					if(in_array($arHeader["id"], $skipHeaders)) //some values are not shown in the columns in this template
						continue;
					if($arHeader["id"] == "PRICE") {?>
						<div class="hidden-xs hidden-sm basket-item-td basket-item-total-title"><?=GetMessage("SALE_TOTAL")?></div>
					<?} elseif($arHeader["id"] == "SUM") {?>
						<div class="basket-item-td">
							<span class="basket-item-total-val" id="allSum_FORMATED"><?=$arResult["allSum_FORMATED"]?></span>
							<?$showTotalPrice = (float)$arResult["DISCOUNT_PRICE_ALL"] > 0;?>
							<span class="basket-item-old-price" id="PRICE_WITHOUT_DISCOUNT" style="display: <?=($showTotalPrice ? 'block' : 'none');?>;"><?=($showTotalPrice ? $arResult["PRICE_WITHOUT_DISCOUNT"] : '');?></span>
							<span class="basket-item-discount" id="DISCOUNT_PRICE_ALL_FORMATED" style="display: <?=($showTotalPrice ? 'block' : 'none');?>;"><?=($showTotalPrice ? GetMessage("SALE_TOTAL_DISCOUNT").' '.$arResult["DISCOUNT_PRICE_ALL_FORMATED"] : '');?></span>
						</div>
					<?} else {?>
						<div class="hidden-xs hidden-sm basket-item-td"></div>
					<?}
				}
				unset($arHeader);?>
				<div class="hidden-xs hidden-sm basket-item-td basket-item-sep"></div>
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
		<div class="hidden-print bx_ordercart_order_pay">
			<div class="bx_ordercart_order_pay_left" id="coupons_block">
				<?//COUPON//
				if($arParams["HIDE_COUPON"] != "Y") {?>
					<div class="bx_ordercart_coupon">						
						<input type="text" id="coupon" name="COUPON" onchange="enterCoupon();" placeholder="<?=GetMessage('STB_COUPON_PROMT')?>" value="" />
						<button type="button" class="btn btn-buy" onclick="enterCoupon();" title="<?=GetMessage('SALE_COUPON_APPLY_TITLE');?>"><i class="icon-arrow-right"></i></button>
					</div>
					<?if(!empty($arResult['COUPON_LIST'])) {
						foreach($arResult['COUPON_LIST'] as $oneCoupon) {
							$couponClass = 'disabled';
							switch($oneCoupon['STATUS']) {
								case DiscountCouponsManager::STATUS_NOT_FOUND:
								case DiscountCouponsManager::STATUS_FREEZE:
									$couponClass = 'bad';
									break;
								case DiscountCouponsManager::STATUS_APPLYED:
									$couponClass = 'good';
									break;
							}?>
							<div class="bx_ordercart_coupon">
								<input type="hidden" name="OLD_COUPON[]" value="<?=htmlspecialcharsbx($oneCoupon['COUPON']);?>" />
								<span class="bx_ordercart_coupon_note <?=$couponClass?>">
									<?=htmlspecialcharsbx($oneCoupon['COUPON']);
									if(isset($oneCoupon['CHECK_CODE_TEXT'])) {
										echo (is_array($oneCoupon['CHECK_CODE_TEXT']) ? ' '.implode('<br>', $oneCoupon['CHECK_CODE_TEXT']) : ' '.$oneCoupon['CHECK_CODE_TEXT']);
									}?>
								</span>
								<span class="bx_ordercart_coupon_close_container"><span class="bx_ordercart_coupon_close" data-coupon="<?=htmlspecialcharsbx($oneCoupon['COUPON'])?>"></span></span>
							</div>
						<?}
						unset($couponClass, $oneCoupon);
					}
				}?>
			</div>			
			<div class="bx_ordercart_order_pay_right">
				<?//BUTTONS//
				$hasObject = false;
				foreach($arResult["GRID"]["ROWS"] as $arItem) {
					if($arItem["PROPERTY_OBJECT_FULL_VALUE"]) {
						$hasObject = true;
						break;
					}
				}
				unset($arItem);

				if($arParams["USE_PREPAYMENT"] == "Y" && strlen($arResult["PREPAY_BUTTON"]) > 0 && !$hasObject) {
					echo $arResult["PREPAY_BUTTON"];?>
					<span><?=GetMessage("SALE_OR")?></span>
				<?}
				if($arParams["AUTO_CALCULATION"] != "Y") {?>
					<button type="button" class="btn btn-default" onclick="updateBasket();"><?=GetMessage("SALE_REFRESH")?></button>
				<?}
				if($arParams["QUICK_ORDER"]) {?>
					<button type="button" class="btn btn-primary"<?=($arParams["MIN_ORDER_SUM"] > 0 && $arResult["allSum"] < $arParams["MIN_ORDER_SUM"] ? " disabled" : "")?> data-entity="quickOrder" data-has-object="<?=($hasObject ? 'true' : 'false')?>"><?=GetMessage("SALE_QUICK_ORDER")?></button>
				<?}
				if(!$arParams["DISABLE_ORDER"] && !$hasObject) {?>
					<button type="button" class="btn btn-buy"<?=($arParams["MIN_ORDER_SUM"] > 0 && $arResult["allSum"] < $arParams["MIN_ORDER_SUM"] ? " disabled" : "")?> data-entity="checkOut"><span><?=GetMessage("SALE_ORDER")?></span></button>
				<?}?>
			</div>
			<div class="clr"></div>
		</div>
	<?} else {
		ShowNote(GetMessage("SALE_NO_ITEMS"), "warning");
	}?>
</div>