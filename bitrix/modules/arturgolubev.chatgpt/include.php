<?
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Iblock\InheritedProperty;

use \Arturgolubev\Chatgpt\Encoding;
use \Arturgolubev\Chatgpt\Tools;
use \Arturgolubev\Chatgpt\Hl;
use \Arturgolubev\Chatgpt\Unitools as UTools;

include 'autoload.php';
include 'jscore.php';

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/arturgolubev.chatgpt/include.php");

Class CArturgolubevChatgpt 
{
	const MODULE_ID = 'arturgolubev.chatgpt';
	
	const TOKEN = 300;
	const TIMEOUT = 180;

	const DEBUG = 0;
	const LOG_DEBUG = 0;
	
	static function isDemo(){
		return (CModule::IncludeModuleEx(self::MODULE_ID) == 2);
	}
	
	// chatgpt
	static function checkLimitError($keynum, $api_keys, $result){
		$keynum++;

		$arErrorVariants = [
			'Rate limit reached',
			'You exceeded your current quota',
			// 'Incorrect API key provided',
		];

		if(is_array($result['error']) && $result['error']['message']){
			foreach($arErrorVariants as $errorType){
				if(strpos($result['error']['message'], $errorType) !== false){
					if(isset($api_keys['keys'][$keynum])){
						return 1;
					}
				}
			}
		}

		return 0;
	}

	static function getGptServerName(){
		$sName = UTools::getSetting('chatgpt_custom_base');
		return ($sName) ? $sName : 'https://api.openai.com/v1';
	}


	/* all system */
	static function callChatProvider($question, $options){
		if($options['provider'] == 'sber'){
			return self::callSberGPT($question, $options);
		}else{
			if($options['content_type'] == 'image'){
				return self::gptGenImage($question, $options);
			}else{
				return self::callChatGPT($question, $options);
			}
		}
	}
	
	/* chatgpt chat api */
	static function getChatGptCallParams($message, $options){
		$data = [];

		$role = (isset($options['role']) && $options['role']) ? $options['role'] : UTools::getSetting('alg_role');
		
		$messages = [
			["role" => $role, "content" => $message]
		];
		
		$max_tokens = intval(UTools::getSetting('alg_max_tokens'));

		if($max_tokens < 1){
			$max_tokens = 4096;
		}elseif($max_tokens > 4096){
			$max_tokens = 4096;
		}
		
		$data = [
			"messages" => $messages,
			"model" => UTools::getSetting('alg_model'),
			"temperature" => floatval(UTools::getSetting('alg_temperature')),
			
			"max_tokens" => $max_tokens,
			"frequency_penalty" => 0.0,
			"presence_penalty" => 0.0,
			
			"stop" => ["A:", "Human:", "AI:"],
		]; 
		
		// echo '<pre>'; print_r($data); echo '</pre>';
		
		return $data;
	}
	
	static function getApiKey($api_key_num = 0){
		$result = [
			'error' => '',
			'keys' => [],
			'key' => '',
		];
		
		$result['keys'] = UTools::explodeByEOL(UTools::getSetting('api_key'));
		
		if(count($result['keys']) > 1){
			if(($api_key_num + 1) > count($result['keys'])){
				$result['error'] = 'no_next';
			}
			
			$result['key'] = $result['keys'][$api_key_num];
		}else{
			$result['key'] = $result['keys'][0];
		}
		
		return $result;
	}
	static function gptGenImage($question, $options){
		$result = [];

		$api_keys = self::getApiKey($options['keynum']);

		if($api_keys['error']){
			if($api_keys['error'] == 'no_next'){
				$result['result']['error']['message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_ERROR').' '.Loc::getMessage('ARTURGOLUBEV_CHATGPT_END_KEY_LIST');
			}else{
				$result['result']['error']['message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_ERROR').' '.$api_keys['error'];
			}
			return $result;
		}
		
		if(!$api_keys['key']){
			$result['result']['error']['message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_ERROR').' '.Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_NO_API_KEY_ERROR');
			return $result;
		}

		$url = self::getGptServerName()."/images/generations";
			
		$headers = [
			"Accept: application/json" ,
			"Content-Type: application/json" ,
			"Authorization: Bearer " . $api_keys['key']
		];
		
		$proxy = self::_getChatGptProxy();
		
		$timeout = intval(UTools::getSetting('max_wait_time'));
		if(!$timeout) $timeout = self::TIMEOUT;
		
		$data = [
			'model' => UTools::getSetting('alg_image_model', 'dall-e-2'),
			'prompt' => $question,
			'n' => 1,
			'size' => $options['size']
		];

		if(self::LOG_DEBUG){
			AddMessage2Log($data, 'ag.chatgpt gpt request data', 0);
		}

		// echo '<pre>'; print_r($data); echo '</pre>';
		// echo '<pre>'; print_r($proxy); echo '</pre>';
		// die();

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout); 
		curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_URL, $url);
		// curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //
		// curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); //
		
		if(is_array($proxy)){
			curl_setopt($curl, CURLOPT_PROXY, $proxy['ip']);
			if($proxy['login']){
				curl_setopt($curl, CURLOPT_PROXYUSERPWD, $proxy['login']);
			}
		}

		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);		
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, \Bitrix\Main\Web\Json::Encode($data));
		
		$baseResult = curl_exec($curl);
		
		$result["error"] = curl_error($curl);
		$result["error_no"] = curl_errno($curl);
		$result["header"] = curl_getinfo($curl);

		if(self::LOG_DEBUG){
			AddMessage2Log($result, 'ag.chatgpt gpt request result', 0);
		}

		if($baseResult){
			if(UTools::isHtmlPage($baseResult)){
				$result['result']['error']['message'] = $baseResult;
			}else{
				$result["result"] = \Bitrix\Main\Web\Json::Decode($baseResult);
			}
		}else{
			$result['result']['error']['message'] = '['.$result['header']['http_code'].'] '.(($result["error"]) ? $result["error"] : 'Empty answer.');
		}

		curl_close($curl);

		if(self::checkLimitError($options['keynum'], $api_keys, $result["result"])){
			$result['next_key'] = 1;
		}

		if($result["error_no"] && $result["error_no"] == 28){
			// $result['renew_request'] = 1;
		}
		
		return $result;
	}

	static function callChatGPT($message, $options){
		$result = [];

		$api_keys = self::getApiKey($options['keynum']);

		if($api_keys['error']){
			if($api_keys['error'] == 'no_next'){
				$result['result']['error']['message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_ERROR').' '.Loc::getMessage('ARTURGOLUBEV_CHATGPT_END_KEY_LIST');
			}else{
				$result['result']['error']['message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_ERROR').' '.$api_keys['error'];
			}
			return $result;
		}
		
		if(!$api_keys['key']){
			$result['result']['error']['message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_ERROR').' '.Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_NO_API_KEY_ERROR');
			return $result;
		}
		
		if(self::DEBUG){
			$result = [
				'result' => [
					'usage' => [
						'total_tokens' => 940,
					],
					'choices' => [
						0 => [
							'message' => [
								'content' => 'Test debug content'
							]
						]
					]
				]
			];

			// $result['result']['error']['message'] = 'LIMIT';
		}else{
			$url = self::getGptServerName()."/chat/completions";
			
			$headers = [
				"Accept: application/json" ,
				"Content-Type: application/json" ,
				"Authorization: Bearer " . $api_keys['key']
			];

			$proxy = self::_getChatGptProxy();
			
			$timeout = intval(UTools::getSetting('max_wait_time'));
			if(!$timeout) $timeout = self::TIMEOUT;
			
			$data = self::getChatGptCallParams($message, $options);

			if(self::LOG_DEBUG){
				AddMessage2Log($data, 'ag.chatgpt gpt request data', 0);
			}
			
			// echo '<pre>'; print_r($data); echo '</pre>';
			// echo '<pre>'; print_r($proxy); echo '</pre>';
			// die();

			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout); 
			curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			// curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //
            // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); //

			if(is_array($proxy)){
				curl_setopt($curl, CURLOPT_PROXY, $proxy['ip']);
				if($proxy['login']){
					curl_setopt($curl, CURLOPT_PROXYUSERPWD, $proxy['login']);
				}
			}

			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);		
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, \Bitrix\Main\Web\Json::Encode($data));
			
			$baseResult = curl_exec($curl);
			
			$result["error"] = curl_error($curl);
			$result["error_no"] = curl_errno($curl);
			$result["header"] = curl_getinfo($curl);

			if(self::LOG_DEBUG){
				AddMessage2Log($result, 'ag.chatgpt gpt request result', 0);
			}

			if($baseResult){
				if(UTools::isHtmlPage($baseResult)){
					$result['result']['error']['message'] = $baseResult;
				}else{
					$result["result"] = \Bitrix\Main\Web\Json::Decode($baseResult);
				}
			}else{
				$result['result']['error']['message'] = '['.$result['header']['http_code'].'] '.(($result["error"]) ? $result["error"] : 'Empty answer.');
			}
			
			// echo '<pre>'; print_r($result); echo '</pre>';

			curl_close($curl);
		}

		if(self::checkLimitError($options['keynum'], $api_keys, $result["result"])){
			$result['next_key'] = 1;
		}

		if($result["error_no"] && $result["error_no"] == 28){
			// $result['renew_request'] = 1;
		}
		
		return $result;
	}
		static function _getChatGptProxy(){
			$result = false;

			Tools::remakeProxy();
			
			$data = [
				'ip' => UTools::getSetting('proxy_ip'),
				'port' => UTools::getSetting('proxy_port'),
				'login' => UTools::getSetting('proxy_login'),
				'pass' => UTools::getSetting('proxy_password'),
			];

			if($data['ip']){
				$result = [];

				$result['ip'] = $data['ip'];
				if($data['port']){
					$result['ip'] .= ':'.$data['port'];
				}

				if($data['login']){
					$result['login'] = $data['login'];
					if($data['pass']){
						$result['login'] .= ':'.$data['pass'];
					}
				}
			}
			
			return $result;
		}

	/* sber */
	static function checkSberToken(){
		$exp = intval(intval(UTools::getSetting('sber_access_expires'))/1000);
		$now = intval(microtime(true));

		$real = $exp-$now;

		if($real <= 60){
			return self::getSberToken();
		}

		return [];
	}

	static function getSberToken(){
		$result = [];

		// UTools::setSetting('sber_access_token', '');
		// UTools::setSetting('sber_access_expires', '');

		$url = "https://ngw.devices.sberbank.ru:9443/api/v2/oauth";
			
		$headers = [
			"Authorization: Bearer ".UTools::getSetting('sber_authorization'),
			"Content-Type: application/x-www-form-urlencoded",
			"RqUID: ".Tools::getGuid4(),
		];
		
		$data = [
			'scope' => UTools::getSetting('sber_scope')
		];

		// echo '<pre>'; print_r($headers); echo '</pre>';
		// echo '<pre>'; print_r($data); echo '</pre>';
		
		$timeout = intval(UTools::getSetting('max_wait_time'));
		if(!$timeout) $timeout = self::TIMEOUT;

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout); 
		curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);		
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
		
		$baseResult = curl_exec($curl);
		
		$result["error"] = curl_error($curl);
		$result["error_no"] = curl_errno($curl);
		$result["header"] = curl_getinfo($curl);
		
		curl_close($curl);

		if($baseResult){
			$result["result"] = \Bitrix\Main\Web\Json::Decode($baseResult);

			if($result["result"]['access_token']){
				UTools::setSetting('sber_access_token', $result["result"]['access_token']);
				UTools::setSetting('sber_access_expires', $result["result"]['expires_at']);
			}elseif($result["result"]['message']){
				$result["error_message"] = $result['result']['message'];
				if($result["result"]['code']){
					$result["error_message"] .= ' [error code = '.$result["result"]['code'].']';
				}
			}
		}elseif($result["error"]){
			$result["error_message"] = $result["error"];
		}

		return $result;
	}

	static function callSberGPT($message, $options){
		$result = [];

		$checkResult = self::checkSberToken();
		if(is_array($checkResult) && $checkResult['error_message']){
			$result['result']['error']['message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_ERROR').' '.$checkResult['error_message'];
			return $result;
		}

		$access_token = UTools::getSetting('sber_access_token');
		
		if(!$access_token){
			$result['result']['error']['message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_ERROR').' '.Loc::getMessage('ARTURGOLUBEV_CHATGPT_SBER_NO_ACCESS_TOKEN_ERROR');
			return $result;
		}
		
		if(self::DEBUG){
			$result = [
				'result' => [
					'usage' => [
						'total_tokens' => 940,
					],
					'choices' => [
						0 => [
							'message' => [
								'content' => 'Test debug content'
							]
						]
					]
				]
			];

			// $result['result']['error']['message'] = 'LIMIT';
		}else{
			$url = "https://gigachat.devices.sberbank.ru/api/v1/chat/completions";
			
			$headers = [
				"Content-Type: application/json",
				"Authorization: Bearer " . $access_token
			];
			
			$data = self::_getSberGptCallParams($message, $options);

			// echo '<pre>'; print_r($data); echo '</pre>';

			$timeout = intval(UTools::getSetting('max_wait_time'));
			if(!$timeout) $timeout = self::TIMEOUT;

			$curl = curl_init();
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout); 
			curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);		
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, \Bitrix\Main\Web\Json::Encode($data));
			
			$baseResult = curl_exec($curl);
			
			$result["error"] = curl_error($curl);
			$result["error_no"] = curl_errno($curl);
			$result["header"] = curl_getinfo($curl);

			if($baseResult){
				if(UTools::isJsonPage($baseResult)){
					$result["result"] = \Bitrix\Main\Web\Json::Decode($baseResult);

					if($result["result"]['status']){
						$result['result']['error']['message'] = $result['result']['message'];
					}
				}else{
					$result['result']['error']['message'] = $baseResult;
				}
			}else{
				$result['result']['error']['message'] = 'Empty answer';
			}
			
			curl_close($curl);
		}

		// AddMessage2Log($result, 'result', 0);
		
		return $result;
	}
		static function _getSberGptCallParams($message, $options){
			$data = [];

			$role = (isset($options['role']) && $options['role']) ? $options['role'] : UTools::getSetting('sber_role');
			
			$max_tokens = intval(UTools::getSetting('sber_max_tokens'));
			if(!$max_tokens) $max_tokens = 2048;
			
			$data = [
				"messages" => [
					["role" => $role, "content" => $message]
				],
				"model" => UTools::getSetting('sber_model', 'GigaChat:latest'),
				"temperature" => floatval(UTools::getSetting('sber_temperature')),
				"max_tokens" => $max_tokens,
			];
			
			return $data;
		}

	static function applyDefaultVals($postFields, $get = 1){
		$arCheckFields = ['provider', 'operation', 'type', 'for', 'from', 'length', 'paragraph', 'html', 'lang', 'template_element', 'template_section', 'template_image', 'mass_save_field', 'save_only_empty'];
		
		if(!isset($_SESSION['AGCG_DEFAULT']) || !is_array($_SESSION['AGCG_DEFAULT'])){
			$_SESSION['AGCG_DEFAULT'] = [];
		}
		
		foreach($arCheckFields as $field){
			if($postFields[$field]){
				if($field == 'template_element' || $field == 'template_section'){
					$postFields[$field] = Encoding::convertFromUtf($postFields[$field]);
				}
				
				$_SESSION['AGCG_DEFAULT'][$field] = $postFields[$field];
			}
		}
		
		if($get){
			foreach($arCheckFields as $field){
				if(!$postFields[$field]){
					if(isset($_SESSION['AGCG_DEFAULT'][$field])){
						$postFields[$field] = $_SESSION['AGCG_DEFAULT'][$field];
					}
				}
			}
		}
		
		// echo '<pre>'; print_r($_SESSION['AGCG_DEFAULT']); echo '</pre>';
		
		return $postFields;
	}
	
	static function createImage($input){
		$data = self::gptGenImage($input['question'], $input);

		if(is_array($data['result']['error'])){
			$result['renew_request'] = intval($data['renew_request']);
			$result['next_key'] = ($data['next_key']) ? 1 : 0;
			$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_CHATGPT_ERROR').' '.($data['result']['error']['message'] ? $data['result']['error']['message'] : $data['result']['error']['code']);
		}else{
			// echo '<pre>'; print_r($data['result']['data'][0]); echo '</pre>';

			if($data['result']['data'][0]){
				$result['created_image'] = $data['result']['data'][0]['url'];
			}else{
				$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_CREATE_ERROR');
			}
		}

		return $result;
	}

	static function askQuestion($input){
		$result = [];

		$options = [
			'provider' => $input['provider'],
			'keynum' => intval($input['keynum']),
		];

		$data = self::callChatProvider($input['question'], $options);
		
		$result['full_result'] = $data['result'];
		
		if(is_array($data['result']['error'])){
			$result['renew_request'] = intval($data['renew_request']);
			$result['next_key'] = ($data['next_key']) ? 1 : 0;
			$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_CHATGPT_ERROR').' '.($data['result']['error']['message'] ? $data['result']['error']['message'] : $data['result']['error']['code']);
		}else{
			if($data['result']['choices'][0]['message']['content']){
				$result['created_text'] = $data['result']['choices'][0]['message']['content'];
			}else{
				$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_CREATE_ERROR');
				if(isset($data['result']['detail']) && $data['result']['detail']){
					$result['error_message'] .= ': ' . $data['result']['detail'];
				}
			}
		}
		
		return $result;
	}
	
	static function makeQuestionByTemplate($template, $main_info){
		$question = $template;
		
		preg_match_all('/#([a-zA-Z_0-9]+)#/is', $template, $match);
		
		if(is_array($match[1]) && count($match[1])){
			$arFind = [];
			$arReplace = [];
			foreach($match[1] as $macros){
				$arFind[] = '#'.$macros.'#';
				$arReplace[] = $main_info[$macros];
			}
			
			$question = str_replace($arFind, $arReplace, $question);
		}
		
		
		return $question;
	}
	
	static function makeQuestion($input, $main_info){
		// echo '<pre>'; print_r($input); echo '</pre>';
		
		$main_info = strip_tags(htmlspecialchars_decode($main_info));
		
		if($input['operation'] == 'REWRITE'){
			$question = Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_REWRITE");
		}elseif($input['operation'] == 'TRANSLATE'){
			$question = Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_TRANSLATE");
		}elseif($input['operation'] == 'CREATE' && $input['type'] == 'REVIEW'){
			$question = Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_REVIEW");
		}else{
			$question = Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE");
		}
		
		if($input['operation'] == 'REWRITE'){
			if($input['for'] != 'ARTICLE'){
				switch($input['type']){
					case 'TEXT': 
						$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_REWRITE_TYPE_DESCRIPTION");
					break;
				}
			}
			
			switch($input['for']){
				case 'PRODUCT': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_REWRITE_FOR_PRODUCT");
				break;
				case 'ARTICLE': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_REWRITE_FOR_ARTICLE");
				break;
				case 'SERVICE': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_REWRITE_FOR_SERVICE");
				break;
			}
			
			// $question .= '. ';
			
			if($input['html']){
				$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_REWRITE_HTML");
			}elseif($input['paragraph']){
				$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_REWRITE_PARAGRAPH");
			}
			
			$question .= $main_info;
		}else{
			switch($input['type']){
				case 'H1': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_TYPE_H1");
				break;
				case 'TITLE': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_TYPE_TITLE");
				break;
				case 'DESCRIPTION': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_TYPE_DESCRIPTION");
				break;
				case 'KEYWORDS': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_TYPE_KEYWORDS");
				break;
				case 'TEXT': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_TYPE_TEXT");
				break;
				case 'REVIEW': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_TYPE_REVIEW");
				break;
			}
			
			switch($input['for']){
				case 'PRODUCT': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_FOR_PRODUCT");
				break;
				case 'ARTICLE': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_FOR_ARTICLE");
				break;
				case 'SERVICE': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_FOR_SERVICE");
				break;
				case 'PRODUCT_SECTION': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_FOR_PRODUCT_SECTION");
				break;
			}
			
			$question .= '"' . $main_info . '" ';
		
			if($input['lang']){
				$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_TRANSLATE_LANG", ['#lang#' => Encoding::convertFromUtf($input['lang'])]);
			}
			
			if($input['paragraph']){
				$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_PARAGRAPH");
			}
			
			if($input['length']){
				$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_LENGTH", ['#length#' => $input['length']]);
			}
			
			if($input['html']){
				$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_HTML");
			}
		}

		if($input['additionals']){
			$question .= '. '.$input['additionals'];
		}
		
		return $question;
	}
	
	// element
	static function getElementBaseData($elementId){
		$result = [];
		
		if(Loader::IncludeModule("iblock")){
			$domain = (CMain::IsHTTPS() ? "https://" : "http://") . $_SERVER["HTTP_HOST"];

			$res = CIBlockElement::GetList([], ['ID' => $elementId], false, ["nPageSize"=>1], ['NAME', 'PREVIEW_TEXT', 'DETAIL_TEXT', '*']);
			while($ob = $res->GetNextElement(true, false)){
				$result = $ob->GetFields();
				$props = $ob->GetProperties();
				
				$result['DETAIL_PAGE_URL'] = $domain . $result['DETAIL_PAGE_URL'];
				
				if($result['PREVIEW_PICTURE']){
					$result['PREVIEW_PICTURE'] = $domain . CFile::GetPath($result['PREVIEW_PICTURE']);
				}

				if($result['DETAIL_PICTURE']){
					$result['DETAIL_PICTURE'] = $domain . CFile::GetPath($result['DETAIL_PICTURE']);
				}

				foreach($props as $property){
					if($property['USER_TYPE'] == 'HTML' && is_array($property['VALUE'])){
						$property['VALUE'] = $property['VALUE']['TEXT'];
					}
					
					if($property['USER_TYPE'] == 'directory'){
						if(is_array($property['VALUE'])){
							foreach($property['VALUE'] as $k=>$v){
								$property['VALUE'][$k] = Hl::getPropValueField($property, $v);
							}
						}else{
							$property['VALUE'] = Hl::getPropValueField($property, $property['VALUE']);
						}
					}
					
					if($property['PROPERTY_TYPE'] == 'E'){
						if(!is_array($property['VALUE'])){
							$property['VALUE'] = [$property['VALUE']];
						}

						foreach($property['VALUE'] as $k=>$v){
							if($v){
								$resOne = CIBlockElement::GetList([], ['ID' => $v], false, ["nPageSize"=>1], ['ID', 'NAME']);
								while($arOneFields = $resOne->Fetch()){
									$property['VALUE'][$k] = $arOneFields['NAME'];
								}
							}
						}
					}

					if($property['PROPERTY_TYPE'] == 'F'){
						if(!is_array($property['VALUE'])){
							if($property['VALUE']){
								$property['VALUE'] = [$property['VALUE']];
							}else{
								$property['VALUE'] = [];
							}
						}

						foreach($property['VALUE'] as $k=>$v){
							if($v){
								$property['VALUE'][$k] = $domain . CFile::GetPath($v);
							}
						}
					}

					$result['PROPERTY_'.$property['CODE']] = (is_array($property['VALUE']) ? implode(', ', $property['VALUE']) : $property['VALUE']);
				}
				
				$ipropElementValues = new InheritedProperty\ElementValues($result['IBLOCK_ID'], $result['ID']);
				$values = $ipropElementValues->getValues();
				foreach($values as $key=>$val){
					$result['SEO_'.$key] = $val;
				}

				if($result['IBLOCK_SECTION_ID']){
					$rdbSections = \Bitrix\Iblock\SectionTable::getList(array(
						'select' => array('NAME'),
						'filter' => array('ID' => $result['IBLOCK_SECTION_ID'])
					));
					while ($dctSection = $rdbSections->fetch()) {
						$result['PARENT_SECTION_NAME'] = $dctSection['NAME'];
					}
				}
				
			}
		}
		
		return $result;
	}

	static function checkElementEmptySaveFiled($ibid, $eid, $field){
		$elementInfo = self::getElementBaseData($eid);
		return ['result' => ($elementInfo[$field] == ''), 'element_name' => $elementInfo['NAME']];
	}
	
	static function createElementText($input){
		$result = [];
		$options = [
			'content_type' => 'text',
			'provider' => $input['provider'],
			'keynum' => intval($input['keynum'])
		];

		if($input['type'] == 'KEYWORDS'){
			$options['role'] = 'system';
		}
		
		$elementInfo = self::getElementBaseData($input['ID']);
		$result['element_name'] = $elementInfo['NAME'];
		
		// echo '<pre>'; print_r($input); echo '</pre>';
		// echo '<pre>elementInfo '; print_r($elementInfo); echo '</pre>';
		
		if(!$elementInfo[$input['from']] && !in_array($input['operation'], ['TEMPLATE', 'IMAGE'])){
			$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_EMPTY_FIELD_FROM_ERROR');
		}

		if($input['mass_generation'] && self::isDemo()){
			$actualToken = intval(UTools::getSetting('alg_max_token'));
			if($actualToken >= self::TOKEN){
				$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_DEMO_MASS_GENERATE_LIMIT');
			}
		}
		
		if(!$result['error_message']){
			$result['question_show'] = (UTools::getSetting('show_query') == 'Y');
			$result['show_tokens'] = (UTools::getSetting('show_tokens') == 'Y');
			
			if($input['operation'] == 'TEMPLATE'){
				$input['template_element'] = Encoding::convertFromUtf($input['template_element']);
				$result['question'] = self::makeQuestionByTemplate($input['template_element'], $elementInfo);
			}elseif($input['operation'] == 'IMAGE'){
				$options['content_type'] = 'image';
				$options['size'] = $input['size'];

				$input['template_image'] = Encoding::convertFromUtf($input['template_image']);
				$result['question'] = self::makeQuestionByTemplate($input['template_image'], $elementInfo);
			}else{
				$result['question'] = self::makeQuestion($input, $elementInfo[$input['from']]);
			}

			$result['content_type'] = $options['content_type'];

			foreach(GetModuleEvents(self::MODULE_ID, "modifyElementQuestionBeforeSend", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, [&$result['question'], $input, $elementInfo]);
			
			if(self::LOG_DEBUG){
				AddMessage2Log($input, 'ag.chatgpt createElementText', 0);
			}

			$data = self::callChatProvider($result['question'], $options);

			$input['question'] = $result['question'];
			
			if($input['preview']){
				return $result;
			}

			foreach(GetModuleEvents(self::MODULE_ID, "modifyElementAnswer", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, [&$data, $input, $elementInfo]);

			if(is_array($data['result']['error'])){
				$result['renew_request'] = intval($data['renew_request']);
				$result['next_key'] = ($data['next_key']) ? 1 : 0;
				$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_CHATGPT_ERROR').' '.($data['result']['error']['message'] ? $data['result']['error']['message'] : $data['result']['error']['code']);
			}else{
				if($input['mass_generation'] && self::isDemo()){
					UTools::setSetting('alg_max_token', $actualToken + 1);
				}
				
				if($options['content_type'] == 'text'){
					$answ = $data['result']['choices'][0]['message']['content'];
					if($answ){
						$answ = Tools::prepareAnswer($answ);
						$result['answer'] = $answ;
						$result['used_tokens_cnt'] = intval($data['result']['usage']['total_tokens']);
						$result['used_tokens'] = Tools::calculateTokens($result['used_tokens_cnt'], $options['provider']);
					}else{
						$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_CREATE_ERROR');
						if(isset($data['result']['detail']) && $data['result']['detail']){
							$result['error_message'] .= ': ' . $data['result']['detail'];
						}
					}
				}else{
					$answ = $data['result']['data'][0]['url'];
					if($answ){
						$result['answer'] = $answ;
						$result['used_tokens_cnt'] = intval($data['result']['usage']['total_tokens']);
						$result['used_tokens'] = Tools::calculateTokens($result['used_tokens_cnt'], $options['provider']);
					}else{
						$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_CREATE_ERROR');
					}
				}
			}
		}
		
		// echo '<pre>input '; print_r($input); echo '</pre>';
		// echo '<pre>'; print_r($result); echo '</pre>';
		
		return $result;
	}
	
	static function saveToElement($params){
		Loader::includeModule('iblock');
		
		if($params['re_encoding']){
			$params['genresult'] = Encoding::convertFromUtf($params['genresult']);
		}
		
		$result = [
			'genresult' => $params['genresult'],
			'savefield_type' => 'field',
			'savefield' => $params['savefield'],
		];
		
		if(strpos($result['savefield'], 'PROPERTY_') !== false){
			$result['savefield_type'] = 'property';
			$result['savefield'] = str_replace('PROPERTY_', '', $result['savefield']);
		}elseif(strpos($result['savefield'], 'SEO_') !== false){
			$result['savefield_type'] = 'seo';
			$result['savefield'] = str_replace('SEO_', '', $result['savefield']);
		}

		if($result['savefield_type'] == 'field'){
			$el = new CIBlockElement;
			
			$updateData = [
				$result['savefield'] => $result['genresult']
			];
			
			if(in_array($result['savefield'], ['PREVIEW_TEXT', 'DETAIL_TEXT']) && $params['html']){
				$updateData[$result['savefield'].'_TYPE'] = 'html';
			}
			
			if(in_array($result['savefield'], ['PREVIEW_PICTURE', 'DETAIL_PICTURE'])){
				$updateData[$result['savefield']] = CFile::MakeFileArray($result['genresult']);
			}

			$res = $el->Update($params['ID'], $updateData);
			if(!$res){
				$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_SAVE_ERROR') . $res->LAST_ERROR;
			}
		}
		
		if($result['savefield_type'] == 'property'){
			$propertyInfo = [];
			$properties = \CIBlockProperty::GetList([], ["CODE"=>$result['savefield'], "IBLOCK_ID"=>$params['IBLOCK_ID']]);
			if($prop_fields = $properties->GetNext()){
				$propertyInfo = $prop_fields;
			}
			
			$result['savefield_id'] = $propertyInfo['ID'];

			if($propertyInfo['PROPERTY_TYPE'] == 'L' && !$propertyInfo['USER_TYPE']){
				$save_xml_id = \Cutil::translit($result['genresult'], "ru", ["replace_space" => "_", "replace_other"=> "-"]);

				$db_enum_list = CIBlockProperty::GetPropertyEnum($propertyInfo['ID'], [], Array("IBLOCK_ID"=>$params['IBLOCK_ID'], "VALUE"=>$result['genresult']));
				if($ar_enum_list = $db_enum_list->GetNext()){
					$saveID = $ar_enum_list['ID'];
				}

				if(!$saveID){
					$db_enum_list = CIBlockProperty::GetPropertyEnum($propertyInfo['ID'], [], Array("IBLOCK_ID"=>$params['IBLOCK_ID'], "XML_ID"=>$save_xml_id));
					if($ar_enum_list = $db_enum_list->GetNext()){
						$saveID = $ar_enum_list['ID'];
					}
				}

				if(!$saveID){
					$ibpenum = new CIBlockPropertyEnum;
					$saveID = $ibpenum->Add([
						'PROPERTY_ID'=>$propertyInfo['ID'],
						'VALUE'=> $result['genresult'],
						'XML_ID'=> $save_xml_id,
					]);
				}

				$saveData = [$result['savefield'] => $saveID];
			}elseif($propertyInfo['USER_TYPE'] == 'HTML'){
				$result['savefield_type'] = 'property_html';
				
				$saveData = [
					$result['savefield'] => [
						'VALUE' => ['TYPE'=>'HTML', 'TEXT'=>$result['genresult']]
					]
				];
			}else{
				$saveData = [$result['savefield'] => $result['genresult']];
			}

			\CIBlockElement::SetPropertyValuesEx($params['ID'], $params['IBLOCK_ID'], $saveData);
		}
		
		if($result['savefield_type'] == 'seo'){
			$ipropElementTemplates = new InheritedProperty\ElementTemplates($params['IBLOCK_ID'], $params['ID']);			
			$ipropElementTemplates->set([$result['savefield'] => $result['genresult']]);
			
			$ipropElementValues = new InheritedProperty\ElementValues($params['IBLOCK_ID'], $params['ID']);
			$ipropElementValues->clearValues();
		}
		
		return $result;
	}
	
	// section
	static function getSectionBaseData($iblockId, $sectionId){
		$result = [];
		
		if(Loader::IncludeModule("iblock")){
			$domain = (CMain::IsHTTPS() ? "https://" : "http://") . $_SERVER["HTTP_HOST"];

			$db_list = CIBlockSection::GetList([$by=>$order], ['IBLOCK_ID' => $iblockId, 'ID'=>$sectionId], false, ['ID', 'NAME', 'DESCRIPTION', 'IBLOCK_ID', 'SECTION_PAGE_URL', 'PICTURE', 'UF_*']);
			while($ar_result = $db_list->GetNext(true, false)){
				$result = $ar_result;
				
				// echo '<pre>'; print_r($result); echo '</pre>';

				$result['SECTION_PAGE_URL'] = (CMain::IsHTTPS() ? "https://" : "http://") . $_SERVER["HTTP_HOST"] . $result['SECTION_PAGE_URL'];
				
				if($result['PICTURE']){
					$result['PICTURE'] = $domain.CFile::GetPath($result['PICTURE']);
				}

				$ipropElementValues = new InheritedProperty\SectionValues($ar_result['IBLOCK_ID'], $ar_result['ID']);
				$values = $ipropElementValues->getValues();
				foreach($values as $key=>$val){
					$result['SEO_'.$key] = $val;
				}
			}
		}
		
		return $result;
	}
	
	static function checkSectionEmptySaveFiled($ibid, $sid, $field){
		$elementInfo = self::getSectionBaseData($ibid, $sid);
		return ['result' => ($elementInfo[$field] == ''), 'element_name' => $elementInfo['NAME']];
	}

	static function createSectionText($input){
		$result = [];
		
		$options = [
			'content_type' => 'text',
			'provider' => $input['provider'],
			'keynum' => intval($input['keynum'])
		];
		
		if($input['type'] == 'KEYWORDS'){
			$options['role'] = 'system';
		}

		$elementInfo = self::getSectionBaseData($input['IBLOCK_ID'], $input['ID']);
		$result['element_name'] = $elementInfo['NAME'];
		
		// echo '<pre>'; print_r($input); echo '</pre>';
		// echo '<pre>elementInfo '; print_r($elementInfo); echo '</pre>';
		
		if(!$elementInfo[$input['from']] && !in_array($input['operation'], ['TEMPLATE', 'IMAGE'])){
			$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_EMPTY_FIELD_FROM_ERROR');
		}
		
		if($input['mass_generation'] && self::isDemo()){
			$actualToken = intval(UTools::getSetting('alg_max_token'));
			if($actualToken >= self::TOKEN){
				$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_DEMO_MASS_GENERATE_LIMIT');
			}
		}
		
		if(!$result['error_message']){
			$result['question_show'] = (UTools::getSetting('show_query') == 'Y');
			$result['show_tokens'] = (UTools::getSetting('show_tokens') == 'Y');
			
			if($input['operation'] == 'TEMPLATE'){
				$input['template_section'] = Encoding::convertFromUtf($input['template_section']);
				$result['question'] = self::makeQuestionByTemplate($input['template_section'], $elementInfo);
			}elseif($input['operation'] == 'IMAGE'){
				$options['content_type'] = 'image';
				$options['size'] = $input['size'];

				$input['template_image'] = Encoding::convertFromUtf($input['template_image']);
				$result['question'] = self::makeQuestionByTemplate($input['template_image'], $elementInfo);
			}else{
				$result['question'] = self::makeQuestion($input, $elementInfo[$input['from']]);
			}

			$result['content_type'] = $options['content_type'];
			
			foreach(GetModuleEvents(self::MODULE_ID, "modifySectionQuestionBeforeSend", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, [&$result['question'], $input, $elementInfo]);
			
			$data = self::callChatProvider($result['question'], $options);

			$input['question'] = $result['question'];

			if($input['preview']){
				return $result;
			}
			
			foreach(GetModuleEvents(self::MODULE_ID, "modifySectionAnswer", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, [&$data, $input, $elementInfo]);

			if(is_array($data['result']['error'])){
				$result['renew_request'] = intval($data['renew_request']);
				$result['next_key'] = ($data['next_key']) ? 1 : 0;
				$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_CHATGPT_ERROR').' '.($data['result']['error']['message'] ? $data['result']['error']['message'] : $data['result']['error']['code']);
			}else{
				if($input['mass_generation'] && self::isDemo()){
					UTools::setSetting('alg_max_token', $actualToken + 1);
				}

				if($options['content_type'] == 'text'){
					$answ = $data['result']['choices'][0]['message']['content'];
					if($answ){
						$answ = Tools::prepareAnswer($answ);
						$result['answer'] = $answ;
						$result['used_tokens_cnt'] = intval($data['result']['usage']['total_tokens']);
						$result['used_tokens'] = Tools::calculateTokens($result['used_tokens_cnt'], $options['provider']);
					}else{
						$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_CREATE_ERROR');
						if(isset($data['result']['detail']) && $data['result']['detail']){
							$result['error_message'] .= ': ' . $data['result']['detail'];
						}
					}
				}else{
					$answ = $data['result']['data'][0]['url'];
					if($answ){
						$result['answer'] = $answ;
						$result['used_tokens_cnt'] = intval($data['result']['usage']['total_tokens']);
						$result['used_tokens'] = Tools::calculateTokens($result['used_tokens_cnt'], $options['provider']);
					}else{
						$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_CREATE_ERROR');
					}
				}
			}
		}
		
		// echo '<pre>input '; print_r($input); echo '</pre>';
		// echo '<pre>'; print_r($result); echo '</pre>';
		
		return $result;
	}
	
	static function saveToSection($params){
		Loader::includeModule('iblock');
		
		if($params['re_encoding']){
			$params['genresult'] = Encoding::convertFromUtf($params['genresult']);
		}
		
		$result = [
			'genresult' => $params['genresult'],
			'savefield_type' => 'field',
			'savefield' => $params['savefield'],
		];
		
		if(strpos($result['savefield'], 'UF_') !== false){
			$result['savefield_type'] = 'section_uf';
		}elseif(strpos($result['savefield'], 'SEO_') !== false){
			$result['savefield_type'] = 'seo';
			$result['savefield'] = str_replace('SEO_', '', $result['savefield']);
		}
		
		if($result['savefield_type'] == 'field' || $result['savefield_type'] == 'section_uf'){
			$bs = new CIBlockSection;
			
			$updateData = [
				$result['savefield'] => $result['genresult']
			];
			
			if(in_array($result['savefield'], ['DESCRIPTION']) && $params['html']){
				$updateData[$result['savefield'].'_TYPE'] = 'html';
			}

			if(in_array($result['savefield'], ['PICTURE'])){
				$updateData[$result['savefield']] = CFile::MakeFileArray($result['genresult']);
			}
			
			$res = $bs->Update($params['ID'], $updateData);
			if(!$res){
				$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_SAVE_ERROR') . $res->LAST_ERROR;
			}
		}
		
		if($result['savefield_type'] == 'seo'){
			$ipropElementTemplates = new \Bitrix\Iblock\InheritedProperty\SectionTemplates($params['IBLOCK_ID'], $params['ID']);			
			$ipropElementTemplates->set([$result['savefield'] => $result['genresult']]);
			
			$ipropElementValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($params['IBLOCK_ID'], $params['ID']);
			$ipropElementValues->clearValues();
		}
		
		return $result;
	}
	
	// events
	static function addActionMenu(&$list){
		$list = \Arturgolubev\Chatgpt\Ehandlers::addActionMenu($list);
	}
	
	static function onEpilog(){
		if(!Loader::IncludeModule(self::MODULE_ID) || !defined("ADMIN_SECTION")) return 0;
		
		$cur = UTools::GetCurPage();
		if(Tools::checkRights('question')){
			$element = ($cur == '/bitrix/admin/iblock_element_edit.php' ? 1 : 0);
			$section = ($cur == '/bitrix/admin/iblock_section_edit.php' ? 1 : 0);
			
			if($element || $section){
				\CJSCore::Init(["ag_chatgpt_base"]);
				
				$genOption = [
					'ENTITY_TYPE' => ($element ? 'element' : 'section'),
					'ID' => $_GET['ID'],
					'IBLOCK_ID' => $_GET['IBLOCK_ID'],
				];
				
				echo '<script>agcg.initElementButton('.CUtil::PhpToJSObject($genOption).');</script>';
			}
			
			$isSectionPage = ($cur == '/bitrix/admin/iblock_section_admin.php');
			$isCatalogPage = ($cur == '/bitrix/admin/iblock_list_admin.php' || $cur == '/bitrix/admin/cat_product_list.php' || $isSectionPage);
			$isCatalogElementPage = ($cur == '/bitrix/admin/iblock_element_admin.php' || $cur == '/bitrix/admin/cat_product_admin.php');
			
			if($isCatalogPage || $isCatalogElementPage){
				\CJSCore::Init(["ag_chatgpt_base"]);
				
				$elements = [];
				$sections = [];
				
				foreach($_POST["ID"] as $v){
					$first = substr($v, 0, 1);
					
					if($first == 'S'){
						$sections[] = substr($v, 1);
					}elseif($isSectionPage){
						$sections[] = $v;
					}elseif($first == 'E'){
						$elements[] = substr($v, 1);
					}else{
						$elements[] = $v;
					}
				}
				
				if(is_array($_POST["action"])){
					foreach($_POST["action"] as $av){
						if($av == "agcg_generate"){
							$showWindow = 1;
						}
					}
				}else{
					$showWindow = ($_POST["action"] == 'agcg_generate');
				}
				
				if($showWindow){
					$demo = self::isDemo();
					$dcount = self::TOKEN - intval(UTools::getSetting('alg_max_token'));
					if($dcount < 0){
						$dcount = 0;
					}
					?>
						<script>
							var agcgInitParams = {
								demo: <?=intval($demo)?>,
								dcount: <?=$dcount?>,
								ibid: <?=IntVal($_GET["IBLOCK_ID"])?>,
								eids: <?=\CUtil::PhpToJSObject($elements)?>,
								sids: <?=\CUtil::PhpToJSObject($sections)?>,
							};
							top.agcg.initMassWork(agcgInitParams);
						</script>
					<?
				}
			}
		}
	}
}
?>
