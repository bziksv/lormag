<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if($arResult['SECTIONS_COUNT'] > 0) {	

$uf_iblock_id = 26;
$uf_name = Array("UF_NAME_SECTION");

//$uf_section_id = $matches[1];

if(CModule::IncludeModule("iblock")): 
	$uf_arresult = CIBlockSection::GetList(Array("SORT"=>"­­ASC"), Array("IBLOCK_ID" => $uf_iblock_id), false, $uf_name);
	while($uf_value = $uf_arresult->GetNext()):
		if($uf_value["UF_NAME_SECTION"]): 
			$nameMenu[$uf_value["CODE"]] = $uf_value["UF_NAME_SECTION"];
		endif;
	endwhile;
endif;


	$boolClear = false;
	$arNewSections = array();
	foreach($arResult['SECTIONS'] as $key => $arOneSection) {

		if($nameMenu[$arOneSection['CODE']]){
			$arResult['SECTIONS'][$key]["NAME"] = $nameMenu[$arOneSection['CODE']];
		}

		if($arOneSection['RELATIVE_DEPTH_LEVEL'] > 1) {
			$boolClear = true;
			continue;
		}
		$arNewSections[] = $arOneSection;
	}
	unset($arOneSection);
	if($boolClear) {
		$arResult['SECTIONS'] = $arNewSections;
		$arResult['SECTIONS_COUNT'] = count($arNewSections);
	}
	unset($arNewSections);
}