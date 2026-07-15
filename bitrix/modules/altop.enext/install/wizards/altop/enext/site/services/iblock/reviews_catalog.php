<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

$iblockXMLFile = WIZARD_SERVICE_RELATIVE_PATH."/xml/".LANGUAGE_ID."/reviews_catalog.xml";
$iblockCode = "enext_reviews_catalog_".WIZARD_SITE_ID;
$iblockType = "reviews";

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

	CWizardUtil::ReplaceMacros($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile, array("XML_SITE_ID" => WIZARD_SITE_ID));

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
		"XML_ID" => $iblockCode
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

//iblock user fields
$arProperty = array();
$dbProperty = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $iblockID));
while($arProp = $dbProperty->Fetch()) {
	$arProperty[$arProp["CODE"]] = $arProp["ID"];
}

//list user options
CUserOptions::SetOption("list", "tbl_iblock_list_".md5($iblockType.".".$iblockID), array(
	"columns" => "NAME, PROPERTY_".$arProperty["PRODUCT_ID"].", PROPERTY_".$arProperty["RATING"].", PROPERTY_".$arProperty["NAME"].", ACTIVE, DATE_ACTIVE_FROM, SORT, TIMESTAMP_X, ID",
	"by" => "timestamp_x",
	"order" => "desc",
	"page_size" => "20"
));

CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/articles/index.php", array("ENEXT_REVIEWS_CATALOG_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/brands/index.php", array("ENEXT_REVIEWS_CATALOG_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/catalog/index.php", array("ENEXT_REVIEWS_CATALOG_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/include/footer_bigdata.php", array("ENEXT_REVIEWS_CATALOG_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/include/hit.php", array("ENEXT_REVIEWS_CATALOG_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/include/new.php", array("ENEXT_REVIEWS_CATALOG_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/include/recommend.php", array("ENEXT_REVIEWS_CATALOG_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/news/index.php", array("ENEXT_REVIEWS_CATALOG_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/personal/cart/index.php", array("ENEXT_REVIEWS_CATALOG_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/places/index.php", array("ENEXT_REVIEWS_CATALOG_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/promotions/index.php", array("ENEXT_REVIEWS_CATALOG_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/services/index.php", array("ENEXT_REVIEWS_CATALOG_IBLOCK_ID" => $iblockID));?>