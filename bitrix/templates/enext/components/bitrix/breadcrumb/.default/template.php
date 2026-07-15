<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

global $APPLICATION;

if(empty($arResult))
	return;

$page = $APPLICATION->GetCurPage();

$arPage = explode('/',$page);

$elementCode = $arPage[count($arPage)-2];


$ar_new_groups = [];

$arr_new_groups = [];
$k_new_groups = 0;

$uf_iblock_id = 26;
$uf_name = Array("UF_NAME_SECTION");

$arSelect = Array("ID", "NAME");
$arFilter = Array("IBLOCK_ID"=>26, "CODE"=>$elementCode);
$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>1), $arSelect);
if($ob = $res->GetNextElement())
{
	$arFields = $ob->GetFields();

	$ELEMENT_ID = $arFields["ID"];


	$db_old_groups = CIBlockElement::GetElementGroups($ELEMENT_ID, true);
	
	while($ar_group = $db_old_groups->Fetch()){
		$ar_new_groups[$ar_group["NAME"]] = $ar_group["CODE"];


		$uf_arresult = CIBlockSection::GetList(Array("SORT"=>"­­ASC"), Array("IBLOCK_ID" => $uf_iblock_id, "ID"=> $ar_group["ID"]), false, $uf_name);
		if($uf_value = $uf_arresult->GetNext()):
			if($uf_value["UF_NAME_SECTION"]): 
				$ar_group["NAME"] = $uf_value["UF_NAME_SECTION"];
	
			endif;
		endif;


		if($ar_group["IBLOCK_SECTION_ID"]){
			$k_new_groups++;
			$arr_new_groups[$ar_group["DEPTH_LEVEL"]-1] = $ar_group;
		}else{
			$arr_new_groups[0] = $ar_group;
		}

	}
}else{

    $uf_arresult = CIBlockSection::GetList(Array("SORT"=>"­­ASC"), Array("IBLOCK_ID" => $uf_iblock_id), false, $uf_name);
    while($uf_value = $uf_arresult->GetNext()):
        if($uf_value["UF_NAME_SECTION"]): 
            $nameMenu[$uf_value["NAME"]] = $uf_value["UF_NAME_SECTION"];
        endif;
    endwhile;

}


$itemSize = count($arResult);

$strReturn = "<div class=\"navigation-block\">";

$strReturn .= "<a href=\"".($arResult[$itemSize - 1]["LINK"] <> "" && $arResult[$itemSize - 1]["LINK"] != $APPLICATION->GetCurPage() ? $arResult[$itemSize - 1]["LINK"] : $arResult[$itemSize - 2]["LINK"])."\" class=\"navigation-back\"><i class=\"icon-arrow-left\"></i></a>";

$strReturn .= "<div class=\"navigation-items\"><div class=\"navigation-breadcrumb\" itemscope itemtype=\"http://schema.org/BreadcrumbList\">";

for($index = 0; $index < $itemSize; $index++) {
	$title = htmlspecialcharsex($arResult[$index]["TITLE"]);
	$arrow = ($index > 0 ? "<i class=\"navigation-breadcrumb__separate\"></i>" : "");

if(!$title) continue;
if($arr_new_groups && $index==2) break;

	if($nameMenu[$title]){
		$title = $nameMenu[$title];
	}

	if($arResult[$index]["LINK"] <> "" && $index != $itemSize - 1) {

		if($ar_new_groups[$title]) $arResult[$index]["LINK"] = '/catalog/'.$ar_new_groups[$title].'/';

		$strReturn .= "
			<div class=\"navigation-breadcrumb__item\" id=\"breadcrumb_".$index."\" itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\">
				".$arrow."
				<a href=\"".$arResult[$index]["LINK"]."\" title=\"".$title."\" itemprop=\"item\">
					<span itemprop=\"name\">".$title."</span>
				</a>
				<meta itemprop=\"position\" content=\"".($index + 1)."\" />
			</div>";
	}
}

if($arr_new_groups){
	foreach ($arr_new_groups as $key => $groups) {
		$index++;
		$link = '/catalog/'.$groups['CODE'].'/';
		$title = $groups['NAME'];
		$strReturn .= "
			<div class=\"navigation-breadcrumb__item\" id=\"breadcrumb_".$index."\" itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\">
				".$arrow."
				<a href=\"".$link."\" title=\"".$title."\" itemprop=\"item\">
					<span itemprop=\"name\">".$title."</span>
				</a>
				<meta itemprop=\"position\" content=\"".($index + 1)."\" />
			</div>";
	}
}

$strReturn .= "</div><h1 id=\"pagetitle\" class=\"navigation-title\">".$APPLICATION->GetTitle()."</h1></div></div>";

return $strReturn;?>