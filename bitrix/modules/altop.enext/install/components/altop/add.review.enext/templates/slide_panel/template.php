<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true ) die();

$this->setFrameMode(true);

use Bitrix\Main\Localization\Loc;

$obName = "ob".preg_replace("/[^a-zA-Z0-9_]/", "x", $this->GetEditAreaId($this->randString()));
$containerName = "slide-panel-form-".$obName;?>

<div class="slide-panel__form" id="<?=$containerName?>">
	<?if(!empty($arResult["ELEMENT"])) {?>
		<div class="slide-panel__form-item">
			<div class="slide-panel__form-item__image">
				<?if(is_array($arResult["ELEMENT"]["PREVIEW_PICTURE"])) {?>
					<img src="<?=$arResult['ELEMENT']['PREVIEW_PICTURE']['SRC']?>" width="<?=$arResult['ELEMENT']['PREVIEW_PICTURE']['WIDTH']?>" height="<?=$arResult['ELEMENT']['PREVIEW_PICTURE']['HEIGHT']?>" alt="<?=$arResult['ELEMENT']['NAME']?>" />
				<?} else {?>
					<img src="<?=SITE_TEMPLATE_PATH?>/images/no_photo.png" width="46" height="46" alt="<?=$arResult['ELEMENT']['NAME']?>" />
				<?}?>
			</div>
			<div class="slide-panel__form-item__name"><?=$arResult["ELEMENT"]["NAME"]?></div>
		</div>
	<?}
	if($arParams["CAN_ADD"]) {?>
		<form action="javascript:void(0)" enctype="multipart/form-data">
			<input type="hidden" name="IBLOCK_STRING" value="<?=$arResult['IBLOCK']['STRING']?>" />
			<?foreach($arResult["IBLOCK"]["PROPERTIES"] as $arProp) {
				if($arProp["PROPERTY_TYPE"] == "E") {?>
					<input type="hidden" name="<?=$arProp['CODE']?>" value="<?=(!empty($arResult['ELEMENT']) && $arResult['ELEMENT']['ID'] > 0 ? $arResult['ELEMENT']['ID'] : '')?>" />
				<?} elseif($arProp["PROPERTY_TYPE"] == "L" && !empty($arProp["VALUES"])) {
					if($arProp["CODE"] == "RATING") {?>
						<div class="form-group form-group-rating">
							<div class="form-group-rating-title"><?=$arProp["NAME"]?></div>
							<div class="form-group-rating-stars">
								<?foreach($arProp["VALUES"] as $val) {?>
									<i class="icon-star-s form-group-rating-star<?=($arParams['RATING_ID'] > 0 && $arProp['VALUES'][$arParams['RATING_ID']]['XML_ID'] >= $val['XML_ID'] ? ' form-group-rating-star-active' : '').($arParams['RATING_ID'] > 0 && $arProp['VALUES'][$arParams['RATING_ID']]['XML_ID'] == $val['XML_ID'] ? ' form-group-rating-star-current' : '')?>" data-code="<?=$arProp['CODE']?>" data-id="<?=$val['ID']?>" data-value="<?=$val['VALUE']?>"></i>
								<?}
								unset($val);?>
							</div>
							<div class="form-group-rating-val"><?=($arParams["RATING_ID"] > 0 ? $arProp["VALUES"][$arParams["RATING_ID"]]["VALUE"] : Loc::getMessage("ADD_REVIEW_SLIDE_PANEL_RATING"))?></div>
							<input type="hidden" name="<?=$arProp['CODE']?>" value="<?=($arParams['RATING_ID'] > 0 ? $arParams['RATING_ID'] : '')?>" />
						</div>
					<?} else {?>
						<div class="form-group form-group-list">
							<div class="form-group-list-title"><?=$arProp["NAME"]?></div>
							<div class="form-group-list-items">
								<?$defId = false;
								foreach($arProp["VALUES"] as $val) {
									if($val["DEF"] == "Y")
										$defId = $val["ID"];?>
									<div class="form-group-list-item<?=($val['DEF'] == 'Y' ? ' form-group-list-item-active' : '')?>" data-code="<?=$arProp['CODE']?>" data-id="<?=$val['ID']?>"><?=$val["VALUE"]?></div>
								<?}
								unset($val);?>
							</div>
							<input type="hidden" name="<?=$arProp['CODE']?>" value="<?=(intval($defId) > 0 ? $defId : '')?>" />
							<?unset($defId);?>
						</div>
					<?}
				} elseif($arProp["PROPERTY_TYPE"] == "S" && $arProp["USER_TYPE"] != "UserID" && $arProp["CODE"] != "CITY") {?>
					<div class="form-group<?=(!empty($arProp['HINT']) ? ' has-feedback' : '')?>">
						<?if($arProp["USER_TYPE"] == "HTML") {?>
							<textarea name="<?=$arProp['CODE']?>" class="form-control" rows="3" placeholder="<?=(!empty($arProp['DEFAULT_VALUE']['TEXT']) ? $arProp['DEFAULT_VALUE']['TEXT'] : $arProp['NAME'])?>" style="height:<?=$arProp['USER_TYPE_SETTINGS']['height']?>px; min-height:<?=$arProp['USER_TYPE_SETTINGS']['height']?>px; max-height:<?=$arProp['USER_TYPE_SETTINGS']['height']?>px;" inputmode="text"></textarea>
						<?} else {?>
							<input type="text" name="<?=$arProp['CODE']?>" class="form-control" placeholder="<?=(!empty($arProp['DEFAULT_VALUE']) ? $arProp['DEFAULT_VALUE'] : $arProp['NAME'])?>"<?=(!empty($arProp["VALUE"]) ? " value='".$arProp["VALUE"]."' readonly='readonly'" : "")?> inputmode="text" />
						<?}
						if(!empty($arProp["HINT"])) {?>
							<i class="form-control-feedback fv-icon-no-has fa <?=$arProp['HINT']?>"></i>
						<?}?>
					</div>
				<?} elseif($arProp["PROPERTY_TYPE"] == "F") {?>
					<div class="form-group form-group-files">
						<div class="form-group-files-title"><?=$arProp["NAME"]?></div>
						<div class="form-group-files-content">
							<input type="file" name="<?=$arProp['CODE']?>[]" data-jfiler-name="<?=$arProp['CODE']?>" data-jfiler-limit="<?=($arProp['MULTIPLE'] == 'Y' ? $arProp['MULTIPLE_CNT'] : 1)?>" data-jfiler-extensions="<?=$arProp['FILE_TYPE']?>" />
						</div>
						<input type="hidden" name="<?=$arProp['CODE']?>_COUNT" value="" />
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
							if($arProp["PROPERTY_TYPE"] == "S" && empty($arProp["USER_TYPE"]) && $arProp["CODE"] != "CITY")
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
									"button_caption" => Loc::getMessage("ADD_REVIEW_SLIDE_PANEL_SUBMIT"),
									"fields" => $fields
								)
							),
							$component
						);?>
						<?unset($fields);?>
					</div>
				</div>
			<?}
			if($arParams["USE_CAPTCHA"]) {?>
				<div class="form-group captcha">
					<div class="pic 24" style="display:none;">								
						<img src ="/bitrix/images/arturgolubev.lazyload/pixel.gif" data-lazyload-src="" width="100" height="36" alt="CAPTCHA" />
					</div>							
					<input type="text" maxlength="5" name="CAPTCHA_WORD" class="form-control" placeholder="<?=Loc::getMessage('ADD_REVIEW_SLIDE_PANEL_CAPTCHA_WORD')?>" />
					<input type="hidden" name="CAPTCHA_SID" value="" />
				</div>
			<?}?>
			<div class="form-group">
				<button type="submit" class="btn btn-buy"><span><?=Loc::getMessage("ADD_REVIEW_SLIDE_PANEL_SUBMIT")?></span></button>
			</div>
		</form>
		<div class="alert" style="display: none;"></div>
	<?} else {?>
		<div class="alert alert-warning"><?=Loc::getMessage("ADD_REVIEW_SLIDE_PANEL_ALERT_AUTHORIZED")?></div>
	<?}?>
