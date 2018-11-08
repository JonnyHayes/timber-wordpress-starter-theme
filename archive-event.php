<?php
/**
 * The template for displaying Archive pages.
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.2
 */

$category = $_GET['category'] ? $_GET['category'] : null;
$month_start = $_GET['date'] ? $_GET['date'] : strtotime(date('Ym01'));
$month_end = $_GET['date'] ? strtotime('+1 month', strtotime(date('Ym01', $_GET['date']))) : strtotime('+1 month', strtotime(date('Ym01')));

$args = array(
	'post_type' => 'event',
	'posts_per_page' => -1,
	'order' => 'ASC',
	'meta_key' => 'start_date',
	'orderby' => 'meta_value',
	'meta_query' => array(
		'relation' => 'OR',
		array(
			'key' => 'recurrence',
			'compare' => '!=',
			'value' => '',
		),
		array(
			'relation' => 'AND',
			array(
				'key' => 'start_date',
				'compare' => '>=',
				'value' => $month_start,
			),
			array(
				'key' => 'end_date',
				'compare' => '<',
				'value' => $month_end,
			),
		),
	),
);

if($category != ''){
	$args['tax_query'] = array(
		array(
			'taxonomy' => 'event-category',
			'field' => 'slug',
			'terms' => $category,
		),
	);
}

$events = expand_recurring_events($args, $month_start, $month_end);
$context = Timber::get_context();
$context['post'] = new stdClass();
if(isset($category) && !empty($category)){
	$context['title'] = sprintf(__('Events in category %s', 'mvnp_basic'), get_term_by('slug', $category, 'event-category')->name);
	$context['post']->post_content = sprintf(__('Upcoming events for %1$s in category %2$s', 'mvnp_basic'), get_bloginfo('name'), get_term_by('slug', $category, 'event-category')->name);
}else{
	$context['title'] = __('Events', 'mvnp_basic');
	$context['post']->post_content = sprintf(__('Upcoming events for %s', 'mvnp_basic'), get_bloginfo('name'));
}

$context['post']->title = $context['title'];
$context['post']->link = home_url(add_query_arg(array(), $wp->request));
$context['post']->type = 'page';

$context['month'] = $_GET['date'] ? $_GET['date'] : strtotime(date('Ym'));
$context['posts'] = sort_posts($events, 'start_date', 'ASC', false);
$context['event_categories'] = get_terms('event-category');
Timber::render('archive-event.twig', $context);
