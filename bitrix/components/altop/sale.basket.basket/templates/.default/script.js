BasketPoolQuantity = function() {
	this.processing = false;
	this.poolQuantity = {};
	this.updateTimer = null;
	this.currentQuantity = {};
	this.lastStableQuantities = {};

	this.updateQuantity();
};


BasketPoolQuantity.prototype.updateQuantity = function() {	
	var items = BX('basket_items') && BX('basket_items').querySelectorAll('[data-entity="row"]');

	if(basketJSParams['USE_ENHANCED_ECOMMERCE'] === 'Y') {
		checkAnalytics(this.lastStableQuantities, items);
	}
	
	if(!!items && items.length > 0) {
		for(var i = 0; items.length > i; i++) {
			var itemId = items[i].id;
			this.currentQuantity[itemId] = BX('QUANTITY_' + itemId).value;
		}
	}
	
	this.lastStableQuantities = BX.clone(this.currentQuantity, true);
};


BasketPoolQuantity.prototype.changeQuantity = function(itemId) {
	var quantity = BX('QUANTITY_' + itemId).value;
	var isPoolEmpty = this.isPoolEmpty();

	if(this.currentQuantity[itemId] && this.currentQuantity[itemId] != quantity) {
		this.poolQuantity[itemId] = this.currentQuantity[itemId] = quantity;
	}
	
	if(!isPoolEmpty) {
		this.enableTimer(true);
	} else {
		this.trySendPool();
	}
};


BasketPoolQuantity.prototype.trySendPool = function() {	
	if(!this.isPoolEmpty() && !this.isProcessing()) {
		this.enableTimer(false);
		recalcBasketAjax({});
	}
};

BasketPoolQuantity.prototype.isPoolEmpty = function() {
	return(Object.keys(this.poolQuantity).length == 0);
};

BasketPoolQuantity.prototype.clearPool = function() {
	this.poolQuantity = {};
};

BasketPoolQuantity.prototype.isProcessing = function() {
	return (this.processing === true);
};

BasketPoolQuantity.prototype.setProcessing = function(value) {
	this.processing = (value === true);
};

BasketPoolQuantity.prototype.enableTimer = function(value) {
	clearTimeout(this.updateTimer);
	if(value === false)
		return;

	this.updateTimer = setTimeout(function() {
		basketPoolQuantity.trySendPool();
	}, 1500);
};

