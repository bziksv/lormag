(function() {
	'use strict';

	if(!!window.JCCatalogSetConstructor)
		return;

	window.JCCatalogSetConstructor = function(arParams) {
		this.numSetItems = arParams.numSetItems || 0;
		this.ajaxPath = arParams.ajaxPath || '';
		this.canBuy = arParams.canBuy;
		this.currency = arParams.currency || '';
		this.mainElementId = arParams.mainElementId || 0;
		this.mainElementRatioPrice = arParams.mainElementRatioPrice || 0;
		this.mainElementRatioOldPrice = arParams.mainElementRatioOldPrice || 0;
		this.mainElementRatioDiffPrice = arParams.mainElementRatioDiffPrice || 0;		
		this.mainElementObject = arParams.mainElementObject;
		this.mainElementPartnersUrl = arParams.mainElementPartnersUrl;
		this.iblockId = arParams.iblockId || '';
		this.basketUrl = arParams.basketUrl || '';
		this.basketParams = {};
		this.setIds = arParams.setIds || null;
		this.productPropsVar = arParams.productPropsVar || '';
		this.partialProductProps = arParams.partialProductProps;
		this.setCartProps = arParams.setCartProps || null;		
		this.offersCartProps = arParams.offersCartProps || null;
		this.itemsRatio = arParams.itemsRatio || null;
		this.noFotoSrc = arParams.noFotoSrc || '';
		this.messages = arParams.messages;
		
		this.parentCont = BX(arParams.parentContId) || null;
		this.setItemsCont = this.parentCont.querySelector('[data-role="set-items"]');
		this.setOtherItemsCont = this.parentCont.querySelector('[data-role="set-other-items"]');
		
		this.setPriceCont = this.parentCont.querySelector('[data-role="set-price"]');
		this.setOldPriceCont = this.parentCont.querySelector('[data-role="set-old-price"]');
		this.setDiffPriceCont = this.parentCont.querySelector('[data-role="set-diff-price"]');
		
		this.buyButton = this.parentCont.querySelector('[data-role="set-buy-btn"]');
		
		this.emptySetMessage = this.parentCont.querySelector('[data-set-message="empty-set"]');

		BX.catalogSetPopup = null;
		this.isTouchDevice = BX.hasClass(document.documentElement, 'bx-touch');

		this.popupPanel = document.body.querySelector('.popup-panel');
		
		if(typeof arParams === 'object') {
			BX.ready(BX.delegate(this.init, this));
		}
	};

	window.JCCatalogSetConstructor.prototype = {
		init: function() {
			if(!!this.setItemsCont)
				BX.bindDelegate(this.setItemsCont, 'click', { 'attribute': 'data-role' }, BX.proxy(this.deleteFromSet, this));
			
			if(!!this.setOtherItemsCont)
				BX.bindDelegate(this.setOtherItemsCont, 'click', { 'attribute': 'data-role' }, BX.proxy(this.addToSet, this));
			
			if(!!this.buyButton)
				BX.bind(this.buyButton, 'click', BX.proxy(this.addToBasket, this));

			this.initTabs();

			if(!this.isTouchDevice) {
				var product = this.parentCont.querySelectorAll('.catalog-set-constructor-other-item');
				if(!!product) {
					for(var i in product) {
						if(product.hasOwnProperty(i) && BX.type.isDomNode(product[i])) {
							BX.bind(product[i], 'mouseenter', function() {
								this.style.height = getComputedStyle(this).height;
								BX.addClass(this, 'hover');
							});
							BX.bind(product[i], 'mouseleave', function() {
								this.style.height = '';
								BX.removeClass(this, 'hover');
							});
						}
					}
				}
			}
		},
		
		initTabs: function() {
			var tabsContainer = this.parentCont.querySelector('[data-entity="set-tabs"]'),		
				tabs = !!tabsContainer && tabsContainer.querySelectorAll('[data-entity="tab"]'),
				tabsContentContainer = this.parentCont.querySelector('[data-entity="set-tabs-content"]'),
				haveActive = false;
			
			if(!!tabs) {
				for(var i in tabs) {
					if(tabs.hasOwnProperty(i) && BX.type.isDomNode(tabs[i])) {
						var tabValue = tabs[i].getAttribute('data-value');
						if(tabValue) {
							var targetTabContent = tabsContentContainer.querySelector('[data-value="' + tabValue + '"]');
							if(BX.type.isDomNode(targetTabContent)) {
								if(!haveActive) {
									BX.addClass(tabs[i], 'active');
									BX.show(targetTabContent);
									haveActive = true;
								} else {
									BX.removeClass(tabs[i], 'active');								
									BX.hide(targetTabContent);
								}
								
								BX.bind(tabs[i], 'click', function(event) {							
									event.preventDefault();
									
									var targetTabValue = this.getAttribute('data-value');
									
									if(!BX.hasClass(this, 'active') && targetTabValue) {
										var tabsContent = tabsContentContainer.querySelectorAll('[data-entity="set-tab-content"]');
										if(!!tabsContent) {
											for(var j in tabsContent) {
												if(tabsContent.hasOwnProperty(j) && BX.type.isDomNode(tabsContent[j])) {
													if(tabsContent[j].getAttribute('data-value') == targetTabValue) {
														BX.show(tabsContent[j]);
													} else {
														BX.hide(tabsContent[j]);
													}
												}
											}
										}
										
										for(var k in tabs) {
											if(tabs.hasOwnProperty(k) && BX.type.isDomNode(tabs[k])) {
												if(tabs[k].getAttribute('data-value') == targetTabValue) {
													BX.addClass(tabs[k], 'active');
												} else {
													BX.removeClass(tabs[k], 'active');
												}
											}
										}
									}
								});
							}
						}
					}
				}
			}
		},
			
		deleteFromSet: function(e) {
			var target = BX.proxy_context;

			if(target && target.hasAttribute('data-role') && target.getAttribute('data-role') == 'set-delete-btn') {
				var item = target.parentNode.parentNode,
					itemSep = BX.findNextSibling(item, {className: 'catalog-set-constructor-added-item-sep'}),
					itemId = item.getAttribute('data-id'),
					itemName = item.getAttribute('data-name'),
					itemSectionId = item.getAttribute('data-section-id'),
					itemImg = item.getAttribute('data-img'),
					itemUrl = item.getAttribute('data-url'),
					itemBrandName = item.getAttribute('data-brand-name'),
					itemBrandImg = item.getAttribute('data-brand-img'),
					itemRatioPrice = item.getAttribute('data-ratio-price'),
					itemPrintRatioPrice = item.getAttribute('data-print-ratio-price'),
					itemRatioOldPrice = item.getAttribute('data-ratio-old-price'),
					itemPrintRatioOldPrice = item.getAttribute('data-print-ratio-old-price'),
					itemRatioDiffPrice = item.getAttribute('data-ratio-diff-price'),
					itemPrintRatioDiffPrice = item.getAttribute('data-print-ratio-diff-price'),
					itemMeasure = item.getAttribute('data-measure'),
					itemBasketQuantity = item.getAttribute('data-quantity');

				var newSliderNodeBrand;				
				if(itemBrandImg) {
					newSliderNodeBrand = BX.create('SPAN', {
						attrs: {
							className: 'catalog-set-constructor-item-brand'
						},
						children: [
							BX.create('IMG', {
								attrs: {
									src: itemBrandImg,
									alt: itemBrandName
								}
							})
						]
					});
				}
				
				var newSliderNodeBasketProps = item.querySelector('.catalog-set-constructor-basket-props-container'),
					newSliderNodeBasketPropsContainer;
				if(!!newSliderNodeBasketProps) {
					newSliderNodeBasketPropsContainer = BX.create('DIV', {
						attrs: {
							className: 'catalog-set-constructor-item-hidden'
						}
					});
					newSliderNodeBasketPropsContainer.appendChild(newSliderNodeBasketProps);
				}
				
				var newSliderNodePrice,
					newSliderNodeOldPrice,
					newSliderNodeDiffPrice;
				if(itemRatioPrice > 0) {
					newSliderNodePrice = BX.create('DIV', {
						attrs: {
							className: 'catalog-set-constructor-price-current'
						},
						html: itemPrintRatioPrice + '<span class="catalog-set-constructor-price-measure">/' + itemBasketQuantity + ' ' + itemMeasure + '</span>'
					});
					if(itemRatioPrice < itemRatioOldPrice) {
						newSliderNodeOldPrice = BX.create('DIV', {
							attrs: {
								className: 'catalog-set-constructor-price-old'
							},
							html: itemPrintRatioOldPrice
						});
						
						newSliderNodeDiffPrice = BX.create('DIV', {
							attrs: {
								className: 'catalog-set-constructor-price-economy'
							},
							html: this.messages.ECONOMY_PRICE + ' ' + itemPrintRatioDiffPrice
						});
					}
				} else {
					newSliderNodePrice = BX.create('DIV', {
						attrs: {
							className: 'catalog-set-constructor-price-not-set'
						},
						html: this.messages.PRICE_NOT_SET
					});
				}

				var newSliderNodeContainer = BX.create('DIV', {
					attrs: {
						className: 'col-xs-12 col-md-4'
					}
				});
				
				var newSliderNode = BX.create('DIV', {					
					attrs: {
						className: 'catalog-set-constructor-item-container catalog-set-constructor-other-item',
						'data-id': itemId,
						'data-name': itemName,
						'data-section-id': itemSectionId,
						'data-img': itemImg,
						'data-url': itemUrl,
						'data-brand-name': itemBrandName,
						'data-brand-img': itemBrandImg,
						'data-ratio-price': itemRatioPrice,
						'data-print-ratio-price': itemPrintRatioPrice,
						'data-ratio-old-price': itemRatioOldPrice,
						'data-print-ratio-old-price': itemPrintRatioOldPrice,
						'data-ratio-diff-price': itemRatioDiffPrice,
						'data-print-ratio-diff-price': itemPrintRatioDiffPrice,
						'data-measure': itemMeasure,
						'data-quantity': itemBasketQuantity
					},
					children: [
						BX.create('DIV', {
							attrs: {
								className: 'catalog-set-constructor-item'
							},
							children: [
								BX.create('DIV', {
									attrs: {
										className: 'catalog-set-constructor-item-image-wrapper'
									},
									children: [
										BX.create('A', {
											attrs: {
												className: 'catalog-set-constructor-item-image',
												href: itemUrl,
												title: itemName
											},
											children: [
												BX.create('IMG', {
													attrs: {
														src: itemImg ? itemImg : this.noFotoSrc,
														alt: itemName
													}
												}),
												newSliderNodeBrand
											]
										})
									]
								}),
								BX.create('DIV', {
									attrs: {
										className: 'catalog-set-constructor-item-title'
									},
									children: [
										BX.create('A', {
											attrs: {
												href: itemUrl,
												title: itemName
											},
											html: itemName
										})
									]
								}),
								BX.create('DIV', {
									attrs: {
										className: 'catalog-set-constructor-item-info-container'
									},
									children: [
										BX.create('DIV', {
											attrs: {
												className: 'catalog-set-constructor-item-info-block'
											},
											children: [
												newSliderNodeBasketPropsContainer,
												BX.create('DIV', {
													attrs: {
														className: 'catalog-set-constructor-item-info'
													},
													children: [
														BX.create('DIV', {
															attrs: {
																className: 'catalog-set-constructor-item-blocks'
															},
															children: [
																BX.create('DIV', {
																	attrs: {
																		className: 'catalog-set-constructor-item-price-container'
																	},
																	children: [
																		newSliderNodePrice,
																		newSliderNodeOldPrice,
																		newSliderNodeDiffPrice
																	]
																})
															]
														}),
														BX.create('DIV', {
															attrs: {
																className: 'catalog-set-constructor-item-button-container'
															},
															children: [
																BX.create('BUTTON', {
																	attrs: {
																		type: 'button',
																		className: 'btn btn-buy',
																		'data-role': 'set-add-btn'
																	},
																	html: '<span>+</span>'
																})
															]
														})
													]
												})
											]
										})
									]
								})
							]
						})
					]
				});

				BX.bind(newSliderNode, 'mouseenter', function() {
					this.style.height = getComputedStyle(this).height;
					BX.addClass(this, 'hover');
				});
				BX.bind(newSliderNode, 'mouseleave', function() {
					this.style.height = '';
					BX.removeClass(this, 'hover');
				});

				newSliderNodeContainer.appendChild(newSliderNode);

				var setTabContent = BX.findChild(this.setOtherItemsCont, {className: 'catalog-set-constructor-tabs-box', attrs: {'data-value': itemSectionId}}, true, false);
				if(!!setTabContent) {
					var setTabContentRow = setTabContent.querySelector('.row');
					if(!!setTabContentRow)
						setTabContentRow.appendChild(newSliderNodeContainer);
				}

				var setTab = BX.findChild(this.setOtherItemsCont, {className: 'catalog-set-constructor-tabs-tab', attrs: {'data-value': itemSectionId}}, true, false);
				if(!!setTab) {
					var setTabVal = setTab.querySelector('span');
					if(!!setTabVal)
						setTabVal.innerHTML = parseInt(setTabVal.innerHTML) + 1;
				}

				this.numSetItems--;
				
				BX.remove(item);
				if(!!itemSep)
					BX.remove(itemSep);

				for(var i = 0, l = this.setIds.length; i < l; i++) {
					if(this.setIds[i] == itemId)
						this.setIds.splice(i, 1);
				}

				this.recountPrice();
				
				if(this.numSetItems <= 0) {
					if(!!this.emptySetMessage) {
						BX.adjust(this.emptySetMessage, {
							props: {
								className: 'alert alert-warning'
							},
							style: {
								display: 'block'
							},
							html: this.messages.EMPTY_SET
						});
					}
					if(!!this.buyButton && this.canBuy && this.mainElementRatioPrice > 0 && !this.mainElementObject && !this.mainElementPartnersUrl) {
						BX.adjust(this.buyButton, {
							props: {
								disabled: true
							}
						});
					}
				}

				if(!!this.popupPanel)
					e.stopPropagation();
			}
		},
			
		addToSet: function(e) {
			var target = BX.proxy_context;

			if(!!target && target.hasAttribute('data-role') && target.getAttribute('data-role') == 'set-add-btn') {
				var item = BX.findParent(target, {className: 'catalog-set-constructor-other-item'}),
					itemId = item.getAttribute('data-id'),
					itemName = item.getAttribute('data-name'),
					itemSectionId = item.getAttribute('data-section-id'),
					itemImg = item.getAttribute('data-img'),
					itemUrl = item.getAttribute('data-url'),
					itemBrandName = item.getAttribute('data-brand-name'),
					itemBrandImg = item.getAttribute('data-brand-img'),
					itemRatioPrice = item.getAttribute('data-ratio-price'),
					itemPrintRatioPrice = item.getAttribute('data-print-ratio-price'),
					itemRatioOldPrice = item.getAttribute('data-ratio-old-price'),
					itemPrintRatioOldPrice = item.getAttribute('data-print-ratio-old-price'),
					itemRatioDiffPrice = item.getAttribute('data-ratio-diff-price'),
					itemPrintRatioDiffPrice = item.getAttribute('data-print-ratio-diff-price'),
					itemMeasure = item.getAttribute('data-measure'),
					itemBasketQuantity = item.getAttribute('data-quantity');

				var newSetNodeBasketProps = item.querySelector('.catalog-set-constructor-basket-props-container'),
					newSetNodeBasketPropsContainer;
				if(!!newSetNodeBasketProps) {
					newSetNodeBasketPropsContainer = BX.create('DIV', {
						attrs: {
							className: 'catalog-set-constructor-added-item-info'
						}
					});
					newSetNodeBasketPropsContainer.appendChild(newSetNodeBasketProps);
				}
				
				var newSetNodePrice,
					newSetNodeOldPrice,
					newSetNodeDiffPrice;
				if(itemRatioPrice > 0) {
					newSetNodePrice = BX.create('DIV', {
						attrs: {
							className: 'catalog-set-constructor-price-current'
						},
						html: itemPrintRatioPrice + '<span class="catalog-set-constructor-price-measure">/' + itemBasketQuantity + ' ' + itemMeasure + '</span>'
					});
					if(itemRatioPrice < itemRatioOldPrice) {
						newSetNodeOldPrice = BX.create('DIV', {
							attrs: {
								className: 'catalog-set-constructor-price-old'
							},
							html: itemPrintRatioOldPrice
						});
						
						newSetNodeDiffPrice = BX.create('DIV', {
							attrs: {
								className: 'catalog-set-constructor-price-economy'
							},
							html: this.messages.ECONOMY_PRICE + ' ' + itemPrintRatioDiffPrice
						});
					}
				} else {
					newSetNodePrice = BX.create('DIV', {
						attrs: {
							className: 'catalog-set-constructor-price-not-set'
						},
						html: this.messages.PRICE_NOT_SET
					});
				}
				
				var newSetNode = BX.create('DIV', {
					attrs: {
						className: 'catalog-set-constructor-added-item-row catalog-set-constructor-added-item',
						'data-id': itemId,
						'data-name': itemName,
						'data-section-id': itemSectionId,
						'data-img': itemImg,
						'data-url': itemUrl,
						'data-brand-name': itemBrandName,
						'data-brand-img': itemBrandImg,
						'data-ratio-price': itemRatioPrice,
						'data-print-ratio-price': itemPrintRatioPrice,
						'data-ratio-old-price': itemRatioOldPrice,
						'data-print-ratio-old-price': itemPrintRatioOldPrice,
						'data-ratio-diff-price': itemRatioDiffPrice,
						'data-print-ratio-diff-price': itemPrintRatioDiffPrice,
						'data-measure': itemMeasure,
						'data-quantity': itemBasketQuantity
					},
					children: [
						BX.create('DIV', {
							attrs: {
								className: 'catalog-set-constructor-added-item-cell catalog-set-constructor-added-item-image-wrapper'
							},
							children: [
								BX.create('A', {
									attrs: {
										className: 'catalog-set-constructor-added-item-image',
										href: itemUrl,
										title: itemName
									},
									children: [
										BX.create('IMG', {
											attrs: {
												src: itemImg ? itemImg : this.noFotoSrc,
												alt: itemName
											}
										})
									]
								})
							]
						}),
						BX.create('DIV', {
							attrs: {
								className: 'catalog-set-constructor-added-item-cell catalog-set-constructor-added-item-caption'
							},
							children: [
								BX.create('A', {
									attrs: {
										className: 'catalog-set-constructor-added-item-title',
										href: itemUrl,
										title: itemName
									},
									html: itemName
								}),
								newSetNodeBasketPropsContainer
							]
						}),
						BX.create('DIV', {
							attrs: {
								className: 'catalog-set-constructor-added-item-cell catalog-set-constructor-added-item-price-container'
							},
							children: [
								newSetNodePrice,
								newSetNodeOldPrice,
								newSetNodeDiffPrice
							]
						}),
						BX.create('DIV', {
							attrs: {
								className: 'catalog-set-constructor-added-item-cell catalog-set-constructor-added-item-delete'
							},
							children: [
								BX.create('I', {
									attrs: {
										className: 'icon-close',
										'data-role': 'set-delete-btn'
									}
								})
							]
						})
					]
				});
				
				if(!!this.emptySetMessage) {
					this.setItemsCont.insertBefore(newSetNode, this.emptySetMessage);

					this.setItemsCont.insertBefore(BX.create('DIV', {
						attrs: {
							className: 'catalog-set-constructor-added-item-sep'
						}
					}), this.emptySetMessage);
				} else {
					this.setItemsCont.appendChild(newSetNode);

					this.setItemsCont.appendChild(BX.create('DIV', {
						attrs: {
							className: 'catalog-set-constructor-added-item-sep'
						}
					}));
				}

				var setTab = BX.findChild(this.setOtherItemsCont, {className: 'catalog-set-constructor-tabs-tab', attrs: {'data-value': itemSectionId}}, true, false);
				if(!!setTab) {
					var setTabVal = setTab.querySelector('span');
					if(!!setTabVal)
						setTabVal.innerHTML = parseInt(setTabVal.innerHTML) - 1;
				}
				
				this.numSetItems++;
				
				BX.remove(item.parentNode);
				
				this.setIds.push(itemId);
				
				this.recountPrice();

				if(this.numSetItems > 0) {
					if(!!this.emptySetMessage) {
						BX.adjust(this.emptySetMessage, {
							props: {
								className: 'alert'
							},
							style: {
								display: 'none'
							},
							html: ''
						});
					}
					if(!!this.buyButton && this.canBuy && this.mainElementRatioPrice > 0 && !this.mainElementObject && !this.mainElementPartnersUrl) {
						BX.adjust(this.buyButton, {
							props: {
								disabled: false
							}
						});
					}
				}

				if(!!this.popupPanel)
					e.stopPropagation();
			}
		},
			
		recountPrice: function() {
			var sumPrice = Number(this.mainElementRatioPrice),
				sumOldPrice = Number(this.mainElementRatioOldPrice),
				sumDiffPrice = Number(this.mainElementRatioDiffPrice),
				setItems = BX.findChildren(this.setItemsCont, {className: 'catalog-set-constructor-added-item'}, true);
			
			if(!!setItems) {
				for(var i = 0, l = setItems.length; i < l; i++) {
					sumPrice += Number(setItems[i].getAttribute('data-ratio-price'));
					sumOldPrice += Number(setItems[i].getAttribute('data-ratio-old-price'));
					sumDiffPrice += Number(setItems[i].getAttribute('data-ratio-diff-price'));
				}
			}
			
			if(!!this.setPriceCont)
				this.setPriceCont.innerHTML = BX.Currency.currencyFormat(sumPrice, this.currency, true);
			
			if(!!this.setOldPriceCont) {
				BX.adjust(this.setOldPriceCont, {
					style: {
						'display': Math.floor(sumDiffPrice * 100) > 0 ? '' : 'none'
					},
					html: Math.floor(sumDiffPrice * 100) > 0 ? BX.Currency.currencyFormat(sumOldPrice, this.currency, true) : ''
				});
			}

			if(!!this.setDiffPriceCont) {
				BX.adjust(this.setDiffPriceCont, {
					style: {
						'display': Math.floor(sumDiffPrice * 100) > 0 ? '' : 'none'
					},
					html: Math.floor(sumDiffPrice * 100) > 0 ? this.messages.ECONOMY_PRICE + ' ' + BX.Currency.currencyFormat(sumDiffPrice, this.currency, true) : ''
				});
			}
		},
			
		showBasketPropsDropDownPopup: function(element, popupId, productId) {
			var contentNode = element.querySelector('[data-entity="dropdownContent"]');

			if(!!BX.catalogSetPopup)
				BX.catalogSetPopup.close();

			BX.catalogSetPopup = BX.PopupWindowManager.create('basketPropsDropDown_' + popupId + '_' + productId, element, {
				autoHide: true,
				offsetLeft: 0,
				offsetTop: 3,
				overlay : false,
				draggable: {restrict: true},
				closeByEsc: true,
				className: 'bx-drop-down-popup-window',
				content: BX.clone(contentNode)
			});	
			element.appendChild(BX('basketPropsDropDown_' + popupId + '_' + productId));
			BX.catalogSetPopup.show();
		},
			
		selectBasketPropsDropDownPopupItem: function(element, valueId) {
			var wrapContainer = BX.findParent(element, {className: 'catalog-set-constructor-basket-props-drop-down'}, false),
				currentValue = wrapContainer.querySelector('INPUT'),
				currentOption = wrapContainer.querySelector('[data-entity="current-option"]');
			currentValue.value = valueId;
			currentOption.innerHTML = element.innerHTML;
			BX.PopupWindowManager.getCurrentPopup().close();
		},

		fillBasketProps: function() {
			var mainElement = this.parentCont.querySelector('.catalog-set-constructor-original-item');
			if(!!mainElement) {
				var propCollection = mainElement.getElementsByTagName('input');
				if(propCollection && propCollection.length) {
					for(var i = 0; i < propCollection.length; i++) {
						if(!propCollection[i].disabled) {
							switch(propCollection[i].type.toLowerCase()) {
								case 'hidden':
									this.basketParams[propCollection[i].name] = propCollection[i].value;
									break;
								case 'radio':
									if(propCollection[i].checked)
										this.basketParams[propCollection[i].name] = propCollection[i].value;
									break;
								default:
									break;
							}
						}
					}
				}
			}

			var setItem = this.setItemsCont.querySelectorAll('.catalog-set-constructor-added-item');
			if(!!setItem) {
				for(var i in setItem) {
					if(setItem.hasOwnProperty(i) && BX.type.isDomNode(setItem[i])) {
						var propCollection = setItem[i].getElementsByTagName('input');
						if(propCollection && propCollection.length) {
							for(var j = 0; j < propCollection.length; j++) {
								if(!propCollection[j].disabled) {
									switch(propCollection[j].type.toLowerCase()) {
										case 'hidden':
											this.basketParams[propCollection[j].name] = propCollection[j].value;
											break;
										case 'radio':
											if(propCollection[j].checked)
												this.basketParams[propCollection[j].name] = propCollection[j].value;
											break;
										default:
											break;
									}
								}
							}
						}
					}
				}
			}
		},
			
		addToBasket: function(e) {
			this.buyButton.innerHTML = '<span class="btn-loader"><span><span></span></span></span>';

			this.basketParams = {							
				action: 'catalogSetAdd2Basket',
				set_ids: this.setIds,
				siteId: BX.message('SITE_ID'),
				iblockId: this.iblockId,
				productPropsVar: this.productPropsVar,
				partialProductProps: this.partialProductProps,
				setCartProps: this.setCartProps,
				setOffersCartProps: this.offersCartProps,
				itemsRatio: this.itemsRatio
			};
			
			this.fillBasketProps();
			
			BX.ajax({
				url: this.ajaxPath,
				method: 'POST',
				dataType: 'json',
				timeout: 60,
				data: this.basketParams,
				onsuccess: BX.delegate(function() {
					BX.adjust(this.buyButton, {
						props: {
							className: 'btn btn-buy-ok'
						},
						html: '<i class="icon-ok-b"></i><span>' + this.messages.SET_BUY_OK + '</span>'
					});
					BX.unbindAll(this.buyButton);
					BX.bind(this.buyButton, "click", BX.delegate(this.basketRedirect, this));
					BX.onCustomEvent('OnBasketChange');
				}, this)
			});

			if(!!this.popupPanel)
				e.stopPropagation();
		},

		basketRedirect: function() {
			window.location.href = this.basketUrl;
		},
	}
})();