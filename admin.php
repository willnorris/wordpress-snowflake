<?php

add_action('add_meta_boxes', 'snowflake_add_meta_boxes');

function snowflake_add_meta_boxes() {
  foreach (array('page', 'post') as $post_type) {
    add_meta_box('snowflake-syndicate', __('Syndicate', 'snowflake'), 'snowflake_syndicate_meta_box', 
      $post_type, 'advanced', 'high');
  }
}

function snowflake_syndicate_meta_box($post) {
  if ($post->post_status != 'publish') {
    echo '<p>' . __('This post must be published before it can be syndicated.', 'snowflake') . '</p>';
  } else {
    do_action('snowflake_syndicate_meta_box', $post);
  }
}

