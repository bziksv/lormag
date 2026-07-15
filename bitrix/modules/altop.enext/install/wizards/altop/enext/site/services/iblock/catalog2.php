<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if(!CModule::IncludeModule("catalog"))
	return;

if(COption::GetOptionString("enext", "wizard_installed", "N", WIZARD_SITE_ID) == "Y" && !WIZARD_INSTALL_DEMO_DATA)
	return;

//offers iblock import
$iblockXMLFileOffers = WIZARD_SERVICE_RELATIVE_PATH."/xml/".LANGUAGE_ID."/offers.xml";
$iblockXMLFilePricesOffers = WIZARD_SERVICE_RELATIVE_PATH."/xml/".LANGUAGE_ID."/offers_prices.xml";

$iblockCodeOffers = "enext_offers_".WIZARD_SITE_ID;
$iblockTypeOffers = "catalog";

$rsIblock = CIBlock::GetList(array(), array("XML_ID" => $iblockCodeOffers, "TYPE" => $iblockTypeOffers));
$IBLOCK_OFFERS_ID = false;

if($arIblock = $rsIblock->Fetch()) {
	$IBLOCK_OFFERS_ID = $arIblock["ID"];
	if(WIZARD_INSTALL_DEMO_DATA) {
		CIBlock::Delete($arIblock["ID"]);
		$IBLOCK_OFFERS_ID = false;
	}
}

if($IBLOCK_OFFERS_ID == false) {	
	$permissions = array(
		"1" => "X",
		"2" => "R"
	);
	
	$dbGroup = CGroup::GetList($by = "", $order = "", array("STRING_ID" => "sale_administrator"));
	if($arGroup = $dbGroup -> Fetch()) {
		$permissions[$arGroup["ID"]] = "W";
	}
	$dbGroup = CGroup::GetList($by = "", $order = "", array("STRING_ID" => "content_editor"));
	if($arGroup = $dbGroup -> Fetch()) {
		$permissions[$arGroup["ID"]] = "W";
	}

	if(file_exists($_SERVER["DOCUMENT_ROOT"].$iblockXMLFileOffers.".back"))
		copy($_SERVER["DOCUMENT_ROOT"].$iblockXMLFileOffers.".back", $_SERVER["DOCUMENT_ROOT"].$iblockXMLFileOffers);
	
	copy($_SERVER["DOCUMENT_ROOT"].$iblockXMLFileOffers, $_SERVER["DOCUMENT_ROOT"].$iblockXMLFileOffers.".back");
		
	CWizardUtil::ReplaceMacros($_SERVER["DOCUMENT_ROOT"].$iblockXMLFileOffers, array("XML_SITE_ID" => WIZARD_SITE_ID));

	\Bitrix\Catalog\Product\Sku::disableUpdateAvailable();
	$IBLOCK_OFFERS_ID = WizardServices::ImportIBlockFromXML(
		$iblockXMLFileOffers,
		$iblockCodeOffers,
		$iblockTypeOffers,
		WIZARD_SITE_ID,
		$permissions
	);
	$iblockID1 = WizardServices::ImportIBlockFromXML(
		$iblockXMLFilePricesOffers,
		$iblockCodeOffers,
		$iblockTypeOffers."_prices",
		WIZARD_SITE_ID,
		$permissions
	);
	\Bitrix\Catalog\Product\Sku::enableUpdateAvailable();

	if(file_exists($_SERVER["DOCUMENT_ROOT"].$iblockXMLFileOffers.".back"))
		copy($_SERVER["DOCUMENT_ROOT"].$iblockXMLFileOffers.".back", $_SERVER["DOCUMENT_ROOT"].$iblockXMLFileOffers);

	if($IBLOCK_OFFERS_ID < 1)
		return;

	$_SESSION["WIZARD_OFFERS_IBLOCK_ID"] = $IBLOCK_OFFERS_ID;
} else {	
	$arSites = array();
	$db_res = CIBlock::GetSite($IBLOCK_OFFERS_ID);
	while($res = $db_res->Fetch())
		$arSites[] = $res["LID"];
	if(!in_array(WIZARD_SITE_ID, $arSites)) {
		$arSites[] = WIZARD_SITE_ID;
		$iblock = new CIBlock;
		$iblock->Update($IBLOCK_OFFERS_ID, array("LID" => $arSites));
	}
}?>