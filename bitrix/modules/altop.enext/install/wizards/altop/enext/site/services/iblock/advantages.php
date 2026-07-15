<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if(!CModule::IncludeModule("iblock"))
	return;

$iblockXMLFile = WIZARD_SERVICE_RELATIVE_PATH."/xml/".LANGUAGE_ID."/advantages.xml";
$iblockCode = "enext_advantages_".WIZARD_SITE_ID;
$iblockType = "references";

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

	$iblockID = WizardServices::ImportIBlockFromXML(
		$iblockXMLFile,
		$iblockCode,
		$iblockType,
		WIZARD_SITE_ID,
		$permissions
	);

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
					"WIDTH" => "",
					"HEIGHT" => "48",
					"IGNORE_ERRORS" => "N",
					"METHOD" => "resample",
					"COMPRESSION" => 95,
					"DELETE_WITH_DETAIL" => "N",
					"UPDATE_WITH_DETAIL" => "N"
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

//list user options
CUserOptions::SetOption("list", "tbl_iblock_list_".md5($iblockType.".".$iblockID), array(
	"columns" => "NAME, PREVIEW_PICTURE, ACTIVE, SORT, TIMESTAMP_X, ID",
	"by" => "timestamp_x",
	"order" => "desc",
	"page_size" => "20"
));?>