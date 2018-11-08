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

$templates = array('archive.twig', 'index.twig');

$context = Timber::get_context();
$context['post'] = new stdClass();
$context['pagination'] = Timber::get_pagination();
$context['post']->title = $context['title'] = __('Archive', 'mvnp_basic');

if(is_day()){
	$context['post']->title = $context['title'] = __('Archive', 'mvnp_basic') . ': ' . get_the_date('D M Y');
	$context['post']->post_content = sprintf(__('These are the archived blog posts from %1$s for %2$s', 'mvnp_basic'), get_bloginfo('name'), get_the_date('D M Y'),  'mvnp_basic');
} else if(is_month()){
	$context['post']->title = $context['title'] = __('Archive', 'mvnp_basic') . ': ' . get_the_date('M Y');
	$context['post']->post_content = sprintf(__('These are the archived blog posts from %1$s for %2$s', 'mvnp_basic'), get_bloginfo('name'), get_the_date('M Y'),  'mvnp_basic');
} else if(is_year()){
	$context['post']->title = $context['title'] = __('Archive', 'mvnp_basic') . ': ' . get_the_date('Y');
	$context['post']->post_content = sprintf(__('These are the archived blog posts from %1$s for %2$s', 'mvnp_basic'), get_bloginfo('name'), get_the_date('Y'),  'mvnp_basic');
} else if(is_tag()){
	$context['post']->title = $context['title'] = single_tag_title('', false);
	$context['post']->post_content = sprintf(__('These are the archived blog posts from %1$s with tag %2$s', 'mvnp_basic'), get_bloginfo('name'), single_tag_title('', false),  'mvnp_basic');
} else if(is_category()){
	$context['post']->title = $context['title'] = single_cat_title('', false);
	$context['post']->post_content = sprintf(__('These are the archived blog posts from %1$s in category %2$s', 'mvnp_basic'), get_bloginfo('name'), single_cat_title('', false),  'mvnp_basic');
	array_unshift($templates, 'archive-' . get_query_var('cat') . '.twig');
} else if(is_post_type_archive()){
	$context['post']->title = $context['title'] = post_type_archive_title('', false);
	array_unshift($templates, 'archive-' . get_post_type() . '.twig');
}

$context['post']->link = home_url(add_query_arg(array(), $wp->request));
$context['post']->type = 'page';

$context['posts'] = Timber::get_posts();

Timber::render($templates, $context);
