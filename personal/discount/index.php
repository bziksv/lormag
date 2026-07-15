<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Накопительная скидка");
global $arSettings;
if($arSettings["ACCUMULATIVE_DISCOUNT"]["VALUE"] == "Y") {?>
	<?$APPLICATION->IncludeComponent("bitrix:catalog.discsave.info", ".default",
		array(
			"SITE_ID" => "s1",
			"USER_ID" => "={$USER->GetID()}",
			"SHOW_NEXT_LEVEL" => "Y"
		),
		false
	);?>
	<?$APPLICATION->IncludeComponent("bitrix:main.include", "description",
		array(
			"AREA_FILE_SHOW" => "file",
			"PATH" => SITE_DIR."include/accumulative_discount_description.php"
		),
		false
	);?>
<?} else {
	LocalRedirect(SITE_DIR."personal/");
}?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>