<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH."/header.php");

$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/template_styles.min.css");

global $arSettings;
if(!empty($arSettings["SITE_CLOSED_TITLE"]["VALUE"]))
	$APPLICATION->SetTitle($arSettings["SITE_CLOSED_TITLE"]["VALUE"]);

//BLOCK_SITE_CLOSED//
$showCountdown = CEnext::LoadCountdown();?>
<div class="site-closed-wrapper">
	<div class="container">
		<div class="row">
			<div class="col-xs-12">
				<div class="site-closed-outer">
					<div class="site-closed-inner">
						<?if(!empty($arSettings["SITE_CLOSED_TITLE"]["VALUE"])) {?>
							<div class="h1"><?=$arSettings["SITE_CLOSED_TITLE"]["VALUE"]?></div>
						<?}
						if(!empty($arSettings["SITE_CLOSED_DESCRIPTION"]["VALUE"])) {
							echo $arSettings["SITE_CLOSED_DESCRIPTION"]["VALUE"];
						}
						if($showCountdown) {?>
							<div class="site-opening">
								<?if(!empty($arSettings["SITE_OPENING_TITLE"]["VALUE"])) {?>
									<div class="h2"><?=$arSettings["SITE_OPENING_TITLE"]["VALUE"]?></div>
								<?}?>
								<div class="site-opening-timer"></div>
							</div>
						<?}?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?//BLOCK_LOCATION//
if(in_array("LOCATION", $arSettings["HOME_PAGE"]["VALUE"])) {?>
	<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
		array(
			"AREA_FILE_SHOW" => "file",
			"PATH" => SITE_DIR."include/block_location.php"
		),
		false,
		array("HIDE_ICONS" => "Y")
	);?>
<?}

require($_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH."/footer.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>