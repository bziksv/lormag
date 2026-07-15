<?
AddEventHandler("iblock", "OnAfterIBlockElementAdd", Array("OnAfterIBlockElementAddClass", "OnAfterIBlockElementAddHandler"));
class OnAfterIBlockElementAddClass
{
    public static function OnAfterIBlockElementAddHandler(&$arFields)
    {
		if($arFields['RESULT']){
				$properties = [];
				$res = CIBlockElement::GetProperty($arFields['IBLOCK_ID'], $arFields["ID"], "sort", "asc", ["CODE" => "CML2_ATTRIBUTES"]);
				while ($ob = $res->GetNext())
				{
					$name = trim(strip_tags($ob['DESCRIPTION']));
					$value = trim(strip_tags($ob['VALUE']));
					$alias = Cutil::translit($name, "ru", ["max_len" => 40]);
					
					$arFieldsProperty = [
					  "NAME" => $name,
					  "CODE" => $alias,
					  "IBLOCK_ID" => $arFields['IBLOCK_ID'],
					];
					
					if (CIBlockProperty::GetList([], ["IBLOCK_ID" => $arFields['IBLOCK_ID'], "CODE" => $alias])->SelectedRowsCount() <= 0)
						(new CIBlockProperty)->Add($arFieldsProperty);
					
					$properties[$arFieldsProperty["CODE"]] = $value;
				}
				
			CIBlockElement::SetPropertyValuesEx($arFields['ID'], false, $properties);
		}
    }
}

function getCurrSection()
{
	global $APPLICATION;
	
	$dir = $APPLICATION->GetCurDir();
	
	if (str_starts_with($dir, '/catalog/')) 
	{
		$path = explode('/', substr($dir, 1, -1));
		
		if(isset($path[1]))
		{
			$code = $path[1];
			
			$db_list = CIBlockSection::GetList([], ['IBLOCK_ID' => 26, 'CODE' => $code], false, ['IBLOCK_ID', 'ID', 'CODE', 'NAME', 'UF_*']);
			if($ar_result = $db_list->GetNext())
			{
				$ar_result["URL"] = 'catalog';
				return $ar_result;
			}
		}
	}

	if (str_starts_with($dir, '/product/')) 
	{
		$path = explode('/', substr($dir, 1, -1));
		
		if(isset($path[1]))
		{
			$code = $path[1];

				
			$db_list = CIBlockSection::GetList([], ['IBLOCK_ID' => 26, '!UF_HIDE_MENU_INDEX' =>  false], false, ['IBLOCK_ID', 'ID', 'CODE', 'NAME', 'UF_*']);
			if($ar_result = $db_list->GetNext())
			{
				$aRRresult = $ar_result;
			}

			$aRRresult["URL"] = 'product';
			return $aRRresult;


			// $arSelect = Array("ID", "NAME", "IBLOCK_SECTION_ID");
			// $arFilter = ['IBLOCK_ID' => 26, 'CODE' => $code];
			// $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>1), $arSelect);
			// if($ob = $res->GetNextElement())
			// {
			// 	$arFields = $ob->GetFields();
			// 	if($arFields["IBLOCK_SECTION_ID"]){
			// 		$db_list = CIBlockSection::GetList([], ['IBLOCK_ID' => 26, 'ID' => $arFields["IBLOCK_SECTION_ID"]], false, ['IBLOCK_ID', 'ID', 'CODE', 'NAME', 'UF_*']);
			// 		if($ar_result = $db_list->GetNext())
			// 		{
			// 			$ar_result["URL"] = 'product';
			// 			return $ar_result;
			// 		}
			// 	}
			// }

		}
	}

	return null;
}

function isWrapAttr($current, $target)
{
    if($current < $target)
        return true;
        
    return false;
}

if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/fix_name.php')){
   require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/fix_name.php');
}