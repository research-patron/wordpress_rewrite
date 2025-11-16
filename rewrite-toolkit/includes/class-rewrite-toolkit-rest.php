<?php
/**
 * REST API endpoints for Rewrite Toolkit.
 */

if ( ! class_exists( 'Rewrite_Toolkit_REST' ) ) {
    class Rewrite_Toolkit_REST {
        public function __construct() {
            add_action( 'rest_api_init', array( $this, 'register_routes' ) );
        }

        public function register_routes() {
            register_rest_route(
                'rewrite-toolkit/v1',
                '/rules',
                array(
                    array(
                        'methods'             => WP_REST_Server::READABLE,
                        'callback'            => array( $this, 'get_rules' ),
                        'permission_callback' => '__return_true',
                    ),
                )
            );

            register_rest_route(
                'rewrite-toolkit/v1',
                '/tags',
                array(
                    array(
                        'methods'             => WP_REST_Server::READABLE,
                        'callback'            => array( $this, 'get_tags' ),
                        'permission_callback' => '__return_true',
                    ),
                )
            );

            register_rest_route(
                'rewrite-toolkit/v1',
                '/flush',
                array(
                    array(
                        'methods'             => WP_REST_Server::CREATABLE,
                        'callback'            => array( $this, 'flush_rules' ),
                        'permission_callback' => array( $this, 'can_manage' ),
                    ),
                )
            );

            register_rest_route(
                'rewrite-toolkit/v1',
                '/rules/preview',
                array(
                    array(
                        'methods'             => WP_REST_Server::CREATABLE,
                        'callback'            => array( $this, 'preview_rule' ),
                        'permission_callback' => array( $this, 'can_manage' ),
                        'args'                => array(
                            'pattern' => array(
                                'type'     => 'string',
                                'required' => true,
                            ),
                            'path'    => array(
                                'type'     => 'string',
                                'required' => true,
                            ),
                        ),
                    ),
                )
            );
        }

        public function get_rules( WP_REST_Request $request ) {
            return rest_ensure_response(
                array(
                    'rules' => Rewrite_Toolkit::get_instance()->get_rules(),
                )
            );
        }

        public function get_tags( WP_REST_Request $request ) {
            return rest_ensure_response(
                array(
                    'tags' => Rewrite_Toolkit::get_instance()->get_tags(),
                )
            );
        }

        public function flush_rules( WP_REST_Request $request ) {
            Rewrite_Toolkit::get_instance()->flush_rules();

            return rest_ensure_response(
                array(
                    'flushed'   => true,
                    'timestamp' => rewrite_toolkit_format_datetime( get_option( 'rewrite_toolkit_last_flush' ) ),
                )
            );
        }

        public function preview_rule( WP_REST_Request $request ) {
            $pattern = $request->get_param( 'pattern' );
            $path    = $request->get_param( 'path' );

            $matches = array();
            $delimited_pattern = '#' . str_replace( '#', '\\#', $pattern ) . '#';
            $result            = @preg_match( $delimited_pattern, $path, $matches );

            if ( false === $result ) {
                return new WP_Error( 'invalid_pattern', __( 'Pattern cannot be evaluated.', 'rewrite-toolkit' ), array( 'status' => 400 ) );
            }

            return rest_ensure_response(
                array(
                    'match'   => (bool) $result,
                    'matches' => $matches,
                )
            );
        }

        public function can_manage() {
            return current_user_can( 'manage_options' );
        }
    }

    new Rewrite_Toolkit_REST();
}
