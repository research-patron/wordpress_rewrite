<?php
/**
 * Core bootstrap for Rewrite Toolkit.
 */

if ( ! class_exists( 'Rewrite_Toolkit' ) ) {
    class Rewrite_Toolkit {
        const OPTION_RULES = 'rewrite_toolkit_rules';
        const OPTION_TAGS  = 'rewrite_toolkit_tags';

        /** @var Rewrite_Toolkit */
        private static $instance;

        /**
         * Retrieve singleton instance.
         */
        public static function get_instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        private function __construct() {
            register_activation_hook( REWRITE_TOOLKIT_PATH . 'rewrite-toolkit.php', array( $this, 'activate' ) );
            register_deactivation_hook( REWRITE_TOOLKIT_PATH . 'rewrite-toolkit.php', array( $this, 'deactivate' ) );

            add_action( 'plugins_loaded', array( $this, 'load_plugin' ) );
        }

        /**
         * Load dependencies and hooks.
         */
        public function load_plugin() {
            load_plugin_textdomain( 'rewrite-toolkit', false, dirname( plugin_basename( REWRITE_TOOLKIT_PATH . 'rewrite-toolkit.php' ) ) . '/languages' );

            require_once REWRITE_TOOLKIT_PATH . 'includes/helpers.php';
            require_once REWRITE_TOOLKIT_PATH . 'includes/class-rewrite-toolkit-admin.php';
            require_once REWRITE_TOOLKIT_PATH . 'includes/class-rewrite-toolkit-rest.php';

            if ( defined( 'WP_CLI' ) && WP_CLI ) {
                require_once REWRITE_TOOLKIT_PATH . 'includes/class-rewrite-toolkit-cli.php';
            }

            add_action( 'init', array( $this, 'register_rewrite_assets' ) );
            add_action( 'init', array( $this, 'register_query_vars' ), 11 );
            add_action( 'admin_init', array( $this, 'maybe_flush_rules' ) );
        }

        /**
         * Register rewrite tags and rules.
         */
        public function register_rewrite_assets() {
            $tags = $this->get_tags();

            foreach ( $tags as $tag ) {
                if ( empty( $tag['name'] ) || empty( $tag['regex'] ) ) {
                    continue;
                }

                $tag_name = '%' . sanitize_title_with_dashes( $tag['name'] ) . '%';
                add_rewrite_tag( $tag_name, $tag['regex'] );
            }

            $rules = $this->get_rules();

            foreach ( $rules as $rule ) {
                if ( empty( $rule['pattern'] ) || empty( $rule['query'] ) || empty( $rule['enabled'] ) ) {
                    continue;
                }

                $priority = in_array( $rule['priority'], array( 'top', 'bottom' ), true ) ? $rule['priority'] : 'top';
                add_rewrite_rule( $rule['pattern'], $rule['query'], $priority );
            }
        }

        /**
         * Ensure query vars registered.
         */
        public function register_query_vars() {
            $tags = $this->get_tags();
            $vars = array();

            foreach ( $tags as $tag ) {
                if ( empty( $tag['query_var'] ) ) {
                    continue;
                }

                $vars[] = sanitize_key( $tag['query_var'] );
            }

            if ( empty( $vars ) ) {
                return;
            }

            add_filter(
                'query_vars',
                function ( $public_query_vars ) use ( $vars ) {
                    foreach ( $vars as $var ) {
                        if ( ! in_array( $var, $public_query_vars, true ) ) {
                            $public_query_vars[] = $var;
                        }
                    }

                    return $public_query_vars;
                }
            );
        }

        /**
         * Maybe flush rewrite rules when requested.
         */
        public function maybe_flush_rules() {
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }

            if ( ! empty( $_POST['rewrite_toolkit_flush_rules'] ) && check_admin_referer( 'rewrite_toolkit_flush' ) ) {
                $this->flush_rules();
            }
        }

        /**
         * Activation callback.
         */
        public function activate() {
            $this->flush_rules();
        }

        /**
         * Deactivation callback.
         */
        public function deactivate() {
            flush_rewrite_rules();
        }

        /**
         * Flush stored rewrite rules and track timestamp.
         */
        public function flush_rules() {
            flush_rewrite_rules();
            update_option( 'rewrite_toolkit_last_flush', time() );
        }

        /**
         * Return rewrite rules from storage.
         */
        public function get_rules() {
            $rules = get_option( self::OPTION_RULES, array() );

            return is_array( $rules ) ? $rules : array();
        }

        /**
         * Return rewrite tags from storage.
         */
        public function get_tags() {
            $tags = get_option( self::OPTION_TAGS, array() );

            return is_array( $tags ) ? $tags : array();
        }
    }
}
