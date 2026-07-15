<?
namespace Arturgolubev\Chatgpt;

use \Bitrix\Main\Loader,
	\Bitrix\Main\Localization\Loc;

use \Arturgolubev\Chatgpt\Unitools as UTools,
	\Arturgolubev\Chatgpt\Tools;

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/arturgolubev.chatgpt/forms.php");

class FormConstructor {
	// all
	static function _makeHtmlForJsForm($arFields){
		$html = '<table cellspacing="0" cellpadding="0" border="0">';
			foreach($arFields as $name=>$field){
				$html .= '<tr>';
					if($field['TYPE'] == 'simpletext'){
						$html .= '<td class="td-title" conspan="2"><div class="agcg-element-form-title">'.$field['NAME'].'</div></td>';
					}else{
						$html .= '<td class="td-name">'.$field['NAME'].'</td>';
						$html .= '<td>';
							switch($field['TYPE']){
								case 'textarea';
									$html .= '<textarea name="'.$name.'" class="'.$field['CLASS'].'">'.($field['VALUE'] ?  : $field['DEFAULT']).'</textarea>';
									if($field['HINT_BUTTON']){
										$html .= '<div class="append-button-wrap js-open-append">';
											$html .= '<div class="append-button">'.Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_BTN_APPEND').'</div>';
											$html .= '<div class="append-list">';
												foreach($field['HINT_BUTTON_VARIANTS'] as $group){
													$html .= '<div class="append-list-group-title">'.$group['NAME'].'</div>';
														
													$html .= '<div class="append-list-group">';
														foreach($group['ITEMS'] as $key=>$name){
															$html .= '<div class="append-list-variant js-add-append" data-value="#'.$key.'#">'.$name.'</div>';
														}
													$html .= '</div>';
												}
											$html .= '</div>';
										$html .= '</div>';
									}
								break;
								
								case 'text';
									$html .= '<input name="'.$name.'" class="'.$field['CLASS'].'" type="text" value="'.($field['VALUE'] ?  : $field['DEFAULT']).'" />';
								break;
								
								case 'checkbox';
									$html .= '<input name="'.$name.'" type="hidden" value="N">';
									$html .= '<input name="'.$name.'" class="'.$field['CLASS'].'" type="checkbox" '.($field['VALUE'] == 'Y' ? 'checked' : '').' value="Y">';
								break;
								
								case 'select';
									$html .= '<select name="'.$name.'" class="'.$field['CLASS'].'">';
										if($field['VALUES_GROUPS']){
											foreach($field['VALUES'] as $group){
												if($group['NAME']){
													$html .= '<optgroup label="'.$group['NAME'].'">';
												}
													foreach($group['ITEMS'] as $value=>$valueName){
														$html .= '<option '.($field['VALUE'] == $value ? 'selected' : '').' value="'.$value.'">'.$valueName.'</option>';
													}
												if($group['NAME']){
													$html .= '</optgroup>';
												}
											}
										}elseif($name == 'saved_template'){
											foreach($field['VALUES'] as $value=>$valueName){
												$html .= '<option value="'.htmlspecialchars($value).'">'.$valueName.'</option>';
											}
										}else{
											foreach($field['VALUES'] as $value=>$valueName){
												$html .= '<option '.($field['VALUE'] == $value ? 'selected' : '').' value="'.$value.'">'.$valueName.'</option>';
											}
										}
									$html .= '</select>';
								break;
							}
						$html .= '</td>';
					}
				$html .= '</tr>';
			}
		$html .= '</table>';
		
		return $html;
	}
	
