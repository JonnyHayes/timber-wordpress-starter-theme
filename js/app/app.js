/* global site: false, __: false, app: true, win: true, urlParams: false, pad: false */
$(document).ready(function(){
	app.head = $('header[role="banner"]');
	app.scrollToTop = $('#scroll-to-top');

	app.observeIntersections();
	/**
	 * Add and removes the hasValue class from inputs for label position
	 */
	$('input, textarea').each(function(){
		var that = $(this);

		if(that.val()){
			that.addClass('hasValue');
			that.nextAll('.clear-input').click(function(e){
				that.val('');
				that.removeClass('hasValue');
			});
		}
	}).on('blur', function(){
		var that = $(this);

		if(that.val()){
			that.addClass('hasValue');
			that.nextAll('.clear-input').click(function(e){
				that.val('');
				that.removeClass('hasValue');
			});
		}else{
			that.removeClass('hasValue');
		}
	});

	/**
	 * Changes the language in a ~~super~~ much less jank way.
	 */
	$('#language-switcher').change(function(e){
		var that = $(this);

		window.location = $('link[hreflang="' + that.val() + '"]').attr('href');
	});

	/**
	 * From submition for login
	 */
	$('#footer-login').submit(function(e){
		var that = $(this);

		e.preventDefault();
		that.find('button[type="submit"]').prop('disabled', true);
		app.doLogin(urlParams('?' + that.serialize()), function(data){
			if(data.status && data.status > 0){
				that.removeClass('show');
				$('#footer-username').html(__('Welcome Back %s!', data.name, 'mvnp_basic'));
				$('#footer-logout button').removeAttr('tabindex');
				$('#footer-login button').attr('tabindex', '-1');
				$('#footer-logout').addClass('show');
				$('nav[role="navigation"] ul').append('<li><a href="' + site.url + '/users/' + data.cleanName + '" title="' + __('My Page', 'mvnp_basic') + '">' + __('My Page', 'mvnp_basic') + '</a></li>');
			}

			that.find('button[type="submit"]').removeAttr('disabled');
		});
	});

	/**
	 * From submition for logout
	 */
	$('#footer-logout').submit(function(e){
		var that = $(this);

		e.preventDefault();
		that.find('button[type="submit"]').prop('disabled', true);
		app.doLogout(urlParams('?' + that.serialize()), function(data){
			if(data.status && data.status > 0){
				that.removeClass('show');
				$('#footer-login button').removeAttr('tabindex');
				$('#footer-logout button').attr('tabindex', '-1');
				$('#footer-login').addClass('show');
				location.reload();
			}

			that.find('button[type="submit"]').removeAttr('disabled');
		});
	});

	/**
	 * Creates the signup modal and from submition for the signup
	 */
	$('#footer-signup-link').click(function(e){
		e.preventDefault();

		app.makeModals();
		app.modal.addClass('signup-modal');
		app.modalContent.load(site.theme.uri + '/views/signup.twig' + '?_=' + (new Date()).getTime(), function(){
			$('#footer-signup input#user_signup_email').focus();
			$('#footer-signup input').on('blur', function(){
				if($(this).val()){
					$(this).addClass('hasValue');
				}else{
					$(this).removeClass('hasValue');
				}
			});

			$('#footer-signup').submit(function(e){
				var that = $(this);

				e.preventDefault();
				that.find('button[type="submit"]').prop('disabled', true);
				app.doSignup(urlParams('?' + that.serialize()), function(data){
					if(data.status && data.status > 0){
						$('#footer-login').removeClass('show');
						$('#footer-username').html(__('Welcome Back %s!', data.name, 'mvnp_basic'));
						$('#footer-logout').addClass('show');
						$('nav[role="navigation"] ul').append('<li><a href="' + site.url + '/users/' + data.name + '" title="My Page">My Page</a></li>');
						app.modalClose();
					}

					that.find('button[type="submit"]').removeAttr('disabled');
				});
			});
		});
	});

	/**
	 * Creates the image modal. Currently works on img tags with class modal-img.
	 * the data-target att on the img should be a link to the full size image. if no target is provided, the src is used
	 */
	$('img.modal-img').click(function(e){
		var src = $(this).attr('data-target') || $(this).attr('src'),
			img = document.createElement('img');

		app.makeModals();
		img.src = src;
		app.modal.addClass('image-modal');
		img.onload = function(){
			app.modalContent.html(img);
			app.modal.css({
				'height': $(img).height(),
				'width': $(img).width(),
				'top': -$(img).height() / 2,
				'margin': '50vh auto 0'
			});
		};
	});

	/**
	 * Creates an ajax modal. Currently works on anchor tags with class modal-ajax and pulls out the data-target attr.
	 * that attr should be a class/id/attr on the target page. if no data-target is present, the role="main" content is used
	 */
	$('a.modal-ajax').click(function(e){
		var url = $(this).attr('href'),
			target = $(this).attr('data-target') || 'article[role="article"]';

		e.preventDefault();
		app.makeModals();
		app.modal.addClass('content-modal');
		$.get(url, function(data){
			if($(data).find(target).length){
				app.modalContent.html($(data).find(target));
			}else{
				app.modalContent.html($(data).find('article[role="article"]'));
			}
		});
	});

	/**
	 * This makes the sidebar sticky, if it exists.
	 */
	if($('aside[role="complementary"]').length && $(window).width() > 980){
		var sidebarWrapper = $('aside[role="complementary"]'),
			stop = $('footer[role="contentinfo"]'),
			wd, hi;

		app.sidebarInner = $('aside[role="complementary"] > div');

		app.moveSidebar = function(){
			if(win.scrollTop() >= sidebarWrapper.offset().top - 20 && app.sidebarInner.height() < win.height() - app.head.height()){
				wd = app.sidebarInner.width();
				hi = app.sidebarInner.height();
				sidebarWrapper.css('height', sidebarWrapper.height());
				app.sidebarInner.css({
					'position': 'fixed',
					'top': 20,
					'width': wd,
					'height': hi
				});
			}else{
				app.sidebarInner.removeAttr('style');
				sidebarWrapper.removeAttr('style');
			}

			if(win.scrollTop() + app.sidebarInner.height() >= stop.offset().top - 41){
				app.sidebarInner.css({
					'top': stop.offset().top - win.scrollTop() - hi - 21
				});
			}

			return app.sidebarInner.offset().top;
		};
	}

	$('nav[role=navigation] ul ul a').focus(function(){
		var that = $(this);

		that.closest('ul').prev('a').addClass('child-has-focus');
	}).blur(function(){
		var that = $(this);

		that.closest('ul').prev('a').removeClass('child-has-focus');
	});

	/**
	 * The actions for the button that shows/hides the menu on mobile.
	 * TODO Find a better way to apply the no-scroll class to the body. i dont like doing it based on a number. or at least a static number
	 */
	$('#burger, .close-sidebar').click(function(e){
		e.preventDefault();
		$('#burger, header[role="banner"] nav').toggleClass('show');

		if(win.outerWidth() <= 600){
			$('body').toggleClass('no-scroll');
		}
	});

	/**
	 * Just a thing to make the window scroll
	 */
	app.scrollToTop.click(function(e){
		scrollTo('html');
	});

	/**
	 * Grabs all the hash links and runs the scrollTo function
	 */
	$('a[href*="#"]').click(function(e){
		if(location.pathname.replace(/^\//, '') === this.pathname.replace(/^\//, '') && location.hostname === this.hostname){
			e.preventDefault();
			scrollTo(this.hash);
		}
	});

	/**
	 * Attach calendars to all inputs of type "date"
	 */
	$('input[type="date"]').each(function(){
		var that = $(this),
			date = app.calendar({
				element: app.createCal(),
				on_select: function(newDate){
					that.val(function(){
						return newDate.getFullYear() + '-' + pad(newDate.getMonth() + 1, 2) + '-' + pad(newDate.getDate(), 2);
					});
					that.addClass('hasValue');
					that.nextAll('.clear-input').click(function(e){
						that.val('');
						that.removeClass('hasValue');
						date.element.removeClass('show');
					});
					date.element.removeClass('show');
				},
				is_valid: function(date){
					return date.getTime() > new Date().getTime();
				}
			});

		that.parent().append(date.element).click(function(e){
			if(e.target === that[0]){
				$('.calendar').removeClass('show');
				that.siblings(date.element).addClass('show');
				win.on('click', date.close);
			}
		});
	});

	/**
	 * move the sidebar and parallaxes on page load in case the window isnt at the top
	 */
	if(typeof app.moveSidebar === 'function') app.moveSidebar();

	if(win.scrollTop() >= app.head.offset().top + app.head.outerHeight()){
		app.scrollToTop.addClass('show');
	}

	/**
	 * Adds scrollEvent function to various event listeners.
	 * This was done to make sure all scrolling events (ie touch, scroll, etc) are in passive mode.
	 */
	for(var ni = 0; ni < app.movementEvents.length; ++ni){
		document.addEventListener(app.movementEvents[ni], app.scrollEvent, {passive: true});
	}
});

/**
 * Hide the fullscreen loader. This happens at window.load so we can wait for all images to load too
 */
$(window).on('load', function(){
	$('#fullscreen-loading').addClass('hide');
	$('header[role="banner"], main[role="main"], footer[role="contentinfo"]').removeClass('blur');
	setTimeout(function(){
		$('#fullscreen-loading').remove();
		$('body').removeClass('no-scroll');
	}, 301);

	setTimeout(function(){
		$('.parallax').each(function(){
			var that = $(this),
				height = that.height(),
				top = that.offset().top,
				img = document.createElement('img');

			img.src = that.css('background-image').replace('url(', '').replace(')', '').replace(/"/gi, '');
			that[0].top = top;
			that[0].bottom = top + height;
			that[0].center = (top + that[0].bottom) / 2;
			img.onload = function(){
				that[0].amount = (img.height / img.width) * 100;
				app.moveParallax(that);
			};
		});
	}, 100);
});
