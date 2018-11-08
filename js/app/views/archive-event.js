/* global site: false, win: true, urlParams: false, __: false, app: true */

/**
 * Makes an ajax call to get the events for the given date
 * @param  {number} _date     unix timecode for the date to fetch (first day on month).
 * @param  {string} _category category slug to fetch
 * @param  {object} that      element scope to apply to
 */
function getEvents(_date, _category, that){
	scrollTo('html');
	$('.event-navigation').prop('disabled', true);
	$('#event-list').html($('#modal-content').html());
	$.get(window.location.href.split('?')[0] + '?date=' + Math.floor(_date.getTime() / 1000) + '&category=' + _category, function(data){
		$('#event-list').html($(data).find('#' + that.attr('data-target')).html());
		$('#event-list .event-category').click(function(e){
			that = $(this);
			e.preventDefault();

			$('#event-categories .event-category[data-category="' + that.data('category') + '"]').addClass('selected').siblings().removeClass('selected');
			$('.event-navigation').prop('disabled', true);
			$('#event-list').html($('#modal-content').html());
			_category = that.data('category');

			getEvents(_date, _category, that);
		});
		$('#events-month').html(app.months[_date.getMonth()] + ' ' + _date.getFullYear());
		$('.event-navigation').removeAttr('disabled');
		app.observeIntersections();
	});
}

$(document).ready(function(){
	var ni, calendarDate,
		_date = new Date(),
		_category = urlParams().category || '';

	_date = new Date(_date.getFullYear(), _date.getMonth(), 1);
	$('.calendar-monthly .body').html('');

	for(ni = 0; ni < app.months.length; ni++){
		$('.calendar-monthly .body').append('<div class="col-sm-4 month ' + app.months[ni].toLowerCase() + '">' + (app.months[ni].length > 4 ? app.months[ni].slice(0, 3) + '.' : app.months[ni]) + '</div>');
	}

	$('#events-month').click(function(e){
		var that = $(this);

		calendarDate = _date;
		$('.calendar-monthly .cal-header .date').html(calendarDate.getFullYear());

		if(e.target === that[0]){
			$('.calendar').removeClass('show');
			that.siblings('.calendar').addClass('show');
			win.on('click.eventCalendar', function(e){
				if($(e.target).parent('.input-wrap').length || $(e.target).parent('.calendar').length || $(e.target).parent('.cal-header').length || $(e.target).parent('.calendar-holder').length){
					return false;
				}
				e.preventDefault();
				$('.calendar').removeClass('show');
				win.off('click.eventCalendar');

				return true;
			});
		}
	});

	$('.calendar-monthly .cal-header .nav').click(function(e){
		var that = $(this);
		e.preventDefault();

		if(that.attr('class').indexOf('prev') > 0){
			calendarDate.setYear(calendarDate.getFullYear() - 1);
		}else if(that.attr('class').indexOf('next') > 0){
			calendarDate.setYear(calendarDate.getFullYear() + 1);
		}

		$('.calendar-monthly .cal-header .date').html(calendarDate.getFullYear());
	});

	$('.calendar-monthly .body .month').click(function(e){
		var that = $(this);
		e.preventDefault();

		that.closest('.calendar').removeClass('show');
		_date.setMonth(that.index());
		_date.setYear(calendarDate.getFullYear());
		getEvents(_date, _category, $('#events-month'));
	});

	$('.event-navigation').click(function(e){
		var that = $(this);
		e.preventDefault();

		if(that.attr('id').indexOf('prev') > 0){
			_date.setMonth(_date.getMonth() - 1);
		}else if(that.attr('id').indexOf('next') > 0){
			_date.setMonth(_date.getMonth() + 1);
		}

		getEvents(_date, _category, that);
	});

	$('#event-categories .event-category[data-category="' + _category + '"]').addClass('selected').siblings().removeClass('selected');

	$('.event-category').click(function(e){
		var that = $(this);
		e.preventDefault();

		$('#event-categories .event-category[data-category="' + that.data('category') + '"]').addClass('selected').siblings().removeClass('selected');
		_category = that.data('category');

		getEvents(_date, _category, that);
	});
});