	// elements
	static function getElementFieldsToSave($IBLOCK_ID, $operation, $type){
		$result = [
			'fields' => [],
			'properties' => [],
			'seo' => [],
		];
		
		Loader::includeModule('iblock');
		
		if($operation == 'IMAGE'){
			$result['fields'] = [
				[
					'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_PREVIEW_PICTURE'),
					'CODE' => 'PREVIEW_PICTURE',
				],
				[
					'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_DETAIL_PICTURE'),
					'CODE' => 'DETAIL_PICTURE',
				]
			];
		}else{
			$result['fields'] = [
				[
					'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_NAME'),
					'CODE' => 'NAME',
				],
				[
					'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_PREVIEW_TEXT'),
					'CODE' => 'PREVIEW_TEXT',
				],
				[
					'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_DETAIL_TEXT'),
					'CODE' => 'DETAIL_TEXT',
				],
			];

			$arProps = self::_getElementFromProperties($IBLOCK_ID);
			foreach($arProps as $code=>$name){
				$result['properties'][] = [
					'NAME' => $name,
					'CODE' => $code,
				];
			}
			
			if(Loader::includeModule('seo')){
				$result['seo'] = [
					[
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_SEO_ELEMENT_PAGE_TITLE'),
						'CODE' => 'SEO_ELEMENT_PAGE_TITLE',
					],
					[
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_SEO_ELEMENT_META_TITLE'),
						'CODE' => 'SEO_ELEMENT_META_TITLE',
					],
					[
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_SEO_ELEMENT_META_DESCRIPTION'),
						'CODE' => 'SEO_ELEMENT_META_DESCRIPTION',
					],
					[
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_SEO_ELEMENT_META_KEYWORDS'),
						'CODE' => 'SEO_ELEMENT_META_KEYWORDS',
					],
					[
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_SEO_ELEMENT_PREVIEW_PICTURE_FILE_ALT'),
						'CODE' => 'SEO_ELEMENT_PREVIEW_PICTURE_FILE_ALT',
					],
					[
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_SEO_ELEMENT_PREVIEW_PICTURE_FILE_TITLE'),
						'CODE' => 'SEO_ELEMENT_PREVIEW_PICTURE_FILE_TITLE',
					],
					[
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_SEO_ELEMENT_DETAIL_PICTURE_FILE_ALT'),
						'CODE' => 'SEO_ELEMENT_DETAIL_PICTURE_FILE_ALT',
					],
					[
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_SEO_ELEMENT_DETAIL_PICTURE_FILE_TITLE'),
						'CODE' => 'SEO_ELEMENT_DETAIL_PICTURE_FILE_TITLE',
					],
				];
			}
		}
		
		return $result;
	}
	
	static function makeElementFormHtml($postFields){
		Loader::includeModule('iblock');
		
		$postFields = \CArturgolubevChatgpt::applyDefaultVals($postFields);
		
		$arFields = [
			'provider' => [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_PROVIDER'),
				'TYPE' => 'select',
				'CLASS' => 'js-renew-form',
				'VALUES_GROUPS' => 0,
				'VALUES' => []
			],
			'operation' => [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_OPERATION'),
				'TYPE' => 'select',
				'CLASS' => 'js-renew-form',
				'VALUES_GROUPS' => 0,
				'VALUES' => [
					'CREATE' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_OPERATION_CREATE'),
					'REWRITE' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_OPERATION_REWRITE'),
					'TRANSLATE' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_OPERATION_TRANSLATE'),
					'TEMPLATE' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_OPERATION_TEMPLATE'),
				]
			],
			'type' => [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TYPE'),
				'TYPE' => 'select',
				'CLASS' => 'js-renew-form js-required',
				'VALUES_GROUPS' => 0,
				'VALUES' => []
			],
			'for' => [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FOR'),
				'TYPE' => 'select',
				'CLASS' => '',
				'VALUES_GROUPS' => 0,
				'VALUES' => [
					'PRODUCT' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FOR_PRODUCT'),
					'ARTICLE' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FOR_ARTICLE'),
					'SERVICE' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FOR_SERVICE'),
				]
			],
			'from' => [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FROM'),
				'TYPE' => 'select',
				'CLASS' => '',
				'VALUES_GROUPS' => 1,
				'VALUES' => []
			]
		];

		if(1){ // provider
			$gptKey = UTools::getSetting('api_key');
			$sberKey = UTools::getSetting('sber_authorization');

			if($gptKey || (!$gptKey && !$sberKey)){
				$arFields['provider']['VALUES']['chatgpt'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_PROVIDER_CHATGTP');
			}

			if($sberKey){
				$arFields['provider']['VALUES']['sber'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_PROVIDER_SBER');
			}
		}
		
		if($postFields['provider'] != 'sber'){
			$arFields['operation']['VALUES']['IMAGE'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_OPERATION_IMAGE_GENERATION');
		}

		if($postFields['operation'] == 'REWRITE'){
			// type
			$arFields['type']['VALUES'] = [
				'TEXT' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TYPE_TEXT'),
			];
			
			// values
			$arFields['from']['VALUES'] = self::_getElementFrom($postFields['IBLOCK_ID'], $postFields['operation']);
			
			if(!$postFields['type'] || !isset($arFields['type']['VALUES'][$postFields['type']])){
				$keys = array_keys($arFields['type']['VALUES']);
				$postFields['type'] = $keys[0];
			}
			
			switch($postFields['type']){
				case 'TEXT';
					$arFields['paragraph'] = [
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_PARAGRAPH'),
						'TYPE' => 'checkbox',
						'CLASS' => '',
						'DEFAULT' => 'Y',
						'VALUE' => '',
					];
					$arFields['html'] = [
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_HTML'),
						'TYPE' => 'checkbox',
						'CLASS' => '',
						'DEFAULT' => 'Y',
						'VALUE' => '',
					];
				break;
			}
		}elseif($postFields['operation'] == 'TRANSLATE'){
			unset($arFields['type']);
			unset($arFields['for']);
			
			$arFields['from']['NAME'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FROM_TRANSLATE');
			
			if($postFields['IBLOCK_ID']){
				$arFields['from']['VALUES'] = self::_getElementFrom($postFields['IBLOCK_ID'], $postFields['operation']);
				
				$arFields['lang'] = [
					'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_TRANSLATE_LANG'),
					'TYPE' => 'select',
					'CLASS' => '',
					'VALUES_GROUPS' => 0,
					'VALUES' => [
						Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_TRANSLATE_LANG_RU') => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_TRANSLATE_LANG_RU'),
						Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_TRANSLATE_LANG_BY') => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_TRANSLATE_LANG_BY'),
						Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_TRANSLATE_LANG_UA') => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_TRANSLATE_LANG_UA'),
						Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_TRANSLATE_LANG_KZ') => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_TRANSLATE_LANG_KZ'),
					]
				];
			}
		}elseif($postFields['operation'] == 'IMAGE'){
			$postFields['type'] = '';
			
			unset($arFields['type']); unset($arFields['for']); unset($arFields['from']);
			
			$rsEnum = \CUserFieldEnum::GetList(array(), array(
				'USER_FIELD_NAME' => 'UF_TYPE',
				'XML_ID' => 'elements'
			));
			if($arEnum = $rsEnum->Fetch()){
				$savedTemplates = Tools::getSavedTemplates([
					'UF_TYPE' => $arEnum['ID']
				]);
			}

			if(is_array($savedTemplates) && count($savedTemplates)){
				$arFields['saved_template'] = [
					'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_SAVED_TEMPLATE'),
					'TYPE' => 'select',
					'CLASS' => 'js-saved-template',
					'VALUES_GROUPS' => 0,
					'VALUES' => []
				];

				$arFields['saved_template']['VALUES'][0] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_SAVED_TEMPLATE_EMPTY_SELECT');

				foreach($savedTemplates as $template){
					$arFields['saved_template']['VALUES'][$template['UF_TEMPLATE']] = $template['UF_NAME'];
				}
			}
			
			$arFields['size'] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_IMAGE_SIZE'),
				'TYPE' => 'select',
				'CLASS' => '',
				'VALUES_GROUPS' => 0,
				'VALUES' => [
					'256x256' => '256x256',
					'512x512' => '512x512',
					'1024x1024' => '1024x1024',
					'1024x1792' => '1024x1792',
					'1792x1024' => '1792x1024'
				]
			];

			if(UTools::getSetting('alg_image_model') == 'dall-e-3'){
				unset($arFields['size']['VALUES']['256x256']);
				unset($arFields['size']['VALUES']['512x512']);
			}

			$arFields['template_image'] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_IMAGE'),
				'TYPE' => 'textarea',
				'CLASS' => '',
				'DEFAULT' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_IMAGE_DEFAULT'),
				'VALUE' => '',
				'HINT_BUTTON' => 1,
				'HINT_BUTTON_VARIANTS' => [],
			];
			
			$arProps = self::_getElementFromProperties($postFields['IBLOCK_ID']);
				
			$arFields['template_image']['HINT_BUTTON_VARIANTS'][] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_GROUP_FIELDS'),
				'ITEMS' => [
					'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_NAME'),
					'PREVIEW_TEXT' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_PREVIEW_TEXT'),
					'DETAIL_TEXT' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_DETAIL_TEXT'),
					'PARENT_SECTION_NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_PARENT_SECTION_NAME'),
					'PREVIEW_PICTURE' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_PREVIEW_PICTURE'),
					'DETAIL_PICTURE' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_DETAIL_PICTURE'),
				]
			];
			
			if(count($arProps)){
				$arFields['template_image']['HINT_BUTTON_VARIANTS'][] = [
					'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_GROUP_PROPERTIES'),
					'ITEMS' => $arProps,
				];
			}
		}elseif($postFields['operation'] == 'TEMPLATE'){
			$postFields['type'] = '';
			
			unset($arFields['type']);
			unset($arFields['for']);
			unset($arFields['from']);
			
			$rsEnum = \CUserFieldEnum::GetList(array(), array(
				'USER_FIELD_NAME' => 'UF_TYPE',
				'XML_ID' => 'elements'
			));
			if($arEnum = $rsEnum->Fetch()){
				$savedTemplates = Tools::getSavedTemplates([
					'UF_TYPE' => $arEnum['ID']
				]);
			}

			if(is_array($savedTemplates) && count($savedTemplates)){
				$arFields['saved_template'] = [
					'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_SAVED_TEMPLATE'),
					'TYPE' => 'select',
					'CLASS' => 'js-saved-template',
					'VALUES_GROUPS' => 0,
					'VALUES' => []
				];

				$arFields['saved_template']['VALUES'][0] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_SAVED_TEMPLATE_EMPTY_SELECT');

				foreach($savedTemplates as $template){
					$arFields['saved_template']['VALUES'][$template['UF_TEMPLATE']] = $template['UF_NAME'];
				}
			}
			
			$arFields['template_element'] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE'),
				'TYPE' => 'textarea',
				'CLASS' => '',
				'DEFAULT' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_DEFAULT'),
				'VALUE' => '',
				'HINT_BUTTON' => 1,
				'HINT_BUTTON_VARIANTS' => [],
			];
			
			$arProps = self::_getElementFromProperties($postFields['IBLOCK_ID']);
				
			$arFields['template_element']['HINT_BUTTON_VARIANTS'][] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_GROUP_FIELDS'),
				'ITEMS' => [
					'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_NAME'),
					'PREVIEW_TEXT' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_PREVIEW_TEXT'),
					'DETAIL_TEXT' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_DETAIL_TEXT'),
					'PARENT_SECTION_NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_PARENT_SECTION_NAME'),
					'PREVIEW_PICTURE' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_PREVIEW_PICTURE'),
					'DETAIL_PICTURE' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_DETAIL_PICTURE'),
				]
			];
			
			if(count($arProps)){
				$arFields['template_element']['HINT_BUTTON_VARIANTS'][] = [
					'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_GROUP_PROPERTIES'),
					'ITEMS' => $arProps,
				];
			}
			
		}else{
			// type
			$arFields['type']['VALUES'] = [
				'H1' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TYPE_H1'),
				'TITLE' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TYPE_TITLE'),
				'DESCRIPTION' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TYPE_DESCRIPTION'),
				'KEYWORDS' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TYPE_KEYWORDS'),
				'TEXT' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TYPE_TEXT'),
				'REVIEW' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TYPE_REVIEW'),
			];
			
			if(!$postFields['type'] || !isset($arFields['type']['VALUES'][$postFields['type']])){
				$keys = array_keys($arFields['type']['VALUES']);
				$postFields['type'] = $keys[0];
			}
			
			switch($postFields['type']){
				case 'TEXT';
					$arFields['length'] = [
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_LENGTH'),
						'TYPE' => 'text',
						'CLASS' => '',
						'DEFAULT' => UTools::getSetting('default_max_length'),
						'VALUE' => '',
					];
					$arFields['paragraph'] = [
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_PARAGRAPH'),
						'TYPE' => 'checkbox',
						'CLASS' => '',
						'VALUE' => '',
						'DEFAULT' => 'Y',
					];
					$arFields['html'] = [
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_HTML'),
						'TYPE' => 'checkbox',
						'CLASS' => '',
						'VALUE' => '',
						'DEFAULT' => 'Y',
					];
				break;
			}
		
			$arFields['from']['VALUES'] = self::_getElementFrom($postFields['IBLOCK_ID'], $postFields['operation']);
		}
		
		if(!in_array($postFields['operation'], ['TEMPLATE', 'IMAGE'])){
			$arFields['additionals'] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_ADDITIONALS'),
				'TYPE' => 'textarea',
				'CLASS' => '',
				'DEFAULT' => '',
				'VALUE' => '',
			];
		}

		if($postFields['MASS'] == 'Y'){
			$arFields['T1'] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_MASS_CREATE_TITLE_SAVE_FIELD'),
				'TYPE' => 'simpletext',
				'CLASS' => '',
			];
			
			$arFields['mass_save_field'] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_MASS_CREATE_SAVE_FIELD'),
				'TYPE' => 'select',
				'CLASS' => '',
				'VALUES_GROUPS' => 1,
				'VALUES' => []
			];
			
			if($postFields['IBLOCK_ID']){
				$saveFields = self::getElementFieldsToSave($postFields['IBLOCK_ID'], $postFields['operation'], $postFields['type']);
								
				foreach($saveFields as $groupKey=>$items){
					$selectItems = [];
					foreach($items as $item){
						$selectItems[$item['CODE']] = $item['NAME'];
					}
					
					if(count($selectItems)){
						$arFields['mass_save_field']['VALUES'][] = [
							'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_GROUP_'.$groupKey),
							'ITEMS' => $selectItems
						];
					}
				}
			}
			
			$arFields['save_only_empty'] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_MASS_CREATE_SAVE_ONLY_EMPTY'),
				'TYPE' => 'checkbox',
				'CLASS' => '',
				'DEFAULT' => 'Y',
				'VALUE' => '',
			];
		}
		
		foreach($arFields as $name=>$field){
			if($postFields[$name]){
				if(!$arFields[$name]['VALUES_GROUPS'] && $arFields[$name]['TYPE'] == 'select'){
					if($arFields[$name]['VALUES'][$postFields[$name]]){
						$arFields[$name]['VALUE'] = $postFields[$name];
					}
				}else{
					$arFields[$name]['VALUE'] = $postFields[$name];
				}
			}
		}
		
		// echo '<pre>'; print_r($arFields); echo '</pre>';
		// echo '<pre>'; print_r($mainType); echo '</pre>';
		// echo '<pre>'; print_r($postFields); echo '</pre>';
		
		return self::_makeHtmlForJsForm($arFields);
	}
		static function _getElementFrom($iblockID, $operation){
			$result = [];

			$baseFields = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FROM_NAME'),
				'PREVIEW_TEXT' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FROM_PREVIEW_TEXT'),
				'DETAIL_TEXT' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FROM_DETAIL_TEXT'),
			];

			if($operation != 'REWRITE' && $operation != 'TRANSLATE'){
				$baseFields['DETAIL_PAGE_URL'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FROM_DETAIL_PAGE_URL');
			}

			$result[] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_GROUP_FIELDS'),
				'ITEMS' => $baseFields
			];

			if($iblockID){
				$arProps = self::_getElementFromProperties($iblockID);
				
				if(count($arProps)){
					$result[] = [
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_GROUP_PROPERTIES'),
						'ITEMS' => $arProps,
					];
				}
			}

			return $result;
		}

		static function _getElementFromProperties($iblockID){
			$arProps = [];
				
			$properties = \CIBlockProperty::GetList(Array("NAME"=>"ASC", "SORT"=>"ASC"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$iblockID, '!CODE' => false));
			while ($prop_fields = $properties->GetNext()){
				$skip = 0;

				if(!in_array($prop_fields['PROPERTY_TYPE'], ['F', 'S', 'N', 'L', 'E'])){
					$skip = 1;
				}

				if($prop_fields['USER_TYPE'] && !in_array($prop_fields['USER_TYPE'], ['HTML', 'directory'])){
					$skip = 1;
				}

				if($skip){
					// echo '<pre>'; print_r($prop_fields); echo '</pre>';
					continue;
				}
				
				// $prop_fields['NAME'] .= ' [type='.$prop_fields['PROPERTY_TYPE'].'; user_type='.$prop_fields['USER_TYPE'].']';

				$arProps['PROPERTY_'.$prop_fields['CODE']] = $prop_fields['NAME'];
			}
			
			return $arProps;
		}
		
		static function _getSectionFrom($iblockID, $operation){
			$result = [];

			$baseFields = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FROM_SECTION_NAME'),
				'DESCRIPTION' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FROM_SECTION_DESCRIPTION'),
			];

			if($operation != 'REWRITE'){
				$baseFields['SECTION_PAGE_URL'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FROM_SECTION_PAGE_URL');
			}

			$result[] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_SECTION_GROUP_FIELDS'),
				'ITEMS' => $baseFields
			];
			
			if($iblockID){
				$arProps = self::_getSectionFormProperties($iblockID);
				
				if(count($arProps)){
					$result[] = [
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_SECTION_GROUP_PROPERTIES'),
						'ITEMS' => $arProps,
					];
				}
			}

			return $result;
		}
		static function _getSectionFormProperties($iblockID){
			$arProps = [];
			
			$rsData = \CUserTypeEntity::GetList([$by=>$order], ['ENTITY_ID' => 'IBLOCK_'.$iblockID.'_SECTION', 'USER_TYPE_ID' => 'string', 'LANG' => 'ru']);
			while($arRes = $rsData->Fetch()){
				$arProps[$arRes['FIELD_NAME']] = ($arRes['EDIT_FORM_LABEL'] ? $arRes['EDIT_FORM_LABEL'] : $arRes['FIELD_NAME']);
			}
			
			return $arProps;
		}
	
	// sections
	static function getSectionFieldsToSave($IBLOCK_ID, $operation, $type){
		$result = [
			'section_fields' => [],
			'section_uf' => [],
			'seo' => [],
		];
		
		Loader::includeModule('iblock');

		if($operation == 'IMAGE'){
			$result['fields'] = [
				[
					'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_SECTION_PICTURE'),
					'CODE' => 'PICTURE',
				]
			];
		}else{
			$result['section_fields'] = [
				[
					'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_SECTION_NAME'),
					'CODE' => 'NAME',
				],
				[
					'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_DESCRIPTION'),
					'CODE' => 'DESCRIPTION',
				],
			];
		
			$rsData = \CUserTypeEntity::GetList([$by=>$order], ['ENTITY_ID' => 'IBLOCK_'.$IBLOCK_ID.'_SECTION', 'USER_TYPE_ID' => 'string', 'LANG' => 'ru']);
			while($arRes = $rsData->Fetch()){
				$result['section_uf'][] = [
					'NAME' => ($arRes['EDIT_FORM_LABEL'] ? $arRes['EDIT_FORM_LABEL'] : $arRes['FIELD_NAME']),
					'CODE' => $arRes['FIELD_NAME']
				];
			}
			
			if(Loader::includeModule('seo')){
				$result['seo'] = [
					[
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_SEO_SECTION_PAGE_TITLE'),
						'CODE' => 'SEO_SECTION_PAGE_TITLE',
					],
					[
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_SEO_SECTION_META_TITLE'),
						'CODE' => 'SEO_SECTION_META_TITLE',
					],
					[
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_SEO_SECTION_META_DESCRIPTION'),
						'CODE' => 'SEO_SECTION_META_DESCRIPTION',
					],
					[
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_SEO_SECTION_META_KEYWORDS'),
						'CODE' => 'SEO_SECTION_META_KEYWORDS',
					],
					[
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_SEO_SECTION_PICTURE_FILE_ALT'),
						'CODE' => 'SEO_SECTION_PICTURE_FILE_ALT',
					],
					[
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_SEO_SECTION_PICTURE_FILE_TITLE'),
						'CODE' => 'SEO_SECTION_PICTURE_FILE_TITLE',
					],
					[
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_SEO_SECTION_DETAIL_PICTURE_FILE_ALT'),
						'CODE' => 'SEO_SECTION_DETAIL_PICTURE_FILE_ALT',
					],
					[
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_SAVETO_SEO_SECTION_DETAIL_PICTURE_FILE_TITLE'),
						'CODE' => 'SEO_SECTION_DETAIL_PICTURE_FILE_TITLE',
					],
				];
			}
		}
		
		return $result;
	}
	
	static function makeSectionFormHtml($postFields){
		Loader::includeModule('iblock');
		
		$postFields = \CArturgolubevChatgpt::applyDefaultVals($postFields);
		
		$arFields = [
			'provider' => [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_PROVIDER'),
				'TYPE' => 'select',
				'CLASS' => 'js-renew-form',
				'VALUES_GROUPS' => 0,
				'VALUES' => []
			],
			'operation' => [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_OPERATION'),
				'TYPE' => 'select',
				'CLASS' => 'js-renew-form',
				'VALUES_GROUPS' => 0,
				'VALUES' => [
					'CREATE' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_OPERATION_CREATE'),
					'REWRITE' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_OPERATION_REWRITE'),
					'TEMPLATE' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_OPERATION_TEMPLATE'),
				]
			],
			'type' => [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TYPE'),
				'TYPE' => 'select',
				'CLASS' => 'js-renew-form js-required',
				'VALUES_GROUPS' => 0,
				'VALUES' => []
			],
			'for' => [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FOR'),
				'TYPE' => 'select',
				'CLASS' => '',
				'VALUES_GROUPS' => 0,
				'VALUES' => [
					'PRODUCT_SECTION' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FOR_PRODUCT_SECTION'),
				]
			],
			'from' => [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FROM'),
				'TYPE' => 'select',
				'CLASS' => '',
				'VALUES_GROUPS' => 1,
				'VALUES' => []
			]
		];

		if(1){ // provider
			$gptKey = UTools::getSetting('api_key');
			$sberKey = UTools::getSetting('sber_authorization');

			if($gptKey || (!$gptKey && !$sberKey)){
				$arFields['provider']['VALUES']['chatgpt'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_PROVIDER_CHATGTP');
			}

			if($sberKey){
				$arFields['provider']['VALUES']['sber'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_PROVIDER_SBER');
			}
		}
		
		if($postFields['provider'] != 'sber'){
			$arFields['operation']['VALUES']['IMAGE'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_OPERATION_IMAGE_GENERATION');
		}

		if($postFields['operation'] == 'REWRITE'){
			// type
			$arFields['type']['VALUES'] = [
				'TEXT' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TYPE_TEXT'),
			];
			
			// values
			$arFields['from']['VALUES'] = self::_getSectionFrom($postFields['IBLOCK_ID'], $postFields['operation']);
			
			if(!$postFields['type'] || !isset($arFields['type']['VALUES'][$postFields['type']])){
				$keys = array_keys($arFields['type']['VALUES']);
				$postFields['type'] = $keys[0];
			}
			
			switch($postFields['type']){
				case 'TEXT';
					$arFields['paragraph'] = [
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_PARAGRAPH'),
						'TYPE' => 'checkbox',
						'CLASS' => '',
						'DEFAULT' => 'Y',
						'VALUE' => '',
					];
					$arFields['html'] = [
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_HTML'),
						'TYPE' => 'checkbox',
						'CLASS' => '',
						'DEFAULT' => 'Y',
						'VALUE' => '',
					];
				break;
			}
		}elseif($postFields['operation'] == 'IMAGE'){
			$postFields['type'] = '';

			unset($arFields['type']); unset($arFields['for']); unset($arFields['from']);
			
			$rsEnum = \CUserFieldEnum::GetList(array(), array(
				'USER_FIELD_NAME' => 'UF_TYPE',
				'XML_ID' => 'sections'
			));
			if($arEnum = $rsEnum->Fetch()){
				$savedTemplates = Tools::getSavedTemplates([
					'UF_TYPE' => $arEnum['ID']
				]);
			}

			if(is_array($savedTemplates) && count($savedTemplates)){
				$arFields['saved_template'] = [
					'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_SAVED_TEMPLATE'),
					'TYPE' => 'select',
					'CLASS' => 'js-saved-template',
					'VALUES_GROUPS' => 0,
					'VALUES' => []
				];

				$arFields['saved_template']['VALUES'][0] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_SAVED_TEMPLATE_EMPTY_SELECT');

				foreach($savedTemplates as $template){
					$arFields['saved_template']['VALUES'][$template['UF_TEMPLATE']] = $template['UF_NAME'];
				}
			}

			$arFields['size'] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_IMAGE_SIZE'),
				'TYPE' => 'select',
				'CLASS' => '',
				'VALUES_GROUPS' => 0,
				'VALUES' => [
					'256x256' => '256x256',
					'512x512' => '512x512',
					'1024x1024' => '1024x1024',
					'1024x1792' => '1024x1792',
					'1792x1024' => '1792x1024'
				]
			];
			
			if(UTools::getSetting('alg_image_model') == 'dall-e-3'){
				unset($arFields['size']['VALUES']['256x256']);
				unset($arFields['size']['VALUES']['512x512']);
			}
			
			$arFields['template_image'] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_IMAGE'),
				'TYPE' => 'textarea',
				'CLASS' => '',
				'DEFAULT' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_IMAGE_DEFAULT'),
				'VALUE' => '',
				'HINT_BUTTON' => 1,
				'HINT_BUTTON_VARIANTS' => [],
			];
				
			$arFields['template_image']['HINT_BUTTON_VARIANTS'][] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_SECTION_GROUP_FIELDS'),
				'ITEMS' => [
					'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FROM_SECTION_NAME'),
					'DESCRIPTION' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FROM_SECTION_DESCRIPTION'),
					'PICTURE' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FROM_SECTION_PICTURE'),
				]
			];
			
			$arProps = self::_getSectionFormProperties($postFields['IBLOCK_ID']);
			if(count($arProps)){
				$arFields['template_image']['HINT_BUTTON_VARIANTS'][] = [
					'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_SECTION_GROUP_PROPERTIES'),
					'ITEMS' => $arProps,
				];
			}
		}elseif($postFields['operation'] == 'TEMPLATE'){
			$postFields['type'] = '';

			unset($arFields['type']);
			unset($arFields['for']);
			unset($arFields['from']);
			
			$rsEnum = \CUserFieldEnum::GetList(array(), array(
				'USER_FIELD_NAME' => 'UF_TYPE',
				'XML_ID' => 'sections'
			));
			if($arEnum = $rsEnum->Fetch()){
				$savedTemplates = Tools::getSavedTemplates([
					'UF_TYPE' => $arEnum['ID']
				]);
			}

			if(is_array($savedTemplates) && count($savedTemplates)){
				$arFields['saved_template'] = [
					'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_SAVED_TEMPLATE'),
					'TYPE' => 'select',
					'CLASS' => 'js-saved-template',
					'VALUES_GROUPS' => 0,
					'VALUES' => []
				];

				$arFields['saved_template']['VALUES'][0] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_SAVED_TEMPLATE_EMPTY_SELECT');

				foreach($savedTemplates as $template){
					$arFields['saved_template']['VALUES'][$template['UF_TEMPLATE']] = $template['UF_NAME'];
				}
			}
			
			$arFields['template_section'] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE'),
				'TYPE' => 'textarea',
				'CLASS' => '',
				'DEFAULT' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_DEFAULT'),
				'VALUE' => '',
				'HINT_BUTTON' => 1,
				'HINT_BUTTON_VARIANTS' => [],
			];
			
				
			$arFields['template_section']['HINT_BUTTON_VARIANTS'][] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_SECTION_GROUP_FIELDS'),
				'ITEMS' => [
					'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FROM_SECTION_NAME'),
					'DESCRIPTION' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FROM_SECTION_DESCRIPTION'),
					'PICTURE' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FROM_SECTION_PICTURE'),
				]
			];
			
			$arProps = self::_getSectionFormProperties($postFields['IBLOCK_ID']);
			if(count($arProps)){
				$arFields['template_section']['HINT_BUTTON_VARIANTS'][] = [
					'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_SECTION_GROUP_PROPERTIES'),
					'ITEMS' => $arProps,
				];
			}
		}else{
			// type
			$arFields['type']['VALUES'] = [
				'H1' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TYPE_H1'),
				'TITLE' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TYPE_TITLE'),
				'DESCRIPTION' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TYPE_DESCRIPTION'),
				'KEYWORDS' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TYPE_KEYWORDS'),
				'TEXT' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TYPE_TEXT'),
			];
			
			if(!$postFields['type'] || !isset($arFields['type']['VALUES'][$postFields['type']])){
				$keys = array_keys($arFields['type']['VALUES']);
				$postFields['type'] = $keys[0];
			}
			
			switch($postFields['type']){
				case 'TEXT';
					$arFields['length'] = [
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_LENGTH'),
						'TYPE' => 'text',
						'CLASS' => '',
						'DEFAULT' => UTools::getSetting('default_max_length'),
						'VALUE' => '',
					];
					$arFields['paragraph'] = [
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_PARAGRAPH'),
						'TYPE' => 'checkbox',
						'CLASS' => '',
						'DEFAULT' => 'Y',
						'VALUE' => '',
					];
					$arFields['html'] = [
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_HTML'),
						'TYPE' => 'checkbox',
						'CLASS' => '',
						'DEFAULT' => 'Y',
						'VALUE' => '',
					];
				break;
			}
		
			// values
			$arFields['from']['VALUES'] = self::_getSectionFrom($postFields['IBLOCK_ID'], $postFields['operation']);
		}

		if(!in_array($postFields['operation'], ['TEMPLATE', 'IMAGE'])){
			$arFields['additionals'] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_ADDITIONALS'),
				'TYPE' => 'textarea',
				'CLASS' => '',
				'DEFAULT' => '',
				'VALUE' => '',
			];
		}
		
		if($postFields['MASS'] == 'Y'){
			$arFields['T1'] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_MASS_CREATE_TITLE_SAVE_FIELD'),
				'TYPE' => 'simpletext',
				'CLASS' => '',
			];

			$arFields['mass_save_field'] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_MASS_CREATE_SAVE_FIELD'),
				'TYPE' => 'select',
				'CLASS' => '',
				'VALUES_GROUPS' => 1,
				'VALUES' => []
			];
			
			if($postFields['IBLOCK_ID']){
				$saveFields = self::getSectionFieldsToSave($postFields['IBLOCK_ID'], $postFields['operation'], $postFields['type']);
				
				foreach($saveFields as $groupKey=>$items){
					$selectItems = [];
					foreach($items as $item){
						$selectItems[$item['CODE']] = $item['NAME'];
					}
					
					if(count($selectItems)){
						$arFields['mass_save_field']['VALUES'][] = [
							'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_GROUP_'.$groupKey),
							'ITEMS' => $selectItems
						];
					}
				}
			}
			
			$arFields['save_only_empty'] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_MASS_CREATE_SAVE_ONLY_EMPTY'),
				'TYPE' => 'checkbox',
				'CLASS' => '',
				'DEFAULT' => 'Y',
				'VALUE' => '',
			];
		}
		
		foreach($arFields as $name=>$field){
			if($postFields[$name]){
				if(!$arFields[$name]['VALUES_GROUPS'] && $arFields[$name]['TYPE'] == 'select'){
					if($arFields[$name]['VALUES'][$postFields[$name]]){
						$arFields[$name]['VALUE'] = $postFields[$name];
					}
				}else{
					$arFields[$name]['VALUE'] = $postFields[$name];
				}
			}
		}
		
		// echo '<pre>'; print_r($arFields); echo '</pre>';
		// echo '<pre>'; print_r($mainType); echo '</pre>';
		// echo '<pre>'; print_r($postFields); echo '</pre>';
		
		return self::_makeHtmlForJsForm($arFields);
	}
}