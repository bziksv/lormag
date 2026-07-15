<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if(Bitrix\Main\Loader::includeModule("currency")) {
	CJSCore::Init(array("currency"));?>
	
	<script type="text/javascript">
		BX.Currency.setCurrencies(<?=CUtil::PhpToJSObject($arResult["CURRENCIES"], false, true, true)?>);
	</script>
<?}