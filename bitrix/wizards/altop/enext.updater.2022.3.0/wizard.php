<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class StartStep extends CWizardStep {
	function InitStep() {
		$this->SetTitle(GetMessage("WIZARD_STEP_START"));
		$this->SetStepID("start");		
		$this->SetNextStep("site");
		$this->SetCancelStep("cancel");
	}

	function ShowStep() {
		$this->content = GetMessage("WIZARD_STEP_START_DESCR");
	}
}

class SiteStep extends CWizardStep {
	function InitStep() {
		$this->SetTitle(GetMessage("WIZARD_STEP_SITE"));		
		$this->SetStepID("site");
		$this->SetNextStep("install");
		$this->SetCancelStep("cancel");
		
		$dbSite = CSite::GetDefList();
		if($arSite = $dbSite->Fetch()) {			
			$wizard = &$this->GetWizard();
			$wizard->SetDefaultVars(
				array(
					"siteID" => $arSite["ID"]
				)
			);
		}
	}

	function ShowStep() {		
		$arSites = array();		
		$dbSite = CSite::GetList($b = "SORT", $o = "ASC", array("ACTIVE" => "Y"));
		while($arSite = $dbSite->Fetch()) {
			$arSites[$arSite["ID"]] = $arSite["NAME"]." (".$arSite["ID"].")";
			if($arSite["DEF"] == "Y")
				$defSite = $arSite["ID"];
		}
		
		$this->content .= "<b>".GetMessage("WIZARD_STEP_SITE_DESCR")."</b><br /><br />";
		$this->content .= $this->ShowSelectField("siteID", $arSites);
	}
}

class InstallStep extends CWizardStep {
	function InitStep() {
		$this->SetTitle(GetMessage("WIZARD_STEP_INSTALL"));		
		$this->SetStepID("install");
		$this->SetNextStep("final");
		$this->SetCancelStep("cancel");

		$wizard = &$this->GetWizard();
		$siteID = $wizard->GetVar("siteID", true);

		$catalogIblockID = false;
		if(Bitrix\Main\Loader::includeModule("iblock")) {
			$rsIblocks = CIBlock::GetList(array(), array("SITE_ID" => $siteID, "ACTIVE" => "Y"));
			while($arIblock = $rsIblocks->Fetch()) {			
				if($arIblock["IBLOCK_TYPE_ID"] == "catalog" && Bitrix\Main\Loader::includeModule("catalog")) {
					$mxResult = CCatalogSKU::GetInfoByProductIBlock($arIblock["ID"]);
					if(is_array($mxResult))
						$catalogIblockID = $mxResult["PRODUCT_IBLOCK_ID"];
				}
			}
		}
		if($catalogIblockID > 0) {
			$wizard->SetDefaultVars(
				array(
					"catalogIblockID" => $catalogIblockID
				)
			);
		}
	}

