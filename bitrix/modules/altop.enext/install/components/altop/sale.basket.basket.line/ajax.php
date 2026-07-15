<?
define('STOP_STATISTICS', true);
define('NOT_CHECK_PERMISSIONS', true);

if(!isset($_POST['siteId']) || !is_string($_POST['siteId']))
	die();

if(!isset($_POST['templateName']) || !is_string($_POST['templateName']))
	die();

if($_SERVER['REQUEST_METHOD'] != 'POST' || preg_match('/^[A-Za-z0-9_]{2}$/', $_POST['siteId']) !== 1 ||	preg_match('/^[.A-Za-z0-9_-]+$/', $_POST['templateName']) !== 1)
	die;

if(!empty($_POST['requestUri']))
	$_SERVER['REQUEST_URI'] = $_POST['requestUri'];

define('SITE_ID', $_POST['siteId']);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if(!check_bitrix_sessid())
	die;

$_POST['arParams']['AJAX'] = 'Y';

$APPLICATION->RestartBuffer();
header('Content-Type: text/html; charset='.LANG_CHARSET);
$APPLICATION->IncludeComponent('altop:sale.basket.basket.line', $_POST['templateName'], $_POST['arParams']);