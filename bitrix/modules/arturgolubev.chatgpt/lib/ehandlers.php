<?
namespace Arturgolubev\Chatgpt;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;

use \Arturgolubev\Chatgpt\Encoding;
use \Arturgolubev\Chatgpt\Tools;

class Ehandlers {
	static function addActionMenu($list){		
		if(!Loader::IncludeModule(\CArturgolubevChatgpt::MODULE_ID) || !defined("ADMIN_SECTION")) return $list;
			
		foreach(['tbl_iblock_element', 'tbl_iblock_list', 'tbl_iblock_section', 'tbl_product_list', 'tbl_product_admin'] as $tName){
			if(Encoding::exStripos($list->table_id, $tName) !== false){
				$isElementsPage = 1;
			}
		}
		
		if($isElementsPage){
			if($_GET["IBLOCK_ID"]){
				if(Tools::checkRights('question')){
					$tmp = $list->arActions;
					
					$list->arActions = [];
					$list->arActions["agcg_generate"] = Loc::getMessage("ARTURGOLUBEV_CHATGPT_MASS_GENERATE_BUTTON");
					
					foreach($tmp as $k=>$v){
						$list->arActions[$k] = $v;
					}
				}
			}
		}
		
		return $list;
	}
}