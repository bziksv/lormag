function JCTitleSearch(arParams)
{
	var _this = this;

	this.arParams = {
		'AJAX_PAGE': arParams.AJAX_PAGE,
		'CONTAINER_ID': arParams.CONTAINER_ID,
		'INPUT_ID': arParams.INPUT_ID,
		'MIN_QUERY_LEN': parseInt(arParams.MIN_QUERY_LEN)
	};
	if(arParams.WAIT_IMAGE)
		this.arParams.WAIT_IMAGE = arParams.WAIT_IMAGE;
	if(arParams.MIN_QUERY_LEN <= 0)
		arParams.MIN_QUERY_LEN = 1;

	this.cache = [];
	this.cache_key = null;

	this.startText = '';
	this.running = false;
	this.currentRow = -1;
	this.RESULT = null;
	this.CONTAINER = null;
	this.INPUT = null;
	this.WAIT = null;

	this.ShowResult = function(result) {
		if(BX.type.isString(result)) {
			_this.RESULT.innerHTML = result;
		}

		_this.RESULT.style.display = _this.RESULT.innerHTML !== '' ? 'block' : 'none';

		_this.adjustResultNode();
	};
	
	this.onKeyPress = function(keyCode) {
		switch(keyCode) {
			case 27: //escape key - close search div
				BX.removeClass(_this.INPUT, 'active');
				_this.INPUT.blur();
				_this.RESULT.style.display = 'none';				
				return true;
		}
		return false;
	};
	
	this.onTimeout = function() {
		_this.onChange(function() {
			setTimeout(_this.onTimeout, 500);
		});
	};

	this.onChange = function(callback) {
		if(_this.running)
			return;
		_this.running = true;

		if(_this.INPUT.value != _this.oldValue && _this.INPUT.value != _this.startText) {
			_this.oldValue = _this.INPUT.value;
			if(_this.INPUT.value.length >= _this.arParams.MIN_QUERY_LEN) {
				_this.cache_key = _this.arParams.INPUT_ID + '|' + _this.INPUT.value;
				if(_this.cache[_this.cache_key] == null) {
					if(_this.WAIT) {										
						_this.WAIT.style.left = 10 + 'px';
						_this.WAIT.style.top = 10 + 'px';
						_this.WAIT.style.display = 'block';
					}

					BX.ajax.post(
						_this.arParams.AJAX_PAGE,
						{
							'ajax_call':'y',
							'INPUT_ID':_this.arParams.INPUT_ID,
							'q':_this.INPUT.value,
							'l':_this.arParams.MIN_QUERY_LEN
						},
						function(result) {
							_this.cache[_this.cache_key] = result;
							_this.ShowResult(result);							
							if(_this.WAIT)
								_this.WAIT.style.display = 'none';
							if(!!callback)
								callback();
							_this.running = false;
						}
					);
					return;
				} else {
					_this.ShowResult(_this.cache[_this.cache_key]);
				}
			} else {
				_this.RESULT.style.display = 'none';
			}
		}
		if(!!callback)
			callback();
		_this.running = false;
	};
	
	this.onFocusLost = function(e) {
		if(BX.hasClass(_this.INPUT, 'active') && (
			!BX.findParent(e.target, {className: 'top-panel__search'}) &&
			!BX.findParent(e.target, {className: 'slide-panel'})
		)) {
			setTimeout(function() {
				BX.removeClass(_this.INPUT, 'active');
				_this.RESULT.style.display = 'none';
			}, 250);
		}
	};

	this.onFocusGain = function() {
		BX.addClass(_this.INPUT, 'active');
		if(_this.RESULT.innerHTML.length)
			_this.ShowResult();
	};

	this.onKeyDown = function(e) {
		if(!e)
			e = window.event;

		if(BX.hasClass(_this.INPUT, 'active') && _this.RESULT.style.display == 'block') {
			if(_this.onKeyPress(e.keyCode))
				return BX.PreventDefault(e);
		}
	};

	this.adjustResultNode = function() {
		var pos = BX.pos(_this.CONTAINER);
		pos.width = pos.right - pos.left;
		_this.RESULT.style.position = 'absolute';		
		_this.RESULT.style.top = '100%';
		_this.RESULT.style.left = '-18px';
		_this.RESULT.style.width = pos.width + 36 + 'px';
	};

	this._onContainerLayoutChange = function() {
		if(_this.RESULT.style.display !== "none" && _this.RESULT.innerHTML !== '') {
			_this.adjustResultNode();
		}
	};
	
	this.Init = function() {
		this.CONTAINER = document.getElementById(this.arParams.CONTAINER_ID);
		BX.addCustomEvent(this.CONTAINER, "OnNodeLayoutChange", this._onContainerLayoutChange);
		
		this.RESULT = document.body.querySelector('.top-panel__search').appendChild(document.createElement("DIV"));
		this.RESULT.className = 'title-search-result';
		
		this.INPUT = document.getElementById(this.arParams.INPUT_ID);
		this.startText = this.oldValue = this.INPUT.value;
		BX.bind(this.INPUT, 'focus', function() {_this.onFocusGain()});
		
		BX.bind(document, 'mousedown', function(e) {
			if(e.button == 0)
				_this.onFocusLost(e);
		});

		BX.bind(document, 'touchend', function(e) {
			_this.onFocusLost(e);
		});

		BX.bind(document, 'keydown', function(e) {
			_this.onKeyDown(e);
		});

		if(this.arParams.WAIT_IMAGE) {
			this.WAIT = document.body.querySelector('.top-panel__search').appendChild(document.createElement("DIV"));
			this.WAIT.className = 'title-search-wait';
			this.WAIT.innerHTML = this.arParams.WAIT_IMAGE;
			this.WAIT.style.backgroundColor = "#fff";
			this.WAIT.style.display = 'none';
			this.WAIT.style.position = 'absolute';
		}

		BX.bind(this.INPUT, 'bxchange', function() {
			_this.onChange()
		});
			
		if(('webkitSpeechRecognition' in window) && window.location.protocol == 'https:') {
			var recognition = new webkitSpeechRecognition(),
				recognizing = false;
			
			recognition.lang = 'ru-Ru';
			recognition.continuous = false;
			recognition.interimResults = false;

			var searchForm = _this.CONTAINER.querySelector('form');
			if(!!searchForm) {
				var microphone = BX.create('SPAN', {
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
				searchForm.appendChild(microphone);

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
			}			

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

			recognition.onresult = function(event) {
				var result = event.results[event.resultIndex];
				if(!!result) {
					_this.INPUT.value = result[0].transcript;					
					if(result.isFinal)
						_this.onChange();
				}
			};
		}
	};

	BX.ready(function () {
		_this.Init(arParams)
	});
}
