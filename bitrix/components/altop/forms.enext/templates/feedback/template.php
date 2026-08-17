<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$this->setFrameMode(true);

use Bitrix\Main\Localization\Loc;

$obName = "ob".preg_replace("/[^a-zA-Z0-9_]/", "x", $this->GetEditAreaId($this->randString()));
$containerName = "feedback-".$obName;?>

<div class="hidden-print feedback-wrapper">
	<div class="container">
		<div class="row feedback" id="<?=$containerName?>">
			<div class="col-xs-12">
				<div class="h1"><?=$arResult["IBLOCK"]["NAME"]?></div>
			</div>
			<form action="javascript:void(0)">
				<input type="hidden" name="IBLOCK_STRING" value="<?=$arResult['IBLOCK']['STRING']?>" />
				<div class="col-xs-12 col-md-3">
					<?foreach($arResult["IBLOCK"]["PROPERTIES"] as $arProp) {
						if($arProp["USER_TYPE"] != "HTML") {
							if($arProp["CODE"] != "SOURCE_URL") {?>
								<div class="form-group<?=(!empty($arProp['HINT']) ? ' has-feedback' : '');?>">
									<input type="<?=($arProp['CODE'] == 'EMAIL' ? 'email' : 'text')?>" name="<?=$arProp['CODE']?>" class="form-control" placeholder="<?=$arProp['NAME']?>" inputmode="<?=($arProp['CODE'] == 'PHONE' ? 'numeric' : 'text')?>" />
									<?if(!empty($arProp["HINT"])) {?>
										<i class="form-control-feedback fv-icon-no-has fa <?=$arProp['HINT']?>"></i>
									<?}?>
								</div>
							<?} else {?>
								<input type="hidden" name="<?=$arProp['CODE']?>" value="" />
							<?}
						}
					}
					unset($arProp);?>
				</div>
				<div class="col-xs-12 col-md-6">
					<?foreach($arResult["IBLOCK"]["PROPERTIES"] as $arProp) {
						if($arProp["USER_TYPE"] == "HTML") {?>
							<div class="form-group<?=(!empty($arProp['HINT']) ? ' has-feedback' : '');?>">
								<textarea name="<?=$arProp['CODE']?>" class="form-control" rows="3" placeholder="<?=$arProp['NAME']?>" style="height:<?=$arProp['USER_TYPE_SETTINGS']['height']?>px; min-height:<?=$arProp['USER_TYPE_SETTINGS']['height']?>px; max-height:<?=$arProp['USER_TYPE_SETTINGS']['height']?>px;" inputmode="text"></textarea>
								<?if(!empty($arProp["HINT"])) {?>
									<i class="form-control-feedback fv-icon-no-has fa <?=$arProp['HINT']?>"></i>
								<?}?>
							</div>
						<?}
					}
					unset($arProp);?>
				</div>
				<div class="col-xs-12 col-md-3">
					<?if($arParams["USE_CAPTCHA"]) {
						$captchaSid = $APPLICATION->CaptchaGetCode();?>
						<div class="form-group captcha">
							<div class="pic">
								<img src="/bitrix/tools/captcha.php?captcha_sid=<?=htmlspecialcharsbx($captchaSid)?>" width="100" height="36" alt="CAPTCHA" />
							</div>
							<input type="text" maxlength="5" name="CAPTCHA_WORD" class="form-control" placeholder="<?=Loc::getMessage('FORMS_FEEDBACK_CAPTCHA_WORD')?>" />
							<input type="hidden" name="CAPTCHA_SID" value="<?=htmlspecialcharsbx($captchaSid)?>" />
						</div>
					<?}?>
					<div class="form-group<?=(!$arParams['USE_CAPTCHA'] ? ' no-captcha' : '');?>">
						<button type="submit" class="btn btn-primary"><?=Loc::getMessage("FORMS_FEEDBACK_SUBMIT")?></button>
					</div>
				</div>
				<?if($arParams["USER_CONSENT"]) {?>
					<input type="hidden" name="USER_CONSENT_ID" value="<?=$arParams['USER_CONSENT_ID']?>" />
					<input type="hidden" name="USER_CONSENT_URL" value="" />
					<div class="col-xs-12">
						<div class="form-group form-group-checkbox">

<span>Нажимая на эту кнопку, я даю свое согласие на обработку персональных данных и соглашаюсь с условиями <a target="_blank" href="/upload/politics-lormag.png">политики обработки персональных данных</a>.</span>

						</div>
					</div>
				<?}?>
			</form>
			<div class="col-xs-12">
				<div class="alert" style="display: none;"></div>
			</div>
		</div>
	</div>
</div>

<?$jsProps = array();
foreach($arResult["IBLOCK"]["PROPERTIES"] as $arProp) {
	if($arProp["CODE"] != "OBJECT_ID" && $arProp["CODE"] != "PRODUCT_ID" && $arProp["CODE"] != "OFFER_ID" && $arProp["CODE"] != "SOURCE_URL") {
		$jsProps[$arProp["CODE"]] = array(
			"CODE" => $arProp["CODE"],
			"REQUIRED" => $arProp["IS_REQUIRED"]
		);
	}
}
unset($arProp);?>

<script type="text/javascript">	
	BX.message({
		FORMS_NOT_EMPTY_INVALID: '<?=GetMessageJS("FORMS_FEEDBACK_NOT_EMPTY_INVALID")?>',
		FORMS_PHONE_WRONG: '<?=GetMessageJS("FORMS_FEEDBACK_PHONE_WRONG")?>',
		FORMS_PHONE_INVALID: '<?=GetMessageJS("FORMS_FEEDBACK_PHONE_INVALID")?>',
		FORMS_USER_CONSENT_NOT_EMPTY_INVALID: '<?=GetMessageJS("FORMS_FEEDBACK_USER_CONSENT_NOT_EMPTY_INVALID")?>',
		FORMS_CAPTCHA_WRONG: '<?=GetMessageJS("FORMS_FEEDBACK_CAPTCHA_WRONG")?>',			
		FORMS_ALERT_SUCCESS: '<?=GetMessageJS("FORMS_FEEDBACK_ALERT_SUCCESS")?>',
		FORMS_ALERT_ERROR: '<?=GetMessageJS("FORMS_FEEDBACK_ALERT_ERROR")?>'
	});
	var <?=$obName?> = new JCFormsFeedbackComponent({
		componentPath: '<?=CUtil::JSEscape($componentPath)?>',
		jsProps: <?=CUtil::PhpToJSObject($jsProps)?>,
		defaultCountry: '<?=CUtil::JSEscape($arParams["DEFAULT_COUNTRY"])?>',
		phoneMask: '<?=$arParams["PHONE_MASK"]?>',
		userConsent: '<?=$arParams["USER_CONSENT"]?>',
		useCaptcha: '<?=$arParams["USE_CAPTCHA"]?>',		
		container: '<?=$containerName?>'
	});
</script>

<?unset($jsProps);