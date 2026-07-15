<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if(!CModule::IncludeModule("iblock") || !CModule::IncludeModule("catalog"))
	return;

if(COption::GetOptionString("enext", "wizard_installed", "N", WIZARD_SITE_ID) == "Y" && !WIZARD_INSTALL_DEMO_DATA)
	return;

//update iblocks, user fields, demo discount
$IBLOCK_CATALOG_ID = (isset($_SESSION["WIZARD_CATALOG_IBLOCK_ID"]) ? (int)$_SESSION["WIZARD_CATALOG_IBLOCK_ID"] : 0);
$IBLOCK_OFFERS_ID = (isset($_SESSION["WIZARD_OFFERS_IBLOCK_ID"]) ? (int)$_SESSION["WIZARD_OFFERS_IBLOCK_ID"] : 0);

if($IBLOCK_OFFERS_ID > 0) {
	$iblockCodeOffers = "enext_offers_".WIZARD_SITE_ID;
	//IBlock fields
	$iblock = new CIBlock;
	$arFields = array(
		"ACTIVE" => "Y",		
		"CODE" => $iblockCodeOffers, 
		"XML_ID" => $iblockCodeOffers,
		"FIELDS" => array(
			"PREVIEW_PICTURE" => array(
				"IS_REQUIRED" => "N",
				"DEFAULT_VALUE" => array(
					"FROM_DETAIL" => "Y",					
					"SCALE" => "Y",
					"WIDTH" => "222",
					"HEIGHT" => "222",
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
			)
		)
	);
	$iblock->Update($IBLOCK_OFFERS_ID, $arFields);
}

if($IBLOCK_CATALOG_ID > 0) {
	$iblockCode = "enext_catalog_".WIZARD_SITE_ID;
	//IBlock fields
	$iblock = new CIBlock;
	$arFields = array(
		"ACTIVE" => "Y",		
		"CODE" => $iblockCode, 
		"XML_ID" => $iblockCode,
		"EDIT_FILE_BEFORE" => "/bitrix/php_interface/include/iblock_element_edit_before_save.php",
		"FIELDS" => array(
			"PREVIEW_PICTURE" => array(
				"IS_REQUIRED" => "N",
				"DEFAULT_VALUE" => array(
					"FROM_DETAIL" => "Y",					
					"SCALE" => "Y",
					"WIDTH" => "222",
					"HEIGHT" => "222",
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
	$iblock->Update($IBLOCK_CATALOG_ID, $arFields);

	if($IBLOCK_OFFERS_ID > 0) {
		$ID_SKU = CCatalog::LinkSKUIBlock($IBLOCK_CATALOG_ID, $IBLOCK_OFFERS_ID);
		$rsCatalogs = CCatalog::GetList(
			array(),
			array("IBLOCK_ID" => $IBLOCK_OFFERS_ID),
			false,
			false,
			array("IBLOCK_ID")
		);
		if($arCatalog = $rsCatalogs->Fetch()) {
			CCatalog::Update($IBLOCK_OFFERS_ID, array("PRODUCT_IBLOCK_ID" => $IBLOCK_CATALOG_ID, "SKU_PROPERTY_ID" => $ID_SKU));
		} else {
			CCatalog::Add(array("IBLOCK_ID" => $IBLOCK_OFFERS_ID, "PRODUCT_IBLOCK_ID" => $IBLOCK_CATALOG_ID, "SKU_PROPERTY_ID" => $ID_SKU));
		}
	}
	
	//user fields for sections	
	$arLanguages = array();
	$rsLanguage = CLanguage::GetList($by, $order, array());
	while($arLanguage = $rsLanguage->Fetch())
		$arLanguages[] = $arLanguage["LID"];
		
	$arUserFields = array("UF_HIDDEN", "UF_HIDDEN_MENU", "UF_PRODUCTS_VIEW", "UF_ICON", "UF_BROWSER_TITLE", "UF_KEYWORDS", "UF_META_DESCRIPTION", "UF_SECTION_TITLE", "UF_BREADCRUMB_TITLE", "UF_BANNER", "UF_BANNER_URL", "UF_PREVIEW", "UF_ADVANTAGES", "UF_FAQ");
	foreach($arUserFields as $userField) {
		$arLabelNames = Array();
		foreach($arLanguages as $languageID) {
			WizardServices::IncludeServiceLang("property_names.php", $languageID);
			$arLabelNames[$languageID] = $userField == "UF_ICON" ? GetMessage("UF_SECTION_ICON") : GetMessage($userField);
		}
		
		$arProperty["EDIT_FORM_LABEL"] = $arLabelNames;
		$arProperty["LIST_COLUMN_LABEL"] = $arLabelNames;
		$arProperty["LIST_FILTER_LABEL"] = $arLabelNames;
		
		$dbRes = CUserTypeEntity::GetList(array(), array("ENTITY_ID" => "IBLOCK_".$IBLOCK_CATALOG_ID."_SECTION", "FIELD_NAME" => $userField));
		if($arRes = $dbRes->Fetch()) {
			$userType = new CUserTypeEntity();
			$userType->Update($arRes["ID"], $arProperty);
		}
	}
	
	//demo discount
	if(\Bitrix\Main\Loader::includeModule("sale")) {
		$specificConditions = array();
		$rsProps = CIBlockProperty::GetList(array(), array("CODE" => "BRAND", "IBLOCK_ID" => $IBLOCK_CATALOG_ID));
		if($arProp = $rsProps->GetNext()) {
			$rsBrands = CIBlockElement::GetList(
				array(),
				array(
					"XML_ID" => "brand_3",
					"IBLOCK_CODE" => "enext_brands_".WIZARD_SITE_ID
				),
				false,
				false,
				array("ID", "IBLOCK_ID")
			);
			if($arBrand = $rsBrands->GetNext()) {		
				$specificConditions = array(
					"CLASS_ID" => "CondIBProp:".$IBLOCK_CATALOG_ID.":".$arProp["ID"],
					"DATA" => array(
						"logic" => "Equal",
						"value" => $arBrand["ID"]
					)
				);
			}
			unset($arBrand, $rsBrands);
		}
		unset($arProp, $rsProps);
		
		$userGroupIds = array();
		$groupIterator = \Bitrix\Main\GroupTable::getList(array(
			"select" => array("ID")
		));
		while($group = $groupIterator->fetch()) {
			$userGroupIds[] = $group["ID"];
		}

		$arFields = array (
			"LID" => WIZARD_SITE_ID,
			"NAME" => GetMessage("WIZ_DISCOUNT"),
			"ACTIVE_FROM" => "",
			"ACTIVE_TO" => "",
			"ACTIVE" => "Y",
			"SORT" => "100",
			"PRIORITY" => "1",
			"LAST_DISCOUNT" => "Y",
			"XML_ID" => "",	
			"CONDITIONS" => array(
				"CLASS_ID" => "CondGroup",
				"DATA" => array(
					"All" => "AND",
					"True" => "True"
				),
				"CHILDREN" => array(
					array(
						"CLASS_ID" => "CondBsktProductGroup",
						"DATA" => array(
							"Found" => "Found",
							"All" => "AND"
						),
						"CHILDREN" => array($specificConditions)
					)
				)
			),
			"ACTIONS" => array(
				"CLASS_ID" => "CondGroup",
				"DATA" => array(
					"All" => "AND"
				),
				"CHILDREN" => array(
					array(
						"CLASS_ID" => "ActSaleBsktGrp",
						"DATA" => array(
							"Type" => "Discount",
							"Value" => 7,
							"Unit" => "Perc",
							"Max" => 0,
							"All" => "AND",
							"True" => "True"
						),
						"CHILDREN" => array($specificConditions)
					)
				)
			),
			"USER_GROUPS" => $userGroupIds
		);
		
		if(!empty($specificConditions)) {
			CSaleDiscount::Add($arFields);
		}

		if($IBLOCK_OFFERS_ID > 0) {
			$specificConditions = array();
			$rsProps = CIBlockProperty::GetList(array(), array("CODE" => "SIZE", "IBLOCK_ID" => $IBLOCK_OFFERS_ID));
			if($arProp = $rsProps->GetNext()) {
				$rsEnumList = CIBlockProperty::GetPropertyEnum("SIZE", array(), array("EXTERNAL_ID" => "xl", "IBLOCK_ID" => $IBLOCK_OFFERS_ID));
				if($arEnumList = $rsEnumList->GetNext()) {
					$specificConditions = array(
						"CLASS_ID" => "CondIBProp:".$IBLOCK_OFFERS_ID.":".$arProp["ID"],
						"DATA" => array(
							"logic" => "Equal",
							"value" => $arEnumList["ID"]
						)
					);
				}
				unset($arEnumList, $rsEnumList);
			}
			unset($arProp, $rsProps);
			
			$userGroupIds = array();
			$groupIterator = \Bitrix\Main\GroupTable::getList(array(
				"select" => array("ID")
			));
			while($group = $groupIterator->fetch()) {
				$userGroupIds[] = $group["ID"];
			}

			$arFields = array (
				"LID" => WIZARD_SITE_ID,
				"NAME" => GetMessage("WIZ_DISCOUNT"),
				"ACTIVE_FROM" => "",
				"ACTIVE_TO" => "",
				"ACTIVE" => "Y",
				"SORT" => "100",
				"PRIORITY" => "1",
				"LAST_DISCOUNT" => "Y",
				"XML_ID" => "",	
				"CONDITIONS" => array(
					"CLASS_ID" => "CondGroup",
					"DATA" => array(
						"All" => "AND",
						"True" => "True"
					),
					"CHILDREN" => array(
						array(
							"CLASS_ID" => "CondBsktProductGroup",
							"DATA" => array(
								"Found" => "Found",
								"All" => "AND"
							),
							"CHILDREN" => array($specificConditions)
						)
					)
				),
				"ACTIONS" => array(
					"CLASS_ID" => "CondGroup",
					"DATA" => array(
						"All" => "AND"
					),
					"CHILDREN" => array(
						array(
							"CLASS_ID" => "ActSaleBsktGrp",
							"DATA" => array(
								"Type" => "Discount",
								"Value" => 15,
								"Unit" => "Perc",
								"Max" => 0,
								"All" => "AND",
								"True" => "True"
							),
							"CHILDREN" => array($specificConditions)
						)
					)
				),
				"USER_GROUPS" => $userGroupIds
			);
			
			if(!empty($specificConditions)) {
				CSaleDiscount::Add($arFields);
			}
		}
	}

	//delete 1c props
	$arPropsToDelete = array("CML2_BAR_CODE", "CML2_ARTICLE", "CML2_ATTRIBUTES", "CML2_TRAITS", "CML2_BASE_UNIT", "CML2_TAXES", "CML2_PICTURES", "CML2_FILES", "CML2_MANUFACTURER");
	foreach($arPropsToDelete as $code) {
		$rsProps = CIBlockProperty::GetList(array(), array("XML_ID" => $code, "IBLOCK_ID" => $IBLOCK_CATALOG_ID));
		if($arProp = $rsProps->GetNext()) {
			CIBlockProperty::Delete($arProp["ID"]);
		}
		unset($arProp, $rsProps);
		
		if($IBLOCK_OFFERS_ID) {
			$rsProps = CIBlockProperty::GetList(array(), array("XML_ID" => $code, "IBLOCK_ID" => $IBLOCK_OFFERS_ID));
			if($arProp = $rsProps->GetNext()) {
				CIBlockProperty::Delete($arProp["ID"]);
			}
			unset($arProp, $rsProps);
		}
	}
	unset($code, $arPropsToDelete);
	
	//iblock user fields
	$arProperty = array();
	$rsProperty = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	while($arProp = $rsProperty->Fetch()) {
		$arProperty[$arProp["CODE"]] = $arProp["ID"];
	}
	unset($arProp, $rsProperty);

	//list user options
	CUserOptions::SetOption("list", "tbl_iblock_section_".md5("catalog.".$IBLOCK_CATALOG_ID), array(
		"columns" => "NAME, UF_HIDDEN, UF_HIDDEN_MENU, UF_ICON, UF_BANNER, UF_BANNER_URL, UF_PREVIEW, UF_ADVANTAGES, ACTIVE, SORT, TIMESTAMP_X, ID",
		"by" => "timestamp_x",
		"order" => "desc",
		"page_size" => "20"
	));	

	CUserOptions::SetOption("list", "tbl_iblock_element_".md5("catalog.".$IBLOCK_CATALOG_ID), array(
		"columns" => "CATALOG_TYPE, NAME, PROPERTY_".$arProperty["ARTNUMBER"].", PREVIEW_PICTURE, DETAIL_PICTURE, CATALOG_QUANTITY, CATALOG_GROUP_1, ACTIVE, SORT, TIMESTAMP_X, ID",
		"by" => "timestamp_x",
		"order" => "desc",
		"page_size" => "20"
	));

	CUserOptions::SetOption("list", "tbl_iblock_list_".md5("catalog.".$IBLOCK_CATALOG_ID), array(
		"columns" => "CATALOG_TYPE, NAME, PROPERTY_".$arProperty["ARTNUMBER"].", PREVIEW_PICTURE, DETAIL_PICTURE, CATALOG_QUANTITY, CATALOG_GROUP_1, ACTIVE, SORT, TIMESTAMP_X, ID",
		"by" => "timestamp_x",
		"order" => "desc",
		"page_size" => "20"
	));

	CUserOptions::SetOption("list", "tbl_catalog_section_".md5("catalog.".$IBLOCK_CATALOG_ID), array(
		"columns" => "NAME, UF_HIDDEN, UF_HIDDEN_MENU, UF_ICON, UF_BANNER, UF_BANNER_URL, UF_PREVIEW, UF_ADVANTAGES, ACTIVE, SORT, TIMESTAMP_X, ID",
		"by" => "timestamp_x",
		"order" => "desc",
		"page_size" => "20"
	));

	CUserOptions::SetOption("list", "tbl_product_admin_".md5("catalog.".$IBLOCK_CATALOG_ID), array(
		"columns" => "CATALOG_TYPE, NAME, PROPERTY_".$arProperty["ARTNUMBER"].", PREVIEW_PICTURE, DETAIL_PICTURE, CATALOG_QUANTITY, CATALOG_GROUP_1, ACTIVE, SORT, TIMESTAMP_X, ID",
		"by" => "timestamp_x",
		"order" => "desc",
		"page_size" => "20"
	));

	CUserOptions::SetOption("list", "tbl_product_list_".md5("catalog.".$IBLOCK_CATALOG_ID), array(
		"columns" => "CATALOG_TYPE, NAME, PROPERTY_".$arProperty["ARTNUMBER"].", PREVIEW_PICTURE, DETAIL_PICTURE, CATALOG_QUANTITY, CATALOG_GROUP_1, ACTIVE, SORT, TIMESTAMP_X, ID",
		"by" => "timestamp_x",
		"order" => "desc",
		"page_size" => "20"
	));

	if($IBLOCK_OFFERS_ID > 0) {
		//iblock user fields
		$arProperty = array();
		$rsProperty = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $IBLOCK_OFFERS_ID));
		while($arProp = $rsProperty->Fetch()) {
			$arProperty[$arProp["CODE"]] = $arProp["ID"];
		}
		unset($arProp, $rsProperty);

		//list user options
		CUserOptions::SetOption("list", "tbl_iblock_element_".md5("catalog.".$IBLOCK_OFFERS_ID), array(
			"columns" => "CATALOG_TYPE, NAME, PROPERTY_".$arProperty["ARTNUMBER"].", PREVIEW_PICTURE, DETAIL_PICTURE, CATALOG_QUANTITY, CATALOG_GROUP_1, ACTIVE, SORT, TIMESTAMP_X, ID",
			"by" => "timestamp_x",
			"order" => "desc",
			"page_size" => "20"
		));

		CUserOptions::SetOption("list", "tbl_iblock_list_".md5("catalog.".$IBLOCK_OFFERS_ID), array(
			"columns" => "CATALOG_TYPE, NAME, PROPERTY_".$arProperty["ARTNUMBER"].", PREVIEW_PICTURE, DETAIL_PICTURE, CATALOG_QUANTITY, CATALOG_GROUP_1, ACTIVE, SORT, TIMESTAMP_X, ID",
			"by" => "timestamp_x",
			"order" => "desc",
			"page_size" => "20"
		));
	}

	//replace macros
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/.left.menu_ext.php", array("ENEXT_CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/articles/index.php", array("ENEXT_CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/brands/index.php", array("ENEXT_CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));	
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/catalog/index.php", array("ENEXT_CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/contacts/index.php", array("ENEXT_CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/include/footer_bigdata.php", array("ENEXT_CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/include/footer_viewed.php", array("ENEXT_CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID, "ENEXT_OFFERS_IBLOCK_ID" => $IBLOCK_OFFERS_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/include/header_compare.php", array("ENEXT_CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/include/hit.php", array("ENEXT_CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/include/new.php", array("ENEXT_CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));	
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/include/recommend.php", array("ENEXT_CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));	
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/news/index.php", array("ENEXT_CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/personal/cart/index.php", array("ENEXT_CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/places/index.php", array("ENEXT_CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/promotions/index.php", array("ENEXT_CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/services/index.php", array("ENEXT_CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	
	$rsProps = CIBlockProperty::GetList(array(), array("CODE" => "MARKER", "IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	if($arProp = $rsProps->GetNext()) {
		$rsMarkers = CIBlockElement::GetList(
			array(),
			array(
				"XML_ID" => array(
					0 => "markers_9",
					1 => "markers_10"
				),
				"IBLOCK_ID" => $arProp["LINK_IBLOCK_ID"]
			),
			false,
			false,
			array("ID", "IBLOCK_ID", "XML_ID")
		);
		while($arMarker = $rsMarkers->GetNext()) {
			if($arMarker["XML_ID"] == "markers_9")
				CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/include/new.php", array("ENEXT_CATALOG_PROPERTY_MARKER" => $arProp["ID"], "ENEXT_CATALOG_PROPERTY_MARKER_NEW" => $arMarker["ID"]));
			elseif($arMarker["XML_ID"] == "markers_10")
				CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/include/hit.php", array("ENEXT_CATALOG_PROPERTY_MARKER" => $arProp["ID"], "ENEXT_CATALOG_PROPERTY_MARKER_HIT" => $arMarker["ID"]));
		}
		unset($arMarker, $rsMarkers);
	}
	unset($arProp, $rsProps);
}?>