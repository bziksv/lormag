<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$this->setFrameMode(true);

global $arSettings;

$isSiteClosed = false;
if(COption::GetOptionString("main", "site_stopped") == "Y" && !$USER->CanDoOperation("edit_other_settings"))
	$isSiteClosed = true;

$obName = 'ob'.preg_replace('/[^a-zA-Z0-9_]/', 'x', $this->GetEditAreaId($this->randString()));
$containerName = 'catalog-menu-'.$obName;

if(!$isSiteClosed && !empty($arResult)) {?>
	<div class="catalog-menu catalog-menu-<?=strtolower($arSettings['CATALOG_MENU_INTERFACE_2_0_SUBMENU']['VALUE'])?> catalog-menu-mobile-<?=strtolower($arSettings['CATALOG_MENU_INTERFACE_2_0_SUBMENU_MOBILE']['VALUE'])?>" id="<?=$containerName?>" data-entity="dropdown-menu">
		<div class="hidden-md hidden-lg catalog-menu-title"><div class="catalog-menu-title-icon"><i class="icon-back"></i></div><div class="catalog-menu-title-text"><?=GetMessage("BM_CATALOG_SHORT")?></div></div>
		<ul id="menu">			
			<?$previousLevel = 0;			
			foreach($arResult as $key=> $arItem) {
				if($previousLevel && $arItem["DEPTH_LEVEL"] < $previousLevel){
					if($arItem["PARAMS"]["HIDE_MENU_INDEX"] && $arItem["PARAMS"]["URL"]=='product'){
						echo str_repeat("</ul></div><!--/noindex--></li>", ($previousLevel - $arItem["DEPTH_LEVEL"]));
					}else{
						echo str_repeat("</ul></div></li>", ($previousLevel - $arItem["DEPTH_LEVEL"]));
					}						
				}
				?>
				<?
				if($arItem["PARAMS"]["URL"]=='catalog'){
					if($key==0){
						if($arItem["PARAMS"]["HIDDEN_FROM_ROBOT"]){
							echo '<!--noindex-->';
						}
					}else{
						if($arItem["PARAMS"]["HIDDEN_FROM_ROBOT"] && !$arResult[$key-1]["PARAMS"]["HIDDEN_FROM_ROBOT"]){
							echo '<!--noindex-->';
						}


						if(!$arItem["PARAMS"]["HIDDEN_FROM_ROBOT"] && $arResult[$key-1]["PARAMS"]["HIDDEN_FROM_ROBOT"]){
							echo '<!--/noindex-->';
						}
					}
				}
				?>
				<?
				if($arItem["IS_PARENT"]) {?>

					<li  <?=($arItem["SELECTED"] ? " class='active'" : "")?> data-entity="dropdown" data-txt="<?=$arItem["TEXT"]?>">
						<a href="<?=$arItem['LINK']?>">
							<?if(!empty($arItem["PARAMS"]["ICON"])) {?>
								<span class="catalog-menu-icon"><i class="<?=$arItem['PARAMS']['ICON']?>"></i></span>
							<?} elseif(is_array($arItem["PARAMS"]["PICTURE"])) {?>
								<span class="catalog-menu-pic">
									<img src ="/bitrix/images/arturgolubev.lazyload/pixel.gif" data-lazyload-src="<?=$arItem['PARAMS']['PICTURE']['SRC']?>" width="<?=$arItem['PARAMS']['PICTURE']['WIDTH']?>" height="<?=$arItem['PARAMS']['PICTURE']['HEIGHT']?>" alt="<?=$arItem['PARAMS']['PICTURE']['ALT']?>" title="<?=$arItem['PARAMS']['PICTURE']['TITLE']?>" />
								</span>
							<?}?>
							
							<? if($arItem["PARAMS"]["HIDDEN_FROM_ROBOT"]): ?>
								<span class="catalog-menu-text" data-text="<?=$arItem["TEXT"]?>"></span>
							<? else: ?>
								<span class="catalog-menu-text"><?=$arItem["TEXT"]?></span>
							<? endif; ?>
							
							<span class="catalog-menu-arrow"><i class="icon-arrow-right"></i></span>
						</a>
						<?
						if($arItem["PARAMS"]["URL"]=='product'){
							if($arItem["PARAMS"]["HIDE_MENU_INDEX"]){
								echo '<!--noindex-->';
							}
						}
						?>
						<div class="catalog-menu-dropdown-menu" data-entity="dropdown-menu">
							<ul>						
				<?} else {?>
					<li <?=$arItem["SELECTED"] ? " class='active'" : ""?> data-txt="<?=$arItem["TEXT"]?>">
						<a href="<?=$arItem['LINK']?>">
							<?if(!empty($arItem["PARAMS"]["ICON"])) {?>
								<span class="catalog-menu-icon"><i class="<?=$arItem['PARAMS']['ICON']?>"></i></span>
							<?} elseif(is_array($arItem["PARAMS"]["PICTURE"])) {?>
								<span class="catalog-menu-pic">
									<img src ="/bitrix/images/arturgolubev.lazyload/pixel.gif" data-lazyload-src="<?=$arItem['PARAMS']['PICTURE']['SRC']?>" width="<?=$arItem['PARAMS']['PICTURE']['WIDTH']?>" height="<?=$arItem['PARAMS']['PICTURE']['HEIGHT']?>" alt="<?=$arItem['PARAMS']['PICTURE']['ALT']?>" title="<?=$arItem['PARAMS']['PICTURE']['TITLE']?>" />
								</span>
							<?}?>
							 
							<? if($arItem["PARAMS"]["HIDDEN_FROM_ROBOT"]): ?>
								<span class="catalog-menu-text" data-text="<?=$arItem["TEXT"]?>"></span>
							<? else: ?>
								<span class="catalog-menu-text"><?=$arItem["TEXT"]?></span>
							<? endif; ?>
							
						</a>
					</li>
				<?}
				$previousLevel = $arItem["DEPTH_LEVEL"];
				if($key!=0){
					if($arItem["PARAMS"]["URL"]=='catalog'){
						if($arItem["PARAMS"]["HIDDEN_FROM_ROBOT"] && ($key+1) == count($arResult) ){
							echo '<!--/noindex-->';
						}
					}
				}						
			}
			if($previousLevel > 1){					
				if($arItem["PARAMS"]["HIDE_MENU_INDEX"] && $arItem["PARAMS"]["URL"]=='product'){
					echo str_repeat("</ul></div><!--/noindex--></li>", ($previousLevel - 1));
				}else{
					echo str_repeat("</ul></div></li>", ($previousLevel - 1));
				}					
			}?>
		</ul>
	</div>
	<script type="text/javascript">
		BX.message({
			MAIN_MENU: '<?=GetMessageJS("BM_MAIN_MENU")?>',
			CATALOG_FULL: '<?=GetMessageJS("BM_CATALOG_FULL")?>'
		});
		var <?=$obName?> = new JCCatalogMenu({
			container: '<?=$containerName?>'
		});
	</script>	
<?}?>