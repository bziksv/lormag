<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$this->addExternalJs($templateFolder.'/action-pool.min.js');

if(!empty($arResult["OBJECTS"]))
	CJSCore::Init(array("ls"));?>

<div class="slide-panel-basket" data-entity="basketContainer">
	<?if(strlen($arResult["ERROR_MESSAGE"]) <= 0) {?>
		<form method="post" action="<?=POST_FORM_ACTION_URI?>" name="slide-panel-basket-form" id="slide-panel-basket-form">
			<div class="slide-panel-basket-warning-message" data-entity="warningMessage" style="<?=(!empty($arResult['WARNING_MESSAGE']) ? '' : 'display: none;')?>">
				<?if(!empty($arResult["WARNING_MESSAGE"]))
					ShowNote(implode("<br />", $arResult["WARNING_MESSAGE"]), "warning");?>
			</div>
			<?if(!empty($arResult["OBJECTS"]))
				include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_items_objects.php");
			else
				include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_items.php");?>
			<input type="hidden" name="BasketOrder" value="BasketOrder" />
		</form>

		<?$signer = new Bitrix\Main\Security\Sign\Signer;
		$signedTemplate = $signer->sign($templateName, "sale.basket.basket");
		$signedParams = $signer->sign(base64_encode(serialize($arParams)), "sale.basket.basket");

		$arBasketJSParams = array(
			"PATH_TO_ORDER" => $arParams["PATH_TO_ORDER"],
			"MIN_ORDER_SUM" => $arParams["MIN_ORDER_SUM"],
			"OBJECTS" => !empty($arResult["OBJECTS"])
		);?>

		<script type="text/javascript">
			BX.message({
				SBB_SLIDE_PANEL_ORDER_SHORT: '<?=GetMessageJS("SBB_SLIDE_PANEL_ORDER_SHORT")?>',
				SBB_SLIDE_PANEL_TOTAL_DISCOUNT: '<?=GetMessage("SBB_SLIDE_PANEL_TOTAL_DISCOUNT")?>',
				SBB_SLIDE_PANEL_DELETE: '<?=GetMessage("SBB_SLIDE_PANEL_DELETE")?>',
				SBB_SLIDE_PANEL_MEASURE_PC: '<?=GetMessageJS("SBB_SLIDE_PANEL_MEASURE_PC")?>',
				SBB_SLIDE_PANEL_MEASURE_SQ_M: '<?=GetMessageJS("SBB_SLIDE_PANEL_MEASURE_SQ_M")?>',
				SBB_SLIDE_PANEL_NO_ITEMS: '<?=GetMessageJS("SBB_SLIDE_PANEL_NO_ITEMS")?>'
			});
			BX.Sale.BasketComponent.init({
				params: <?=CUtil::PhpToJSObject($arBasketJSParams)?>,
				template: '<?=CUtil::JSEscape($signedTemplate)?>',
				signedParamsString: '<?=CUtil::JSEscape($signedParams)?>',
				templateFolder: '<?=CUtil::JSEscape($templateFolder)?>'
			});
		</script>
	<?} else {
		ShowNote($arResult["ERROR_MESSAGE"], "warning");
	}?>
</div>