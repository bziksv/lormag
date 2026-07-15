<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

$this->IncludeLangFile("template.php");

if($arParams["SHOW_DELAY"] == "Y") {?>
	<a class="mini-cart__delay<?=($arResult['DELAY']['NUM_PRODUCTS'] <= 0 ? ' empty' : '')?>" href="<?=$arParams['PATH_TO_BASKET'].($arParams['SHOW_BASKET'] == 'Y' ? '?delay=Y' : '')?>" title="<?=Loc::getMessage('SBBL_DELAY')?>" data-entity="delay">
		<i class="icon-heart"></i>		
		<span class="mini-cart__count"><?=$arResult["DELAY"]["NUM_PRODUCTS"]?></span>
		<span class="mini-cart__title"><?=Loc::getMessage("SBBL_DELAY")?></span>
	</a>
<?}
if($arParams["SHOW_BASKET"] == "Y") {?>
	<a class="mini-cart__cart<?=($arResult['CART']['NUM_PRODUCTS'] <= 0 ? ' empty' : '')?>" href="<?=($arParams['BASKET_VIEW'] == 'RIGHT' && !CSite::inDir(SITE_DIR.'personal/cart/') && !CSite::inDir(SITE_DIR.'personal/order/make/') ? 'javascript:void(0)' : $arParams['PATH_TO_BASKET'])?>" title="<?=Loc::getMessage('SBBL_CART')?>" data-entity="cart">
		<i class="icon-cart"></i>		
		<span class="mini-cart__count"><?=$arResult["CART"]["NUM_PRODUCTS"]?></span>
		<span class="mini-cart__title"><?=Loc::getMessage("SBBL_CART")?></span>
	</a>
<?}