(function() {
	'use strict';

	if(!!window.JCUserComponent)
		return;

	window.JCUserComponent = function(params) {
		this.container = params.container;
		this.isAuth = params.isAuth;
		
		if(window.frameCacheVars !== undefined)
			BX.addCustomEvent('onFrameDataReceived', BX.proxy(this.init, this));
		else
			BX.ready(BX.proxy(this.init, this));
	};

	window.JCUserComponent.prototype = {
		init: function() {
			var obUser = BX(this.container);
			if(!!obUser) {
				if(!this.isAuth) {
					BX.bind(obUser, 'click', BX.proxy(this.showSlidePanelLogin, this));
				} else {
					this.userMenuPopup = obUser.parentNode.querySelector('[data-role="dropdownContent"]');
					if(this.userMenuPopup) {
						BX.bind(obUser, 'click', BX.proxy(this.showUserMenuDropDownPopup, this));
						BX.bind(document, 'click', BX.proxy(this.hideUserMenuDropDownPopup, this));
					}
				}
			}
			
			BX.addCustomEvent(this, 'showSlidePanelLoginRequest', BX.proxy(this.showSlidePanelLoginRequest, this));
		},
			
		showSlidePanelLoginRequest: function(sPanelContent) {
			BX.ajax({
				url: BX.message('SITE_DIR') + 'ajax/slide_panel.php',
				method: 'POST',
				dataType: 'json',
				timeout: 60,
				data: {
					action: 'login',
					REQUEST_URI: window.location.pathname
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

		showSlidePanelLogin: function(e) {
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
									className: 'icon-user'
								}
							}),						
							BX.create('SPAN', {
								props: {
									className: 'slide-panel__title'
								},
								html: BX.message('USER_SLIDE_PANEL_TITLE')
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
					BX.onCustomEvent(this, 'showSlidePanelLoginRequest', [sPanelContent]);
				
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
					
				BX.addClass(document.body, 'slide-panel-active');
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
		},

		showUserMenuDropDownPopup: function(e) {
			BX.style(this.userMenuPopup, 'display', BX.isNodeHidden(this.userMenuPopup) ? '' : 'none');
			
			e.preventDefault();
		},

		hideUserMenuDropDownPopup: function(e) {
			var target = BX.getEventTarget(e);
			if(!BX.findParent(target, {className: 'top-panel__user'}) && !BX.hasClass(target, 'top-panel__user')) {
				BX.style(this.userMenuPopup, 'display', 'none');
				e.stopPropagation();
			}
		}
	};
})();