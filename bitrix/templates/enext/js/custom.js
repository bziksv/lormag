/***CUSTOM JAVASCRIPT FOR YOUR SITE***/
$(function() {
	
	$('[data-text]').each(function(i, el){
			let self = $(el);
			self.text(self.data('text'));
	});
});