</div>

<?if($arParams["CAN_ADD"]) {
	$jsProps = $jsPropsReq = array();
	foreach($arResult["IBLOCK"]["PROPERTIES"] as $key => $arProp) {
		if($arProp["PROPERTY_TYPE"] == "S" && empty($arProp["USER_TYPE"]) && $arProp["CODE"] != "CITY" && empty($arProp["VALUE"]))
			$jsProps[$key] = $arProp["CODE"];
		
		if($arProp["IS_REQUIRED"] == "Y") {
			$jsPropsReq[$key] = array(
				"CODE" => $arProp["CODE"].($arProp["PROPERTY_TYPE"] == "F" ? "_COUNT" : ""),
				"PROPERTY_TYPE" => $arProp["PROPERTY_TYPE"],
				"MULTIPLE" => $arProp["MULTIPLE"]
			);
		}
	}
	unset($key, $arProp);?>

	<script type="text/javascript">	
		BX.message({
			ADD_REVIEW_RATING: '<?=GetMessageJS("ADD_REVIEW_SLIDE_PANEL_RATING")?>',
			ADD_REVIEW_SELECT_FILES: '<?=GetMessageJS("ADD_REVIEW_SLIDE_PANEL_SELECT_FILES")?>',
			ADD_REVIEW_SELECT_FILE: '<?=GetMessageJS("ADD_REVIEW_SLIDE_PANEL_SELECT_FILE")?>',
			ADD_REVIEW_REMOVE_FILE: '<?=GetMessageJS("ADD_REVIEW_SLIDE_PANEL_REMOVE_FILE")?>',
			ADD_REVIEW_LIMIT_FILES: '<?=GetMessageJS("ADD_REVIEW_SLIDE_PANEL_LIMIT_FILES")?>',
			ADD_REVIEW_TYPE_FILES: '<?=GetMessageJS("ADD_REVIEW_SLIDE_PANEL_TYPE_FILES")?>',
			ADD_REVIEW_NOT_EMPTY_INVALID: '<?=GetMessageJS("ADD_REVIEW_SLIDE_PANEL_NOT_EMPTY_INVALID")?>',
			ADD_REVIEW_FILES_NOT_EMPTY_INVALID: '<?=GetMessageJS("ADD_REVIEW_SLIDE_PANEL_FILES_NOT_EMPTY_INVALID")?>',
			ADD_REVIEW_FILE_NOT_EMPTY_INVALID: '<?=GetMessageJS("ADD_REVIEW_SLIDE_PANEL_FILE_NOT_EMPTY_INVALID")?>',
			ADD_REVIEW_USER_CONSENT_NOT_EMPTY_INVALID: '<?=GetMessageJS("ADD_REVIEW_SLIDE_PANEL_USER_CONSENT_NOT_EMPTY_INVALID")?>',		
			ADD_REVIEW_CAPTCHA_WRONG: '<?=GetMessageJS("ADD_REVIEW_SLIDE_PANEL_CAPTCHA_WRONG")?>',			
			ADD_REVIEW_ALERT_SUCCESS: '<?=GetMessageJS("ADD_REVIEW_SLIDE_PANEL_ALERT_SUCCESS")?>',
			ADD_REVIEW_ALERT_ERROR: '<?=GetMessageJS("ADD_REVIEW_SLIDE_PANEL_ALERT_ERROR")?>',
			ADD_REVIEW_ALERT_PREMODERATION: '<?=GetMessageJS("ADD_REVIEW_SLIDE_PANEL_ALERT_PREMODERATION")?>'
		});
		var <?=$obName?> = new JCAddReviewComponent({
			componentPath: '<?=CUtil::JSEscape($componentPath)?>',
			jsProps: <?=CUtil::PhpToJSObject($jsProps)?>,
			jsPropsReq: <?=CUtil::PhpToJSObject($jsPropsReq)?>,
			userConsent: '<?=($arParams["USER_CONSENT"])?>',
			useCaptcha: '<?=($arParams["USE_CAPTCHA"])?>',
			premoderation: '<?=$arParams["PREMODERATION"]?>',
			container: '<?=$containerName?>'
		});
	</script>
<?}?>