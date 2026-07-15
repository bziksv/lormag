;(function(){
	'use strict';

	BX.namespace('BX.Sale.BasketActionPool');

	BX.Sale.BasketActionPool = function(component) {
		this.component = component;

		this.processing = false;
		this.poolQuantity = {};
		this.updateTimer = null;
		this.currentQuantity = {};
		this.lastStableQuantities = {};

		this.updateQuantity();
	};

	BX.Sale.BasketActionPool.prototype.updateQuantity = function() {	
		var items = this.component.container.querySelectorAll('[data-entity="row"]');
		if(!!items && items.length > 0) {
			for(var i = 0; items.length > i; i++) {
				var itemId = items[i].getAttribute('data-id');
				this.currentQuantity[itemId] = BX('QUANTITY_' + itemId).value;
			}
		}
		
		this.lastStableQuantities = BX.clone(this.currentQuantity, true);
	};
	
	BX.Sale.BasketActionPool.prototype.changeQuantity = function(itemId) {
		var quantity = BX('QUANTITY_' + itemId).value;
		var isPoolEmpty = this.isPoolEmpty();

		if(this.currentQuantity[itemId] && this.currentQuantity[itemId] != quantity) {
			this.poolQuantity[itemId] = this.currentQuantity[itemId] = quantity;
		}
		
		if(!isPoolEmpty) {
			this.enableTimer(true);
		} else {
			this.trySendPool();
		}
	};


	BX.Sale.BasketActionPool.prototype.trySendPool = function() {	
		if(!this.isPoolEmpty() && !this.isProcessing()) {
			this.enableTimer(false);
			this.component.recalcBasketAjax({});
		}
	};

	BX.Sale.BasketActionPool.prototype.isPoolEmpty = function() {
		return(Object.keys(this.poolQuantity).length == 0);
	};

	BX.Sale.BasketActionPool.prototype.clearPool = function() {
		this.poolQuantity = {};
	};

	BX.Sale.BasketActionPool.prototype.isProcessing = function() {
		return (this.processing === true);
	};

	BX.Sale.BasketActionPool.prototype.setProcessing = function(value) {
		this.processing = (value === true);
	};

	BX.Sale.BasketActionPool.prototype.enableTimer = function(value) {
		clearTimeout(this.updateTimer);
		if(value === false)
			return;
		
		this.updateTimer = setTimeout(BX.proxy(this.trySendPool, this), 1500);
	};
})();