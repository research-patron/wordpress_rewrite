<?php
/**
 * Plugin Name: Rewrite Toolkit
 * Description: Adds a UI, REST API, and WP-CLI helpers for managing custom rewrite tags and rules.
 * Version: 1.0.0
 * Author: OpenAI Assistant
 * Text Domain: rewrite-toolkit
 * Domain Path: /languages
 */

define( 'REWRITE_TOOLKIT_VERSION', '1.0.0' );
define( 'REWRITE_TOOLKIT_PATH', plugin_dir_path( __FILE__ ) );
define( 'REWRITE_TOOLKIT_URL', plugin_dir_url( __FILE__ ) );

require_once REWRITE_TOOLKIT_PATH . 'includes/class-rewrite-toolkit.php';

Rewrite_Toolkit::get_instance();
