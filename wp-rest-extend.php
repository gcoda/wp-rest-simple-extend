<?php
/*
 * Plugin Name: Simple WP-REST Extender 
 * Version: 1.0.0
 * Description: Adding few routes to /wp-json/extend, /cat/CATEGORY_ID will list jsut post ids,  /post/POST_ID will get you post title, content, images and thumbnail with direct links
 * Author: Evgeniy Foucault
 * Author URI: https://gcoda.github.io
 * Plugin URI: https://github.com/gcoda/wp-rest-simple-extend
 * Text Domain: wp-rest-simple-extend
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
*/

function posts_in_cat( WP_REST_Request $request) {
  $parameters = $request->get_params();
  $res_posts = [];
	$posts = get_posts( array(
		'category' => $request['id'],
	) );

	if ( empty( $posts ) ) {
		return null;
	}
  
  foreach ( $posts as $post ) {
    setup_postdata( $post );
     $res_posts[] = $post->ID;
  }

  return $res_posts;
  return json_encode($posts, JSON_UNESCAPED_UNICODE);
}

function posts_in_cat_day (  ) {
  $args = array(
	'cat'      => 22,
  date_query => array (
    'after' => array (
      'year' => '2016',
      'month' => '12',
      'day' => '1'
    ),
    'before' => array (
      'year' => '2017',
      'month' => '12',
      'day' => '1'
    )
  ),
  // 'column' => 'post_date_gmt',
  // 'inclusive' => false,
  'orderby'  => 'date',
	'order'    => 'DESC'
  );
  
  query_posts( $args );

}

function one_post( $data ) {
	$post = get_post( $data['id'] );
	if ( empty( $post ) ) {
		return null;
	}

  $tpost = [];

  $images =& get_children( array(
	  'post_parent' => $post->ID,
	  'post_type'   => 'any', 
	  'numberposts' => -1,
	  'post_status' => 'any' 
  ) );
  $post_thumbnail_id = get_post_thumbnail_id($post->ID);
  $post_thumbnail_url = wp_get_attachment_url( $post_thumbnail_id );
    
  $tpost['images'] = $images;
  $tpost['thumbnail'] = $post_thumbnail_url;
    
  $tpost['thumbnail-html'] = get_the_post_thumbnail( $post->ID, 'thumbnail', '');

  $tpost['title'] = $post->post_title;
  $tpost['id'] = $post->ID;
  $post_split  = explode('<!--more-->', $post->post_content);
  $tpost['excerpt'] = $post_split[0];
  $tpost['content'] = $post_split[1];
  $tpost['date'] = $post->post_date_gmt;
	return $tpost;
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'extend', '/cat/(?P<id>\d+)', array(
		'methods' => 'GET',
		'callback' => 'posts_in_cat',
	) );
} );

add_action( 'rest_api_init', function () {
	register_rest_route( 'extend', '/post/(?P<id>\d+)', array(
		'methods' => 'GET',
		'callback' => 'one_post',
	) );
} );

