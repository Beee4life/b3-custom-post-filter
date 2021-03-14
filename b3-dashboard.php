<?php
    /*
     * Content for the settings page
     */
    function b3cpf_dashboard() {
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
        }
        
        B3CustomPostFilter::b3cpf_show_admin_notices();
    
        $all_post_types      = get_post_types( [], 'objects' );
        $allowed_post_types  = apply_filters( 'b3cpf_allowed_post_types', get_option( 'b3cpf_post_types', [] ) );
        $all_filters         = get_option( 'b3cpf_post_filters' );
        $excluded_post_types = apply_filters( 'b3cpf_excluded_post_types', [
            'attachment',
            'revision',
            'nav_menu_item',
            'custom_css',
            'customize_changeset',
            'oembed_cache',
            'user_request',
            'wp_block',
            'acf-field-group',
            'acf-field'
        ] );
        ?>
        
        <div class="wrap b3cpf">
            <div id="icon-options-general" class="icon32"><br /></div>
            
            <h1>
                <?php echo get_admin_page_title(); ?>
            </h1>

            <div class="b3cpf__section b3cpf__section--post-types">

                <h2>
                    <?php esc_html_e( 'Set allowed post types', 'b3-cpf' ); ?>
                </h2>
                
                <form method="post">
                    <input name="b3cpf_set_post_types_nonce" type="hidden" value="<?php echo wp_create_nonce( 'b3cpf-set-post-types-nonce' ); ?>" />
                    
                    <ul>
                        <?php foreach( $all_post_types as $post_type => $values ) { ?>
                            <?php if ( ! in_array( $post_type, $excluded_post_types ) ) { ?>
                                <?php $selected = ( in_array( $post_type, $allowed_post_types ) ) ? ' checked="checked"' : false; ?>
                                <li>
                                    <label>
                                        <input name="b3cpf_post_type[]" type="checkbox" value="<?php echo $post_type; ?>"<?php echo $selected; ?>/>
                                        <?php echo $values->label; ?>
                                    </label>
                                </li>
                            <?php } ?>
                        <?php } ?>
                    </ul>

                    <input type="submit" class="button button-primary" value="<?php esc_html_e( 'Save allowed post types', 'b3-cpf' ); ?>" />
                </form>
            </div>
    
            <?php if ( is_array( $all_post_types ) && ! empty( $all_post_types ) ) { ?>
                <div class="b3cpf__section b3cpf__section--filters">
                    <?php if ( $all_filters && is_array( $all_filters ) && ! empty( $all_filters ) ) { ?>
                        <h2>
                            <?php esc_html_e( 'Current filter options', 'b3-cpf' ); ?>
                        </h2>
                        <?php if ( $all_filters ) { ?>
                            <form method="post">
                                <input name="b3cpf_remove_filters_nonce" type="hidden" value="<?php echo wp_create_nonce( 'b3cpf-remove-filters-nonce' ); ?>" />
                                <table>
                                    <thead>
                                    <tr>
                                        <th>Remove</th>
                                        <th>Meta key</th>
                                        <th>Option label</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach( $all_filters as $key => $label ) { ?>
                                            <tr>
                                                <td>
                                                    <input name="" type="hidden" value="<?php echo $key; ?>" />
                                                    <label>
                                                        <input name="b3cpf_filters[]" type="checkbox" value="<?php echo $key; ?>" />
                                                    </label>
                                                </td>
                                                <td>
                                                    <?php echo $key; ?>
                                                </td>
                                                <td>
                                                    <?php echo $label; ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                                
                                <br />
                                <input type="submit" class="button button-primary" value="<?php esc_html_e( 'Remove filters', 'b3-cpf' ); ?>" />
                            </form>
                        <?php } ?>
                    <?php } ?>
                    
                    <h2>
                        <?php esc_html_e( 'Add filter options', 'b3-cpf' ); ?>
                    </h2>
                    <form method="post">
                        <input name="b3cpf_set_filters_nonce" type="hidden" value="<?php echo wp_create_nonce( 'b3cpf-set-filters-nonce' ); ?>" />

                        <label>
                            <input name="b3cpf_filter_key" type="text" value="" placeholder="Meta key (no spaces)" />
                        </label>

                        <label>
                            <input name="b3cpf_filter_label" type="text" value="" placeholder="Filter label" />
                        </label>

                        <label>
                            <select class="hidden" name="b3cpf_filter_operator">
                                <option value="">Operator</option>
                                <option value="not_empty">Not empty</option>
                                <option value="empty">Empty</option>
                            </select>
                        </label>

                        <input type="submit" class="button button-primary" value="<?php esc_html_e( 'Add filter', 'b3-cpf' ); ?>" />
                    </form>

                </div>
            <?php } ?>

        </div>
        <?php
    }

