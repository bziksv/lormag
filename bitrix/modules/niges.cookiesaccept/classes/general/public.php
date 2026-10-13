<?php
class CNigesCookiesAcceptPublic
{
	/**
	 * Inject cookie notice before </body> so HTML stays valid.
	 * Registered on main::OnEndBufferContent.
	 */
	public static function OnEndBufferContent(&$content)
	{
		if (!is_string($content) || $content === '') {
			return;
		}

		if (!CModule::IncludeModule(cookiesaccept_MODULE_ID)) {
			return;
		}

		if (COption::GetOptionString(cookiesaccept_MODULE_ID, 'ACTIVE', 'N', SITE_ID) !== 'Y') {
			return;
		}

		if (defined('PUBLIC_AJAX_MODE') && PUBLIC_AJAX_MODE === true) {
			return;
		}
		if (isset($_REQUEST['ajax']) && (string)$_REQUEST['ajax'] !== '') {
			return;
		}
		if (isset($_REQUEST['bxajaxid']) && (string)$_REQUEST['bxajaxid'] !== '') {
			return;
		}
		if (defined('ADMIN_SECTION') && ADMIN_SECTION === true) {
			return;
		}

		// Already injected (composite / double buffer)
		if (strpos($content, 'id="nca-cookiesaccept-line"') !== false) {
			return;
		}

		global $APPLICATION;
		ob_start();
		$APPLICATION->IncludeComponent(
			'niges:cookiesaccept',
			'.default',
			array(),
			false,
			array('HIDE_ICONS' => 'Y')
		);
		$html = ob_get_clean();
		if ($html === '') {
			return;
		}

		if (stripos($content, '</body>') !== false) {
			$content = preg_replace('/<\/body>/i', $html . "\n</body>", $content, 1);
		} else {
			$content .= $html;
		}
	}

	/** @deprecated kept for old event registrations until DB is updated */
	public static function OnEpilog()
	{
		// no-op: banner is injected via OnEndBufferContent
	}
}
