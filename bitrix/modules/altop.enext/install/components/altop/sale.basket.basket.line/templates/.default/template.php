<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

$cartId = "bx_basket".$this->randString();
$arParams["cartId"] = $cartId;

$class = "";
if($arParams["SHOW_DELAY"] != "Y" || $arParams["SHOW_BASKET"] != "Y") {
	$class = " one";
} else {
	if($arParams["SHOW_CONTACTS"] != "Y")
		$class = " hide-contacts";
}?>

<div class="top-panel__col top-panel__mini-cart<?=$class?>">
	<script type="text/javascript">
		var <?=$cartId?> = new BitrixSmallCart;
	</script>
	<div id="<?=$cartId?>" class="mini-cart">		
		<?$frame = $this->createFrame($cartId)->begin(($arParams["SHOW_DELAY"] == "Y" ? "<div class='mini-cart__delay'><i class='icon-heart'></i><span class='mini-cart__title'>".Loc::getMessage("SBBL_DELAY")."</span></div>" : "").($arParams["SHOW_BASKET"] == "Y" ? "<div class='mini-cart__cart'><i class='icon-cart'></i><span class='mini-cart__title'>".Loc::getMessage("SBBL_CART")."</span></div>" : ""));
		require(realpath(dirname(__FILE__))."/ajax_template.php");
		$frame->end();?>
	</div>
	<script type="text/javascript">
		<?=$cartId?>.siteId = "<?=SITE_ID?>";
		<?=$cartId?>.cartId = "<?=$cartId?>";
		<?=$cartId?>.ajaxPath = "<?=$componentPath?>/ajax.php";
		<?=$cartId?>.templateName = "<?=$templateName?>";
		<?=$cartId?>.arParams =  <?=CUtil::PhpToJSObject($arParams)?>;
		<?=$cartId?>.activate();
	</script>
</div>