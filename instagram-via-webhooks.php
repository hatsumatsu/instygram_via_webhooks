<?php
/*
 * Plugin Name: instagram_via_webhooks
 * Version: 1.0
 * Plugin URI: http://www.whiskyvangoghgo.com/
 * Description: Receive Instagram updates via IFTTT.
 * Author: Eric Jacobsen
 * Author URI: http://www.whiskyvangoghgo.com/
 * Requires at least: 4.4.2
 * Tested up to: 4.4.2
 *
 * Text Domain: instagram-via-webhooks
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Eric Jacobsen
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-instagram-via-webhooks.php' );
require_once( 'includes/class-instagram-via-webhooks-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-instagram-via-webhooks-admin-api.php' );
require_once( 'includes/lib/class-instagram-via-webhooks-post-type.php' );
require_once( 'includes/lib/class-instagram-via-webhooks-taxonomy.php' );

/**
 * Returns the main instance of instagram_via_webhooks to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object instagram_via_webhooks
 */
function instagram_via_webhooks () {
	$instance = instagram_via_webhooks::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = instagram_via_webhooks_Settings::instance( $instance );
	}

	return $instance;
}

instagram_via_webhooks();
