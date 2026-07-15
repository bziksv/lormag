<?

function fix_name(){

	CModule::IncludeModule("iblock");

	$IBLOCK_ID = 26;

	$arSelect = Array("ID", "NAME");
	$arFilter = Array("IBLOCK_ID"=>IntVal($IBLOCK_ID), "NAME"=>"%&quot;%");
	$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>500), $arSelect);
	while($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();


		$arFields["NAME"] = str_replace("&quot;", '', $arFields["NAME"]);
		$arFields["NAME"] = str_replace("&amp;quot;", '"', $arFields["NAME"]);

		$el = new CIBlockElement;

		$arLoadProductArray = array(
	            "NAME"   => $arFields["NAME"]
		);

	    $newElement = $el->Update($arFields["ID"], $arLoadProductArray);


	}

	return 'fix_name();';

}