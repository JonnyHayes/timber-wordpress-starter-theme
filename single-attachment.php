<?php
/**
 * The Template for displaying image attachments. Probably just going to norobots this since its not a useful page.
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context = Timber::get_context();
$post = Timber::query_post();
$context['post'] = $post;

Timber::render('single-attachment.twig', $context);
