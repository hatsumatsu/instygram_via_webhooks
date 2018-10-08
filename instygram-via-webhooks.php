<?php
/*
 * Plugin Name: instygram_via_webhooks
 * Version: 1.0.5
 * Plugin URI: http://www.whiskyvangoghgo.com/
 * Description: Receive instagram updates via IFTTT.
 * Author: Eric Jacobsen
 * Author URI: http://www.whiskyvangoghgo.com/
 * Requires at least: 4.4.2
 * Tested up to: 4.4.2
 *
 * Text Domain: instygram-via-webhooks
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Eric Jacobsen
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-instygram-via-webhooks.php' );
require_once( 'includes/class-instygram-via-webhooks-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-instygram-via-webhooks-admin-api.php' );
require_once( 'includes/lib/class-instygram-via-webhooks-post-type.php' );
require_once( 'includes/lib/class-instygram-via-webhooks-taxonomy.php' );

/**
 * Returns the main instance of instygram_via_webhooks to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object instygram_via_webhooks
 */
function instygram_via_webhooks () {
	$instance = instygram_via_webhooks::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = instygram_via_webhooks_Settings::instance( $instance );
	}

	return $instance;
}

instygram_via_webhooks();
