<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

$this->setFrameMode(false);?>

<div class="hidden-print hidden-xs hidden-sm settings-panel<?=($_COOKIE['settingsPanel'] == 'open' ? ' active' : '');?>">
	<div class="panel-title">
		<?=GetMessage("SETTINGS_PANEL_TITLE")?><span class="switch"><i class="icon-cog-s"></i></span>		
	</div>
	<form method="post" name="settings-panel">
		<?=bitrix_sessid_post();
		foreach($arResult as $optionCode => $arOption) {
			if($arOption["IN_SETTINGS_PANEL"] == "Y") {
				if($optionCode != "COLOR_SCHEME_CUSTOM" && $optionCode != "TOP_PANEL_SCHEME_CUSTOM") {?>
					<div class="panel-block" id="panel-block-<?=$optionCode?>">					
						<div class="block-title"><span><?=$arOption["TITLE"]?></span><i class="icon-plus"></i></div>
						<div class="block-options<?=($arOption['TYPE'] == 'selectbox' ? ' selectbox' : ($arOption['TYPE'] == 'multiselectbox' ? ' multiselectbox' : ''));?>">							
							<?if($optionCode != "COLOR_SCHEME") {
								if($arOption["TYPE"] == "selectbox") {
									foreach($arOption["LIST"] as $variantCode => $arVariant) {
										if($variantCode != "CUSTOM") {?>
											<div class="block-option">
												<input type="radio" id="<?=$optionCode.'_'.$variantCode?>" name="<?=$optionCode?>" <?=$arVariant["CURRENT"] == "Y" ? "checked=\"checked\"" : ""?> value="<?=$variantCode?>" />
												<label for="<?=$optionCode.'_'.$variantCode?>"><?=$arVariant["TITLE"]?></label>
											</div>
										<?} else {																					
											if($arResult['TOP_PANEL_SCHEME']['VALUE'] == 'DARK') {
												$color = $arResult['TOP_PANEL_SCHEME_CUSTOM']['DEFAULT'];
											} elseif($arResult['TOP_PANEL_SCHEME']['VALUE'] == 'SCHEME') {
												foreach($arResult["COLOR_SCHEME"]["LIST"] as $code => $val) {
													if($val["CURRENT"] == "Y")
														$color = $code != "CUSTOM" ? $val["COLOR"] : $arResult['COLOR_SCHEME_CUSTOM']['VALUE'];
												}
												unset($val, $code);
											} else {
												$color = $arResult['TOP_PANEL_SCHEME_CUSTOM']['VALUE'];
											}?>
											<div class="block-option-custom">
												<div class="block-option color" data-color="<?=$color?>">
													<input type="radio" id="<?=$optionCode.'_'.$variantCode?>" name="<?=$optionCode?>" <?=$arVariant["CURRENT"] == "Y" ? "checked=\"checked\"" : ""?> value="<?=$variantCode?>" />
													<label for="<?=$optionCode.'_'.$variantCode?>" title="<?=$arVariant['TITLE']?>" style="background-color:<?=$color?>;"><i class="icon-ok-b"></i></label>
												</div>											
												<input type="text" name="TOP_PANEL_SCHEME_CUSTOM" maxlength="7" value="<?=$color?>" />
												<button type="button" name="palette_button" class="btn btn-primary"><i class="icon-dropper"></i><span><?=GetMessage("SETTINGS_PANEL_PALETTE")?></span></button>
											</div>
											<?unset($color);
										}
									}
									unset($arVariant, $variantCode);
								} elseif($arOption["TYPE"] == "multiselectbox") {
									foreach($arOption["LIST"] as $variantCode => $arVariant) {?>
										<div class="block-option">
											<input type="checkbox" id="<?=$optionCode.'_'.$variantCode?>" name="<?=$optionCode?>[]" <?=$arVariant["CURRENT"] == "Y" ? "checked=\"checked\"" : ""?> value="<?=$variantCode?>" />
											<label for="<?=$optionCode.'_'.$variantCode?>"><span class="check-cont"><span class="check"><i class="icon-ok-b"></i></span></span><span class="check-title"><?=$arVariant["TITLE"]?></span></label>
										</div>
									<?}
									unset($arVariant, $variantCode);
								}
							} else {
								foreach($arOption["LIST"] as $variantCode => $arVariant) {
									if($variantCode != "CUSTOM") {?>
										<div class="block-option color" data-color="<?=$arVariant['COLOR']?>">
											<input type="radio" id="<?=$optionCode.'_'.$variantCode?>" name="<?=$optionCode?>" <?=$arVariant["CURRENT"] == "Y" ? "checked=\"checked\"" : ""?> value="<?=$variantCode?>" />
											<label for="<?=$optionCode.'_'.$variantCode?>" title="<?=$arVariant['TITLE']?>" style="background-color:<?=$arVariant['COLOR']?>;"><i class="icon-ok-b"></i></label>
										</div>
									<?} else {										
										foreach($arResult["COLOR_SCHEME"]["LIST"] as $code => $val) {
											if($val["CURRENT"] == "Y")
												$color = $code != "CUSTOM" ? $val["COLOR"] : $arResult['COLOR_SCHEME_CUSTOM']['VALUE'];
										}
										unset($val, $code);?>
										<div class="block-option-custom">
											<div class="block-option color" data-color="<?=$color?>">
												<input type="radio" id="<?=$optionCode.'_'.$variantCode?>" name="<?=$optionCode?>" <?=$arVariant["CURRENT"] == "Y" ? "checked=\"checked\"" : ""?> value="<?=$variantCode?>" />
												<label for="<?=$optionCode.'_'.$variantCode?>" title="<?=$arVariant['TITLE']?>" style="background-color:<?=$color?>;"><i class="icon-ok-b"></i></label>
											</div>											
											<input type="text" name="COLOR_SCHEME_CUSTOM" maxlength="7" value="<?=$color?>" />
											<button type="button" name="palette_button" class="btn btn-primary"><i class="icon-dropper"></i><span><?=GetMessage("SETTINGS_PANEL_PALETTE")?></span></button>
										</div>
										<?unset($color);
									}
								}
								unset($arVariant, $variantCode);
							}?>								
						</div>
					</div>
				<?}
			} else {?>
				<input type="hidden" name="<?=$optionCode?>" value="<?=strtr(base64_encode(serialize($arOption['VALUE'])), '+/=', '-_,')?>" />
			<?}
		}
		unset($arOption, $optionCode);?>
		<div class="panel-block reset">
			<button type="button" name="reset_button" class="btn btn-reset"><i class="icon-repeat"></i><span><?=GetMessage("SETTINGS_PANEL_RESET")?></span></button>
		</div>
	</form>
