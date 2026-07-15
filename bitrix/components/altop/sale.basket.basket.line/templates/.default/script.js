'use strict';

function BitrixSmallCart() {}

BitrixSmallCart.prototype = {
	activate: function() {
		this.cartElement = BX(this.cartId);
		
		BX.addCustomEvent(window, 'OnBasketChange', this.closure('refreshCart', {'action': 'addToCart'}));
		BX.addCustomEvent(window, 'OnBasketDelayChange', this.closure('refreshCart', {'action': 'addToDelay'}));
		
		if(this.arParams.BASKET_VIEW == 'RIGHT' &&
			window.location.href.indexOf('/personal/cart/') == -1 &&
			window.location.href.indexOf('/personal/order/make/') == -1
		) {
			if(window.frameCacheVars !== undefined)
				BX.addCustomEvent('onFrameDataReceived', BX.proxy(this.init, this));
			else
				BX.ready(BX.proxy(this.init, this));
		}
	},

	init: function() {
		this.cart = this.cartElement.querySelector('[data-entity="cart"]');
		if(this.cart)
			BX.bind(this.cart, 'click', BX.proxy(this.showSlidePanelCart, this));

		BX.addCustomEvent(this, 'showSlidePanelCartRequest', BX.proxy(this.showSlidePanelCartRequest, this));
	},
	
	closure: function(fname, data) {
		var obj = this;
		return data
			? function(){obj[fname](data)}
			: function(arg1){obj[fname](arg1)};
	},
	
	refreshCart: function(data) {
		data.requestUri = window.location.pathname;
		data.sessid = BX.bitrix_sessid();
		data.siteId = this.siteId;
		data.templateName = this.templateName;
		data.arParams = this.arParams;
		BX.ajax({
			url: this.ajaxPath,
			method: 'POST',
			dataType: 'html',
			data: data,
			onsuccess: data.action == 'addToDelay' ? this.closure('setCartDelayBody') : this.closure('setCartBody')
		});
	},
		
	setCartBody: function(result) {		
		if(this.cartElement) {
			this.cartElement.innerHTML = result;
			
			this.cart = this.cartElement.querySelector('[data-entity="cart"]');
			if(this.cart) {
				BX.addClass(this.cart, "shake shake-constant");
				setTimeout(BX.delegate(function() {
					BX.removeClass(this.cart, "shake shake-constant");
				}, this), 1000);

				if(this.arParams.BASKET_VIEW == 'RIGHT' &&
					window.location.href.indexOf('/personal/cart/') == -1 &&
					window.location.href.indexOf('/personal/order/make/') == -1
				) {
					BX.bind(this.cart, 'click', BX.proxy(this.showSlidePanelCart, this));
				}
			}
		}
	},

	setCartDelayBody: function(result) {
		if(this.cartElement) {
			this.cartElement.innerHTML = result;

			this.cart = this.cartElement.querySelector('[data-entity="cart"]');
			if(this.cart) {
				if(this.arParams.BASKET_VIEW == 'RIGHT' &&
					window.location.href.indexOf('/personal/cart/') == -1 &&
					window.location.href.indexOf('/personal/order/make/') == -1
				) {
					BX.bind(this.cart, 'click', BX.proxy(this.showSlidePanelCart, this));
				}
			}

			var cartDelay = this.cartElement.querySelector('[data-entity="delay"]');
			if(cartDelay) {
				BX.addClass(cartDelay, "shake shake-constant");
				setTimeout(function() {
					BX.removeClass(cartDelay, "shake shake-constant");
				}, 1000);
			}
		}
	},

	showSlidePanelCartRequest: function(sPanelCartContent) {
		let xhReq = new XMLHttpRequest();
		xhReq.open('GET', '/bitrix/components/altop/sale.basket.basket/templates/slide_panel/style.min.css');
		xhReq.onreadystatechange = function() {
			if(xhReq.readyState === XMLHttpRequest.DONE && xhReq.status === 200) {
				BX.loadCSS(xhReq.responseURL + '?' + Date.parse(xhReq.getResponseHeader('Last-Modified')));
			}
		}
		xhReq.send();

		BX.ajax({
			url: BX.message('SITE_DIR') + 'ajax/slide_panel.php',
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {
				action: 'cart',
				REQUEST_URI: window.location.pathname
			},
			onsuccess: BX.delegate(function(result) {
				if(!result.content || !result.JS) {
					BX.cleanNode(sPanelCartContent);
					sPanelCartContent.appendChild(BX.create('DIV', {
						props: {
							className: 'slide-panel-cart__form'
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
							var processed = BX.processHTML(result.content);
							
							sPanelCartContent.innerHTML = processed.HTML;
							
							BX.ajax.processScripts(processed.SCRIPT);
						}, this)
					);
				}
				
				$(sPanelCartContent).scrollbar();
			}, this)
		});
	},

	showSlidePanelCart: function(e) {
		var sPanelCart = BX.create('DIV', {props: {className: 'slide-panel-cart slidePanelCartRightIn'}});		
		sPanelCart.appendChild(
			BX.create('DIV', {
				props: {
					className: 'slide-panel-cart__title-wrap'
				},
				children: [
					BX.create('I', {
						props: {
							className: 'icon-cart'
						}
					}),						
					BX.create('SPAN', {
						props: {
							className: 'slide-panel-cart__title'
						},
						html: this.cart.title
					}),
					BX.create('SPAN', {
						props: {
							className: 'slide-panel-cart__close'
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

		sPanelCart.appendChild(
			BX.create('DIV', {
				props: {
					className: 'slide-panel-cart__content scrollbar-inner'
				},
				children: [
					BX.create('DIV', {
						props: {
							className: 'slide-panel-cart__loader'
						},
						html: '<div><span></span></div>'
					})
				]
			})
		);
					
		var sPanelCartContent = sPanelCart.querySelector('.slide-panel-cart__content');
		if(!!sPanelCartContent)
			BX.onCustomEvent(this, 'showSlidePanelCartRequest', [sPanelCartContent]);
		
		var scrollWidth = window.innerWidth - document.body.clientWidth;
		if(scrollWidth > 0) {
			BX.style(document.body, 'padding-right', scrollWidth + 'px');
			
			var topPanel = document.body.querySelector('.top-panel');
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

			var sectionPanel = document.body.querySelector('.catalog-section-panel');
			if(!!sectionPanel && BX.hasClass(sectionPanel, 'fixed'))
				BX.style(sectionPanel, 'padding-right', scrollWidth + 'px');

			var tabsPanel = document.body.querySelector('[data-entity="tabs"]');
			if(!!tabsPanel && BX.hasClass(tabsPanel, 'fixed'))
				BX.style(tabsPanel, 'padding-right', scrollWidth + 'px');

			var objectsMap = document.body.querySelector('.objects-map');
			if(!!objectsMap)
				BX.style(objectsMap, 'padding-right', scrollWidth + 'px');

			var catalogCompareList = document.querySelector('.catalog-compare-list');
			if(!!catalogCompareList && BX.hasClass(catalogCompareList, 'active'))
				BX.style(catalogCompareList, 'margin-left', '-' + scrollWidth/2 + 'px');
		}
			
		var scrollTop = BX.GetWindowScrollPos().scrollTop;
		if(!!scrollTop && scrollTop > 0)
			BX.style(document.body, 'top', '-' + scrollTop + 'px');
		
		BX.addClass(document.body, 'slide-panel-cart-active');
		
		document.body.appendChild(sPanelCart);
		
		document.body.appendChild(
			BX.create('DIV', {
				props: {
					className: 'modal-backdrop slide-panel-cart__backdrop fadeInBig'
				}
			})
		);

		e.stopPropagation();
	}
};
