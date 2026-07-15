BX.namespace("BX.Iblock.Catalog");

BX.Iblock.Catalog.CompareClass = (function() {
	var CompareClass = function(wrapObjId, actionVariable, productIdVariable, isExternal, reloadUrl) {
		this.wrapObjId = BX(wrapObjId);
		this.actionVariable = actionVariable;
		this.productIdVariable = productIdVariable;		
		this.isExternal = isExternal;
		this.reloadUrl = reloadUrl;
		
		BX.ready(BX.delegate(this.init, this));
	};

	CompareClass.prototype.init = function() {
		this.compareAdjusted = false;
		this.adjustCompare();
		BX.bind(window, 'resize', BX.proxy(this.adjustCompare, this));
		BX.addCustomEvent(window, 'slideMenu', BX.proxy(this.readjustCompare, this));
		
		this.compareTheadFixed = false;
		this.compareTheadScrolled = false;
		this.compareTheadHidden = false;
		this.lastScrollTop = 0;
		this.checkTopCompareScroll();
		BX.bind(window, 'scroll', BX.proxy(this.checkTopCompareScroll, this));
		
		this.scrollbarFixed = false;
		this.checkScrollbarScroll();
		BX.bind(window, 'scroll', BX.proxy(this.checkScrollbarScroll, this));
		
		var deleteItemAll = this.wrapObjId.querySelectorAll('[data-entity="deleteItem"]');
		if(!!deleteItemAll) {
			for(var i in deleteItemAll) {
				if(deleteItemAll.hasOwnProperty(i) && BX.type.isDomNode(deleteItemAll[i])) {
					BX.bind(deleteItemAll[i], 'click', BX.proxy(this.deleteItem, this));
				}
			}
		}

		var clearBtnAll = this.wrapObjId.querySelectorAll('[data-entity="clear"]');
		if(!!clearBtnAll) {
			for(var i in clearBtnAll) {
				if(clearBtnAll.hasOwnProperty(i) && BX.type.isDomNode(clearBtnAll[i])) {
					BX.bind(clearBtnAll[i], 'click', BX.proxy(this.clear, this));
				}
			}
		}

		var buyBtnAll = this.wrapObjId.querySelectorAll('[data-entity="addBasket"]');
		if(!!buyBtnAll) {
			for(var i in buyBtnAll) {
				if(buyBtnAll.hasOwnProperty(i) && BX.type.isDomNode(buyBtnAll[i])) {
					BX.bind(buyBtnAll[i], 'click', BX.proxy(this.add2Basket, this));
				}
			}
		}
	};
	
	CompareClass.prototype.adjustCompare = function() {
		if(window.innerWidth < 1043) {
			var compareLeft = this.wrapObjId.querySelector('.compare-left');
			if(!!compareLeft) {
				if(!!this.compareAdjusted) {
					var compareLeftColThead = compareLeft.querySelector('.compare-col-thead');
					if(!!compareLeftColThead) {
						compareLeftColThead.removeAttribute('style');
						BX.removeClass(compareLeftColThead, 'compare-col-thead-fixed');
						BX.removeClass(compareLeftColThead, 'compare-col-thead-hidden');
					}

					var compareRight = this.wrapObjId.querySelector('.compare-right');
					if(!!compareRight)
						BX.remove(compareRight);
					
					this.compareAdjusted = false;

					this.compareTheadFixed = false;
					this.compareTheadScrolled = true;
					this.compareTheadHidden = false;

					this.scrollbarFixed = false;
				}
				
				$(compareLeft).scrollbar({
					onScroll: function(y, x) {
						var compareLeftRowThead = this.wrapper[0].querySelector('.compare-row-thead');
						if(!!compareLeftRowThead)
							BX.style(compareLeftRowThead, 'marginLeft', '-' + x.scroll + 'px');
					},
					onUpdate: function() {
						var scrollbar = this.wrapper[0].querySelector('.scroll-element.scroll-x');
						if(!!scrollbar)
							BX.style(scrollbar, 'width', this.wrapper[0].getBoundingClientRect().width + 'px');
					}
				});
			}
		} else {
			if(!this.compareAdjusted) {
				var compareLeft = this.wrapObjId.querySelector('.compare-left');
				if(!!compareLeft) {
					var compareLeftScrollContent = compareLeft.querySelector('.scroll-content');
					if(!!compareLeftScrollContent) {
						$(compareLeftScrollContent).scrollbar('destroy');
						compareLeft = compareLeftScrollContent;
					}

					var compareLeftCompare = compareLeft.querySelector('.compare');
					if(!!compareLeftCompare) {
						compareLeftCompare.removeAttribute('style');

						var compareLeftRowThead = compareLeftCompare.querySelector('.compare-row-thead');
						if(!!compareLeftRowThead) {
							compareLeftRowThead.removeAttribute('style');
							BX.removeClass(compareLeftRowThead, 'compare-row-thead-fixed');
							BX.removeClass(compareLeftRowThead, 'compare-row-thead-hidden');
						}
						
						var compareRight = BX.create('DIV', {props: {className: 'compare-right scrollbar-inner'}}),
							newCompare = BX.clone(compareLeftCompare),
							compareLeftColThead = compareLeftCompare.querySelector('.compare-col-thead'),
							compareLeftColTheadWidth = !!compareLeftColThead ? compareLeftColThead.getBoundingClientRect().width : 0;
						
						BX.style(newCompare, 'width', compareLeftCompare.getBoundingClientRect().width + 'px');
						BX.style(newCompare, 'marginLeft', '-' + compareLeftColTheadWidth + 'px');
						
						var itemPics = newCompare.querySelectorAll('img[data-lazyload-src]');
						if(!!itemPics) {
							for(var i in itemPics) {
								if(itemPics.hasOwnProperty(i)) {
									if(!BX.hasClass(itemPics[i], 'bx-lazyload-success')) {
										var dataSrc = itemPics[i].getAttribute('data-lazyload-src');
										if(!!dataSrc) {
											BX.adjust(itemPics[i], {
												props: {
													className: 'bx-lazyload-success',
													src: dataSrc
												}
											});
										}
									}
								}
							}
						}
						
						compareRight.appendChild(newCompare);
						BX.insertAfter(compareRight, compareLeft);
						
						$(compareRight).scrollbar({
							onInit: function() {
								BX.style(this.wrapper[0], 'left', compareLeftColTheadWidth + 'px');
							},
							onScroll: function(y, x) {
								var compareRightRowThead = this.wrapper[0].querySelector('.compare-row-thead');
								if(!!compareRightRowThead)
									BX.style(compareRightRowThead, 'marginLeft', '-' + x.scroll + 'px');
							},
							onUpdate: function() {
								var scrollbar = this.wrapper[0].querySelector('.scroll-element.scroll-x');
								if(!!scrollbar)
									BX.style(scrollbar, 'width', this.wrapper[0].getBoundingClientRect().width + 'px');
							}
						});
						
						if(!BX.hasClass(compareRight.parentNode, 'scroll-wrapper')) {
							BX.style(compareRight, 'left', compareLeftColTheadWidth + 'px');
							
							BX.bind(compareRight, 'scroll', function() {
								var compareRightRowThead = compareRight.querySelector('.compare-row-thead'),
									scrollLeft = compareRight.scrollLeft;
								
								if(!!compareRightRowThead)
									BX.style(compareRightRowThead, 'marginLeft', '-' + scrollLeft + 'px');
							});
						}
						
						this.compareAdjusted = true;

						this.compareTheadFixed = false;
						this.compareTheadScrolled = false;
						this.compareTheadHidden = false;
						this.checkTopCompareScroll();

						this.scrollbarFixed = false;
						this.checkScrollbarScroll();
					}
				}
			} else {
				this.readjustCompare();
			}
		}
	};

	CompareClass.prototype.readjustCompare = function() {
		var compareLeft = this.wrapObjId.querySelector('.compare-left'),
			compareLeftCompare = !!compareLeft && compareLeft.querySelector('.compare'),
			compareLeftRowThead = !!compareLeftCompare && compareLeftCompare.querySelector('.compare-row-thead'),
			compareLeftRowTheadCol = !!compareLeftRowThead && compareLeftRowThead.querySelectorAll('.compare-col'),
			compareLeftColThead = !!compareLeftRowThead && compareLeftRowThead.querySelector('.compare-col-thead'),
			compareLeftColTfoot = !!compareLeftCompare && compareLeftCompare.querySelector('.compare-col-tfoot'),
			compareLeftColTfootWidth = !!compareLeftColTfoot ? compareLeftColTfoot.getBoundingClientRect().width : 0,			
			compareRight = this.wrapObjId.querySelector('.compare-right'),			
			compareRightCompare = !!compareRight && compareRight.querySelector('.compare'),
			compareRightRowThead = !!compareRightCompare && compareRightCompare.querySelector('.compare-row-thead'),
			compareRightRowTheadCol = !!compareRightRowThead && compareRightRowThead.querySelectorAll('.compare-col');
		
		if(!!compareRightCompare) {
			BX.style(compareRight, 'left', compareLeftColTfootWidth + 'px');
			BX.style(compareRightCompare, 'width', compareLeftCompare.getBoundingClientRect().width + 'px');
			BX.style(compareRightCompare, 'marginLeft', '-' + compareLeftColTfootWidth + 'px');
		}

		if(!!compareLeftColThead && BX.hasClass(compareLeftColThead, 'compare-col-thead-fixed')) {	
			BX.adjust(compareLeftColThead, {
				style: {
					width: compareLeftColTfootWidth + 'px',
					minWidth: compareLeftColTfootWidth + 'px',
					maxWidth: compareLeftColTfootWidth + 'px'
				}
			});
		}

		if(BX.hasClass(compareRightRowThead, 'compare-row-thead-fixed')) {
			if(!!compareRightRowTheadCol) {
				for(var i in compareRightRowTheadCol) {
					if(compareRightRowTheadCol.hasOwnProperty(i) && BX.type.isDomNode(compareRightRowTheadCol[i]) 
						&& compareLeftRowTheadCol.hasOwnProperty(i) && BX.type.isDomNode(compareLeftRowTheadCol[i])
					) {
						BX.adjust(compareRightRowTheadCol[i], {
							style: {
								width: compareLeftRowTheadCol[i].getBoundingClientRect().width + 'px',
								minWidth: compareLeftRowTheadCol[i].getBoundingClientRect().width + 'px',
								maxWidth: compareLeftRowTheadCol[i].getBoundingClientRect().width + 'px'
							}
						});
					}
				}
			}
		}
	};
	
	CompareClass.prototype.checkTopCompareScroll = function() {
		var sMenuInt2 = BX.hasClass(document.body, 'slide-menu-interface-2-0-1-inner'),
			topPanel = document.querySelector('.top-panel'),
			topPanelHeight = 0,
			topPanelThead = !!topPanel && topPanel.querySelector('.top-panel__thead'),
			topPanelTfoot = !!topPanel && topPanel.querySelector('.top-panel__tfoot'),
			compareContainerTop = BX.pos(this.wrapObjId).top,
			compareDiffMobile = this.wrapObjId.querySelector('.compare-different-mobile'),
			compareClearMobile = this.wrapObjId.querySelector('.compare-clear-mobile'),
			compareContainerHeight = this.wrapObjId.offsetHeight
				- (!!compareDiffMobile && !BX.isNodeHidden(compareDiffMobile) ? compareDiffMobile.offsetHeight + Math.abs(parseInt(BX.style(compareDiffMobile, 'margin-top'), 10)) : 0) 
				- (!!compareClearMobile && !BX.isNodeHidden(compareClearMobile) ? compareClearMobile.offsetHeight + Math.abs(parseInt(BX.style(compareClearMobile, 'margin-top'), 10)) : 0),
			compareLeft = this.wrapObjId.querySelector('.compare-left'),
			compareLeftCompare = !!compareLeft && compareLeft.querySelector('.compare'),
			compareLeftCompareTop = !!compareLeftCompare ? BX.pos(compareLeftCompare).top : 0,
			compareLeftRowThead = !!compareLeftCompare && compareLeftCompare.querySelector('.compare-row-thead'),
			compareLeftRowTheadHeight = !!compareLeftRowThead ? compareLeftRowThead.offsetHeight : 0,
			compareLeftRowTheadCol = !!compareLeftRowThead && compareLeftRowThead.querySelectorAll('.compare-col'),
			compareLeftColThead = !!compareLeftRowThead && compareLeftRowThead.querySelector('.compare-col-thead'),			
			compareLeftColTheadWidth = !!compareLeftColThead ? compareLeftColThead.getBoundingClientRect().width : 0,
			compareLeftColTheadHeight = !!compareLeftColThead ? compareLeftColThead.offsetHeight : 0,			
			compareRight = this.wrapObjId.querySelector('.compare-right'),			
			compareRightCompare = !!compareRight && compareRight.querySelector('.compare'),
			compareRightCompareTop = !!compareRightCompare ? BX.pos(compareRightCompare).top : 0,			
			compareRightRowThead = !!compareRightCompare && compareRightCompare.querySelector('.compare-row-thead'),			
			compareRightRowTheadHeight = !!compareRightRowThead ? compareRightRowThead.offsetHeight : 0,
			compareRightRowTheadCol = !!compareRightRowThead && compareRightRowThead.querySelectorAll('.compare-col'),			
			scrollTop = BX.GetWindowScrollPos().scrollTop;
		
		if(window.innerWidth < 1043 && !!compareLeftRowThead) {
			if(!!topPanelThead && !!BX.hasClass(topPanelThead, 'fixed')) {
				topPanelHeight = topPanelThead.offsetHeight;
				if(!!topPanelTfoot && !!BX.hasClass(topPanelTfoot, 'visible'))
					topPanelHeight += topPanelTfoot.offsetHeight;
			}
			
			if(scrollTop + topPanelHeight >= compareLeftCompareTop) {
				if(!this.compareTheadFixed) {
					this.compareTheadFixed = true;
					BX.style(compareLeftRowThead, 'top', topPanelHeight + 'px');
					BX.addClass(compareLeftRowThead, 'compare-row-thead-fixed');
					BX.style(compareLeftCompare, 'paddingTop', compareLeftRowTheadHeight + 'px');
				} else {
					if(!sMenuInt2) {
						if(!this.compareTheadScrolled && topPanelHeight > 0 && scrollTop < this.lastScrollTop) {
							this.compareTheadScrolled = true;
							new BX.easing({
								duration: 300,
								start: {top: Math.abs(parseInt(BX.style(compareLeftRowThead, 'top'), 10))},
								finish: {top: topPanelHeight},
								transition: BX.easing.transitions.linear,
								step: BX.delegate(function(state) {
									if(!!this.compareTheadScrolled)
										BX.style(compareLeftRowThead, 'top', state.top + 'px');								
								}, this)
							}).animate();
						} else if(!!this.compareTheadScrolled && topPanelHeight > 0 && scrollTop > this.lastScrollTop) {								
							this.compareTheadScrolled = false;
							new BX.easing({
								duration: 300,
								start: {top: Math.abs(parseInt(BX.style(compareLeftRowThead, 'top'), 10))},
								finish: {top: topPanelHeight},
								transition: BX.easing.transitions.linear,
								step: function(state) {
									BX.style(compareLeftRowThead, 'top', state.top + 'px');								
								}
							}).animate();
						}
					}

					if(!this.compareTheadHidden && (scrollTop + topPanelHeight + compareLeftRowTheadHeight >= compareContainerTop + compareContainerHeight)) {
						this.compareTheadHidden = true;
						BX.addClass(compareLeftRowThead, 'compare-row-thead-hidden');
					} else if(!!this.compareTheadHidden && (scrollTop + topPanelHeight + compareLeftRowTheadHeight < compareContainerTop + compareContainerHeight)) {
						this.compareTheadHidden = false;
						BX.removeClass(compareLeftRowThead, 'compare-row-thead-hidden');
					}
				}
			} else if(!!this.compareTheadFixed && (scrollTop + topPanelHeight < compareLeftCompareTop)) {
				this.compareTheadFixed = false;
				BX.style(compareLeftRowThead, 'top', '');
				BX.removeClass(compareLeftRowThead, 'compare-row-thead-fixed');
				BX.style(compareLeftCompare, 'paddingTop', '');
			}
		} else if(window.innerWidth >= 1043 && !!compareLeftColThead && !!compareRightRowThead) {				
			if(!!topPanel && !!BX.hasClass(topPanel, 'fixed'))
				topPanelHeight = topPanel.offsetHeight;
			
			if(scrollTop + topPanelHeight >= compareRightCompareTop) {
				if(!this.compareTheadFixed) {
					this.compareTheadFixed = true;

					BX.adjust(compareLeftColThead, {
						style: {
							width: compareLeftColTheadWidth + 'px',
							minWidth: compareLeftColTheadWidth + 'px',
							maxWidth: compareLeftColTheadWidth + 'px',
							height: compareLeftColTheadHeight + 'px',
							top: topPanelHeight + 'px'
						}
					});
					BX.addClass(compareLeftColThead, 'compare-col-thead-fixed');

					BX.style(compareRightRowThead, 'top', topPanelHeight + 'px');
					if(!!compareRightRowTheadCol) {
						for(var i in compareRightRowTheadCol) {
							if(compareRightRowTheadCol.hasOwnProperty(i) && BX.type.isDomNode(compareRightRowTheadCol[i]) 
								&& compareLeftRowTheadCol.hasOwnProperty(i) && BX.type.isDomNode(compareLeftRowTheadCol[i])
							) {
								BX.adjust(compareRightRowTheadCol[i], {
									style: {
										width: compareLeftRowTheadCol[i].getBoundingClientRect().width + 'px',
										minWidth: compareLeftRowTheadCol[i].getBoundingClientRect().width + 'px',
										maxWidth: compareLeftRowTheadCol[i].getBoundingClientRect().width + 'px'
									}
								});
							}
						}
					}
					BX.addClass(compareRightRowThead, 'compare-row-thead-fixed');

					BX.style(compareRightCompare, 'paddingTop', compareRightRowTheadHeight + 'px');
				} else {
					if(!this.compareTheadHidden && (scrollTop + topPanelHeight + compareRightRowTheadHeight >= compareContainerTop + compareContainerHeight)) {
						this.compareTheadHidden = true;
						BX.addClass(compareLeftColThead, 'compare-col-thead-hidden');
						BX.addClass(compareRightRowThead, 'compare-row-thead-hidden');
					} else if(!!this.compareTheadHidden && (scrollTop + topPanelHeight + compareRightRowTheadHeight < compareContainerTop + compareContainerHeight)) {
						this.compareTheadHidden = false;
						BX.removeClass(compareLeftColThead, 'compare-col-thead-hidden');
						BX.removeClass(compareRightRowThead, 'compare-row-thead-hidden');
					}
				}
			} else if(!!this.compareTheadFixed && (scrollTop + topPanelHeight < compareRightCompareTop)) {
				this.compareTheadFixed = false;

				compareLeftColThead.removeAttribute('style');
				BX.removeClass(compareLeftColThead, 'compare-col-thead-fixed');
				
				BX.style(compareRightRowThead, 'top', '');
				if(!!compareRightRowTheadCol) {
					for(var i in compareRightRowTheadCol) {
						if(compareRightRowTheadCol.hasOwnProperty(i) && BX.type.isDomNode(compareRightRowTheadCol[i]))
							compareRightRowTheadCol[i].removeAttribute('style');
					}
				}
				BX.removeClass(compareRightRowThead, 'compare-row-thead-fixed');

				BX.style(compareRightCompare, 'paddingTop', '');
			}
		}
		this.lastScrollTop = scrollTop;
	};
	
	CompareClass.prototype.checkScrollbarScroll = function() {		
		var scrollbar = this.wrapObjId.querySelector('.scroll-element.scroll-x');
		if(!!scrollbar) {
			var compareContainerTop = BX.pos(this.wrapObjId).top,
				compareDiffMobile = this.wrapObjId.querySelector('.compare-different-mobile'),
				compareClearMobile = this.wrapObjId.querySelector('.compare-clear-mobile'),
				compareContainerHeight = this.wrapObjId.offsetHeight
					- (!!compareDiffMobile && !BX.isNodeHidden(compareDiffMobile) ? compareDiffMobile.offsetHeight + Math.abs(parseInt(BX.style(compareDiffMobile, 'margin-top'), 10)) : 0) 
					- (!!compareClearMobile && !BX.isNodeHidden(compareClearMobile) ? compareClearMobile.offsetHeight + Math.abs(parseInt(BX.style(compareClearMobile, 'margin-top'), 10)) : 0),
				scrollTop = BX.GetWindowScrollPos().scrollTop;
			
			if(!this.scrollbarFixed && (scrollTop + window.innerHeight < compareContainerTop + compareContainerHeight)) {
				this.scrollbarFixed = true;
				BX.addClass(scrollbar, 'fixed');
			} else if(!!this.scrollbarFixed && (scrollTop + window.innerHeight >= compareContainerTop + compareContainerHeight)) {
				this.scrollbarFixed = false;
				BX.removeClass(scrollbar, 'fixed');
			}
		}
	};
	
	CompareClass.prototype.different = function(target) {
		var url = new URL(window.location.href);
		
		url.searchParams.set('DIFFERENT', target.checked ? 'Y' : 'N');
		window.location.href = url.toString();
	};
	
	CompareClass.prototype.deleteItem = function() {
		var target = BX.proxy_context,
			productId = target.getAttribute('data-id');

		if(!!productId) {
			var url = new URL(window.location.href),
				ids = url.searchParams.has('ids') ? url.searchParams.get('ids').split("+") : [],
				data = {};

			if(ids.length > 0) {
				for(var i = 0; i < ids.length; i++) {
					if(ids[i] == productId)
						ids.splice(i, 1);
				}
			}
			
			data['ajax_action'] = 'Y';
			if(!this.isExternal) {
				data[this.actionVariable] = 'DELETE_FROM_COMPARE_RESULT';
				data[this.productIdVariable] = productId;
			}
			
			BX.showWait(this.wrapObjId);		
			BX.ajax({
				method: 'POST',
				dataType: 'html',
				url: this.reloadUrl + (!!this.isExternal ? (ids.length > 0 ? 'ids=' + ids.join('%2B') : '') : ''),
				data: data,
				onsuccess: BX.delegate(function(result) {
					BX.closeWait();

					if(url.searchParams.has('ids')) {
						if(ids.length > 0)
							url.searchParams.set('ids', ids.join('+'));
						else
							url.searchParams.delete('ids');

						window.history.pushState("", document.title, url.toString());
					}

					if(!this.isExternal)
						BX.onCustomEvent('OnCompareChange');
					
					this.wrapObjId.innerHTML = result;
					
					this.init();
					
				}, this)
			});
		}
	};

	CompareClass.prototype.clear = function() {
		var url = new URL(window.location.href),
			data = {};
		
		data['ajax_action'] = 'Y';
		data[this.actionVariable] = 'CLEAR_COMPARE';
		data[this.productIdVariable] = 0;

		BX.showWait(this.wrapObjId);		
		BX.ajax({
			method: 'POST',
			dataType: 'html',
			url: this.reloadUrl,
			data: data,
			onsuccess: BX.delegate(function(result) {
				BX.closeWait();

				if(url.searchParams.has('ids')) {
					url.searchParams.delete('ids');
					window.history.pushState("", document.title, url.toString());
				}
				
				BX.onCustomEvent('OnCompareChange');
				
				this.wrapObjId.innerHTML = result;
			}, this)
		});
	};
	
	CompareClass.prototype.setBuyedAdded = function(buyedAddedIds) {
		for(var i in buyedAddedIds) {
			if(buyedAddedIds.hasOwnProperty(i)) {
				var buyBtn = this.wrapObjId.querySelectorAll('[data-entity="addBasket"][data-id="' + i + '"]');
				if(!!buyBtn) {
					for(var j in buyBtn) {
						if(buyBtn.hasOwnProperty(j)) {
							if(!!buyedAddedIds[i]) {
								BX.adjust(buyBtn[j], {
									props: {
										className: 'btn btn-buy-ok',
										title: BX.message('ADD_BASKET_OK_MESSAGE')
									},
									html: '<i class="icon-ok-b"></i>'
								});
								BX.unbindAll(buyBtn[j]);
								BX.bind(buyBtn[j], "click", BX.delegate(this.basketRedirect, this));				
							} else {
								BX.adjust(buyBtn[j], {
									props: {
										className: 'btn btn-buy',
										title: BX.message('ADD_BASKET_MESSAGE')
									},
									html: '<i class="icon-cart"></i>'
								});
								BX.unbindAll(buyBtn[j]);
								BX.bind(buyBtn[j], "click", BX.proxy(this.add2Basket, this));
							}
						}
					}
				}
			}
		}
	};
	
	CompareClass.prototype.add2Basket = function() {
		var target = BX.proxy_context,
			productId = target.getAttribute('data-id');
		
		if(!!productId) {
			var data = {};
			data['ajax_action'] = 'Y';
			data[this.actionVariable] = 'COMPARE_ADD2BASKET';
			data[this.productIdVariable] = productId;

			BX.showWait(this.wrapObjId);
			BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: this.reloadUrl,
				data: data,
				onsuccess: BX.delegate(function(arResult) {
					BX.closeWait();
					if(arResult.STATUS == 'OK') {
						var buyedAddedIds = {};
						buyedAddedIds[productId] = true;
						this.setBuyedAdded(buyedAddedIds);
						BX.onCustomEvent('OnBasketChange');
					}
				}, this)
			});
		}
	};
	
	CompareClass.prototype.basketRedirect = function() {
		window.location.href = BX.message('BASKET_URL');
	}
	
	return CompareClass;
})();