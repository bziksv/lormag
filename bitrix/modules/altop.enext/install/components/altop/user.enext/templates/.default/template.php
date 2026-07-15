<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

$frame = $this->createFrame("top-panel__user-link")->begin("<div class='top-panel__user-link'><div class='top-panel__user-block'><i class='icon-user'></i><span class='top-panel__user-title'>".Loc::getMessage("USER_LOGIN")."</span></div></div>");

$obName = 'ob'.preg_replace('/[^a-zA-Z0-9_]/', 'x', $this->randString());
$containerName = 'user-'.$obName;

$strTitle = $arResult["IS_AUTHORIZED"] ? Loc::getMessage("USER_PROFILE") : Loc::getMessage("USER_LOGIN");?>

<a class="top-panel__user-link" href="<?=($arResult['IS_AUTHORIZED'] ? $arParams['PATH_TO_PERSONAL'] : 'javascript:void(0)')?>" title="<?=$strTitle?>" id="<?=$containerName?>">
	<span class="top-panel__user-block">
		<i class="icon-user"></i>
		<span class="top-panel__user-title"><?=$strTitle?></span>
	</span>	
</a>

<script type="text/javascript">
	BX.message({
		USER_SLIDE_PANEL_TITLE: '<?=GetMessageJS("USER_SLIDE_PANEL_TITLE")?>'
	});
	var <?=$obName?> = new JCUserComponent({
		container: '<?=$containerName?>',
		isAuth: '<?=$arResult["IS_AUTHORIZED"]?>'
	});
</script>

<?$frame->end();?>