function updateBasketTable(basketItemId, res) {
	var table = BX('basket_items'),
		rows,
		newBasketItemId,
		arItem,
		lastRow,
		newRow,
		arColumns,
		bShowDeleteColumn = false,
		bShowDelayColumn = false,
		bShowPropsColumn = false,
		bShowPriceType = false,
		bShowArticleColumn = false,		
		bArticleColumnTitle = getColumnName(res, 'PROPERTY_ARTNUMBER_VALUE'),
		bArticleColumnId,		
		bSqMColumn = false,
		bSqMColumnId,
		bUseFloatQuantity,
		origBasketItem,		
		i,		
		image,		
		cellItemHTML,
		bSkip,
		j,
		val,
		propId,
		arProp,				
		arVal,
		valId,
		arSkuValue,
		selected,
		valueId,
		k,
		arItemProp,		
		oCellQuantityHTML,
		ratio,
		isUpdateQuantity,
		isUpdatePcQuantity,
		isUpdateSqMQuantity,
		oldQuantity,
		oldPcQuantity,
		oldSqMQuantity,
		oCellPriceHTML,
		fullPrice,
		id,
		oCellDiscountHTML,
		oCellSumHTML,
		customColumnVal,
		oCellControlHTML,
		arItemInfo,
		arItemControlsContainer,
		arItemControls,
		propsMap,
		selectedIndex,
		counter,		
		createNewItem;

	if(!table || typeof res !== 'object') {
		return;
	}
	
	rows = table.querySelectorAll('[data-entity="row"]')
	lastRow = rows[rows.length - 1];	
	bUseFloatQuantity = (res.PARAMS.QUANTITY_FLOAT === 'Y');

	//insert new row instead of original basket item row	
	if(basketItemId !== null && !!res.BASKET_DATA) {
		origBasketItem = BX(basketItemId);

		newBasketItemId = res.BASKET_ID;
		createNewItem = BX.type.isPlainObject(res.BASKET_DATA.GRID.ROWS[newBasketItemId]);
		if(createNewItem) {
			arItem = res.BASKET_DATA.GRID.ROWS[newBasketItemId];
			newRow = BX.create('DIV', {props: {className: 'basket' + (!!res.BASKET_DATA.OBJECTS ? '-object' : '') + '-item-tr'}, attrs: {'data-entity': 'row'}});
			newRow.setAttribute('id', res.BASKET_ID);			
			newRow.setAttribute('data-item-name', arItem['NAME']);
			newRow.setAttribute('data-item-brand', arItem[basketJSParams['BRAND_PROPERTY'] + '_VALUE'] ? arItem[basketJSParams['BRAND_PROPERTY'] + '_VALUE'] : '');
			newRow.setAttribute('data-item-price', arItem['PRICE']);
			newRow.setAttribute('data-item-currency', arItem['CURRENCY']);
			newRow.setAttribute('data-item-measure', arItem['MEASURE_SYMBOL_INTL'] != null ? arItem['MEASURE_SYMBOL_INTL'] : '');
			
			origBasketItem.parentNode.insertBefore(newRow, origBasketItem.nextSibling);
		}

		if(res.DELETE_ORIGINAL === 'Y') {
			origBasketItem.parentNode.removeChild(origBasketItem);
			if(!createNewItem)
				BX.onCustomEvent('OnBasketChange');
		}
		
		if(createNewItem) {			
			if(!res.BASKET_DATA.OBJECTS) {
				newRow.appendChild(
					BX.create('DIV', {
						props: {
							className: 'hidden-xs basket-item-td basket-item-sep'
						}
					})
				);
			}
				
			arColumns = res.COLUMNS.split(',');

			for(i = 0; i < arColumns.length; i++) {
				if(arColumns[i] === 'DELETE') {
					bShowDeleteColumn = true;
				} else if(arColumns[i] === 'DELAY') {
					bShowDelayColumn = true;
				} else if(arColumns[i] === 'PROPS') {
					bShowPropsColumn = true;
				} else if(arColumns[i] === 'TYPE') {
					bShowPriceType = true;
				} else if(arColumns[i] === 'PROPERTY_ARTNUMBER_VALUE') {
					bShowArticleColumn = true;
					bArticleColumnId = arColumns[i];
				} else if(arColumns[i] === 'PROPERTY_M2_COUNT_VALUE') {
					bSqMColumn = true;
					bSqMColumnId = arColumns[i];
				}
			}
			
			for(i = 0; i < arColumns.length; i++) {
				switch(arColumns[i]) {
					case 'PROPS':
					case 'DELAY':
					case 'DELETE':
					case 'TYPE':
					case 'PROPERTY_ARTNUMBER_VALUE':
					case 'PROPERTY_OBJECT_VALUE':
					case 'PROPERTY_M2_COUNT_VALUE':
					case 'PROPERTY_OLD_PRICE_VALUE':
						break;
					case 'NAME':
						cellItemHTML = '';
						
						if(arItem['PREVIEW_PICTURE_SRC'].length > 0) {
							image = arItem['PREVIEW_PICTURE_SRC'];
						} else if(arItem['DETAIL_PICTURE_SRC'].length > 0) {
							image = arItem['DETAIL_PICTURE_SRC'];
						}

						cellItemHTML = '<div class="basket-item-image-container"><div class="basket-item-image' + (image.length > 0 ? '' : ' basket-item-image-empty') + '">';
						if(image.length > 0) {
							cellItemHTML += '<img src="' + image + '" alt="' + arItem['NAME'] + '" />';
						}
						cellItemHTML += '</div></div>';

						cellItemHTML += '<div class="basket-item-info"><div class="basket-item-title">';
						
						if(bShowArticleColumn) {
							cellItemHTML += '<span class="basket-item-article">';
							cellItemHTML += '<span id="col_' + bArticleColumnId + '">' + bArticleColumnTitle + '</span>: ';
							cellItemHTML += (arItem[bArticleColumnId] ? arItem[bArticleColumnId] : '-') + '</span>';
						}
						
						if(arItem['DETAIL_PAGE_URL'].length > 0)
							cellItemHTML += '<a href="' + arItem['DETAIL_PAGE_URL'] + '">';
						cellItemHTML += arItem['NAME'];
						if(arItem['DETAIL_PAGE_URL'].length > 0)
							cellItemHTML += '</a>';

						if(bShowPropsColumn) {
							for(j = 0; j < arItem['PROPS'].length; j++) {
								val = arItem['PROPS'][j];
								if(arItem['SKU_DATA']) {
									bSkip = false;
									for(propId in arItem['SKU_DATA']) {
										if(arItem['SKU_DATA'].hasOwnProperty(propId)) {
											arProp = arItem['SKU_DATA'][propId];
											if(arProp['CODE'] === val['CODE']) {
												bSkip = true;
												break;
											}
										}
									}
									if(bSkip)
										continue;
								}
								if(val['CODE'] != 'ARTNUMBER')
									cellItemHTML += '<span class="basket-item-prop">' + BX.util.htmlspecialchars(val['NAME']) + ': ' + val['VALUE'] + '</span>';
							}
						}
						
						if(arItem['SKU_DATA']) {
							propsMap = {};
							for(k = 0; k < arItem['PROPS'].length; k++) {
								arItemProp = arItem['PROPS'][k];
								propsMap[arItemProp['CODE']] = (BX.type.isNotEmptyString(arItemProp['~VALUE']) ? arItemProp['~VALUE'] : arItemProp['VALUE']);
							}
							for(propId in arItem['SKU_DATA']) {
								if(arItem['SKU_DATA'].hasOwnProperty(propId)) {
									selectedIndex = 0;
									arProp = arItem['SKU_DATA'][propId];
									counter = 0;
									for(valId in arProp['VALUES']) {
										counter++;
										arVal = arProp['VALUES'][valId];
										if(BX.type.isNotEmptyString(propsMap[arProp['CODE']])) {
											if(propsMap[arProp['CODE']] == arVal['NAME'] || propsMap[arProp['CODE']] == arVal['XML_ID'])
												selectedIndex = counter;
										}
									}

									cellItemHTML += '<div class="basket-item-sku-prop">';
									cellItemHTML += '<div class="basket-item-sku-title">' + BX.util.htmlspecialchars(arProp['NAME']);

									counter = 0;
									for(valueId in arProp['VALUES']) {
										counter++;
										arSkuValue = arProp['VALUES'][valueId];
										if(selectedIndex == counter) {
											if((!!arSkuValue['CODE'] && arSkuValue['CODE'].length > 0) || !!arSkuValue['PICT']) {
												cellItemHTML += '<span>' + BX.util.htmlspecialchars(arSkuValue['NAME']) + '</span>';
												break;
											}
										}
									}

									cellItemHTML += '</div>';
									cellItemHTML += '<ul class="basket-item-sku-list" id="prop_' + arProp['CODE'] + '_' + arItem['ID'] + '">';
									
									counter = 0;
									for(valueId in arProp['VALUES']) {
										counter++;
										arSkuValue = arProp['VALUES'][valueId];
										selected = (selectedIndex == counter ? ' selected' : '');
										
										if((!!arSkuValue['CODE'] && arSkuValue['CODE'].length > 0) || !!arSkuValue['PICT']) {
											style = !!arSkuValue['CODE'] && arSkuValue['CODE'].length > 0
												? ' background-color: #' + arSkuValue['CODE'] + ';'
												: (!!arSkuValue['PICT'] ? ' background-image: url(' + arSkuValue['PICT']['SRC'] + ');' : '');
											cellItemHTML += '<li class="basket-item-sku-item-color' + selected + '" \
												data-sku-selector="Y" \
												data-value-id="' + arSkuValue['XML_ID'] + '" \
												data-sku-name="' + BX.util.htmlspecialchars(arSkuValue['NAME']) + '" \
												data-element="' + arItem['ID'] + '" \
												data-property="' + arProp['CODE'] + '" \
												style="' + style + '"></li>';
										} else {									
											cellItemHTML += '<li class="basket-item-sku-item-text' + selected + '" \
												data-sku-selector="Y" \
												data-value-id="' + (arProp['TYPE'] === 'S' && arProp['USER_TYPE'] === 'directory' ? arSkuValue['XML_ID'] : BX.util.htmlspecialchars(arSkuValue['NAME'])) + '" \
												data-sku-name="' + BX.util.htmlspecialchars(arSkuValue['NAME']) + '" \
												data-element="' + arItem['ID'] + '" \
												data-property="' + arProp['CODE'] + '">' + BX.util.htmlspecialchars(arSkuValue['NAME']) + '</li>';
										}
									}
									
									cellItemHTML += '</ul></div>';
								}
							}
						}
						
						newRow.appendChild(
							BX.create('DIV', {
								props: {
									className: 'basket' + (!!res.BASKET_DATA.OBJECTS ? '-object' : '') + '-item-td basket-item-item'
								},
								html: cellItemHTML
							})
						);
						break;
					case 'QUANTITY':
						oCellQuantityHTML = '';
						
						if(bSqMColumn && arItem[bSqMColumnId] && (arItem['MEASURE_SYMBOL_INTL'] == 'pc. 1' || arItem['MEASURE_SYMBOL_INTL'] == 'm2')) {
							isUpdatePcQuantity = false;
							oldPcQuantity = arItem['PC_QUANTITY'];
							arItem['PC_QUANTITY'] = getCorrectRatioQuantity(arItem['PC_QUANTITY'], arItem['PC_MEASURE_RATIO'], false);
							if(oldPcQuantity != arItem['PC_QUANTITY']) {
								isUpdatePcQuantity = true;
							}

							isUpdateSqMQuantity = false;
							oldSqMQuantity = arItem['SQ_M_QUANTITY'];
							arItem['SQ_M_QUANTITY'] = getCorrectRatioQuantity(arItem['SQ_M_QUANTITY'], arItem['SQ_M_MEASURE_RATIO'], true);
							if(oldSqMQuantity != arItem['SQ_M_QUANTITY']) {
								isUpdateSqMQuantity = true;
							}
							
							oCellQuantityHTML += '<div class="basket-item-amount">';
							oCellQuantityHTML += '<a class="basket-item-amount-btn-minus" href="javascript:void(0)" onclick="setQuantity(\'PC_QUANTITY_INPUT_' + arItem['ID'] + '\', ' + arItem['ID'] + ', ' + arItem['PC_MEASURE_RATIO'] + ', \'down\', false);">-</a>';	
							oCellQuantityHTML += '<input type="text" class="basket-item-amount-input" id="PC_QUANTITY_INPUT_' + arItem['ID'] + '" \
								name="PC_QUANTITY_INPUT_' + arItem['ID'] + '" \
								maxlength="18" \
								value="' + arItem['PC_QUANTITY'] + '" \
								onchange="updatePcQuantity(\'PC_QUANTITY_INPUT_' + arItem['ID'] + '\', ' + arItem['ID'] + ', ' + arItem['PC_MEASURE_RATIO'] + ', false);" \
								data-ratio="' + arItem['PC_MEASURE_RATIO'] + '" \
								/>';
							oCellQuantityHTML += '<a class="basket-item-amount-btn-plus" href="javascript:void(0)" onclick="setQuantity(\'PC_QUANTITY_INPUT_' + arItem['ID'] + '\', ' + arItem['ID'] + ', ' + arItem['PC_MEASURE_RATIO'] + ', \'up\', false);">+</a>';
							oCellQuantityHTML += '<div class="basket-item-amount-measure">' + BX.message('SBB_MEASURE_PC') + '</div>';
							oCellQuantityHTML += '</div>';

							oCellQuantityHTML += '<div class="basket-item-amount">';
							oCellQuantityHTML += '<a class="basket-item-amount-btn-minus" href="javascript:void(0)" onclick="setQuantity(\'SQ_M_QUANTITY_INPUT_' + arItem['ID'] + '\', ' + arItem['ID'] + ', ' + arItem['SQ_M_MEASURE_RATIO'] + ', \'down\', true);">-</a>';	
							oCellQuantityHTML += '<input type="text" class="basket-item-amount-input" id="SQ_M_QUANTITY_INPUT_' + arItem['ID'] + '" \
								name="SQ_M_QUANTITY_INPUT_' + arItem['ID'] + '" \
								maxlength="18" \
								value="' + arItem['SQ_M_QUANTITY'] + '" \
								onchange="updateSqMQuantity(\'SQ_M_QUANTITY_INPUT_' + arItem['ID'] + '\', ' + arItem['ID'] + ', ' + arItem['SQ_M_MEASURE_RATIO'] + ', true);" \
								data-ratio="' + arItem['SQ_M_MEASURE_RATIO'] + '" \
								/>';
							oCellQuantityHTML += '<a class="basket-item-amount-btn-plus" href="javascript:void(0)" onclick="setQuantity(\'SQ_M_QUANTITY_INPUT_' + arItem['ID'] + '\', ' + arItem['ID'] + ', ' + arItem['SQ_M_MEASURE_RATIO'] + ', \'up\', true);">+</a>';
							oCellQuantityHTML += '<div class="basket-item-amount-measure">' + BX.message('SBB_MEASURE_SQ_M') + '</div>';
							oCellQuantityHTML += '</div>';
						} else {
							ratio = parseFloat(arItem['MEASURE_RATIO']) > 0 ? arItem['MEASURE_RATIO'] : 1;
							isUpdateQuantity = false;
							if(ratio != 0 && ratio != '') {
								oldQuantity = arItem['QUANTITY'];
								arItem['QUANTITY'] = getCorrectRatioQuantity(arItem['QUANTITY'], ratio, bUseFloatQuantity);
								if(oldQuantity != arItem['QUANTITY']) {
									isUpdateQuantity = true;
								}
							}
							
							oCellQuantityHTML += '<div class="basket-item-amount">';
							if(ratio != 0 && ratio != '') {
								oCellQuantityHTML += '<a class="basket-item-amount-btn-minus" href="javascript:void(0)" onclick="setQuantity(\'QUANTITY_INPUT_' + arItem['ID'] + '\', ' + arItem['ID'] + ', ' + arItem['MEASURE_RATIO'] + ', \'down\', ' + bUseFloatQuantity + ');">-</a>';
							}
							oCellQuantityHTML += '<input type="text" class="basket-item-amount-input" id="QUANTITY_INPUT_' + arItem['ID'] + '" \
								name="QUANTITY_INPUT_' + arItem['ID'] + '" \
								maxlength="18" \
								value="' + arItem['QUANTITY'] + '" \
								onchange="updateQuantity(\'QUANTITY_INPUT_' + arItem['ID'] + '\', ' + arItem['ID'] + ', ' + ratio + ', ' + bUseFloatQuantity + ')" \
								/>';
							if(ratio != 0 && ratio != '') {
								oCellQuantityHTML += '<a class="basket-item-amount-btn-plus" href="javascript:void(0)" onclick="setQuantity(\'QUANTITY_INPUT_' + arItem['ID'] + '\', ' + arItem['ID'] + ', ' + arItem['MEASURE_RATIO'] + ', \'up\', ' + bUseFloatQuantity + ');">+</a>';
							}

							if(arItem.hasOwnProperty('MEASURE_TEXT') && arItem['MEASURE_TEXT'].length > 0)
								oCellQuantityHTML += '<div class="basket-item-amount-measure">' + BX.util.htmlspecialchars(arItem['MEASURE_TEXT']) + '</div>';

							oCellQuantityHTML += '</div>';
						}

						oCellQuantityHTML += '<input type="hidden" id="QUANTITY_' + arItem['ID'] + '" name="QUANTITY_' + arItem['ID'] + '" value="' + arItem['QUANTITY'] + '" />';

						newRow.appendChild(
							BX.create('DIV', {
								props: {
									className: 'basket' + (!!res.BASKET_DATA.OBJECTS ? '-object' : '') + '-item-td basket-item-quantity'
								},
								html: oCellQuantityHTML
							})
						);

						if(isUpdateQuantity) {
							updateQuantity('QUANTITY_INPUT_' + arItem['ID'], arItem['ID'], ratio, bUseFloatQuantity);
						}
						if(isUpdatePcQuantity) {
							updatePcQuantity('PC_QUANTITY_INPUT_' + arItem['ID'], arItem['ID'], arItem['PC_MEASURE_RATIO'], false);
						} else if(isUpdateSqMQuantity) {
							updateSqMQuantity('SQ_M_QUANTITY_INPUT_' + arItem['ID'], arItem['ID'], arItem['SQ_M_MEASURE_RATIO'], true);
						}
						break;					
					case 'PRICE':
						oCellPriceHTML = '';
						
						fullPrice = arItem['DISCOUNT_PRICE_PERCENT'] > 0 ? (arItem['SQ_M_FULL_PRICE'] ? arItem['SQ_M_FULL_PRICE_FORMATED'] : arItem['FULL_PRICE_FORMATED']) : '';

						oCellPriceHTML += '<div id="current_price_' + arItem['ID'] + '"><span data-entity="price-current">' + (arItem['SQ_M_PRICE'] ? arItem['SQ_M_PRICE_FORMATED'] : arItem['PRICE_FORMATED']) + '</span>';
						if(bSqMColumn && arItem[bSqMColumnId] && (arItem['MEASURE_SYMBOL_INTL'] == 'pc. 1' || arItem['MEASURE_SYMBOL_INTL'] == 'm2')) {
							oCellPriceHTML += '<span>/' + BX.message('SBB_MEASURE_SQ_M') + '</span>';
						}
						oCellPriceHTML += '</div>';
						oCellPriceHTML += '<div class="basket-item-old-price" id="old_price_' + arItem['ID'] + '">' + fullPrice + '</div>';

						if(bShowPriceType && arItem['NOTES'].length > 0) {
							oCellPriceHTML += '<div class="basket-item-type-price">' + BX.message('SBB_TYPE') + '</div>';
							oCellPriceHTML += '<div class="basket-item-type-price-value">' + arItem['NOTES'] + '</div>';
						}
						
						newRow.appendChild(
							BX.create('DIV', {
								props: {
									className: 'hidden-xs hidden-sm basket' + (!!res.BASKET_DATA.OBJECTS ? '-object' : '') + '-item-td'
								},
								html: oCellPriceHTML
							})
						);						
						break;					
					case 'DISCOUNT':
						oCellDiscountHTML = '<div id="discount_value_' + arItem['ID'] + '">' + arItem['DISCOUNT_PRICE_PERCENT_FORMATED'] + '</div>';

						newRow.appendChild(
							BX.create('DIV', {
								props: {
									className: 'hidden-xs hidden-sm basket' + (!!res.BASKET_DATA.OBJECTS ? '-object' : '') + '-item-td basket-item-discount-percent'
								},
								html: oCellDiscountHTML
							})
						);
						break;					
					case 'WEIGHT':
						newRow.appendChild(
							BX.create('DIV', {
								props: {
									className: 'hidden-xs hidden-sm basket' + (!!res.BASKET_DATA.OBJECTS ? '-object' : '') + '-item-td'
								},
								html: arItem['WEIGHT_FORMATED']
							})
						);
						break;
					case 'SUM':
						oCellSumHTML = '<div id="sum_' + arItem['ID'] + '">';
						
						if(typeof(arItem[arColumns[i]]) != 'undefined') {
							oCellSumHTML += arItem[arColumns[i]];
						}

						oCellSumHTML += '</div>';

						newRow.appendChild(
							BX.create('DIV', {
								props: {
									className: 'basket' + (!!res.BASKET_DATA.OBJECTS ? '-object' : '') + '-item-td basket-item-sum'
								},
								html: oCellSumHTML
							})
						);
						break;
					default:
						customColumnVal = '';
					
						if(typeof(arItem[arColumns[i]]) != 'undefined') {
							customColumnVal += arItem[arColumns[i]];
						}
						
						newRow.appendChild(
							BX.create('DIV', {
								props: {
									className: 'hidden-xs hidden-sm basket' + (!!res.BASKET_DATA.OBJECTS ? '-object' : '') + '-item-td'
								},
								html: customColumnVal
							})
						);
						break;
				}
			}
			
			if(bShowDeleteColumn || (!basketJSParams['DISABLE_DELAY'] && bShowDelayColumn)) {
				oCellControlHTML = '';

				if(!basketJSParams['DISABLE_DELAY'] && bShowDelayColumn) {
					oCellControlHTML += '<a class="basket-item-control" \
						href="' + basketJSParams['DELAY_URL'].replace('#ID#', arItem['ID']) + '" \
						title="' + BX.message('SBB_DELAY') + '">\
						<i class="icon-heart"></i></a>';
				}
				if(bShowDeleteColumn) {
					oCellControlHTML += '<a class="basket-item-control" \
						href="' + basketJSParams['DELETE_URL'].replace('#ID#', arItem['ID']) + '" \
						title="' + BX.message('SBB_DELETE') + '">\
						<i class="icon-close"></i></a>';
				}

				newRow.appendChild(
					BX.create('DIV', {
						props: {
							className: 'basket' + (!!res.BASKET_DATA.OBJECTS ? '-object' : '') + '-item-td basket' + (!!res.BASKET_DATA.OBJECTS ? '-object' : '') + '-item-sep'
						},
						style: {
							position: !res.BASKET_DATA.OBJECTS ? 'relative' : ''
						},
						children: [
							BX.create('DIV', {
								props: {
									className: 'basket-item-controls'
								},
								html: oCellControlHTML
							})
						]
					})
				);
			} else {
				if(!res.BASKET_DATA.OBJECTS) {
					newRow.appendChild(
						BX.create('DIV', {
							props: {
								className: 'hidden-xs basket-item-td basket-item-sep'
							}
						})
					);
				}
			}
			
			arItemInfo = BX(arItem['ID']).querySelector('.basket-item-info');
			arItemControlsContainer = BX(arItem['ID']).querySelector('.basket-item-controls');
			arItemControls = !!arItemControlsContainer && arItemControlsContainer.querySelectorAll('.basket-item-control');

			if(!!arItemInfo && !!arItemControls) {
				if(window.innerWidth < 1043)
					BX.style(arItemInfo, 'padding-right', arItemControlsContainer.offsetWidth + 'px');
				else
					BX.style(arItemInfo, 'padding-right', '');
			}
		}		
	}
	
	//update product params after recalculation
	if(!!res.BASKET_DATA) {
		for(id in res.BASKET_DATA.GRID.ROWS) {
			if(res.BASKET_DATA.GRID.ROWS.hasOwnProperty(id)) {
				var item = res.BASKET_DATA.GRID.ROWS[id];

				if(BX('discount_value_' + id))
					BX('discount_value_' + id).innerHTML = item.DISCOUNT_PRICE_PERCENT_FORMATED;

				if(BX('current_price_' + id))
					BX('current_price_' + id).querySelector('[data-entity="price-current"]').innerHTML = item.SQ_M_PRICE ? item.SQ_M_PRICE_FORMATED : item.PRICE_FORMATED;

				if(BX('old_price_' + id))
					BX('old_price_' + id).innerHTML = item.DISCOUNT_PRICE_PERCENT > 0 ? (item.SQ_M_FULL_PRICE ? item.SQ_M_FULL_PRICE_FORMATED : item.FULL_PRICE_FORMATED) : '';

				if(BX('sum_' + id))
					BX('sum_' + id).innerHTML = item.SUM;

				//if the quantity was set by user to 0 or was too much, we need to show corrected quantity value from ajax response
				if(BX('QUANTITY_' + id)) {
					if(BX('QUANTITY_INPUT_' + id)) {
						BX('QUANTITY_INPUT_' + id).value = item.QUANTITY;
						BX('QUANTITY_INPUT_' + id).defaultValue = item.QUANTITY;
					}

					if(BX('PC_QUANTITY_INPUT_' + id) && BX('SQ_M_QUANTITY_INPUT_' + id)) {
						BX('PC_QUANTITY_INPUT_' + id).value = item.PC_QUANTITY;
						BX('PC_QUANTITY_INPUT_' + id).defaultValue = item.PC_QUANTITY;
						
						BX('SQ_M_QUANTITY_INPUT_' + id).value = item.SQ_M_QUANTITY;
						BX('SQ_M_QUANTITY_INPUT_' + id).defaultValue = item.SQ_M_QUANTITY;
					}
					
					BX('QUANTITY_' + id).value = item.QUANTITY;
				}
			}
		}
	}

	//update coupon info
	if(!!res.BASKET_DATA)
		couponListUpdate(res.BASKET_DATA);

	//update warnings if any	
	if(!!res.BASKET_DATA) {
		var warningMessage = BX('warning_message'),
			warningText = [];
		
		if(res.hasOwnProperty('WARNING_MESSAGE')) {
			for(i = 0; i < res['WARNING_MESSAGE'].length; i++) {
				warningText[i] = res['WARNING_MESSAGE'][i];
			}
			
			warningMessage.innerHTML = '<span class="alert alert-warning">' + warningText.join('<br />') + '</span>';
			warningMessage.style.display = 'block';
		} else {
			warningMessage.innerHTML = '';
			warningMessage.style.display = 'none';
		}
	}
	
	//update total basket values
	if(!!res.BASKET_DATA) {
		if(!!res.BASKET_DATA.OBJECTS) {
			for(id in res.BASKET_DATA.OBJECTS) {
				if(res.BASKET_DATA.OBJECTS.hasOwnProperty(id)) {
					var item = res.BASKET_DATA.OBJECTS[id];

					if(BX('allSum_FORMATED_' + id))
						BX('allSum_FORMATED_' + id).innerHTML = item.allSum_FORMATED;

					var showPriceWithoutDiscount = item.PRICE_WITHOUT_DISCOUNT != item.allSum_FORMATED;

					if(BX('PRICE_WITHOUT_DISCOUNT_' + id)) {
						BX('PRICE_WITHOUT_DISCOUNT_' + id).innerHTML = showPriceWithoutDiscount ? item.PRICE_WITHOUT_DISCOUNT : '';
						BX.style(BX('PRICE_WITHOUT_DISCOUNT_' + id), 'display', showPriceWithoutDiscount ? 'block' : 'none');
					}

					if(BX('DISCOUNT_PRICE_ALL_FORMATED_' + id)) {
						BX('DISCOUNT_PRICE_ALL_FORMATED_' + id).innerHTML = showPriceWithoutDiscount ? BX.message('SBB_TOTAL_DISCOUNT') + ' ' + item.DISCOUNT_PRICE_ALL_FORMATED : '';
						BX.style(BX('DISCOUNT_PRICE_ALL_FORMATED_' + id), 'display', showPriceWithoutDiscount ? 'block' : 'none');
					}
				}
			}
		} else {
			if(BX('allWeight_FORMATED'))
				BX('allWeight_FORMATED').innerHTML = res['BASKET_DATA']['allWeight_FORMATED'];

			if(BX('allSum_wVAT_FORMATED'))
				BX('allSum_wVAT_FORMATED').innerHTML = res['BASKET_DATA']['allSum_wVAT_FORMATED'];

			if(BX('allVATSum_FORMATED'))
				BX('allVATSum_FORMATED').innerHTML = res['BASKET_DATA']['allVATSum_FORMATED'];

			if(BX('allSum_FORMATED'))
				BX('allSum_FORMATED').innerHTML = res['BASKET_DATA']['allSum_FORMATED'];
			
			var showPriceWithoutDiscount = (res['BASKET_DATA']['PRICE_WITHOUT_DISCOUNT'] != res['BASKET_DATA']['allSum_FORMATED']);
			
			if(BX('PRICE_WITHOUT_DISCOUNT')) {
				BX('PRICE_WITHOUT_DISCOUNT').innerHTML = showPriceWithoutDiscount ? res['BASKET_DATA']['PRICE_WITHOUT_DISCOUNT'] : '';
				BX.style(BX('PRICE_WITHOUT_DISCOUNT'), 'display', (showPriceWithoutDiscount ? 'block' : 'none'));
			}

			if(BX('DISCOUNT_PRICE_ALL_FORMATED')) {
				BX('DISCOUNT_PRICE_ALL_FORMATED').innerHTML = showPriceWithoutDiscount ? BX.message('SBB_TOTAL_DISCOUNT') + ' ' + res['BASKET_DATA']['DISCOUNT_PRICE_ALL_FORMATED'] : '';
				BX.style(BX('DISCOUNT_PRICE_ALL_FORMATED'), 'display', (showPriceWithoutDiscount ? 'block' : 'none'));
			}

			if(basketJSParams['MIN_ORDER_SUM'] > 0) {
				var btnQuickOrder = document.body.querySelector('[data-entity="quickOrder"]');
				if(!!btnQuickOrder) {
					BX.unbindAll(btnQuickOrder);
					if(res['BASKET_DATA']['allSum'] < parseFloat(basketJSParams['MIN_ORDER_SUM'])) {
						BX.adjust(btnQuickOrder, {props: {disabled: true}});
					} else {
						BX.adjust(btnQuickOrder, {props: {disabled: false}});
						var hasObject = btnQuickOrder.getAttribute('data-has-object') == 'true';
						BX.bind(btnQuickOrder, 'click', function(event) {
							quickOrder(event, hasObject);
						});
					}
				}
				
				var btnCheckOut = document.body.querySelector('[data-entity="checkOut"]');
				if(!!btnCheckOut) {
					BX.unbindAll(btnCheckOut);
					if(res['BASKET_DATA']['allSum'] < parseFloat(basketJSParams['MIN_ORDER_SUM'])) {
						BX.adjust(btnCheckOut, {props: {disabled: true}});
					} else {
						BX.adjust(btnCheckOut, {props: {disabled: false}});
						BX.bind(btnCheckOut, 'click', function() {
							checkOut();
						});
					}
				}
			}
		}
		
		BX.onCustomEvent('OnBasketTableChange');
	}
}

