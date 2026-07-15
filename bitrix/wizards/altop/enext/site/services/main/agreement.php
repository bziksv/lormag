<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!defined("WIZARD_SITE_ID"))
	return;

use Bitrix\Main\UserConsent\Agreement;

$agreement = new Agreement(null);
if($agreement) {
	$data = array(
		"NAME" => GetMessage("AGREEMENT_NAME"),
		"TYPE" => "C",
		"LANGUAGE_ID" => LANGUAGE_ID,
		"DATA_PROVIDER" => "N",
		"AGREEMENT_TEXT" => GetMessage("AGREEMENT_TEXT"),
		"LABEL_TEXT" => GetMessage("AGREEMENT_LABEL_TEXT"),
		"FIELDS" => array(),
	);

	$agreement->mergeData($data);
	$agreement->save();
	
	$agreementId = $agreement->getId();
	if(intval($agreementId) > 0) {		
		CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/personal/order/make/index.php", array("ENEXT_USER_CONSENT_ID" => $agreementId));	
		CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/.footer.menu.php", array("ENEXT_USER_CONSENT_ID" => $agreementId));
	}
}