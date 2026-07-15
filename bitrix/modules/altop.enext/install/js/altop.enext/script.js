let imgLazyLoad = function() {
	var lazyImages = [].slice.call(document.querySelectorAll('img[data-lazyload-src]'));
	
	if('IntersectionObserver' in window) {
		let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
			entries.forEach(function(entry) {
				if(entry.isIntersecting) {
					let lazyImage = entry.target;
					lazyImage.src = lazyImage.dataset.lazyloadSrc;
					lazyImage.classList.add('bx-lazyload-success');
					lazyImageObserver.unobserve(lazyImage);
				}
			});
		});

		lazyImages.forEach(function(lazyImage) {
			lazyImageObserver.observe(lazyImage);
		});
	} else {
		lazyImages.forEach(function(lazyImage) {
			let newImage = new Image();
			newImage.src = lazyImage.dataset.lazyloadSrc;
			newImage.onload = function() {
				if(lazyImage.dataset.lazyloadSrc)
					lazyImage.src = lazyImage.dataset.lazyloadSrc;
				lazyImage.classList.add('bx-lazyload-success');
			}
		});
	}
}

let seq = function(arr, callback, index) {
	//first call, without an index
	if(typeof index === 'undefined') {
		index = 0;
	}

	arr[index](function() {
		index++;
		if(index === arr.length) {
			callback();
		} else {
			seq(arr, callback, index);
		}
	});
}

let scriptsDone = function() {
	var DOMContentLoadedEvent = document.createEvent('Event');
	DOMContentLoadedEvent.initEvent('DOMContentLoaded', true, true);
	document.dispatchEvent(DOMContentLoadedEvent);
}

let insertScript = function(script, callback) {
	var s = document.createElement('script');
	
	s.type = 'text/javascript';

	var attrs = script.attributes;
	if(!!attrs) {
		for(var i in attrs) {
			if(attrs[i].nodeName)
				s.setAttribute(attrs[i].nodeName, attrs[i].nodeValue);
		}
	}

	if(script.hasAttribute('src')) {
		s.onload = callback;
		s.onerror = callback;		
	} else {
		s.textContent = script.innerText;
	}
	
	document.body.appendChild(s);
	
	//run the callback immediately for inline scripts
	if(!script.hasAttribute('src')) {
		callback();
	}
}

let runScripts = function(container) {
	var scripts = container.querySelectorAll('script'),
		runList = [];
	
	[].forEach.call(scripts, function(script) {
		runList.push(function(callback) {
			insertScript(script, callback)
		});
	});
			
	//insert the script tags sequentially
	//to preserve execution order
	seq(runList, scriptsDone);
}

let jsLazyLoad = function() {
	var jsLazyload = document.body.getAttribute('data-js-lazyload'),
		siteId = document.body.getAttribute('data-site-id');
	if(!!jsLazyload) {
		$.ajax({
			url: '/bitrix/tools/altop.enext/ajax.php',
			type: 'POST',
			dataType: 'json',
			data: {
				'action': 'getJs',
				'url': window.location.pathname + window.location.search,
				'siteId': !!siteId ? siteId : ''
			},
			success: function(result) {
				if(!result || !result.JS)
					return;
				
				var temporaryNode = document.createElement("div");
				temporaryNode.innerHTML = result.JS;

				runScripts(temporaryNode);
			}
		});
	}
}

let convertImgToWebp = function(srcList) {
	$.ajax({
		url: '/bitrix/tools/altop.enext/ajax.php',
		type: 'POST',
		dataType: 'json',
		data: {
			'action': 'getWebp',
			'images': srcList
		}
	});
}

document.addEventListener('DOMContentLoaded', function() {
	imgLazyLoad();

	var imgWebp = document.body.getAttribute('data-img-webp');
	if(imgWebp && imgWebp == 'true') {
		var srcList = {},
			images = document.querySelectorAll('img');
		
		if(!!images) {
			for(var i in images) {
				if(images.hasOwnProperty(i)) {
					var imageDataLazyloadSrc = images[i].getAttribute('data-lazyload-src'),
						imageSrc = images[i].getAttribute('src');

					if(!!imageDataLazyloadSrc && imageDataLazyloadSrc.substr(0, 4) !== 'http' 
						&& imageDataLazyloadSrc.substr(0, 2) !== '//' 
						&& imageDataLazyloadSrc.substr(0, 11) !== 'data:image/' 
						&& imageDataLazyloadSrc.indexOf('.webp') == -1
					) {
						srcList[imageDataLazyloadSrc] = imageDataLazyloadSrc;
					} else if(!!imageSrc && imageSrc.substr(0, 4) !== 'http'
						&& imageSrc.substr(0, 2) !== '//' 
						&& imageSrc.substr(0, 11) !== 'data:image/' 
						&& imageSrc.indexOf('.webp') == -1
					) {
						srcList[imageSrc] = imageSrc;
					}
				}
			}
		}

		if(Object.keys(srcList).length > 0)
			convertImgToWebp(srcList);
	}
});

setTimeout(function() {
	jsLazyLoad();
}, 1500);