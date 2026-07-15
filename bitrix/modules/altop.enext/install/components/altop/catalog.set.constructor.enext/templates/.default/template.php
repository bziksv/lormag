<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

$this->setFrameMode(true);

$templateData = array(
	"CURRENCIES" => CUtil::PhpToJSObject($arResult["CURRENCIES"], false, true, true)
);

$mainId = $this->GetEditAreaId($arResult["ELEMENT"]["ID"]);
$obName = "ob".preg_replace("/[^a-zA-Z0-9_]/", "x", $mainId);?>

<div id="bx-set-const-<?=$mainId?>" class="catalog-set-constructor">
	<div class="h2"><?=Loc::getMessage("CATALOG_SET_TITLE")?></div>
	<div class="row">
		<div class="col-xs-12 col-md-4">
			<?//ELEMENT//?>
			<div class="catalog-set-constructor-item-container catalog-set-constructor-original-item">
				<div class="catalog-set-constructor-item">
					<div class="catalog-set-constructor-item-image-wrapper">
						<div class="catalog-set-constructor-item-image">
							<?//PREVIEW_PICTURE//
							if(is_array($arResult["ELEMENT"]["PREVIEW_PICTURE"])) {?>
								<img src="<?=$arResult['ELEMENT']['PREVIEW_PICTURE']['SRC']?>" width="<?=$arResult['ELEMENT']['PREVIEW_PICTURE']['WIDTH']?>" height="<?=$arResult['ELEMENT']['PREVIEW_PICTURE']['HEIGHT']?>" alt="<?=$arResult['ELEMENT']['NAME']?>" title="<?=$arResult['ELEMENT']['NAME']?>" />
							<?} else {?>
								<img src="<?=SITE_TEMPLATE_PATH?>/images/no_photo.png" width="222" height="222" alt="<?=$arResult['ELEMENT']['NAME']?>" title="<?=$arResult['ELEMENT']['NAME']?>" />
							<?}
							//BRAND//
							if(!empty($arResult["ELEMENT"]["PROPERTIES"]["BRAND"]["FULL_VALUE"]["PREVIEW_PICTURE"])) {?>
								<div class="catalog-set-constructor-item-brand">
									<img src="<?=$arResult['ELEMENT']['PROPERTIES']['BRAND']['FULL_VALUE']['PREVIEW_PICTURE']['SRC']?>" width="<?=$arResult['ELEMENT']['PROPERTIES']['BRAND']['FULL_VALUE']['PREVIEW_PICTURE']['WIDTH']?>" height="<?=$arResult['ELEMENT']['PROPERTIES']['BRAND']['FULL_VALUE']['PREVIEW_PICTURE']['HEIGHT']?>" alt="<?=$arResult['ELEMENT']['PROPERTIES']['BRAND']['FULL_VALUE']['NAME']?>" title="<?=$arResult['ELEMENT']['PROPERTIES']['BRAND']['FULL_VALUE']['NAME']?>" />
								</div>
							<?}?>
						</div>
					</div>
					<?//TITLE//?>
					<div class="catalog-set-constructor-item-title"><span><?=$arResult["ELEMENT"]["NAME"]?></span></div>
					<div class="catalog-set-constructor-item-info-container">
						<div class="catalog-set-constructor-item-info-block">
							<?//BASKET_PROPERTIES//
							if(!$arResult["ELEMENT"]["OBJECT"] && !$arResult["ELEMENT"]["PARTNERS_URL"]) {
								if($arParams["ADD_PROPERTIES_TO_BASKET"] === "Y" && !empty($arResult["ELEMENT"]["PRODUCT_PROPERTIES"])) {?>
									<div class="catalog-set-constructor-item-info">
										<?if(!empty($arResult["ELEMENT"]["PRODUCT_PROPERTIES_FILL"])) {
											foreach($arResult["ELEMENT"]["PRODUCT_PROPERTIES_FILL"] as $propId => $propInfo) {?>
												<input type="hidden" name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$arResult['ELEMENT']['ID']?>][<?=$propId?>]" value="<?=htmlspecialcharsbx($propInfo['ID'])?>" />
												<?unset($arResult["ELEMENT"]["PRODUCT_PROPERTIES"][$propID]);
											}
										}
										if(!empty($arResult["ELEMENT"]["PRODUCT_PROPERTIES"])) {
											foreach($arResult["ELEMENT"]["PRODUCT_PROPERTIES"] as $propId => $propInfo) {?>
												<div class="catalog-set-constructor-basket-props-container">
													<div class="catalog-set-constructor-basket-props-title"><?=$arResult["ELEMENT"]["PROPERTIES"][$propId]["NAME"]?></div>
													<div class="catalog-set-constructor-basket-props-block">
														<?if($arResult["ELEMENT"]["PROPERTIES"][$propId]["PROPERTY_TYPE"] === "L" && $arResult["ELEMENT"]["PROPERTIES"][$propId]["LIST_TYPE"] === "C") {?>
															<div class="catalog-set-constructor-basket-props-input-radio">
																<?foreach($propInfo["VALUES"] as $valueId => $value) {?>
																	<label>
																		<input type="radio" name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$arResult['ELEMENT']['ID']?>][<?=$propId?>]" value="<?=$valueId?>"<?=($valueId == $propInfo["SELECTED"] ? " checked='checked'" : "");?> />
																		<span class="check-container">
																			<span class="check"><i class="icon-ok-b"></i></span>
																		</span>
																		<span class="text" title="<?=$value?>"><?=$value?></span>
																	</label>
																<?}?>
															</div>
														<?} else {?>
															<div class="catalog-set-constructor-basket-props-drop-down" onclick="<?=$obName?>.showBasketPropsDropDownPopup(this, '<?=$propId?>', '<?=$arResult['ELEMENT']['ID']?>');">
																<?$currId = $currVal = false;
																foreach($propInfo["VALUES"] as $valueId => $value) {
																	if($valueId == $propInfo["SELECTED"]) {
																		$currId = $valueId;
																		$currVal = $value;
																	}
																}
																unset($value);?>
																<input type="hidden" name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$arResult['ELEMENT']['ID']?>][<?=$propId?>]" value="<?=(!empty($currId) ? $currId : '');?>" />
																<div class="drop-down-text" data-entity="current-option"><?=(!empty($currVal) ? $currVal : "");?></div>
																<?unset($currVal, $currId);?>
																<div class="drop-down-arrow"><i class="icon-arrow-down"></i></div>
																<div class="drop-down-popup" data-entity="dropdownContent" style="display: none;">
																	<ul>
																		<?foreach($propInfo["VALUES"] as $valueId => $value) {?>
																			<li><span onclick="<?=$obName?>.selectBasketPropsDropDownPopupItem(this, '<?=$valueId?>');"><?=$value?></span></li>
																		<?}
																		unset($value);?>
																	</ul>
																</div>
															</div>
														<?}?>
													</div>
												</div>
											<?}
										}?>
									</div>
								<?}
							}?>
							<div class="catalog-set-constructor-item-info">
								<div class="catalog-set-constructor-item-blocks">
									<?//PRICE//?>
									<div class="catalog-set-constructor-item-price-container">
										<?if($arResult["ELEMENT"]["PRICE"]["RATIO_DISCOUNT_VALUE"] > 0) {?>
											<div class="catalog-set-constructor-price-current"><?=$arResult["ELEMENT"]["PRICE"]["PRINT_RATIO_DISCOUNT_VALUE"]?><span class="catalog-set-constructor-price-measure">/<?=$arResult["ELEMENT"]["BASKET_QUANTITY"]." ".$arResult["ELEMENT"]["MEASURE"]["SYMBOL"]?></span></div>
											<?if($arResult["ELEMENT"]["PRICE"]["RATIO_DISCOUNT_VALUE"] != $arResult["ELEMENT"]["PRICE"]["RATIO_VALUE"]) {?>
												<div class="catalog-set-constructor-price-old"><?=$arResult["ELEMENT"]["PRICE"]["PRINT_RATIO_VALUE"]?></div>
												<div class="catalog-set-constructor-price-economy"><?=Loc::getMessage("CATALOG_SET_ECONOMY_PRICE")." ".$arResult["ELEMENT"]["PRICE"]["PRINT_RATIO_DISCOUNT_DIFF"]?></div>
											<?}
										} else {?>
											<div class="catalog-set-constructor-price-not-set"><?=Loc::getMessage("CATALOG_SET_PRICE_NOT_SET")?></div>
										<?}?>
									</div>
								</div>
								<?//BUTTONS//?>
								<div class="catalog-set-constructor-item-button-container"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-md-8">
			<?//DEFAULT_ITEMS//?>
			<div class="catalog-set-constructor-added-items" data-role="set-items">				
				<?foreach($arResult["SET_ITEMS"]["DEFAULT"] as $arItem) {?>
					<div class="catalog-set-constructor-added-item-row catalog-set-constructor-added-item" data-id="<?=$arItem['ID']?>" data-name="<?=$arItem['NAME']?>" data-section-id="<?=$arItem['IBLOCK_SECTION_ID']?>" data-img="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" data-url="<?=$arItem['DETAIL_PAGE_URL']?>" data-brand-name="<?=$arItem['PROPERTIES']['BRAND']['FULL_VALUE']['NAME']?>" data-brand-img="<?=$arItem['PROPERTIES']['BRAND']['FULL_VALUE']['PREVIEW_PICTURE']['SRC']?>" data-ratio-price="<?=$arItem['PRICE']['RATIO_DISCOUNT_VALUE']?>" data-print-ratio-price="<?=$arItem['PRICE']['PRINT_RATIO_DISCOUNT_VALUE']?>" data-ratio-old-price="<?=$arItem['PRICE']['RATIO_VALUE']?>" data-print-ratio-old-price="<?=$arItem['PRICE']['PRINT_RATIO_VALUE']?>" data-ratio-diff-price="<?=$arItem['PRICE']['RATIO_DISCOUNT_DIFF']?>" data-print-ratio-diff-price="<?=$arItem['PRICE']['PRINT_RATIO_DISCOUNT_DIFF']?>" data-measure="<?=$arItem['MEASURE']['SYMBOL']?>" data-quantity="<?=$arItem['BASKET_QUANTITY']?>">
						<?//PREVIEW_PICTURE//?>
						<div class="catalog-set-constructor-added-item-cell catalog-set-constructor-added-item-image-wrapper">
							<a class="catalog-set-constructor-added-item-image" href="<?=$arItem['DETAIL_PAGE_URL']?>" title="<?=$arItem['NAME']?>">
								<?if(is_array($arItem["PREVIEW_PICTURE"])) {?>
									<img src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" width="<?=$arItem['PREVIEW_PICTURE']['WIDTH']?>" height="<?=$arItem['PREVIEW_PICTURE']['HEIGHT']?>" alt="<?=$arItem['NAME']?>" title="<?=$arItem['NAME']?>" />
								<?} else {?>
									<img src="<?=SITE_TEMPLATE_PATH?>/images/no_photo.png" width="222" height="222" alt="<?=$arItem['NAME']?>" title="<?=$arItem['NAME']?>" />
								<?}?>
							</a>
						</div>
						<div class="catalog-set-constructor-added-item-cell catalog-set-constructor-added-item-caption">
							<?//TITLE//?>
							<a class="catalog-set-constructor-added-item-title" href="<?=$arItem['DETAIL_PAGE_URL']?>" title="<?=$arItem['NAME']?>"><?=$arItem["NAME"]?></a>
							<?//BASKET_PROPERTIES//
							if(!$arItem["OBJECT"] && !$arItem["PARTNERS_URL"]) {
								if($arParams["ADD_PROPERTIES_TO_BASKET"] === "Y" && !empty($arItem["PRODUCT_PROPERTIES"])) {?>
									<div class="catalog-set-constructor-added-item-info">
										<?if(!empty($arItem["PRODUCT_PROPERTIES_FILL"])) {
											foreach($arItem["PRODUCT_PROPERTIES_FILL"] as $propId => $propInfo) {?>
												<input type="hidden" name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$arItem['ID']?>][<?=$propId?>]" value="<?=htmlspecialcharsbx($propInfo['ID'])?>" />
												<?unset($arItem["PRODUCT_PROPERTIES"][$propID]);
											}
										}
										if(!empty($arItem["PRODUCT_PROPERTIES"])) {
											foreach($arItem["PRODUCT_PROPERTIES"] as $propId => $propInfo) {?>
												<div class="catalog-set-constructor-basket-props-container">
													<div class="catalog-set-constructor-basket-props-title"><?=$arItem["PROPERTIES"][$propId]["NAME"]?></div>
													<div class="catalog-set-constructor-basket-props-block">
														<?if($arItem["PROPERTIES"][$propId]["PROPERTY_TYPE"] === "L" && $arItem["PROPERTIES"][$propId]["LIST_TYPE"] === "C") {?>
															<div class="catalog-set-constructor-basket-props-input-radio">
																<?foreach($propInfo["VALUES"] as $valueId => $value) {?>
																	<label>
																		<input type="radio" name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$arItem['ID']?>][<?=$propId?>]" value="<?=$valueId?>"<?=($valueId == $propInfo["SELECTED"] ? " checked='checked'" : "");?> />
																		<span class="check-container">
																			<span class="check"><i class="icon-ok-b"></i></span>
																		</span>
																		<span class="text" title="<?=$value?>"><?=$value?></span>
																	</label>
																<?}?>
															</div>
														<?} else {?>
															<div class="catalog-set-constructor-basket-props-drop-down" onclick="<?=$obName?>.showBasketPropsDropDownPopup(this, '<?=$propId?>', '<?=$arItem['ID']?>');">
																<?$currId = $currVal = false;
																foreach($propInfo["VALUES"] as $valueId => $value) {
																	if($valueId == $propInfo["SELECTED"]) {
																		$currId = $valueId;
																		$currVal = $value;
																	}
																}
																unset($value);?>
																<input type="hidden" name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$arItem['ID']?>][<?=$propId?>]" value="<?=(!empty($currId) ? $currId : '');?>" />
																<div class="drop-down-text" data-entity="current-option"><?=(!empty($currVal) ? $currVal : "");?></div>
																<?unset($currVal, $currId);?>
																<div class="drop-down-arrow"><i class="icon-arrow-down"></i></div>
																<div class="drop-down-popup" data-entity="dropdownContent" style="display: none;">
																	<ul>
																		<?foreach($propInfo["VALUES"] as $valueId => $value) {?>
																			<li><span onclick="<?=$obName?>.selectBasketPropsDropDownPopupItem(this, '<?=$valueId?>');"><?=$value?></span></li>
																		<?}
																		unset($value);?>
																	</ul>
																</div>
															</div>
														<?}?>
													</div>
												</div>
											<?}
										}?>
									</div>
								<?}
							}?>
						</div>
						<?//PRICE//?>
						<div class="catalog-set-constructor-added-item-cell catalog-set-constructor-added-item-price-container">
							<div class="catalog-set-constructor-price-current"><?=$arItem["PRICE"]["PRINT_RATIO_DISCOUNT_VALUE"]?><span class="catalog-set-constructor-price-measure">/<?=$arItem["BASKET_QUANTITY"]." ".$arItem["MEASURE"]["SYMBOL"]?></span></div>
							<?if($arItem["PRICE"]["RATIO_DISCOUNT_VALUE"] != $arItem["PRICE"]["RATIO_VALUE"]) {?>
								<div class="catalog-set-constructor-price-old"><?=$arItem["PRICE"]["PRINT_RATIO_VALUE"]?></div>
								<div class="catalog-set-constructor-price-economy"><?=Loc::getMessage("CATALOG_SET_ECONOMY_PRICE")." ".$arItem["PRICE"]["PRINT_RATIO_DISCOUNT_DIFF"]?></div>
							<?}?>
						</div>
						<?//DELETE//?>
						<div class="catalog-set-constructor-added-item-cell catalog-set-constructor-added-item-delete"><i class="icon-close" data-role="set-delete-btn"></i></div>
					</div>
					<?//SEPARATE//?>
					<div class="catalog-set-constructor-added-item-sep"></div>
				<?}
				unset($arItem);
				//ALERT//?>
				<div class="alert" style="display: none;" data-set-message="empty-set"></div>
			</div>
			<?//RESULT//?>
			<div class="catalog-set-constructor-result">
				<div class="catalog-set-constructor-price-current" data-role="set-price"><?=$arResult["SET_ITEMS"]["PRICE"]["DISCOUNT_VALUE"]?></div>
				<div class="catalog-set-constructor-price-old" data-role="set-old-price"<?=($arResult["SHOW_DEFAULT_SET_DISCOUNT"] ? "" : " style='display: none;'")?>><?=$arResult["SET_ITEMS"]["PRICE"]["VALUE"]?></div>
				<div class="catalog-set-constructor-price-economy" data-role="set-diff-price"<?=($arResult["SHOW_DEFAULT_SET_DISCOUNT"] ? "" : " style='display: none;'")?>><?=Loc::getMessage("CATALOG_SET_ECONOMY_PRICE")." ".$arResult["SET_ITEMS"]["PRICE"]["DISCOUNT_DIFF"]?></div>
				<?if(!$arParams["DISABLE_BASKET"]) {?>
					<button type="button" class="btn btn-buy" data-role="set-buy-btn"<?=($arResult["ELEMENT"]["CAN_BUY"] && $arResult["ELEMENT"]["PRICE"]["DISCOUNT_VALUE"] > 0 && (!$arResult["ELEMENT"]["OBJECT"] || ($arResult["ELEMENT"]["OBJECT"] && $arResult["ELEMENT"]["OBJECT_CONTACTS"])) && !$arResult["ELEMENT"]["PARTNERS_URL"] ? "" : " disabled='disabled'")?>><i class="icon-cart"></i><span><?=Loc::getMessage("CATALOG_SET_BUY")?></span></button>
				<?}?>
			</div>
		</div>
	</div>
	<?//OTHER_ITEMS//?>
	<div class="catalog-set-constructor-other-items" data-role="set-other-items">
		<div class="h2"><?=Loc::getMessage("CATALOG_SET_OTHER_ITEMS_TITLE")?></div>
		<div class="catalog-set-constructor-tabs-tabs" data-entity="set-tabs">
			<?foreach($arResult["SET_ITEMS"]["SECTIONS"] as $arSetSection) {?>
				<div class="catalog-set-constructor-tabs-tab" data-entity="tab" data-value="<?=$arSetSection['ID']?>"><?=$arSetSection["NAME"]?><span><?=count($arSetSection["ITEMS"])?></span></div>
			<?}
			unset($arSetSection);?>
		</div>
		<div class="catalog-set-constructor-tabs-content" data-entity="set-tabs-content">
			<?foreach($arResult["SET_ITEMS"]["SECTIONS"] as $arSetSection) {?>
				<div class="catalog-set-constructor-tabs-box" data-entity="set-tab-content" data-value="<?=$arSetSection['ID']?>">
					<div class="row">
						<?foreach($arSetSection["ITEMS"] as $arItem) {?>
							<div class="col-xs-12 col-md-4">
								<div class="catalog-set-constructor-item-container catalog-set-constructor-other-item" data-id="<?=$arItem['ID']?>" data-name="<?=$arItem['NAME']?>" data-section-id="<?=$arItem['IBLOCK_SECTION_ID']?>" data-img="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" data-url="<?=$arItem['DETAIL_PAGE_URL']?>" data-brand-name="<?=$arItem['PROPERTIES']['BRAND']['FULL_VALUE']['NAME']?>" data-brand-img="<?=$arItem['PROPERTIES']['BRAND']['FULL_VALUE']['PREVIEW_PICTURE']['SRC']?>" data-ratio-price="<?=$arItem['PRICE']['RATIO_DISCOUNT_VALUE']?>" data-print-ratio-price="<?=$arItem['PRICE']['PRINT_RATIO_DISCOUNT_VALUE']?>" data-ratio-old-price="<?=$arItem['PRICE']['RATIO_VALUE']?>" data-print-ratio-old-price="<?=$arItem['PRICE']['PRINT_RATIO_VALUE']?>" data-ratio-diff-price="<?=$arItem['PRICE']['RATIO_DISCOUNT_DIFF']?>" data-print-ratio-diff-price="<?=$arItem['PRICE']['PRINT_RATIO_DISCOUNT_DIFF']?>" data-measure="<?=$arItem['MEASURE']['SYMBOL']?>" data-quantity="<?=$arItem['BASKET_QUANTITY']?>">
									<div class="catalog-set-constructor-item">
										<div class="catalog-set-constructor-item-image-wrapper">
											<a class="catalog-set-constructor-item-image" href="<?=$arItem['DETAIL_PAGE_URL']?>" title="<?=$arItem['NAME']?>">
												<?//PREVIEW_PICTURE//
												if(is_array($arItem["PREVIEW_PICTURE"])) {?>
													<img src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" width="<?=$arItem['PREVIEW_PICTURE']['WIDTH']?>" height="<?=$arItem['PREVIEW_PICTURE']['HEIGHT']?>" alt="<?=$arItem['NAME']?>" title="<?=$arItem['NAME']?>" />
												<?} else {?>
													<img src="<?=SITE_TEMPLATE_PATH?>/images/no_photo.png" width="222" height="222" alt="<?=$arItem['NAME']?>" title="<?=$arItem['NAME']?>" />
												<?}
												//BRAND//
												if(!empty($arItem["PROPERTIES"]["BRAND"]["FULL_VALUE"]["PREVIEW_PICTURE"])) {?>
													<span class="catalog-set-constructor-item-brand">
														<img src="<?=$arItem['PROPERTIES']['BRAND']['FULL_VALUE']['PREVIEW_PICTURE']['SRC']?>" width="<?=$arItem['PROPERTIES']['BRAND']['FULL_VALUE']['PREVIEW_PICTURE']['WIDTH']?>" height="<?=$arItem['PROPERTIES']['BRAND']['FULL_VALUE']['PREVIEW_PICTURE']['HEIGHT']?>" alt="<?=$arItem['PROPERTIES']['BRAND']['FULL_VALUE']['NAME']?>" title="<?=$arItem['PROPERTIES']['BRAND']['FULL_VALUE']['NAME']?>" />
													</span>
												<?}?>
											</a>
										</div>
										<?//TITLE//?>
										<div class="catalog-set-constructor-item-title"><a href="<?=$arItem['DETAIL_PAGE_URL']?>" title="<?=$arItem['NAME']?>"><?=$arItem["NAME"]?></a></div>
										<div class="catalog-set-constructor-item-info-container">
											<div class="catalog-set-constructor-item-info-block">
												<?//BASKET_PROPERTIES//
												if(!$arItem["OBJECT"] && !$arItem["PARTNERS_URL"]) {
													if($arParams["ADD_PROPERTIES_TO_BASKET"] === "Y" && !empty($arItem["PRODUCT_PROPERTIES"])) {?>
														<div class="catalog-set-constructor-item-hidden">
															<?if(!empty($arItem["PRODUCT_PROPERTIES_FILL"])) {
																foreach($arItem["PRODUCT_PROPERTIES_FILL"] as $propId => $propInfo) {?>
																	<input type="hidden" name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$arItem['ID']?>][<?=$propId?>]" value="<?=htmlspecialcharsbx($propInfo['ID'])?>" />
																	<?unset($arItem["PRODUCT_PROPERTIES"][$propID]);
																}
															}
															if(!empty($arItem["PRODUCT_PROPERTIES"])) {
																foreach($arItem["PRODUCT_PROPERTIES"] as $propId => $propInfo) {?>
																	<div class="catalog-set-constructor-basket-props-container">
																		<div class="catalog-set-constructor-basket-props-title"><?=$arItem["PROPERTIES"][$propId]["NAME"]?></div>
																		<div class="catalog-set-constructor-basket-props-block">
																			<?if($arItem["PROPERTIES"][$propId]["PROPERTY_TYPE"] === "L" && $arItem["PROPERTIES"][$propId]["LIST_TYPE"] === "C") {?>
																				<div class="catalog-set-constructor-basket-props-input-radio">
																					<?foreach($propInfo["VALUES"] as $valueId => $value) {?>
																						<label>
																							<input type="radio" name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$arItem['ID']?>][<?=$propId?>]" value="<?=$valueId?>"<?=($valueId == $propInfo["SELECTED"] ? " checked='checked'" : "");?> />
																							<span class="check-container">
																								<span class="check"><i class="icon-ok-b"></i></span>
																							</span>
																							<span class="text" title="<?=$value?>"><?=$value?></span>
																						</label>
																					<?}?>
																				</div>
																			<?} else {?>
																				<div class="catalog-set-constructor-basket-props-drop-down" onclick="<?=$obName?>.showBasketPropsDropDownPopup(this, '<?=$propId?>', '<?=$arItem['ID']?>');">
																					<?$currId = $currVal = false;
																					foreach($propInfo["VALUES"] as $valueId => $value) {
																						if($valueId == $propInfo["SELECTED"]) {
																							$currId = $valueId;
																							$currVal = $value;
																						}
																					}
																					unset($value);?>
																					<input type="hidden" name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$arItem['ID']?>][<?=$propId?>]" value="<?=(!empty($currId) ? $currId : '');?>" />
																					<div class="drop-down-text" data-entity="current-option"><?=(!empty($currVal) ? $currVal : "");?></div>
																					<?unset($currVal, $currId);?>
																					<div class="drop-down-arrow"><i class="icon-arrow-down"></i></div>
																					<div class="drop-down-popup" data-entity="dropdownContent" style="display: none;">
																						<ul>
																							<?foreach($propInfo["VALUES"] as $valueId => $value) {?>
																								<li><span onclick="<?=$obName?>.selectBasketPropsDropDownPopupItem(this, '<?=$valueId?>');"><?=$value?></span></li>
																							<?}
																							unset($value);?>
																						</ul>
																					</div>
																				</div>
																			<?}?>
																		</div>
																	</div>
																<?}
															}?>
														</div>
													<?}
												}?>
												<div class="catalog-set-constructor-item-info">
													<div class="catalog-set-constructor-item-blocks">
														<?//PRICE//?>
														<div class="catalog-set-constructor-item-price-container">
															<?if($arItem["PRICE"]["RATIO_DISCOUNT_VALUE"] > 0) {?>
																<div class="catalog-set-constructor-price-current"><?=$arItem["PRICE"]["PRINT_RATIO_DISCOUNT_VALUE"]?><span class="catalog-set-constructor-price-measure">/<?=$arItem["BASKET_QUANTITY"]." ".$arItem["MEASURE"]["SYMBOL"]?></span></div>
																<?if($arItem["PRICE"]["RATIO_DISCOUNT_VALUE"] != $arItem["PRICE"]["RATIO_VALUE"]) {?>
																	<div class="catalog-set-constructor-price-old"><?=$arItem["PRICE"]["PRINT_RATIO_VALUE"]?></div>
																	<div class="catalog-set-constructor-price-economy"><?=Loc::getMessage("CATALOG_SET_ECONOMY_PRICE")." ".$arItem["PRICE"]["PRINT_RATIO_DISCOUNT_DIFF"]?></div>
																<?}
															} else {?>
																<div class="catalog-set-constructor-price-not-set"><?=Loc::getMessage("CATALOG_SET_PRICE_NOT_SET")?></div>
															<?}?>
														</div>
													</div>
													<?//BUTTONS//?>
													<div class="catalog-set-constructor-item-button-container">
														<?if(($arItem["OBJECT"] && !$arItem["OBJECT_CONTACTS"]) || $arItem["PARTNERS_URL"]) {?>
															<a target="_blank" class="btn btn-buy" href="<?=$arItem['DETAIL_PAGE_URL']?>"><i class="icon-arrow-right"></i></a>
														<?} else {?>
															<button type="button" class="btn btn-buy" data-role="set-add-btn"<?=($arItem["CAN_BUY"] && $arItem["PRICE"]["RATIO_DISCOUNT_VALUE"] > 0 ? "" : " disabled='disabled'")?>><span>+</span></button>
														<?}?>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						<?}
						unset($arItem);?>
					</div>
				</div>
			<?}
			unset($arSetSection);?>
		</div>
	</div>
