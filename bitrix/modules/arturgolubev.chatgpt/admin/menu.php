<?
use \Bitrix\Main\Config\Option;

$module_id = 'arturgolubev.chatgpt';

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/arturgolubev.chatgpt/menu.php");

$arSubmenu[] = [
	'text' => GetMessage("ARTURGOLUBEV_CHATGPT_SUBMENU_SETTINGS"),
	'more_url' => [],
	'url' => '/bitrix/admin/settings.php?lang='.LANG.'&mid=arturgolubev.chatgpt',
	'icon' => 'sys_menu_icon',
];

$arSubmenu[] = [
	'text' => GetMessage("ARTURGOLUBEV_CHATGPT_SUBMENU_ASK"),
	'more_url' => [],
	'url' => '/bitrix/admin/arturgolubev_chatgpt_ask_chatgpt.php?lang='.LANG,
	'icon' => '',
];

$arSubmenu[] = [
	'text' => GetMessage("ARTURGOLUBEV_CHATGPT_SUBMENU_IMAGE"),
	'more_url' => [],
	'url' => '/bitrix/admin/arturgolubev_chatgpt_complection_chatgpt.php?lang='.LANG,
	'icon' => '',
]; 
/* $arSubmenu[] = [
	'text' => GetMessage("ARTURGOLUBEV_CHATGPT_SUBMENU_GENERATE_ELEMENTS"),
	'more_url' => [],
	'url' => '/bitrix/admin/arturgolubev_chatgpt_generate_elements.php?lang='.LANG,
	'icon' => '',
]; */
/* $arSubmenu[] = [
	'text' => GetMessage("ARTURGOLUBEV_CHATGPT_SUBMENU_AUTOMATIC_TASKS"),
	'more_url' => [],
	'url' => '/bitrix/admin/arturgolubev_chatgpt_automatic_tasks.php?lang='.LANG,
	'icon' => '',
]; */


$hl = Option::get($module_id, 'hl_id_templates');
if($hl){
	$arSubmenu[] = [
		'text' => GetMessage("ARTURGOLUBEV_CHATGPT_MY_TEMPLATES"),
		'more_url' => [],
		'url' => '/bitrix/admin/highloadblock_rows_list.php?ENTITY_ID='.$hl.'&lang=ru'.LANG,
		'icon' => '',
	];
}

$aMenu = [
	'parent_menu' => 'global_menu_services',
	'section' => 'arturgolubev_chatgpt',
	'sort' => 1,
	'text' => GetMessage("ARTURGOLUBEV_CHATGPT_MENU_MAIN"),
	'icon' => 'arturgolubev_chatgpt_icon_main',
	'items_id' => 'arcg_icon_main',
	'items' => $arSubmenu,
];


return $aMenu;