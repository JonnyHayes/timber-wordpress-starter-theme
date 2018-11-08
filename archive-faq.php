<?php
/**
 * The template for displaying FAQ Archive page.
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


$context['pagination'] = Timber::get_pagination();
$context['title'] = __('Archive', 'mvnp_basic');
if(is_category()){
	$context['title'] = single_cat_title('', false);
	array_unshift($templates, 'archive-' . get_query_var('cat') . '.twig');
} else if(is_post_type_archive()){
	$context['title'] = post_type_archive_title('', false);
	array_unshift($templates, 'archive-' . get_post_type() . '.twig');
}

$context['posts'] = Timber::get_posts(array('post_type' => get_query_var('post_type'), 'post_parent' => 0));

Timber::render($templates, $context);
