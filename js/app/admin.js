/* globals siteUrl: true, google: false, __: false, qTranslateX: false, qTranslateConfig: false, wp: false, eventAddress: false, repeating: true, recurrence: true, noLocation: true */
/**
 * Debounces your function. ie waits for the the end of input before executing
 * @param  {Function} fn The function you want to debounce
 * @param  {Number} delay The amount of time before executing (in ms)
 * @return {Function} Returns the function with a timout
 */
function debounce(fn, delay){
	var timer = null;

	return function(){
		var context = this,
			args = arguments;

		clearTimeout(timer);
		timer = setTimeout(function(){
			fn.apply(context, args);
		}, delay);
	};
}

/**
 * Makes an ajax post to the wp signup function
 * @param  {obj} data  The form data
 * @param  {fn} _callback Callback function
 * @return {fn}           The callback
 */
function importGoogleCalendar(data, _callback){
	data = {
		action: 'import_google_cal'
	};

	jQuery.ajax({
		type: 'POST',
		url: siteUrl + '/wp-admin/admin-ajax.php',
		data: data,
		success: function(data){
			location.reload();
		}
	});
}

/**
 * Attaches google map to a DOM element
 * @param  {Object} elem The HTML element that you want to attach the map to
 * @param  {string} [name='My Location'] Name on the marker
 * @param  {Object} coords Object containing lat and lng strings
 * @return {Boolean} Returns false if required params are missing.
 */
function initMap(elem, name, coords){
	var map, marker;

	name = name || __('My Location', 'mvnp_basic');
	map = new google.maps.Map(elem, {
		center: coords,
		zoom: 18,
		scrollwheel: false
	});

	marker = new google.maps.Marker({
		position: coords,
		map: map,
		title: name
	});

	return true;
}

/**
 * Codes an addrress string to a lat/lng object and sends it to the map making function
 * @param  {Object} elem The HTML element that you want to attach the map to. Passed to initMap
 * @param  {string} [name='My Location'] Name on the marker. Passed to initMap
 * @param  {string} address Address string you want to encode
 * @return {Boolean|Object} Google geocoder object. Returns false if required params are missing.
 */
function codeAddress(elem, name, address, makeMap){
	var geocoder = new google.maps.Geocoder();

	name = name || __('My Location', 'mvnp_basic');
	return geocoder.geocode({
		'address': address
	}, function(results, status){
		if(status === google.maps.GeocoderStatus.OK && makeMap){
			initMap(elem, name, {
				lat: results[0].geometry.location.lat(),
				lng: results[0].geometry.location.lng()
			});
		}
	});
}