</div>

<script type="text/javascript">
	$(function() {
		//SHOW_HIDE_SETTINGS_PANEL//
		if($.cookie('settingsPanel') == 'open')
			$('.settings-panel').addClass('active');
		
		$('.settings-panel .switch').on('click', function() {			
			var panel = $(this).closest('.settings-panel'),
				isPanelActive = panel.hasClass('active');
			if(!!isPanelActive) {			
				panel.animate({right: '-' + panel.outerWidth() + 'px'}, 300).removeClass('active');
				$.removeCookie('settingsPanel', {path: '/'});
			} else {
				panel.animate({right: '0'}, 300).addClass('active');				
				$.cookie('settingsPanel', 'open', {path: '/'});
			}
		});
		
		//SHOW_HIDE_BLOCK_OPTIONS//
		<?foreach($arResult as $optionCode => $arOption) {
			if($arOption['IN_SETTINGS_PANEL'] == 'Y') {
				if($optionCode != "COLOR_SCHEME_CUSTOM" && $optionCode != "TOP_PANEL_SCHEME_CUSTOM") {?>
					if($.cookie('panel-block-<?=$optionCode?>') == 'open') {					
						$('#panel-block-<?=$optionCode?>').children('.block-title').addClass('active').children('i').removeClass('icon-plus').addClass('icon-minus').closest('.block-title').siblings('.block-options').show();
					}					
					$('#panel-block-<?=$optionCode?> .block-title').on('click', function() {
						var clickitem = $(this),
							isClickitemActive = clickitem.hasClass('active');					
						if(!!isClickitemActive) {
							clickitem.removeClass('active').children('i').removeClass('icon-minus').addClass('icon-plus');
							$.removeCookie('panel-block-<?=$optionCode?>', {path: '/'});
						} else {
							clickitem.addClass('active').children('i').removeClass('icon-plus').addClass('icon-minus');
							$.cookie('panel-block-<?=$optionCode?>', 'open', {path: '/'});
						}
						clickitem.siblings('.block-options').slideToggle();					
					});
				<?}
			}
		}?>
		
		//VARIABLES//
		var formPanel = $('form[name="settings-panel"]'),
			formPanelActionInput = '<input type="hidden" name="action" value="change_theme" />';
		
		//SPECTRUM//		
		$('.settings-panel .block-option-custom').each(function() {
			var custom = $(this),
				customDiv = custom.find('.block-option'),
				customDivLabel = customDiv.find('label'),
				customInput = custom.find('input[type="text"]'),
				customBtn = custom.find('button');
			
			customBtn.spectrum({				
				clickoutFiresChange: false,
				cancelText: '<?=GetMessage("SETTINGS_PANEL_PALETTE_CHANCEL")?>',
				chooseText: '<?=GetMessage("SETTINGS_PANEL_PALETTE_CHOOSE_COLOR")?>',
				containerClassName: 'palette_cont',				
				move: function(color) {
					var hex = color.toHexString();
					customDiv.attr('data-color', hex);
					customDivLabel.css({'background-color': hex});
					customInput.val(hex);						
				},
				hide: function(color) {
					var hex = color.toHexString();						
					customDiv.attr('data-color', hex);
					customDivLabel.css({'background-color': hex});
					customInput.val(hex);						
				},
				change: function(color) {
					customDiv.find('input[type="radio"]').prop('checked', true);						
					formPanel.append(formPanelActionInput);
					formPanel.submit();
				}
			});
			customBtn.spectrum('set', customInput.val());
		});		
		
		//CHANGE_INPUT//
		$('.settings-panel .block-option input').on('change', function() {
			formPanel.append(formPanelActionInput);
			formPanel.submit();
		});
		
		//CHANGE_CUSTOM_INPUT//		
		function CheckColor(color) {
			color = color.replace(/#/g, '');
			if(color.length < 6) {
				if(color.length != 3) {
					for($i = 0, $l = 6 - color.length; $i < $l; ++$i) {
						color = color + '0';
					}					
				}
			} else if(color.length > 6) {
				color = color.slice(0, -(color.length - 6));	
			}
			color = '#' + color;
			return color;
		}
		$('.settings-panel .block-option-custom').each(function() {
			var custom = $(this),
				customDiv = custom.find('.block-option'),
				customDivLabel = customDiv.find('label'),
				customInput = custom.find('input[type="text"]'),
				customBtn = custom.find('button'),
				activeColor = customInput.val();
			
			customInput.on('change', function() {			
				var hex = $(this).val();
				if(hex.length > 0) {
					hex = CheckColor(hex);				
					$(this).val(hex);
					customDiv.attr('data-color', hex);
					customDivLabel.css({'background-color': hex});
					customBtn.spectrum('set', hex);
				} else {					
					$(this).val(activeColor);
					customDiv.attr('data-color', activeColor);
					customDivLabel.css({'background-color': activeColor});
					customBtn.spectrum('set', activeColor);					
				}
			});
			customInput.on('keypress', function(e) {
				if(e.keyCode == 13) {
					e.preventDefault();
					var hex = $(this).val();
					if(hex.length > 0) {
						hex = CheckColor(hex);				
						$(this).val(hex);
						customDiv.attr('data-color', hex);
						customDivLabel.css({'background-color': hex});					
					} else {
						$(this).val(activeColor);
					}
					customDiv.find('input[type="radio"]').prop('checked', true);
					formPanel.append(formPanelActionInput);
					formPanel.submit();
				}
			});
		});
		
		//RESET_BUTTON//
		$('.settings-panel button[name="reset_button"]').on('click', function() {
			formPanel.append('<input type="hidden" name="THEME" value="default" />');
			formPanel.append(formPanelActionInput);
			formPanel.submit();
		});

		//FORM_SUBMIT//
		formPanel.submit(function(e) {
			e.preventDefault();

			var $form = $(e.target);
			
			$.ajax({
				url: '<?=$componentPath?>/ajax.php',
				type: 'POST',
				data: $form.serialize() + '&SITE_ID=<?=SITE_ID?>',
				success: function() {
					window.location.reload();
				}
			});
		});
	});
</script>