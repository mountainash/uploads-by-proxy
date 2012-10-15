<?php
/*
Plugin Name: Uploads by Proxy
Plugin URI: http://brainstormmedia.com
Description: Load images from live site if missing in development or staging environment. Only runs in a local development environment by default. Force the plugin to run with <code>define('UBP_IS_LOCAL', true);</code> in wp-config.php. If live domain is different than development domain, set the live domain with <code>define('UBP_LIVE_DOMAIN', 'live-domain.com');</code> in wp-config.php.
Version: 1.0
Author: Brainstorm Media
Author URI: http://brainstormmedia.com
*/

/**
 * Copyright (c) 2012 Brainstorm Media. All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

/**
 * Load live images from a domain differing from the current site's
 * For example, we're on domain.dev or stage.domain.com but want to load from domain.com
 */
if ( !defined('UBP_LIVE_DOMAIN') ) define('UBP_LIVE_DOMAIN', $_SERVER['HTTP_HOST'] ); // e.g., domain.com

/**
 * Check that we're on a development server.
 * This tests if we're serving from and to localhost (127.0.0.1),
 * which should catch most common dev environments like MAMP, WAMP, XAMPP, etc.
 *
 * If you're hosting from a staging environment, or some weird situation where
 * this test doesn't return true, redefine it in wp-config.php:
 *     define('UBP_IS_LOCAL', true);
 *
 * 	   WARNING!!
 *     Do not set this to "true" on a live site!
 *     Doing so will cause 404 pages for wp-content/uploads to go into
 *     an infinite loop until Apache kills the PHP process.
 */
if ( !defined('UBP_IS_LOCAL') ) define('UBP_IS_LOCAL', ( '127.0.0.1' == $_SERVER['SERVER_ADDR'] && '127.0.0.1' == $_SERVER['REMOTE_ADDR'] ) );

/**
 * Used for deactivating the plugin here or in class-helpers.php if requirements aren't met.
 */
define( 'UBP_PLUGIN_FILE', __FILE__ );

/**
 * Check for PHP 5.2 or higher before activating.
 */
if ( version_compare(PHP_VERSION, '5.2', '<') ) {
	if ( is_admin() && (!defined('DOING_AJAX') || !DOING_AJAX) ) {
		require_once ABSPATH.'/wp-admin/includes/plugin.php';
		deactivate_plugins( UBP_PLUGIN_FILE );
		wp_die( sprintf( __( 'Uploads by Proxy requires PHP 5.2 or higher, as does WordPress 3.2 and higher. The plugin has now disabled itself. For information on upgrading, %ssee this article%s.', 'uploads-by-proxy'), '<a href="http://codex.wordpress.org/Switching_to_PHP5" target="_blank">', '</a>') );
	} else {
		return;
	}
}

require_once dirname( __FILE__ ).'/class-helpers.php';

// Only initialize if we're on a development server
if ( UBP_IS_LOCAL ) {
	add_action( 'admin_init', 'UBP_Helpers::requirements_check' );
	add_filter( '404_template', 'UBP_Helpers::init_404_template' );
}