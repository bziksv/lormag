<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arResult */
/** @var CMain $APPLICATION */

if (method_exists($this, 'setFrameMode')) {
	$this->setFrameMode(true);
}

$cssPath = $this->GetFolder().'/style.css';
$this->addExternalCss($cssPath);
// OnEndBufferContent injects after </head> — link must ship with the markup
$cssFs = $_SERVER['DOCUMENT_ROOT'].$cssPath;
$cssVer = is_file($cssFs) ? (string)filemtime($cssFs) : '1';
$cssHref = htmlspecialcharsbx($cssPath.'?v='.$cssVer);

$cookieName = htmlspecialcharsbx($arResult['COOKIE_NAME']);
$textVer = (int)$arResult['TEXTVER'];
$padding = (int)$arResult['PADDINGSIZE'];
$opacity = htmlspecialcharsbx((string)$arResult['OPACITY']);
$zIndex = (int)$arResult['ZINDEX'];
$topOffset = (int)$arResult['TOP'];
$styleId = (int)$arResult['SETSTYLE'];
$position = htmlspecialcharsbx($arResult['POSITION']);
$animation = htmlspecialcharsbx($arResult['ANIMATION']);
$btnText = htmlspecialcharsbx($arResult['TEXTBTN']);
// MAINTEXT already sanitized in helper — safe subset of HTML
$mainText = $arResult['MAINTEXT'];

$classes = array(
	'nca-cookiesaccept-line',
	'nca-skin-lormag',
	'style-'.$styleId,
	'nca-position-'.$position,
	'nca-animation-'.$animation,
);
if ($arResult['HIDE_MOBILE'] === 'Y') {
	$classes[] = 'nca-hidden-mobile';
}
if ($arResult['HIDE_PC'] === 'Y') {
	$classes[] = 'nca-hidden-pc';
}
if ($arResult['TOPORBOTTOM'] === '1') {
	$classes[] = 'nca-place-top';
} else {
	$classes[] = 'nca-place-bottom';
}

$styleVars = sprintf(
	'--nca-padding:%dpx;--nca-opacity:%s;--nca-zindex:%d;--nca-top:%d%%;',
	$padding,
	$opacity,
	$zIndex,
	$topOffset
);
?>
<link rel="stylesheet" href="<?=$cssHref?>">
<div
	id="nca-cookiesaccept-line"
	class="<?=htmlspecialcharsbx(implode(' ', $classes))?>"
	style="<?=htmlspecialcharsbx($styleVars)?>"
	data-cookie-name="<?=$cookieName?>"
	data-textver="<?=$textVer?>"
	hidden
	role="dialog"
	aria-modal="false"
	aria-label="Уведомление об использовании cookie"
	aria-live="polite"
>
	<div class="nca-bar" id="nca-bar">
		<div class="nca-cookiesaccept-icon" aria-hidden="true">
			<svg viewBox="0 0 40 40" width="40" height="40" focusable="false">
				<circle cx="20" cy="20" r="20" fill="currentColor" opacity="0.14"/>
				<path fill="currentColor" d="M27.2 14.1c-.4 0-.8.1-1.1.3a3.1 3.1 0 0 1-3.3-3.1c0-.2 0-.4.1-.6A7.8 7.8 0 1 0 28 21.4a3.2 3.2 0 0 1-.8-7.3Zm-9.5 2.2a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm-3.2 6.1a1.7 1.7 0 1 1 0 3.4 1.7 1.7 0 0 1 0-3.4Zm7.4 1.2a1.4 1.4 0 1 1 0 2.8 1.4 1.4 0 0 1 0-2.8Zm3.6-5.4a1.2 1.2 0 1 1 0 2.4 1.2 1.2 0 0 1 0-2.4Z"/>
			</svg>
		</div>
		<div class="nca-cookiesaccept-body">
			<div class="nca-cookiesaccept-title">Мы используем cookie</div>
			<div class="nca-cookiesaccept-line-text"><?=$mainText?></div>
		</div>
		<div class="nca-cookiesaccept-line-actions">
			<button type="button" id="nca-cookiesaccept-line-accept-btn" class="nca-accept-btn">
				<span class="nca-accept-btn-icon" aria-hidden="true">
					<svg viewBox="0 0 20 20" width="18" height="18" focusable="false">
						<path fill="currentColor" d="M8.2 14.4 3.8 10l1.4-1.4 3 3 6.6-6.6L16.2 6l-8 8.4Z"/>
					</svg>
				</span>
				<span class="nca-accept-btn-label"><?=$btnText?></span>
			</button>
		</div>
	</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
	(function () {
		if (window !== window.top) {
			return;
		}

		var root = document.getElementById('nca-cookiesaccept-line');
		if (!root) {
			return;
		}

		var cookieName = root.getAttribute('data-cookie-name') || '';
		if (!cookieName) {
			return;
		}

		function readCookie(name) {
			var parts = ('; ' + document.cookie).split('; ' + name + '=');
			if (parts.length === 2) {
				return parts.pop().split(';').shift();
			}
			return '';
		}

		function writeAcceptCookie(name) {
			var maxAge = 60 * 60 * 24 * 400; // ~400 days (browser caps)
			var parts = [
				name + '=Y',
				'path=/',
				'max-age=' + maxAge,
				'SameSite=Lax'
			];
			if (window.location.protocol === 'https:') {
				parts.push('Secure');
			}
			document.cookie = parts.join('; ');
		}

		function hideBanner() {
			if (!root) {
				return;
			}
			root.classList.add('nca-is-hiding');
			window.setTimeout(function () {
				if (root && root.parentNode) {
					root.parentNode.removeChild(root);
				}
			}, 220);
		}

		if (readCookie(cookieName) === 'Y') {
			if (root.parentNode) {
				root.parentNode.removeChild(root);
			}
			return;
		}

		root.hidden = false;
		requestAnimationFrame(function () {
			root.classList.add('nca-is-visible');
		});

		var btn = document.getElementById('nca-cookiesaccept-line-accept-btn');
		if (btn) {
			btn.addEventListener('click', function () {
				writeAcceptCookie(cookieName);
				hideBanner();
			});
		}
	})();
});
</script>
