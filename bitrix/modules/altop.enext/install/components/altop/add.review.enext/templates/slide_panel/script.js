(function() {
	'use strict';

	if(!!window.JCAddReviewComponent)
		return;

	window.JCAddReviewComponent = function(params) {
		this.componentPath = params.componentPath || '';
		this.props = params.jsProps || '';
		this.propsReq = params.jsPropsReq || '';
		this.userConsent = params.userConsent || '';
		this.useCaptcha = params.useCaptcha || '';
		this.premoderation = params.premoderation || '';
		this.container = BX(params.container);
		
		BX.ready(BX.delegate(this.initSlidePanelForm, this));
	};

	window.JCAddReviewComponent.prototype =	{		
		initSlidePanelForm: function() {
			this.form = this.container.querySelector('form');
			this.alert = this.container.querySelector('.alert');

			this.ratingStars = this.form.querySelector('.form-group-rating-stars');			
			if(!!this.ratingStars)
				this.initRatingStars();

			this.listItems = this.form.querySelectorAll('.form-group-list-items');			
			if(!!this.listItems) {
				for(var i in this.listItems) {
					if(this.listItems.hasOwnProperty(i) && BX.type.isDomNode(this.listItems[i]))
						this.initListItems(this.listItems[i]);
				}
			}
			
			var inputFiles = this.form.querySelectorAll('[type="file"]');
			if(!!inputFiles) {
				for(var i in inputFiles) {
					if(inputFiles.hasOwnProperty(i) && BX.type.isDomNode(inputFiles[i])) {						
						var limit = inputFiles[i].getAttribute('data-jfiler-limit');
						$(inputFiles[i]).filer({
							changeInput: '<button type="button" class="btn btn-primary"><i class="icon-plus"></i><span>' + (limit > 1 ? BX.message('ADD_REVIEW_SELECT_FILES') : BX.message('ADD_REVIEW_SELECT_FILE')) + '</span></button>',
							showThumbs: true,							
							templates: {
								box: '<div class="jFiler-items-list"></div>',								
								item: '<div class="jFiler-item">{{fi-image}}<div class="jFiler-item-trash-action"><i class="icon-close"></i></div></div>',
								itemAppend: null,
								progressBar: null,
								itemAppendToEnd: true,
								_selectors: {
									list: '.jFiler-items-list',
									item: '.jFiler-item',
									progressBar: null,
									remove: '.jFiler-item-trash-action'
								}
							},
							addMore: true,
							captions: {
								removeConfirmation: BX.message('ADD_REVIEW_REMOVE_FILE'),
								errors: {
									filesLimit: BX.message('ADD_REVIEW_LIMIT_FILES'),
									filesType: BX.message('ADD_REVIEW_TYPE_FILES')
								}
							}
						});
					}
				}
			}
			
			if(!!this.userConsent) {
				var userConsentUrl = this.form.querySelector('[name="USER_CONSENT_URL"]');
				if(!!userConsentUrl)
					userConsentUrl.value = window.location.href;
				
				BX.UserConsent.load(this.form);
			}

			if(!!this.useCaptcha) {
				var captchaImg = this.form.querySelector('[alt="CAPTCHA"]'),
					captchaWord = this.form.querySelector('[name="CAPTCHA_WORD"]'),
					captchaSid = this.form.querySelector('[name="CAPTCHA_SID"]');
			}
			
			if(this.props.length > 0 || !!this.useCaptcha) {
				BX.ajax({
					url: this.componentPath + '/ajax.php',
					method: 'POST',
					dataType: 'json',
					timeout: 60,
					data: {							
						action: 'getData',
						getCaptcha: !!this.useCaptcha ? true : false,
						props: this.props.length > 0 ? this.props : false
					},
					onsuccess: BX.delegate(function(result) {
						for(var i in this.props) {
							if(this.props.hasOwnProperty(i)) {
								var formInput = this.form.querySelector('[name="' + this.props[i] + '"]');
								if(!!formInput && !!result[this.props[i]] && result[this.props[i]].length > 0)
									formInput.value = result[this.props[i]];
							}
						}
						
						if(!!this.useCaptcha && !!result.captcha) {
							if(!!captchaImg) {
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

			for(var i in this.propsReq) {
				if(this.propsReq.hasOwnProperty(i)) {
					fields[this.propsReq[i].CODE] = {
						row: '.form-group',
						validators: {								
							notEmpty: {
								message: this.propsReq[i].PROPERTY_TYPE == 'F'
									? (this.propsReq[i].MULTIPLE == 'Y' ? BX.message('ADD_REVIEW_FILES_NOT_EMPTY_INVALID') : BX.message('ADD_REVIEW_FILE_NOT_EMPTY_INVALID'))
									: BX.message('ADD_REVIEW_NOT_EMPTY_INVALID')
							}
						}
					};
				}
			}
			
			if(!!this.userConsent) {
				fields.USER_CONSENT = {
					row: '.form-group',
					validators: {
						notEmpty: {
							message: BX.message('ADD_REVIEW_USER_CONSENT_NOT_EMPTY_INVALID')
						}
					}
				};
			}

			if(!!this.useCaptcha) {
				fields.CAPTCHA_WORD = {
					row: '.form-group',
					validators: {
						notEmpty: {
							message: BX.message('ADD_REVIEW_NOT_EMPTY_INVALID')
						},
						remote: {
							type: 'POST',
							url: this.componentPath + '/ajax.php',
							message: BX.message('ADD_REVIEW_CAPTCHA_WRONG'),
							data: function() {
								return {
									action: 'checkCaptcha',
									CAPTCHA_SID: captchaSid.value
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
				excluded:[":disabled", "[readonly]"],
				fields: fields				
			});
			
			if(!!this.userConsent) {
				BX.addCustomEvent(this.form, 'OnFormInputUserConsentChange', BX.delegate(function() {
					$(this.form).formValidation('revalidateField', 'USER_CONSENT');
				}, this));
			}
			
			$(this.form).on('success.form.fv', BX.delegate(function() {
				var data = new FormData(this.form);

				data.append('action', 'addReview');
				data.append('premoderation', !!this.premoderation ? 'Y' : 'N');
				data.append('siteId', BX.message('SITE_ID'));
				data.append('siteCharset', BX.message('SITE_CHARSET'));
				data.append('siteServerName', BX.message('SITE_SERVER_NAME'));
				data.append('languageId', BX.message('LANGUAGE_ID'));

				$.ajax({
					url: this.componentPath + '/ajax.php',
					type: 'POST',
					data: data,
					contentType: false,
					cache: false,
					processData: false,
					dataType: 'json',
					success: BX.delegate(function(result) {
						$(this.form).formValidation('resetForm', false);
						
						if(!!result.status) {
							BX.remove(this.form);

							if(!!this.alert) {
								BX.adjust(this.alert, {
									props: {
										className: 'alert alert-success'
									},
									style: {
										display: 'block'
									},
									html: !!this.premoderation ? BX.message('ADD_REVIEW_ALERT_PREMODERATION') : BX.message('ADD_REVIEW_ALERT_SUCCESS')
								});
							}
							
							setTimeout(function() {
								window.location.href = window.location.pathname + '#reviews';
								window.location.reload(true);
							}, 3000);
						} else {
							if(!!this.alert) {
								BX.adjust(this.alert, {
									props: {
										className: 'alert alert-error'
									},
									style: {
										display: 'block'
									},
									html: BX.message('ADD_REVIEW_ALERT_ERROR')
								});
							}
						}
						
						if(!!this.useCaptcha && !!result.captcha_code) {
							if(!!captchaImg)
								BX.adjust(captchaImg, {attrs: {src: '/bitrix/tools/captcha.php?captcha_sid=' + result.captcha_code}});
							if(!!captchaWord)
								captchaWord.value = '';
							if(!!captchaSid)
								captchaSid.value = result.captcha_code;
						}
					}, this)
				});
			}, this));
		},

		initRatingStars: function() {
			var ratingStar = this.ratingStars.querySelectorAll('.form-group-rating-star'),
				ratingVal = this.form.querySelector('.form-group-rating-val');
			
			if(!!ratingStar) {				
				BX.bind(this.ratingStars, 'click', BX.delegate(function(e) {
					var target = e.target;
					if(BX.hasClass(target, 'form-group-rating-star')) {
						for(var i in ratingStar) {
							if(ratingStar.hasOwnProperty(i) && BX.type.isDomNode(ratingStar[i]))
								BX.removeClass(ratingStar[i], 'form-group-rating-star-current');
						}
						
						BX.addClass(target, 'form-group-rating-star-current');

						var input = this.form.querySelector('[name="' + target.getAttribute('data-code') + '"]');
						if(!!input) {
							input.value = target.getAttribute('data-id');
							$(this.form).formValidation('revalidateField', target.getAttribute('data-code'));
						}
					}
				}, this));
				
				BX.bind(this.ratingStars, 'mouseover', BX.delegate(function(e) {
					var target = e.target;
					if(BX.hasClass(target, 'form-group-rating-star')) {
						if(!!ratingVal)
							ratingVal.innerHTML = target.getAttribute('data-value');

						for(var i in ratingStar) {
							if(ratingStar.hasOwnProperty(i) && BX.type.isDomNode(ratingStar[i]))
								BX.removeClass(ratingStar[i], 'form-group-rating-star-active');
						}
						
						BX.addClass(target, 'form-group-rating-star-active');
						
						for(var i in ratingStar) {
							if(ratingStar.hasOwnProperty(i) && BX.type.isDomNode(ratingStar[i])) {
								if(BX.hasClass(ratingStar[i], 'form-group-rating-star-active'))
									break;
								else
									BX.addClass(ratingStar[i], 'form-group-rating-star-active');
							}
						}
					}
				}, this));
			
				BX.bind(this.ratingStars, 'mouseout', BX.delegate(function() {
					if(!!ratingVal)
						ratingVal.innerHTML = BX.message('ADD_REVIEW_RATING');
					
					for(var i in ratingStar) {
						if(ratingStar.hasOwnProperty(i) && BX.type.isDomNode(ratingStar[i]))
							BX.addClass(ratingStar[i], 'form-group-rating-star-active');
					}
					
					for(var i = Object.keys(ratingStar).length; i >= 0; i--) {
						if(ratingStar.hasOwnProperty(i) && BX.type.isDomNode(ratingStar[i])) {
							if(BX.hasClass(ratingStar[i], 'form-group-rating-star-current')) {
								if(!!ratingVal)
									ratingVal.innerHTML = ratingStar[i].getAttribute('data-value');
								break;
							} else {
								BX.removeClass(ratingStar[i], 'form-group-rating-star-active');
							}
						}
					}
				}, this));
			}
		},

		initListItems: function(listItems) {
			var listItem = listItems.querySelectorAll('.form-group-list-item');
			if(!!listItem) {
				BX.bind(listItems, 'click', BX.delegate(function(e) {
					var target = e.target;
					if(BX.hasClass(target, 'form-group-list-item')) {
						for(var i in listItem) {
							if(listItem.hasOwnProperty(i) && BX.type.isDomNode(listItem[i]))
								BX.removeClass(listItem[i], 'form-group-list-item-active');
						}

						BX.addClass(target, 'form-group-list-item-active');

						var input = this.form.querySelector('[name="' + target.getAttribute('data-code') + '"]');
						if(!!input) {
							input.value = target.getAttribute('data-id');
							$(this.form).formValidation('revalidateField', target.getAttribute('data-code'));
						}
					}
				}, this));
			}
		}
	}
})();