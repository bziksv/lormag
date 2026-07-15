;(function() {
	'use strict';

	BX.namespace('BX.Sale.BasketComponent');

	BX.Sale.BasketComponent = {
		init: function(parameters) {
			this.container = document.body.querySelector('[data-entity="basketContainer"]');

			this.params = parameters.params || {};
			this.template = parameters.template || '';
			this.signedParamsString = parameters.signedParamsString || '';
			this.templateFolder = parameters.templateFolder || '';
			
			this.initializeActionPool();

			BX.bindDelegate(this.container, 'click', {tagName: 'li', 'attr': {'data-sku-selector': 'Y'}}, BX.proxy(this.skuPropClickHandler, this));

			this.btnQuickOrder = this.container.querySelector('[data-entity="quickOrder"]');
			if(!!this.btnQuickOrder && this.btnQuickOrder.disabled != true)
				BX.bind(this.btnQuickOrder, 'click', BX.proxy(this.quickOrder, this));
			
			this.btnCheckOut = this.container.querySelector('[data-entity="checkOut"]');
			if(!!this.btnCheckOut && this.btnCheckOut.disabled != true)
				BX.bind(this.btnCheckOut, 'click', BX.proxy(this.checkOutAction, this));
			
			var btnCheckOutObject = this.container.querySelectorAll('[data-entity="checkOutObject"]');
			for(var i in btnCheckOutObject) {
				if(btnCheckOutObject.hasOwnProperty(i) && BX.type.isDomNode(btnCheckOutObject[i]))
					BX.bind(btnCheckOutObject[i], 'click', BX.proxy(this.checkOutObjectAction, this));
			}
			
			BX.addCustomEvent(this, 'quickOrderRequest', BX.proxy(this.quickOrderRequest, this));

			BX.addCustomEvent('OnOrderSavedBasketChange', BX.proxy(this.basketChange, this));
		},
			
		initializeActionPool: function() {
			this.actionPool = new BX.Sale.BasketActionPool(this);
		},
			
		updateBasketTable: function(basketItemId, res) {
			var rows,
				newBasketItemId,
				arItem,
				lastRow,
				newRow,
				arColumns,
				bShowPropsColumn = false,
				bShowDeleteColumn = false,
				bShowArticleColumn = false,		
				bArticleColumnTitle = this.getColumnName('PROPERTY_ARTNUMBER_VALUE'),
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
				style,
				createNewItem;

			if(typeof res !== 'object') {
				return;
			}

			rows = this.container.querySelectorAll('[data-entity="row"]');
			lastRow = rows[rows.length - 1];
			bUseFloatQuantity = (res.PARAMS.QUANTITY_FLOAT === 'Y');
			
			//insert new row instead of original basket item row	
			if(basketItemId !== null && !!res.BASKET_DATA) {
				origBasketItem = this.container.querySelector('[data-id="' + basketItemId + '"]');
				
				newBasketItemId = res.BASKET_ID;				
				createNewItem = BX.type.isPlainObject(res.BASKET_DATA.GRID.ROWS[newBasketItemId]);				
				if(createNewItem) {
					arItem = res.BASKET_DATA.GRID.ROWS[newBasketItemId];
					newRow = BX.create('DIV', {props: {className: 'slide-panel-basket' + (!!res.BASKET_DATA.OBJECTS ? '-object' : '') + '-item-tr'}, attrs: {'data-entity': 'row'}});
					newRow.setAttribute('data-id', res.BASKET_ID);
					newRow.setAttribute('data-item-measure', arItem['MEASURE_SYMBOL_INTL'] != null ? arItem['MEASURE_SYMBOL_INTL'] : '');
					
					origBasketItem.parentNode.insertBefore(newRow, origBasketItem.nextSibling);
				}

				if(res.DELETE_ORIGINAL === 'Y') {
					origBasketItem.parentNode.removeChild(origBasketItem);
					if(!createNewItem)
						BX.onCustomEvent('OnBasketChange');
				}
				
				if(createNewItem) {
					arColumns = res.COLUMNS.split(',');

					for(i = 0; i < arColumns.length; i++) {
						if(arColumns[i] === 'PROPS') {
							bShowPropsColumn = true;
						} else if(arColumns[i] === 'DELETE') {
							bShowDeleteColumn = true;
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
							case 'NAME':
								cellItemHTML = '';
								
								if(arItem['PREVIEW_PICTURE_SRC'].length > 0) {
									image = arItem['PREVIEW_PICTURE_SRC'];
								} else if(arItem['DETAIL_PICTURE_SRC'].length > 0) {
									image = arItem['DETAIL_PICTURE_SRC'];
								} else {
									image = this.templateFolder + '/images/no_photo.png';
								}

								cellItemHTML = '<div class="slide-panel-basket-item-image-container"><div class="slide-panel-basket-item-image">';
								if(image.length > 0) {
									cellItemHTML += '<img src="' + image + '" alt="' + arItem['NAME'] + '" />';
								}

								cellItemHTML += '<div id="discount_value_' + arItem['ID'] + '" class="slide-panel-basket-item-marker" style="display: ' + (arItem['DISCOUNT_PRICE_PERCENT'] > 0 ? '' : 'none') + ';">' + (arItem['DISCOUNT_PRICE_PERCENT'] > 0 ? '-' + arItem['DISCOUNT_PRICE_PERCENT'] + '%' : '') + '</div>';
								
								cellItemHTML += '</div></div>';

								cellItemHTML += '<div class="slide-panel-basket-item-info"><div class="slide-panel-basket-item-title">';
								
								if(bShowArticleColumn) {
									cellItemHTML += '<span class="slide-panel-basket-item-article">';
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
											cellItemHTML += '<span class="slide-panel-basket-item-prop">' + BX.util.htmlspecialchars(val['NAME']) + ': ' + val['VALUE'] + '</span>';
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

											cellItemHTML += '<div class="slide-panel-basket-item-sku-prop">';
											cellItemHTML += '<div class="slide-panel-basket-item-sku-title">' + BX.util.htmlspecialchars(arProp['NAME']);

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
											cellItemHTML += '<ul class="slide-panel-basket-item-sku-list" id="prop_' + arProp['CODE'] + '_' + arItem['ID'] + '">';
											
											counter = 0;
											for(valueId in arProp['VALUES']) {
												counter++;
												arSkuValue = arProp['VALUES'][valueId];
												selected = (selectedIndex == counter ? ' selected' : '');
												
												if((!!arSkuValue['CODE'] && arSkuValue['CODE'].length > 0) || !!arSkuValue['PICT']) {
													style = !!arSkuValue['CODE'] && arSkuValue['CODE'].length > 0
														? ' background-color: #' + arSkuValue['CODE'] + ';'
														: (!!arSkuValue['PICT'] ? ' background-image: url(' + arSkuValue['PICT']['SRC'] + ');' : '');
													cellItemHTML += '<li class="slide-panel-basket-item-sku-item-color' + selected + '" \
														data-sku-selector="Y" \
														data-value-id="' + arSkuValue['XML_ID'] + '" \
														data-sku-name="' + BX.util.htmlspecialchars(arSkuValue['NAME']) + '" \
														data-element="' + arItem['ID'] + '" \
														data-property="' + arProp['CODE'] + '" \
														style="' + style + '"></li>';
												} else {									
													cellItemHTML += '<li class="slide-panel-basket-item-sku-item-text' + selected + '" \
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
											className: 'slide-panel-basket' + (!!res.BASKET_DATA.OBJECTS ? '-object' : '') + '-item-td slide-panel-basket-item-item'
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
									arItem['PC_QUANTITY'] = this.getCorrectRatioQuantity(arItem['PC_QUANTITY'], arItem['PC_MEASURE_RATIO'], false);
									if(oldPcQuantity != arItem['PC_QUANTITY']) {
										isUpdatePcQuantity = true;
									}

									isUpdateSqMQuantity = false;
									oldSqMQuantity = arItem['SQ_M_QUANTITY'];
									arItem['SQ_M_QUANTITY'] = this.getCorrectRatioQuantity(arItem['SQ_M_QUANTITY'], arItem['SQ_M_MEASURE_RATIO'], true);
									if(oldSqMQuantity != arItem['SQ_M_QUANTITY']) {
										isUpdateSqMQuantity = true;
									}
									
									oCellQuantityHTML += '<div class="slide-panel-basket-item-amount">';
									oCellQuantityHTML += '<a class="slide-panel-basket-item-amount-btn-minus" href="javascript:void(0)" onclick="BX.Sale.BasketComponent.setQuantity(\'PC_QUANTITY_INPUT_' + arItem['ID'] + '\', ' + arItem['ID'] + ', ' + arItem['PC_MEASURE_RATIO'] + ', \'down\', false);">-</a>';	
									oCellQuantityHTML += '<input type="text" class="slide-panel-basket-item-amount-input" id="PC_QUANTITY_INPUT_' + arItem['ID'] + '" \
										name="PC_QUANTITY_INPUT_' + arItem['ID'] + '" \
										maxlength="18" \
										value="' + arItem['PC_QUANTITY'] + '" \
										onchange="BX.Sale.BasketComponent.updatePcQuantity(\'PC_QUANTITY_INPUT_' + arItem['ID'] + '\', ' + arItem['ID'] + ', ' + arItem['PC_MEASURE_RATIO'] + ', false);" \
										data-ratio="' + arItem['PC_MEASURE_RATIO'] + '" \
										/>';
									oCellQuantityHTML += '<a class="slide-panel-basket-item-amount-btn-plus" href="javascript:void(0)" onclick="BX.Sale.BasketComponent.setQuantity(\'PC_QUANTITY_INPUT_' + arItem['ID'] + '\', ' + arItem['ID'] + ', ' + arItem['PC_MEASURE_RATIO'] + ', \'up\', false);">+</a>';
									oCellQuantityHTML += '<div class="slide-panel-basket-item-amount-measure">' + BX.message('SBB_SLIDE_PANEL_MEASURE_PC') + '</div>';
									oCellQuantityHTML += '</div>';

									oCellQuantityHTML += '<div class="slide-panel-basket-item-amount">';
									oCellQuantityHTML += '<a class="slide-panel-basket-item-amount-btn-minus" href="javascript:void(0)" onclick="BX.Sale.BasketComponent.setQuantity(\'SQ_M_QUANTITY_INPUT_' + arItem['ID'] + '\', ' + arItem['ID'] + ', ' + arItem['SQ_M_MEASURE_RATIO'] + ', \'down\', true);">-</a>';	
									oCellQuantityHTML += '<input type="text" class="slide-panel-basket-item-amount-input" id="SQ_M_QUANTITY_INPUT_' + arItem['ID'] + '" \
										name="SQ_M_QUANTITY_INPUT_' + arItem['ID'] + '" \
										maxlength="18" \
										value="' + arItem['SQ_M_QUANTITY'] + '" \
										onchange="BX.Sale.BasketComponent.updateSqMQuantity(\'SQ_M_QUANTITY_INPUT_' + arItem['ID'] + '\', ' + arItem['ID'] + ', ' + arItem['SQ_M_MEASURE_RATIO'] + ', true);" \
										data-ratio="' + arItem['SQ_M_MEASURE_RATIO'] + '" \
										/>';
									oCellQuantityHTML += '<a class="slide-panel-basket-item-amount-btn-plus" href="javascript:void(0)" onclick="BX.Sale.BasketComponent.setQuantity(\'SQ_M_QUANTITY_INPUT_' + arItem['ID'] + '\', ' + arItem['ID'] + ', ' + arItem['SQ_M_MEASURE_RATIO'] + ', \'up\', true);">+</a>';
									oCellQuantityHTML += '<div class="slide-panel-basket-item-amount-measure">' + BX.message('SBB_SLIDE_PANEL_MEASURE_SQ_M') + '</div>';
									oCellQuantityHTML += '</div>';
								} else {
									ratio = parseFloat(arItem['MEASURE_RATIO']) > 0 ? arItem['MEASURE_RATIO'] : 1;
									isUpdateQuantity = false;
									if(ratio != 0 && ratio != '') {
										oldQuantity = arItem['QUANTITY'];
										arItem['QUANTITY'] = this.getCorrectRatioQuantity(arItem['QUANTITY'], ratio, bUseFloatQuantity);
										if(oldQuantity != arItem['QUANTITY']) {
											isUpdateQuantity = true;
										}
									}
									
									oCellQuantityHTML += '<div class="slide-panel-basket-item-amount">';
									if(ratio != 0 && ratio != '') {
										oCellQuantityHTML += '<a class="slide-panel-basket-item-amount-btn-minus" href="javascript:void(0)" onclick="BX.Sale.BasketComponent.setQuantity(\'QUANTITY_INPUT_' + arItem['ID'] + '\', ' + arItem['ID'] + ', ' + arItem['MEASURE_RATIO'] + ', \'down\', ' + bUseFloatQuantity + ');">-</a>';
									}
									oCellQuantityHTML += '<input type="text" class="slide-panel-basket-item-amount-input" id="QUANTITY_INPUT_' + arItem['ID'] + '" \
										name="QUANTITY_INPUT_' + arItem['ID'] + '" \
										maxlength="18" \
										value="' + arItem['QUANTITY'] + '" \
										onchange="BX.Sale.BasketComponent.updateQuantity(\'QUANTITY_INPUT_' + arItem['ID'] + '\', ' + arItem['ID'] + ', ' + ratio + ', ' + bUseFloatQuantity + ')" \
										/>';
									if(ratio != 0 && ratio != '') {
										oCellQuantityHTML += '<a class="slide-panel-basket-item-amount-btn-plus" href="javascript:void(0)" onclick="BX.Sale.BasketComponent.setQuantity(\'QUANTITY_INPUT_' + arItem['ID'] + '\', ' + arItem['ID'] + ', ' + arItem['MEASURE_RATIO'] + ', \'up\', ' + bUseFloatQuantity + ');">+</a>';
									}

									if(arItem.hasOwnProperty('MEASURE_TEXT') && arItem['MEASURE_TEXT'].length > 0)
										oCellQuantityHTML += '<div class="slide-panel-basket-item-amount-measure">' + BX.util.htmlspecialchars(arItem['MEASURE_TEXT']) + '</div>';

									oCellQuantityHTML += '</div>';
								}

								oCellQuantityHTML += '<input type="hidden" id="QUANTITY_' + arItem['ID'] + '" name="QUANTITY_' + arItem['ID'] + '" value="' + arItem['QUANTITY'] + '" />';

								newRow.appendChild(
									BX.create('DIV', {
										props: {
											className: 'slide-panel-basket' + (!!res.BASKET_DATA.OBJECTS ? '-object' : '') + '-item-td'
										},
										html: oCellQuantityHTML
									})
								);

								if(isUpdateQuantity) {
									this.updateQuantity('QUANTITY_INPUT_' + arItem['ID'], arItem['ID'], ratio, bUseFloatQuantity);
								}
								if(isUpdatePcQuantity) {
									this.updatePcQuantity('PC_QUANTITY_INPUT_' + arItem['ID'], arItem['ID'], arItem['PC_MEASURE_RATIO'], false);
								} else if(isUpdateSqMQuantity) {
									this.updateSqMQuantity('SQ_M_QUANTITY_INPUT_' + arItem['ID'], arItem['ID'], arItem['SQ_M_MEASURE_RATIO'], true);
								}
								break;
							case 'SUM':
								oCellSumHTML = '<div id="sum_' + arItem['ID'] + '">';
								
								if(typeof(arItem[arColumns[i]]) != 'undefined') {
									oCellSumHTML += arItem[arColumns[i]];
								}

								oCellSumHTML += '</div>';
								
								oCellSumHTML += '<div id="old_sum_' + arItem['ID'] + '" class="slide-panel-basket-item-old-sum" style="display: ' + (arItem['SUM_DISCOUNT_PRICE'] > 0 ? '' : 'none') + ';">' + (arItem['SUM_DISCOUNT_PRICE'] > 0 ? arItem['SUM_FULL_PRICE_FORMATED'] : '') + '</div>';
								
								newRow.appendChild(
									BX.create('DIV', {
										props: {
											className: 'slide-panel-basket' + (!!res.BASKET_DATA.OBJECTS ? '-object' : '') + '-item-td'
										},
										html: oCellSumHTML
									})
								);
								break;
							default:
								break;
						}
					}
					
					if(bShowDeleteColumn) {
						oCellControlHTML = '<a class="slide-panel-basket-item-control" \
							href="javascript:void(0);" \
							onclick="BX.Sale.BasketComponent.deleteItem(' + arItem['ID'] + ');" \
							title="' + BX.message('SBB_SLIDE_PANEL_DELETE') + '">\
							<i class="icon-close"></i></a>';

						newRow.appendChild(
							BX.create('DIV', {
								props: {
									className: 'slide-panel-basket' + (!!res.BASKET_DATA.OBJECTS ? '-object' : '') + '-item-td slide-panel-basket-item-controls'
								},
								html: oCellControlHTML
							})
						);
					}
				}		
			}
			
			//update product params after recalculation
			if(!!res.BASKET_DATA) {
				for(id in res.BASKET_DATA.GRID.ROWS) {
					if(res.BASKET_DATA.GRID.ROWS.hasOwnProperty(id)) {
						var item = res.BASKET_DATA.GRID.ROWS[id];

						if(BX('discount_value_' + id)) {
							BX('discount_value_' + id).innerHTML = item.DISCOUNT_PRICE_PERCENT > 0 ? '-' + item.DISCOUNT_PRICE_PERCENT + '%' : '';
							BX.style(BX('discount_value_' + id), 'display', item.DISCOUNT_PRICE_PERCENT > 0 ? 'block' : 'none');
						}
						
						if(BX('sum_' + id))
							BX('sum_' + id).innerHTML = item.SUM;

						if(BX('old_sum_' + id)) {
							BX('old_sum_' + id).innerHTML = item.SUM_DISCOUNT_PRICE > 0 ? item.SUM_FULL_PRICE_FORMATED : '';
							BX.style(BX('old_sum_' + id), 'display', item.SUM_DISCOUNT_PRICE > 0 ? 'block' : 'none');
						}

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
			
			//update warnings if any	
			if(!!res.BASKET_DATA) {
				var warningMessage = this.container.querySelector('[data-entity="warningMessage"]'),
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

							var allSum = this.container.querySelector('[data-entity="allSum_' + id + '"]');
							if(!!allSum)
								allSum.innerHTML = item.allSum_FORMATED;

							var showPriceWithoutDiscount = item.PRICE_WITHOUT_DISCOUNT != item.allSum_FORMATED;

							var oldSum = this.container.querySelector('[data-entity="oldSum_' + id + '"]');
							if(!!oldSum) {
								oldSum.innerHTML = showPriceWithoutDiscount ? item.PRICE_WITHOUT_DISCOUNT : '';
								BX.style(oldSum, 'display', showPriceWithoutDiscount ? 'block' : 'none');
							}

							var discountSum = this.container.querySelector('[data-entity="discountSum_' + id + '"]');
							if(!!discountSum) {
								discountSum.innerHTML = showPriceWithoutDiscount ? BX.message('SBB_SLIDE_PANEL_TOTAL_DISCOUNT') + ' ' + item.DISCOUNT_PRICE_ALL_FORMATED : '';
								BX.style(discountSum, 'display', showPriceWithoutDiscount ? 'block' : 'none');
							}
						}
					}
				} else {
					var allSum = this.container.querySelector('[data-entity="allSum"]');
					if(!!allSum)
						allSum.innerHTML = res.BASKET_DATA.allSum_FORMATED;
					
					var showPriceWithoutDiscount = res.BASKET_DATA.PRICE_WITHOUT_DISCOUNT != res.BASKET_DATA.allSum_FORMATED;
					
					var oldSum = this.container.querySelector('[data-entity="oldSum"]');
					if(!!oldSum) {
						oldSum.innerHTML = showPriceWithoutDiscount ? res.BASKET_DATA.PRICE_WITHOUT_DISCOUNT : '';
						BX.style(oldSum, 'display', showPriceWithoutDiscount ? 'block' : 'none');
					}

					var discountSum = this.container.querySelector('[data-entity="discountSum"]');
					if(!!discountSum) {
						discountSum.innerHTML = showPriceWithoutDiscount ? BX.message('SBB_SLIDE_PANEL_TOTAL_DISCOUNT') + ' ' + res.BASKET_DATA.DISCOUNT_PRICE_ALL_FORMATED : '';
						BX.style(discountSum, 'display', showPriceWithoutDiscount ? 'block' : 'none');
					}

					if(this.params.MIN_ORDER_SUM > 0)
						this.checkBasketFooterButtons(res.BASKET_DATA.allSum);
				}
				
				BX.onCustomEvent('OnBasketTableChange');
			}
		},

		checkBasketFooterButtons: function(allSum) {
			if(!!this.btnQuickOrder) {
				BX.unbindAll(this.btnQuickOrder);
				if(allSum < parseFloat(this.params.MIN_ORDER_SUM)) {
					BX.adjust(this.btnQuickOrder, {props: {disabled: true}});
				} else {
					BX.adjust(this.btnQuickOrder, {props: {disabled: false}});
					BX.bind(this.btnQuickOrder, 'click', BX.proxy(this.quickOrder, this));
				}
			}
			
			if(!!this.btnCheckOut) {
				BX.unbindAll(this.btnCheckOut);
				if(allSum < parseFloat(this.params.MIN_ORDER_SUM)) {
					BX.adjust(this.btnCheckOut, {props: {disabled: true}});
				} else {
					BX.adjust(this.btnCheckOut, {props: {disabled: false}});
					BX.bind(this.btnCheckOut, 'click', BX.proxy(this.checkOutAction, this));
				}
			}
		},
			
		skuPropClickHandler: function() {
			var target = BX.proxy_context,		
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
				var loader = BX.create('DIV', {props: {className: 'slide-panel__loader'}, html: '<div><span></span></div>'}),
					itemsContainer = this.container.querySelector('.slide-panel-basket-' + (!this.params.OBJECTS ? 'items' : 'objects')),
					loaderTimeOut;
			
				loaderTimeOut = setTimeout(BX.delegate(function() {
					this.container.appendChild(loader);
					if(!!itemsContainer)
						BX.addClass(itemsContainer, 'slide-panel-basket-' + (!this.params.OBJECTS ? 'items' : 'objects') + '-loading');
				}, this), 1000);
				
				basketItemId = target.getAttribute('data-element');
				property = target.getAttribute('data-property');
				action_var = BX('action_var').value;

				property_values[property] = BX.util.htmlspecialcharsback(target.getAttribute('data-value-id'));

				//if already selected element is clicked
				if(BX.hasClass(target, 'selected'))
					return;
				
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
					'template': this.template,
					'signedParamsString': this.signedParamsString
				};
					
				postData[action_var] = 'select_item';

				BX.ajax({
					url: '/bitrix/components/altop/sale.basket.basket/ajax.php',
					method: 'POST',
					data: postData,
					dataType: 'json',
					onsuccess: BX.delegate(function(result) {
						clearTimeout(loaderTimeOut);					
						BX.remove(loader);					
						if(!!itemsContainer && BX.hasClass(itemsContainer, 'slide-panel-basket-' + (!this.params.OBJECTS ? 'items' : 'objects') + '-loading'))
							BX.removeClass(itemsContainer, 'slide-panel-basket-' + (!this.params.OBJECTS ? 'items' : 'objects') + '-loading');
						
						this.updateBasketTable(basketItemId, result);
					}, this)
				});
			}
		},

		deleteItem: function(basketItemId) {
			var data = {basket: {}};

			data.basket['DELETE_' + basketItemId] = 'Y';
			
			this.sendRequest(data);
		},

		clearBasket: function() {
			var data = {basket: {}},
				items = this.container.querySelectorAll('[data-entity="row"]');
			
			if(!!items && items.length > 0) {
				for(var i = 0; items.length > i; i++) {
					var itemId = items[i].getAttribute('data-id');
					data.basket['DELETE_' + itemId] = 'Y';
				}
			}
			
			this.sendRequest(data);
		},

		getData: function(data) {
			data = data || {};

			var action_var = BX('action_var').value;
			if(!!action_var)
				data[action_var] = 'recalculateAjax';
			
			data.via_ajax = 'Y';
			data.site_id = BX.message('SITE_ID');
			data.sessid = BX.bitrix_sessid();
			data.template = this.template;
			data.signedParamsString = this.signedParamsString;

			return data;
		},

		sendRequest: function(data) {
			var loader = BX.create('DIV', {props: {className: 'slide-panel__loader'}, html: '<div><span></span></div>'}),
				itemsContainer = this.container.querySelector('.slide-panel-basket-' + (!this.params.OBJECTS ? 'items' : 'objects')),
				loaderTimeOut;
			
			loaderTimeOut = setTimeout(BX.delegate(function() {
				this.container.appendChild(loader);
				if(!!itemsContainer)
					BX.addClass(itemsContainer, 'slide-panel-basket-' + (!this.params.OBJECTS ? 'items' : 'objects') + '-loading');
			}, this), 1000);
			
			BX.ajax({
				url: '/bitrix/components/altop/sale.basket.basket/ajax.php',
				method: 'POST',
				data: this.getData(data),
				dataType: 'json',
				onsuccess: BX.delegate(function(result) {
					clearTimeout(loaderTimeOut);					
					BX.remove(loader);					
					if(!!itemsContainer && BX.hasClass(itemsContainer, 'slide-panel-basket-' + (!this.params.OBJECTS ? 'items' : 'objects') + '-loading'))
						BX.removeClass(itemsContainer, 'slide-panel-basket-' + (!this.params.OBJECTS ? 'items' : 'objects') + '-loading');
					
					if(result.DELETED_BASKET_ITEMS) {
						for(var i in result.DELETED_BASKET_ITEMS) {
							if(result.DELETED_BASKET_ITEMS.hasOwnProperty(i)) {
								var basketItem = this.container.querySelector('[data-id="' + result.DELETED_BASKET_ITEMS[i] + '"]');
								if(!!basketItem)
									BX.remove(basketItem);
							}
						}
						
						var basketObjects = this.container.querySelectorAll('.slide-panel-basket-object');
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
						if(result.BASKET_DATA.ORDERABLE_BASKET_ITEMS_COUNT > 0) {
							if(result.BASKET_DATA.OBJECTS) {
								for(var id in result.BASKET_DATA.OBJECTS) {
									if(result.BASKET_DATA.OBJECTS.hasOwnProperty(id)) {
										var item = result.BASKET_DATA.OBJECTS[id];

										var allSum = this.container.querySelector('[data-entity="allSum_' + id + '"]');
										if(!!allSum)
											allSum.innerHTML = item.allSum_FORMATED;

										var showPriceWithoutDiscount = item.PRICE_WITHOUT_DISCOUNT != item.allSum_FORMATED;

										var oldSum = this.container.querySelector('[data-entity="oldSum_' + id + '"]');
										if(!!oldSum) {
											oldSum.innerHTML = showPriceWithoutDiscount ? item.PRICE_WITHOUT_DISCOUNT : '';
											BX.style(oldSum, 'display', showPriceWithoutDiscount ? 'block' : 'none');
										}

										var discountSum = this.container.querySelector('[data-entity="discountSum_' + id + '"]');
										if(!!discountSum) {
											discountSum.innerHTML = showPriceWithoutDiscount ? BX.message('SBB_SLIDE_PANEL_TOTAL_DISCOUNT') + ' ' + item.DISCOUNT_PRICE_ALL_FORMATED : '';
											BX.style(discountSum, 'display', showPriceWithoutDiscount ? 'block' : 'none');
										}
									}
								}
							} else {
								var allSum = this.container.querySelector('[data-entity="allSum"]');
								if(!!allSum)
									allSum.innerHTML = result.BASKET_DATA.allSum_FORMATED;
								
								var showPriceWithoutDiscount = result.BASKET_DATA.PRICE_WITHOUT_DISCOUNT != result.BASKET_DATA.allSum_FORMATED;
								
								var oldSum = this.container.querySelector('[data-entity="oldSum"]');
								if(!!oldSum) {
									oldSum.innerHTML = showPriceWithoutDiscount ? result.BASKET_DATA.PRICE_WITHOUT_DISCOUNT : '';
									BX.style(oldSum, 'display', showPriceWithoutDiscount ? 'block' : 'none');
								}

								var discountSum = this.container.querySelector('[data-entity="discountSum"]');
								if(!!discountSum) {
									discountSum.innerHTML = showPriceWithoutDiscount ? BX.message('SBB_SLIDE_PANEL_TOTAL_DISCOUNT') + ' ' + result.BASKET_DATA.DISCOUNT_PRICE_ALL_FORMATED : '';
									BX.style(discountSum, 'display', showPriceWithoutDiscount ? 'block' : 'none');
								}

								if(this.params.MIN_ORDER_SUM > 0)
									this.checkBasketFooterButtons(result.BASKET_DATA.allSum);
							}
						} else {
							if(result.BASKET_DATA.DELAYED_BASKET_ITEMS_COUNT > 0) {
								this.container.innerHTML = '<span class="alert alert-warning">' + BX.message('SBB_SLIDE_PANEL_NO_ITEMS') + '</span>';
							} else {
								this.container.innerHTML = '<span class="alert alert-warning">' + result.BASKET_DATA.ERROR_MESSAGE + '</span>';
							}
						}
					}
					
					if(result.WARNING_MESSAGE) {
						var warningMessage = this.container.querySelector('[data-entity="warningMessage"]');
						if(!!warningMessage) {
							var warningText = [];

							for(var i = 0; i < result.WARNING_MESSAGE.length; i++) {
								warningText[i] = result.WARNING_MESSAGE[i];
							}

							warningMessage.innerHTML = '<span class="alert alert-warning">' + warningText.join('<br />') + '</span>';
							warningMessage.style.display = 'block';
						}
					}
				}, this)
			});
		},

		getColumnName: function(columnCode) {
			if(BX('col_' + columnCode)) {
				return BX.util.trim(BX('col_' + columnCode).innerHTML);
			} else {
				return '';
			}
		},

		checkOutAction: function() {
			document.location.href = this.params.PATH_TO_ORDER;
		},
			
		checkOutObjectAction: function(e) {
			var target = BX.proxy_context,
				basketObject = BX.findParent(target, {className: 'slide-panel-basket-object'});
			
			if(!!basketObject) {
				var rows = basketObject.querySelectorAll('[data-entity="row"]');
				if(!!rows) {
					var ids = [];
					for(var i in rows) {
						if(rows.hasOwnProperty(i))
							ids[i] = rows[i].getAttribute('data-id');
					}

					target.style.width = target.offsetWidth + 'px';
					target.innerHTML = '<span class="btn-loader"><span><span></span></span></span>';
					
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
						onsuccess: BX.delegate(function(result) {
							if(!!result.status) {
								var iOS = navigator.userAgent.match(/iPhone|iPad|iPod/i);
								if(iOS != null) {
									window.location.href = this.params.PATH_TO_ORDER + '?siteId=' + result.siteId;
								} else {
									var newTab = window.open('', '_blank');
									newTab.location = this.params.PATH_TO_ORDER + '?siteId=' + result.siteId;
								}
							}

							target.style.width = '';
							target.innerHTML = '<span>' + BX.message('SBB_SLIDE_PANEL_ORDER_SHORT') + '</span>';
						}, this)
					});
				}
			}
			
			e.stopPropagation();
		},

		basketChange: function(result) {
			if(result.DELETED_BASKET_ITEMS) {
				for(var i in result.DELETED_BASKET_ITEMS) {
					if(result.DELETED_BASKET_ITEMS.hasOwnProperty(i)) {
						var basketItem = this.container.querySelector('[data-id="' + result.DELETED_BASKET_ITEMS[i] + '"]');
						if(!!basketItem)
							BX.remove(basketItem);
					}
				}

				var basketObjects = this.container.querySelectorAll('.slide-panel-basket-object');
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
				if(result.BASKET_DATA.ORDERABLE_BASKET_ITEMS_COUNT <= 0) {
					if(result.BASKET_DATA.DELAYED_BASKET_ITEMS_COUNT > 0) {
						this.container.innerHTML = '<span class="alert alert-warning">' + BX.message('SBB_SLIDE_PANEL_NO_ITEMS') + '</span>';
					} else {
						this.container.innerHTML = '<span class="alert alert-warning">' + result.BASKET_DATA.ERROR_MESSAGE + '</span>';
					}
				}
			}
		},
			
		updateQuantity: function(controlId, basketId, ratio, bUseFloatQuantity) {
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
			newVal = this.correctQuantity(newVal);

			if(bIsCorrectQuantityForRatio) {
				BX(controlId).defaultValue = newVal;
				BX(controlId).value = newVal;
				BX('QUANTITY_' + basketId).value = newVal;
				
				if(autoCalculate) {
					this.actionPool.changeQuantity(basketId);
				}
			} else {
				newVal = this.getCorrectRatioQuantity(newVal, ratio, bUseFloatQuantity);
				newVal = this.correctQuantity(newVal);

				if(newVal != oldVal) {
					BX(controlId).defaultValue = newVal;
					BX(controlId).value = newVal;
					BX('QUANTITY_' + basketId).value = newVal;
					
					if(autoCalculate) {
						this.actionPool.changeQuantity(basketId);
					}
				} else {
					BX(controlId).value = oldVal;
				}
			}
		},

		updatePcQuantity: function(controlId, basketId, ratio, bUseFloatQuantity) {
			var item = this.container.querySelector('[data-id="' + basketId + '"]'),
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
			newVal = this.correctQuantity(newVal);
			
			if(bIsCorrectQuantityForRatio) {
				BX(controlId).defaultValue = newVal;
				BX(controlId).value = newVal;

				if(!!measure && measure == 'pc. 1') {
					BX('QUANTITY_' + basketId).value = newVal;
					bIsChangeQuantity = true;
				}
			} else {
				newVal = this.getCorrectRatioQuantity(newVal, ratio, bUseFloatQuantity);
				newVal = this.correctQuantity(newVal);

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
			newSqMVal = this.correctQuantity(newSqMVal);
			
			if(bIsCorrectSqMQuantityForRatio) {
				obSqMQuantity.defaultValue = newSqMVal;
				obSqMQuantity.value = newSqMVal;

				if(!!measure && measure == 'm2') {
					BX('QUANTITY_' + basketId).value = newSqMVal;
					bIsChangeQuantity = true;
				}
			} else {
				newSqMVal = this.getCorrectRatioQuantity(newSqMVal, ratioSqM, bUseFloatSqMQuantity);
				newSqMVal = this.correctQuantity(newSqMVal);
				
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
				this.actionPool.changeQuantity(basketId);
		},

		updateSqMQuantity: function(controlId, basketId, ratio, bUseFloatQuantity) {
			var item = this.container.querySelector('[data-id="' + basketId + '"]'),
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
			newVal = this.correctQuantity(newVal);
			
			if(bIsCorrectQuantityForRatio) {
				BX(controlId).defaultValue = newVal;
				BX(controlId).value = newVal;
				
				if(!!measure && measure == 'm2') {
					BX('QUANTITY_' + basketId).value = newVal;
					bIsChangeQuantity = true;
				}
			} else {		
				newVal = this.getCorrectRatioQuantity(newVal, ratio, bUseFloatQuantity);		
				newVal = this.correctQuantity(newVal);		
				
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
			newPcVal = this.correctQuantity(newPcVal);

			if(bIsCorrectPcQuantityForRatio) {
				obPcQuantity.defaultValue = newPcVal;
				obPcQuantity.value = newPcVal;

				if(!!measure && measure == 'pc. 1') {
					BX('QUANTITY_' + basketId).value = newPcVal;
					bIsChangeQuantity = true;
				}
			} else {
				newPcVal = this.getCorrectRatioQuantity(newPcVal, ratioPc, bUseFloatPcQuantity);
				newPcVal = this.correctQuantity(newPcVal);

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
				this.actionPool.changeQuantity(basketId);
		},

		setQuantity: function(controlId, basketId, ratio, sign, bUseFloatQuantity) {
			var curVal = parseFloat(BX(controlId).value),
				newVal;

			BX(controlId).defaultValue = curVal;
			
			newVal = sign == 'up' ? curVal + ratio : curVal - ratio;

			if(newVal < 0)
				newVal = 0;
			
			if(bUseFloatQuantity) {
				newVal = parseFloat(newVal).toFixed(4);
			}
			newVal = this.correctQuantity(newVal);
			
			if(ratio > 0 && newVal < ratio) {
				newVal = ratio;
			}

			if(!bUseFloatQuantity && newVal != newVal.toFixed(4)) {
				newVal = parseFloat(newVal).toFixed(4);
			}

			newVal = this.getCorrectRatioQuantity(newVal, ratio, bUseFloatQuantity);
			newVal = this.correctQuantity(newVal);

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
				newSqMVal = this.correctQuantity(newSqMVal);
				
				if(ratioSqM > 0 && newSqMVal < ratioSqM) {
					newSqMVal = ratioSqM;
				}

				if(!bUseFloatSqMQuantity && newSqMVal != newSqMVal.toFixed(4)) {
					newSqMVal = parseFloat(newSqMVal).toFixed(4);
				}
				
				newSqMVal = this.getCorrectRatioQuantity(newSqMVal, ratioSqM, bUseFloatSqMQuantity);		
				newSqMVal = this.correctQuantity(newSqMVal);		

				obSqMQuantity.value = newSqMVal;
				
				this.updatePcQuantity(controlId, basketId, ratio, bUseFloatQuantity);
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
				newPcVal = this.correctQuantity(newPcVal);

				if(ratioPc > 0 && newPcVal < ratioPc) {
					newPcVal = ratioPc;
				}

				if(!bUseFloatPcQuantity && newPcVal != newPcVal.toFixed(4)) {
					newPcVal = parseFloat(newPcVal).toFixed(4);
				}
				
				newPcVal = this.getCorrectRatioQuantity(newPcVal, ratioPc, false);
				newPcVal = this.correctQuantity(newPcVal);

				obPcQuantity.value = newPcVal;
				
				this.updateSqMQuantity(controlId, basketId, ratio, bUseFloatQuantity);
			} else {
				this.updateQuantity(controlId, basketId, ratio, bUseFloatQuantity);
			}
		},

		getCorrectRatioQuantity: function(quantity, ratio, bUseFloatQuantity) {
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
			result = this.correctQuantity(result);
			return result;
		},

		correctQuantity: function(quantity) {
			return parseFloat((quantity * 1).toString());
		},
			
		recalcBasketAjax: function(params) {
			if(this.actionPool.isProcessing())
				return false;
			
			var loader = BX.create('DIV', {props: {className: 'slide-panel__loader'}, html: '<div><span></span></div>'}),
				itemsContainer = this.container.querySelector('.slide-panel-basket-' + (!this.params.OBJECTS ? 'items' : 'objects')),
				loaderTimeOut;
			
			loaderTimeOut = setTimeout(BX.delegate(function() {
				this.container.appendChild(loader);
				if(!!itemsContainer)
					BX.addClass(itemsContainer, 'slide-panel-basket-' + (!this.params.OBJECTS ? 'items' : 'objects') + '-loading');
			}, this), 1000);

			var property_values = {},
				action_var = BX('action_var').value,
				items = this.container.querySelectorAll('[data-entity="row"]'),
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
				'template': this.template,
				'signedParamsString': this.signedParamsString
			};
			
			postData[action_var] = 'recalculate';
			
			if(!!params && typeof params === 'object') {
				for(i in params) {
					if(params.hasOwnProperty(i))
						postData[i] = params[i];
				}
			}
			
			if(!!items && items.length > 0) {
				for(i = 0; items.length > i; i++) {
					var itemId = items[i].getAttribute('data-id');
					postData['QUANTITY_' + itemId] = BX('QUANTITY_' + itemId).value;
				}
			}
			
			this.actionPool.setProcessing(true);
			this.actionPool.clearPool();

			BX.ajax({
				url: '/bitrix/components/altop/sale.basket.basket/ajax.php',
				method: 'POST',
				data: postData,
				dataType: 'json',
				onsuccess: BX.delegate(function(result) {
					clearTimeout(loaderTimeOut);					
					BX.remove(loader);					
					if(!!itemsContainer && BX.hasClass(itemsContainer, 'slide-panel-basket-' + (!this.params.OBJECTS ? 'items' : 'objects') + '-loading'))
						BX.removeClass(itemsContainer, 'slide-panel-basket-' + (!this.params.OBJECTS ? 'items' : 'objects') + '-loading');
					
					this.actionPool.setProcessing(false);
					
					if(this.actionPool.isPoolEmpty()) {
						this.updateBasketTable(null, result);
						this.actionPool.updateQuantity();
					} else {
						this.actionPool.enableTimer(true);
					}
				}, this)
			});
		},
			
		quickOrderRequest: function(hasObject, sPanel, sPanelContent) {
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
		},
			
		quickOrder: function(e) {
			var sPanel = document.body.querySelector('.slide-panel');
			if(!!sPanel) {
				var target = BX.proxy_context,
					hasObject = target.getAttribute('data-has-object') == 'true';

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
					BX.onCustomEvent(this, 'quickOrderRequest', [hasObject, sPanel, sPanelContent]);
				
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
	};
})();