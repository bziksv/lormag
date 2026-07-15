(function() {
	'use strict';

	BX.namespace('BX.Sale.GeoLocationComponent');
	
	BX.Sale.GeoLocationComponent = {
		init: function(params) {
			this.siteId = params.siteId || '';
			this.componentPath = params.componentPath || '';		
			this.parameters = params.parameters || '';
			
			this.container = BX(params.container);

			BX.bind(this.container, 'click', BX.delegate(this.sPanelGeoLocation, this));

			BX.addCustomEvent(this, 'sPanelGeoLocationRequest', BX.proxy(this.sPanelGeoLocationRequest, this));
		},
			
		sPanelGeoLocationRequest: function(sPanelContent) {
			BX.ajax({
				url: this.componentPath + '/ajax.php',
				method: 'POST',
				dataType: 'json',
				timeout: 60,
				data: {
					action: 'sPanelGeoLocationRequest',
					parameters: this.parameters,
					siteId: this.siteId,					
					siteServerName: BX.message('SITE_SERVER_NAME'),
					languageId: BX.message('LANGUAGE_ID')
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
								var processed = BX.processHTML(result.content);
								
								sPanelContent.innerHTML = processed.HTML;
								
								BX.ajax.processScripts(processed.SCRIPT);
							}, this)
						);
					}
					
					$(sPanelContent).scrollbar();
				}, this)
			});
		},
			
		sPanelGeoLocation: function(e) {
			var sPanel = document.body.querySelector('.slide-panel');
			if(!!sPanel) {
				sPanel.appendChild(
					BX.create('DIV', {
						props: {
							className: 'slide-panel__title-wrap'
						},
						children: [
							BX.create('I', {
								props: {
									className: 'icon-delivery'
								}
							}),						
							BX.create('SPAN', {
								props: {
									className: 'slide-panel__title'
								},
								html: BX.message('GEO_LOCATION_SLIDE_PANEL_TITLE')
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
					BX.onCustomEvent(this, 'sPanelGeoLocationRequest', [sPanelContent]);
				
				if(!this.popupPanel) {
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

						if(!!this.obTabsBlock && !!this.tabsPanelFixed)
							BX.style(this.obTabsBlock, 'padding-right', scrollWidth + 'px');

						var catalogCompareList = document.querySelector('.catalog-compare-list');
						if(!!catalogCompareList && BX.hasClass(catalogCompareList, 'active'))
							BX.style(catalogCompareList, 'margin-left', '-' + scrollWidth/2 + 'px');
					}

					var scrollTop = BX.GetWindowScrollPos().scrollTop;
					if(!!scrollTop && scrollTop > 0)
						BX.style(document.body, 'top', '-' + scrollTop + 'px');
				}
				
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
	}	
})();