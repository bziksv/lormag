<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if(!CModule::IncludeModule("highloadblock"))
	return;

if(!WIZARD_INSTALL_DEMO_DATA)
	return;

$COLOR_ID = $_SESSION["ENEXT_HBLOCK_COLOR_ID"];
unset($_SESSION["ENEXT_HBLOCK_COLOR_ID"]);

//adding rows
WizardServices::IncludeServiceLang("references.php", LANGUAGE_ID);

use Bitrix\Highloadblock as HL;
global $USER_FIELD_MANAGER;

if(intval($COLOR_ID) > 0) {
	$hldata = HL\HighloadBlockTable::getById($COLOR_ID)->fetch();
	if(is_array($hldata)) {
		$hlentity = HL\HighloadBlockTable::compileEntity($hldata);

		$entity_data_class = $hlentity->getDataClass();
		$arColors = array(
			'WHITE' => array('UF_FILE' => '', 'UF_CODE' => 'ffffff'),
			'BLUE' => array('UF_FILE' => '', 'UF_CODE' => '3292e4'),
			'BLACK' => array('UF_FILE' => '', 'UF_CODE' => '000000'),
			'PINK' => array('UF_FILE' => '', 'UF_CODE' => 'f736cc'),
			'LIGHT_CAMO' => array('UF_FILE' => 'references_files/uf/e79/e79b9cdc1acc5af2f9ddd97edd2203ee.png', 'UF_CODE' => ''),
			'CAMO' => array('UF_FILE' => 'references_files/uf/d4c/d4c0446e296cbf808533618cca0ccee4.png', 'UF_CODE' => ''),
			'ORANGE' => array('UF_FILE' => '', 'UF_CODE' => 'ff8a00'),
			'RED' => array('UF_FILE' => '', 'UF_CODE' => 'e71b1b'),
			'YELLOW' => array('UF_FILE' => '', 'UF_CODE' => 'fff600'),
			'GREEN' => array('UF_FILE' => '', 'UF_CODE' => '33b35a'),
			'GRAY' => array('UF_FILE' => '', 'UF_CODE' => 'b2b2b2'),
			'PURPLE' => array('UF_FILE' => '', 'UF_CODE' => 'cc00ff'),
			'BEIGE' => array('UF_FILE' => '', 'UF_CODE' => 'ddd096'),
			'BROWN' => array('UF_FILE' => '', 'UF_CODE' => '624128'),
		);	
		foreach($arColors as $colorName => $colorVal) {		
			$arData = array(
				'UF_NAME' => GetMessage("WZD_REF_COLOR_".$colorName),
				'UF_FILE' => !empty($colorVal['UF_FILE']) ?
					array (
						'name' => ToLower($colorName).".jpg",
						'type' => 'image/png',
						'tmp_name' => WIZARD_ABSOLUTE_PATH."/site/services/iblock/".$colorVal['UF_FILE']
					) : '',
				'UF_CODE' => !empty($colorVal['UF_CODE']) ? $colorVal['UF_CODE'] : '',			
				'UF_XML_ID' => ToLower($colorName)
			);
			$USER_FIELD_MANAGER->EditFormAddFields('HLBLOCK_'.$COLOR_ID, $arData);
			$USER_FIELD_MANAGER->checkFields('HLBLOCK_'.$COLOR_ID, null, $arData);

			$result = $entity_data_class::add($arData);
		}
	}
}?>