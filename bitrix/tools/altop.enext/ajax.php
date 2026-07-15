<?define("STOP_STATISTICS", true);
define("NOT_CHECK_PERMISSIONS", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;

$moduleClass = "CEnext";
$moduleID = "altop.enext";

if(!Bitrix\Main\Loader::IncludeModule($moduleID))
	return;
	
$moduleClass::ajax();