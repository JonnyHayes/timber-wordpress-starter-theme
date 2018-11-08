/* globals mapLocation, app, __, post: false */
$(document).ready(function(){
	if(post.custom.location){
		app.codeAddress($('#event-map')[0], post.custom.location_name || post.post_title, post.custom.location, true);
	}
});
