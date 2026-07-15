<?
function BXIBlockAfterSave($arFields) {
   CEnext::UpdateMeasureRatio($arFields);
}
?>