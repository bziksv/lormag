<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$this->setFrameMode(true);

use Bitrix\Main\Localization\Loc;

$obName = "ob".preg_replace("/[^a-zA-Z0-9_]/", "x", $this->GetEditAreaId($this->randString()));?>

<div id="<?=$arResult['CONTAINER_ID']?>" class="<?=$arResult['CONTAINER_CLASS']?>" style="<?=($arResult['DEFAULT_DISPLAY'] ? '' : 'display: none;')?>">
	<div class="quick-order-form">
		<div class="quick-order-form-title"><?=Loc::getMessage("QUICK_ORDER_TEMPLATE_TITLE")?></div>
		<form action="javascript:void(0)" data-entity="quickOrderForm">			
			<input type="hidden" name="MODE" value="<?=$arResult['MODE']?>" />
			<?if($arResult["MODE"] == "PRODUCT") {?>
				<input type="hidden" name="PRODUCT_ID" value="<?=$arResult['PRODUCT_ID']?>" />
				<input type="hidden" name="PRODUCT_PROPS_VARIABLE" value="<?=$arResult['PRODUCT_PROPS_VARIABLE']?>" />
				<input type="hidden" name="PARTIAL_PRODUCT_PROPERTIES" value="<?=$arResult['PARTIAL_PRODUCT_PROPERTIES']?>" />
				<input type="hidden" name="CART_PROPERTIES" value="<?=$arResult['CART_PROPERTIES']?>" />
				<input type="hidden" name="OFFERS_CART_PROPERTIES" value="<?=$arResult['OFFERS_CART_PROPERTIES']?>" />
				<?if($arResult["OBJECT_ID"] > 0) {?>
					<input type="hidden" name="OBJECT_ID" value="<?=$arResult['OBJECT_ID']?>" />
				<?}
			}
			foreach($arResult["FIELDS"] as $arField) {?>
				<div class="form-group has-feedback">
					<?if($arField["TYPE"] == "TEXTAREA") {?>
						<textarea name="<?=$arField['CODE']?>" class="form-control" rows="3" placeholder="<?=Loc::getMessage('QUICK_ORDER_TEMPLATE_'.$arField['CODE'])?>" inputmode="text"></textarea>
						<i class="form-control-feedback fv-icon-no-has<?=(!empty($arField['ICON']) ? ' '.$arField['ICON'] : '')?>"></i>
					<?} else {?>
						<input type="text" name="<?=$arField['CODE']?>" class="form-control" placeholder="<?=Loc::getMessage('QUICK_ORDER_TEMPLATE_'.$arField['CODE'])?>" inputmode="<?=($arField['CODE'] == 'PHONE' ? 'numeric' : ($arField['CODE'] == 'EMAIL' ? 'email' : 'text'))?>" />
						<i class="form-control-feedback fv-icon-no-has<?=(!empty($arField['ICON']) ? ' '.$arField['ICON'] : '')?>"></i>
					<?}?>
				</div>
			<?}
			unset($arField);
			if($arResult["USER_CONSENT"]) {?>
				<input type="hidden" name="USER_CONSENT_ID" value="<?=$arResult['USER_CONSENT_ID']?>" />
				<input type="hidden" name="USER_CONSENT_URL" value="" />
				<div class="form-group form-group-checkbox<?=($arResult['MODE'] == 'PRODUCT' ? ' form-group-hidden' : '')?>">
					<div class="checkbox">
						<?$fields = array();
						foreach($arResult["FIELDS"] as $arField) {
							if($arField["TYPE"] != "TEXTAREA")
								$fields[] = $arField["NAME"];
						}
						unset($arField);?>
						<?$APPLICATION->IncludeComponent("bitrix:main.userconsent.request", "",
							array(
								"ID" => $arResult["USER_CONSENT_ID"],
								"INPUT_NAME" => "USER_CONSENT",
								"IS_CHECKED" => $arResult["USER_CONSENT_IS_CHECKED"],
								"AUTO_SAVE" => "N",
								"IS_LOADED" => $arResult["USER_CONSENT_IS_LOADED"],
								"REPLACE" => array(
									"button_caption" => Loc::getMessage("QUICK_ORDER_TEMPLATE_SUBMIT"),
									"fields" => $fields
								)
							),
							$component
						);?>
						<?unset($fields);?>
					</div>
				</div>
			<?}
			if($arResult["USE_CAPTCHA"]) {?>
				<div class="form-group captcha<?=($arResult['MODE'] == 'PRODUCT' ? ' form-group-hidden' : '')?>">
					<div class="pic 21" style="display:none;">								
						<img src ="/bitrix/images/arturgolubev.lazyload/pixel.gif" data-lazyload-src="" width="100" height="36" alt="CAPTCHA" />
					</div>							
					<input type="text" maxlength="5" name="CAPTCHA_WORD" class="form-control" placeholder="<?=Loc::getMessage('QUICK_ORDER_TEMPLATE_CAPTCHA_WORD')?>" />
					<input type="hidden" name="CAPTCHA_SID" value="" />
				</div>
			<?}?>
			<div class="form-group<?=($arResult['MODE'] == 'PRODUCT' ? ' form-group-hidden' : '')?>">
				<button type="submit" class="btn btn-primary" data-entity="quickOrderBtn"><?=Loc::getMessage("QUICK_ORDER_TEMPLATE_SUBMIT")?></button>
			</div>
		</form>
		<div style="display: none;" data-entity="quickOrderAlert"></div>
	</div>
</div>

<script type="text/javascript">	
	BX.message({		
		QUICK_ORDER_NOT_EMPTY_INVALID: '<?=GetMessageJS("QUICK_ORDER_TEMPLATE_NOT_EMPTY_INVALID");?>',
		QUICK_ORDER_PHONE_WRONG: '<?=GetMessageJS("QUICK_ORDER_TEMPLATE_PHONE_WRONG");?>',
		QUICK_ORDER_PHONE_INVALID: '<?=GetMessageJS("QUICK_ORDER_TEMPLATE_PHONE_INVALID")?>',
		QUICK_ORDER_EMAIL_ADDRESS_INVALID: '<?=GetMessageJS("QUICK_ORDER_TEMPLATE_EMAIL_ADDRESS_INVALID");?>',
		QUICK_ORDER_USER_CONSENT_NOT_EMPTY_INVALID: '<?=GetMessageJS("QUICK_ORDER_TEMPLATE_USER_CONSENT_NOT_EMPTY_INVALID");?>',		
		QUICK_ORDER_CAPTCHA_WRONG: '<?=GetMessageJS("QUICK_ORDER_TEMPLATE_CAPTCHA_WRONG");?>'
	});
	var <?=$obName?> = new JCQuickOrderComponent({
		componentPath: '<?=CUtil::JSEscape($componentPath)?>',
		jsProps: <?=CUtil::PhpToJSObject($arResult["FIELDS"])?>,
		defaultCountry: '<?=CUtil::JSEscape($arResult["DEFAULT_COUNTRY"])?>',
		phoneMask: '<?=$arResult["PHONE_MASK"]?>',
		userConsent: '<?=$arResult["USER_CONSENT"]?>',
		useCaptcha: '<?=$arResult["USE_CAPTCHA"]?>',
		quantityId: '<?=CUtil::JSEscape($arResult["QUANTITY_ID"])?>',
		basketPropsId: '<?=CUtil::JSEscape($arResult["BASKET_PROPS_ID"])?>',
		basketSkuProps: '<?=CUtil::JSEscape($arResult["BASKET_SKU_PROPS"])?>',
		container: '<?=CUtil::JSEscape($arResult["CONTAINER_ID"])?>'
	});
</script>