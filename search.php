<?php
/**
 * Search results page
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.1
 */

$templates = array('search.twig', 'archive.twig', 'index.twig');
$context = Timber::get_context();
$context['title'] = __('Search results for', 'mvnp_basic') . ' ' . get_search_query();

$context['post'] = new stdClass();
$context['post']->title = $context['title'];
$context['post']->type = 'page';
$context['post']->post_content = __('Search results for', 'mvnp_basic') . ' ' . get_search_query();

$context['posts'] = Timber::get_posts();

Timber::render($templates, $context);
