<?if(!check_bitrix_sessid())
	return;?>

<style type="text/css">
	.adm-info-message-wrap + .adm-info-message-wrap .adm-info-message{
		margin-top: 0 !important;
	}
</style>

<?=CAdminMessage::ShowNote(GetMessage("ENEXT_MOD_INST_OK"));
echo BeginNote("align='left'");
echo GetMessage("ENEXT_MOD_INST_NOTE");
echo EndNote();?>

<form action="/bitrix/admin/wizard_list.php?lang=ru">
	<input type="submit" name="" value="<?=GetMessage('ENEXT_OPEN_WIZARDS_LIST')?>">
<form>