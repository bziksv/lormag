<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$this->setFrameMode(true);

use Bitrix\Main\Localization\Loc;

$obName = 'ob'.preg_replace('/[^a-zA-Z0-9_]/', 'x', $this->randString());
$containerName = 'search-yandex-'.$obName;?>

<div class="search-yandex-form" id="<?=$containerName?>">
	<input type="text" name="q" value="" autocomplete="off" placeholder="<?=Loc::getMessage('SEARCH_YANDEX_TPL_PLACEHOLDER')?>" />
	<span class="title-search-icon"><i class="icon-search"></i></span>
</div>

<?$signer = new \Bitrix\Main\Security\Sign\Signer;
$signedParams = $signer->sign(base64_encode(serialize($arParams)), "search.yandex.enext");?>

<script type="text/javascript">
	BX.message({
		SEARCH_YANDEX_PLACEHOLDER: '<?=GetMessageJS("SEARCH_YANDEX_TPL_PLACEHOLDER")?>',
		SEARCH_YANDEX_AVAILABLE: '<?=GetMessageJS("SEARCH_YANDEX_TPL_AVAILABLE")?>',
        SEARCH_YANDEX_SECTIONS_ALL: '<?=GetMessageJS("SEARCH_YANDEX_TPL_SECTIONS_ALL")?>',
        SEARCH_YANDEX_SECTIONS_HIDE: '<?=GetMessageJS("SEARCH_YANDEX_TPL_SECTIONS_HIDE")?>',
	});
	var <?=$obName?> = new JCSearchYandexComponent({
		componentPath: '<?=CUtil::JSEscape($componentPath)?>',
		showCheckAvailable: '<?=$arParams["SHOW_CHECKBOX_AVAILABLE"]?>',  
		parameters: '<?=CUtil::JSEscape($signedParams)?>',
		container: '<?=$containerName?>'
	});
</script>