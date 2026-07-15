<div class="tabs-wrap">
	<div class="tabs__list" data-entity="main-tabs">
		<div class="container-ws">
			<div class="row">
				<div class="col-xs-12">
					<div class="tabs__scroll">
						<ul class="tabs__tabs">		
							<li class="tabs__tab" data-entity="tab" data-value="recommend">Рекомендуем</li>		
							<li class="tabs__tab" data-entity="tab" data-value="new">Новинки</li>
							<li class="tabs__tab" data-entity="tab" data-value="hit">Хиты продаж</li>						
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="tabs__content" data-entity="main-tabs-content">
		<div class="container-ws">
			<div class="row">
				<div class="col-xs-12">
					<div class="tabs__box" data-entity="tab-content" data-value="recommend">
						<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
							array(
								"AREA_FILE_SHOW" => "file",
								"PATH" => SITE_DIR."include/recommend.php",
								"AREA_FILE_RECURSIVE" => "N",
								"EDIT_MODE" => "html",
							),
							false,
							array("HIDE_ICONS" => "Y")
						);?>
					</div>
					<div class="tabs__box" data-entity="tab-content" data-value="new">		
						<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
							array(
								"AREA_FILE_SHOW" => "file",
								"PATH" => SITE_DIR."include/new.php",
								"AREA_FILE_RECURSIVE" => "N",
								"EDIT_MODE" => "html",
							),
							false,
							array("HIDE_ICONS" => "Y")
						);?>
					</div>
					<div class="tabs__box" data-entity="tab-content" data-value="hit">		
						<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
							array(
								"AREA_FILE_SHOW" => "file",
								"PATH" => SITE_DIR."include/hit.php",
								"AREA_FILE_RECURSIVE" => "N",
								"EDIT_MODE" => "html",
							),
							false,
							array("HIDE_ICONS" => "Y")
						);?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>