<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

$loadCurrency = Loader::includeModule("currency");

CJSCore::Init(array("popup", "currency"));?>

<script type="text/javascript">
	BX.Currency.setCurrencies(<?=$templateData["CURRENCIES"]?>);
</script>