function couponCreate(couponBlock, oneCoupon) {
	var couponClass = 'disabled';

	if(!BX.type.isElementNode(couponBlock))
		return;
	if(oneCoupon.JS_STATUS === 'BAD')
		couponClass = 'bad';
	else if(oneCoupon.JS_STATUS === 'APPLYED')
		couponClass = 'good';

	couponBlock.appendChild(
		BX.create('div', {
			props: {
				className: 'bx_ordercart_coupon'
			},
			children: [
				BX.create('input', {
					props: {					
						type: 'hidden',
						name: 'OLD_COUPON[]',
						value: oneCoupon.COUPON
					}
				}),
				BX.create('span', {
					props: {
						className: 'bx_ordercart_coupon_note ' + couponClass
					},
					html: oneCoupon.COUPON + ' ' + oneCoupon.JS_CHECK_CODE
				}),
				BX.create('span', {
					props: {
						className: 'bx_ordercart_coupon_close_container'
					},
					children: [
						BX.create('span', {
							props: {
								className: 'bx_ordercart_coupon_close'
							},
							attrs: {
								'data-coupon': oneCoupon.COUPON
							}
						})
					]
				})
			]
		})
	);
}

function couponListUpdate(res) {
	var couponBlock,
		couponClass,
		fieldCoupon,
		couponsCollection,
		couponFound,
		couponNote,
		i,
		j,
		key;

	if(!!res && typeof res !== 'object') {
		return;
	}

	couponBlock = BX('coupons_block');
	if(!!couponBlock) {
		if(!!res.COUPON_LIST && BX.type.isArray(res.COUPON_LIST)) {
			fieldCoupon = BX('coupon');
			if(!!fieldCoupon) {
				fieldCoupon.value = '';
			}
			couponsCollection = BX.findChildren(couponBlock, {tagName: 'input', property: {name: 'OLD_COUPON[]'}}, true);
			if(!!couponsCollection) {
				if(BX.type.isElementNode(couponsCollection)) {
					couponsCollection = [couponsCollection];
				}
				for(i = 0; i < res.COUPON_LIST.length; i++) {
					couponFound = false;
					key = -1;
					for(j = 0; j < couponsCollection.length; j++) {
						if(couponsCollection[j].value === res.COUPON_LIST[i].COUPON) {
							couponFound = true;
							key = j;
							couponsCollection[j].couponUpdate = true;
							break;
						}
					}
					if(couponFound) {
						couponClass = 'disabled';
						if(res.COUPON_LIST[i].JS_STATUS === 'BAD')
							couponClass = 'bad';
						else if(res.COUPON_LIST[i].JS_STATUS === 'APPLYED')
							couponClass = 'good';
						
						couponNote = BX.findNextSibling(couponsCollection[key], {className: 'bx_ordercart_coupon_note'});
						if(!!couponNote) {
							BX.adjust(couponNote, {
								props: {
									className: 'bx_ordercart_coupon_note ' + couponClass
								},
								html: res.COUPON_LIST[i].COUPON + ' ' + res.COUPON_LIST[i].JS_CHECK_CODE
							});
						}
					} else {
						couponCreate(couponBlock, res.COUPON_LIST[i]);
					}
				}
				for(j = 0; j < couponsCollection.length; j++) {
					if(typeof(couponsCollection[j].couponUpdate) === 'undefined' || !couponsCollection[j].couponUpdate) {
						BX.remove(couponsCollection[j].parentNode);
						couponsCollection[j] = null;
					} else {
						couponsCollection[j].couponUpdate = null;
					}
				}
			} else {
				for(i = 0; i < res.COUPON_LIST.length; i++) {
					couponCreate(couponBlock, res.COUPON_LIST[i]);
				}
			}
		}
	}
	couponBlock = null;
}

