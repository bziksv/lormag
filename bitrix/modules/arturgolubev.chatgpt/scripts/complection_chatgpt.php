<?
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;

use \Arturgolubev\Chatgpt\Unitools as UTools;
use \Arturgolubev\Chatgpt\Tools;

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

$module_id = 'arturgolubev.chatgpt';
Loader::IncludeModule($module_id);
CJSCore::Init(array("ag_chatgpt_base"));

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/options.php");

$APPLICATION->SetTitle(Loc::getMessage("ARTURGOLUBEV_CHATGPT_IMAGE_PAGE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if(Loader::IncludeModule($module_id)):
	if(UTools::getSetting('alg_image_model') == 'dall-e-3'){
		$arSizes = ['1024x1024', '1024x1792', '1792x1024'];
	}else{
		$arSizes = ['256x256', '512x512', '1024x1024', '1024x1792', '1792x1024'];
	}
	
?>
	<div class="agcg_adm_page">
		<?if(Tools::checkRights('question')):?>
			<?=Loc::getMessage("ARTURGOLUBEV_CHATGPT_IMAGE_PAGE_TEXT")?>
			
			<div class="agcg_askpage_form">
				<div class="title"><?=Loc::getMessage("ARTURGOLUBEV_CHATGPT_ASK_QUERY_AREA")?></div>
				
				<form class="input-form js-image-form">
					<input type="hidden" name="action" value="image-request" />
					
					<div class="input-field">
						<div class="input-label"><?=Loc::getMessage("ARTURGOLUBEV_CHATGPT_IMAGE_INPUT_QUERY")?></div>
						<div class="input-field"><textarea name="question" class="js-image-area"></textarea></div>
					</div>
					
					
					<div class="input-field">
						<div class="input-label"><?=Loc::getMessage("ARTURGOLUBEV_CHATGPT_IMAGE_INPUT_SIZE")?></div>
						<div class="input-field">
							<select name="size">
								<?foreach($arSizes as $size):?>
									<option value="<?=$size?>"><?=$size?></option>
								<?endforeach?>
							</select>
						</div>
					</div>


					<div class="input-buttons">
						<div class="input-button input-button-colored js-image-send"><?=Loc::getMessage("ARTURGOLUBEV_CHATGPT_ASK_SEND_QUERY")?></div>
					</div>
				</form>
			</div>
			
			<div class="agcg_askpage_result">
				<div class="title"><?=Loc::getMessage("ARTURGOLUBEV_CHATGPT_ASK_RESULT_AREA")?></div>
				<div class="js-image-result"></div>
			</div>
			
			<script>
				BX.ready(function(){
					agcg.initImagePage();
				});
			</script>
		<?else:?>
			<?=Loc::getMessage('ARTURGOLUBEV_CHATGPT_RIGHTS_ERROR')?>
		<?endif;?>
	</div>
	
<?else:
	CAdminMessage::ShowMessage(array("DETAILS"=>Loc::getMessage("ARTURGOLUBEV_CHATGPT_DEMO_IS_EXPIRED"), "HTML"=>true));
endif;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');?>