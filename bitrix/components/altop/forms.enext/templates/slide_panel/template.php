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
	<form action="javascript:void(0)" novalidate>
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
		
		if($arParams["USER_CONSENT"]) {?>
			<input type="hidden" name="USER_CONSENT_ID" value="<?=$arParams['USER_CONSENT_ID']?>" />
			<input type="hidden" name="USER_CONSENT_URL" value="" />
			<div class="form-group form-group-checkbox">
				<div class="checkbox">
					<?$fields = array();
					foreach($arResult["IBLOCK"]["PROPERTIES"] as $arProp) {
						if($arProp["CODE"] != "OBJECT_ID" && $arProp["CODE"] != "PRODUCT_ID" && $arProp["CODE"] != "OFFER_ID" && $arProp["USER_TYPE"] != "HTML")
							$fields[] = $arProp["NAME"];
					}
					unset($arProp);?>
					<?$APPLICATION->IncludeComponent("bitrix:main.userconsent.request", "",
						array(
							"ID" => $arParams["USER_CONSENT_ID"],
							"INPUT_NAME" => "USER_CONSENT",
							"IS_CHECKED" => $arParams["USER_CONSENT_IS_CHECKED"],
							"AUTO_SAVE" => "N",
							"IS_LOADED" => $arParams["USER_CONSENT_IS_LOADED"],
							"REPLACE" => array(
								"button_caption" => Loc::getMessage("FORMS_SLIDE_PANEL_SUBMIT"),
								"fields" => $fields
							)
						),
						$component
					);?>
					<?unset($fields);?>
				</div>
			</div>
		<?}

		if($arParams["USE_CAPTCHA"]) {
			$captchaFrame = $this->createFrame("forms_captcha", false)->begin();
			$captchaSid = $APPLICATION->CaptchaGetCode();?>
			<div class="form-group captcha">
				<div class="pic">
					<img src="/bitrix/tools/captcha.php?captcha_sid=<?=htmlspecialcharsbx($captchaSid)?>" width="100" height="36" alt="CAPTCHA" />
				</div>
				<input type="text" maxlength="5" name="CAPTCHA_WORD" class="form-control" placeholder="<?=Loc::getMessage('FORMS_SLIDE_PANEL_CAPTCHA_WORD')?>" />
				<input type="hidden" name="CAPTCHA_SID" value="<?=htmlspecialcharsbx($captchaSid)?>" />
			</div>
		<?
			$captchaFrame->end();
		}?>
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