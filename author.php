<?php
/**
 * The template for displaying Author Archive pages
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */
global $wp_query;

$context = Timber::get_context();
$context['posts'] = Timber::get_posts();
if(isset($wp_query->query_vars['author'])){
	$author = new TimberUser($wp_query->query_vars['author']);
	$context['author'] = $author;


	$context['post'] = new stdClass();
	$context['post']->title = $context['title'] = sprintf(__('Author Archives: %s', 'mvnp_basic'), $author->name());
	$context['post']->link = home_url(add_query_arg(array(), $wp->request));
	$context['post']->type = 'page';
	$context['post']->post_content = $author->description();
}

Timber::render(array('author.twig', 'archive.twig'), $context);
