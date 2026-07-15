<?
use \Bitrix\Main\Loader,
	\Bitrix\Main\Localization\Loc;

use \Arturgolubev\Chatgpt\Unitools as UTools,
	\Arturgolubev\Chatgpt\Tools;

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

$module_id = 'arturgolubev.chatgpt';
Loader::IncludeModule($module_id);
CJSCore::Init(array("ag_chatgpt_base"));

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/options.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/admin/automatic_tasks.php");

$APPLICATION->SetTitle(Loc::getMessage("ARTURGOLUBEV_CHATGPT_TASKS_TITLE")); 

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$action = $_REQUEST['action'];
$id = $_REQUEST['id'];

if(Loader::IncludeModule($module_id)):?>
	<div class="agcg_adm_page">
		<?if(Tools::checkRights('question')):
				$page = 'list';

				if($action == 'create'){
					$page = 'edit';
				}

				echo '<pre>action: '; print_r($action); echo '</pre>';
				echo '<pre>id: '; print_r($id); echo '</pre>';
				echo '<pre>page: '; print_r($page); echo '</pre>';
				

				include 'generate_elements_'.$page.'.php';
			?>
		<?else:?>
			<?=Loc::getMessage('ARTURGOLUBEV_CHATGPT_RIGHTS_ERROR')?>
		<?endif;?>
	</div>
<?else:
	CAdminMessage::ShowMessage(array("DETAILS"=>Loc::getMessage("ARTURGOLUBEV_CHATGPT_DEMO_IS_EXPIRED"), "HTML"=>true));
endif;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');?>