<?php
/**
 * The template for displaying 404 pages (Not Found)
 *
 * Methods for TimberHelper can be found in the /functions sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context = Timber::get_context();

$context['post'] = new stdClass();
$context['post']->title = 'Page not found';
$context['post']->link = home_url(add_query_arg(array(), $wp->request));
$context['post']->type = 'page';
$context['post']->post_content = 'The page you are looking for cannot found';

Timber::render('404.twig', $context);
