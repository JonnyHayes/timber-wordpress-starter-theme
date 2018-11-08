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

$context = Timber::get_context();
$post = Timber::query_post();
$context['post'] = $post;
$context['gmt_offset_seconds'] = get_option('gmt_offset') * 3600;
if(!is_array($context['post']->custom['recurrence']) || strlen($context['post']->custom['recurrence'][0]) > 0){
	$context['post']->custom['start_date'] = $_GET['start'] ? $_GET['start'] : $context['post']->custom['start_date'];
	$context['post']->custom['end_date'] = $_GET['end'] ? $_GET['end'] : $context['post']->custom['end_date'];
}
Timber::render('single-event.twig', $context);
