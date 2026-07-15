(function() {
	'use strict';

	if(!!window.JCGeoLocationSlidePanelComponent)
		return;

	window.JCGeoLocationSlidePanelComponent = function(params) {
		this.componentPath = params.componentPath || '';
		
		this.container = BX(params.container);
		
		BX.ready(BX.delegate(this.init, this));
	};

	window.JCGeoLocationSlidePanelComponent.prototype = {
		init: function() {
			var obSelectLocation = this.container.querySelector('[data-entity="selectLocation"]');
			if(!!obSelectLocation)
				BX.bind(obSelectLocation, 'click', BX.delegate(this.changeGeoLocation, this));
		},
			
		changeGeoLocation: function(e) {
			var target = BX.proxy_context;

			target.innerHTML = '<span class="btn-loader"><span><span></span></span></span>';

			BX.ajax({
				url: this.componentPath + '/ajax.php',
				method: 'POST',
				dataType: 'json',
				timeout: 60,
				data: {
					action: 'changeGeoLocation',
					locationId: this.container.querySelector('[name="LOCATION"]').value,
					siteServerName: BX.message('SITE_SERVER_NAME')
				},
				onsuccess: BX.delegate(function(result) {
					if(!!result.status)
						window.location.reload(true);
				}, this)
			});
			
			e.stopPropagation();
		}
	}
})();