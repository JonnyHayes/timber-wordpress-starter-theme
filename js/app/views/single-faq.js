/* globals mapLocation, app, __ */
$(document).ready(function(){
	$('.faq-slide-content h4').click(function(e){
		var that = $(this),
			hasClass = that.nextAll('div').hasClass('open');

		$('.faq-slide-content div').removeClass('open');

		if(!hasClass){
			setTimeout(function(){
				that.nextAll('div').addClass('open');
			}, 301);
		}
	});
});
