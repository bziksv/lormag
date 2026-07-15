<?define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

if(is_string($_REQUEST["backurl"]) && strpos($_REQUEST["backurl"], "/") === 0)
	LocalRedirect($_REQUEST["backurl"]);

$APPLICATION->SetTitle("Авторизация");?>

<div class="alert alert-success alert-show">Вы зарегистрированы и успешно авторизовались.<br /><a href="<?=SITE_DIR?>">Вернуться на главную страницу</a></div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>