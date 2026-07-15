(function() {
	'use strict';

	if(!!window.JCGeoDeliverySlidePanelComponent)
		return;

	window.JCGeoDeliverySlidePanelComponent = function(params) {
		this.componentPath = params.componentPath || '';
		this.stepQuantity = params.stepQuantity || '';
		this.parameters = params.parameters || '';
		this.siteId = params.siteId || '';
		this.customSiteId = params.customSiteId || '';
		this.geoDeliveryContainer = BX(params.geoDeliveryContainerId) || '';
		
		this.itemsContainer = null;
		this.items = [];
		
		this.obQuantity = null;
		this.obQuantityUp = null;
		this.obQuantityDown = null;

		this.precision = 6;
		this.precisionFactor = Math.pow(10, this.precision);

		this.container = BX(params.container);
		
		BX.ready(BX.delegate(this.init, this));
	};

	window.JCGeoDeliverySlidePanelComponent.prototype = {
		init: function() {
			this.itemsContainer = this.container.querySelector('[data-entity="items"]');
			if(!!this.itemsContainer) {
				this.items = this.itemsContainer.querySelectorAll('[data-entity="item"]');
				if(this.items.length) {
					for(var i in this.items) {
						if(this.items.hasOwnProperty(i) && BX.type.isDomNode(this.items[i])) {
							BX.bind(this.items[i], 'click', BX.delegate(this.changeItem, this));
						}
					}
				}
			}
			
			this.stepQuantity = Math.round(this.stepQuantity * this.precisionFactor) / this.precisionFactor;

			this.obQuantity = this.container.querySelector('[data-entity="quantity"]');
			if(this.obQuantity)
				BX.bind(this.obQuantity, 'change', BX.delegate(this.quantityChange, this));
			
			this.obQuantityUp = this.container.querySelector('[data-entity="quantity-up"]');
			if(this.obQuantityUp)
				BX.bind(this.obQuantityUp, 'click', BX.delegate(this.quantityUp, this));
			
			this.obQuantityDown = this.container.querySelector('[data-entity="quantity-down"]');
			if(this.obQuantityDown)
				BX.bind(this.obQuantityDown, 'click', BX.delegate(this.quantityDown, this));

			var propCollection = this.container.querySelectorAll('input');
			if(!!propCollection) {
				for(var i in propCollection) {
					if(propCollection.hasOwnProperty(i) && BX.type.isDomNode(propCollection[i])) {
						switch(propCollection[i].type.toLowerCase()) {
							case 'radio':
							case 'checkbox':
								BX.bind(propCollection[i], 'change', BX.delegate(this.recalculateDeliveryItems, this));
								break;
							default:
								if(propCollection[i].name) {
									BX.bind(propCollection[i], 'change', BX.delegate(function() {
										var target = BX.proxy_context;
										if(target.value)
											this.recalculateDeliveryItems();
									}, this));
								}
								break;
						}
					}
				}
			}
		},

		changeItem: function() {
			var target = BX.proxy_context;
			if(!BX.hasClass(target, 'slide-panel-geo-delivery-item-active')) {
				for(var i in this.items) {
					if(this.items.hasOwnProperty(i) && BX.type.isDomNode(this.items[i])) {
						if(BX.hasClass(this.items[i], 'slide-panel-geo-delivery-item-active'))
							BX.removeClass(this.items[i], 'slide-panel-geo-delivery-item-active');
					}
				}

				BX.addClass(target, 'slide-panel-geo-delivery-item-active');
			}
		},

		quantityUp: function() {
			var curValue = 0;

			curValue = parseFloat(this.obQuantity.value);
			if(!isNaN(curValue)) {
				curValue += this.stepQuantity;
				curValue = Math.round(curValue * this.precisionFactor) / this.precisionFactor;
				this.obQuantity.value = curValue;
				this.recalculateDeliveryItems();
			}
		},

		quantityDown: function() {
			var curValue = 0,
				boolSet = true;
			
			curValue = parseFloat(this.obQuantity.value);
			if(!isNaN(curValue)) {
				curValue -= this.stepQuantity;

				if(curValue < this.stepQuantity)
					boolSet = false;
				
				if(boolSet) {
					curValue = Math.round(curValue * this.precisionFactor) / this.precisionFactor;
					this.obQuantity.value = curValue;
					this.recalculateDeliveryItems();
				}
			}
		},

		quantityChange: function() {
			var curValue = 0,
				intCount;

			curValue = parseFloat(this.obQuantity.value);
			if(!isNaN(curValue)) {
				if(curValue < this.stepQuantity) {
					curValue = this.stepQuantity;
				} else {
					intCount = Math.round(Math.round(curValue * this.precisionFactor / this.stepQuantity) / this.precisionFactor) || 1;
					curValue = intCount <= 1 ? this.stepQuantity : intCount * this.stepQuantity;
					curValue = Math.round(curValue * this.precisionFactor) / this.precisionFactor;
				}
				this.obQuantity.value = curValue;
			} else {
				this.obQuantity.value = this.stepQuantity;
			}
			this.recalculateDeliveryItems();
		},
			
		recalculateDeliveryItems: function() {
			if(!!this.itemsContainer) {
				this.itemsContainer.appendChild(BX.create('DIV', {
					props: {
						className: 'slide-panel__loader'
					},
					html: '<div><span></span></div>'
				}));
				
				if(this.items.length) {
					for(var i in this.items) {
						if(this.items.hasOwnProperty(i) && BX.type.isDomNode(this.items[i])) {
							this.items[i].style.opacity = 0.2;
						}
					}
				} else {
					var alert = this.itemsContainer.querySelector('.alert');
					if(!!alert)
						alert.style.opacity = 0.2;
				}
				
				var data = {
					action: 'recalculateDeliveryItems',
					siteId: this.siteId,
					customSiteId: this.customSiteId,
					siteServerName: BX.message('SITE_SERVER_NAME'),
					parameters: this.parameters
				};

				var propCollection = this.container.querySelectorAll('input');
				if(!!propCollection) {
					for(var i in propCollection) {
						if(propCollection.hasOwnProperty(i) && BX.type.isDomNode(propCollection[i])) {
							switch(propCollection[i].type.toLowerCase()) {
								case 'radio':
								case 'checkbox':
									if(propCollection[i].checked)
										data[propCollection[i].name] = propCollection[i].value;
									break;
								default:
									if(propCollection[i].name)
										data[propCollection[i].name] = propCollection[i].value;
									break;
							}
						}
					}
				}
				
				BX.ajax({
					url: this.componentPath + '/ajax.php',
					method: 'POST',
					dataType: 'json',
					timeout: 60,
					data: data,
					onsuccess: BX.delegate(function(result) {
						BX.cleanNode(this.itemsContainer);
						
						if(!result.items || !result.JS) {
							var alert = BX.create('DIV', {
								props: {
									className: 'alert alert-error'
								},
								style: {
									opacity: 0.2
								},
								html: BX.message('SLIDE_PANEL_UNDEFINED_ERROR')
							});
							
							this.itemsContainer.appendChild(alert);

							new BX.easing({
								duration: 2000,
								start: {opacity: 20},
								finish: {opacity: 100},
								transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
								step: function(state) {
									alert.style.opacity = state.opacity / 100;
								},
								complete: function() {
									alert.removeAttribute('style');
								}
							}).animate();
						} else {
							var processed = BX.processHTML(result.items),
								temporaryNode = BX.create('DIV');

							temporaryNode.innerHTML = processed.HTML;
							
							this.items = temporaryNode.querySelectorAll('[data-entity="item"]');							
							if(this.items.length) {
								for(var i in this.items) {
									if(this.items.hasOwnProperty(i)) {
										this.items[i].style.opacity = 0.2;
										BX.bind(this.items[i], 'click', BX.delegate(this.changeItem, this));
										this.itemsContainer.appendChild(this.items[i]);
									}
								}

								new BX.easing({
									duration: 2000,
									start: {opacity: 20},
									finish: {opacity: 100},
									transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
									step: BX.delegate(function(state) {
										for(var i in this.items) {
											if(this.items.hasOwnProperty(i)) {
												this.items[i].style.opacity = state.opacity / 100;
											}
										}
									}, this),
									complete: BX.delegate(function() {
										for(var i in this.items) {
											if(this.items.hasOwnProperty(i)) {
												this.items[i].removeAttribute('style');
											}
										}
									}, this)
								}).animate();
							} else {
								var alert = temporaryNode.querySelector('.alert');
								if(!!alert) {
									alert.style.opacity = 0.2;
									this.itemsContainer.appendChild(alert);

									new BX.easing({
										duration: 2000,
										start: {opacity: 20},
										finish: {opacity: 100},
										transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
										step: function(state) {
											alert.style.opacity = state.opacity / 100;
										},
										complete: function() {
											alert.removeAttribute('style');
										}
									}).animate();
								}
							}

							if(!!result.data) {
								var geoDeliveryCity = this.geoDeliveryContainer.querySelector('[data-entity="city"]');
								if(!!geoDeliveryCity)
									geoDeliveryCity.innerHTML = !!result.data.CITY ? result.data.CITY : BX.message('CATALOG_ELEMENT_GEO_DELIVERY_LOCATION');
								
								var geoDeliveryFrom = this.geoDeliveryContainer.querySelector('.product-item-detail-geo-delivery-from');
								if(!!geoDeliveryFrom)
									geoDeliveryFrom.innerHTML = !!result.data.MIN_PRICE ? BX.message('CATALOG_ELEMENT_GEO_DELIVERY_FROM') : BX.message('CATALOG_ELEMENT_GEO_DELIVERY_UNDEFINED');
								
								var geoDeliveryPrice = this.geoDeliveryContainer.querySelector('.product-item-detail-geo-delivery-price');
								if(!!geoDeliveryPrice)
									geoDeliveryPrice.innerHTML = !!result.data.MIN_PRICE ? result.data.MIN_PRICE : '';

								var topPanelGeoLocation = document.body.querySelector('.top-panel__geo-location');
								if(!!topPanelGeoLocation) {
									var topPanelGeoLocationCity = topPanelGeoLocation.querySelector('[data-entity="city"]');
									if(!!topPanelGeoLocationCity)
										topPanelGeoLocationCity.innerHTML = !!result.data.CITY ? result.data.CITY : BX.message('CATALOG_ELEMENT_GEO_DELIVERY_LOCATION');
								}
							}
						}
					}, this)
				});
			}
		}
	}
})();