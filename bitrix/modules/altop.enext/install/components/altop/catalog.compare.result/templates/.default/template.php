<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();

$isAjax = false;
if($request->isAjaxRequest()) {
	$actionByAjax = $request->get("ajax_action");
	if($actionByAjax == "Y")
		$isAjax = true;
}?>

<div id="bx_catalog_compare_block" class="compare-container">
	<?if($isAjax)
		$APPLICATION->RestartBuffer();?>
	<div class="compare-left scrollbar-inner">
		<div class="compare">
			<div class="compare-row compare-row-thead">
				<div class="hidden-xs hidden-sm compare-col compare-col-thead">
					<label class="compare-different">
						<input type="checkbox" onchange="CatalogCompareObj.different(this);"<?=($arResult["DIFFERENT"] ? " checked='checked'" : "")?> data-entity="compare-checkbox">
						<span class="compare-different-checkbox"><i class="icon-ok-b"></i></span>
						<span class="compare-different-title" data-entity="compare-title"><?=Loc::getMessage("CATALOG_ONLY_DIFFERENT")?></span>
					</label>
				</div>
				<?foreach($arResult["ITEMS"] as $arElement) {?>
					<div class="compare-col vertical-align-top">
						<div class="compare-item-item">
							<a class="compare-item-caption" href="<?=$arElement['DETAIL_PAGE_URL']?>">
								<span class="compare-item-image">
									<?if(is_array($arElement["OFFER_FIELDS"]["PREVIEW_PICTURE"])) {?>
										<img src="<?=$arElement['OFFER_FIELDS']['PREVIEW_PICTURE']['SRC']?>" width="<?=$arElement['OFFER_FIELDS']['PREVIEW_PICTURE']['WIDTH']?>" height="<?=$arElement['OFFER_FIELDS']['PREVIEW_PICTURE']['HEIGHT']?>" alt="<?=$arElement['OFFER_FIELDS']['PREVIEW_PICTURE']['ALT']?>" title="<?=$arElement['OFFER_FIELDS']['PREVIEW_PICTURE']['TITLE']?>" />
									<?} elseif(is_array($arElement["FIELDS"]["PREVIEW_PICTURE"])) {?>
										<img src="<?=$arElement['FIELDS']['PREVIEW_PICTURE']['SRC']?>" width="<?=$arElement['FIELDS']['PREVIEW_PICTURE']['WIDTH']?>" height="<?=$arElement['FIELDS']['PREVIEW_PICTURE']['HEIGHT']?>" alt="<?=$arElement['FIELDS']['PREVIEW_PICTURE']['ALT']?>" title="<?=$arElement['FIELDS']['PREVIEW_PICTURE']['TITLE']?>" />
									<?} else {?>
										<img src="<?=SITE_TEMPLATE_PATH?>/images/no_photo.png" width="50" height="50" alt="<?=$arElement['NAME']?>" title="<?=$arElement['NAME']?>" />
									<?}?>
								</span>
								<span class="compare-item-name"><?=(!empty($arElement["OFFER_FIELDS"]["NAME"]) ? $arElement["OFFER_FIELDS"]["NAME"] : (!empty($arElement["FIELDS"]["NAME"]) ? $arElement["FIELDS"]["NAME"] : $arElement["NAME"]))?></span>
							</a>
							<div class="compare-item-delete" title="<?=Loc::getMessage('CATALOG_DELETE')?>" data-entity="deleteItem" data-id="<?=$arElement['ID']?>"><i class="icon-close"></i></div>
						</div>
					</div>
				<?}
				unset($arElement);?>
			</div>
			<?if(!empty($arResult["SHOW_FIELDS"])) {
				foreach($arResult["SHOW_FIELDS"] as $code => $arProp) {
					if($code != "NAME" && $code != "PREVIEW_PICTURE" && $code != "DETAIL_PICTURE") {
						$showRow = true;
						if($arResult["DIFFERENT"]) {
							$arCompare = array();
							foreach($arResult["ITEMS"] as $arElement) {
								$arPropertyValue = $arElement["FIELDS"][$code];
								if(is_array($arPropertyValue)) {
									sort($arPropertyValue);
									$arPropertyValue = implode(" / ", $arPropertyValue);
								}
								$arCompare[] = $arPropertyValue;
							}
							unset($arElement);
							$showRow = (count(array_unique($arCompare)) > 1);
						}
						if($showRow) {?>
							<div class="compare-row">
								<div class="hidden-xs hidden-sm compare-col"><?=Loc::getMessage("IBLOCK_FIELD_".$code)?></div>
								<?foreach($arResult["ITEMS"] as $arElement) {?>
									<div class="compare-col">
										<div class="visible-xs visible-sm compare-col-title"><?=Loc::getMessage("IBLOCK_FIELD_".$code)?></div>
										<div class="compare-col-val"><?=$arElement["FIELDS"][$code]?></div>
									</div>
								<?}
								unset($arElement);?>
							</div>
						<?}
					}
				}
				unset($arProp, $code);
			}
			if(!empty($arResult["SHOW_OFFER_FIELDS"])) {
				foreach($arResult["SHOW_OFFER_FIELDS"] as $code => $arProp) {
					if($code != "NAME" && $code != "PREVIEW_PICTURE" && $code != "DETAIL_PICTURE") {
						$showRow = true;
						if($arResult["DIFFERENT"]) {
							$arCompare = array();
							foreach($arResult["ITEMS"] as $arElement) {
								$arPropertyValue = $arElement["OFFER_FIELDS"][$code];
								if(is_array($arPropertyValue)) {
									sort($arPropertyValue);
									$arPropertyValue = implode(" / ", $arPropertyValue);
								}
								$arCompare[] = $arPropertyValue;
							}
							unset($arElement);
							$showRow = (count(array_unique($arCompare)) > 1);
						}
						if($showRow) {?>
							<div class="compare-row">
								<div class="hidden-xs hidden-sm compare-col"><?=Loc::getMessage("IBLOCK_OFFER_FIELD_".$code)?></div>
								<?foreach($arResult["ITEMS"] as $arElement) {?>
									<div class="compare-col">
										<div class="visible-xs visible-sm compare-col-title"><?=Loc::getMessage("IBLOCK_OFFER_FIELD_".$code)?></div>
										<div class="compare-col-val"><?=$arElement["OFFER_FIELDS"][$code]?></div>
									</div>
								<?}
								unset($arElement);?>
							</div>
						<?}
					}
				}
				unset($arProp, $code);
			}
			if(!empty($arResult["SHOW_PROPERTIES"])) {
				foreach($arResult["SHOW_PROPERTIES"] as $code => $arProp) {
					$showRow = true;
					if($arResult["DIFFERENT"]) {
						$arCompare = array();
						foreach($arResult["ITEMS"] as $arElement) {
							$arPropertyValue = $arElement["DISPLAY_PROPERTIES"][$code]["VALUE"];
							if(is_array($arPropertyValue)) {
								sort($arPropertyValue);
								$arPropertyValue = implode(" / ", $arPropertyValue);
							}
							$arCompare[] = $arPropertyValue;
						}
						unset($arElement);
						$showRow = (count(array_unique($arCompare)) > 1);
					}
					if($showRow) {?>
						<div class="compare-row">
							<div class="hidden-xs hidden-sm compare-col"><?=$arProp["NAME"]?></div>
							<?foreach($arResult["ITEMS"] as $arElement) {?>
								<div class="compare-col">
									<div class="visible-xs visible-sm compare-col-title"><?=$arProp["NAME"]?></div>
									<div class="compare-col-val"><?=(is_array($arElement["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]) ? implode("/ ", $arElement["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]) : $arElement["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"])?></div>
								</div>
							<?}
							unset($arElement);?>
						</div>
					<?}
				}
				unset($arProp, $code);
			}
			if(!empty($arResult["SHOW_OFFER_PROPERTIES"])) {
				foreach($arResult["SHOW_OFFER_PROPERTIES"] as $code => $arProp) {
					$showRow = true;
					if($arResult["DIFFERENT"]) {
						$arCompare = array();
						foreach($arResult["ITEMS"] as $arElement) {
							$arPropertyValue = $arElement["OFFER_DISPLAY_PROPERTIES"][$code]["VALUE"];
							if(is_array($arPropertyValue)) {
								sort($arPropertyValue);
								$arPropertyValue = implode(" / ", $arPropertyValue);
							}
							$arCompare[] = $arPropertyValue;
						}
						unset($arElement);
						$showRow = (count(array_unique($arCompare)) > 1);
					}
					if($showRow) {?>
						<div class="compare-row">
							<div class="hidden-xs hidden-sm compare-col"><?=$arProp["NAME"]?></div>
							<?foreach($arResult["ITEMS"] as $arElement) {?>
								<div class="compare-col">
									<div class="visible-xs visible-sm compare-col-title"><?=$arProp["NAME"]?></div>
									<div class="compare-col-val"><?=(is_array($arElement["OFFER_DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]) ? implode("/ ", $arElement["OFFER_DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]) : $arElement["OFFER_DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"])?></div>
								</div>
							<?}
							unset($arElement);?>
						</div>
					<?}
				}
				unset($arProp, $code);
			}?>
			<div class="compare-row">
				<div class="hidden-xs hidden-sm compare-col compare-col-tfoot vertical-align-top">
					<div class="compare-clear">
						<button class="btn btn-default" data-entity="clear"><?=Loc::getMessage("CATALOG_CLEAR")?></button>
					</div>
				</div>
				<?foreach($arResult["ITEMS"] as $arElement) {?>
					<div class="compare-col vertical-align-top">
						<div class="compare-item-article">
							<?=Loc::getMessage("CATALOG_ARTICLE").(!empty($arElement["OFFER_PROPERTIES"]["ARTNUMBER"]["VALUE"]) ? $arElement["OFFER_PROPERTIES"]["ARTNUMBER"]["VALUE"] : (!empty($arElement["PROPERTIES"]["ARTNUMBER"]["VALUE"]) ? $arElement["PROPERTIES"]["ARTNUMBER"]["VALUE"] : "-"));?>
						</div>
						<?if(isset($arElement["REVIEWS_COUNT"])) {?>
							<div class="compare-item-rating">
								<?if($arElement["REVIEWS_COUNT"] > 0) {?>
									<div class="compare-item-rating-val"<?=($arElement["RATING_VALUE"] <= 4.4 ? " data-rate='".intval($arElement["RATING_VALUE"])."'" : "")?>><?=$arElement["RATING_VALUE"]?></div>
									<div class="compare-item-rating-reviews-count"><?=$arElement["REVIEWS_COUNT"]." ".$arElement["REVIEWS_DECLENSION"]?></div>
								<?}?>
							</div>
						<?}?>
						<div class="compare-item-info-container">
							<div class="compare-item-info">
								<div class="compare-item-price-container">
									<?if($arElement["MIN_PRICE"]["DISCOUNT_VALUE"] > 0) {
										if($arElement["MIN_PRICE"]["DISCOUNT_DIFF"] > 0) {?>
											<div class="compare-item-price-old"><?=$arElement["MIN_PRICE"]["PRINT_VALUE"]?></div>
											<div class="compare-item-price-economy"><?=Loc::getMessage("CATALOG_PRICE_ECONOMY", array("#ECONOMY#" => $arElement["MIN_PRICE"]["PRINT_DISCOUNT_DIFF"]))?></div>
										<?}?>
										<div class="compare-item-price-current"><?=$arElement["MIN_PRICE"]["PRINT_DISCOUNT_VALUE"]?><span class="compare-item-price-measure">/<?=$arElement["MEASURE"]["SYMBOL_RUS"]?></span></div>
									<?} else {?>
										<div class="compare-item-price-not-set"><?=Loc::getMessage("CATALOG_PRICE_NOT_SET")?></div>
									<?}?>
								</div>
								<div class="compare-item-btn">
									<?$object = !empty($arElement["PROPERTIES"]["OBJECT"]["FULL_VALUE"]) ? $arElement["PROPERTIES"]["OBJECT"]["FULL_VALUE"] : false;
									$objectContacts = $object["PHONE_SMS"] || $object["EMAIL_EMAIL"] ? true : false;
									$partnersUrl = !empty($arElement["OFFER_PROPERTIES"]["PARTNERS_URL"]["VALUE"]) ? true : (!empty($arElement["PROPERTIES"]["PARTNERS_URL"]["VALUE"]) ? true : false);
									if(($object && !$objectContacts) || $partnersUrl || $arParams["DISABLE_BASKET"]) {?>
										<a<?=((($object && !$objectContacts) || $partnersUrl) ? " target='_blank'" : "")?> class="btn btn-buy" href="<?=$arElement['DETAIL_PAGE_URL']?>" title="<?=Loc::getMessage('CATALOG_DETAIL')?>"><i class="icon-arrow-right"></i></a>
									<?} else {?>
										<button type="button" class="btn btn-buy" title="<?=Loc::getMessage('CATALOG_ADD_TO_BASKET')?>" data-entity="addBasket" data-id="<?=$arElement['ID']?>"<?=($arElement["CAN_BUY"] && $arElement["MIN_PRICE"]["DISCOUNT_VALUE"] > 0 ? "" : " disabled='disabled'")?>><i class="icon-cart"></i></button>
									<?}?>
								</div>
							</div>
						</div>
					</div>
				<?}
				unset($arElement);?>
			</div>
		</div>
	</div>
	<div class="visible-xs visible-sm compare-different-mobile">
		<label class="compare-different">
			<input type="checkbox" onchange="CatalogCompareObj.different(this);"<?=($arResult["DIFFERENT"] ? " checked='checked'" : "")?> data-entity="compare-checkbox">
			<span class="compare-different-checkbox"><i class="icon-ok-b"></i></span>
			<span class="compare-different-title" data-entity="compare-title"><?=Loc::getMessage("CATALOG_ONLY_DIFFERENT")?></span>
		</label>
	</div>
	<div class="visible-xs visible-sm compare-clear-mobile">
		<button class="btn btn-default" data-entity="clear"><?=Loc::getMessage("CATALOG_CLEAR")?></button>
	</div>
	<?if($isAjax)
		die();?>
</div>

<script type="text/javascript">
	BX.message({
		BASKET_URL: '<?=$arParams["BASKET_URL"]?>',
		ADD_BASKET_MESSAGE: '<?=GetMessageJS("CATALOG_ADD_TO_BASKET")?>',
		ADD_BASKET_OK_MESSAGE: '<?=GetMessageJS("CATALOG_ADD_TO_BASKET_OK")?>'
	});
	var CatalogCompareObj = new BX.Iblock.Catalog.CompareClass('bx_catalog_compare_block', '<?=CUtil::JSEscape($arParams["ACTION_VARIABLE"])?>', '<?=CUtil::JSEscape($arParams["PRODUCT_ID_VARIABLE"])?>', '<?=CUtil::JSEscape($arResult["IS_EXTERNAL"])?>', '<?=CUtil::JSEscape($arResult["COMPARE_URL_TEMPLATE"])?>');
</script>