function skuPropClickHandler() {
	var target = this,		
		basketItemId,
		property,
		property_values = {},
		postData = {},
		action_var,
		all_sku_props,
		i,
		sku_prop_value,
		m;

	if(!!target && target.hasAttribute('data-value-id')) {
		BX.showWait();
		
		basketItemId = target.getAttribute('data-element');
		property = target.getAttribute('data-property');
		action_var = BX('action_var').value;

		property_values[property] = BX.util.htmlspecialcharsback(target.getAttribute('data-value-id'));

		//if already selected element is clicked
		if(BX.hasClass(target, 'selected')) {
			BX.closeWait();
			return;
		}

		//get other basket item props to get full unique set of props of the new product
		all_sku_props = BX.findChildren(BX(basketItemId), {tagName: 'ul', className: 'basket-item-sku-list'}, true);
		if(!!all_sku_props && all_sku_props.length > 0) {
			for(i = 0; all_sku_props.length > i; i++) {
				if(all_sku_props[i].id !== 'prop_' + property + '_' + basketItemId) {
					sku_prop_value = BX.findChildren(BX(all_sku_props[i].id), {tagName: 'li', className: 'selected'}, true);
					if(!!sku_prop_value && sku_prop_value.length > 0) {
						for(m = 0; sku_prop_value.length > m; m++) {
							if(sku_prop_value[m].hasAttribute('data-value-id')) {
								property_values[sku_prop_value[m].getAttribute('data-property')] = BX.util.htmlspecialcharsback(sku_prop_value[m].getAttribute('data-value-id'));
							}
						}
					}
				}
			}
		}
		
		postData = {			
			'basketItemId': basketItemId,
			'sessid': BX.bitrix_sessid(),
			'site_id': BX.message('SITE_ID'),
			'props': property_values,
			'action_var': action_var,
			'select_props': BX('column_headers').value,
			'offers_props': BX('offers_props').value,
			'quantity_float': BX('quantity_float').value,
			'price_vat_show_value': BX('price_vat_show_value').value,
			'hide_coupon': BX('hide_coupon').value,
			'use_prepayment': BX('use_prepayment').value,
			'via_ajax': 'Y',
			'template': basketJSParams['SIGNED_TEMPLATE'],
			'signedParamsString': basketJSParams['SIGNED_PARAMS']
		};
			
		postData[action_var] = 'select_item';

		BX.ajax({
			url: '/bitrix/components/altop/sale.basket.basket/ajax.php',
			method: 'POST',
			data: postData,
			dataType: 'json',
			onsuccess: function(result) {
				BX.closeWait();
				updateBasketTable(basketItemId, result);
			}
		});
	}
}

