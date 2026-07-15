<?$APPLICATION->IncludeComponent("bitrix:system.auth.form", "slide_panel",
	array(
		"REGISTER_URL" => SITE_DIR."personal/",
		"FORGOT_PASSWORD_URL" => SITE_DIR."personal/",
		"PROFILE_URL" => SITE_DIR."personal/",
		"SHOW_ERRORS" => "N"
	),
	false
);?>