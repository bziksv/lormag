<?if(!check_bitrix_sessid())
	return;

echo CAdminMessage::ShowNote(GetMessage("ENEXT_MOD_UNINST_OK"));?>

<form action="<?=$APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?=LANG?>">
	<input type="submit" name="" value="<?=GetMessage('ENEXT_MOD_BACK')?>">
<form>