(function() {
	'use strict';

	if(!!window.JCQuickOrderComponent)
		return;

	window.JCQuickOrderComponent = function(params) {
		this.componentPath = params.componentPath || '';
		this.props = params.jsProps || '';
		this.defaultCountry = params.defaultCountry || '';
		this.phoneMask = params.phoneMask || '';
		this.userConsent = params.userConsent || '';
		this.useCaptcha = params.useCaptcha || '';
		this.quantityId = params.quantityId || '';
		this.basketPropsId = params.basketPropsId || '';
		this.basketSkuProps = params.basketSkuProps || '';
		this.container = BX(params.container);
		
		BX.ready(BX.delegate(this.init, this));
	};

	window.JCQuickOrderComponent.prototype =	{		
		init: function() {
			this.form = this.container.querySelector('[data-entity="quickOrderForm"]');
			this.alert = this.container.querySelector('[data-entity="quickOrderAlert"]');
			
			if(!!this.userConsent) {
				var inputUserConsent = this.form.querySelector('[name="USER_CONSENT"]'),
					inputUserConsentUrl = this.form.querySelector('[name="USER_CONSENT_URL"]');
				
				if(!!inputUserConsentUrl)
					inputUserConsentUrl.value = window.location.href;

				BX.UserConsent.load(this.form);
			}

			if(!!this.useCaptcha) {
				var captchaImg = this.form.querySelector('[alt="CAPTCHA"]'),
					captchaWord = this.form.querySelector('[name="CAPTCHA_WORD"]'),
					captchaSid = this.form.querySelector('[name="CAPTCHA_SID"]');
			}
			
			if(!!this.props || !!this.useCaptcha) {
				var showFormHiddenInputs = false;
				BX.ajax({
					url: this.componentPath + '/ajax.php',
					method: 'POST',
					dataType: 'json',
					timeout: 60,
					data: {							
						action: 'getData',
						getCaptcha: !!this.useCaptcha ? 1 : 0,
						props: !!this.props ? this.props : 0,
						phoneMask: !!this.phoneMask ? 1 : 0,
						siteServerName: BX.message('SITE_SERVER_NAME'),
						languageId: BX.message('LANGUAGE_ID')
					},
					onsuccess: BX.delegate(function(result) {
						if(!!this.props) {
							for(var i in this.props) {
								if(this.props.hasOwnProperty(i)) {
									var formInput = this.form.querySelector('[name="' + this.props[i].CODE + '"]');
									if(!!formInput) {
										if(formInput.name == 'PHONE' && !!this.phoneMask) {
											this.inputPhone = formInput;
											this.iti = window.intlTelInput(this.inputPhone, {
												autoHideDialCode: false,
												autoPlaceholder : 'aggressive',							
												customPlaceholder: function(selectedCountryPlaceholder, selectedCountryData) {
													return selectedCountryPlaceholder.replace(/[0-9]/g, '_');
												},
												initialCountry: !!result.COUNTRY ? result.COUNTRY : this.defaultCountry,
												nationalMode: false,
												preferredCountries: ['ru', 'by', 'ua', 'kz'],
												separateDialCode: true,
												utilsScript: '/bitrix/js/altop.enext/intlTelInput/utils.js'
											});
											this.iti.promise.then(BX.delegate(function() {
												Inputmask(this.inputPhone.placeholder.replace(/_/g, '9')).mask(this.inputPhone);
											}, this));
											this.inputPhone.addEventListener('countrychange', BX.delegate(function(e) {
												var target = e.currentTarget;
												$(this.form).formValidation('resetField', target.name, true);
												Inputmask(target.placeholder.replace(/_/g, '9')).mask(target);
												target.blur();
												target.focus();
											}, this));
										}

										if(!!result[this.props[i].CODE] && result[this.props[i].CODE].length > 0) {
											formInput.value = result[this.props[i].CODE];
											if(!showFormHiddenInputs)
												showFormHiddenInputs = true;
										}
									}
								}
							}
						}
						
						if(!!showFormHiddenInputs) {
							var formHiddenInputs = this.form.querySelectorAll('.form-group-hidden');
							if(!!formHiddenInputs) {
								for(var i in formHiddenInputs) {
									if(formHiddenInputs.hasOwnProperty(i) && BX.type.isDomNode(formHiddenInputs[i])) {
										BX.removeClass(formHiddenInputs[i], 'form-group-hidden');
									}
								}
							}
						}
						
						if(!!this.useCaptcha && !!result.captcha) {
							if(!!captchaImg) {
								if(captchaImg.hasAttribute('data-lazyload-src'))
									BX.adjust(captchaImg, {attrs: {'data-lazyload-src': '/bitrix/tools/captcha.php?captcha_sid=' + result.captcha}});
								else
									BX.adjust(captchaImg, {props: {src: '/bitrix/tools/captcha.php?captcha_sid=' + result.captcha}});
								captchaImg.parentNode.style.display = '';
							}
							if(!!captchaWord)
								captchaWord.value = '';
							if(!!captchaSid)
								captchaSid.value = result.captcha;
						}
					}, this)
				});
			}
			
			var fields = {};
			
			if(!!this.props) {
				for(var i in this.props) {
					if(this.props.hasOwnProperty(i)) {
						var formInput = this.form.querySelector('[name="' + this.props[i].CODE + '"]');
						if(!!formInput) {
							fields[this.props[i].CODE] = {
								row: '.form-group',
								validators: {}
							};

							if(this.props[i].REQUIRED == 'Y') {
								fields[this.props[i].CODE].validators.notEmpty = {
									message: BX.message('QUICK_ORDER_NOT_EMPTY_INVALID')
								};
							}
							
							if(this.props[i].CODE == 'PHONE') {
								if(!!this.phoneMask) {
									fields[this.props[i].CODE].validators.callback = {
										message: BX.message('QUICK_ORDER_PHONE_WRONG'),
										callback: BX.delegate(function(value, validator, $field) {
											if(!this.iti.isValidNumber()) {
												return false;
											} else {
												return true;
											}
										}, this)
									};
								} else {
									fields[this.props[i].CODE].validators.regexp = {
										regexp: /^[0-9\s\-()+]+$/,
										message: BX.message('QUICK_ORDER_PHONE_INVALID')
									};
								}
							} else if(this.props[i].CODE == 'EMAIL') {
								fields[this.props[i].CODE].validators.emailAddress = {
									message: BX.message('QUICK_ORDER_EMAIL_ADDRESS_INVALID')
								};
							}
						}
					}
				}
			}
			
			if(!!this.userConsent && !!inputUserConsent) {
				fields.USER_CONSENT = {
					row: '.form-group',
					validators: {
						notEmpty: {
							message: BX.message('QUICK_ORDER_USER_CONSENT_NOT_EMPTY_INVALID')
						}
					}
				};
			}

			if(!!this.useCaptcha && !!captchaWord) {
				fields.CAPTCHA_WORD = {
					row: '.form-group',
					validators: {
						notEmpty: {
							message: BX.message('QUICK_ORDER_NOT_EMPTY_INVALID')
						},
						remote: {
							type: 'POST',
							url: this.componentPath + '/ajax.php',
							message: BX.message('QUICK_ORDER_CAPTCHA_WRONG'),
							data: function() {
								return {
									action: 'checkCaptcha',
									CAPTCHA_SID: !!captchaSid ? captchaSid.value : ''
								};
							},
							delay: 1000
						}
					}
				};
			}
			
			$(this.form).formValidation({
				framework: 'bootstrap',
				icon: {
					valid: 'icon-ok-b',
					invalid: 'icon-close-b',
					validating: 'icon-repeat-b'
				},
				fields: fields				
			});
				
			if(!!this.userConsent && !!inputUserConsent) {
				BX.addCustomEvent(this.form, 'OnFormInputUserConsentChange', BX.delegate(function() {
					$(this.form).formValidation('revalidateField', inputUserConsent.name);
				}, this));
			}
			
			$(this.form).on('success.field.fv', BX.delegate(function(e) {
				e.preventDefault();
				
				var input = e.target;

				if(input.name == 'PHONE') {
					var formHiddenInputs = this.form.querySelectorAll('.form-group-hidden');
					if(!!formHiddenInputs) {
						for(var i in formHiddenInputs) {
							if(formHiddenInputs.hasOwnProperty(i) && BX.type.isDomNode(formHiddenInputs[i])) {
								BX.removeClass(formHiddenInputs[i], 'form-group-hidden');
							}
						}
					}
				}
			}, this));
			
			$(this.form).on('success.form.fv', BX.delegate(function() {
				var data = {
					action: 'createOrder',
					phoneMask: !!this.phoneMask ? 1 : 0,
					siteId: BX.message('SITE_ID'),
					siteCharset: BX.message('SITE_CHARSET'),
					siteServerName: BX.message('SITE_SERVER_NAME'),
					languageId: BX.message('LANGUAGE_ID')
				};
				
				var propCollection = this.form.querySelectorAll('input, textarea');
				if(!!propCollection) {
					for(var i in propCollection) {
						if(propCollection.hasOwnProperty(i) && BX.type.isDomNode(propCollection[i])) {
							if(propCollection[i].name == 'PHONE' && !!this.phoneMask) {
								data[propCollection[i].name] = {};
								data[propCollection[i].name]['COUNTRY'] = this.iti.getSelectedCountryData();
								data[propCollection[i].name]['VALUE'] = propCollection[i].value;
								data[propCollection[i].name]['FULL_VALUE'] = this.iti.getNumber(intlTelInputUtils.numberFormat.INTERNATIONAL);
							} else {
								data[propCollection[i].name] = propCollection[i].value;
							}
						}
					}
				}

				var obQuantity = BX(this.quantityId);
				if(!!obQuantity)
					data['quantity'] = obQuantity.value;

				var obBasketProps = BX(this.basketPropsId);
				if(!!obBasketProps) {
					var propCollection = obBasketProps.getElementsByTagName('input');
					if(propCollection && propCollection.length) {
						for(i = 0; i < propCollection.length; i++) {
							if(!propCollection[i].disabled) {
								switch(propCollection[i].type.toLowerCase()) {
									case 'hidden':
										data[propCollection[i].name] = propCollection[i].value;
										break;
									case 'radio':
										if(propCollection[i].checked)
											data[propCollection[i].name] = propCollection[i].value;
										break;
									default:
										break;
								}
							}
						}
					}
				}

				if(!!this.basketSkuProps)
					data['basket_props'] = this.basketSkuProps;
				
				BX.ajax({
					url: this.componentPath + '/ajax.php',
					method: 'POST',
					dataType: 'json',
					timeout: 60,
					data: data,
					onsuccess: BX.delegate(function(result) {
						$(this.form).formValidation('resetForm', false);

						window.dataLayer = window.dataLayer || [];
						
						if(result.RESULTS.length == 1) {
							for(var i in result.RESULTS) {
								if(result.RESULTS.hasOwnProperty(i)) {
									if(!!result.RESULTS[i].status) {
										BX.remove(this.form);

										if(!!this.alert && !!result.RESULTS[i].text) {
											BX.append(BX.create('SPAN', {
												props: {
													className: 'alert alert-success'
												},
												html: result.RESULTS[i].text
											}), this.alert);
											BX.style(this.alert, 'display', '');
										}

										var info = {
											'event': 'purchase',
											'ecommerce': {
												'purchase': {
													'actionField': {
														'id': result.RESULTS[i].order.id,
														'revenue': result.RESULTS[i].order.total_price,
														'tax': result.RESULTS[i].order.tax_price,
														'shipping': result.RESULTS[i].order.delivery_price
													},
													'products': result.RESULTS[i].products
												}
											}
										};

										window.dataLayer.push(info);

										BX.onCustomEvent('OnBasketChange');

										var sPanelCart = document.body.querySelector('.slide-panel-cart');
										if(!!sPanelCart) {
											var sPanelCartBack = document.body.querySelector('.slide-panel-cart__backdrop');

											new BX.easing({
												duration: 300,
												start: {
													right: 0,
													opacity: 100
												},
												finish: {
													right: -400,
													opacity: 0
												},
												transition: BX.easing.transitions.linear,
												step: function(state) {
													sPanelCart.style.right = state.right + 'px';
													sPanelCartBack.style.opacity = state.opacity / 100;
												},
												complete: function() {
													BX.remove(sPanelCart);
													BX.remove(sPanelCartBack);
												}
											}).animate();
											
											BX.removeClass(document.body, 'slide-panel-cart-active');

											BX.removeCustomEvent(BX.Sale.BasketComponent, 'quickOrderRequest', BX.proxy(BX.Sale.BasketComponent.quickOrderRequest, BX.Sale.BasketComponent));
										}
									} else {
										if(!!this.alert && !!result.RESULTS[i].text) {
											BX.append(BX.create('SPAN', {
												props: {
													className: 'alert alert-error'
												},
												html: result.RESULTS[i].text
											}), this.alert);
											BX.style(this.alert, 'display', '');
										}
									}

									if(!!this.useCaptcha && !!result.RESULTS[i].captcha_code) {
										if(!!captchaImg)
											BX.adjust(captchaImg, {attrs: {src: '/bitrix/tools/captcha.php?captcha_sid=' + result.RESULTS[i].captcha_code}});
										if(!!captchaWord)
											captchaWord.value = '';
										if(!!captchaSid)
											captchaSid.value = result.RESULTS[i].captcha_code;
									}
								}
							}
						} else if(result.RESULTS.length > 1) {
							BX.remove(this.form);

							for(var i in result.RESULTS) {
								if(result.RESULTS.hasOwnProperty(i)) {
									if(!!this.alert && !!result.RESULTS[i].text) {
										BX.append(BX.create('SPAN', {
											props: {
												className: 'alert alert-' + (!!result.RESULTS[i].status ? 'success' : 'error')
											},
											html: result.RESULTS[i].text
										}), this.alert);
										BX.style(this.alert, 'display', '');
									}

									if (!!result.RESULTS[i].status) {
										var info = {
											'event': 'purchase',
											'ecommerce': {
												'purchase': {
													'actionField': {
														'id': result.RESULTS[i].order.id,
														'revenue': result.RESULTS[i].order.total_price,
														'tax': result.RESULTS[i].order.tax_price,
														'shipping': result.RESULTS[i].order.delivery_price
													},
													'products': result.RESULTS[i].products
												}
											}
										};

										window.dataLayer.push(info);
									}
								}
							}

							BX.onCustomEvent('OnBasketChange');

							var sPanelCart = document.body.querySelector('.slide-panel-cart');
							if(!!sPanelCart) {
								var sPanelCartBack = document.body.querySelector('.slide-panel-cart__backdrop');

								new BX.easing({
									duration: 300,
									start: {
										right: 0,
										opacity: 100
									},
									finish: {
										right: -400,
										opacity: 0
									},
									transition: BX.easing.transitions.linear,
									step: function(state) {
										sPanelCart.style.right = state.right + 'px';
										sPanelCartBack.style.opacity = state.opacity / 100;
									},
									complete: function() {
										BX.remove(sPanelCart);
										BX.remove(sPanelCartBack);
									}
								}).animate();
								
								BX.removeClass(document.body, 'slide-panel-cart-active');

								BX.removeCustomEvent(BX.Sale.BasketComponent, 'quickOrderRequest', BX.proxy(BX.Sale.BasketComponent.quickOrderRequest, BX.Sale.BasketComponent));
							}
						}
					}, this)
				});
			}, this));
		}
	}
})();