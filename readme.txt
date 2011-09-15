=== Snowflake ===
Contributors: willnorris
Requires at least: 3.2

Syndicate your WordPress posts out to various social services.

== Installation ==

This plugin follows the [standard WordPress installation method][]:

1. Upload the `openid` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

[standard WordPress installation method]: http://codex.wordpress.org/Managing_Plugins#Installing_Plugins

To activate Google+, first [register for a Google API Key][].  Then define a 
GOOGLE_API_KEY constant by adding the following to your wp-config.php:

    define('GOOGLE_API_KEY', 'AIzaSyAajUpwv85lxDxytrqVpXopaCzOPv3mqfg');

[register for a Google API Key]: code.google.com/apis/console?api=plus#:access

Get your Google+ ID by visiting <http://profiles.google.com/me> and note the 
numeric ID in the URL.  Go to your WordPress profile and enter this number 
as your "Google+ ID".  Then you should be good to go.
