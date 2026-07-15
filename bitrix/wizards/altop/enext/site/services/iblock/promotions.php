<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

$iblockXMLFile = WIZARD_SERVICE_RELATIVE_PATH."/xml/".LANGUAGE_ID."/promotions.xml";
$iblockCode = "enext_promotions_".WIZARD_SITE_ID;
$iblockType = "content";

$rsIblock = CIBlock::GetList(array(), array("XML_ID" => $iblockCode, "TYPE" => $iblockType));
$iblockID = false; 

if($arIblock = $rsIblock->Fetch()) {
	$iblockID = $arIblock["ID"]; 
	if(WIZARD_INSTALL_DEMO_DATA) {
		CIBlock::Delete($arIblock["ID"]); 
		$iblockID = false; 
	}
}

if($iblockID == false) {

	$permissions = array(
		"1" => "X",
		"2" => "R"
	);

	if(file_exists($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile.".back"))
		copy($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile.".back", $_SERVER["DOCUMENT_ROOT"].$iblockXMLFile);
	
	copy($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile, $_SERVER["DOCUMENT_ROOT"].$iblockXMLFile.".back");
	
	CWizardUtil::ReplaceMacros($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile, array("XML_SITE_ID" => WIZARD_SITE_ID, "PROMOTIONS_ACTIVE" => date("Y-m-d H:i:s", time() + 86400 * 30), "PROMOTIONS_COMPLETED" => date("Y-m-d H:i:s", time() - 86400 * 30)));
	
	$iblockID = WizardServices::ImportIBlockFromXML(
		$iblockXMLFile,
		$iblockCode,
		$iblockType,
		WIZARD_SITE_ID,
		$permissions
	);

	if(file_exists($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile.".back"))
		copy($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile.".back", $_SERVER["DOCUMENT_ROOT"].$iblockXMLFile);
	
	if($iblockID < 1)
		return;

	//IBlock fields
	$iblock = new CIBlock;
	$arFields = array(
		"ACTIVE" => "Y",		
		"CODE" => $iblockCode, 
		"XML_ID" => $iblockCode,
		"FIELDS" => array(
			"PREVIEW_PICTURE" => array(
				"IS_REQUIRED" => "N",
				"DEFAULT_VALUE" => array(
					"FROM_DETAIL" => "N",					
					"SCALE" => "Y",
					"WIDTH" => "640",
					"HEIGHT" => "480",
					"IGNORE_ERRORS" => "N",
					"METHOD" => "resample",
					"COMPRESSION" => 95,
					"DELETE_WITH_DETAIL" => "N",
					"UPDATE_WITH_DETAIL" => "N"
				)
			),
			"DETAIL_PICTURE" => array(
				"IS_REQUIRED" => "N",
				"DEFAULT_VALUE" => array(
					"SCALE" => "Y",
					"WIDTH" => "1920",
					"HEIGHT" => "1080",
					"IGNORE_ERRORS" => "N",
					"METHOD" => "resample",
					"COMPRESSION" => 95,
				)
			),
			"CODE" => array(
				"IS_REQUIRED" => "Y",
				"DEFAULT_VALUE" => array(
					"UNIQUE" => "Y",
					"TRANSLITERATION" => "Y",
					"TRANS_LEN" => 100,
					"TRANS_CASE" => "L",
					"TRANS_SPACE" => "-",
					"TRANS_OTHER" => "-",
					"TRANS_EAT" => "Y",
					"USE_GOOGLE" => "N"
				)
			),
			"SECTION_PICTURE" => array(
				"IS_REQUIRED" => "N",
				"DEFAULT_VALUE" => array(
					"FROM_DETAIL" => "N",
					"SCALE" => "Y",
					"WIDTH" => "134",
					"HEIGHT" => "134",
					"IGNORE_ERRORS" => "N",
					"METHOD" => "resample",
					"COMPRESSION" => 95,
					"DELETE_WITH_DETAIL" => "N",
					"UPDATE_WITH_DETAIL" => "N",
				)
			),
			"SECTION_DETAIL_PICTURE" => array(
				"IS_REQUIRED" => "N",
				"DEFAULT_VALUE" => array(
					"SCALE" => "Y",
					"WIDTH" => "1920",
					"HEIGHT" => "1080",
					"IGNORE_ERRORS" => "N",
					"METHOD" => "resample",
					"COMPRESSION" => 95,
				)
			),
			"SECTION_CODE" => array(
				"IS_REQUIRED" => "Y",
				"DEFAULT_VALUE" => array(
					"UNIQUE" => "Y",
					"TRANSLITERATION" => "Y",
					"TRANS_LEN" => 100,
					"TRANS_CASE" => "L",
					"TRANS_SPACE" => "-",
					"TRANS_OTHER" => "-",
					"TRANS_EAT" => "Y",
					"USE_GOOGLE" => "N",
				)
			)
		)
	);
	$iblock->Update($iblockID, $arFields);

} else {
	
	$arSites = array(); 
	$db_res = CIBlock::GetSite($iblockID);
	while($res = $db_res->Fetch())
		$arSites[] = $res["LID"]; 
	if(!in_array(WIZARD_SITE_ID, $arSites)) {
		$arSites[] = WIZARD_SITE_ID;
		$iblock = new CIBlock;
		$iblock->Update($iblockID, array("LID" => $arSites));
	}

}

