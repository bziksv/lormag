<?
namespace Arturgolubev\Chatgpt;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;

use \Arturgolubev\Chatgpt\Unitools as UTools,
	\Arturgolubev\Chatgpt\Hl;

class Tools {
	// simple
	static function checkGlobalUser(){
		global $USER;
		if(!is_object($USER)){
			$USER = new \CUser();
		}
	}
	
	static function checkRights($dir){
		global $USER;
		
		self::checkGlobalUser();
		
		if($USER->IsAdmin()) return 1;

		if($dir != 'settings'){
			$arGroups = $USER->GetUserGroupArray();

			$dirGroups = explode(',', UTools::getSetting('rights_'.$dir));
			foreach($dirGroups as $dg){
				if(in_array($dg, $arGroups)){
					return 1;
				}
			}
		}
		
		return 0;
	}
	
	static function checkHlTemplate(){
		$hlID = UTools::getSetting('hl_id_templates');
		if(!$hlID || true){
			include_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/arturgolubev.chatgpt/lib/installation.php';
			\agInstaHelperChatgpt::installTemplateHL();
		}
	}

	static function getSavedTemplates($filter){
		$result = [];

		$hlID = UTools::getSetting('hl_id_templates');
		if($hlID && Loader::includeModule("highloadblock")){
			try {
				$edc = Hl::getDataClassByID($hlID);
				$rsData = $edc::getList([
					"select" => ["*"],
					"order" => ["UF_SORT" => "ASC"],
					"filter" => $filter
				]);
				while($arData = $rsData->Fetch()){
					$result[] = $arData;
				}
			} catch (\Bitrix\Main\SystemException $e) {
				// $error = true; //$e->getMessage();
				// echo '<pre>'; print_r($e->getMessage()); echo '</pre>';
			}
		}

		return $result;
	}

	static function remakeProxy(){
		$proxyList = UTools::explodeByEOL(UTools::getSetting('proxy'));
		if(count($proxyList) && $proxyList[0]){
			$newFormat = [];

			$arProxyNext = explode(' ', $proxyList[0]);

			if($arProxyNext[0]){
				$tmp = explode(':', $arProxyNext[0]);
				$newFormat['ip'] = ($tmp[0]) ? $tmp[0] : '';
				$newFormat['port'] = ($tmp[1]) ? $tmp[1] : '';

				UTools::setSetting('proxy_ip', $newFormat['ip']);
				UTools::setSetting('proxy_port', $newFormat['port']);
			}

			if($arProxyNext[1]){
				$tmp = explode(':', $arProxyNext[1]);
				$newFormat['login'] = ($tmp[0]) ? $tmp[0] : '';
				$newFormat['pass'] = ($tmp[1]) ? $tmp[1] : '';

				UTools::setSetting('proxy_login', $newFormat['login']);
				UTools::setSetting('proxy_password', $newFormat['pass']);
			}
			
			UTools::setSetting('proxy', '');
		}
	}

	static function getGuid4(){
		if (function_exists('com_create_guid') === true){
			return trim(com_create_guid(), '{}');
		}
	
		return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
	}

	static function prepareAnswer($answ){
		if(mb_substr($answ, 0, 1) == '"' && mb_substr($answ, -1, 1) == '"'){
			$answ = mb_substr($answ, 1);
			$answ = mb_substr($answ, 0, -1);
		}
		
		return $answ;
	}

	static function getTokenPrice($provider){
		if($provider == 'sber'){
			$token_price = 0.055;
		}else{
			$model = UTools::getSetting('alg_model');
			
			$token_price = 0;

			$pricemap = [
				'gpt-3.5-turbo-16k' => 0.002,
				'gpt-3.5-turbo' => 0.002,
				'gpt-4' => 0.06,
				'gpt-4-1106-preview' => 0.03,
			];

			if($pricemap[$model]){
				$token_price = $pricemap[$model];
			}
		}

		return $token_price;
	}

	static function calculateTokens($tokens, $provider){
		return Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_WRITE_TOKEN_PRICING", ['#tcount#' => $tokens, '#usd#' => number_format($tokens*self::getTokenPrice($provider) / 1000, 6)]);
	}
}