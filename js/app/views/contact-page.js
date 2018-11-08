/* globals contactMapLocation, app, __, site: false */
$(document).ready(function(){
	if(contactMapLocation && $('#contact-map').length){
		app.codeAddress($('#contact-map')[0], site.title, contactMapLocation, true);
	}
});
