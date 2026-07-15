<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$this->setFrameMode(true);

$obName = "ob".preg_replace("/[^a-zA-Z0-9_]/", "x", $this->GetEditAreaId($this->randString()));
$containerName = "slide-panel-geo-location-".$obName;?>

<div class="slide-panel__form" id="<?=$containerName?>">
	<div class="form-group">
		<?$APPLICATION->IncludeComponent("bitrix:sale.location.selector.search", "slide_panel",
			array(
				"ID" => $arParams["LOCATION_ID"],
				"CODE" => "",
				"INPUT_NAME" => "LOCATION",
				"PROVIDE_LINK_BY" => "id",
				"FILTER_BY_SITE" => "Y",
				"FILTER_SITE_ID" => $arParams["SITE_ID"],
				"SHOW_DEFAULT_LOCATIONS" => "Y",
				"JSCONTROL_GLOBAL_ID" => "",
				"JS_CALLBACK" => "",
				"CACHE_TYPE" => $arParams["CACHE_TYPE"],
				"CACHE_TIME" => $arParams["CACHE_TIME"],
				"SUPPRESS_ERRORS" => "N",
				"INITIALIZE_BY_GLOBAL_EVENT" => "",
				"SHOW_TITLE" => "N",
				"SHOW_SELECT_LOCATION_BUTTON" => "Y"
			),
			$component,
			array("HIDE_ICONS" => "Y")
		);?>
	</div>	
</div>

<?$signer = new \Bitrix\Main\Security\Sign\Signer;
$signedParams = $signer->sign(base64_encode(serialize($arParams)), "geo.location.enext");?>

<script type="text/javascript">
	var <?=$obName?> = new JCGeoLocationSlidePanelComponent({
		componentPath: '<?=CUtil::JSEscape($componentPath)?>',
		container: '<?=$containerName?>'
	});
</script>