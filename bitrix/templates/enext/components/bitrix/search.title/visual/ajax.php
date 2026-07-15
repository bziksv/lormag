<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (empty($arResult["CATEGORIES"]) || !$arResult['CATEGORIES_ITEMS_EXISTS'])
	return;
?>
<div class="bx_searche">

<? if($arResult["SECTIONS"]): ?>
	<div class="bx_item_block all_result">
		<div class="bx_img_element"></div>
		<div class="bx_item_element">
			<span class="all_result_title"><a href="">Разделы</a></span>
		</div>
		<div style="clear:both;"></div>
	</div>
	<div class="bx_item_block" style="min-height:0">
		<div class="bx_img_element"></div>
		<div class="bx_item_element"><hr></div>
	</div>
<? endif; ?>

<? foreach($arResult["SECTIONS"] as $arSection): ?>		
	<div class="bx_item_block" style="min-height: 35px;">
		<?if ($arSection["PICTURE"]):?>
		<div class="bx_img_element">
			<div class="bx_image" style="background-image: url('<?echo $arSection["PICTURE"]?>');width: 25px;height: 25px;margin: 0 auto;"></div>
		</div>
		<?endif;?>
		<div class="bx_item_element">
			<a href="/catalog/<?echo $arSection["CODE"]?>/"><?echo $arSection["NAME"]?></a>
		</div>
		<div style="clear:both;"></div>
	</div>	
<? endforeach; ?>

<? if($arResult["SECTIONS"]): ?>
	<div class="bx_item_block all_result">
		<div class="bx_img_element"></div>
		<div class="bx_item_element">
			<span class="all_result_title"><a href="">Товары</a></span>
		</div>
		<div style="clear:both;"></div>
	</div>
	<div class="bx_item_block" style="min-height:0">
		<div class="bx_img_element"></div>
		<div class="bx_item_element"><hr></div>
	</div>
<? endif; ?>

<?
foreach($arResult["CATEGORIES"] as $category_id => $arCategory):?>
	<?foreach($arCategory["ITEMS"] as $i => $arItem):?>
		<?//echo $arCategory["TITLE"]?>
		<?if($category_id === "all"):?>
			<div class="bx_item_block" style="min-height:0">
				<div class="bx_img_element"></div>
				<div class="bx_item_element"><hr></div>
			</div>
			<div class="bx_item_block all_result">
				<div class="bx_img_element"></div>
				<div class="bx_item_element">
					<span class="all_result_title"><a href="<?echo $arItem["URL"]?>"><?echo $arItem["NAME"]?></a></span>
				</div>
				<div style="clear:both;"></div>
			</div>
		<?elseif(isset($arResult["ELEMENTS"][$arItem["ITEM_ID"]])):
			$arElement = $arResult["ELEMENTS"][$arItem["ITEM_ID"]];?>
			<div class="bx_item_block">
				<?if (is_array($arElement["PICTURE"])):?>
				<div class="bx_img_element">
					<div class="bx_image" style="background-image: url('<?echo $arElement["PICTURE"]["src"]?>')"></div>
				</div>
				<?endif;?>
				<div class="bx_item_element">
					<a href="<?echo $arItem["URL"]?>"><?echo $arItem["NAME"]?></a>
					<?
					foreach($arElement["PRICES"] as $code=>$arPrice)
					{
						if ($arPrice["MIN_PRICE"] != "Y")
							continue;

						if($arPrice["CAN_ACCESS"])
						{
							if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
								<div class="bx_price">
									<?=$arPrice["PRINT_DISCOUNT_VALUE"]?>
									<span class="old"><?=$arPrice["PRINT_VALUE"]?></span>
								</div>
							<?else:?>
								<div class="bx_price"><?=$arPrice["PRINT_VALUE"]?></div>
							<?endif;
						}
						if ($arPrice["MIN_PRICE"] == "Y")
							break;
					}
					?>
				</div>
				<div style="clear:both;"></div>
			</div>	
		<?endif;?>
	<?endforeach;?>
<?endforeach;?>
</div>