<?php
/**
 * The Template for displaying all single posts
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

// $child_args = array(
// 	'post_type' => 'faq',
// 	'post_parent' => $post->ID,
// 	'numberposts' => -1,
// 	'post_status' => 'publish'
// );
//
// $children = get_children($child_args);

$context = Timber::get_context();
$post = Timber::query_post();
$context['post'] = $post;
//$context['children'] = Timber::get_posts($child_args);

Timber::render('single-faq.twig', $context);