function getColumnName(result, columnCode) {
	if(BX('col_' + columnCode)) {
		return BX.util.trim(BX('col_' + columnCode).innerHTML);
	} else {
		return '';
	}
}

function checkOut() {
	if(!!BX('coupon'))
		BX('coupon').disabled = true;
	BX('basket_form').submit();
	return true;
}

function updateBasket() {
	recalcBasketAjax({});
}

function enterCoupon() {
	var newCoupon = BX('coupon');
	if(!!newCoupon && !!newCoupon.value)
		recalcBasketAjax({'coupon' : newCoupon.value});
}

function updateQuantity(controlId, basketId, ratio, bUseFloatQuantity) {
	var oldVal = BX(controlId).defaultValue,
		newVal = parseFloat(BX(controlId).value) || 0,
		bIsCorrectQuantityForRatio = false,
		autoCalculate = ((BX('auto_calculation') && BX('auto_calculation').value == 'Y') || !BX('auto_calculation'));
	
	if(ratio === 0 || ratio == 1) {
		bIsCorrectQuantityForRatio = true;
	} else {
		var newValInt = newVal * 10000,
			ratioInt = ratio * 10000,
			reminder = newValInt % ratioInt;
		
		if(reminder === 0) {
			bIsCorrectQuantityForRatio = true;
		}
	}

	var bIsQuantityFloat = false;

	if(parseInt(newVal) != parseFloat(newVal)) {
		bIsQuantityFloat = true;
	}

	newVal = bUseFloatQuantity === false && bIsQuantityFloat === false ? parseInt(newVal) : parseFloat(newVal).toFixed(4);
	newVal = correctQuantity(newVal);

	if(bIsCorrectQuantityForRatio) {
		BX(controlId).defaultValue = newVal;
		BX(controlId).value = newVal;
		BX('QUANTITY_' + basketId).value = newVal;
		
		if(autoCalculate) {
			basketPoolQuantity.changeQuantity(basketId);
		}
	} else {
		newVal = getCorrectRatioQuantity(newVal, ratio, bUseFloatQuantity);
		newVal = correctQuantity(newVal);

		if(newVal != oldVal) {
			BX(controlId).defaultValue = newVal;
			BX(controlId).value = newVal;
			BX('QUANTITY_' + basketId).value = newVal;
			
			if(autoCalculate) {
				basketPoolQuantity.changeQuantity(basketId);
			}
		} else {
			BX(controlId).value = oldVal;
		}
	}
}

function updatePcQuantity(controlId, basketId, ratio, bUseFloatQuantity) {
	var item = BX('basket_items').querySelector('[id="' + basketId + '"]'),
		measure = !!item && item.getAttribute('data-item-measure'),
		oldVal = BX(controlId).defaultValue,
		newVal = parseFloat(BX(controlId).value) || 0,
		bIsCorrectQuantityForRatio = false,
		autoCalculate = ((BX('auto_calculation') && BX('auto_calculation').value == 'Y') || !BX('auto_calculation')),
		bIsChangeQuantity = false;
	
	if(ratio === 0 || ratio == 1) {
		bIsCorrectQuantityForRatio = true;
	} else {
		var newValInt = newVal * 10000,
			ratioInt = ratio * 10000,
			reminder = newValInt % ratioInt;

		if(reminder === 0) {
			bIsCorrectQuantityForRatio = true;
		}
	}

	var bIsQuantityFloat = false;

	if(parseInt(newVal) != parseFloat(newVal)) {
		bIsQuantityFloat = true;
	}

	newVal = bUseFloatQuantity === false && bIsQuantityFloat === false ? parseInt(newVal) : parseFloat(newVal).toFixed(4);
	newVal = correctQuantity(newVal);
	
	if(bIsCorrectQuantityForRatio) {
		BX(controlId).defaultValue = newVal;
		BX(controlId).value = newVal;

		if(!!measure && measure == 'pc. 1') {
			BX('QUANTITY_' + basketId).value = newVal;
			bIsChangeQuantity = true;
		}
	} else {
		newVal = getCorrectRatioQuantity(newVal, ratio, bUseFloatQuantity);
		newVal = correctQuantity(newVal);

		if(newVal != oldVal) {
			BX(controlId).defaultValue = newVal;
			BX(controlId).value = newVal;

			if(!!measure && measure == 'pc. 1') {
				BX('QUANTITY_' + basketId).value = newVal;
				bIsChangeQuantity = true;
			}
		} else {
			BX(controlId).value = oldVal;
		}
	}

	var obSqMQuantity = BX('SQ_M_QUANTITY_INPUT_' + basketId),
		oldSqMVal = obSqMQuantity.defaultValue,
		ratioSqM = parseFloat(obSqMQuantity.getAttribute('data-ratio')),
		newSqMVal = parseFloat((BX(controlId).value * ratioSqM) / ratio) || 0,
		bUseFloatSqMQuantity = true,
		bIsCorrectSqMQuantityForRatio = false;
	
	if(ratioSqM === 0 || ratioSqM == 1) {
		bIsCorrectSqMQuantityForRatio = true;
	} else {
		var newSqMValInt = newSqMVal * 10000,
			ratioSqMInt = ratioSqM * 10000,
			reminderSqM = newSqMValInt % ratioSqMInt;

		if(reminderSqM === 0) {
			bIsCorrectSqMQuantityForRatio = true;
		}
	}

	var bIsSqMQuantityFloat = false;

	if(parseInt(newSqMVal) != parseFloat(newSqMVal)) {
		bIsSqMQuantityFloat = true;
	}
	
	newSqMVal = bUseFloatSqMQuantity === false && bIsQuantityFloat === false ? parseInt(newSqMVal) : parseFloat(newSqMVal).toFixed(4);
	newSqMVal = correctQuantity(newSqMVal);
	
	if(bIsCorrectSqMQuantityForRatio) {
		obSqMQuantity.defaultValue = newSqMVal;
		obSqMQuantity.value = newSqMVal;

		if(!!measure && measure == 'm2') {
			BX('QUANTITY_' + basketId).value = newSqMVal;
			bIsChangeQuantity = true;
		}
	} else {
		newSqMVal = getCorrectRatioQuantity(newSqMVal, ratioSqM, bUseFloatSqMQuantity);
		newSqMVal = correctQuantity(newSqMVal);
		
		if(newSqMVal != oldSqMVal) {
			obSqMQuantity.defaultValue = newSqMVal;
			obSqMQuantity.value = newSqMVal;

			if(!!measure && measure == 'm2') {
				BX('QUANTITY_' + basketId).value = newSqMVal;
				bIsChangeQuantity = true;
			}
		} else {
			obSqMQuantity.value = oldSqMVal;
		}
	}

	if(autoCalculate && bIsChangeQuantity)
		basketPoolQuantity.changeQuantity(basketId);
}

