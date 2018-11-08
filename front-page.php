<?php
/**
 * The template for displaying the front page.
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;
$context['gallery'] = Timber::get_post(148);
Timber::render('front-page.twig', $context );
