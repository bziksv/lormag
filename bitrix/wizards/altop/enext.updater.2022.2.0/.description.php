<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arWizardDescription = array(
	"NAME" => GetMessage("WIZARD_NAME"), 
	"DESCRIPTION" => GetMessage("WIZARD_DESC"), 
	"ICON" => "",
	"COPYRIGHT" => "ALTOP",
	"VERSION" => "2022.2.0",
	"DEPENDENCIES" => array( 
		"altop.enext" => "2022.1.0"
	),
	"STEPS" => array("StartStep", "SiteStep", "InstallStep", "FinalStep", "CancelStep")
);?>