function updateSqMQuantity(controlId, basketId, ratio, bUseFloatQuantity) {
	var item = BX('basket_items').querySelector('[id="' + basketId + '"]'),
		measure = !!item && item.getAttribute('data-item-measure'),
		oldVal = BX(controlId).defaultValue,
		newVal = parseFloat(BX(controlId).value) || 0,
		bIsCorrectQuantityForRatio = false,
		autoCalculate = ((BX('auto_calculation') && BX('auto_calculation').value == 'Y') || !BX('auto_calculation')),
		bIsChangeQuantity = false;
	
	if(ratio === 0 || ratio == 1) {
		bIsCorrectQuantityForRatio = true;
	} else {
		var newValInt = newVal * 10000,
			ratioInt = ratio * 10000,
			reminder = newValInt % ratioInt;
		
		if(reminder === 0) {
			bIsCorrectQuantityForRatio = true;
		}
	}

	var bIsQuantityFloat = false;

	if(parseInt(newVal) != parseFloat(newVal)) {
		bIsQuantityFloat = true;
	}

	newVal = bUseFloatQuantity === false && bIsQuantityFloat === false ? parseInt(newVal) : parseFloat(newVal).toFixed(4);
	newVal = correctQuantity(newVal);
	
	if(bIsCorrectQuantityForRatio) {
		BX(controlId).defaultValue = newVal;
		BX(controlId).value = newVal;
		
		if(!!measure && measure == 'm2') {
			BX('QUANTITY_' + basketId).value = newVal;
			bIsChangeQuantity = true;
		}
	} else {		
		newVal = getCorrectRatioQuantity(newVal, ratio, bUseFloatQuantity);		
		newVal = correctQuantity(newVal);		
		
		if(newVal != oldVal) {
			BX(controlId).defaultValue = newVal;
			BX(controlId).value = newVal;

			if(!!measure && measure == 'm2') {
				BX('QUANTITY_' + basketId).value = newVal;
				bIsChangeQuantity = true;
			}
		} else {
			BX(controlId).value = oldVal;
		}
	}
	
	var obPcQuantity = BX('PC_QUANTITY_INPUT_' + basketId),
		oldPcVal = obPcQuantity.defaultValue,
		ratioPc = parseFloat(obPcQuantity.getAttribute('data-ratio')),
		newPcVal = parseFloat((BX(controlId).value * ratioPc) / ratio) || 0,
		bUseFloatPcQuantity = false,
		bIsCorrectPcQuantityForRatio = false;
	
	if(ratioPc === 0 || ratioPc == 1) {
		bIsCorrectPcQuantityForRatio = true;
	} else {
		var newPcValInt = newPcVal * 10000,
			ratioPcInt = ratioPc * 10000,
			reminderPc = newPcValInt % ratioPcInt;

		if(reminderPc === 0) {
			bIsCorrectPcQuantityForRatio = true;
		}
	}

	var bIsPcQuantityFloat = false;

	if(parseInt(newPcVal) != parseFloat(newPcVal)) {
		bIsPcQuantityFloat = true;
	}
	
	newPcVal = bUseFloatPcQuantity === false && bIsQuantityFloat === false ? parseInt(newPcVal) : parseFloat(newPcVal).toFixed(4);
	newPcVal = correctQuantity(newPcVal);

	if(bIsCorrectPcQuantityForRatio) {
		obPcQuantity.defaultValue = newPcVal;
		obPcQuantity.value = newPcVal;

		if(!!measure && measure == 'pc. 1') {
			BX('QUANTITY_' + basketId).value = newPcVal;
			bIsChangeQuantity = true;
		}
	} else {
		newPcVal = getCorrectRatioQuantity(newPcVal, ratioPc, bUseFloatPcQuantity);
		newPcVal = correctQuantity(newPcVal);

		if(newPcVal != oldPcVal) {
			obPcQuantity.defaultValue = newPcVal;
			obPcQuantity.value = newPcVal;

			if(!!measure && measure == 'pc. 1') {
				BX('QUANTITY_' + basketId).value = newPcVal;
				bIsChangeQuantity = true;
			}
		} else {
			obPcQuantity.value = oldPcVal;
		}
	}
	
	if(autoCalculate && bIsChangeQuantity)
		basketPoolQuantity.changeQuantity(basketId);
}

//used when quantity is changed by clicking on arrows
function setQuantity(controlId, basketId, ratio, sign, bUseFloatQuantity) {
	var curVal = parseFloat(BX(controlId).value),
		newVal;

	BX(controlId).defaultValue = curVal;
	
	newVal = sign == 'up' ? curVal + ratio : curVal - ratio;

	if(newVal < 0)
		newVal = 0;
	
	if(bUseFloatQuantity) {
		newVal = parseFloat(newVal).toFixed(4);
	}
	newVal = correctQuantity(newVal);
	
	if(ratio > 0 && newVal < ratio) {
		newVal = ratio;
	}

	if(!bUseFloatQuantity && newVal != newVal.toFixed(4)) {
		newVal = parseFloat(newVal).toFixed(4);
	}

	newVal = getCorrectRatioQuantity(newVal, ratio, bUseFloatQuantity);
	newVal = correctQuantity(newVal);

	BX(controlId).value = newVal;
	
	if(controlId == 'PC_QUANTITY_INPUT_' + basketId) {
		var obSqMQuantity = BX('SQ_M_QUANTITY_INPUT_' + basketId),
			curSqMVal = parseFloat(obSqMQuantity.value),
			ratioSqM = parseFloat(obSqMQuantity.getAttribute('data-ratio')),
			bUseFloatSqMQuantity = true,
			newSqMVal;

		obSqMQuantity.defaultValue = curSqMVal;
		
		newSqMVal = sign == 'up' ? curSqMVal + ratioSqM : curSqMVal - ratioSqM;
		
		if(newSqMVal < 0)
			newSqMVal = 0;

		if(bUseFloatSqMQuantity) {
			newSqMVal = parseFloat(newSqMVal).toFixed(4);
		}
		newSqMVal = correctQuantity(newSqMVal);
		
		if(ratioSqM > 0 && newSqMVal < ratioSqM) {
			newSqMVal = ratioSqM;
		}

		if(!bUseFloatSqMQuantity && newSqMVal != newSqMVal.toFixed(4)) {
			newSqMVal = parseFloat(newSqMVal).toFixed(4);
		}
		
		newSqMVal = getCorrectRatioQuantity(newSqMVal, ratioSqM, bUseFloatSqMQuantity);		
		newSqMVal = correctQuantity(newSqMVal);		

		obSqMQuantity.value = newSqMVal;
		
		updatePcQuantity(controlId, basketId, ratio, bUseFloatQuantity);
	} else if(controlId == 'SQ_M_QUANTITY_INPUT_' + basketId) {
		var obPcQuantity = BX('PC_QUANTITY_INPUT_' + basketId),
			curPcVal = parseFloat(obPcQuantity.value),
			ratioPc = parseFloat(obPcQuantity.getAttribute('data-ratio')),
			bUseFloatPcQuantity = false,
			newPcVal;

		obPcQuantity.defaultValue = curPcVal;

		newPcVal = sign == 'up' ? curPcVal + ratioPc : curPcVal - ratioPc;

		if(newPcVal < 0)
			newPcVal = 0;
		
		if(bUseFloatPcQuantity) {
			newPcVal = parseFloat(newPcVal).toFixed(4);
		}
		newPcVal = correctQuantity(newPcVal);

		if(ratioPc > 0 && newPcVal < ratioPc) {
			newPcVal = ratioPc;
		}

		if(!bUseFloatPcQuantity && newPcVal != newPcVal.toFixed(4)) {
			newPcVal = parseFloat(newPcVal).toFixed(4);
		}
		
		newPcVal = getCorrectRatioQuantity(newPcVal, ratioPc, false);
		newPcVal = correctQuantity(newPcVal);

		obPcQuantity.value = newPcVal;
		
		updateSqMQuantity(controlId, basketId, ratio, bUseFloatQuantity);
	} else {
		updateQuantity(controlId, basketId, ratio, bUseFloatQuantity);
	}
}

function getCorrectRatioQuantity(quantity, ratio, bUseFloatQuantity) {
	var newValInt = quantity * 10000,
		ratioInt = ratio * 10000,
		reminder = (quantity / ratio - ((quantity / ratio).toFixed(0))).toFixed(6),
		result = quantity,
		bIsQuantityFloat = false,
		i;
	ratio = parseFloat(ratio);

	if(reminder == 0) {
		return result;
	}

	if(ratio !== 0 && ratio != 1) {
		for(i = ratio, max = parseFloat(quantity) + parseFloat(ratio); i <= max; i = parseFloat(parseFloat(i) + parseFloat(ratio)).toFixed(4)) {
			result = i;
		}
	} else if(ratio === 1) {
		result = quantity | 0;
	}

	if(parseInt(result, 10) != parseFloat(result)) {
		bIsQuantityFloat = true;
	}

	result = (bUseFloatQuantity === false && bIsQuantityFloat === false) ? parseInt(result, 10) : parseFloat(result).toFixed(4);
	result = correctQuantity(result);
	return result;
}

function correctQuantity(quantity) {
	return parseFloat((quantity * 1).toString());
}

function recalcBasketAjax(params) {
	if(basketPoolQuantity.isProcessing()) {
		return false;
	}
	
	BX.showWait();

	var property_values = {},
		action_var = BX('action_var').value,
		items = BX('basket_items') && BX('basket_items').querySelectorAll('[data-entity="row"]'),
		delayedItems = BX('delayed_items') && BX('delayed_items').querySelectorAll('[data-entity="row"]'),
		postData,
		i;
	
	postData = {
		'sessid': BX.bitrix_sessid(),
		'site_id': BX.message('SITE_ID'),
		'props': property_values,
		'action_var': action_var,
		'select_props': BX('column_headers').value,
		'offers_props': BX('offers_props').value,
		'quantity_float': BX('quantity_float').value,
		'price_vat_show_value': BX('price_vat_show_value').value,
		'hide_coupon': BX('hide_coupon').value,
		'use_prepayment': BX('use_prepayment').value,
		'via_ajax': 'Y',
		'template': basketJSParams['SIGNED_TEMPLATE'],
		'signedParamsString': basketJSParams['SIGNED_PARAMS']
	};
	postData[action_var] = 'recalculate';
	if(!!params && typeof params === 'object') {
		for(i in params) {
			if(params.hasOwnProperty(i))
				postData[i] = params[i];
		}
	}
	
	if(!!items && items.length > 0) {
		for(i = 0; items.length > i; i++)
			postData['QUANTITY_' + items[i].id] = BX('QUANTITY_' + items[i].id).value;
	}

	if(!!delayedItems && delayedItems.length > 0) {
		for(i = 0; delayedItems.length > i; i++)
			postData['DELAY_' + delayedItems[i].id] = 'Y';
	}

	basketPoolQuantity.setProcessing(true);
	basketPoolQuantity.clearPool();

	BX.ajax({
		url: '/bitrix/components/altop/sale.basket.basket/ajax.php',
		method: 'POST',
		data: postData,
		dataType: 'json',
		onsuccess: function(result) {
			BX.closeWait();
			basketPoolQuantity.setProcessing(false);

			if(params.coupon) {
				//hello, gifts!
				if(!!result && !!result.BASKET_DATA && !!result.BASKET_DATA.NEED_TO_RELOAD_FOR_GETTING_GIFTS) {
					BX.reload();
				}
			}

			if(basketPoolQuantity.isPoolEmpty()) {
				updateBasketTable(null, result);
				basketPoolQuantity.updateQuantity();
			} else {
				basketPoolQuantity.enableTimer(true);
			}
		}
	});
}

