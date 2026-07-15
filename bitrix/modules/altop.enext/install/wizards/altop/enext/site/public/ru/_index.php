<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("ЭЛЕКТРОСИЛА NEXT - Новый интернет-магазин на 1С-Битрикс");
global $arSettings;

//BLOCK_SLIDER//
if(in_array("SLIDER", $arSettings["HOME_PAGE"]["VALUE"])) {?>
	<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
		array(
			"AREA_FILE_SHOW" => "file",
			"PATH" => SITE_DIR."include/block_slider.php"
		),
		false,
		array("HIDE_ICONS" => "Y")
	);?>
<?}

//BLOCK_BANNERS//
if(in_array("BANNERS", $arSettings["HOME_PAGE"]["VALUE"])) {?>
	<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
		array(
			"AREA_FILE_SHOW" => "file",
			"PATH" => SITE_DIR."include/block_banners.php"
		),
		false,
		array("HIDE_ICONS" => "Y")
	);?>
<?}

//BLOCK_PROMOTIONS//
if(in_array("PROMOTIONS", $arSettings["HOME_PAGE"]["VALUE"])) {?>
	<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
		array(
			"AREA_FILE_SHOW" => "file",
			"PATH" => SITE_DIR."include/block_promotions.php"
		),
		false,
		array("HIDE_ICONS" => "Y")
	);?>
<?}

//BLOCK_ADVANTAGES//
if(in_array("ADVANTAGES", $arSettings["HOME_PAGE"]["VALUE"])) {?>
	<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
		array(
			"AREA_FILE_SHOW" => "file",
			"PATH" => SITE_DIR."include/block_advantages.php"
		),
		false,
		array("HIDE_ICONS" => "Y")
	);?>
<?}

//BLOCK_TABS//
if(in_array("TABS", $arSettings["HOME_PAGE"]["VALUE"])) {
	$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/js/owlCarousel/owl.carousel.min.css");?>
	<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
		array(
			"AREA_FILE_SHOW" => "file",
			"PATH" => SITE_DIR."include/block_tabs.php"
		),
		false,
		array("HIDE_ICONS" => "Y")
	);?>
<?}

//BLOCK_CATALOG_SECTIONS//
if(in_array("SECTIONS", $arSettings["HOME_PAGE"]["VALUE"])) {?>
	<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
		array(
			"AREA_FILE_SHOW" => "file",
			"PATH" => SITE_DIR."include/block_catalog_sections.php"
		),
		false,
		array("HIDE_ICONS" => "Y")
	);?>
<?}

//BLOCK_BRANDS//
if(in_array("BRANDS", $arSettings["HOME_PAGE"]["VALUE"])) {?>
	<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
		array(
			"AREA_FILE_SHOW" => "file",
			"PATH" => SITE_DIR."include/block_brands.php"
		),
		false,
		array("HIDE_ICONS" => "Y")
	);?>
<?}

//BLOCK_SERVICES//
if(in_array("SERVICES", $arSettings["HOME_PAGE"]["VALUE"])) {?>
	<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
		array(
			"AREA_FILE_SHOW" => "file",
			"PATH" => SITE_DIR."include/block_services.php"
		),
		false,
		array("HIDE_ICONS" => "Y")
	);?>
<?}

//BLOCK_CONTENT//
if(in_array("CONTENT", $arSettings["HOME_PAGE"]["VALUE"])) {?>
	<div class="content-wrapper">
		<div class="container">				
			<div class="row content">
				<div class="col-xs-12">
					<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
						array(
							"AREA_FILE_SHOW" => "file",
							"PATH" => SITE_DIR."include/block_content.php"
						),
						false
					);?>
				</div>		
			</div>
		</div>
	</div>
<?}

//BLOCK_GALLERY//
if(in_array("GALLERY", $arSettings["HOME_PAGE"]["VALUE"])) {?>
	<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
		array(
			"AREA_FILE_SHOW" => "file",
			"PATH" => SITE_DIR."include/block_gallery.php"
		),
		false,
		array("HIDE_ICONS" => "Y")
	);?>
<?}

//BLOCK_NEWS//
if(in_array("NEWS", $arSettings["HOME_PAGE"]["VALUE"])) {?>
	<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
		array(
			"AREA_FILE_SHOW" => "file",
			"PATH" => SITE_DIR."include/block_news.php"
		),
		false,
		array("HIDE_ICONS" => "Y")
	);?>
<?}

//BLOCK_ARTICLES//
if(in_array("ARTICLES", $arSettings["HOME_PAGE"]["VALUE"])) {?>
	<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
		array(
			"AREA_FILE_SHOW" => "file",
			"PATH" => SITE_DIR."include/block_articles.php"
		),
		false,
		array("HIDE_ICONS" => "Y")
	);?>
<?}

//BLOCK_LOCATION//
if(in_array("LOCATION", $arSettings["HOME_PAGE"]["VALUE"])) {?>
	<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
		array(
			"AREA_FILE_SHOW" => "file",
			"PATH" => SITE_DIR."include/block_location.php"
		),
		false,
		array("HIDE_ICONS" => "Y")
	);?>
<?}?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>