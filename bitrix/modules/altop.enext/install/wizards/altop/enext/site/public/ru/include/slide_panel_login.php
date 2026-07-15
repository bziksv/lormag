<?$APPLICATION->IncludeComponent("bitrix:system.auth.form", "slide_panel",
	array(
		"REGISTER_URL" => SITE_DIR."personal/private/",
		"FORGOT_PASSWORD_URL" => SITE_DIR."personal/private/",
		"PROFILE_URL" => SITE_DIR."personal/private/",
		"SHOW_ERRORS" => "N"
	),
	false
);?>