	function OnPostForm() {
		$wizard = &$this->GetWizard();		
		if($wizard->IsNextButtonClick()) {
			$path = $wizard->package->path;
			$arResult = $wizard->GetVars(true);
			
			if(Bitrix\Main\Config\Option::get("enext", "2022.3.0", "N", $arResult["siteID"]) == "Y")
				return;

			//SITE_DIR//
			$dbSite = CSite::GetList($b = "SORT", $o = "ASC", array("ID" => $arResult["siteID"]));
			if($arSite = $dbSite->Fetch())
				$arResult["siteDir"] = $arSite["DIR"];

			if(empty($arResult["siteDir"]))
				$arResult["siteDir"] = "/";

			//SITE_PATH//
			$sitePath = $_SERVER["DOCUMENT_ROOT"].$arResult["siteDir"];

			//REPLACE_CONTENT_INCLUDE_SLIDE_PANEL_CART//
			$file = new Bitrix\Main\IO\File($sitePath."include/slide_panel_cart.php");
			if($file->isExists()) {
				$content = $file->getContents();

				$content = preg_replace("!\"COLUMNS_LIST_EXT\" => array\((.*?)\)!s", "\"COLUMNS_LIST_EXT\" => array(\r\n\t\t\t0 => \"NAME\",\r\n\t\t\t1 => \"PROPS\",\r\n\t\t\t2 => \"DELETE\",\r\n\t\t\t3 => \"QUANTITY\",\r\n\t\t\t4 => \"SUM\",\r\n\t\t\t5 => \"PROPERTY_ARTNUMBER\",\r\n\t\t\t6 => \"PROPERTY_OBJECT\",\r\n\t\t\t7 => \"PROPERTY_M2_COUNT\",\r\n\t\t\t8 => \"PROPERTY_OLD_PRICE\",\r\n\t\t)", $content);

				$file->putContents($content);
			}
			unset($content, $file);
			
			//CATALOG//
			$catalogIblockID = (int)$arResult["catalogIblockID"];
			if($catalogIblockID > 0) {
				//UF_HIDDEN//
				$dbField = CUserTypeEntity::GetList(array(), array("ENTITY_ID" => "IBLOCK_".$catalogIblockID."_SECTION", "FIELD_NAME" => "UF_HIDDEN"));
				if($arField = $dbField->GetNext()) {
					$uf = new CUserTypeEntity();
					$arFieldsUpdate = array(						
						"EDIT_FORM_LABEL" => array(
							LANGUAGE_ID => GetMessage("UF_HIDDEN")
						),
						"LIST_COLUMN_LABEL" => array(
							LANGUAGE_ID => GetMessage("UF_HIDDEN")
						),
						"LIST_FILTER_LABEL" => array(
							LANGUAGE_ID => GetMessage("UF_HIDDEN")
						)
					);
					$uf->Update($arField["ID"], $arFieldsUpdate);
				}
				unset($arFieldsUpdate, $uf, $arField, $dbField);
			}
			unset($catalogIblockID);
			
			Bitrix\Main\Config\Option::set("enext", "2022.3.0", "Y", $arResult["siteID"]);
		}
	}

	function ShowStep() {
		$wizard = &$this->GetWizard();
		$siteID = $wizard->GetVar("siteID", true);
		
		$catalogIblocks = array();
		if(Bitrix\Main\Loader::includeModule("iblock")) {
			$rsIblocks = CIBlock::GetList(array(), array("SITE_ID" => $siteID, "ACTIVE" => "Y"));
			while($arIblock = $rsIblocks->Fetch()) {
				if($arIblock["IBLOCK_TYPE_ID"] == "catalog")
					$catalogIblocks[$arIblock["ID"]] = $arIblock["NAME"]." (".$arIblock["ID"].")";
			}
		}
		
		$this->content .= "<b>".GetMessage("WIZARD_STEP_INSTALL_DESCR")."</b><br /><br />";
		$this->content .= $this->ShowSelectField("catalogIblockID", $catalogIblocks);
	}
}

class FinalStep extends CWizardStep {
	function InitStep() {
		$this->SetTitle(GetMessage("WIZARD_STEP_FINAL"));
		$this->SetStepID("final");
		$this->SetCancelCaption(GetMessage("WIZARD_CLOSE"));
		$this->SetCancelStep("final");
	}

	function ShowStep() {
		$wizard = &$this->GetWizard();
		$arResult = $wizard->GetVars(true);

		$arSite = CSite::GetList($b = "SORT", $o = "ASC", array("ID" => $arResult["siteID"]))->Fetch();
		
		$this->content .= GetMessage("WIZARD_STEP_FINAL_DESCR", array("#SITE_NAME#" => $arSite["NAME"], "#SITE_ID#" => $arSite["ID"]));
	}
}

class CancelStep extends CWizardStep {
	function InitStep() {
		$this->SetTitle(GetMessage("WIZARD_STEP_CANCEL"));
		$this->SetStepID("cancel");
		$this->SetCancelCaption(GetMessage("WIZARD_CLOSE"));
		$this->SetCancelStep("cancel");
	}

	function ShowStep() {
		$this->content .= GetMessage("WIZARD_STEP_CANCEL_DESCR");
	}
}