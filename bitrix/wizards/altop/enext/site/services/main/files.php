<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if(!defined("WIZARD_SITE_ID") || !defined("WIZARD_SITE_DIR"))
	return;

function ___writeToAreasFile($path, $text) {
	$fd = @fopen($path, "wb");
	if(!$fd)
		return false;

	if(false === fwrite($fd, $text)) {
		fclose($fd);
		return false;
	}

	fclose($fd);

	if(defined("BX_FILE_PERMISSIONS"))
		@chmod($path, BX_FILE_PERMISSIONS);
}

if(COption::GetOptionString("main", "upload_dir") == "")
	COption::SetOptionString("main", "upload_dir", "upload");

if(COption::GetOptionString("enext", "wizard_installed", "N", WIZARD_SITE_ID) == "N" || WIZARD_INSTALL_DEMO_DATA) {
	if(file_exists(WIZARD_ABSOLUTE_PATH."/site/public/".LANGUAGE_ID."/")) {
		CopyDirFiles(
			WIZARD_ABSOLUTE_PATH."/site/public/".LANGUAGE_ID."/",
			WIZARD_SITE_PATH,
			$rewrite = true,
			$recursive = true,
			$delete_after_copy = false
		);
	}

	if(file_exists(WIZARD_ABSOLUTE_PATH."/site/services/main/php_interface/")) {
		CopyDirFiles(
			WIZARD_ABSOLUTE_PATH."/site/services/main/php_interface/",
			Bitrix\Main\Application::getDocumentRoot()."/bitrix/php_interface/".SITE_ID."/",
			$rewrite = true,
			$recursive = true,
			$delete_after_copy = false,
			$exclude = "include"
		);

		CopyDirFiles(
			WIZARD_ABSOLUTE_PATH."/site/services/main/php_interface/",
			Bitrix\Main\Application::getDocumentRoot()."/bitrix/php_interface/",
			$rewrite = true,
			$recursive = true,
			$delete_after_copy = false,
			$exclude = "site_closed.php"
		);
	}
}

if(COption::GetOptionString("enext", "wizard_installed", "N", WIZARD_SITE_ID) == "Y" && !WIZARD_INSTALL_DEMO_DATA)
	return;

$wizard =& $this->GetWizard();

//SITE_NAME_EMAIL//
$siteName = $wizard->GetVar("siteName");
$siteEmail = $wizard->GetVar("siteEmail");

COption::SetOptionString("main", "site_name", $siteName);
COption::SetOptionString("main", "email_from", $siteEmail);

$obSite = new CSite;
$arFields = array("NAME" => $siteName, "SITE_NAME" => $siteName, "SERVER_NAME" => $_SERVER["SERVER_NAME"], "EMAIL" => $siteEmail);			
$siteRes = $obSite->Update(WIZARD_SITE_ID, $arFields);

//HTACCESS//
WizardServices::PatchHtaccess(WIZARD_SITE_PATH);

//REPLACE_MACROS//
WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH, array("SITE_ID" => WIZARD_SITE_ID, "SITE_DIR" => WIZARD_SITE_DIR));

//URLREWRITE//
$arUrlRewrite = array();
if(file_exists(WIZARD_SITE_ROOT_PATH."/urlrewrite.php")) {
	include(WIZARD_SITE_ROOT_PATH."/urlrewrite.php");
}

$arNewUrlRewrite = array(
	array(
		"CONDITION" => "#^".WIZARD_SITE_DIR."articles/#",
		"RULE" => "",
		"ID" => "bitrix:news",
		"PATH" => WIZARD_SITE_DIR."articles/index.php",
	),
	array(
		"CONDITION" => "#^".WIZARD_SITE_DIR."brands/#",
		"RULE" => "",
		"ID" => "bitrix:news",
		"PATH" => WIZARD_SITE_DIR."brands/index.php",
	),
	array(
		"CONDITION" => "#^".WIZARD_SITE_DIR."catalog/#",
		"RULE" => "",
		"ID" => "bitrix:catalog",
		"PATH" => WIZARD_SITE_DIR."catalog/index.php",
	),
	array(
		"CONDITION" => "#^".WIZARD_SITE_DIR."gallery/#",
		"RULE" => "",
		"ID" => "bitrix:news",
		"PATH" => WIZARD_SITE_DIR."gallery/index.php",
	),
	array(
		"CONDITION" => "#^".WIZARD_SITE_DIR."news/#",
		"RULE" => "",
		"ID" => "bitrix:news",
		"PATH" => WIZARD_SITE_DIR."news/index.php",
	),
	array(
		"CONDITION" => "#^".WIZARD_SITE_DIR."personal/#",
		"RULE" => "",
		"ID" => "bitrix:sale.personal.section",
		"PATH" => WIZARD_SITE_DIR."personal/index.php",
	),
	array(
		"CONDITION" => "#^".WIZARD_SITE_DIR."places/#",
		"RULE" => "",
		"ID" => "bitrix:news",
		"PATH" => WIZARD_SITE_DIR."places/index.php",
	),
	array(
		"CONDITION" => "#^".WIZARD_SITE_DIR."promotions/#",
		"RULE" => "",
		"ID" => "bitrix:news",
		"PATH" => WIZARD_SITE_DIR."promotions/index.php",
	),
	array(
		"CONDITION" => "#^".WIZARD_SITE_DIR."services/#",
		"RULE" => "",
		"ID" => "bitrix:news",
		"PATH" => WIZARD_SITE_DIR."services/index.php",
	)
);

foreach($arNewUrlRewrite as $arUrl) {
	if(!in_array($arUrl, $arUrlRewrite)) {
		CUrlRewriter::Add($arUrl);
	}
}?>