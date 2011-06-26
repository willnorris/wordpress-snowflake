<?php
/*
 Plugin Name: Snowflake
 Plugin URI: http://github.com/willnorris/wordpress-snowflake
 Description: Content syndicator for WordPress
 Author: Will Norris
 Author URI: http://willnorris.com/
 Version: 1.0
 License: Dual GPL (http://www.fsf.org/licensing/licenses/info/GPLv2.html) and Modified BSD (http://www.fsf.org/licensing/licenses/index_html#ModifiedBSD)
 Text Domain: hum
 */


/**
 * Get the content of the specified post, truncated to a maximum number of
 * characters.  Attribution will be added to the end of the content in the
 * form of a shortlink back to the original post URL or a schemeless URI
 * reference.
 *
 * @param int $id post ID
 * @param int $max maximum length for the shortened post
 * @return string shortened post content
 */
function snowflake_shorten_post( $id, $max = -1 ) {
  $post = get_post($id);
  $post_id = $post->ID;

  $content = '';
  $content = $post->post_title;
  if ( !$content ) {
    $content = $post->post_content;
  }
  if ( !$content ) {
    // not able to find content for this post
    return false;
  }

  $shortlink = wp_get_shortlink($post_id);
  $shortid = preg_replace('|https?://|', '', $shortlink);

  // best case shortened post
  $shortpost = $content . ' (' . $shortid . ')';

  if ( $max >= 0 && $max < strlen($shortpost) ) {
    $content_max = $max - strlen($shortlink) - 1;
    $shortpost = ellipsize_to_word($content, $content_max, '...', 0) . ' ' . $shortlink;
  }

  $shortpost = apply_filters('snowflake_shorten_post', $shortpost, $id, $max);
  return $shortpost;
}

/**
 * Initialize snowflake handlers.
 */
function snowflake_init() {
  $post_types = array('post');
  $post_types = apply_filters('snowflake_post_types', $post_types);
  foreach( $post_types as $post_type ) {
    add_action("publish_{$post_type}", 'snowflake_publish', 10, 2);
  }
}
add_action('init', 'snowflake_init');


/**
 * Publish the specified post to all snowflake connectors.
 *
 * @uses do_action() Calls "snowflake_publish" action
 */
function snowflake_publish( $id, $post ) {
  do_action('snowflake_publish', $id, $post);
}


// connectors

/**
 * Publish the post to Twitter.  Requires wp-to-twitter plugin.
 */
function snowflake_post_to_twitter($id, $post) {
  $shortpost = snowflake_shorten_post($id, 140);
  if (function_exists('jd_doTwitterAPIPost')) {
    jd_doTwitterAPIPost( $shortpost );
  }
}
add_action('snowflake_publish', 'snowflake_post_to_twitter', 10, 2);


// Cassis
// slightly modified from Cassis Project (http://cassisproject.com/)
// Copyright 2010 Tantek Çelik, released under Creative Commons by-sa 3.0

if ( !function_exists('ellipsize_to_word') ):
function ellipsize_to_word($s, $max, $e, $min) {
  if (strlen($s)<=$max) {
    return $s; // no need to ellipsize
  }

  $elen = strlen($e);
  $slen = $max-$elen;

  // if last characters before $max are ': ', truncate w/o ellipsis.
  // no need to take length of ellipsis into account
  if ($e=='...') {
    for ($ii=2;$ii<=$elen+1;$ii++) {
      if (substr($s,$max-$ii,2)==': ') {
        return substr($s,0,$max-$ii+1);
      }
    }
  }

  if ($min) {
    // if a non-zero minimum is provided, then
    // find previous space or word punctuation to break at.
    // do not break at %`'"&.!?^ - reasons why to be documented.
    while ($slen>$min && strpos('@$ -~*()_+[]\{}|;,<>',$s[$slen-1]) === FALSE) {
      --$slen;
    }
  }
  // at this point we've got a min length string, 
  // so only do minimum trimming necessary to avoid a punctuation error.
  
  // trim slash after colon or slash
  if ($s[$slen-1]=='/' && $slen > 2) {
    if ($s[$slen-2]==':') {
      --$slen;    
    }
    if ($s[$slen-2]=='/') {
      $slen -= 2;
    }
  }

  //if trimmed at a ":" in a URL, trim the whole thing
    //or trimmed at "http", trim the whole URL
  if ($s[$slen-1]==':' && $slen > 5 && substr($s,$slen-5,5)=='http:') {
    $slen -= 5;
  }
  else if ($s[$slen-1]=='p' && $slen > 4 && substr($s,$slen-4,4)=='http') {
    $slen -= 4;
  }
  
  //if char immediately before ellipsis would be @$ then trim it as well
  if ($slen > 0 && strpos('@$',$s[$slen-1]) !== FALSE) {
    --$slen;
  }
 
  //while char immed before ellipsis would be a sentence terminator, trim 2 more
  while ($slen > 1 && strpos('.!?',$s[$slen-1]) !== FALSE) {
    $slen-=2;
  }

  if ($slen < 1) { // somehow shortened too much
    return $e; // or ellipsis by itself filled/exceeded max, return ellipsis.
  }

  // if last two chars are ': ', omit ellipsis. 
  if ($e=='...' && substr($s,$slen-2,2)==': ') {
    return substr($s,0,$slen);
  }

  return substr($s,0,$slen) . $e;
}
endif;

