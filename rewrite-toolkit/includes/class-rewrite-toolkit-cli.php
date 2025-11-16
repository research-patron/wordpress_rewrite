<?php
/**
 * WP-CLI helpers.
 */

if ( class_exists( 'WP_CLI_Command' ) && ! class_exists( 'Rewrite_Toolkit_CLI' ) ) {
    class Rewrite_Toolkit_CLI extends WP_CLI_Command {
        /**
         * List configured rewrite rules.
         *
         * ## EXAMPLES
         *
         *     wp rewrite-toolkit list
         */
        public function list_( $args, $assoc_args ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodDoubleUnderscore
            $rules = Rewrite_Toolkit::get_instance()->get_rules();

            if ( empty( $rules ) ) {
                WP_CLI::success( 'No rules have been configured yet.' );
                return;
            }

            \WP_CLI\Utils::format_items(
                'table',
                array_map(
                    function ( $rule ) {
                        return array(
                            'label'    => $rule['label'] ?? '',
                            'pattern'  => $rule['pattern'] ?? '',
                            'query'    => $rule['query'] ?? '',
                            'priority' => $rule['priority'] ?? 'top',
                            'enabled'  => ! empty( $rule['enabled'] ) ? 'yes' : 'no',
                        );
                    },
                    $rules
                ),
                array( 'label', 'pattern', 'query', 'priority', 'enabled' )
            );
        }

        /**
         * Flush rewrite rules from the command line.
         *
         * ## EXAMPLES
         *
         *     wp rewrite-toolkit flush
         */
        public function flush( $args, $assoc_args ) {
            Rewrite_Toolkit::get_instance()->flush_rules();
            WP_CLI::success( 'Rewrite rules flushed.' );
        }

        /**
         * Test a stored rule against a path.
         *
         * ## OPTIONS
         *
         * <path>
         * : Relative path to test, e.g. docs/how-to
         *
         * [--pattern=<pattern>]
         * : Optional regex pattern. When omitted the command will test against every stored rule until one matches.
         */
        public function test( $args, $assoc_args ) {
            $path    = $args[0] ?? '';
            $pattern = $assoc_args['pattern'] ?? '';

            if ( empty( $path ) ) {
                WP_CLI::error( 'You must pass a path to test.' );
            }

            $rules = Rewrite_Toolkit::get_instance()->get_rules();

            if ( empty( $pattern ) ) {
                foreach ( $rules as $rule ) {
                    if ( empty( $rule['pattern'] ) ) {
                        continue;
                    }

                    $delimited = '#' . str_replace( '#', '\\#', $rule['pattern'] ) . '#';

                    if ( preg_match( $delimited, $path, $matches ) ) {
                        WP_CLI::success( sprintf( 'Matched rule "%s". Query: %s', $rule['label'], $rule['query'] ) );
                        WP_CLI::line( print_r( $matches, true ) );
                        return;
                    }
                }

                WP_CLI::warning( 'No stored rules match the provided path.' );
                return;
            }

            $delimited = '#' . str_replace( '#', '\\#', $pattern ) . '#';
            $result    = preg_match( $delimited, $path, $matches );
            if ( $result ) {
                WP_CLI::success( 'Pattern matched.' );
                WP_CLI::line( print_r( $matches, true ) );
            } else {
                WP_CLI::warning( 'Pattern did not match.' );
            }
        }
    }

    WP_CLI::add_command( 'rewrite-toolkit', 'Rewrite_Toolkit_CLI' );
}
