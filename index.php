<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("description", "Оториноларингологическое (ЛОР) оборудование по низким ценам в наличии и под заказ по всей территории РФ | Широкий ассортимент товаров для ЛОР-врача | Акции и скидки | ☎️ +7-499-112-02-80");
$APPLICATION->SetPageProperty("title", "Интернет-магазин оториноларингологического (ЛОР) оборудования Лормаг (LorMag)");
$APPLICATION->SetTitle("ЛОР оборудование в наличии и под заказ по всей территории РФ ");
global $arSettings;

//BLOCK_SLIDER//
if(in_array("SLIDER", $arSettings["HOME_PAGE"]["VALUE"])) {?><?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_DIR."include/block_slider.php"
	),
false,
Array(
	'HIDE_ICONS' => 'Y'
)
);?>
<?}

//BLOCK_BANNERS//
if(in_array("BANNERS", $arSettings["HOME_PAGE"]["VALUE"])) {?> <?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_DIR."include/block_banners.php"
	),
false,
Array(
	'HIDE_ICONS' => 'Y'
)
);?> <?}

//BLOCK_PROMOTIONS//
if(in_array("PROMOTIONS", $arSettings["HOME_PAGE"]["VALUE"])) {?> <?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_DIR."include/block_promotions.php"
	),
false,
Array(
	'HIDE_ICONS' => 'Y'
)
);?> <?}

//BLOCK_ADVANTAGES//
if(in_array("ADVANTAGES", $arSettings["HOME_PAGE"]["VALUE"])) {?> <?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_DIR."include/block_advantages.php"
	),
false,
Array(
	'HIDE_ICONS' => 'Y'
)
);?> <?}

//BLOCK_TABS//
if(in_array("TABS", $arSettings["HOME_PAGE"]["VALUE"])) {
	$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/js/owlCarousel/owl.carousel.min.css");?> <?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_DIR."include/block_tabs.php"
	),
false,
Array(
	'HIDE_ICONS' => 'Y'
)
);?> <?}

//BLOCK_CATALOG_SECTIONS//
if(in_array("SECTIONS", $arSettings["HOME_PAGE"]["VALUE"])) {?> <?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_DIR."include/block_catalog_sections.php"
	),
false,
Array(
	'HIDE_ICONS' => 'Y'
)
);?> <?}

//BLOCK_BRANDS//
if(in_array("BRANDS", $arSettings["HOME_PAGE"]["VALUE"])) {?> <?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_DIR."include/block_brands.php"
	),
false,
Array(
	'HIDE_ICONS' => 'Y'
)
);?> <?}

//BLOCK_SERVICES//
if(in_array("SERVICES", $arSettings["HOME_PAGE"]["VALUE"])) {?> <?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_DIR."include/block_services.php"
	),
false,
Array(
	'HIDE_ICONS' => 'Y'
)
);?> <?}

//BLOCK_CONTENT//
if(in_array("CONTENT", $arSettings["HOME_PAGE"]["VALUE"])) {?>
<div class="content-wrapper">
	<div class="container">
		<div class="row content">
			<div class="col-xs-12">
				 <?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_DIR."include/block_content.php"
	)
);?>
			</div>
		</div>
	</div>
</div>
<?}

//BLOCK_GALLERY//
if(in_array("GALLERY", $arSettings["HOME_PAGE"]["VALUE"])) {?>
	<?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_DIR."include/block_gallery.php"
	),
false,
Array(
	'HIDE_ICONS' => 'Y'
)
);?>
<?}

//BLOCK_NEWS//
if(in_array("NEWS", $arSettings["HOME_PAGE"]["VALUE"])) {?>
	<?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_DIR."include/block_news.php"
	),
false,
Array(
	'HIDE_ICONS' => 'Y'
)
);?>
<?}

//BLOCK_ARTICLES//
if(in_array("ARTICLES", $arSettings["HOME_PAGE"]["VALUE"])) {?>
	<?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_DIR."include/block_articles.php"
	),
false,
Array(
	'HIDE_ICONS' => 'Y'
)
);?>
<?}

//BLOCK_LOCATION//
if(in_array("LOCATION", $arSettings["HOME_PAGE"]["VALUE"])) {?>
	<?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_DIR."include/block_location.php"
	),
false,
Array(
	'HIDE_ICONS' => 'Y'
)
);?>
<?}?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>