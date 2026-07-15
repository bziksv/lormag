<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Соглашение на обработку персональных данных");?>

<?use Bitrix\Main\Context,
	Bitrix\Main\UserConsent\Agreement;

$id = Context::getCurrent()->getRequest()->get("id");
if(!$id)
	return;

$agreement = new Agreement($id);
if(!$agreement->isExist() || !$agreement->isActive())
	return;

$agreement->setReplace(array("fields" => array("Имя", "Телефон", "IP-адрес")));
$agreementText = $agreement->getText();?>

<p><?=nl2br(htmlspecialcharsbx($agreementText))?></p>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>