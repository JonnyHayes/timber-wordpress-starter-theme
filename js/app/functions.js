/* global site: false, gaCode: false, gtmCode: false, pixelCode: false, fbq: false, ga: flase, google: false */
var win = $(window);

/**
 * The main app wrapper
 */
var app = {
	movementEvents: ['scroll', 'wheel', ' mousewheel', 'touchstart', 'touchmove'],
	ajaxUrl: site.url.replace('/' + site.language.slice(0, 2), '') + '/wp-admin/admin-ajax.php',

	/**
	 * Watch images when they come into the viewport and swap out low res images for full res. aka lazy loading.
	 * Currently only works with es6 capable browsers and falls back to loading the full resolution immediatly on older browsers
	 * TODO write an es5 intersection obersver and implement that as the fallback
	 */
	observeIntersections: function(){
		var img, no,
			imgs = document.querySelectorAll('[data-src]'),
			observer = (typeof IntersectionObserver === 'function') ? new IntersectionObserver(intersectionHandler, {threshold: 0, rootMargin: '50px 0px'}) : '';
		/**
		 * Handles the image changes when the object is scrolled into view
		 * @param  {object} changes  the observed object
		 * @param  {IntersectionObserver} observer the thing that does the watching
		 */
		function intersectionHandler(changes, observer){
			changes.forEach(function(change){
				if(change.isIntersecting){
					if(change.target.style.backgroundImage !== '' && change.target.dataset.src !== ''){
						img = document.createElement('img');

						img.src = change.target.dataset.src;
						img.onload = function(){
							change.target.style.backgroundImage = 'url("' + this.src + '")';
						};
					}

					if(change.target.src !== '' && typeof change.target.src !== 'undefined' && change.target.dataset.src !== ''){
						img = document.createElement('img');

						img.src = change.target.dataset.src;
						img.onload = function(){
							change.target.src = this.src;
							change.target.removeAttribute('width');
						};
					}else if(change.target.srcset !== '' && typeof change.target.srcset !== 'undefined' && change.target.dataset.src !== ''){
						img = document.createElement('img');

						img.src = change.target.dataset.src;
						img.onload = function(){
							change.target.srcset = this.src;
							change.target.removeAttribute('width');
						};
					}

					change.target.removeAttribute('data-src');
					change.target.classList.remove('lazy-placeholder');
					observer.unobserve(change.target);
				}
			});
		};

		for(no = 0; no < imgs.length; ++no){
			if(typeof observer.observe === 'function'){
				observer.observe(imgs[no]);
			}else{
				if($(imgs[no]).css('background-image') !== 'none'){
					$(imgs[no]).css('background-image', 'url("' + $(imgs[no]).data('src') + '")');
				}

				if($(imgs[no]).attr('src')){
					$(imgs[no]).attr('src', $(imgs[no]).data('src'));
				}else if($(imgs[no]).attr('srcset')){
					$(imgs[no]).attr('srcset', $(imgs[no]).data('src'));
				}
			}
		}
	},

	/**
	 * Do all the stuff that needs to be done when the window scrolls
	 * @param  {object} event the sroll event
	 */
	scrollEvent: function(event){
		if(typeof app.moveSidebar === 'function' && win.height() - 21 > app.sidebarInner.height()) app.moveSidebar();

		if(win.scrollTop() >= app.head.offset().top + app.head.outerHeight()){
			app.scrollToTop.addClass('show');
		}else{
			app.scrollToTop.removeClass('show');
		}

		$('.parallax').each(function(){
			app.moveParallax($(this));
		});

		document.removeEventListener(event, app.scrollEvent, {passive: true});
	},

	/**
	 * Messaging system. Adds colord badges with you message in the top right of the screen. to invoke, use notification(messge, level)
	 * @param  {string} msg   The message you want to display
	 * @param  {string} level Message type. Error, success and "normal", anything that is not error or success is treated as normal
	 * @return {function} Returns an object will notification properties
	 * TODO rewite into a less confusing form. Can probably ditch the IIFE and put the divs directly into the markup, this way we will simply have a notifications fn.
	 */
	notification: (function(){
		var visible = 6000,
			remove = 300,
			/**
			 * The notice constructor
			 * @param  {string} msg   The message you want to didsplay
			 * @param  {string} level the notice severity
			 * @return {object}       returns self
			 */
			Notice = function(msg, level){
				var _self = {},
					icon = document.createElement('div'),
					content = document.createElement('div');

				icon.className += ' notification-icon';
				content.className += ' message-content';
				icon.innerHTML = '!';
				_self.elem = document.createElement('div');
				_self.elem.className = 'entry';

				if(/(error|success)/.test(level)){
					_self.elem.className += ' ' + level;
				}

				content.innerHTML = msg;
				_self.elem.appendChild(icon);
				_self.elem.appendChild(content);

				if(/^error$/.test(level)){
					var close = document.createElement('div');
					close.className = 'notification-close';
					close.innerHTML = 'click to close';
					content.appendChild(close);
				}

				if(/^success$/.test(level)){
					// the second entity is a variation selector. necessary for stupid safari which replaces entities with emojis. god i hate emojis
					icon.innerHTML = '&#x2714;&#xFE0E;';
				}

				var clearElem = function(){
					if(_self.time) clearTimeout(_self.time);
					$(_self.elem).off('mousedown', clearElem);
					_self.elem.className += ' remove';
					setTimeout(function(){
						_self.elem.parentNode.removeChild(_self.elem);
					}, remove);
				};

				$(_self.elem).mousedown(clearElem);
				_self.time = setTimeout(clearElem, visible);

				setTimeout(function(){
					_self.elem.className += ' active';
				}, 5);
				return _self.elem;
			},
			/**
			 * @param  {string} msg   The message you want to didsplay
			 * @param  {string} level the notice severity
			 * @return  {string} the message array
			 */
			self = function(msg, level){
				if(!/(error|success)/.test(level)){
					level = 'normal';
				}

				if(Object.prototype.toString.call(msg) !== '[object Array]'){
					msg = [msg];
				}

				for(var ni = 0; ni < msg.length; ni++){
					if(self.preinit){
						self.prequeue.push({
							msg: msg[ni],
							level: level
						});
						continue;
					}
					self.elem.insertBefore(new Notice(msg[ni], level), self.elem.firstChild);
				}

				return msg;
			};

		self.elem = null;
		self.prequeue = [];
		self.preinit = setInterval(function(){
			if(!document.body){
				return;
			}

			clearInterval(self.preinit);

			if(!self.elem){
				self.elem = document.createElement('div');
				self.elem.className = 'notifications';
				self.elem.setAttribute('role', 'alert');
				self.elem.setAttribute('aria-relevant', 'all');
				document.body.appendChild(self.elem);
			}

			for(var ni = 0; ni < self.prequeue.length; ni++){
				self.elem.insertBefore(new Notice(self.prequeue[ni].msg, self.prequeue[ni].level), self.elem.firstChild);
			}

			self.preinit = null;
		}, 30);

		return self;
	})(),

	/**
	 * Attaches google map to a DOM element
	 * @param  {Object} elem The HTML element that you want to attach the map to
	 * @param  {string} [name='My Location'] Name on the marker
	 * @param  {Object} coords Object containing lat and lng strings
	 * @return {Boolean} Returns false if required params are missing.
	 */
	initMap: function(elem, name, coords){
		try{
			if(!google) throw __('Google maps not loaded', 'mvnp_basic');
			if(!elem) throw __('No element specified', 'mvnp_basic');
			if(!coords) throw __('No address specified', 'mvnp_basic');
		} catch (error){
			console.error(new Error(error));
			return app.notification(app.initMap.name.toString() + ': ' + error, 'error');
		}

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
	},

	/**
	 * Codes an addrress string to a lat/lng object and sends it to the map making function
	 * @param  {object} elem The HTML element that you want to attach the map to. Passed to initMap
	 * @param  {string} [name='My Location'] Name on the marker. Passed to initMap
	 * @param  {string} address Address string you want to encode
	 * @return {boolean|object} Google geocoder object. Returns false if required params are missing.
	 */
	codeAddress: function(elem, name, address, makeMap){
		try{
			if(!google) throw __('Google maps not loaded', 'mvnp_basic');
			if(!elem) throw __('No element specified', 'mvnp_basic');
			if(!address) throw __('No address specified', 'mvnp_basic');
		} catch (error){
			console.error(new Error(error));
			return app.notification(app.codeAddress.name.toString() + ': ' + error, 'error');
		}

		var geocoder = new google.maps.Geocoder();

		name = name || __('My Location', 'mvnp_basic');
		return geocoder.geocode({
			'address': address
		}, function(results, status){
			if(status === google.maps.GeocoderStatus.OK && makeMap){
				app.initMap(elem, name, {
					lat: results[0].geometry.location.lat(),
					lng: results[0].geometry.location.lng()
				});
			}
		});
	},

	/**
	 * Makes an ajax post to the wp signup function
	 * @param  {object} data  The form data
	 * @param  {function=} _callback Callback function
	 * @return {function}           The callback
	 */
	doSignup: function(data, _callback){
		data = {
			action: 'user_signup',
			data: data
		};

		$.ajax({
			type: 'POST',
			url: app.ajaxUrl,
			data: data,
			success: function(data){
				if(parseInt(data, 10) !== 0){
					if(data.status > 0){
						app.notification(__('Successfully signed up as %s', data.name, 'mvnp_basic'), 'success');
					}else{
						app.notification(data.errors, 'error');
					}
				}else{
					app.notification(__('Internal error. Please try again.', 'mvnp_basic'), 'error');
				}

				if(typeof _callback === 'function') return _callback(data);
				else return data !== 0;
			}
		});
	},

	/**
	 * Makes an ajax post to the wp login function
	 * @param  {object} data  The serialized form data
	 * @param  {function=} _callback Callback function
	 * @return {function}           The callback
	 */
	doLogin: function(data, _callback){
		data = {
			action: 'user_login',
			data: data
		};

		$.ajax({
			type: 'POST',
			url: app.ajaxUrl,
			data: data,
			success: function(data){
				if(parseInt(data, 10) !== 0){
					if(data.status > 0){
						app.notification(__('Successfully logged in as %s', data.name, 'mvnp_basic'), 'success');
					}else{
						app.notification(data.errors, 'error');
					}
				}else{
					app.notification(__('Internal error. Please try again.', 'mvnp_basic'), 'error');
				}

				if(typeof _callback === 'function') return _callback(data);
				else return data !== 0;
			}
		});
	},

	/**
	 * Makes an ajax post to the wp logout function
	 * @param  {object} data  The form data
	 * @param  {function=} _callback Callback function
	 * @return {function}           The callback
	 */
	doLogout: function(data, _callback){
		data = {
			action: 'user_logout',
			data: data
		};

		$.ajax({
			type: 'POST',
			url: app.ajaxUrl,
			data: data,
			success: function(data){
				if(parseInt(data, 10) !== 0){
					if(data.status > 0){
						app.notification(__('Logged out. Reloading browser window.', 'mvnp_basic'), 'success');
					}else{
						app.notification(data.errors, 'error');
					}
				}else{
					app.notification(__('Internal error. Please try again.', 'mvnp_basic'), 'error');
				}

				if(typeof _callback === 'function') return _callback(data);
				else return data !== 0;
			}
		});
	},

	/**
	 * Moves the paralxes
	 * @param  {object} self the element with the paralax
	 * @return {null}
	 */
	moveParallax: function(self){
		var paraScroll, _self,
			wintop = win.scrollTop();

		_self = self;
		if(self[0].bottom < wintop || self[0].top > wintop + win.height()){
			return;
		}

		paraScroll = ((self[0].center - wintop) / win.height()) * self[0].amount;
		self.css('background-position', '0 ' + clamp(paraScroll, 0, 100) + '%');
	},

	/**
	 * Close the modal
	 */
	modalClose: function(){
		app.backdrop.removeClass('show');
		app.modal.removeAttr('open');
		app.body.removeClass('no-scroll').removeAttr('style').scrollTop(app.winScroll);
		$('html, body').scrollTop(app.winScroll);
		setTimeout(function(){
			app.modalContent.html(app.loading);
			app.modal.removeAttr('style class').addClass('clearfix');
		}, 301);
	},

	/**
	 * Adds content to the modal div and fixes the window.
	 * @param  {function=} _callback Just a callback
	 * @return {function} Your callback
	 */
	makeModals: function(_callback){
		app.body = $('body');
		app.backdrop = $('#backdrop');
		app.modalContent = $('#modal-content');
		app.modal = $('#modal');
		app.loading = app.modalContent.html();
		app.winScroll = win.scrollTop();

		try{
			if(!app.backdrop || !app.body || !win) throw __('Something went horribly wrong', 'mvnp_basic');
			if(!$) throw __('jQuery not loaded', 'mvnp_basic');
			if(!app.modal || !app.modalContent) throw __('Modal element doesn\'t exist', 'mvnp_basic');
		} catch (error){
			console.error(new Error(error));
			return app.notification(app.makeModals.name.toString() + ': ' + error, 'error');
		}

		app.backdrop.addClass('show');
		app.modal.attr('open', '');
		app.body.addClass('no-scroll').css('top', -app.winScroll);
		win.scrollTop(0);

		$('#backdrop, #modal-close').click(function(e){
			if(e.target !== this){
				return;
			}

			app.modalClose();
		});

		win.on('keydown', function(e){
			if(e.which === 27){
				e.preventDefault();
				app.modalClose();
				win.off('keydown');
			}
		});

		if(typeof _callback === 'function') return _callback();

		return true;
	},

	/**
	 * Calendar!
	 * @param  {object} options An object containing the cal options like the calendar markup, the function executed on select, etc
	 * @return {object}         The calendar object
	 */
	calendar: function(options){
		try{
			if(!options) throw __('No options specified', 'mvnp_basic');
			if(!options.element) throw __('Send an element to your calendar', 'mvnp_basic');
		} catch (error){
			console.error(new Error(error));
			return app.notification(app.calendar.name.toString() + ': ' + error, 'error');
		}

		var self = {
			element: $(options.element),
			nav_date: options.date || new Date(),
			on_select: options.on_select || null,
			selected_date: null,
			valid_date: options.is_valid || function(date){
				return true;
			}
		};

		self.element.find('.cal-header .date').on('click', function(){
			self.nav_date = new Date();
			self.redraw();
		});

		self.element.find('.cal-header .next').on('click', function(){
			self.nav_date.setMonth(self.nav_date.getMonth() + 1);
			self.redraw();
		});

		self.element.find('.cal-header .prev').on('click', function(){
			self.nav_date.setMonth(self.nav_date.getMonth() - 1);
			self.redraw();
		});

		self.date = function(_date){
			if(!arguments.length) return self.nav_date;
			self.nav_date = _date;
			self.redraw();
		};

		/**
		 * Redraws the calendar based on the date and restricted days
		 */
		self.redraw = function(){
			var ni, day, isDisabled, isSelected,
				year = self.nav_date.getFullYear(),
				month = self.nav_date.getMonth(),
				lastLastDate = (new Date(year, month, 0)).getDate(),
				firstDay = (new Date(year, month, 1)).getDay(),
				lastDate = (new Date(year, month + 1, 0)).getDate(),
				lastDay = 6 - (new Date(year, month + 1, 0)).getDay(),
				anchor = self.element.find('.body');

			self.element.find('.cal-header .month').html(app.months[month]);
			self.element.find('.cal-header .year').html(year);
			anchor.html('');

			for(ni = firstDay - 1; ni >= 0; ni--){
				anchor.append('<div class="day disabled previous">' + (lastLastDate - ni) + '</div>');
			}

			for(ni = 0; ni < lastDate; ni++){
				isDisabled = !self.valid_date(new Date(year, month, ni + 1));
				isSelected = (self.selected_date && self.selected_date.valueOf() === (new Date(year, month, ni + 1)).valueOf());
				day = $('<div class="day' + (isDisabled ? ' disabled' : '') + '' + (!isDisabled && isSelected ? ' selected' : '') + '">' + (ni + 1) + '</div>');
				anchor.append(day);

				if(isDisabled) continue;

				day.on('click', (selectWrap)(new Date(year, month, ni + 1)));
			}

			for(ni = 0; ni < lastDay; ni++){
				anchor.append('<div class="day disabled previous">' + (ni + 1) + '</div>');
			}
		};

		/**
		 * The structural markup for the calendars
		 * TODO get this in a template for easier editing
		 */

		/**
		 * Closes the calendars attached to inputs
		 * @param  {object} e The click event that triggered the function
		 * @return {boolean}   returns true if the cal is closed successfully
		 */
		self.close = function(e){
			if($(e.target).parent('.input-wrap').length || $(e.target).parent('.calendar').length || $(e.target).parent('.cal-header').length || $(e.target).parent('.calendar-holder').length){
				return false;
			}
			e.preventDefault();
			$('.calendar').removeClass('show');
			$(window).off('click', app.calendar.close);

			return true;
		};

		/**
		 * Updates the calendar and runs the on select callback function
		 * @param  {Date} date the calendar date
		 * @return {function}
		 */
		function selectWrap(date){
			return function(evt){
				if(self.selected_date && self.selected_date === date){
					self.selected_date = null;
				}else{
					self.selected_date = date;
				}
				self.redraw();
				if(typeof self.on_select === 'function'){
					self.on_select(self.selected_date);
				}
			};
		}

		self.redraw();
		return self;
	},

	createCal: function(){
		return $([
			'<div class="calendar">',
			'<div class="cal-header">',
			'<div class="nav prev">&#8592;</div>',
			'<div class="nav next">&#8594;</div>',
			'<div class="date">',
			'<span class="month">December</span>',
			'<span class="year">2013</span>',
			'</div>',
			'</div>',
			'<div class="days clearfix">',
			'<div class="day">', __('translators: Sunday', 'S', 'mvnp_basic'), '</div>',
			'<div class="day">', __('M', 'mvnp_basic'), '</div>',
			'<div class="day">', __('translators: Tuesday', 'T', 'mvnp_basic'), '</div>',
			'<div class="day">', __('W', 'mvnp_basic'), '</div>',
			'<div class="day">', __('translators: Thursday', 'T', 'mvnp_basic'), '</div>',
			'<div class="day">', __('F', 'mvnp_basic'), '</div>',
			'<div class="day">', __('translators: Saturday', 'S', 'mvnp_basic'), '</div>',
			'</div>',
			'<div class="body clearfix">',
			'	<!-- <div class="day available disabled today"></div> -->',
			'</div>',
			'</div>'
		].join(''));
	},

	analytics: (function(){
		var codes = {};

		if(gaCode && typeof gaCode === 'string' && (!gtmCode || gtmCode === '')){
			(function(i, s, o, g, r, a, m){
				i.GoogleAnalyticsObject = r;
				i[r] = i[r] || function(){
					(i[r].q = i[r].q || []).push(arguments);
				};
				i[r].l = 1 * new Date();
				a = s.createElement(o);
				m = s.getElementsByTagName(o)[0];
				a.async = 1;
				a.src = g;
				m.parentNode.insertBefore(a, m);
			})(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');
			ga('create', gaCode, 'auto');
			ga('send', 'pageview');

			codes.ga = gaCode;
		}

		if(gtmCode && typeof gtmCode === 'string'){
			(function(w, d, s, l, i){
				w[l] = w[l] || [];
				w[l].push({
					'gtm.start': new Date().getTime(),
					event: 'gtm.js'
				});
				var f = d.getElementsByTagName(s)[0],
					j = d.createElement(s),
					dl = l !== 'dataLayer' ? '&l=' + l : '';
				j.async = true;
				j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
				f.parentNode.insertBefore(j, f);
			})(window, document, 'script', 'dataLayer', gtmCode);

			codes.gtm = gtmCode;
		}

		if(pixelCode && typeof pixelCode === 'string'){
			(function(f, b, e, v, n, t, s){
				if(f.fbq) return;
				n = f.fbq = function(){
					n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
				};
				if(!f._fbq) f._fbq = n;
				n.push = n;
				n.loaded = !0;
				n.version = '2.0';
				n.queue = [];
				t = b.createElement(e);
				t.async = !0;
				t.src = v;
				s = b.getElementsByTagName(e)[0];
				s.parentNode.insertBefore(t, s);
			})(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

			fbq('init', pixelCode);
			fbq('track', 'PageView');

			codes.pixel = pixelCode;
		}

		return codes.length ? codes : false;
	})()
};

/**
 * JS sprintf Translation function. It takes the following params: translator hint, translation string,
 * any number of vars as strings for $s and finally, text domain, in that order.
 * @return {string} the translated string
 */
var __ = (function(){
	var langFile = {},
		ret = function(){
			return (function(args){
				var hint = args[0].indexOf('translators:') === 0,
					_val = args[hint ? 1 : 0],
					_key = args[0],
					_params = Array.prototype.slice.call(args, hint ? 2 : 1, args.length - 1);

				return (function(){
					var text = '' + _val;

					if(langFile && langFile[_key]){
						text = langFile[_key];
					}

					// basic sprintf feature
					var p, index, ni, no,
						bits = text.split('%'),
						out = bits[0],
						re = /^([ds])(.*)$/;

					for(ni = 1; ni < bits.length; ni++){
						if(bits[ni].indexOf('$') < 0){
							p = re.exec(bits[ni]);
							if(!p || _params[ni - 1] === null) continue;
							if(p[1] === 'd'){
								out += parseFloat(_params[ni - 1], 10);
							}else if(p[1] === 's'){
								out += _params[ni - 1];
							}
							out += p[2];
						}else{
							index = bits[ni].split('$');
							for(no = 1; no < index.length; no++){
								p = re.exec(index[no]);
								if(!p || _params[index[0] - 1] === null) continue;
								if(p[1] === 'd'){
									out += parseFloat(_params[index[0] - 1], 10);
								}else if(p[1] === 's'){
									out += _params[index[0] - 1];
								}
								out += p[2];
							}
						}
					}
					return out;
				})();
			})(arguments);
		};

	ret.load = function(i18n, _cb){
		if(i18n && i18n !== 'en_US'){
			$.get(site.theme.uri + '/languages/' + i18n + '.po?_=' + (new Date()).getTime(), function(data){
				var curr, i,
					lines = data.split('\n'),
					len = lines.length;

				for(i = 0; i < len; i++){
					if(lines[i].substring(0, 5) === 'msgid'){
						curr = lines[i].substring(5).trim().replace(/["]+/g, '');

						if(curr === '') continue;

						if(lines[i + 1].substring(0, 6) === 'msgstr'){
							langFile[curr] = lines[i + 1].substring(6).trim().replace(/["]+/g, '');
						}
					}
				}
			}).promise().done(function(){
				if(typeof _cb === 'function') _cb();
				return true;
			});
		}else if(typeof _cb === 'function') _cb();
		return true;
	};

	return ret;
})();

site.language = site.language.replace('-', '_');

__.load(site.language, function(){
	app.locale = site.language;
	app.months = [
		__('January', 'mvnp_basic'),
		__('February', 'mvnp_basic'),
		__('March', 'mvnp_basic'),
		__('April', 'mvnp_basic'),
		__('May', 'mvnp_basic'),
		__('June', 'mvnp_basic'),
		__('July', 'mvnp_basic'),
		__('August', 'mvnp_basic'),
		__('September', 'mvnp_basic'),
		__('October', 'mvnp_basic'),
		__('November', 'mvnp_basic'),
		__('December', 'mvnp_basic')
	];
	app.days = [
		__('Sunday', 'mvnp_basic'),
		__('Monday', 'mvnp_basic'),
		__('Tuesday', 'mvnp_basic'),
		__('Wednesday', 'mvnp_basic'),
		__('Thursday', 'mvnp_basic'),
		__('Friday', 'mvnp_basic'),
		__('Saturday', 'mvnp_basic')
	];
});

/**
 * Grabs the ULR parameters and returns an object
 * @param  {string} [query=window.location.search.substring(1)] Fully formatted URL, in case you dont want to use the current pages params
 * @return {object} URL params returned as an object
 */
function urlParams(query){
	var queryString = {},
		vars, pair, i, arr;

	query = query ? query.split('?')[1] : window.location.search.substring(1);

	if(!query){
		return query;
	}

	vars = query.split('&');

	for(i = 0; i < vars.length; i++){
		pair = vars[i].split('=');

		if(typeof queryString[pair[0]] === 'undefined'){
			queryString[pair[0]] = decodeURIComponent(pair[1]);
			// If second entry with this name
		}else if(typeof queryString[pair[0]] === 'string'){
			arr = [queryString[pair[0]], decodeURIComponent(pair[1])];
			queryString[pair[0]] = arr;
			// If third or later entry with this name
		}else{
			queryString[pair[0]].push(decodeURIComponent(pair[1]));
		}
	}

	return queryString;
}

/**
 * Scroll to an element on the page
 * @param  {string|object} target target Where you want the window to scroll to. Can be a DOM element or a selector for an element. gets wrapped in jQ
 * @param  {number} speed=1000 the time it takes in ms to scroll to the element
 * @param  {number} offset=28 amout to offset the scroll. for when you dont want the window exactly at the top of the element
 * @return {boolean} True when element exists, false when not found.
 */
function scrollTo(target, speed, offset){
	target = $(target) || null;
	speed = parseInt(speed, 10) || 1000;
	offset = parseInt(offset, 10) || 28;

	if(target.length){
		$('html, body').animate({
			scrollTop: target.offset().top - offset
		}, speed);

		return true;
	}

	return false;
};

/**
 * Clamps your input
 * @param  {number} num Number to be clamped
 * @param  {number} min Minimum nuber to be returned
 * @param  {number} max Maximum nuber to be returned
 * @param  {number} radix the number base
 * @return {number}     The clamped value
 */
function clamp(num, min, max, radix){
	radix = radix || 10;
	return radix === 10 ? (num <= min ? min : num >= max ? max : num) : parseInt((num <= min ? min : num >= max ? max : num), radix).toString(radix);
}

/**
 * Pads your number. Adds leading zeros
 * @param  {number} num  the number you wish to pad
 * @param  {number} size how long you want the number, not the number of zeros
 * @return {number}      the padded number
 */
function pad(num, size){
	var s = num + '';
	while(s.length < size) s = '0' + s;
	return s;
}

/**
 * Debounces your function. ie waits for the the end of input before executing
 * @param  {function} fn The function you want to debounce
 * @param  {number} delay The amount of time before executing (in ms)
 * @return {function} Returns the function with a timout
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
 * Throttles your function. ie creates a fixed lag after input before executing
 * @param  {function} fn The functionn you want to throttle
 * @param  {number} threshhold=250 The amout you want to throttle it by (in ms)
 * @param  {object} scope The scope
 * @return {function} Returns the function with a timout
 */
function throttle(fn, threshhold, scope){
	var last,
		deferTimer;

	threshhold = threshhold || (threshhold = 250);

	return function(){
		var context = scope || this,
			now = +new Date(),
			args = arguments;

		if(last && now < last + threshhold){
			// hold on to it
			clearTimeout(deferTimer);
			deferTimer = setTimeout(function(){
				last = now;
				fn.apply(context, args);
			}, threshhold);
		}else{
			last = now;
			fn.apply(context, args);
		}
	};
}
