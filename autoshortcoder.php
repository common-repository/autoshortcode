<?php
/*
 * Plugin Name: autoshortcoder
 * Version: 1.0
 * Plugin URI: https://git.nexlab.net/nextime/autoshortcoder 
 * Description: programmatically add shortcodes to posts and pages
 * Author: Franco (nextime) Lanza
 * Author URI: http://www.nexlab.net
 * Requires at least: 4.0
 * Tested up to: 4.5.2
 *
 * Text Domain: autoshortcoder
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Franco (nextime) Lanza
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-autoshortcoder.php' );
require_once( 'includes/class-autoshortcoder-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-autoshortcoder-admin-api.php' );

/**
 * Returns the main instance of autoshortcoder to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object autoshortcoder
 */
function autoshortcoder () {
	$instance = autoshortcoder::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = autoshortcoder_Settings::instance( $instance );
	}

	return $instance;
}

autoshortcoder();
