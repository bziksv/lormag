(function() {
	'use strict';

	if(!!window.JCSearchYandexComponent)
		return;

	window.JCSearchYandexComponent = function(params) {
		this.componentPath = params.componentPath || '';
		this.showCheckAvailable = params.showCheckAvailable || '';
		this.parameters = params.parameters || '';
		this.container = BX(params.container);

		this.cache = [];
		this.cache_key = null;
		
		this.startText = '';		
		this.oldValue = null;

		this.timeout = null;
		
		this.popup = null;		
		this.input = null;
		this.wait = null;
		this.inputContainer = null;
		this.checkAvailable = null;
		this.checkAvailableInput = null;
		this.result = null;

		this.available = 'N';
		this.oldAvailable = null;

		this.sectionsContainer = null;
		this.sectionsContainerHeight = 92;
		this.sections = null;

		this.sectionBtnSpan = BX.create('SPAN');
		this.sectionBtnI = BX.create('I');

		this.sectionBtn = BX.create('DIV', {
			props: {
				className: 'search-yandex-section-btn-container'
			},
			children: [
				BX.create('DIV', {
					props: {
						className: 'search-yandex-section search-yandex-section-btn'
					},
					children: [
						this.sectionBtnSpan,
						this.sectionBtnI
					],
					events: {
						click: BX.proxy(this.showHideSections, this)
					}
				})
			]
		});

		BX.isYandexSearchInputFocused = false;
		BX.yandexSearchInputValue = null;
		
		BX.ready(BX.proxy(this.init, this));
	};

	window.JCSearchYandexComponent.prototype = {
		init: function() {
			var input = this.container.querySelector('input[name="q"]');
			if(!!input)
				BX.bind(input, 'focus', BX.proxy(this.onShowPopup, this));

			if(!!this.showCheckAvailable && BX.getCookie(BX.message('COOKIE_NAME') + '_ENEXT_SEARCH_YANDEX_AVAILABLE'))
				this.available = BX.getCookie(BX.message('COOKIE_NAME') + '_ENEXT_SEARCH_YANDEX_AVAILABLE');

			BX.bind(window, 'resize', BX.proxy(this.adjustSectionBtn, this));
		},
			
		showResult: function(result) {
			if(BX.type.isString(result)) {
				this.result.style.display = result.length ? 'flex' : 'none';

				this.result.innerHTML = result;
				this.result.scrollTo(0, 0);

				this.sectionsContainer = this.result.querySelector('.search-yandex-sections');
				if(!!this.sectionsContainer) {
					this.sections = this.result.querySelectorAll('.search-yandex-section');
					if(!!this.sections) {
						for(var i in this.sections) {
							if(this.sections.hasOwnProperty(i)) {
								BX.bind(this.sections[i], 'click', BX.proxy(this.onShowSectionItems, this));
							}
						}

						this.sectionBtnAdjusted = false;
						this.adjustSectionBtn();
					}
				}
			}
		},
			
		onChange: function() {
			if((this.input.value != this.oldValue || this.available != this.oldAvailable) && this.input.value != this.startText) {
				if(this.timeout)
					clearTimeout(this.timeout);

				if(this.input.value != this.oldValue)
					this.oldValue = this.input.value;
				
				if(this.available != this.oldAvailable)
					this.oldAvailable = this.available;
				
				if(this.input.value.length >= 1) {
					this.cache_key = this.input.name + '|' + this.input.value + '|available|' + this.available;
					if(this.cache[this.cache_key] == null) {
						this.timeout = setTimeout(BX.delegate(function() {					
							this.wait.style.display = 'block';
							
							BX.ajax({
								url: this.componentPath + '/ajax.php',
								method: 'POST',
								dataType: 'json',
								timeout: 60,
								data: {
									action: 'onChange',
									text: encodeURIComponent(this.input.value),
									per_page: 100,
									available: this.available,
									siteCharset: BX.message('SITE_CHARSET'),
									parameters: this.parameters
								},
								onsuccess: BX.delegate(function(result) {
									var html = '';

									if(!!result.SECTIONS) {
										html += '<div class="search-yandex-sections">';
											for(var i in result.SECTIONS) {
												if(result.SECTIONS.hasOwnProperty(i)) {												
													html += '<div class="search-yandex-section' + (i == 0 ? ' active' : '') + '" data-id="' + result.SECTIONS[i].id + '">';
														html += result.SECTIONS[i].value + '<span>' + result.SECTIONS[i].found + '</span>';
													html += '</div>';
												}
											}
										html += '</div>';
									}

									if(!!result.ITEMS) {
										html += '<div class="search-yandex-items">';
											for(var i in result.ITEMS) {
												if(result.ITEMS.hasOwnProperty(i)) {
													html += '<div class="search-yandex-item" data-section-id="' + result.ITEMS[i].categoryId + '">';
														html += '<div class="search-yandex-item-image">';
															if(result.ITEMS[i].snippet.length) {
																html += '<img src="' + result.ITEMS[i].snippet + '" width="50" height="50" alt="' + result.ITEMS[i].name + '" />';
															} else {
																html += '<img src="' + BX.message('SITE_TEMPLATE_PATH') + '/images/no_photo.png" width="50" height="50" alt="' + result.ITEMS[i].name + '" />';
															}
														html += '</div>';
														html += '<div class="search-yandex-item-info">';
															html += '<div class="search-yandex-item-block">';
																html += '<a target="_blank" class="search-yandex-item-title" href="' + result.ITEMS[i].url + '">' + result.ITEMS[i].name + '</a>';
																if(!!result.ITEMS[i].parameters) {
																	html += '<div class="search-yandex-item-props">';
																	for(var j in result.ITEMS[i].parameters) {
																		if(result.ITEMS[i].parameters.hasOwnProperty(j)) {
																			html += '<div class="search-yandex-item-prop">' + result.ITEMS[i].parameters[j].name + ': ' + result.ITEMS[i].parameters[j].value + '</div>';
																		}
																	}
																	html += '</div>';
																}
															html += '</div>';
															if(result.ITEMS[i].price > 0) {
																html += '<div class="search-yandex-item-price">';
																	html += '<div class="search-yandex-item-price-current">' + BX.Currency.currencyFormat(result.ITEMS[i].price, result.ITEMS[i].currencyId, true) + '</div>';
																	if(result.ITEMS[i].oldPrice != null) {
																		html += '<div class="search-yandex-item-price-old">' + BX.Currency.currencyFormat(result.ITEMS[i].oldPrice, result.ITEMS[i].currencyId, true) + '</div>';
																	}
																html += '</div>';
															}
														html += '</div>';
													html += '</div>';
												}
											}
										html += '</div>';
									}
									
									this.cache[this.cache_key] = html;							
									
									this.showResult(html);							
									
									this.wait.style.display = 'none';
								}, this)
							});
						}, this), 500);
					} else {
						this.showResult(this.cache[this.cache_key]);
					}
				} else {
					this.result.innerHTML = '';
					this.result.style.display = 'none';
				}
			} else if(this.input.value == this.startText) {
				this.result.innerHTML = '';
				this.result.style.display = 'none';
			}
		},

		onChangeCheckAvailable: function() {
			if(this.checkAvailableInput.checked)
				this.available = 'Y';
			else
				this.available = 'N';
			
			BX.setCookie(BX.message('COOKIE_NAME') + '_ENEXT_SEARCH_YANDEX_AVAILABLE', this.available, {expires: 32832000, path: '/', secure: false});

			this.onChange();
		},

		onShowSectionItems: function() {
			var target = BX.proxy_context,
				id = target.getAttribute('data-id');

			if(!!id) {
				var sections = this.result.querySelectorAll('.search-yandex-section');
				if(!!sections) {
					for(var i in sections) {
						if(sections.hasOwnProperty(i)) {
							if(sections[i].getAttribute('data-id') == id)
								BX.addClass(sections[i], 'active');
							else
								BX.removeClass(sections[i], 'active');
						}
					}
				}

				var items = this.result.querySelectorAll('.search-yandex-item');
				if(!!items) {
					for(var i in items) {
						if(items.hasOwnProperty(i)) {
							if(items[i].getAttribute('data-section-id') == id || id == 0)
								items[i].style.display = 'flex';
							else
								items[i].style.display = 'none';
						}
					}
				}
			}
		},

		onShowPopup: function() {
			var target = BX.proxy_context,
				slidePanelContent = BX.findParent(target, {className: 'slide-panel__content'});
			
			if(!!slidePanelContent && !BX.isYandexSearchInputFocused) {
				BX.isYandexSearchInputFocused = true;
				
				this.input = target;
				BX.addClass(this.input, 'active');
				
				this.startText = this.oldValue = this.input.value;
				
				BX.bind(this.input, 'bxchange', BX.proxy(this.onChange, this));
				
				this.wait = this.container.querySelector('.search-yandex-wait');
				if(!this.wait) {
					this.wait = BX.create('DIV', {
						props: {
							className: 'search-yandex-wait'
						},
						html: '<div><span></span></div>'
					});
					
					this.container.appendChild(this.wait);
				}

				if(!!this.showCheckAvailable) {
					this.checkAvailableInput = BX.create('INPUT', {
						props: {
							type: 'checkbox',
							checked: this.available == 'Y' ? true : false
						}
					});

					this.checkAvailable = BX.create('DIV', {
						props: {
							className: 'search-yandex-check-available'
						},
						children: [
							BX.create('LABEL', {
								children: [
									this.checkAvailableInput,
									BX.create('SPAN', {
										props: {
											className: 'search-yandex-check-available-check'
										},
										children: [
											BX.create('I', {
												props: {
													className: 'icon-ok-b'
												}
											})
										]
									}),
									BX.create('SPAN', {
										props: {
											className: 'search-yandex-check-available-title'
										},
										html: BX.message('SEARCH_YANDEX_AVAILABLE')
									})
								],
								events: {
									change: BX.proxy(this.onChangeCheckAvailable, this)
								}
							})
						]
					});
				}
				
				this.result = BX.create('DIV', {
					props: {
						className: 'search-yandex-result'
					}
				});
				
				if(!!this.showCheckAvailable)
					slidePanelContent.appendChild(this.checkAvailable);
				slidePanelContent.appendChild(this.result);

				if(BX.yandexSearchInputValue != null && !!this.cache[this.input.name + '|' + BX.yandexSearchInputValue + '|available|' + this.available]) {
					this.input.value = BX.yandexSearchInputValue;
					this.showResult(this.cache[this.input.name + '|' + this.input.value + '|available|' + this.available]);
				}

				if(('webkitSpeechRecognition' in window) && window.location.protocol == 'https:') {
					var recognition = new webkitSpeechRecognition(),
						recognizing = false;
					
					recognition.lang = 'ru-Ru';
					recognition.continuous = false;
					recognition.interimResults = false;
					
					var microphone = this.container.querySelector('.title-search-microphone');
					if(!microphone) {
						microphone = BX.create('DIV', {
							props: {
								className: 'title-search-microphone'
							},
							children: [
								BX.create('I', {
									props: {
										className: 'icon-microphone'
									}
								})
							]
						});
						
						this.container.appendChild(microphone);
					}
					
					if(!BX.getCookie(BX.message('COOKIE_NAME') + '_ENEXT_SITE_VISITED')) {
						BX.addClass(microphone, 'shadow-pulse');
						BX.setCookie(BX.message('COOKIE_NAME') + '_ENEXT_SITE_VISITED', true, {expires: 32832000, path: '/', secure: false});
					}
						
					BX.bind(microphone, 'click', function() {
						if(recognizing) {
							recognition.stop();
							BX.removeClass(microphone, 'active shadow-pulse-infinite');
							return;
						}										
						recognition.start();
					});
					
					recognition.onstart = function() {
						recognizing = true;				
						BX.addClass(microphone, 'active shadow-pulse-infinite');
					};
					
					recognition.onerror = function(event) {
						console.log(event.error);
					};

					recognition.onend = function() {
						recognizing = false;
						BX.removeClass(microphone, 'active shadow-pulse-infinite');
					};

					recognition.onresult = BX.delegate(function(event) {
						var result = event.results[event.resultIndex];
						if(!!result) {
							this.input.value = result[0].transcript;					
							if(result.isFinal)
								this.onChange();
						}
					}, this);
				}
			} else if(!slidePanelContent) {
				this.popup = BX.create('DIV', {
					props: {
						className: 'search-yandex-popup fadeInBig'
					}
				});

				this.input = BX.create('INPUT', {
					props: {									
						type: 'text',
						name: 'q',
						autocomplete: 'off',
						placeholder: BX.message('SEARCH_YANDEX_PLACEHOLDER')
					},
					events: {
						bxchange: BX.proxy(this.onChange, this)
					}
				});
				
				this.wait = BX.create('DIV', {
					props: {
						className: 'search-yandex-wait'
					},
					html: '<div><span></span></div>'
				});

				this.inputContainer = BX.create('DIV', {
					props: {
						className: 'search-yandex-input'
					},
					children: [					
						BX.create('DIV', {
							props: {
								className: 'search-yandex-icon'
							},
							children: [
								BX.create('I', {
									props: {
										className: 'icon-search'
									}
								})
							]
						}),
						this.wait,
						this.input,
						BX.create('DIV', {
							props: {
								className: 'search-yandex-close'
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
				});
								
				if(!!this.showCheckAvailable) {
					this.checkAvailableInput = BX.create('INPUT', {
						props: {
							type: 'checkbox',
							checked: this.available == 'Y' ? true : false
						}
					});

					this.checkAvailable = BX.create('DIV', {
						props: {
							className: 'search-yandex-check-available'
						},
						children: [
							BX.create('LABEL', {
								children: [
									this.checkAvailableInput,
									BX.create('SPAN', {
										props: {
											className: 'search-yandex-check-available-check'
										},
										children: [
											BX.create('I', {
												props: {
													className: 'icon-ok-b'
												}
											})
										]
									}),
									BX.create('SPAN', {
										props: {
											className: 'search-yandex-check-available-title'
										},
										html: BX.message('SEARCH_YANDEX_AVAILABLE')
									})
								],
								events: {
									change: BX.proxy(this.onChangeCheckAvailable, this)
								}
							})
						]
					});
				}
				
				this.result = BX.create('DIV', {
					props: {
						className: 'search-yandex-result'
					}
				});

				this.popup.appendChild(this.inputContainer);
				if(!!this.showCheckAvailable)
					this.popup.appendChild(this.checkAvailable);
				this.popup.appendChild(this.result);

				this.startText = this.oldValue = this.input.value;
				
				if(BX.yandexSearchInputValue != null && !!this.cache[this.input.name + '|' + BX.yandexSearchInputValue + '|available|' + this.available]) {
					this.input.value = BX.yandexSearchInputValue;
					this.showResult(this.cache[this.input.name + '|' + this.input.value + '|available|' + this.available]);
				}
				
				if(('webkitSpeechRecognition' in window) && window.location.protocol == 'https:') {
					var recognition = new webkitSpeechRecognition(),
						recognizing = false;
					
					recognition.lang = 'ru-Ru';
					recognition.continuous = false;
					recognition.interimResults = false;
					
					var microphone = BX.create('DIV', {
						props: {
							className: 'search-yandex-microphone'
						},
						children: [
							BX.create('I', {
								props: {
									className: 'icon-microphone'
								}
							})
						]
					});
					this.inputContainer.appendChild(microphone);
					
					if(!BX.getCookie(BX.message('COOKIE_NAME') + '_ENEXT_SITE_VISITED')) {
						BX.addClass(microphone, 'shadow-pulse');
						BX.setCookie(BX.message('COOKIE_NAME') + '_ENEXT_SITE_VISITED', true, {expires: 32832000, path: '/', secure: false});
					}
						
					BX.bind(microphone, 'click', function() {
						if(recognizing) {
							recognition.stop();
							BX.removeClass(microphone, 'active shadow-pulse-infinite');
							return;
						}										
						recognition.start();
					});
					
					recognition.onstart = function() {
						recognizing = true;				
						BX.addClass(microphone, 'active shadow-pulse-infinite');
					};
					
					recognition.onerror = function(event) {
						console.log(event.error);
					};

					recognition.onend = function() {
						recognizing = false;
						BX.removeClass(microphone, 'active shadow-pulse-infinite');
					};

					recognition.onresult = BX.delegate(function(event) {
						var result = event.results[event.resultIndex];
						if(!!result) {
							this.input.value = result[0].transcript;					
							if(result.isFinal)
								this.onChange();
						}
					}, this);
				}
				
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

					var sectionPanel = document.body.querySelector('.catalog-section-panel');
					if(!!sectionPanel && BX.hasClass(sectionPanel, 'fixed'))
						BX.style(sectionPanel, 'padding-right', scrollWidth + 'px');
					
					var tabsPanel = document.body.querySelector('[data-entity="tabs"]');
					if(!!tabsPanel && BX.hasClass(tabsPanel, 'fixed'))
						BX.style(tabsPanel, 'padding-right', scrollWidth + 'px');

					var objectsMap = document.body.querySelector('.objects-map');
					if(!!objectsMap)
						BX.style(objectsMap, 'padding-right', scrollWidth + 'px');

					var catalogCompareList = document.body.querySelector('.catalog-compare-list');
					if(!!catalogCompareList && BX.hasClass(catalogCompareList, 'active'))
						BX.style(catalogCompareList, 'margin-left', '-' + scrollWidth/2 + 'px');
				}
				
				var scrollTop = BX.GetWindowScrollPos().scrollTop;
				if(!!scrollTop && scrollTop > 0)
					BX.style(document.body, 'top', '-' + scrollTop + 'px');

				BX.addClass(document.body, 'search-yandex-active');
				
				document.body.appendChild(this.popup);

				document.body.appendChild(
					BX.create('DIV', {
						props: {
							className: 'modal-backdrop search-yandex__backdrop fadeInBig'
						}
					})
				);
					
				this.input.focus();
			}
		},

		adjustSectionBtn: function() {
			var slidePanelContent = BX.findParent(this.result, {className: 'slide-panel__content'});
			if(!!slidePanelContent) {
				if(
					BX.pos(this.sections[Object.keys(this.sections).length - 1], true).bottom > this.sectionsContainerHeight
					&& !this.sectionBtnAdjusted
				) {
					this.sectionBtnAdjusted = true;

					if(!BX.hasClass(this.sectionsContainer, 'active')) {
						this.sectionBtnSpan.innerHTML = BX.message('SEARCH_YANDEX_SECTIONS_ALL');
						this.sectionBtnI.className = 'icon-arrow-down';
					}

					this.sectionsContainer.appendChild(this.sectionBtn);
				} else if(
					BX.pos(this.sections[Object.keys(this.sections).length - 1], true).bottom <= this.sectionsContainerHeight
					&& !!this.sectionBtnAdjusted
				) {
					this.sectionBtnAdjusted = false;

					this.sectionsContainer.removeChild(this.sectionBtn);
				}
			}
		},

		showHideSections: function() {
			if(!BX.hasClass(this.sectionsContainer, 'active')) {
				BX.addClass(this.sectionsContainer, 'active');
				this.sectionBtnSpan.innerHTML = BX.message('SEARCH_YANDEX_SECTIONS_HIDE');
				this.sectionBtnI.className = 'icon-arrow-up';
			} else {
				BX.removeClass(this.sectionsContainer, 'active');
				this.sectionBtnSpan.innerHTML = BX.message('SEARCH_YANDEX_SECTIONS_ALL');
				this.sectionBtnI.className = 'icon-arrow-down';
			}
		}
	};
})();