</div>

<?$arJSParams = array(
	"numSetItems" => count($arResult["SET_ITEMS"]["DEFAULT"]),
	"parentContId" => "bx-set-const-".$mainId,
	"ajaxPath" => $this->GetFolder()."/ajax.php",
	"canBuy" => $arResult["ELEMENT"]["CAN_BUY"],
	"currency" => $arResult["ELEMENT"]["PRICE"]["CURRENCY"],
	"mainElementId" => $arResult["ELEMENT"]["ID"],
	"mainElementRatioPrice" => $arResult["ELEMENT"]["PRICE"]["RATIO_DISCOUNT_VALUE"],
	"mainElementRatioOldPrice" => $arResult["ELEMENT"]["PRICE"]["RATIO_VALUE"],
	"mainElementRatioDiffPrice" => $arResult["ELEMENT"]["PRICE"]["RATIO_DISCOUNT_DIFF"],
	"mainElementObject" => $arResult["ELEMENT"]["OBJECT"],
	"mainElementPartnersUrl" => $arResult["ELEMENT"]["PARTNERS_URL"],
	"iblockId" => $arParams["IBLOCK_ID"],
	"basketUrl" => $arParams["BASKET_URL"],
	"setIds" => $arResult["DEFAULT_SET_IDS"],
	"productPropsVar" => $arParams["PRODUCT_PROPS_VARIABLE"],
	"partialProductProps" => $arParams["PARTIAL_PRODUCT_PROPERTIES"],
	"setCartProps" => $arParams["PRODUCT_PROPERTIES"],
	"offersCartProps" => $arParams["OFFERS_CART_PROPERTIES"],
	"itemsRatio" => $arResult["BASKET_QUANTITY"],
	"noFotoSrc" => SITE_TEMPLATE_PATH."/images/no_foto.png",
	"messages" => array(
		"PRICE_NOT_SET" => Loc::getMessage("CATALOG_SET_PRICE_NOT_SET"),
		"ECONOMY_PRICE" => Loc::getMessage("CATALOG_SET_ECONOMY_PRICE"),
		"EMPTY_SET" => Loc::getMessage("CATALOG_SET_EMPTY_SET"),
		"SET_BUY" => Loc::getMessage("CATALOG_SET_BUY"),
		"SET_BUY_OK" => Loc::getMessage("CATALOG_SET_BUY_OK")
	)
);?>

<script type="text/javascript">
	var <?=$obName?> = new JCCatalogSetConstructor(<?=CUtil::PhpToJSObject($arJSParams, false, true)?>);
</script>