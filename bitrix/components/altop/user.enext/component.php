<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

$arParams["PATH_TO_PERSONAL"] = trim($arParams["PATH_TO_PERSONAL"]);
if($arParams["PATH_TO_PERSONAL"] == "")
	$arParams["PATH_TO_PERSONAL"] = SITE_DIR."personal/";

global $USER;

if($this->StartResultCache(false, $userId = intval($USER->GetID()))) {
	if($userId > 0) {
		$arResult["IS_AUTHORIZED"] = true;

		$arResult["USER_NAME"] = !empty($USER->GetFirstName()) ? $USER->GetFirstName() : $USER->GetLogin();
	
		$rsUser = CUser::GetByID($userId);
		if($arUser = $rsUser->Fetch()) {
			if($arUser["PERSONAL_PHOTO"] > 0) {
				$arFile = CFile::GetFileArray($arUser["PERSONAL_PHOTO"]);
				if($arFile["WIDTH"] > 32 || $arFile["HEIGHT"] > 32) {
					$arFileTmp = CFile::ResizeImageGet(
						$arFile,
						array("width" => 32, "height" => 32),
						BX_RESIZE_IMAGE_PROPORTIONAL,
						true
					);
					$arResult["USER_PHOTO"] = array(
						"SRC" => $arFileTmp["src"],
						"WIDTH" => $arFileTmp["width"],
						"HEIGHT" => $arFileTmp["height"],
					);	
				} else {
					$arResult["USER_PHOTO"] = $arFile;
				}
				unset($arFile);
			}
		}
		unset($arUser, $rsUser);
	} else {
		$this->abortResultCache();
	}
	
	$this->IncludeComponentTemplate();
}