//user fields for sections	
$arLanguages = array();
$rsLanguage = CLanguage::GetList($by, $order, array());
while($arLanguage = $rsLanguage->Fetch())
	$arLanguages[] = $arLanguage["LID"];
	
$arUserFields = array("UF_ICON", "UF_BROWSER_TITLE", "UF_KEYWORDS", "UF_META_DESCRIPTION", "UF_SECTION_TITLE", "UF_BREADCRUMB_TITLE", "UF_BANNER", "UF_BANNER_URL", "UF_PREVIEW");
foreach($arUserFields as $userField) {
	$arLabelNames = Array();
	foreach($arLanguages as $languageID) {
		WizardServices::IncludeServiceLang("property_names.php", $languageID);
		$arLabelNames[$languageID] = $userField == "UF_ICON" ? GetMessage("UF_SECTION_ICON") : GetMessage($userField);
	}
	
	$arProperty["EDIT_FORM_LABEL"] = $arLabelNames;
	$arProperty["LIST_COLUMN_LABEL"] = $arLabelNames;
	$arProperty["LIST_FILTER_LABEL"] = $arLabelNames;
	
	$dbRes = CUserTypeEntity::GetList(array(), array("ENTITY_ID" => "IBLOCK_".$iblockID."_SECTION", "FIELD_NAME" => $userField));
	if($arRes = $dbRes->Fetch()) {
		$userType = new CUserTypeEntity();
		$userType->Update($arRes["ID"], $arProperty);
	}
}

//iblock user fields
$arProperty = array();
$dbProperty = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $iblockID));
while($arProp = $dbProperty->Fetch()) {
	$arProperty[$arProp["CODE"]] = $arProp["ID"];
}

//list user options
CUserOptions::SetOption("list", "tbl_iblock_list_".md5($iblockType.".".$iblockID), array(
	"columns" => "NAME, PREVIEW_PICTURE, DETAIL_PICTURE, PROPERTY_".$arProperty["MARKER"].", PROPERTY_".$arProperty["SHOW_TIMER"].", ACTIVE, SORT, TIMESTAMP_X, ID",
	"by" => "timestamp_x",
	"order" => "desc",
	"page_size" => "20"
));

CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/contacts/index.php", array("ENEXT_PROMOTIONS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/include/block_promotions.php", array("ENEXT_PROMOTIONS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/places/index.php", array("ENEXT_PROMOTIONS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/promotions/.topchild.menu_ext.php", array("ENEXT_PROMOTIONS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/promotions/index.php", array("ENEXT_PROMOTIONS_IBLOCK_ID" => $iblockID));?>