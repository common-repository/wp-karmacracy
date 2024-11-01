<?php
/*
  Plugin Name: WP Karmacracy
  Plugin URI: http://www.berriart.com/en/wp-karmacracy/
  Description: Karmacracy is an easy way to know people sharing relevant content around your social networks, it’s a new generation URL shortener. This wordpress plugin will allow you to generate and insert short URLs from this service onto a post or page, resumes your Karmacracy profile in a dashboard widget and allows you to share your published posts on Facebook or Twitter.
  Version: 1.1
  Author: Alberto Varela
  Author URI: http://www.berriart.com
  Requires at least: 3.0
  License: GPL2
*/

/*  Released in june 2011 by Alberto Varela. (email : alberto@berriart.com)
    Initially based on a Ronald Huereca's plugin (PluginBuddy YOURLS - http://wordpress.org/extend/plugins/pluginbuddy-yourls/)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

    Althought you may obtain a copy of the License at

    http://www.gnu.org/licenses/gpl-2.0.html
*/

// requires
include dirname (__FILE__) . '/karmacracy-functions.php';
include dirname (__FILE__) . '/karmacracy-insert-link.php';
include dirname (__FILE__) . '/karmacracy-dashboard-widget.php';
include dirname (__FILE__) . '/karmacracy-share-post.php';

global $wp_karmacracy;

// Ensure WP version
if (get_bloginfo('version') >= "3.0") {

    // Plugin localization
    wp_karmacracy_localization();
    // Instantiate class with the admin options and the inserting links in post code
    add_action('plugins_loaded', 'wp_karmacracy_instantiate');
    // Executes the warnings
    wp_karmacracy_warnings();
    // Add dashboard widget
    add_action( 'wp_dashboard_setup', 'wp_karmacracy_add_dashboard_widget' );
    // Add the option to share post on twitter or facebook
    add_action( 'publish_post', 'wp_karmacracy_share_post' );

}




