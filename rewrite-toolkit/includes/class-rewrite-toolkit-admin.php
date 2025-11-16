<?php
/**
 * Admin UI for Rewrite Toolkit.
 */

if ( ! class_exists( 'Rewrite_Toolkit_Admin' ) ) {
    class Rewrite_Toolkit_Admin {
        public function __construct() {
            add_action( 'admin_menu', array( $this, 'register_menu' ) );
            add_action( 'admin_post_rewrite_toolkit_save_rule', array( $this, 'save_rule' ) );
            add_action( 'admin_post_rewrite_toolkit_delete_rule', array( $this, 'delete_rule' ) );
            add_action( 'admin_post_rewrite_toolkit_save_tag', array( $this, 'save_tag' ) );
            add_action( 'admin_post_rewrite_toolkit_delete_tag', array( $this, 'delete_tag' ) );
        }

        public function register_menu() {
            add_options_page(
                __( 'Rewrite Toolkit', 'rewrite-toolkit' ),
                __( 'Rewrite Toolkit', 'rewrite-toolkit' ),
                'manage_options',
                'rewrite-toolkit',
                array( $this, 'render_page' )
            );
        }

        public function render_page() {
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }

            $core   = Rewrite_Toolkit::get_instance();
            $rules  = $core->get_rules();
            $tags   = $core->get_tags();
            $last   = get_option( 'rewrite_toolkit_last_flush' );
            ?>
            <div class="wrap">
                <h1><?php esc_html_e( 'Rewrite Toolkit', 'rewrite-toolkit' ); ?></h1>
                <p><?php esc_html_e( 'Create custom rewrite tags and rules without touching code. Rules are loaded on every page request, so keep them concise.', 'rewrite-toolkit' ); ?></p>
                <?php settings_errors( 'rewrite_toolkit' ); ?>

                <h2><?php esc_html_e( 'Rewrite Tags', 'rewrite-toolkit' ); ?></h2>
                <p><?php esc_html_e( 'Tags become available as tokens such as %my-tag% that you can re-use when building rewrite rules.', 'rewrite-toolkit' ); ?></p>
                <?php $this->render_tags_table( $tags ); ?>

                <h2><?php esc_html_e( 'Rewrite Rules', 'rewrite-toolkit' ); ?></h2>
                <p><?php esc_html_e( 'Rules run in the order they are stored. Remember to flush rules when you add or edit items.', 'rewrite-toolkit' ); ?></p>
                <?php $this->render_rules_table( $rules ); ?>

                <h2><?php esc_html_e( 'Maintenance', 'rewrite-toolkit' ); ?></h2>
                <p><?php printf( esc_html__( 'Last flushed: %s', 'rewrite-toolkit' ), esc_html( rewrite_toolkit_format_datetime( $last ) ) ); ?></p>
                <form method="post">
                    <?php wp_nonce_field( 'rewrite_toolkit_flush' ); ?>
                    <input type="hidden" name="rewrite_toolkit_flush_rules" value="1" />
                    <?php submit_button( __( 'Flush rewrite rules now', 'rewrite-toolkit' ), 'secondary', 'submit', false ); ?>
                </form>
            </div>
            <?php
        }

        private function render_rules_table( $rules ) {
            ?>
            <table class="widefat striped">
                <thead>
                <tr>
                    <th><?php esc_html_e( 'Label', 'rewrite-toolkit' ); ?></th>
                    <th><?php esc_html_e( 'Pattern', 'rewrite-toolkit' ); ?></th>
                    <th><?php esc_html_e( 'Query', 'rewrite-toolkit' ); ?></th>
                    <th><?php esc_html_e( 'Priority', 'rewrite-toolkit' ); ?></th>
                    <th><?php esc_html_e( 'Enabled', 'rewrite-toolkit' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'rewrite-toolkit' ); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php if ( empty( $rules ) ) : ?>
                    <tr>
                        <td colspan="6"><?php esc_html_e( 'No custom rewrite rules have been added yet.', 'rewrite-toolkit' ); ?></td>
                    </tr>
                <?php else : ?>
                    <?php foreach ( $rules as $rule ) : ?>
                        <tr>
                            <td><?php echo esc_html( $rule['label'] ?? '' ); ?></td>
                            <td><code><?php echo esc_html( $rule['pattern'] ?? '' ); ?></code></td>
                            <td><code><?php echo esc_html( $rule['query'] ?? '' ); ?></code></td>
                            <td><?php echo esc_html( ucfirst( $rule['priority'] ?? 'top' ) ); ?></td>
                            <td><?php echo ! empty( $rule['enabled'] ) ? '✅' : '—'; ?></td>
                            <td>
                                <details>
                                    <summary><?php esc_html_e( 'Edit', 'rewrite-toolkit' ); ?></summary>
                                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                                        <?php wp_nonce_field( 'rewrite_toolkit_save_rule' ); ?>
                                        <input type="hidden" name="action" value="rewrite_toolkit_save_rule" />
                                        <input type="hidden" name="id" value="<?php echo esc_attr( $rule['id'] ); ?>" />
                                        <p>
                                            <label><?php esc_html_e( 'Label', 'rewrite-toolkit' ); ?></label>
                                            <input type="text" name="label" value="<?php echo esc_attr( $rule['label'] ?? '' ); ?>" class="regular-text" required />
                                        </p>
                                        <p>
                                            <label><?php esc_html_e( 'Pattern', 'rewrite-toolkit' ); ?></label>
                                            <input type="text" name="pattern" value="<?php echo esc_attr( $rule['pattern'] ?? '' ); ?>" class="regular-text" required />
                                        </p>
                                        <p>
                                            <label><?php esc_html_e( 'Query', 'rewrite-toolkit' ); ?></label>
                                            <input type="text" name="query" value="<?php echo esc_attr( $rule['query'] ?? '' ); ?>" class="regular-text" required />
                                        </p>
                                        <p>
                                            <label><?php esc_html_e( 'Priority', 'rewrite-toolkit' ); ?></label>
                                            <select name="priority">
                                                <option value="top" <?php selected( $rule['priority'] ?? 'top', 'top' ); ?>><?php esc_html_e( 'Top', 'rewrite-toolkit' ); ?></option>
                                                <option value="bottom" <?php selected( $rule['priority'] ?? 'top', 'bottom' ); ?>><?php esc_html_e( 'Bottom', 'rewrite-toolkit' ); ?></option>
                                            </select>
                                        </p>
                                        <p>
                                            <label><input type="checkbox" name="enabled" value="1" <?php checked( ! empty( $rule['enabled'] ) ); ?> /> <?php esc_html_e( 'Enabled', 'rewrite-toolkit' ); ?></label>
                                        </p>
                                        <?php submit_button( __( 'Save changes', 'rewrite-toolkit' ), 'secondary', 'submit', false ); ?>
                                    </form>
                                </details>
                                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;margin-left:8px;">
                                    <?php wp_nonce_field( 'rewrite_toolkit_delete_rule' ); ?>
                                    <input type="hidden" name="action" value="rewrite_toolkit_delete_rule" />
                                    <input type="hidden" name="id" value="<?php echo esc_attr( $rule['id'] ); ?>" />
                                    <?php submit_button( __( 'Delete', 'rewrite-toolkit' ), 'delete', 'submit', false, sprintf( 'onclick="return confirm(\'%s\');"', esc_js( __( 'Delete this rule?', 'rewrite-toolkit' ) ) ) ); ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>

            <h3><?php esc_html_e( 'Add new rule', 'rewrite-toolkit' ); ?></h3>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'rewrite_toolkit_save_rule' ); ?>
                <input type="hidden" name="action" value="rewrite_toolkit_save_rule" />
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="rewrite_rule_label"><?php esc_html_e( 'Label', 'rewrite-toolkit' ); ?></label></th>
                        <td><input type="text" id="rewrite_rule_label" name="label" class="regular-text" required /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="rewrite_rule_pattern"><?php esc_html_e( 'Regex pattern', 'rewrite-toolkit' ); ?></label></th>
                        <td><input type="text" id="rewrite_rule_pattern" name="pattern" class="regular-text" required placeholder="^docs/([^/]+)/?" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="rewrite_rule_query"><?php esc_html_e( 'Query string', 'rewrite-toolkit' ); ?></label></th>
                        <td><input type="text" id="rewrite_rule_query" name="query" class="regular-text" required placeholder="index.php?post_type=docs&name=$matches[1]" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Priority', 'rewrite-toolkit' ); ?></th>
                        <td>
                            <select name="priority">
                                <option value="top"><?php esc_html_e( 'Top', 'rewrite-toolkit' ); ?></option>
                                <option value="bottom"><?php esc_html_e( 'Bottom', 'rewrite-toolkit' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Enabled', 'rewrite-toolkit' ); ?></th>
                        <td><label><input type="checkbox" name="enabled" value="1" checked /> <?php esc_html_e( 'Active', 'rewrite-toolkit' ); ?></label></td>
                    </tr>
                </table>
                <?php submit_button( __( 'Save rule', 'rewrite-toolkit' ) ); ?>
            </form>
            <?php
        }

        private function render_tags_table( $tags ) {
            ?>
            <table class="widefat striped">
                <thead>
                <tr>
                    <th><?php esc_html_e( 'Name', 'rewrite-toolkit' ); ?></th>
                    <th><?php esc_html_e( 'Regex', 'rewrite-toolkit' ); ?></th>
                    <th><?php esc_html_e( 'Query var (optional)', 'rewrite-toolkit' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'rewrite-toolkit' ); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php if ( empty( $tags ) ) : ?>
                    <tr>
                        <td colspan="4"><?php esc_html_e( 'No rewrite tags yet.', 'rewrite-toolkit' ); ?></td>
                    </tr>
                <?php else : ?>
                    <?php foreach ( $tags as $tag ) : ?>
                        <tr>
                            <td><code>%<?php echo esc_html( $tag['name'] ); ?>%</code></td>
                            <td><code><?php echo esc_html( $tag['regex'] ); ?></code></td>
                            <td><?php echo esc_html( $tag['query_var'] ); ?></td>
                            <td>
                                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;">
                                    <?php wp_nonce_field( 'rewrite_toolkit_delete_tag' ); ?>
                                    <input type="hidden" name="action" value="rewrite_toolkit_delete_tag" />
                                    <input type="hidden" name="id" value="<?php echo esc_attr( $tag['id'] ); ?>" />
                                    <?php submit_button( __( 'Delete', 'rewrite-toolkit' ), 'delete', 'submit', false, sprintf( 'onclick="return confirm(\'%s\');"', esc_js( __( 'Delete this tag?', 'rewrite-toolkit' ) ) ) ); ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>

            <h3><?php esc_html_e( 'Add new tag', 'rewrite-toolkit' ); ?></h3>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'rewrite_toolkit_save_tag' ); ?>
                <input type="hidden" name="action" value="rewrite_toolkit_save_tag" />
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="rewrite_tag_name"><?php esc_html_e( 'Name (without %)', 'rewrite-toolkit' ); ?></label></th>
                        <td><input type="text" id="rewrite_tag_name" name="name" class="regular-text" required /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="rewrite_tag_regex"><?php esc_html_e( 'Regex', 'rewrite-toolkit' ); ?></label></th>
                        <td><input type="text" id="rewrite_tag_regex" name="regex" class="regular-text" required placeholder="([^/]+)" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="rewrite_tag_query_var"><?php esc_html_e( 'Query var (optional)', 'rewrite-toolkit' ); ?></label></th>
                        <td><input type="text" id="rewrite_tag_query_var" name="query_var" class="regular-text" placeholder="doc_token" /></td>
                    </tr>
                </table>
                <?php submit_button( __( 'Save tag', 'rewrite-toolkit' ) ); ?>
            </form>
            <?php
        }

        public function save_rule() {
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( esc_html__( 'You do not have permission to edit rewrite rules.', 'rewrite-toolkit' ) );
            }

            check_admin_referer( 'rewrite_toolkit_save_rule' );

            $rules = get_option( Rewrite_Toolkit::OPTION_RULES, array() );
            $rule  = rewrite_toolkit_sanitize_rule( $_POST );

            $redirect = wp_get_referer() ?: admin_url( 'options-general.php?page=rewrite-toolkit' );

            if ( empty( $rule['pattern'] ) || empty( $rule['query'] ) ) {
                add_settings_error( 'rewrite_toolkit', 'invalid-rule', __( 'Both pattern and query string are required.', 'rewrite-toolkit' ) );
                wp_safe_redirect( $redirect );
                exit;
            }

            $rules = is_array( $rules ) ? $rules : array();
            $found = false;
            foreach ( $rules as $index => $existing ) {
                if ( isset( $existing['id'] ) && $existing['id'] === $rule['id'] ) {
                    $rules[ $index ] = $rule;
                    $found           = true;
                    break;
                }
            }

            if ( ! $found ) {
                $rules[] = $rule;
            }

            update_option( Rewrite_Toolkit::OPTION_RULES, array_values( $rules ) );
            Rewrite_Toolkit::get_instance()->flush_rules();
            add_settings_error( 'rewrite_toolkit', 'rule-saved', __( 'Rewrite rule saved.', 'rewrite-toolkit' ), 'updated' );
            wp_safe_redirect( $redirect );
            exit;
        }

        public function delete_rule() {
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( esc_html__( 'You do not have permission to delete rewrite rules.', 'rewrite-toolkit' ) );
            }

            check_admin_referer( 'rewrite_toolkit_delete_rule' );

            $redirect = wp_get_referer() ?: admin_url( 'options-general.php?page=rewrite-toolkit' );

            $rules = get_option( Rewrite_Toolkit::OPTION_RULES, array() );
            $rule_id = sanitize_key( $_POST['id'] ?? '' );
            $rules   = array_filter(
                $rules,
                function ( $rule ) use ( $rule_id ) {
                    return isset( $rule['id'] ) && $rule['id'] !== $rule_id;
                }
            );

            update_option( Rewrite_Toolkit::OPTION_RULES, array_values( $rules ) );
            Rewrite_Toolkit::get_instance()->flush_rules();
            add_settings_error( 'rewrite_toolkit', 'rule-deleted', __( 'Rewrite rule deleted.', 'rewrite-toolkit' ), 'updated' );
            wp_safe_redirect( $redirect );
            exit;
        }

        public function save_tag() {
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( esc_html__( 'You do not have permission to edit rewrite tags.', 'rewrite-toolkit' ) );
            }

            check_admin_referer( 'rewrite_toolkit_save_tag' );

            $redirect = wp_get_referer() ?: admin_url( 'options-general.php?page=rewrite-toolkit' );

            $tag  = rewrite_toolkit_sanitize_tag( $_POST );
            $tags = get_option( Rewrite_Toolkit::OPTION_TAGS, array() );

            if ( empty( $tag['name'] ) || empty( $tag['regex'] ) ) {
                add_settings_error( 'rewrite_toolkit', 'invalid-tag', __( 'Both name and regex are required.', 'rewrite-toolkit' ) );
                wp_safe_redirect( $redirect );
                exit;
            }

            $tags   = is_array( $tags ) ? $tags : array();
            $exists = false;
            foreach ( $tags as $index => $existing ) {
                if ( isset( $existing['id'] ) && $existing['id'] === $tag['id'] ) {
                    $tags[ $index ] = $tag;
                    $exists         = true;
                    break;
                }
            }

            if ( ! $exists ) {
                $tags[] = $tag;
            }

            update_option( Rewrite_Toolkit::OPTION_TAGS, array_values( $tags ) );
            Rewrite_Toolkit::get_instance()->flush_rules();
            add_settings_error( 'rewrite_toolkit', 'tag-saved', __( 'Rewrite tag saved.', 'rewrite-toolkit' ), 'updated' );
            wp_safe_redirect( $redirect );
            exit;
        }

        public function delete_tag() {
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( esc_html__( 'You do not have permission to delete rewrite tags.', 'rewrite-toolkit' ) );
            }

            check_admin_referer( 'rewrite_toolkit_delete_tag' );

            $redirect = wp_get_referer() ?: admin_url( 'options-general.php?page=rewrite-toolkit' );

            $tags = get_option( Rewrite_Toolkit::OPTION_TAGS, array() );
            $tag_id = sanitize_key( $_POST['id'] ?? '' );
            $tags   = array_filter(
                $tags,
                function ( $tag ) use ( $tag_id ) {
                    return isset( $tag['id'] ) && $tag['id'] !== $tag_id;
                }
            );

            update_option( Rewrite_Toolkit::OPTION_TAGS, array_values( $tags ) );
            Rewrite_Toolkit::get_instance()->flush_rules();
            add_settings_error( 'rewrite_toolkit', 'tag-deleted', __( 'Rewrite tag deleted.', 'rewrite-toolkit' ), 'updated' );
            wp_safe_redirect( $redirect );
            exit;
        }
    }

    new Rewrite_Toolkit_Admin();
}