jQuery(document).ready(function($){
	if(typeof qTranslateX === 'function'){
		var qtx = new qTranslateX(qTranslateConfig);
	}

	/**
	 * Pulls up the in-post WP media manager and sets values for the media object when selected
	 * @param  {object} self The Scope of the element that was clicked
	 */
	function makeMediaGallery(self){
		if(!self) return;

		var metaImageFrame,
			mediaAttachment;

		if(metaImageFrame){
			metaImageFrame.open();
			return;
		}
		metaImageFrame = wp.media.frames.metaImageFrame = wp.media({
			// title: meta_image.title,
			// button: { text:  meta_image.button },
			// library: { type: 'image' }
		});

		metaImageFrame.on('select', function(){
			mediaAttachment = metaImageFrame.state().get('selection').first().toJSON();
			jQuery(self).nextAll('.custom-media-url').html(JSON.stringify(mediaAttachment));
			jQuery(self).attr('src', mediaAttachment.sizes.thumbnail.url);

			qtx.removeContentHook(jQuery(self).parent().next().find('.name-input')[0]);
			jQuery(self).parent().next().find('.name-input').val(mediaAttachment.title);
			qtx.addContentHook(jQuery(self).parent().next().find('.name-input')[0]);

			qtx.removeContentHook(jQuery(self).parent().next().find('.alt-input')[0]);
			jQuery(self).parent().next().find('.alt-input').val(mediaAttachment.alt);
			qtx.addContentHook(jQuery(self).parent().next().find('.alt-input')[0]);

			qtx.removeContentHook(jQuery(self).parent().next().find('.copy-input')[0]);
			jQuery(self).parent().next().find('.copy-input').val(mediaAttachment.caption);
			qtx.addContentHook(jQuery(self).parent().next().find('.copy-input')[0]);
		});

		metaImageFrame.open();
	}

	$('#repeatable-fieldset-one .ui-sortable').sortable({
		handle: '.drag-handle',
		placeholder: 'drag-placeholder'
	});

	$('.import-google-cal').on('click', function(e){
		e.preventDefault();
		importGoogleCalendar();
	});

	if($('#event-location-map').length && eventAddress){
		codeAddress($('#event-location-map')[0], 'My event location', eventAddress, true);
	}

	if(repeating){
		$('#repeating-properties').show();

		if(recurrence.FREQ === 'DAILY'){
			$('#interval-wrapper').hide();
		}

		if(recurrence.FREQ === 'WEEKLY'){
			$('#recurr_days').show();
		}

		if(recurrence.FREQ === 'MONTHLY'){
			$('#recurr_by').show();
		}
	}

	if(typeof noLocation !== 'undefined' && !noLocation){
		$('#event_location').slideDown();
	}

	$('.location-input').on('input', debounce(function(){
		if($(this).val()){
			codeAddress($('#event-location-map')[0], 'My event location', $(this).val(), true);
		}
	}, 750));

	$('#all_day').on('change', function(){
		var that = $(this);

		$('.event-time').each(function(){
			$(this).attr('disabled', that.prop('checked'));
		});
	});

	$('#recurring').on('change', function(){
		var that = $(this);
		repeating = that.prop('checked');

		if(repeating){
			$('#repeating-properties').slideDown();
		}else{
			$('#repeating-properties').slideUp();
		}
	});

	$('#noLocation').on('change', function(){
		var that = $(this);
		noLocation = that.prop('checked');

		if(!noLocation){
			$('#event_location').slideDown();
		}else{
			$('#event_location').slideUp();
		}
	});

	$('#freq').on('change', function(){
		var that = $(this);

		if(that.val() === 'DAILY'){
			$('#interval-wrapper').hide();
		}else{
			$('#interval-wrapper').show();
		}

		if(that.val() === 'WEEKLY'){
			$('#recurr_days').show();
		}else{
			$('#recurr_days').hide();
		}

		if(that.val() === 'MONTHLY'){
			$('#recurr_by').show();
		}else{
			$('#recurr_by').hide();
		}
	});

	/**
	 * Adds a new item to the gallery manager. Sets name attribtes to the newly created row elements
	 */
	$('#add-row').on('click', function(){
		var row = document.createElement('tr');

		$(row).html($('.empty-row.screen-reader-text').html());
		$(row).attr('name', 'item[]');
		$(row).find('.custom-media-url').attr('name', 'props[]');
		$(row).find('.name-input').attr('name', 'name[]');
		$(row).find('.alt-input').attr('name', 'alt[]');
		$(row).find('.link-input').attr('name', 'link[]');
		$(row).find('.iframe-input').attr('name', 'iframe[]');
		$(row).find('.copy-input').attr('name', 'copy[]');
		$(row).find('.toggle-media').click(function(e){
			$(this).nextAll('.media-image, label').toggleClass('hidden');
			$(this).nextAll('.media-image').attr('src', siteUrl + '/images/gallery-no-image.jpg');
			$(this).nextAll('.custom-media-url').html('');
			$(this).nextAll('label').find('.iframe-input').val('');
		});

		$(row).find('.media-image').click(function(e){
			e.preventDefault();
			makeMediaGallery(this);
		});

		$(row).find('.remove-row').on('click', function(){
			$(this).parents('tr').remove();
			return false;
		});

		$(row).insertBefore('#repeatable-fieldset-one tbody>tr:last');
		$(row).find('input[class*="input"], textarea[class*="input"]').each(function(){
			$(this).attr('id', $(this).attr('id').replace('new', $(row).index() + 1));
			qtx.addContentHooks($(this));
		});

		return false;
	});

	/**
	 * Removes a row from the gallery manager
	 */
	$('.remove-row').on('click', function(){
		$(this).parents('tr').remove();

		return false;
	});

	/**
	 * Just runs the media manager opener when you click an image
	 */
	$('.media-image').click(function(e){
		e.preventDefault();
		makeMediaGallery(this);
	});

	$('.toggle-media').click(function(){
		$(this).nextAll('.media-image, label').toggleClass('hidden');
		$(this).nextAll('.media-image').attr('src', siteUrl + '/images/gallery-no-image.jpg');
		$(this).nextAll('.custom-media-url').html('');
		$(this).nextAll('label').find('.iframe-input').val('');
	});
});
