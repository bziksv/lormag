<?use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class altop_enext extends CModule {
	const solutionName	= "enext"; 	
	const partnerName = "altop"; 
	const moduleClass = "CEnext"; 
	
	var $MODULE_ID = "altop.enext";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "R";

	function __construct() {
		$arModuleVersion = array();
		
		include(__DIR__.'/version.php');
		
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = Loc::getMessage("ENEXT_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("ENEXT_MODULE_DESC");
		$this->PARTNER_NAME = Loc::getMessage("ENEXT_PARTNER");
		$this->PARTNER_URI = Loc::getMessage("ENEXT_PARTNER_URI");
	}
	
	function InstallDB($install_wizard = true) {
		global $DB, $DBType, $APPLICATION;

		RegisterModule($this->MODULE_ID);
		COption::SetOptionString($this->MODULE_ID, "GROUP_DEFAULT_RIGHT", $this->MODULE_GROUP_RIGHTS);
		
		RegisterModuleDependences("main", "OnBeforeProlog", $this->MODULE_ID, self::moduleClass, "ReinitPath");
		RegisterModuleDependences("main", "OnBeforeProlog", $this->MODULE_ID, self::moduleClass, "showPanel");
		if(preg_match("/.bitrixlabs.ru/", $_SERVER["HTTP_HOST"])){
			RegisterModuleDependences("main", "OnBeforeProlog", $this->MODULE_ID, self::moduleClass, "correctInstall");
		}
		
		return true;
	}

	function UnInstallDB($arParams = array()) {
		global $DB, $DBType, $APPLICATION;		
		
		UnRegisterModuleDependences("main", "OnBeforeProlog", $this->MODULE_ID, self::moduleClass, "ReinitPath");
		UnRegisterModuleDependences("main", "OnBeforeProlog", $this->MODULE_ID, self::moduleClass, "showPanel");
		
		COption::RemoveOption($this->MODULE_ID, "GROUP_DEFAULT_RIGHT");
		UnRegisterModule($this->MODULE_ID);

		return true;
	}

	function InstallEvents() {
		return true;
	}

	function UnInstallEvents() {
		return true;
	}

	function InstallPublic() {
	}
	
	function InstallFiles() {		
		CopyDirFiles(__DIR__."/components/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		CopyDirFiles(__DIR__."/js/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
		CopyDirFiles(__DIR__."/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools", true, true);
		CopyDirFiles(__DIR__."/wizards/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards", true, true);
		
		if(preg_match("/.bitrixlabs.ru/", $_SERVER["HTTP_HOST"])){
			@set_time_limit(0);
			include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
			CFileMan::DeleteEx(array("s1", "/bitrix/modules/".$this->MODULE_ID."/install/wizards"));
		}

		return true;
	}

	function UnInstallFiles() {
		DeleteDirFilesEx("/bitrix/js/".$this->MODULE_ID."/");
		DeleteDirFilesEx("/bitrix/tools/".$this->MODULE_ID."/");
		DeleteDirFilesEx("/bitrix/wizards/".self::partnerName."/".self::solutionName."/");

		return true;
	}

	function DoInstall(){
		global $APPLICATION, $step;

		$this->InstallFiles();
		$this->InstallDB(false);
		$this->InstallEvents();
		$this->InstallPublic();

		$APPLICATION->IncludeAdminFile(Loc::getMessage("ENEXT_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/step.php");
	}

	function DoUninstall(){
		global $APPLICATION, $step;

		$this->UnInstallDB();
		$this->UnInstallFiles();
		$this->UnInstallEvents();
		$APPLICATION->IncludeAdminFile(Loc::getMessage("ENEXT_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/unstep.php");
	}
}?>