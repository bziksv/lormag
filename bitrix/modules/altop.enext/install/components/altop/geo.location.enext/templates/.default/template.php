<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$this->setFrameMode(true);

use Bitrix\Main\Localization\Loc;

$this->addExternalCss(SITE_TEMPLATE_PATH."/components/bitrix/sale.location.selector.search/slide_panel/style.min.css");	

$obName = "ob".preg_replace("/[^a-zA-Z0-9_]/", "x", $this->GetEditAreaId($this->randString()));
$containerName = "geo-location-".$obName;?>

<div class="top-panel__geo-location-block" id="<?=$containerName?>">
	<i class="icon-map-marker"></i>
	<?$frame = $this->createFrame($containerName)->begin("<span>".Loc::getMessage("GEO_LOCATION_TPL_DETERMINE")."</span>");?>
	<span data-entity="city"><?=(!empty($arResult["CITY"]) ? $arResult["CITY"] : Loc::getMessage("GEO_LOCATION_TPL_LOCATION"))?></span>
	<?$frame->end();?>
</div>

<?$signer = new \Bitrix\Main\Security\Sign\Signer;
$signedParams = $signer->sign(base64_encode(serialize($arParams)), "geo.location.enext");?>

<script type="text/javascript">
	BX.message({
		GEO_LOCATION_SLIDE_PANEL_TITLE: '<?=GetMessageJS("GEO_LOCATION_TPL_SLIDE_PANEL_TITLE")?>'
	});
	BX.Sale.GeoLocationComponent.init({
		siteId: '<?=CUtil::JSEscape(SITE_ID)?>',
		componentPath: '<?=CUtil::JSEscape($componentPath)?>',		
		parameters: '<?=CUtil::JSEscape($signedParams)?>',
		container: '<?=$containerName?>'
	});
</script>