function showBasketItemsList(val) {
	var tabsContainer = document.body.querySelector('[data-entity="tabs"]'),
		tabs = tabsContainer && tabsContainer.querySelectorAll('[data-entity="tab"]'),		
		tabItems = tabsContainer && tabsContainer.querySelector('[data-value="basket-items"]'),
		tabItemsDelay = tabsContainer && tabsContainer.querySelector('[data-value="basket-items-delayed"]'),
		btnPrint = tabsContainer && tabsContainer.querySelector('[data-entity="print"]'),
		btnClear = tabsContainer && tabsContainer.querySelector('[data-entity="clear"]'),
		btnClearDelay = tabsContainer && tabsContainer.querySelector('[data-entity="clearDelay"]'),
		itemsContainer = BX('basket_items_list'),
		items = BX('basket_items'),
		itemsDelayedContainer = BX('basket_items_delayed'),
		itemsDelayed = BX('delayed_items');
	
	if(tabs) {
		for(var i in tabs) {
			if(tabs.hasOwnProperty(i)) {
				BX.removeClass(tabs[i], 'active');
			}
		}
	}
	
	if(val == 2) {
		if(tabItemsDelay)
			BX.addClass(tabItemsDelay, 'active');

		if(btnClear)
			BX.style(btnClear, 'display', 'none');

		if(itemsDelayed) {
			if(btnPrint)
				BX.style(btnPrint, 'display', 'flex');
			if(btnClearDelay)
				BX.style(btnClearDelay, 'display', 'flex');
		} else {
			if(btnPrint)
				BX.style(btnPrint, 'display', 'none');
			if(btnClearDelay)
				BX.style(btnClearDelay, 'display', 'none');
		}
		
		if(itemsContainer)
			itemsContainer.style.display = 'none';
		if(itemsDelayedContainer)
			itemsDelayedContainer.style.display = 'block';

		if(itemsDelayed)
			checkBasketDelayItemInfoPadding(itemsDelayed);
	} else {
		if(tabItems)
			BX.addClass(tabItems, 'active');

		if(btnClearDelay)
			BX.style(btnClearDelay, 'display', 'none');

		if(items) {
			if(btnPrint)
				BX.style(btnPrint, 'display', 'flex');
			if(btnClear)
				BX.style(btnClear, 'display', 'flex');
		} else {
			if(btnPrint)
				BX.style(btnPrint, 'display', 'none');
			if(btnClear)
				BX.style(btnClear, 'display', 'none');
		}
		
		if(itemsContainer)
			itemsContainer.style.display = 'block';
		if(itemsDelayedContainer)
			itemsDelayedContainer.style.display = 'none';

		if(items)
			checkBasketDelayItemInfoPadding(items);
	}
}

function checkBasketDelayItemInfoPadding(target) {
	if(!BX.isNodeHidden(target)) {
		var items = target.querySelectorAll('[data-entity="row"]');
		
		if(!!items && items.length > 0) {
			for(var i = 0; items.length > i; i++) {
				var itemInfo = items[i].querySelector('.basket-item-info'),
					itemControlsContainer = items[i].querySelector('.basket-item-controls'),
					itemControls = !!itemControlsContainer && itemControlsContainer.querySelectorAll('.basket-item-control');

				if(!!itemInfo && !!itemControls) {
					if(window.innerWidth < 1043)					
						BX.style(itemInfo, 'padding-right', itemControlsContainer.offsetWidth + 'px');
					else
						BX.style(itemInfo, 'padding-right', '');
				}
			}
		}
	}
}

function deleteCoupon() {
	var target = this,
		value;

	if(!!target && target.hasAttribute('data-coupon')) {
		value = target.getAttribute('data-coupon');
		if(!!value && value.length > 0) {
			recalcBasketAjax({'delete_coupon' : value});
		}
	}
}

function deleteProductRow(target) {
	var targetRow = BX.findParent(target, {className: 'basket-item-tr'}),
		quantityNode,
		delItem;

	if(targetRow) {
		quantityNode = BX('QUANTITY_' + targetRow.id);
		if(quantityNode) {
			delItem = getCurrentItemAnalyticsInfo(targetRow, quantityNode.value);
		}
	}

	setAnalyticsDataLayer([], [delItem]);

	document.location.href = target.href;

	return false;
}

function quickOrderRequest(hasObject, sPanel, sPanelContent) {
	BX.ajax({
		url: BX.message('SITE_DIR') + 'ajax/slide_panel.php',
		method: 'POST',
		dataType: 'json',
		timeout: 60,
		data: {
			action: !hasObject ? 'quick_order' : 'quick_order_objects'
		},
		onsuccess: BX.delegate(function(result) {
			if(!result.content || !result.JS) {
				BX.cleanNode(sPanelContent);
				sPanelContent.appendChild(BX.create('DIV', {
					props: {
						className: 'slide-panel__form'
					},
					children: [
						BX.create('DIV', {							
							props: {
								className: 'alert alert-error'
							},
							html: BX.message('SLIDE_PANEL_UNDEFINED_ERROR')
						})
					]
				}));
			} else {
				BX.ajax.processScripts(
					BX.processHTML(result.JS).SCRIPT,
					false,
					BX.delegate(function() {
						var processed = BX.processHTML(result.content),
							temporaryNode = BX.create('DIV');

						temporaryNode.innerHTML = processed.HTML;

						var sPanelTitle = sPanel.querySelector('.slide-panel__title'),
							sPanelFormTitle = temporaryNode.querySelector('.quick-order-form-title');
						if(!!sPanelFormTitle) {
							sPanelTitle.innerHTML = sPanelFormTitle.innerHTML;
							BX.remove(sPanelFormTitle);
						}
						
						sPanelContent.innerHTML = temporaryNode.innerHTML;
						
						BX.ajax.processScripts(processed.SCRIPT);
					}, this)
				);
			}
			
			$(sPanelContent).scrollbar();
		}, this)
	});
}

function quickOrder(e, hasObject) {
	var sPanel = document.querySelector('.slide-panel');
	if(!!sPanel) {
		sPanel.appendChild(
			BX.create('DIV', {
				props: {
					className: 'slide-panel__title-wrap'
				},
				children: [
					BX.create('I', {
						props: {
							className: 'icon-bolt'
						}
					}),						
					BX.create('SPAN', {
						props: {
							className: 'slide-panel__title'
						}
					}),
					BX.create('SPAN', {
						props: {
							className: 'slide-panel__close'
						},
						children: [
							BX.create('I', {
								props: {
									className: 'icon-close'
								}
							})
						]
					})
				]
			})
		);

		sPanel.appendChild(
			BX.create('DIV', {
				props: {
					className: 'slide-panel__content scrollbar-inner'
				},
				children: [
					BX.create('DIV', {
						props: {
							className: 'slide-panel__loader'
						},
						html: '<div><span></span></div>'
					})
				]
			})
		);

		var sPanelContent = sPanel.querySelector('.slide-panel__content');
		if(!!sPanelContent)
			BX.onCustomEvent('quickOrderRequest', [hasObject, sPanel, sPanelContent]);
		
		var scrollWidth = window.innerWidth - document.body.clientWidth;
		if(scrollWidth > 0) {
			BX.style(document.body, 'padding-right', scrollWidth + 'px');
			
			var topPanel = document.querySelector('.top-panel');
			if(!!topPanel) {
				if(BX.hasClass(topPanel, 'fixed'))
					BX.style(topPanel, 'padding-right', scrollWidth + 'px');
				
				var topPanelThead = topPanel.querySelector('.top-panel__thead');
				if(!!topPanelThead && BX.hasClass(topPanelThead, 'fixed'))
					BX.style(topPanelThead, 'padding-right', scrollWidth + 'px');
				
				var topPanelTfoot = topPanel.querySelector('.top-panel__tfoot');
				if(!!topPanelTfoot && BX.hasClass(topPanelTfoot, 'fixed'))
					BX.style(topPanelTfoot, 'padding-right', scrollWidth + 'px');
			}
		}

		var scrollTop = BX.GetWindowScrollPos().scrollTop;
		if(!!scrollTop && scrollTop > 0)
			BX.style(document.body, 'top', '-' + scrollTop + 'px');

		BX.addClass(document.body, 'slide-panel-active')
		BX.addClass(sPanel, 'active');
	
		document.body.appendChild(
			BX.create('DIV', {
				props: {
					className: 'modal-backdrop slide-panel__backdrop fadeInBig'
				}
			})
		);
			
		e.stopPropagation();
	}
}

