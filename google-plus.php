<?php

/**
 * Add a Google +1 button to the snowflake syndication meta box.
 */
function snowflake_googleplus_meta_box($post) {
  $user = wp_get_current_user();
  $googleplus_id = get_user_meta($user->ID, 'googleplus_id', true);

  if ( !defined('GOOGLE_API_KEY') ) {
    echo '<p>' . __('Please set your Google API Key.') . '</p>';
    return;
  }

  if ( !$googleplus_id ) {
    echo '<p>' . __('Please set your Google+ ID.') . '</p>';
    return;
  }
?>
  <g:plusone size="medium" href="<?php echo get_permalink($post); ?>" onendinteraction="plusone_end"></g:plusone>
  <script>
    // load async
    (function() {
      jQuery('<script>', {async:true, src:'https://apis.google.com/js/plusone.js'}).prependTo('script:first');
    })();

    function plusone_end(data) {
      if (true || data.type == 'confirm') {
        // fetch recent activities from Google+ API
        jQuery.getJSON('https://www.googleapis.com/plus/v1/people/<?php echo $googleplus_id; ?>/activities/public?callback=?', {
          fields: 'items/id,items/url,items/object/attachments/objectType,items/object/attachments/url',
          maxResults: 5,
          key: '<?php echo GOOGLE_API_KEY; ?>'
        }, function(response) {
          for (var i=0; i<response.items.length; i++) {
            var item = response.items[i];
            if (item['object'] != undefined && item['object'].attachments != undefined) {
              var attachments = item['object'].attachments;
              for (var j=0; j<attachments.length; j++) {
                var attachment = attachments[j];
                if (attachment.objectType == 'article' && attachment.url == data.id) {
                  setGooglePlusData(item.id, item.url);
                  return;
                }
              }
            }
          }
        });
      }
    }

    function setGooglePlusData(id, url) {
      // pull WordPress post ID out of the URL (TODO: there must be a better way)
      var post_id = location.href.match(/post=(\d+)/)[1];

      jQuery.post(ajaxurl, {
        action:'snowflake_googleplus_metadata',
        post_id: post_id,
        googleplus_id: id,
        googleplus_url: url
      }, function(response) {
        if (response['googleplus_url'] != undefined) {
          jQuery('#snowflake-syndicate .inside').append('<p>Updated <a href="' 
            + response.googleplus_url + '">Google+ URL</a></p>');
        }
      });
    }
  </script>
<?php
}
add_action('snowflake_syndicate_meta_box', 'snowflake_googleplus_meta_box');


function snowflake_googleplus_ajax_metadata() {
  extract($_POST);
  if ($post_id) {
    if ($googleplus_id && !get_post_meta($post_id, '_googleplus_id')) {
      update_post_meta($post_id, '_googleplus_id', $googleplus_id);
    }
    if ($googleplus_url && !get_post_meta($post_id, '_googleplus_url')) {
      update_post_meta($post_id, '_googleplus_url', $googleplus_url);
    }
  }
  header('content-type: application/json');
  $response = array(
    'googleplus_id' => get_post_meta($post_id, '_googleplus_id', true),
    'googleplus_url' => get_post_meta($post_id, '_googleplus_url', true),
  );
  echo json_encode($response);
  die();
}
add_action('wp_ajax_snowflake_googleplus_metadata', 'snowflake_googleplus_ajax_metadata');


function snowflake_googleplus_user_contactmethods($methods) {
  $methods['googleplus_id'] = 'Google+ ID';
  return $methods;
}
add_filter('user_contactmethods', 'snowflake_googleplus_user_contactmethods');
