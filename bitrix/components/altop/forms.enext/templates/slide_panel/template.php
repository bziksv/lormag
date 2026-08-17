<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$this->setFrameMode(true);

use Bitrix\Main\Localization\Loc;

$obName = "ob".preg_replace("/[^a-zA-Z0-9_]/", "x", $this->GetEditAreaId($this->randString()));
$containerName = "slide-panel-form-".$obName;?>

<div class="slide-panel__form" id="<?=$containerName?>">
	<div class="slide-panel__form-title"><?=$arResult["IBLOCK"]["NAME"]?></div>
	<?if(!empty($arResult["IBLOCK"]["DESCRIPTION"])) {?>
		<div class="slide-panel__form-caption"><?=$arResult["IBLOCK"]["DESCRIPTION"]?></div>
	<?}?>
	<form action="javascript:void(0)">		
		<input type="hidden" name="IBLOCK_STRING" value="<?=$arResult['IBLOCK']['STRING']?>" />
		<?foreach($arResult["IBLOCK"]["PROPERTIES"] as $arProp) {
			if($arProp["CODE"] == "OBJECT_ID" || $arProp["CODE"] == "PRODUCT_ID" || $arProp["CODE"] == "OFFER_ID") {?>
				<input type="hidden" name="<?=$arProp['CODE']?>" value="" />
				<?if($arProp["CODE"] == "PRODUCT_ID") {?>
					<input type="hidden" name="PRODUCT_LINK" value="" />
				<?}
			} else {?>
				<div class="form-group<?=(!empty($arProp['HINT']) ? ' has-feedback' : '');?>">
					<?if($arProp["USER_TYPE"] != "HTML") {?>
						<input type="text" name="<?=$arProp['CODE']?>" class="form-control" placeholder="<?=(!empty($arProp['DEFAULT_VALUE']) ? $arProp['DEFAULT_VALUE'] : $arProp['NAME']);?>" inputmode="<?=($arProp['CODE'] == 'PHONE' ? 'numeric' : 'text')?>" />
					<?} else {?>									
						<textarea name="<?=$arProp['CODE']?>" class="form-control" rows="3" placeholder="<?=(!empty($arProp['DEFAULT_VALUE']['TEXT']) ? $arProp['DEFAULT_VALUE']['TEXT'] : $arProp['NAME']);?>" style="height:<?=$arProp['USER_TYPE_SETTINGS']['height']?>px; min-height:<?=$arProp['USER_TYPE_SETTINGS']['height']?>px; max-height:<?=$arProp['USER_TYPE_SETTINGS']['height']?>px;" inputmode="text"></textarea>
					<?}
					if(!empty($arProp["HINT"])) {?>
						<i class="form-control-feedback fv-icon-no-has fa <?=$arProp['HINT']?>"></i>
					<?}?>
				</div>
			<?}
		}
		unset($arProp);
		
		// ============= ЗАМЕНЁН БЛОК СОГЛАСИЯ =============
		if($arParams["USER_CONSENT"]) {?>
			<div class="form-group form-group-checkbox">
				<div class="checkbox">
					<label>
						<input type="checkbox" name="USER_CONSENT" value="Y" <?=($arParams["USER_CONSENT_IS_CHECKED"] ? 'checked' : '')?> required>
						<span>Нажимая на эту кнопку, я даю свое согласие на обработку персональных данных и соглашаюсь с условиями <a target="_blank" href="/upload/politics-lormag.png">политики обработки персональных данных</a>.</span>
					</label>
				</div>
			</div>
		<?}
		// ===================================================
		
		if($arParams["USE_CAPTCHA"]) {
			$captchaSid = $APPLICATION->CaptchaGetCode();?>
			<div class="form-group captcha">
				<div class="pic">
					<img src="/bitrix/tools/captcha.php?captcha_sid=<?=htmlspecialcharsbx($captchaSid)?>" width="100" height="36" alt="CAPTCHA" />
				</div>
				<input type="text" maxlength="5" name="CAPTCHA_WORD" class="form-control" placeholder="<?=Loc::getMessage('FORMS_SLIDE_PANEL_CAPTCHA_WORD')?>" />
				<input type="hidden" name="CAPTCHA_SID" value="<?=htmlspecialcharsbx($captchaSid)?>" />
			</div>
		<?}?>		
		<div class="form-group">
			<button type="submit" class="btn btn-buy"><span><?=Loc::getMessage("FORMS_SLIDE_PANEL_SUBMIT")?></span></button>
		</div>		
	</form>
	<div class="alert" style="display: none;"></div>
</div>

<?$jsProps = array();
foreach($arResult["IBLOCK"]["PROPERTIES"] as $arProp) {
	if($arProp["CODE"] != "OBJECT_ID" && $arProp["CODE"] != "PRODUCT_ID" && $arProp["CODE"] != "OFFER_ID") {
		$jsProps[$arProp["CODE"]] = array(
			"CODE" => $arProp["CODE"],
			"REQUIRED" => $arProp["IS_REQUIRED"]
		);
	}
}
unset($arProp);?>

<script type="text/javascript">	
	BX.message({
		FORMS_NOT_EMPTY_INVALID: '<?=GetMessageJS("FORMS_SLIDE_PANEL_NOT_EMPTY_INVALID");?>',
		FORMS_PHONE_WRONG: '<?=GetMessageJS("FORMS_SLIDE_PANEL_PHONE_WRONG");?>',
		FORMS_PHONE_INVALID: '<?=GetMessageJS("FORMS_SLIDE_PANEL_PHONE_INVALID")?>',
		FORMS_EMAIL_INVALID: '<?=GetMessageJS("FORMS_SLIDE_PANEL_EMAIL_INVALID")?>',
		FORMS_USER_CONSENT_NOT_EMPTY_INVALID: '<?=GetMessageJS("FORMS_SLIDE_PANEL_USER_CONSENT_NOT_EMPTY_INVALID");?>',
		FORMS_CAPTCHA_WRONG: '<?=GetMessageJS("FORMS_SLIDE_PANEL_CAPTCHA_WRONG");?>',			
		FORMS_ALERT_SUCCESS: '<?=GetMessageJS("FORMS_SLIDE_PANEL_ALERT_SUCCESS");?>',
		FORMS_ALERT_ERROR: '<?=GetMessageJS("FORMS_SLIDE_PANEL_ALERT_ERROR");?>'
	});
	var <?=$obName?> = new JCFormsSlidePanelComponent({
		componentPath: '<?=CUtil::JSEscape($componentPath)?>',
		jsProps: <?=CUtil::PhpToJSObject($jsProps)?>,
		defaultCountry: '<?=CUtil::JSEscape($arParams["DEFAULT_COUNTRY"])?>',
		phoneMask: '<?=$arParams["PHONE_MASK"]?>',
		userConsent: '<?=$arParams["USER_CONSENT"]?>',
		useCaptcha: '<?=$arParams["USE_CAPTCHA"]?>',
		container: '<?=$containerName?>'
	});
</script>

<?unset($jsProps);?>