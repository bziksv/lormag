<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arServices = array(
	"main" => array(
		"NAME" => GetMessage("SERVICE_MAIN_SETTINGS"),
		"STAGES" => array(
			"files.php",
			"search.php",
			"template.php",
			"menu.php",
			"settings.php",
			"agreement.php"
		)
	),
	"catalog" => Array(
		"NAME" => GetMessage("SERVICE_CATALOG_SETTINGS"),
		"STAGES" => Array(
			"index.php"
		)
	),
	"iblock" => array(
		"NAME" => GetMessage("SERVICE_IBLOCK_DEMO_DATA"),
		"STAGES" => array(
			"types.php",									
			"working_hours.php",
			"markers.php",
			"advantages.php",
			"countries.php",
			"timezones.php",
			"block_contacts.php",
			"block_slider.php",
			"block_banners.php",
			"block_advantages.php",
			"block_services.php",
			"block_gallery.php",
			"block_social.php",
			"objects.php",
			"services.php",
			"news.php",			
			"gallery.php",
			"faq.php",
			"callback.php",
			"feedback.php",
			"callback_objects.php",
			"reviews.php",
			"reviews_objects.php",
			"references.php",
			"references2.php",
			"brands.php",
			"collections.php",
			"props_groups.php",
			"catalog.php",
			"catalog2.php",
			"catalog3.php",
			"catalog4.php",
			"articles.php",
			"promotions.php",
			"not_available.php",
			"ask_price.php",
			"not_available_objects.php",
			"ask_price_objects.php",
			"reviews_catalog.php",
			"smart_filter_seo.php"
		)
	),
	"sale" => array(
		"NAME" => GetMessage("SERVICE_SALE_DEMO_DATA"),
		"STAGES" => array(
			"locations.php",
			"step1.php",
			"step2.php",
			"step3.php"
		)
	)
);?>