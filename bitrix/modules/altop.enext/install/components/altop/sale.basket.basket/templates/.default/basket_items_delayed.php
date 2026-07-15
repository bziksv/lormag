<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$bPriceType = false;
$bPropsColumn = false;
$bDeleteColumn = false;
$bWeightColumn = false;
$bArticleColumn = false;
$bObjectColumn = false;?>

<div id="basket_items_delayed" style="<?=(!$arParams['DISABLE_BASKET'] ? 'display: none;' : '')?>">
	<?if($delayCount > 0) {?>
		<div class="basket-items" id="delayed_items">
			<div class="hidden-xs hidden-sm basket-item-tr basket-item-thead">	
				<div class="basket-item-td basket-item-sep"></div>
				<?foreach($arResult["GRID"]["HEADERS"] as $arHeader) {
					if(in_array($arHeader["id"], array("TYPE"))) {
						$bPriceType = true;
						continue;
					} elseif($arHeader["id"] == "PROPS") {
						$bPropsColumn = true;
						continue;
					} elseif($arHeader["id"] == "DELAY") {
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
						<div class="basket-item-td basket-item-item">
					<?} else {?>
						<div class="basket-item-td">
					<?}
					echo $arHeader["name"]."</div>";
				}
				unset($arHeader);?>
				<div class="basket-item-td basket-item-sep"></div>
			</div>
			<?$skipHeaders = array('PROPS', 'DELAY', 'DELETE', 'TYPE', 'PROPERTY_ARTNUMBER_VALUE', 'PROPERTY_OBJECT_VALUE', 'PROPERTY_M2_COUNT_VALUE', 'PROPERTY_OLD_PRICE_VALUE');
			foreach($arResult["GRID"]["ROWS"] as $arItem) {
				if($arItem["DELAY"] == "Y" && $arItem["CAN_BUY"] == "Y") {?>
					<div class="basket-item-tr" id="<?=$arItem['ID']?>" data-entity="row">
						<div class="hidden-xs hidden-sm basket-item-td basket-item-sep"></div>
						<?foreach($arResult["GRID"]["HEADERS"] as $arHeader) {
							if(in_array($arHeader["id"], $skipHeaders))
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
																<li class="basket-item-sku-item-color<?=$selected?>" style="<?=(!empty($arSkuValue['CODE']) ? 'background-color: #'.$arSkuValue['CODE'].';' : (!empty($arSkuValue['PICT']) ? 'background-image: url('.$arSkuValue['PICT']['SRC'].');' : ''));?>"></li>
															<?} else {?>
																<li class="basket-item-sku-item-text<?=$selected?>">
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
										<input type="hidden" name="DELAY_<?=$arItem["ID"]?>" value="Y"/>
									</div>
								</div>
							<?//QUANTITY//
							} elseif($arHeader["id"] == "QUANTITY") {?>
								<div class="basket-item-td basket-item-quantity">
									<?if($bSqMColumn && $arItem[$bSqMColumnId] && ($arItem["MEASURE_SYMBOL_INTL"] == "pc. 1" || $arItem["MEASURE_SYMBOL_INTL"] == "m2")) {
										echo $arItem["PC_QUANTITY"]." ".GetMessage("SALE_MEASURE_PC")."<br />".$arItem["SQ_M_QUANTITY"]." ".GetMessage("SALE_MEASURE_SQ_M");
									} else {
										echo $arItem["QUANTITY"].(isset($arItem["MEASURE_TEXT"]) ? " ".htmlspecialcharsbx($arItem["MEASURE_TEXT"]) : "");
									}?>
								</div>
							<?//PRICE//
							} elseif($arHeader["id"] == "PRICE") {?>
								<div class="hidden-xs hidden-sm basket-item-td">
									<div class="basket-item-current-price">
										<?=($arItem["SQ_M_PRICE"] ? $arItem["SQ_M_PRICE_FORMATED"] : $arItem["PRICE_FORMATED"]).($bSqMColumn && $arItem[$bSqMColumnId] && ($arItem["MEASURE_SYMBOL_INTL"] == "pc. 1" || $arItem["MEASURE_SYMBOL_INTL"] == "m2") ? " /".GetMessage("SALE_MEASURE_SQ_M") : "");?>
									</div>
									<?if(doubleval($arItem["DISCOUNT_PRICE_PERCENT"]) > 0) {?>
										<div class="basket-item-old-price"><?=($arItem["SQ_M_FULL_PRICE"] ? $arItem["SQ_M_FULL_PRICE_FORMATED"] : $arItem["FULL_PRICE_FORMATED"])?></div>
									<?}
									if($bPriceType && strlen($arItem["NOTES"]) > 0) {?>
										<div class="basket-item-type-price"><?=GetMessage("SALE_TYPE")?></div>
										<div class="basket-item-type-price-value"><?=$arItem["NOTES"]?></div>
									<?}?>
								</div>
							<?//DISCOUNT_PERCENT//
							} elseif($arHeader["id"] == "DISCOUNT") {?>
								<div class="hidden-xs hidden-sm basket-item-td basket-item-discount-percent">
									<?=$arItem["DISCOUNT_PRICE_PERCENT_FORMATED"]?>
								</div>
							<?//WEIGHT//
							} elseif($arHeader["id"] == "WEIGHT") {?>
								<div class="hidden-xs hidden-sm basket-item-td">										
									<?=$arItem["WEIGHT_FORMATED"]?>
								</div>
							<?//SUM//
							} elseif($arHeader["id"] == "SUM") {?>
								<div class="basket-item-td basket-item-sum">
									<?=$arItem[$arHeader["id"]]?>
								</div>
							<?//OTHER//
							} else {?>
								<div class="hidden-xs hidden-sm basket-item-td">
									<?=$arItem[$arHeader["id"]]?>
								</div>
							<?}
						}
						unset($arHeader);
						//CONTROLS//?>
						<div class="basket-item-td basket-item-sep" style="position: relative;">
							<div class="hidden-print basket-item-controls">									
								<?if(!$arParams['DISABLE_BASKET']) {?>
									<a class="basket-item-control" href="<?=str_replace('#ID#', $arItem['ID'], $arUrls['add'])?>" title="<?=GetMessage('SALE_ADD_TO_BASKET')?>"><i class="icon-cart"></i></a>
								<?}
								if($bDeleteColumn) {?>
									<a class="basket-item-control" href="<?=str_replace('#ID#', $arItem['ID'], $arUrls['delete'])?>" onclick="return deleteProductRow(this)" title="<?=GetMessage('SALE_DELETE')?>"><i class="icon-close"></i></a>
								<?}?>
							</div>
						</div>
					</div>
				<?}
			}
			unset($arItem);
			//CLEAR_DELAYED//?>
			<div class="hidden-md hidden-lg basket-item-tr">
				<div class="basket-item-td basket-item-sep">
					<a class="btn btn-default" href="<?=$arUrls['clearDelay']?>" role="button"><i class="icon-trash"></i><span><?=GetMessage("SALE_BASKET_CLEAR_DELAYED")?></span></a>
				</div>
				<div class="basket-item-td"></div>
			</div>
		</div>
	<?} else {
		ShowNote(GetMessage("SALE_DELAYED_NO_ITEMS"), "warning");
	}?>
</div>