function checkOutObject(target) {
	var basketObject = BX.findParent(target, {className: 'basket-object'});
	if(!!basketObject) {
		var rows = basketObject.querySelectorAll('[data-entity="row"]');
		if(!!rows) {
			var ids = [];
			for(var i in rows) {
				if(rows.hasOwnProperty(i)) {
					ids[i] = rows[i].id;
				}
			}
			
			BX.ajax({
				url: '/bitrix/components/altop/sale.basket.basket/templates/.default/ajax.php',
				method: 'POST',
				dataType: 'json',
				timeout: 60,
				data: {							
					action: 'checkOutObject',
					objectId: basketObject.getAttribute('data-object-id'),
					ids: ids,
					siteId: BX.message('SITE_ID'),
					siteServerName: BX.message('SITE_SERVER_NAME')
				},
				onsuccess: function(result) {
					if(!!result.status) {
						var iOS = navigator.userAgent.match(/iPhone|iPad|iPod/i);
						if(iOS != null) {
							window.location.href = basketJSParams['PATH_TO_ORDER'] + '?siteId=' + result.siteId;
						} else {
							var newTab = window.open('', '_blank');
							newTab.location = basketJSParams['PATH_TO_ORDER'] + '?siteId=' + result.siteId;
						}
					}

					target.style.width = '';
					target.innerHTML = '<span>' + BX.message(window.innerWidth >= 1043 ? 'SBB_ORDER' : 'SBB_ORDER_SHORT') + '</span>';
				}
			});
		}
	}
}

function checkAnalytics(currentQuantity, newItems) {
	if(!currentQuantity || !newItems || BX.util.array_values(currentQuantity).length === 0)
		return;

	var itemId, diff,
		current = {}, addItems = [], delItems = [],
		i;

	if(!!newItems && newItems.length) {
		for(i = 0; newItems.length > i; i++) {
			itemId = newItems[i].id;
			diff = BX('QUANTITY_' + itemId).value - currentQuantity[itemId];

			if(diff != 0) {
				current = getCurrentItemAnalyticsInfo(newItems[i], diff);

				if(diff > 0) {
					addItems.push(current);
				} else {
					delItems.push(current);
				}
			}
		}
	}

	if(addItems.length || delItems.length) {
		setAnalyticsDataLayer(addItems, delItems);
	}
}

function getCurrentItemAnalyticsInfo(row, diff) {
	if(!row)
		return;

	var temp, k, variants = [];

	var current = {
		'name': row.getAttribute('data-item-name') || '',
		'id': row.id,
		'price': row.getAttribute('data-item-price') || 0,
		'brand': (row.getAttribute('data-item-brand') || '').split(',  ').join('/'),
		'variant': '',
		'quantity': Math.abs(diff)
	};

	temp = row.querySelectorAll('.selected[data-sku-name]');
	for(k = 0; k < temp.length; k++) {
		variants.push(temp[k].getAttribute('data-sku-name'));
	}

	current.variant = variants.join('/');

	return current;
}

function setAnalyticsDataLayer(addItems, delItems) {
	window[basketJSParams['DATA_LAYER_NAME']] = window[basketJSParams['DATA_LAYER_NAME']] || [];

	if(addItems && addItems.length) {
		window[basketJSParams['DATA_LAYER_NAME']].push({
			'event': 'addToCart',
			'ecommerce': {
				'currencyCode': getCurrencyCode(),
				'add': {
					'products': addItems
				}
			}
		});
	}

	if(delItems && delItems.length) {
		window[basketJSParams['DATA_LAYER_NAME']].push({
			'event': 'removeFromCart',
			'ecommerce': {
				'currencyCode': getCurrencyCode(),
				'remove': {
					'products': delItems
				}
			}
		});
	}
}

function getCurrencyCode() {
	var root = BX('basket_items'),
		node,
		currency = '';

	if(root) {
		node = root.querySelector('[data-item-currency');
		node && (currency = node.getAttribute('data-item-currency'));
	}

	return currency;
}

BX.ready(function() {
	if(!basketJSParams['DISABLE_DELAY'] && window.location.search == '?delay=Y')
		showBasketItemsList(2);
	
	basketPoolQuantity = new BasketPoolQuantity();
	
	var tabsList = document.querySelector('.bx-ordercart-tabs-list'),		
		basketItems = BX('basket_items'),
		delayedItems = BX('delayed_items'),
		couponBlock = BX('coupons_block');
	
	if(!!tabsList) {
		BX.addClass(tabsList, 'owl-carousel');
		$(tabsList).owlCarousel({								
			autoWidth: true,
			nav: true,
			navText: ['<i class=\"icon-arrow-left\"></i>', '<i class=\"icon-arrow-right\"></i>'],
			navContainer: '.bx-ordercart-tabs-scroll',
			dots: false,			
		});
	}

	if(BX.type.isElementNode(basketItems)) {
		checkBasketDelayItemInfoPadding(basketItems);
		BX.bind(window, 'resize', function() {
			checkBasketDelayItemInfoPadding(basketItems);
		});
		
		BX.bindDelegate(basketItems, 'click', {tagName: 'li', 'attr': {'data-sku-selector': 'Y'}}, skuPropClickHandler);
	}

	if(BX.type.isElementNode(delayedItems)) {
		checkBasketDelayItemInfoPadding(delayedItems);
		BX.bind(window, 'resize', function() {
			checkBasketDelayItemInfoPadding(delayedItems);
		});
	}
	
	if(BX.type.isElementNode(couponBlock))
		BX.bindDelegate(couponBlock, 'click', {'attribute': 'data-coupon'}, deleteCoupon);
	
	var btnQuickOrder = document.body.querySelector('[data-entity="quickOrder"]');
	if(!!btnQuickOrder && (basketJSParams['OBJECTS'] || (!basketJSParams['OBJECTS'] && (basketJSParams['MIN_ORDER_SUM'] <= 0 || parseFloat(basketJSParams['ORDER_SUM']) >= parseFloat(basketJSParams['MIN_ORDER_SUM']))))) {
		var hasObject = btnQuickOrder.getAttribute('data-has-object') == 'true';
		BX.bind(btnQuickOrder, 'click', function(event) {			
			quickOrder(event, hasObject);
		});
	}
	
	var btnCheckOut = document.body.querySelector('[data-entity="checkOut"]');
	if(!!btnCheckOut && (basketJSParams['MIN_ORDER_SUM'] <= 0 || parseFloat(basketJSParams['ORDER_SUM']) >= parseFloat(basketJSParams['MIN_ORDER_SUM']))) {
		BX.bind(btnCheckOut, 'click', function() {
			checkOut();
		});
	}

	var btnCheckOutObject = document.body.querySelectorAll('[data-entity="checkOutObject"]');
	for(var i in btnCheckOutObject) {
		if(btnCheckOutObject.hasOwnProperty(i) && BX.type.isDomNode(btnCheckOutObject[i])) {
			BX.bind(btnCheckOutObject[i], 'click', function() {
				this.style.width = this.offsetWidth + 'px';
				this.innerHTML = '<span class="btn-loader"><span><span></span></span></span>';
				checkOutObject(this);
			});
		}
	}
	
	BX.addCustomEvent('quickOrderRequest', function(hasObject, sPanel, sPanelContent) {
		quickOrderRequest(hasObject, sPanel, sPanelContent);
	});
	
	BX.addCustomEvent('OnOrderSavedBasketChange', function(result) {
		if(result.DELETED_BASKET_ITEMS) {
			for(var i in result.DELETED_BASKET_ITEMS) {
				if(result.DELETED_BASKET_ITEMS.hasOwnProperty(i)) {
					var basketItem = BX('basket_items').querySelector('[id="' + result.DELETED_BASKET_ITEMS[i] + '"]');
					if(!!basketItem)
						BX.remove(basketItem);
				}
			}

			var basketObjects = BX('basket_items').querySelectorAll('.basket-object');
			if(!!basketObjects) {
				for(var i in basketObjects) {
					if(basketObjects.hasOwnProperty(i)) {
						var basketItems = basketObjects[i].querySelectorAll('[data-entity="row"]');
						if(basketItems.length == 0)
							BX.remove(basketObjects[i]);
					}
				}
			}

			BX.onCustomEvent('OnBasketChange');
		}

		if(result.BASKET_DATA) {
			var personalMenuItems = document.body.querySelectorAll('.personal-menu-item');
			if(!!personalMenuItems) {
				for(var i in personalMenuItems) {
					if(personalMenuItems.hasOwnProperty(i)) {
						if(BX.hasClass(personalMenuItems[i], 'selected')) {
							var personalMenuItemCount = personalMenuItems[i].querySelector('.personal-menu-item-count');
							if(!!personalMenuItemCount)
								personalMenuItemCount.innerHTML = result.BASKET_DATA.ORDERABLE_BASKET_ITEMS_COUNT;
						}
					}
				}
			}

			var tabItems = document.body.querySelector('[data-value="basket-items"]');
			if(!!tabItems) {
				var tabCount = tabItems.querySelector('.bx-ordercart-tab-count');
				if(!!tabCount)
					tabCount.innerHTML = result.BASKET_DATA.ORDERABLE_BASKET_ITEMS_COUNT;
			}

			if(result.BASKET_DATA.ORDERABLE_BASKET_ITEMS_COUNT <= 0) {
				if(result.BASKET_DATA.DELAYED_BASKET_ITEMS_COUNT > 0) {
					BX('basket_items').innerHTML = '<span class="alert alert-warning">' + BX.message('SBB_NO_ITEMS') + '</span>';
				} else {
					BX('basket_items').innerHTML = '<span class="alert alert-warning">' + result.BASKET_DATA.ERROR_MESSAGE + '</span>';
				}
			}
		}
	});
});