<?php
/*
Plugin Name: Default Post Thumbnail
Plugin URI:
Description: Default the post thumbnail to the first attachment, if there is one.
Version: 1.0
Author: Austin Matzko
Author URI: http://austinmatzko.com
*/

class Post_Thumb_Default
{
	public function __construct()
	{
		add_filter( 'get_post_metadata', array( $this, 'filter_get_post_metadata' ), 10, 4 );
	}

	public function filter_get_post_metadata( $ignore = null, $object_id = 0, $meta_key = null, $single = null )
	{
		global $pages, $post, $wpdb;
		$object_id = (int) $object_id;


		$id = null;
		if ( '_thumbnail_id' == $meta_key && ! is_admin() ) {
			$existing = $wpdb->get_var( 
				"SELECT meta_value 
					FROM {$wpdb->postmeta} 
					WHERE 
						post_id = {$object_id} AND
						meta_key = '_thumbnail_id' 
					LIMIT 1
				"
			);

			if ( empty( $existing ) ) {

				if ( is_object( $pages ) ) {
					$_pages = clone $pages;
				} else {
					$_pages = $pages;
				}

				if ( is_object( $post ) ) {
					$_post = clone $post;
				} else {
					$_post = $post;
				}

				$attachments_query = new WP_Query( array(
					'post_parent' => $object_id,
					'post_mime_type' => 'image',
					'post_status' => 'inherit',
					'post_type' => 'attachment',
					'orderby' => 'menu_order',
					'order' => 'ASC',
					'showposts' => 1,
				) );

				if ( $attachments_query->have_posts() ) {
					$attachments_query->the_post();
					$id = get_the_ID();
				}
				wp_reset_query();
				$GLOBALS['pages'] = $_pages;
				$GLOBALS['post'] = $_post;
			}
		}
	
		
		if ( ! empty( $id ) ) {
			return $id;
		} else {
			return $ignore;
		}
	}
}

function load_post_thumb_default()
{
	global $post_thumb_default;
	$post_thumb_default = new Post_Thumb_Default;
}

add_action( 'plugins_loaded', 'load_post_thumb_default' );
