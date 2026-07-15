<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if(!defined("WIZARD_TEMPLATE_ID"))
	return;

$bitrixTemplateDir = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".WIZARD_TEMPLATE_ID;

CopyDirFiles(
	$_SERVER["DOCUMENT_ROOT"].WizardServices::GetTemplatesPath(WIZARD_RELATIVE_PATH."/site")."/".WIZARD_TEMPLATE_ID,
	$bitrixTemplateDir,
	$rewrite = true,
	$recursive = true, 
	$delete_after_copy = false,
	$exclude = ""
);

//Attach template to default site
$obSite = CSite::GetList($by = "def", $order = "desc", Array("LID" => WIZARD_SITE_ID));
if($arSite = $obSite->Fetch()) {
	$arTemplates = array();
	$found = false;
	$foundEmpty = false;
	$obTemplate = CSite::GetTemplateList($arSite["LID"]);
	while($arTemplate = $obTemplate->Fetch()) {
		if(!$found && strlen(trim($arTemplate["CONDITION"])) <= 0) {
			$arTemplate["TEMPLATE"] = WIZARD_TEMPLATE_ID;
			$found = true;
		}
		if($arTemplate["TEMPLATE"] == "empty") {
			$foundEmpty = true;
			continue;
		}
		$arTemplates[]= $arTemplate;
	}

	if(!$found)
		$arTemplates[]= Array("CONDITION" => "", "SORT" => 150, "TEMPLATE" => WIZARD_TEMPLATE_ID);

	$arFields = Array(
		"TEMPLATE" => $arTemplates
	);

	$obSite = new CSite();
	$obSite->Update($arSite["LID"], $arFields);

	$siteCharset = $arSite["CHARSET"];
	if(strtolower($siteCharset) != "windows-1251") {
		$file = new Bitrix\Main\IO\File($bitrixTemplateDir."/snippets/.content.php");
		if($file->isExists()) {
			$content = $file->getContents();
			$content = Bitrix\Main\Text\Encoding::convertEncoding($content, "windows-1251", $siteCharset);
			$file->putContents($content);
		}
		unset($content, $file);

		$file = new Bitrix\Main\IO\File($bitrixTemplateDir."/snippets/text.enext.snp");
		if($file->isExists()) {
			$content = $file->getContents();
			$content = Bitrix\Main\Text\Encoding::convertEncoding($content, "windows-1251", $siteCharset);
			$file->putContents($content);
		}
		unset($content, $file);

		$file = new Bitrix\Main\IO\File($bitrixTemplateDir."/snippets/video.enext.snp");
		if($file->isExists()) {
			$content = $file->getContents();
			$content = Bitrix\Main\Text\Encoding::convertEncoding($content, "windows-1251", $siteCharset);
			$file->putContents($content);
		}
		unset($content, $file);

		$file = new Bitrix\Main\IO\File($bitrixTemplateDir."/snippets/photo.enext.snp");
		if($file->isExists()) {
			$content = $file->getContents();
			$content = Bitrix\Main\Text\Encoding::convertEncoding($content, "windows-1251", $siteCharset);
			$file->putContents($content);
		}
		unset($content, $file);

		$file = new Bitrix\Main\IO\File($bitrixTemplateDir."/snippets/card-text-photo.enext.snp");
		if($file->isExists()) {
			$content = $file->getContents();
			$content = Bitrix\Main\Text\Encoding::convertEncoding($content, "windows-1251", $siteCharset);
			$file->putContents($content);
		}
		unset($content, $file);

		$file = new Bitrix\Main\IO\File($bitrixTemplateDir."/snippets/card-photo-text.enext.snp");
		if($file->isExists()) {
			$content = $file->getContents();
			$content = Bitrix\Main\Text\Encoding::convertEncoding($content, "windows-1251", $siteCharset);
			$file->putContents($content);
		}
		unset($content, $file);

		$file = new Bitrix\Main\IO\File($bitrixTemplateDir."/snippets/accent.enext.snp");
		if($file->isExists()) {
			$content = $file->getContents();
			$content = Bitrix\Main\Text\Encoding::convertEncoding($content, "windows-1251", $siteCharset);
			$file->putContents($content);
		}
		unset($content, $file);

		$file = new Bitrix\Main\IO\File($bitrixTemplateDir."/snippets/accent-plus.enext.snp");
		if($file->isExists()) {
			$content = $file->getContents();
			$content = Bitrix\Main\Text\Encoding::convertEncoding($content, "windows-1251", $siteCharset);
			$file->putContents($content);
		}
		unset($content, $file);

		$file = new Bitrix\Main\IO\File($bitrixTemplateDir."/snippets/list-ul.enext.snp");
		if($file->isExists()) {
			$content = $file->getContents();
			$content = Bitrix\Main\Text\Encoding::convertEncoding($content, "windows-1251", $siteCharset);
			$file->putContents($content);
		}
		unset($content, $file);

		$file = new Bitrix\Main\IO\File($bitrixTemplateDir."/snippets/list-ol.enext.snp");
		if($file->isExists()) {
			$content = $file->getContents();
			$content = Bitrix\Main\Text\Encoding::convertEncoding($content, "windows-1251", $siteCharset);
			$file->putContents($content);
		}
		unset($content, $file);

		$file = new Bitrix\Main\IO\File($bitrixTemplateDir."/snippets/top.enext.snp");
		if($file->isExists()) {
			$content = $file->getContents();
			$content = Bitrix\Main\Text\Encoding::convertEncoding($content, "windows-1251", $siteCharset);
			$file->putContents($content);
		}
		unset($content, $file);
	}
}

COption::SetOptionString("main", "wizard_template_id", WIZARD_TEMPLATE_ID, false, WIZARD_SITE_ID);?>